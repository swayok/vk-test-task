<h1><?php echo \Dictionary\translate('Admins'); ?></h1>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th><?php echo \Dictionary\translate('ID'); ?></th>
            <th><?php echo \Dictionary\translate('E-mail'); ?></th>
            <th><?php echo \Dictionary\translate('Status'); ?></th>
            <th><?php echo \Dictionary\translate('Created at'); ?></th>
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
                <td class="bg-success"><?php echo \Dictionary\translate('Active'); ?>{{?}}</td>
            {{?item.is_active == 0}}
                <td class="bg-danger"><?php echo \Dictionary\translate('Inactive'); ?></td>
            {{?}}
            <td>{{=item.created_at}}</td>
            <td>{{=item.creator_email || ''}}</td>
            <td class="actions">
                <a data-route="admin/admin-edit" data-params="id={{=item.id}}"
                   class="edit-link btn btn-primary btn-sm" href="javascript:void(0)">
                    <?php echo \Dictionary\translate('Edit'); ?>
                </a>
                {{? item.is_active == 1 }}
                    <a data-api-action="update-admin" data-params="id={{=item.id}}&is_active=0" data-method="post"
                       class="deactivate-link btn btn-danger btn-sm" href="javascript:void(0)">
                        <?php echo \Dictionary\translate('Deactivate'); ?>
                    </a>
                {{?}}
                {{? item.is_active == 0 }}
                    <a data-api-action="update-admin" data-params="id={{=item.id}}&is_active=1" data-method="post"
                       class="deactivate-link btn btn-success btn-sm" href="javascript:void(0)">
                        <?php echo \Dictionary\translate('Activate'); ?>
                    </a>
                {{?}}
            </td>
        </tr>
        {{~}}
    </tbody>
</table>