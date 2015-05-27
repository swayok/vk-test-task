<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#admin-navigation-links">
                <span class="sr-only"><?php \Dictionary\translate('Toggle navigation'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/?route=admin-dashboard" >
                <?php echo \Dictionary\translate('OES') ?>: <?php echo \Dictionary\translate('System management') ?>
            </a>
        </div>
        <div class="collapse navbar-collapse" id="admin-navigation-links">
            <ul class="nav navbar-nav">
                <li>
                    <a href="/?route=admin-dashboard"><?php echo \Dictionary\translate('System Stats'); ?></a>
                </li>
                <li>
                    <a href="/?route=admin-clients-list"><?php echo \Dictionary\translate('Clients'); ?></a>
                </li>
                <li>
                    <a href="/?route=admin-executors-list"><?php echo \Dictionary\translate('Executors'); ?></a>
                </li>
                <li>
                    <a href="/?route=admin-admins-list"><?php echo \Dictionary\translate('Admins'); ?></a>
                </li>
            </ul>
            {{? it.user && it.user.email }}
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="/?route=admin-profile" id="profile-edit">{{= it.user.email }}</a>
                    </li>
                    <li><a href="/?route=logout"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a></li>
                </ul>
            {{?}}
        </div>
    </div>
</nav>
