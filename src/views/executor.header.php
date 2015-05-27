<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#client-navigation-links">
                <span class="sr-only"><?php \Dictionary\translate('Toggle navigation'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/?route=executor-pending-tasks-list">
                <?php echo \Dictionary\translate('OES') ?>: <?php echo \Dictionary\translate('Executor') ?>
            </a>
        </div>
        <div class="collapse navbar-collapse" id="client-navigation-links">
            <ul class="nav navbar-nav">
                <li>
                    <a href="/?route=executor-pending-tasks-list"><?php echo \Dictionary\translate('Pending tasks'); ?></a>
                </li>
                <li>
                    <a href="/?route=executor-executed-tasks-list"><?php echo \Dictionary\translate('Executed tasks'); ?></a>
                </li>
            </ul>
            {{? it.user && it.user.email }}
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="/?route=executor-profile" id="profile-edit">{{= it.user.email }}</a>
                </li>
                <li class="navbar-text">{{= it.user.balance }} <?php echo \Dictionary\translate('RUB'); ?></li>
                <li><a href="/?route=logout"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a></li>
            </ul>
            {{?}}
        </div>
    </div>
</nav>
