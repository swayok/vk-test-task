var AppComponents = {
    messagesContainer: '#page-messages',
    navigationMenus: {
        viewsUrls: {},
        currentSection: null,
        templates: {},
        container: '#page-navigation'
    },
    pagination: {
        viewUrl: null,
        template: null
    },
    dataGrid: {
        tableContainer: null,
        paginationContainer: null,
        paginationInfo: {},
        template: null
    }
};

AppComponents.init = function () {
    AppComponents.navigationMenus.container = $('<div id="page-navigation"></div>');
    App.container.before(AppComponents.navigationMenus.container);

    AppComponents.messagesContainer = $('<div id="page-messages" title="click to hide"></div>');
    App.container.before(AppComponents.messagesContainer);
    AppComponents.messagesContainer.on('click', function () {
        AppComponents.hideMessage();
        return false;
    })
};

AppComponents.setMessage = function (message, type) {
    $.when(AppComponents.hideMessage()).done(function () {
        if ($.isPlainObject(message)) {
            type = message.type || type;
            message = message.message;
        }
        if (!message) {
            return;
        }
        message = $('<div>').append(message);
        AppComponents.messagesContainer.html('').append(message);
        if (!type) {
            type = 'info';
        } else {
            type = type.toLowerCase();
            if ($.inArray(type, ['success', 'info', 'warning', 'danger']) < 0) {
                type = 'info';
            }
        }
        message.attr('class', 'container bg-' + type);
        message.fadeIn(App.animationsDurationMs);
    });
};

AppComponents.setErrorMessageFromXhr = function (xhr, isNotJson) {
    if (!isNotJson) {
        try {
            var json = JSON.parse(xhr.responseText);
            if (json && json._message) {
                AppComponents.setMessage(json._message, 'danger');
            }
            return;
        } catch (exc) {}
    }
    AppComponents.setMessage('Error loading data. HTTP code: ' + xhr.status + ' ' + xhr.statusText, 'danger');
};

AppComponents.hideMessage = function () {
    return AppComponents.messagesContainer.find('.container').fadeOut(App.animationsDurationMs);
};

AppComponents.displayNavigationMenu = function (rerender) {
    var section = App.currentSection;
    if (section && AppComponents.navigationMenus.viewsUrls[section]) {
        if (AppComponents.navigationMenus.templates[section]) {
            if (AppComponents.navigationMenus.templates[section] === true) {
                // already loading a template
            } else if (section !== AppComponents.navigationMenus.currentSection || rerender) {
                var html = AppComponents.navigationMenus.templates[section]({user: App.getUser()});
                AppComponents.navigationMenus.container.html(html);
                AppComponents.activateNavigationMenuButton(App.currentRoute);
                AppComponents.navigationMenus.currentSection = section;
            }
            return true;
        } else {
            AppComponents.navigationMenus.templates[section] = true;
            $.ajax({
                url: AppComponents.navigationMenus.viewsUrls[section],
                cache: true
            }).done(function (html) {
                AppComponents.navigationMenus.templates[section] = doT.template(html);
                var navHtml = AppComponents.navigationMenus.templates[section]({user: App.getUser()});
                AppComponents.navigationMenus.container.html(navHtml);
                AppComponents.activateNavigationMenuButton(App.currentRoute);
                AppComponents.navigationMenus.currentSection = section;
            }).fail(App.handleAjaxFail);
            return true;
        }
    } else {
        AppComponents.navigationMenus.currentSection = null;
        AppComponents.navigationMenus.container.html('');
    }
    return false;
};

AppComponents.activateNavigationMenuButton = function (route) {
    AppComponents.navigationMenus.container.find('li.active').removeClass('active').end()
        .find('li a[href*="?route=' + route + '"]').closest('li').addClass('active');
};

AppComponents.getPaginationTemplate = function (data) {
    if (!AppComponents.pagination.template) {
        var deferred = $.Deferred();
        $.ajax({
            url: AppComponents.pagination.viewUrl,
            cache: true
        }).done(function (html) {
            AppComponents.pagination.template = doT.template(html);
            if (data) {
                deferred.resolve(AppComponents.pagination.template(data));
            } else {
                deferred.resolve(AppComponents.pagination.template);
            }
        }).fail(function (xhr) {
            deferred.reject(xhr);
        });
        return deferred;
    } else {
        return data ? AppComponents.pagination.template(data) : AppComponents.pagination.template;
    }
};

