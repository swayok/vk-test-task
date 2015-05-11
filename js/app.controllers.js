var AppController = {

};

AppController.loginForm = function (element, isFromCache) {
    App.container.html('').append(element);
    if (!isFromCache) {
        var form =App.container.find('form');
        form.on('submit', function (event) {
            App.hideMessage();
            App.removeFormValidationErrors(form);
            var data = App.collectFormData(form);
            $.ajax({
                url: App.getApiUrl('login'),
                method: 'POST',
                data: data,
                dataType: 'json'
            }).done(function (json) {

            }).fail(function (xhr) {
                if (App.isNotAuthorisationFailure(xhr)) {
                    App.applyFormValidationErrors(form, xhr);
                }
            });
            return false;
        })
    }
};