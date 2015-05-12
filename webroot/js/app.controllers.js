var AppController = {
    userInfo: null
};

AppController.loginForm = function (element, isFromCache) {
    App.container.html('').append(element);
    if (!isFromCache) {
        var form = App.container.find('form');
        form.on('submit', function (event) {
            App.removeFormValidationErrors(form, true);
            form.addClass('loading');
            var data = App.collectFormData(form);
            $.ajax({
                url: App.getApiUrl('login'),
                method: 'POST',
                data: data,
                dataType: 'json'
            }).done(function (json) {
                App.setUser(json);
                App.setRoute(json.route);
            }).fail(function (xhr) {
                if (App.isNotAuthorisationFailure(xhr)) {
                    App.applyFormValidationErrors(form, xhr);
                }
            }).always(function () {
                setTimeout(function () {
                    form.removeClass('loading');
                }, 500);
            });
            return false;
        })
    }
};

AppController.adminDashboard = function (template, isFromCache) {
    App.container.html('').append(template);
};