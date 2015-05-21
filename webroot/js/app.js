var App = {
    container: '#page-content',
    baseUrl: '/',
    viewsUrl: null,
    apiUrl: null,
    initialUrlArgs: {},
    routes: {},
    loadedRoutes: {},
    apiActions: {},
    currentRoute: null,
    currentUrlArgs: {},
    animationsDurationMs: 150,
    userInfo: null,
    baseBrowserTitle: ''
};

App.init = function (urlArgs) {
    if (!!urlArgs && $.isPlainObject(urlArgs)) {
        App.initialUrlArgs = urlArgs;
    }

    AppConfigs.configureApp();

    $(document).ready(function () {
        App.baseBrowserTitle = document.title;
        App.container = $(App.container);
        AppComponents.init();

        $(document.body).on('click', 'a[href^="/"]', function () {
            if (!$(this).attr('data-route')) {
                $(this).attr('data-route', App.extractRouteFromUrl(this.href));
            }
            App.setRoute($(this).attr('data-route'));
            return false;
        });

        window.addEventListener('popstate', function(event){
            if (event.state && event.state.route) {
                App.setRoute(event.state.route, event.state, true);
            }
        }, false);

        App.getUser();
    });
};

App.getRouteUrl = function (route, urlArgs) {
    if (!route) {
        route = App.currentRoute;
        if (!urlArgs || !$.isPlainObject(urlArgs)) {
            urlArgs = App.currentUrlArgs;
        }
    }
    if (!App.routes[route]) {
        var error = 'Unknown route [' + route + '] detected';
        AppComponents.setMessage(error, 'danger');
        return false;
    }
    if (!urlArgs || !$.isPlainObject(urlArgs)) {
        urlArgs = {};
    }
    urlArgs.route = route;
    return App.baseUrl + '?' + $.param(urlArgs);
};

App.extractRouteFromUrl = function (url) {
    var matches = url.match(/^.*?\?.*?route=([a-zA-Z\-_0-9]+)/i);
    return matches && matches[1] ? matches[1] : '__undefined__route__';
};

App.setUser = function (userInfo) {
    App.userInfo = userInfo;
    if (App.currentRoute && App.routes[App.currentRoute].section) {
        AppComponents.displayNavigationMenu(App.routes[App.currentRoute].section, true);
    }
};

App.getUser = function () {
    if (!$.isPlainObject(App.userInfo)) {
        return $.ajax({
            url: App.apiUrl + App.apiActions.status,
            cache: false,
            dataType: 'json'
        }).done(function (json) {
            if (!!App.initialUrlArgs.route && !!App.routes[App.initialUrlArgs.route] && App.initialUrlArgs.route !== 'login') {
                App.setRoute(App.initialUrlArgs.route, App.initialUrlArgs);
                App.initialUrlArgs = {};
            } else if (!!json.route && !!App.routes[json.route]) {
                App.setRoute(json.route);
            } else {
                App.setRoute('login');
                return;
            }
            App.setUser(json);
        }).fail(function (xhr) {
            App.setRoute('login');
        });
    } else {
        return App.userInfo;
    }
};

App.setCurrentRoute = function (route, urlArgs) {
    if (!urlArgs || !$.isPlainObject(urlArgs)) {
        urlArgs = {};
    }
    urlArgs.route = route;
    if (App.currentRoute !== route || !Utils.compareObjects(urlArgs, App.currentUrlArgs)) {
        if (App.currentRoute !== route) {
            App.container.find('a.active').removeClass('active').end()
                .find('a[href*="?route=' + route + '"]').addClass('active');
            AppComponents.activateNavigationMenuButton(route);
        }
        App.currentUrlArgs = urlArgs;
        App.currentRoute = route;
        return true;
    } else {
        return false;
    }
};

App._changeBrowserUrl = function () {
    window.history.pushState(App.currentUrlArgs, null, App.getRouteUrl());
};

