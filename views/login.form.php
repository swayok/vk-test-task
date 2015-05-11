<?php require_once __DIR__ . '/../src/configs/bootstrap.view.php'; ?>
<form id="login-form" onsubmit="return false;">
    <h1><?php echo \Dictionary\translate('Authorisation'); ?></h1>
    <div class="inputs">
        <div class="form-group">
            <label for="user-role"><?php echo \Dictionary\translate('Role'); ?></label>
            <select name="role" id="user-role" class="form-control" required>
                <option value="client"><?php echo \Dictionary\translate('Client'); ?></option>
                <option value="executor"><?php echo \Dictionary\translate('Executor'); ?></option>
                <option value="admin"><?php echo \Dictionary\translate('Admin'); ?></option>
            </select>
        </div>
        <div class="form-group">
            <label for="user-email"><?php echo \Dictionary\translate('Email'); ?></label>
            <input name="email" type="email" id="user-email" required autofocus class="form-control">
        </div>
        <div class="form-group">
            <label for="user-password"><?php echo \Dictionary\translate('Password'); ?></label>
            <input name="password" type="password" id="user-password" required class="form-control">
        </div>
        <button type="submit" class="btn btn-lg btn-primary btn-block">Sign in</button>
    </div>
</form>