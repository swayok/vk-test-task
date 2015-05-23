<?php

namespace Db;

$__DB_CONNECTIONS = array();
$__DB_LAST_QUERY = '';

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
        'encoding' => 'UTF8',
        'connection' => null
    );
    $GLOBALS['__DB_CONNECTIONS'][$name] = array_replace($defaultConfig, $config);
}

function getLastQuery() {
    return $GLOBALS['__DB_LAST_QUERY'];
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
        query('SET NAMES ' . $GLOBALS['__DB_CONNECTIONS'][$name]['encoding'], $name);
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
    $GLOBALS['__DB_LAST_QUERY'] = $query;
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
function smartSelect($query, array $values = array(), $connectionName = 'default') {
    foreach ($values as $key => $value) {
        $query = str_replace(':' . $key, quoteValue($value, $connectionName), $query);
    }
    return select($query, $connectionName);
}

/**
 * Select 1st value from the 1st record.
 * Use for queries like "Select Count(*) FROM tabe"
 * @param $query
 * @param $values
 * @param string $connectionName
 * @return mixed|null
 */
function selectValue($query, array $values = array(), $connectionName = 'default') {
    $records = smartSelect($query, $values, $connectionName);
    if (empty($records)) {
        return null;
    } else {
        return array_shift($records[0]);
    }
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
    if (is_bool($value)) {
        return $value ? '1' : '0';
    } if (!is_int($value) && !is_float($value)) {
        $connection = getConnection($connectionName);
        return "'" . mysqli_real_escape_string($connection, $value) . "'";
    } else {
        return "$value";
    }
}

/**
 * @param array $data
 * @param string $table
 * @param string $connectionName
 * @return bool|array
 * @throws \Exception
 */
function insert(array $data, $table, $connectionName = 'default') {
    if (empty($data)) {
        throw new \Exception('No data passed');
    } else if (empty($table)) {
        throw new \Exception('No table passed');
    }
    $values = array();
    foreach ($data as $key => $value) {
        $values[$key] = quoteValue($value, $connectionName);
    }
    $table = trim($table, '` ');
    $query = "INSERT INTO `$table` (`" . implode('`,`', array_keys($values)) . '`) VALUES (' . implode(',', $values) . ')';
    $result = query($query, $connectionName);
    if (!empty($result)) {
        $id = quoteValue(mysqli_insert_id(getConnection($connectionName)), $connectionName);
        $rows = select("SELECT * FROM `$table` WHERE `id` = $id", $connectionName);
        if (!empty($rows[0])) {
            return $rows[0];
        }
    }
    return false;
}

/**
 * @param array $data
 * @param string $table
 * @param mixed $id
 * @param string $connectionName
 * @return array|bool
 * @throws \Exception
 */
function updateById(array $data, $table, $id, $connectionName = 'default') {
    if (empty($data)) {
        throw new \Exception('No data passed');
    } else if (empty($table)) {
        throw new \Exception('No table passed');
    } else if (empty($id)) {
        throw new \Exception('No record ID passed');
    }
    $values = array();
    foreach ($data as $key => $value) {
        $values[$key] = quoteValue($value, $connectionName);
    }
    $table = trim($table, '` ');
    $values = array();
    foreach ($data as $field => $value) {
        $values[] = "`$field` = " . quoteValue($value, $connectionName);
    }
    $values = implode(', ', $values);
    $id = quoteValue($id, $connectionName);
    $query = "UPDATE `$table` SET {$values} WHERE `id` = $id";
    $result = query($query, $connectionName);
    if (!empty($result)) {
        $rows = select("SELECT * FROM `$table` WHERE `id` = $id", $connectionName);
        if (!empty($rows[0])) {
            return $rows[0];
        }
    }
    return false;
}

function idExists($id, $table, $connectionName = 'default') {
    if (empty($table)) {
        throw new \Exception('No table passed');
    } else if (empty($id)) {
        throw new \Exception('No record ID passed');
    }
    $table = trim($table, '` ');
    return selectValue("SELECT COUNT(*) as cnt FROM `$table` WHERE `id` = :id", array('id' => $id), $connectionName);
}