App.setRoute = function (route, urlArgs, doNotChangeUrl) {
    AppComponents.hideMessage();
    if (!route || !App.routes[route]) {
        var error = 'Unknown route [' + route + '] detected';
        AppComponents.setMessage(error, 'danger');
        return false;
    }
    var routeInfo = App.routes[route];
    var isDifferentRoute = App.setCurrentRoute(route, urlArgs);
    if (isDifferentRoute || routeInfo.canBeReloaded) {
        if (!routeInfo.url) {
            routeInfo.controller();
        } else if (!App.loadedRoutes[route]) {
            App._loadViewForRoute(route, routeInfo)
        } else {
            document.title = App.loadedRoutes[route].browserTitle;
            routeInfo.controller(App.loadedRoutes[route].template, true);
        }
        if (!doNotChangeUrl && isDifferentRoute) {
            App._changeBrowserUrl();
        }
    }
};

App._loadViewForRoute = function (route, routeInfo) {
    App.isLoading(true);
    $.ajax({
        url: routeInfo.url,
        cache: true
    }).done(function (html) {
        // use <h1> text as browser title
        var browserTitle = App.baseBrowserTitle;
        var matches = html.match(/<h1[^>]*>([\s\S]+)<\/h1/i);
        if (matches && matches.length) {
            browserTitle = matches[1] + ' - ' + browserTitle;
        }
        document.title = browserTitle;

        var template = '<div class="content-wrapper" id="' + route + '-action-container">' + html + '</div>';
        if (!!routeInfo.compileTemplate) {
            template = doT.template(template);
        } else {
            template = $.parseHTML(template);
        }

        if (!!routeInfo.cache) {
            App.loadedRoutes[route] = {
                template: template,
                browserTitle: browserTitle
            };
        }

        routeInfo.controller(template, false);
    }).fail(function (xhr) {
        App.isLoading(false);
        if (!App.isAuthorisationFailure(xhr)) {
            AppComponents.setErrorMessageFromXhr();
        }
    });
};

App.isLoading = function (yes) {
    if (yes || typeof yes === 'undefined') {
        App.container.addClass('loading');
    } else {
        setTimeout(function () {
            App.container.removeClass('loading');
        }, 200);
    }
};

App.getApiUrl = function (action) {
    if (!App.apiActions[action]) {
        var error = 'Api action [' + action + '] not defined';
        AppComponents.setMessage(error, 'danger');
        throw error;
    }
    return App.apiUrl + action;
};

App.isValidationErrors = function (xhr) {
    return xhr.status === 400;
};

App.isAuthorisationFailure = function (xhr) {
    return xhr.status === 401;
};

App.isNotAuthorisationFailure = function (xhr) {
    if (App.isAuthorisationFailure(xhr)) {
        App.setRoute('login');
        AppComponents.setErrorMessageFromXhr(xhr);
        return false;
    }
    return true;
};

App.collectFormData = function (form) {
    var dataObject = {};
    var dataArray = form.serializeArray();
    $.each(dataArray, function() {
        if (dataObject[this.name] !== undefined) {
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

App.removeFormValidationErrors = function (form, hideMessage) {
    if (!!hideMessage) {
        AppComponents.hideMessage();
    }
    form.find('.has-error').removeClass('has-error');
    return form.find('.error-text').slideUp(App.animationsDurationMs);
};

App.applyFormValidationErrors = function (form, xhr) {
    $.when(App.removeFormValidationErrors(form, true)).done(function () {
        try {
            var response = JSON.parse(xhr.responseText);
            if (response.message) {
                AppComponents.setMessage(response.message, 'danger');
            }
            if (response.errors && $.isPlainObject(response.errors)) {
                for (var inputName in response.errors) {
                    if (form[0][inputName]) {
                        var errorEl = $('<div class="error-text bg-danger">' + response.errors[inputName] + '</div>').hide();
                        $(form[0][inputName]).parent().addClass('has-error').append(errorEl);
                        errorEl.slideDown(App.animationsDurationMs);
                    }
                }
            }
        } catch (exc) {
            AppComponents.setErrorMessageFromXhr(xhr, true);
        }
    });
};

