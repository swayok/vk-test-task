var AppController = {
    userInfo: null
};

AppController.loginForm = function (element, isFromCache) {
    App.container.html('').append(element);
    App.setUser(null);
    App.isLoading(false);
    var form = App.container.find('form');
    form[0].reset();
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
    });
};

AppController.logout = function () {
    App.isLoading(true);
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
        App.isLoading(false);
    });
};

AppController.adminDashboard = function (template, isFromCache) {
    App.displayNavigationMenu('admin');
    // todo: request dasboard data from api
    App.isLoading(true);
    $.when(App.getUser()).done(function (admin) {
        var html = template({admin: admin});
        App.container.html('').append(html);
    }).always(function () {
        App.isLoading(false);
    });
};

AppController.adminUsersDataGrid = function (template, role, isFromCache) {
    App.displayNavigationMenu('admin');
    App.isLoading(true);
    var itemsAjax = $.ajax({
        url: App.getApiUrl(role + 's-list'),
        method: 'GET',
        dataType: 'json',
        cache: false
    });
    $.when(itemsAjax, App.getUser()).done(function (itemsResponse, admin) {
        var html = template({admin: admin, items: itemsResponse[0]});
        App.container.html('').append(html);
    }).fail(function (xhr) {
        if (App.isNotAuthorisationFailure(xhr)) {
            App.setRoute(App.userInfo && App.userInfo.route ? App.userInfo.route : 'login');
            App.setMessage('Error loading data. HTTP code: ' + xhr.status + ' ' + xhr.statusText, 'danger');
        }
    }).always(function () {
        App.isLoading(false);
    });
};