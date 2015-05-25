<?php $forExecutedTasks = !empty($_GET['executed_tasks']); ?>
<div class="row data-grid-header">
    <h1><?php echo \Dictionary\translate($forExecutedTasks ? 'Executed tasks' : 'Pending tasks'); ?></h1>
</div>

<table class="table table-striped table-hover tasks-list">
    <thead>
        <tr>
            <th><?php echo \Dictionary\translate('ID'); ?></th>
            <th><?php echo \Dictionary\translate('Task info'); ?></th>
            <th><?php echo \Dictionary\translate('Payment'); ?></th>
            <th><?php echo \Dictionary\translate('Client'); ?></th>
            <th>
                <?php echo \Dictionary\translate('Created at'); ?>
                <?php if (!$forExecutedTasks): ?>
                    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                <?php endif; ?>
            </th>
            <?php if ($forExecutedTasks): ?>
                <th>
                    <?php echo \Dictionary\translate('Executed at'); ?>
                    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                </th>
            <?php else: ?>
                <th><?php echo \Dictionary\translate('Actions'); ?></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        {{? !it.items || !it.items.length }}
        <tr>
            <td colspan="8" class="no-items">
                <?php echo \Dictionary\translate('There is no ' . ($forExecutedTasks ? 'executed' : 'pending') . ' tasks yet'); ?>
            </td>
        </tr>
        {{?}}
        {{~ it.items :item }}
        <tr>
            <td>{{= item.id }}</td>
            <td class="item-title">
                {{= item.title }}
                <a class="item-description-container" href="javascript: void(0)">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    <div class="item-description">{{=item.description}}</div>
                </a>
            </td>
            <td>
                <?php if ($forExecutedTasks) : ?>
                    {{= item.paid_to_executor }}
                <?php else: ?>
                    {{= item.payment }}
                <?php endif; ?>
                <?php echo \Dictionary\translate('RUB'); ?>
            </td>
            <td>{{= item.client_email }}</td>
            <td>{{= item.created_at }}</td>
            <?php if ($forExecutedTasks): ?>
                <td>{{= item.executed_at }}</td>
            <?php else: ?>
                <td class="actions">
                    {{? !item.executor_id }}
                        <a data-api-action="execute-task" data-args="id={{=item.id}}" data-method="post"
                           class="btn btn-success btn-sm" href="javascript:void(0)">
                            <span class="glyphicon glyphicon-check" aria-hidden="true"></span>
                            <?php echo \Dictionary\translate('Execute'); ?>
                        </a>
                    {{?}}
                </td>
            <?php endif; ?>
        </tr>
        {{~}}
    </tbody>
</table>