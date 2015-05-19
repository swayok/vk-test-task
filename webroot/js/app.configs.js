var AppConfigs = {};

AppConfigs.configureApp = function () {

    App.baseUrl = '/';
    App.viewsUrl = '/get_view.php?view=';
    App.apiUrl = '/api.php?action=';

    App.navigationMenus.viewsUrls = {
        admin: App.viewsUrl +'admin.header',
        client: App.viewsUrl +'client.header',
        executor: App.viewsUrl +'executor.header'
    };

    App.apiActions = {
        status: 'status',
        login: 'login',
        logout: 'logout',
        'clients-list': 'clients',
        'executors-list': 'executors',
        'admins-list': 'admins'
    };

    App.routes = {
        login: {
            section: 'login',
            url: App.viewsUrl + 'login.form',
            compileTemplate: false,
            controller: AppController.loginForm,
            cache: true,
            canBeReloaded: false
        },
        logout: {
            controller: AppController.logout,
            canBeReloaded: true
        },
        'admin-dashboard': {
            section: 'admin',
            url: App.viewsUrl + 'admin.dashboard',
            compileTemplate: true,
            controller: AppController.adminDashboard,
            cache: true,
            canBeReloaded: true
        },
        'admin-clients-list': {
            section: 'admin',
            url: App.viewsUrl + 'clients.list',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUsersDataGrid(template, 'client', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-executors-list': {
            section: 'admin',
            url: App.viewsUrl + 'executors.list',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUsersDataGrid(template, 'executor', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-admins-list': {
            section: 'admin',
            url: App.viewsUrl + 'admins.list',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUsersDataGrid(template, 'admin', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        }
    };


};