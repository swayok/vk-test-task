<nav class="navbar navbar-default">
    <div class="container-fluid">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#client-navigation-links">
            <span class="sr-only"><?php \Dictionary\translate('Toggle navigation'); ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/?route=client-tasks-list" ><?php echo \Dictionary\translate('OES') ?>: <?php echo \Dictionary\translate('Client') ?></a>
    </div>
        <div class="collapse navbar-collapse" id="client-navigation-links">
            <ul class="nav navbar-nav">
                <li>
                    <a href="/?route=client-tasks-list"><?php echo \Dictionary\translate('My tasks'); ?></a>
                </li>
                <li>
                    <a href="/?route=client-task-add"><?php echo \Dictionary\translate('Add task'); ?></a>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="/?route=client-profile" id="profile-edit">{{?it.user}}{{=it.user.email}}{{?}}</a>
                </li>
                <li><a href="/?route=logout"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a></li>
            </ul>
        </div>
    </div>
</nav>
