<div class="row data-grid-header">
    <div class="col-md-9">
        <h1><?php echo \Dictionary\translate('My tasks'); ?></h1>
    </div>
    <div class="col-md-3 actions">
        <a href="/?route=client-task-add" data-add-back-url="1" class="create-link btn btn-primary btn-sm">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            <?php echo \Dictionary\translate('Add task'); ?>
        </a>
    </div>
</div>

<table class="table table-striped table-hover tasks-list">
    <thead>
        <tr>
            <th><?php echo \Dictionary\translate('ID'); ?></th>
            <th><?php echo \Dictionary\translate('Task info'); ?></th>
            <th><?php echo \Dictionary\translate('Payment'); ?></th>
            <th>
                <?php echo \Dictionary\translate('Created at'); ?>
                <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
            </th>
            <th><?php echo \Dictionary\translate('Status'); ?></th>
            <th><?php echo \Dictionary\translate('Executor'); ?></th>
            <th><?php echo \Dictionary\translate('Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        {{? !it.items || !it.items.length }}
        <tr>
            <td colspan="8" class="no-items"><?php echo \Dictionary\translate('You haven\'t created any tasks yet'); ?></td>
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
            <td>{{= item.payment }} <?php echo \Dictionary\translate('RUB'); ?></td>
            <td>{{= item.created_at }}</td>
            {{? item.is_active == 1 }}
                {{? !item.executed_at }}
                <td class="warning"><?php echo \Dictionary\translate('Waiting'); ?></td>
                {{??}}
                <td class="success">
                    <?php echo \Dictionary\translate('Executed'); ?>
                    <div class="item-executed-at">{{= item.executed_at }}</div>
                </td>
                {{?}}
            {{?}}
            {{? item.is_active == 0 }}
                <td class="danger"><?php echo \Dictionary\translate('Inactive'); ?></td>
            {{?}}
            <td>{{= item.executor_email || '' }}</td>
            <td class="actions">
                {{? !item.executor_id }}
                    <a data-route="client-task-edit" data-args="id={{=item.id}}"
                       class="edit-link btn btn-primary btn-sm" href="javascript:void(0)">
                        <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                        <?php echo \Dictionary\translate('Edit'); ?>
                    </a>
                    {{? item.is_active == 1 }}
                        <a data-api-action="update-task" data-args="id={{=item.id}}&is_active=0" data-method="post"
                           class="deactivate-link btn btn-danger btn-sm" href="javascript:void(0)">
                            <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
                            <?php echo \Dictionary\translate('Deactivate'); ?>
                        </a>
                    {{?}}
                    {{? item.is_active == 0 }}
                        <a data-api-action="update-task" data-args="id={{=item.id}}&is_active=1" data-method="post"
                           class="activate-link btn btn-success btn-sm" href="javascript:void(0)">
                            <span class="glyphicon glyphicon-flash" aria-hidden="true"></span>
                            <?php echo \Dictionary\translate('Activate'); ?>
                        </a>
                    {{?}}
                    <a data-api-action="delete-task" data-args="id={{=item.id}}" data-method="get"
                       class="delete-link btn btn-danger btn-sm" href="javascript:void(0)"
                       title="<?php echo \Dictionary\translate('Delete'); ?>">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    </a>
                {{??}}
                    &nbsp;
                {{?}}
            </td>
        </tr>
        {{~}}
    </tbody>
</table>