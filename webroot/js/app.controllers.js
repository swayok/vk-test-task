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
        if (App.isNotAuthorisationFailure(xhr)) {
            AppComponents.setErrorMessageFromXhr(xhr);
        }
    }).always(function () {
        App.isLoading(false);
    });
};

AppController.adminDashboard = function (template, isFromCache) {
    AppComponents.displayNavigationMenu('admin');
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
    App.isLoading(true);
    AppComponents.displayNavigationMenu('admin');
    AppController.dataGridTemplate = dataGridTemplate;
    AppController.dataGridTableContainer = $('<div class="data-grid-table-container"></div>');
    AppController.dataGridPaginationContainer = $('<div class="data-grid-pagination-container"></div>');
    App.container.html(AppController.dataGridTableContainer).append(AppController.dataGridPaginationContainer);
    // load data
    AppController.dataGridPaginationInfo = {
        total: 0,
        pages: 1,
        items_per_page: 25,
        page: App.currentUrlArgs.page > 1 ? App.currentUrlArgs.page - 0 : 1
    };
    var paginationInfoAjaxOptions = {
        url: App.getApiUrl(role + 's-list-info'),
        method: 'GET',
        dataType: 'json',
        cache: false
    };
    $.when(
        AppController.adminLoadUsers(role, true),
        App.getUser(),
        $.ajax(paginationInfoAjaxOptions),
        AppComponents.getPaginationTemplate()
    ).done(function (itemsResponse, admin, paginationInfoResponse, paginationTemplate) {
        AppController.dataGridTableContainer.html(AppController.dataGridTemplate({items: itemsResponse[0]}));
        paginationInfoResponse[0].page = AppController.dataGridPaginationInfo.page;
        AppController.dataGridPaginationInfo = paginationInfoResponse[0];
        AppController.dataGridPaginationContainer.html(paginationTemplate(AppController.dataGridPaginationInfo));
    }).fail(function (xhr) {
        if (App.isNotAuthorisationFailure(xhr)) {
            App.setRoute(App.userInfo && App.userInfo._route ? App.userInfo._route : 'login');
            AppComponents.setErrorMessageFromXhr(xhr);
        }
    }).always(function () {
        App.isLoading(false);
    });
    AppController.adminDataGridApplyEventHandlers(role);
};

AppController.adminDataGridApplyEventHandlers = function (role) {
    // pagination
    AppController.dataGridPaginationContainer.on('click', 'a.next-page, a.prev-page', function () {
        var newPageNum = AppController.dataGridPaginationInfo.page + ($(this).hasClass('next-page') ? 1 : -1);
        if (
            !$(this).parent().hasClass('disabled')
            && newPageNum > 0
            && newPageNum <= AppController.dataGridPaginationInfo.pages
        ) {
            var justReloadData = AppController.dataGridPaginationInfo.page === newPageNum;
            AppController.dataGridPaginationInfo.page = newPageNum;
            var html = AppComponents.getPaginationTemplate(AppController.dataGridPaginationInfo);
            AppController.dataGridPaginationContainer.html(html);
            AppController.adminLoadUsers(role, false, justReloadData);
        }
        return false;
    });
    // actions
    AppController.dataGridTableContainer.on('click', 'td.actions a', function () {
        var $el = $(this);
        var route = $el.attr('data-route');
        var queryArgs = $el.attr('data-args') || '';
        if ($el.attr('data-route')) {
            var urlArgs = Utils.parseUrlQuery(queryArgs);
            urlArgs.back_url = App.getRouteUrl();
            App.setRoute($el.attr('data-route'), urlArgs);
        } else if ($el.attr('data-api-action')) {
            var method = $el.attr('data-method') || 'GET';
            AppController.dataGridTableContainer.addClass('loading');
            $.ajax({
                url: App.getApiUrl($el.attr('data-api-action')),
                data: queryArgs,
                method: method,
                dataType: 'json',
                cache: false
            }).done(function (json) {
                if (json._message) {
                    AppComponents.setMessage(json._message, 'success');
                }
                AppController.adminLoadUsers(role, false, true);
            }).fail(function () {
                if (App.isNotAuthorisationFailure(xhr)) {
                    App.setErrorMessageFromXhr(xhr);
                }
            }).always(function () {
                AppController.dataGridTableContainer.removeClass('loading');
            });
        } else {
            AppComponents.setMessage('Invalid action', 'danger');
        }
    })
};

AppController.adminLoadUsers = function (role, returnDeferred, justReloadData) {
    var request = $.ajax({
        url: App.getApiUrl(role + 's-list') + '&page=' + AppController.dataGridPaginationInfo.page,
        method: 'GET',
        dataType: 'json',
        cache: false
    });
    if (returnDeferred) {
        return request;
    }
    if (!justReloadData) {
        AppController.adminDataGridChangeUrl();
    }
    AppController.dataGridTableContainer.addClass('loading');
    request.done(function (items) {
        AppController.dataGridTableContainer.html(AppController.dataGridTemplate({items: items}));
    }).fail(function (xhr) {
        if (App.isNotAuthorisationFailure(xhr)) {
            AppComponents.setErrorMessageFromXhr(xhr);
        }
    }).always(function () {
        setTimeout(function () {
            AppController.dataGridTableContainer.removeClass('loading')
        }, App.animationsDurationMs);
    })
};

AppController.adminDataGridChangeUrl = function () {
    App.currentUrlArgs.page = AppController.dataGridPaginationInfo.page;
    App._changeBrowserUrl();
};

AppController.adminUserForm = function (template, role, editMode, isFromCache) {
    AppComponents.displayNavigationMenu('admin');
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
            App.setRoute(backUrlArgs.route, backUrlArgs);
            AppComponents.setMessage('Item ID not found in URL arguments');
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
            if (App.isNotAuthorisationFailure(xhr)) {
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
    AppComponents.displayNavigationMenu(role);
    App.isLoading(true);
    $.when(App.getUser(true))
        .done(function (item) {
            App.container.html(template({item: item}));
            AppComponents.initForm();
        }).always(function () {
            App.isLoading(false);
        });
};