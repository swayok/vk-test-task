var AppController = {
    userInfo: null
};

AppController.loginForm = function (element, isFromCache) {
    App.container.html('').append(element);
    App.setUser(null);
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
                }, 200);
            });
            return false;
        })
    }
};

AppController.logout = function () {
    App.container.addClass('loading');
    $.ajax({
        url: App.getApiUrl('logout'),
        method: 'GET'
    }).done(function () {
        App.setRoute('login');
    }).fail(function (xhr) {
        if (App.isNotAuthorisationFailure(xhr)) {
            App.applyFormValidationErrors(form, xhr);
        }
    }).always(function () {
        setTimeout(function () {
            App.container.removeClass('loading');
        }, 200);
    });
};

AppController.adminDashboard = function (template, isFromCache) {
    // todo: request dasboard data from api
    var html = template({admin: App.getUser()});
    App.container.html('').append(html);
};