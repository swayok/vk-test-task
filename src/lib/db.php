<?php

namespace Db;

$__DB_CONNECTIONS = array();

function addDbConnectionConfig($config, $name = 'default') {
    if (array_key_exists($name, $GLOBALS['__DB_CONNECTIONS'])) {
        throw new \Exception("DB Config [{$name}] already exists");
    }

    if (
        !is_array($config)
        || !array_key_exists('user', $config)
        || !array_key_exists('password', $config)
    ) {
        throw new \Exception("Invalid DB Config [{$name}]");
    }

    static $defaultConfig = array(
        'host' => 'localhost',
        'port' => 3306,
        'name' => '',
        'user' => '',
        'password' => '',
        'encoding' => 'UTF-8',
        'connection' => null
    );
    $GLOBALS['__DB_CONNECTIONS'][$name] = array_replace($defaultConfig, $config);
}

/**
 * @param $name - connection name
 * @return \mysqli
 * @throws \Exception
 */
function getConnection($name = 'default') {

    if (empty($name)) {
        throw new \Exception('Invalid DB Connection name');
    }

    if (!array_key_exists($name, $GLOBALS['__DB_CONNECTIONS'])) {
        throw new \Exception("Unknown DB Connection name [$name]");
    }

    if (!isset($GLOBALS['__DB_CONNECTIONS'][$name]['connection'])) {
        $GLOBALS['__DB_CONNECTIONS'][$name]['connection'] = mysqli_connect(
            $GLOBALS['__DB_CONNECTIONS'][$name]['host'],
            $GLOBALS['__DB_CONNECTIONS'][$name]['user'],
            $GLOBALS['__DB_CONNECTIONS'][$name]['password'],
            $GLOBALS['__DB_CONNECTIONS'][$name]['name'],
            $GLOBALS['__DB_CONNECTIONS'][$name]['port']
        );
        if ($GLOBALS['__DB_CONNECTIONS'][$name]['connection'] === false) {
            throw new \Exception(mysqli_connect_error());
        }
    }

    return $GLOBALS['__DB_CONNECTIONS'][$name]['connection'];
}

/**
 * @param $connectionName
 * @param string $query
 * @return bool|\mysqli_result
 * @throws \Exception
 */
function query($query, $connectionName = 'default') {
    // Connect to the database
    $connection = getConnection($connectionName);

    // Query the database
    $result = mysqli_query($connection, $query);
    if ($result === false) {
        throw new \Exception(getQueryError());
    }
    return $result;
}

/**
 * @param string $query
 * @param string $connectionName
 * @return bool|array
 * @throws \Exception
 */
function select($query, $connectionName = 'default') {
    $rows = array();
    $result = query($query, $connectionName);
    // If query failed, return `false`
    if ($result === false) {
        return false;
    }
    // If query was successful, retrieve all the rows into an array
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * @param string $query
 * @param array $values
 * @param string $connectionName
 * @return bool|array
 */
function smartSelect($query, $values = array(), $connectionName = 'default') {
    foreach ($values as $key => $value) {
        $query = str_replace(':' . $key, quoteValue($value, $connectionName), $query);
    }
    return select($query, $connectionName);
}

/**
 * @param string $connectionName
 * @return string
 * @throws \Exception
 */
function getQueryError($connectionName = 'default') {
    $connection = getConnection($connectionName);
    return mysqli_error($connection);
}

/**
 * @param string $value
 * @param string $connectionName
 * @return string
 * @throws \Exception
 */
function quoteValue($value, $connectionName = 'default') {
    $connection = getConnection($connectionName);
    return "'" . mysqli_real_escape_string($connection, $value) . "'";
}

/**
 * @param $data
 * @param $table
 * @param string $connectionName
 * @return bool|array
 * @throws \Exception
 */
function insert($data, $table, $connectionName = 'default') {
    $values = array();
    foreach ($data as $key => $value) {
        $values[$key] = quoteValue($value, $connectionName);
    }
    $query = "INSERT INTO `$table` (`" . implode('`,`', array_keys($values)) . '`) VALUES (' . implode(',', $values) . ')';
    $result = query($query);
    if ($result->num_rows) {
        $id = quoteValue(mysqli_insert_id(getConnection($connectionName)));
        $rows = select("SELECT * from `$table` WHERE `id` = $id");
        if (!empty($rows[0])) {
            return $rows[0];
        }
    }
    return false;
}