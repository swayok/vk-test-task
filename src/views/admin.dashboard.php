<h1><?php echo \Dictionary\translate('System Stats'); ?></h1>

<div id="dashboard-panels">
    <div class="row">
        <div class="col-md-3">
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item list-group-item-success">
                        <strong><?php echo \Dictionary\translate('Number of executed tasks') ?></strong>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Today') ?>
                        <span class="badge">{{= it.stats.tasks_executed_today }}</span>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Yesterday') ?>
                        <span class="badge">{{= it.stats.tasks_executed_yesterday }}</span>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Total') ?>
                        <span class="badge">{{= it.stats.tasks_executed_total }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item list-group-item-info">
                        <strong><?php echo \Dictionary\translate('Number of added tasks') ?></strong>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Today') ?>
                        <span class="badge">{{= it.stats.tasks_added_today }}</span>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Yesterday') ?>
                        <span class="badge">{{= it.stats.tasks_added_yesterday }}</span>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Total') ?>
                        <span class="badge">{{= it.stats.tasks_total }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item list-group-item-success">
                        <strong><?php echo \Dictionary\translate('Payments to system') ?></strong>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Today') ?>
                        <span class="badge">{{= it.stats.system_earned_today }} <?php echo \Dictionary\translate('RUB') ?></span>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Yesterday') ?>
                        <span class="badge">{{= it.stats.system_earned_yesterday }} <?php echo \Dictionary\translate('RUB') ?></span>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Total') ?>
                        <span class="badge">{{= it.stats.system_earned_total }} <?php echo \Dictionary\translate('RUB') ?></span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item list-group-item-info" >
                        <strong><?php echo \Dictionary\translate('Payments to executors') ?></strong>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Today') ?>
                        <span class="badge">{{= it.stats.executors_earned_today }} <?php echo \Dictionary\translate('RUB') ?></span>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Yesterday') ?>
                        <span class="badge">{{= it.stats.executors_earned_yesterday }} <?php echo \Dictionary\translate('RUB') ?></span>
                    </li>
                    <li class="list-group-item">
                        <?php echo \Dictionary\translate('Total') ?>
                        <span class="badge">{{= it.stats.executors_earned_total }} <?php echo \Dictionary\translate('RUB') ?></span>
                    </li>
                </ul>
            </div>
        </div>
</div>