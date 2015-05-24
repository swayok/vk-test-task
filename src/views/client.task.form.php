<form id="client-task-form" class="task-form" method="post" onsubmit="return false;"
      data-api-action="{{? it.editMode }}update-task{{?}}{{? !it.editMode }}add-task{{?}}"
      data-after-save-go-to="{{= it.backUrl }}">
    {{? it.editMode }}
        <h1><?php echo \Dictionary\translate('Task editing'); ?></h1>
    {{?}}
    {{? !it.editMode }}
        <h1><?php echo \Dictionary\translate('Task creation'); ?></h1>
    {{?}}
    <div class="inputs">
        {{? it.editMode }}
            <input name="id" type="hidden" value="{{=it.item.id}}" id="task-id">
        {{?}}
        <div class="form-group">
            <label for="task-title"><?php echo \Dictionary\translate('Title'); ?></label>
            <input name="title" type="text" value="{{=it.item.title || ''}}" required autofocus
                   id="task-title" class="form-control">
        </div>
         <div class="form-group">
            <label for="task-description"><?php echo \Dictionary\translate('Description'); ?></label>
            <textarea name="description" required
                      id="task-description" class="form-control">{{=it.item.description || ''}}</textarea>
        </div>
        <div class="form-group">
            <label for="task-payment"><?php echo \Dictionary\translate('Payment'); ?></label>
            <div class="input-group">
                <input name="payment" type="text" value="{{=it.item.payment || '<?php echo MIN_TASK_PAYMENT; ?>'}}" required
                       id="task-payment" class="form-control">
                <span class="input-group-addon" id="email-addon"><?php echo \Dictionary\translate('RUB'); ?></span>
            </div>
            <div class="input-comment">
                <?php echo str_ireplace(':value', MIN_TASK_PAYMENT, \Dictionary\translate('Minimal payment is :value RUB')); ?>
            </div>
        </div>
        <div class="checkbox">
            <input name="is_active" type="hidden" id="_task-is-active" value="0">
            <label for="task-is-active">
                <input name="is_active" type="checkbox" value="1"
                       {{? !it.editMode || it.item.is_active == 1 }}checked{{?}}
                       id="task-is-active">
                <?php echo \Dictionary\translate('Can be executed'); ?>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">
            {{? it.editMode }}
                <?php echo \Dictionary\translate('Save updates'); ?>
            {{?}}
            {{? !it.editMode }}
                <?php echo \Dictionary\translate('Create task'); ?>
            {{?}}
        </button>
        <a href="{{= it.backUrl }}" class="btn btn-danger">
            <?php echo \Dictionary\translate('Cancel'); ?>
        </a>
    </div>
</form>