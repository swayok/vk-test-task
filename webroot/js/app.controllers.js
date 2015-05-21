var AppController = {
    userInfo: null,
    dataGridTableContainer: null,
    dataGridPaginationContainer: null,
    dataGridPaginationInfo: {}
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
    AppController.dataGridTableContainer = $('<div class="data-grid-table-container"></div>');
    AppController.dataGridPaginationContainer = $('<div class="data-grid-pagination-container"></div>');
    App.container.html(AppController.dataGridTableContainer).append(AppController.dataGridPaginationContainer);
    // load data
    AppController.dataGridPaginationInfo = {
        total: 0,
        pages: 1,
        items_per_page: 25,
        page: App.urlArgs.page > 1 ? parseInt(App.urlArgs.page) : 1
    };
    var paginationInfoAjaxOptions = {
        url: App.getApiUrl(role + 's-list-info'),
        method: 'GET',
        dataType: 'json',
        cache: false
    };
    $.when(
        AppController.adminLoadUsers(role, dataGridTemplate, true),
        App.getUser(),
        $.ajax(paginationInfoAjaxOptions),
        AppComponents.getPaginationTemplate()
    ).done(function (itemsResponse, admin, paginationInfoResponse, paginationTemplate) {
        AppController.dataGridTableContainer.html(dataGridTemplate({items: itemsResponse[0]}));
        paginationInfoResponse[0].page = AppController.dataGridPaginationInfo.page;
        AppController.dataGridPaginationInfo = paginationInfoResponse[0];
        AppController.dataGridPaginationContainer.html(paginationTemplate(AppController.dataGridPaginationInfo));
    }).fail(function (xhr) {
        if (App.isNotAuthorisationFailure(xhr)) {
            App.setRoute(App.userInfo && App.userInfo.route ? App.userInfo.route : 'login');
            AppComponents.setMessage('Error loading data. HTTP code: ' + xhr.status + ' ' + xhr.statusText, 'danger');
        }
    }).always(function () {
        App.isLoading(false);
    });
    // apply events
    AppController.dataGridPaginationContainer.on('click', 'a.next-page, a.prev-page', function () {
        var newPageNum = AppController.dataGridPaginationInfo.page + ($(this).hasClass('next-page') ? 1 : -1);
        if (!$(this).parent().hasClass('disabled') && newPageNum > 0 && newPageNum <= AppController.dataGridPaginationInfo.pages) {
            AppController.dataGridPaginationInfo.page = newPageNum;
            var html = AppComponents.getPaginationTemplate(AppController.dataGridPaginationInfo);
            AppController.dataGridPaginationContainer.html(html);
            AppController.adminLoadUsers(role, dataGridTemplate);
        }
        return false;
    });

};

AppController.adminLoadUsers = function (role, dataGridTemplate, returnDeferred) {
    var request = $.ajax({
        url: App.getApiUrl(role + 's-list') + '&page=' + AppController.dataGridPaginationInfo.page,
        method: 'GET',
        dataType: 'json',
        cache: false
    });
    if (returnDeferred) {
        return request;
    }
    AppController.adminDataGridChangeUrl();
    AppController.dataGridTableContainer.addClass('loading');
    request.done(function (items) {
        AppController.dataGridTableContainer.html(dataGridTemplate({items: items}));
    }).fail(function (xhr) {
        if (App.isNotAuthorisationFailure(xhr)) {
            AppComponents.setMessage('Error loading data. HTTP code: ' + xhr.status + ' ' + xhr.statusText, 'danger');
        }
    }).always(function () {
        setTimeout(function () {
            AppController.dataGridTableContainer.removeClass('loading')
        }, App.animationsDurationMs);
    })
};

AppController.adminDataGridChangeUrl = function () {
    App.urlArgs.page = AppController.dataGridPaginationInfo.page;
    var newUrl = App.getRouteUrl();
    var data = {route: App.currentRoute, urlArgs: {page: AppController.dataGridPaginationInfo.page}};
    window.history.pushState(data, null, newUrl);
};