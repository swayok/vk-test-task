<div class="row data-grid-header">
    <div class="col-md-9">
        <h1><?php echo \Dictionary\translate('Clients'); ?></h1>
    </div>
    <div class="col-md-3 actions">
        <a href="/?route=admin-client-add" data-add-back-url="1" class="create-link btn btn-primary btn-sm">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            <?php echo \Dictionary\translate('Add client'); ?>
        </a>
    </div>
</div>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th><?php echo \Dictionary\translate('ID'); ?></th>
            <th><?php echo \Dictionary\translate('E-mail'); ?></th>
            <th><?php echo \Dictionary\translate('Status'); ?></th>
            <th>
                <?php echo \Dictionary\translate('Created at'); ?>
                <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
            </th>
            <th><?php echo \Dictionary\translate('Created by'); ?></th>
            <th><?php echo \Dictionary\translate('Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        {{~it.items :item}}
        <tr>
            <td>{{=item.id}}</td>
            <td>{{=item.email}}</td>
            {{?item.is_active == 1}}
                <td class="success"><?php echo \Dictionary\translate('Active'); ?>{{?}}</td>
            {{?item.is_active == 0}}
                <td class="danger"><?php echo \Dictionary\translate('Inactive'); ?></td>
            {{?}}
            <td>{{=item.created_at}}</td>
            <td>{{=item.creator_email || ''}}</td>
            <td class="actions">
                <a data-route="admin-client-edit" data-args="id={{=item.id}}"
                   class="edit-link btn btn-primary btn-sm" href="javascript:void(0)">
                    <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                    <?php echo \Dictionary\translate('Edit'); ?>
                </a>
                {{? item.is_active == 1 }}
                    <a data-api-action="update-client" data-args="id={{=item.id}}&is_active=0" data-method="post"
                       class="deactivate-link btn btn-danger btn-sm" href="javascript:void(0)">
                        <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
                        <?php echo \Dictionary\translate('Deactivate'); ?>
                    </a>
                {{?}}
                {{? item.is_active == 0 }}
                    <a data-api-action="update-client" data-args="id={{=item.id}}&is_active=1" data-method="post"
                       class="activate-link btn btn-success btn-sm" href="javascript:void(0)">
                        <span class="glyphicon glyphicon-flash" aria-hidden="true"></span>
                        <?php echo \Dictionary\translate('Activate'); ?>
                    </a>
                {{?}}
            </td>
        </tr>
        {{~}}
    </tbody>
</table>