<form id="admin-admin-form" class="user-form" onsubmit="return false;" autocomplete="off"
      data-api-action="update-profile">
    <h1><?php echo \Dictionary\translate('Profile editing'); ?></h1>
    <div class="inputs">
        <input name="id" type="hidden" value="{{=it.item.id}}" id="user-id" autocomplete="off">
        <input name="role" type="hidden" value="{{=it.item.role}}" id="user-role" autocomplete="off">
        <!-- make chrome autofill burn in hell -->
        <input style="display:none;" type="text" />
        <input style="display:none;" type="password" />
        <!-- chrome autofill burns in hell -->
        <div class="form-group">
            <label for="user-email"><?php echo \Dictionary\translate('E-mail'); ?></label>
            <div class="input-group">
                <input name="email" type="email" value="{{=it.item.email}}"
                       required autofocus autocomplete="off" disabled
                       id="user-email" class="form-control" aria-describedby="email-addon">
                <span class="input-group-addon" id="email-addon">@</span>
            </div>
        </div>
        <div class="form-group">
            <label for="user-password"><?php echo \Dictionary\translate('Password'); ?></label>
            <input name="password" type="password" autocomplete="off"
                   id="user-password" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">
            <?php echo \Dictionary\translate('Save updates'); ?>
        </button>
    </div>
</form>