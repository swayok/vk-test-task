<h1><?php echo \Dictionary\translate('Admins'); ?></h1>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>E-mail</th>
            <th>Active</th>
            <th>Created at</th>
            <th>Creator</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {{~it.items :item}}
        <tr>
            <td>{{=item.id}}</td>
            <td>{{=item.email}}</td>
            <td>{{=item.is_active}}</td>
            <td>{{=item.created_at}}</td>
            <td>{{=item.creator_email || ''}}</td>
            <td>
                <a href="javascript:void(0)" class="deactivate-link" data-id="{{=item.id}}">Deactivate</a>
                <a href="/?route=admin-admin-edit&id={{=item.id}}" class="edit-link">Edit</a>
            </td>
        </tr>
        {{~}}
    </tbody>
</table>