AppComponents.initForm = function (onSuccessCallback) {
    var form = App.container.find('form[data-api-action]').addClass('has-loader no-delay');
    form.on('submit', function (event) {
        AppComponents.removeFormValidationErrors(form, true);
        form.addClass('loading');
        var data = AppComponents.collectFormData(form);
        $.ajax({
            url: App.getApiUrl(form.attr('data-api-action')),
            method: 'POST',
            data: data,
            dataType: 'json'
        }).done(function (json) {
            var proceed = true;
            if (typeof onSuccessCallback === 'function') {
                proceed = onSuccessCallback(json, form);
                if (typeof proceed === 'undefined') {
                    proceed = true;
                }
            }
            if (proceed) {
                if (json._message) {
                    App.setMessageAfterRouteChange(json._message, 'success');
                }
                if (form.attr('data-after-save-go-to')) {
                    var urlArgs = Utils.parseUrlQuery(form.attr('data-after-save-go-to'));
                    if (urlArgs.route) {
                        App.setRoute(urlArgs.route, urlArgs);
                        return;
                    }
                }
                if (json._route) {
                    App.setRoute(json._route);
                }
            }
        }).fail(function (xhr) {
            if (App.isNotAuthorisationFailure(xhr) && App.isNotInternalServerError(xhr)) {
                AppComponents.applyFormValidationErrors(form, xhr);
            }
        }).always(function () {
            setTimeout(function () {
                form.removeClass('loading');
            }, 200);
        });
        return false;
    });
};


AppComponents.collectFormData = function (form) {
    var dataObject = {};
    var dataArray = form.serializeArray();
    $.each(dataArray, function() {
        if (dataObject[this.name] !== undefined && this.name.match(/\[.*?\]$/)) {
            if (!dataObject[this.name].push) {
                dataObject[this.name] = [dataObject[this.name]];
            }
            dataObject[this.name].push(this.value || '');
        } else {
            dataObject[this.name] = this.value || '';
        }
    });
    return dataObject;
};

AppComponents.removeFormValidationErrors = function (form, hideMessage) {
    if (!!hideMessage) {
        AppComponents.hideMessage();
    }
    form.find('.has-error').removeClass('has-error');
    return form.find('.error-text').slideUp(App.animationsDurationMs, function () {
        $(this).html('');
    });
};

AppComponents.applyFormValidationErrors = function (form, xhr) {
    $.when(AppComponents.removeFormValidationErrors(form, true)).done(function () {
        try {
            var response = JSON.parse(xhr.responseText);
            if (response._message) {
                AppComponents.setMessage(response._message, 'danger');
            }
            if (response.errors && $.isPlainObject(response.errors)) {
                for (var inputName in response.errors) {
                    if (form[0][inputName]) {
                        var container = $(form[0][inputName]).closest('.form-group, .checkbox').addClass('has-error');
                        var errorEl = container.find('.error-text');
                        if (errorEl.length == 0) {
                            errorEl = $('<div class="error-text bg-danger"></div>').hide();
                            container.append(errorEl);
                        }
                        errorEl.html(response.errors[inputName]).slideDown(App.animationsDurationMs);
                    }
                }
            }
        } catch (exc) {
            AppComponents.setErrorMessageFromXhr(xhr, true);
        }
    });
};

AppComponents.initDataGrid = function (dataGridTemplate, dataSourceApiAction, paginationInfoApiAction) {
    AppComponents.displayNavigationMenu();
    App.isLoading(true);
    AppComponents.dataGrid.template = dataGridTemplate;
    AppComponents.dataGrid.tableContainer = $('<div class="data-grid-table-container has-loader"></div>');
    AppComponents.dataGrid.paginationContainer = $('<div class="data-grid-pagination-container"></div>');
    App.container.html(AppComponents.dataGrid.tableContainer).append(AppComponents.dataGrid.paginationContainer);
    // load data
    AppComponents.dataGrid.paginationInfo = {
        total: 0,
        pages: 1,
        items_per_page: 25,
        page: App.currentUrlArgs.page > 1 ? App.currentUrlArgs.page - 0 : 1
    };
    var paginationInfoAjaxOptions = {
        url: App.getApiUrl(paginationInfoApiAction),
        method: 'GET',
        dataType: 'json',
        cache: false
    };
    $.when(
        AppComponents.dataGridLoadRecords(dataSourceApiAction, true),
        $.ajax(paginationInfoAjaxOptions),
        AppComponents.getPaginationTemplate()
    ).done(function (itemsResponse, paginationInfoResponse, paginationTemplate) {
        AppComponents.dataGrid.tableContainer.html(AppComponents.dataGrid.template({items: itemsResponse[0]}));
        paginationInfoResponse[0].page = AppComponents.dataGrid.paginationInfo.page;
        AppComponents.dataGrid.paginationInfo = paginationInfoResponse[0];
        AppComponents.dataGrid.paginationContainer.html(paginationTemplate(AppComponents.dataGrid.paginationInfo));
    }).fail(function (xhr) {
        if (App.isNotAuthorisationFailure(xhr) && App.isNotInternalServerError(xhr)) {
            App.setRoute(App.userInfo && App.userInfo._route ? App.userInfo._route : 'login');
            AppComponents.setErrorMessageFromXhr(xhr);
        }
    }).always(function () {
        App.isLoading(false);
    });
    AppComponents.dataGridApplyEventHandlers(dataSourceApiAction);
};

