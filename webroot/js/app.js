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
    currentSection: null,
    currentUrlArgs: {},
    messageAfterRouteChange: null,
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

        $(document.body).on('click', 'a[href]', function () {
            if (!this.href.match(document.location.origin)) {
                return true;
            }
            var urlArgs = Utils.parseUrlQuery(this.href);
            if ($(this).attr('data-add-back-url') == '1') {
                urlArgs.back_url = App.getRouteUrl();
            }
            App.setRoute(urlArgs.route, urlArgs);
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

App.setUser = function (userInfo) {
    App.userInfo = userInfo;
    if (App.currentRoute && App.routes[App.currentRoute].section) {
        AppComponents.displayNavigationMenu(true);
    }
};

App.getUser = function (reload) {
    if (!$.isPlainObject(App.userInfo) || reload) {
        var deferred = $.Deferred();
        $.ajax({
            url: App.getApiUrl('status'),
            cache: false,
            dataType: 'json'
        }).done(function (json) {
            if (!reload) {
                if (!!App.initialUrlArgs.route && !!App.routes[App.initialUrlArgs.route] && App.initialUrlArgs.route !== 'login') {
                    App.setRoute(App.initialUrlArgs.route, App.initialUrlArgs);
                    App.initialUrlArgs = {};
                } else if (!!json._route && !!App.routes[json._route]) {
                    App.setRoute(json._route);
                } else {
                    App.setRoute('login');
                    return;
                }
            }
            App.setUser(json);
            deferred.resolve(App.userInfo);
        }).fail(function (xhr) {
            App.setRoute('login');
        });
        return deferred;
    } else {
        return App.userInfo;
    }
};

App.setCurrentRoute = function (route, urlArgs) {
    if (!route || !App.routes[route]) {
        var error = 'Unknown route [' + route + '] detected';
        AppComponents.setMessage(error, 'danger');
        return null;
    }
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
        App.currentSection = App.routes[route].section;
        return true;
    } else {
        return false;
    }
};

App._changeBrowserUrl = function () {
    window.history.pushState(App.currentUrlArgs, null, App.getRouteUrl());
};

App.setRoute = function (route, urlArgs, doNotChangeUrl) {
    if (App.messageAfterRouteChange) {
        AppComponents.setMessage(App.messageAfterRouteChange);
        App.messageAfterRouteChange = null;
    } else {
        AppComponents.hideMessage();
    }

    var isDifferentRoute = App.setCurrentRoute(route, urlArgs);
    if (isDifferentRoute === null) {
        return;
    }
    var routeInfo = App.routes[route];
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

App.setMessageAfterRouteChange = function (message, type) {
    App.messageAfterRouteChange = {message: message, type: type};
};

App._loadViewForRoute = function (route, routeInfo) {
    App.isLoading(true);
    $.ajax({
        url: routeInfo.url,
        cache: true
    }).done(function (html) {
        // use <h1> text as browser title
        var browserTitle = App.baseBrowserTitle;
        var matches = html.match(/<h1[^>]*>([\s\S]+?)<\/h1/i);
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
        if (App.isNotAuthorisationFailure(xhr) && App.isNotInternalServerError(xhr)) {
            AppComponents.setErrorMessageFromXhr(xhr);
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
    return App.apiUrl + App.apiActions[action];
};

App.isValidationErrors = function (xhr) {
    return xhr.status === 400;
};

App.isNotAuthorisationFailure = function (xhr) {
    if (xhr.status === 401) {
        App.setRoute('login');
        AppComponents.setErrorMessageFromXhr(xhr);
        return false;
    }
    return true;
};

App.isNotInternalServerError = function (xhr) {
    if (xhr.status === 500) {
        AppComponents.setErrorMessageFromXhr(xhr);
        return false;
    }
    return true;
};

