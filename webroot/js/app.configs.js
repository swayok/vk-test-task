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
        'update-admin': 'update-admin',

        'get-task': 'get-task&not_executed=1&id=',
        'add-task': 'add-task',
        'update-task': 'update-task',
        'client-tasks-list': 'client-tasks-list',
        'client-tasks-list-info': 'client-tasks-list-info',

        'pending-tasks-list': 'pending-tasks-list',
        'pending-tasks-list-info': 'pending-tasks-list-info',
        'executed-tasks-list': 'executed-tasks-list',
        'executed-tasks-list-info': 'executed-tasks-list-info',
        'execute-task': 'execute-task'
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
        },

        'client-tasks-list': {
            section: 'client',
            url: App.viewsUrl + 'client.tasks.list',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.clientTasksDataGrid(template, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'client-task-add': {
            section: 'client',
            url: App.viewsUrl + 'client.task.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.clientTaskForm(template, false, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'client-task-edit': {
            section: 'client',
            url: App.viewsUrl + 'client.task.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.clientTaskForm(template, true, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'client-profile': {
            section: 'client',
            url: App.viewsUrl + 'client.profile.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.profileForm(template, 'client', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },

        'executor-pending-tasks-list': {
            section: 'executor',
            url: App.viewsUrl + 'executor.tasks.list',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.executorPendingTasksDataGrid(template, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'executor-executed-tasks-list': {
            section: 'executor',
            url: App.viewsUrl + 'executor.tasks.list&executed_tasks=1',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.executorExecutedTasksDataGrid(template, isFromCache);
            },
            cache: true,
            canBeReloaded: true
        },
        'executor-profile': {
            section: 'executor',
            url: App.viewsUrl + 'executor.profile.form',
            compileTemplate: true,
            controller: function (template, isFromCache) {
                AppController.profileForm(template, 'executor', isFromCache);
            },
            cache: true,
            canBeReloaded: true
        }

    };


};