AppComponents.dataGridApplyEventHandlers = function (dataSourceApiAction) {
    // pagination
    AppComponents.dataGrid.paginationContainer.on('click', 'a.next-page, a.prev-page', function () {
        var newPageNum = AppComponents.dataGrid.paginationInfo.page + ($(this).hasClass('next-page') ? 1 : -1);
        if (
            !$(this).parent().hasClass('disabled')
            && newPageNum > 0
            && newPageNum <= AppComponents.dataGrid.paginationInfo.pages
        ) {
            var justReloadData = AppComponents.dataGrid.paginationInfo.page === newPageNum;
            AppComponents.dataGrid.paginationInfo.page = newPageNum;
            var html = AppComponents.getPaginationTemplate(AppComponents.dataGrid.paginationInfo);
            AppComponents.dataGrid.paginationContainer.html(html);
            AppComponents.dataGridLoadRecords(dataSourceApiAction, false, justReloadData);
        }
        return false;
    });
    // actions
    AppComponents.dataGrid.tableContainer.on('click', 'td.actions a', function () {
        var $el = $(this);
        var route = $el.attr('data-route');
        var queryArgs = $el.attr('data-args') || '';
        if ($el.attr('data-route')) {
            var urlArgs = Utils.parseUrlQuery(queryArgs);
            urlArgs.back_url = App.getRouteUrl();
            App.setRoute($el.attr('data-route'), urlArgs);
        } else if ($el.attr('data-api-action')) {
            var method = $el.attr('data-method') || 'GET';
            AppComponents.dataGrid.tableContainer.addClass('loading');
            var apiAction = $el.attr('data-api-action');
            $.ajax({
                url: App.getApiUrl(apiAction),
                data: queryArgs,
                method: method,
                dataType: 'json',
                cache: false
            }).done(function (json) {
                if (json._message) {
                    AppComponents.setMessage(json._message, 'success');
                }
                AppComponents.dataGridLoadRecords(dataSourceApiAction, false, true);
                AppComponents.dataGrid.tableContainer.trigger(
                    'dataGridApiActionComplete:' + apiAction,
                    {element: $el, data: json, queryArgs: queryArgs}
                );
            }).fail(
                App.handleAjaxFail
            ).always(function () {
                AppComponents.dataGrid.tableContainer.removeClass('loading');
            });
        } else {
            AppComponents.setMessage('Invalid action', 'danger');
        }
    })
};

AppComponents.dataGridLoadRecords = function (dataSourceApiAction, returnDeferred, justReloadData) {
    var request = $.ajax({
        url: App.getApiUrl(dataSourceApiAction) + '&page=' + AppComponents.dataGrid.paginationInfo.page,
        method: 'GET',
        dataType: 'json',
        cache: false
    });
    if (returnDeferred) {
        return request;
    }
    if (!justReloadData) {
        App.currentUrlArgs.page = AppComponents.dataGrid.paginationInfo.page;
        App._changeBrowserUrl();
    }
    AppComponents.dataGrid.tableContainer.addClass('loading');
    request.done(function (items) {
        AppComponents.dataGrid.tableContainer.html(AppComponents.dataGrid.template({items: items}));
    }).fail(
        App.handleAjaxFail
    ).always(function () {
        setTimeout(function () {
            AppComponents.dataGrid.tableContainer.removeClass('loading');
        }, App.animationsDurationMs);
    })
};