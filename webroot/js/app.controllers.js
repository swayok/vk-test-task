var AppController = {
    userInfo: null,
    dataGridTableContainer: null,
    dataGridPaginationContainer: null,
    dataGridPaginationInfo: {},
    dataGridTemplate: null
};

AppController.loginForm = function (element, isFromCache) {
    App.container.html('').append(element);
    App.setUser(null);
    App.isLoading(false);
    App.container.find('form')[0].reset();
    AppComponents.initForm(function (json, form) {
        App.setUser(json);
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
        if (App.isNotAuthorisationFailure(xhr) && App.isNotInternalServerError(xhr)) {
            AppComponents.setErrorMessageFromXhr(xhr);
        }
    }).always(function () {
        App.isLoading(false);
    });
};

AppController.adminDashboard = function (template, isFromCache) {
    AppComponents.displayNavigationMenu();
    // todo: request dasboard data from api
    App.isLoading(true);
    $.when(App.getUser()).done(function (admin) {
        var html = template({admin: admin});
        App.container.html(html);
    }).always(function () {
        App.isLoading(false);
    });
};

AppController.adminUsersDataGrid = function (dataGridTemplate, role, isFromCache) {
    AppComponents.initDataGrid(dataGridTemplate, role + 's-list', role + 's-list-info');
};

AppController.adminUserForm = function (template, role, editMode, isFromCache) {
    AppComponents.displayNavigationMenu();
    var backUrl = App.currentUrlArgs.back_url || null;
    var backUrlArgs;
    if (backUrl) {
        backUrlArgs = Utils.parseUrlQuery(App.currentUrlArgs.back_url);
        if (!backUrlArgs.route) {
            backUrl = null;
        }
    }
    if (!backUrl) {
        backUrlArgs = {route: 'admin-' + role + 's-list'};
        backUrl = App.getRouteUrl(backUrlArgs.route);
    }

    if (editMode) {
        if (!App.currentUrlArgs.id) {
            AppComponents.setMessageAfterRouteChange('Item ID not found in URL arguments', 'danger');
            App.setRoute(backUrlArgs.route, backUrlArgs);
            return;
        }
        App.isLoading(true);
        $.ajax({
            url: App.getApiUrl('get-' + role) + App.currentUrlArgs.id,
            method: 'GET',
            dataType: 'json'
        }).done(function (item) {
            App.container.html(template({editMode: editMode, item: item, backUrl: backUrl}));
            AppComponents.initForm();
        }).fail(function (xhr) {
            if (App.isNotAuthorisationFailure(xhr) && App.isNotInternalServerError(xhr)) {
                App.setRoute(backUrlArgs.route, backUrlArgs);
                AppComponents.setErrorMessageFromXhr(xhr);
            }
        }).always(function () {
            App.isLoading(false);
        });
    } else {
        App.container.html(template({editMode: editMode, item: {}, backUrl: backUrl}));
        AppComponents.initForm();
        App.isLoading(false);
    }
};

AppController.profileForm = function (template, role, isFromCache) {
    AppComponents.displayNavigationMenu();
    App.isLoading(true);
    $.when(App.getUser(true))
        .done(function (item) {
            App.container.html(template({item: item}));
            AppComponents.initForm();
        }).always(function () {
            App.isLoading(false);
        });
};

AppController.clientTasksDataGrid = function (dataGridTemplate, isFromCache) {
    AppComponents.initDataGrid(dataGridTemplate, 'client-tasks-list', 'client-tasks-list-info');
};

AppController.clientTaskForm = function (template, editMode, isFromCache) {
    var backUrl = App.currentUrlArgs.back_url || null;
    var backUrlArgs;
    if (backUrl) {
        backUrlArgs = Utils.parseUrlQuery(App.currentUrlArgs.back_url);
        if (!backUrlArgs.route) {
            backUrl = null;
        }
    }
    if (!backUrl) {
        backUrlArgs = {route: 'client-tasks-list'};
        backUrl = App.getRouteUrl(backUrlArgs.route);
    }

    if (editMode) {
        if (!App.currentUrlArgs.id) {
            AppComponents.setMessageAfterRouteChange('Item ID not found in URL arguments', 'danger');
            App.setRoute(backUrlArgs.route, backUrlArgs);
            return;
        }
        App.isLoading(true);
        $.ajax({
            url: App.getApiUrl('get-task') + App.currentUrlArgs.id,
            method: 'GET',
            dataType: 'json'
        }).done(function (item) {
            App.container.html(template({editMode: editMode, item: item, backUrl: backUrl}));
            AppComponents.initForm();
        }).fail(function (xhr) {
            if (App.isNotAuthorisationFailure(xhr) && App.isNotInternalServerError(xhr)) {
                App.setRoute(backUrlArgs.route, backUrlArgs);
                AppComponents.setErrorMessageFromXhr(xhr);
            }
        }).always(function () {
            App.isLoading(false);
        });
    } else {
        App.container.html(template({editMode: editMode, item: {}, backUrl: backUrl}));
        AppComponents.initForm();
        App.isLoading(false);
    }
};

AppController.executorPendingTasksDataGrid = function (dataGridTemplate, isFromCache) {
    AppComponents.initDataGrid(dataGridTemplate, 'pending-tasks-list', 'pending-tasks-list-info');
    AppComponents.dataGrid.tableContainer.on('dataGridApiActionComplete:execute-task', function (event, data) {
        App.userInfo.balance = data.data.balance;
        AppComponents.displayNavigationMenu(true);
    });
};

AppController.executorExecutedTasksDataGrid = function (dataGridTemplate, isFromCache) {
    AppComponents.initDataGrid(dataGridTemplate, 'executed-tasks-list', 'executed-tasks-list-info');
};