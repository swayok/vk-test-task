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
    }
};

AppComponents.init = function () {
    AppComponents.navigationMenus.container = $('<div id="page-navigation"></div>');
    App.container.before(AppComponents.navigationMenus.container);

    AppComponents.messagesContainer = $('<div id="page-messages" title="click to hide"></div>').hide();
    $(document.body).prepend(AppComponents.messagesContainer);
    AppComponents.messagesContainer.on('click', function () {
        AppComponents.messagesContainer.slideUp();
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
        message = $('<div class="container">').append(message);
        AppComponents.messagesContainer.html('').append(message);
        if (!type) {
            type = 'info';
        } else {
            type = type.toLowerCase();
            if ($.inArray(type, ['success', 'info', 'warning', 'danger']) < 0) {
                type = 'info';
            }
        }
        AppComponents.messagesContainer.attr('class', 'bg-' + type);
        AppComponents.messagesContainer.slideDown(App.animationsDurationMs);
    });
};

AppComponents.setErrorMessageFromXhr = function (xhr, isNotJson) {
    if (!isNotJson) {
        try {
            var json = JSON.parse(xhr.responseText);
            if (json && json.message) {
                AppComponents.setMessage(json.message, 'danger');
            }
            return;
        } catch (exc) {}
    }
    //AppComponents.setMessage(xhr.responseText, 'danger');
    AppComponents.setMessage('Error loading data. HTTP code: ' + xhr.status + ' ' + xhr.statusText, 'danger');
};

AppComponents.hideMessage = function () {
    return AppComponents.messagesContainer.slideUp(App.animationsDurationMs);
};

AppComponents.displayNavigationMenu = function (section, rerender) {
    if (AppComponents.navigationMenus.viewsUrls[section]) {
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
            }).fail(function (xhr) {
                if (App.isNotAuthorisationFailure(xhr)) {
                    AppComponents.setErrorMessageFromXhr(xhr);
                }
            });
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
    var form = App.container.find('form[data-api-action]');
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
            if (App.isNotAuthorisationFailure(xhr)) {
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
            if (response.message) {
                AppComponents.setMessage(response.message, 'danger');
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