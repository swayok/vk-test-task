var App = {
    container: '#page-content',
    apiUrl: '/api.php?action=',
    urlArgs: {},
    views: {
        login: '/views/login.form.php'
    },
    apiActions: {
        status: 'status'
    },
    currentView: 'login'
};

App.init = function (urlArgs) {
    if (!!urlArgs && $.isPlainObject(urlArgs)) {
        App.urlArgs = urlArgs;
    }

    $(document).ready(function () {
        App.container = $(App.container);
        App.isLoggedIn();
    });
};

App.isLoggedIn = function () {
    return $.ajax({
        url: App.apiUrl + App.apiActions.status,
        cache: false,
        dataType: 'json'
    }).done(function (json) {
        if (!!App.urlArgs.view && !!App.views[App.urlArgs.view]) {
            App.currentView = App.urlArgs.view;
        } else if (!!json.view && !!App.views[json.view]) {
            App.currentView = json.view;
        } else {
            App.currentView = App.views.login;
        }
        App.loadCurrentView()
    }).fail(function (xhr) {
        App.currentView = App.views.login;
        App.loadCurrentView();
    });
};

App.loadCurrentView = function () {
    $.ajax({
        url: App.currentView,
        cache: true
    }).done(function (html) {
        App.container.html(html);
    });
};