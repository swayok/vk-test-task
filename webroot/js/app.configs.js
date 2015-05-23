var AppConfigs = {};

AppConfigs.configureApp = function () {

    App.viewsUrl = '/get_view.php?view=';
    App.apiUrl = '/api.php?action=';

    AppComponents.navigationMenus.viewsUrls = {
        admin: App.viewsUrl +'admin.header',
        client: App.viewsUrl +'client.header',
        executor: App.viewsUrl +'executor.header'
    };

    AppComponents.pagination.viewUrl = App.viewsUrl +'pagination';

    App.apiActions = {
        status: 'status',
        login: 'login',
        logout: 'logout',
        'update-profile': 'update-profile',

        'clients-list': 'clients-list',
        'clients-list-info': 'clients-list-info',
        'get-client': 'get-client&id=',
        'add-client': 'add-client',
        'update-client': 'update-client',

        'executors-list': 'executors-list',
        'executors-list-info': 'executors-list-info',
        'get-executor': 'get-executor&id=',
        'add-executor': 'add-executor',
        'update-executor': 'update-executor',

        'admins-list': 'admins-list',
        'admins-list-info': 'admins-list-info',
        'get-admin': 'get-admin&id=',
        'add-admin': 'add-admin',
        'update-admin': 'update-admin'
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
            url: App.viewsUrl + 'admin.clients.list',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUsersDataGrid(template, 'client', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-client-add': {
            section: 'admin',
            url: App.viewsUrl + 'admin.client.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUserForm(template, 'client', false, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-client-edit': {
            section: 'admin',
            url: App.viewsUrl + 'admin.client.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUserForm(template, 'client', true, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },

        'admin-executors-list': {
            section: 'admin',
            url: App.viewsUrl + 'admin.executors.list',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUsersDataGrid(template, 'executor', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-executor-add': {
            section: 'admin',
            url: App.viewsUrl + 'admin.executor.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUserForm(template, 'executor', false, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-executor-edit': {
            section: 'admin',
            url: App.viewsUrl + 'admin.executor.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUserForm(template, 'executor', true, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },

        'admin-admins-list': {
            section: 'admin',
            url: App.viewsUrl + 'admin.admins.list',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUsersDataGrid(template, 'admin', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-admin-add': {
            section: 'admin',
            url: App.viewsUrl + 'admin.admin.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUserForm(template, 'admin', false, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-admin-edit': {
            section: 'admin',
            url: App.viewsUrl + 'admin.admin.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.adminUserForm(template, 'admin', true, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'admin-profile': {
            section: 'admin',
            url: App.viewsUrl + 'admin.profile.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.profileForm(template, 'admin', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        }
    };


};