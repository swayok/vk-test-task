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
            App.setRoute(App.userInfo && App.userInfo.route ? App.userInfo.route : 'login');
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
        var params = $el.attr('data-params') || '';
        var method = $el.attr('data-method') || 'GET';
        if ($el.attr('data-route')) {

        } else if ($el.attr('data-api-action')) {
            AppController.dataGridTableContainer.addClass('loading');
            $.ajax({
                url: App.getApiUrl($el.attr('data-api-action')),
                data: params,
                method: method,
                dataType: 'json',
                cache: false
            }).done(function (json) {
                if (json.message) {
                    AppComponents.setMessage(json.message, 'success');
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