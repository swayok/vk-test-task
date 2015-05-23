<form id="admin-admin-form" class="user-form" onsubmit="return false;" autocomplete="off"
      data-api-action="{{? it.editMode }}update-admin{{?}}{{? !it.editMode }}add-admin{{?}}"
      data-after-save-go-to="{{= it.backUrl }}">
    {{? it.editMode }}
        <h1><?php echo \Dictionary\translate('Admin account editing'); ?></h1>
    {{?}}
    {{? !it.editMode }}
        <h1><?php echo \Dictionary\translate('Admin account creation'); ?></h1>
    {{?}}
    <div class="inputs">
        {{? it.editMode }}
            <input name="id" type="hidden" value="{{=it.item.id}}" id="user-id" autocomplete="off">
        {{?}}
        <!-- make chrome autofill burn in hell -->
        <input style="display:none;" type="text" />
        <input style="display:none;" type="password" />
        <!-- chrome autofill burns in hell -->
        <div class="form-group">
            <label for="user-email"><?php echo \Dictionary\translate('E-mail'); ?></label>
            <div class="input-group">
                <input name="email" type="email" value="{{=it.item.email || ''}}"
                       required autofocus autocomplete="off" {{? it.editMode }}disabled{{?}}
                       id="user-email" class="form-control" aria-describedby="email-addon">
                <span class="input-group-addon" id="email-addon">@</span>
            </div>
        </div>
        <div class="form-group">
            <label for="user-password"><?php echo \Dictionary\translate('Password'); ?></label>
            <input name="password" type="password"
                   autocomplete="off" {{? !it.editMode }}required{{?}}
                   id="user-password" class="form-control">
        </div>
        <div class="checkbox">
            <input name="is_active" type="hidden" id="_user-is-active" value="0">
            <label for="user-is-active">
                <input name="is_active" type="checkbox" value="1" {{? it.item.is_active == 1 }}checked{{?}}
                       id="user-is-active" autocomplete="off">
                <?php echo \Dictionary\translate('Authorisation is allowed'); ?>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">
            {{? it.editMode }}
                <?php echo \Dictionary\translate('Save updates'); ?>
            {{?}}
            {{? !it.editMode }}
                <?php echo \Dictionary\translate('Create account'); ?>
            {{?}}
        </button>
        <a href="{{= it.backUrl }}" class="btn btn-danger">
            <?php echo \Dictionary\translate('Cancel'); ?>
        </a>
    </div>
</form>