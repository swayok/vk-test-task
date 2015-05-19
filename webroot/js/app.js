var App = {
    container: '#page-content',
    messagesContainer: '#page-messages',
    baseUrl: null,
    viewsUrl: null,
    apiUrl: null,
    urlArgs: {},
    routes: {},
    loadedRoutes: {},
    apiActions: {},
    navigationMenus: {
        viewsUrls: {},
        currentSection: null,
        templates: {},
        container: '#page-navigation'
    },
    currentRoute: null,
    animationsDurationMs: 150,
    userInfo: null,
    baseBrowserTitle: ''
};

App.init = function (urlArgs) {
    if (!!urlArgs && $.isPlainObject(urlArgs)) {
        App.urlArgs = urlArgs;
    }
    
    AppConfigs.configureApp();

    $(document).ready(function () {
        App.baseBrowserTitle = document.title;
        App.container = $(App.container);
        App.navigationMenus.container = $('<div id="page-navigation"></div>');
        App.container.before(App.navigationMenus.container);
        App.messagesContainer = $('<div id="page-messages"></div>').hide();
        $(document.body).prepend(App.messagesContainer);

        $(document.body).on('click', 'a[href]', function () {
            if (!$(this).attr('data-route')) {
                $(this).attr('data-route', App.extractRouteFromUrl(this.href));
            }
            App.setRoute($(this).attr('data-route'));
            return false;
        });

        window.addEventListener('popstate', function(event){
            if (event.state && event.state.route) {
                App.setRoute(event.state.route);
            }
        }, false);

        App.getUser();
    });
};

App.extractRouteFromUrl = function (url) {
    var matches = url.match(/^.*?\?.*?route=([a-zA-Z\-_0-9]+)/i);
    return matches && matches[1] ? matches[1] : '__undefined__route__';
};

App.setUser = function (userInfo) {
    App.userInfo = userInfo;
    if (App.currentRoute && App.routes[App.currentRoute].section) {
        App.displayNavigationMenu(App.routes[App.currentRoute].section, true);
    }
};

App.getUser = function () {
    if (!$.isPlainObject(App.userInfo)) {
        return $.ajax({
            url: App.apiUrl + App.apiActions.status,
            cache: false,
            dataType: 'json'
        }).done(function (json) {
            if (!!App.urlArgs.route && !!App.routes[App.urlArgs.route] && App.urlArgs.route !== 'login') {
                App.setRoute(App.urlArgs.route);
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

App.setCurrentRoute = function (route) {
    if (App.currentRoute !== route) {
        App.currentRoute = route;
        App.container.find('a.active').removeClass('active').end()
            .find('a[href*="?route=' + route + '"]').addClass('active');
        App.activateNavigationMenuButton(route);
    }
};

App.setRoute = function (route, doNotChangeUrl, message) {
    if (!!message) {
        if ($.isPlainObject(message)) {
            App.setMessage(message.message, message.type);
        } else {
            App.setMessage(message);
        }
    } else {
        App.hideMessage();
    }
    if (!route || !App.routes[route]) {
        var error = 'Unknown route [' + route + '] detected';
        App.setMessage(error, 'danger');
        return false;
    }
    var routeInfo = App.routes[route];
    if (routeInfo.canBeReloaded || App.currentRoute !== route) {
        App.setCurrentRoute(route);
        if (!routeInfo.url) {
            routeInfo.controller();
        } else if (!App.loadedRoutes[route]) {
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
                    App.setMessage(xhr.responseText, 'danger');
                }
            });
        } else {
            document.title = App.loadedRoutes[route].browserTitle;
            routeInfo.controller(App.loadedRoutes[route].template, true);
        }
        if (!doNotChangeUrl) {
            window.history.pushState({route: App.currentRoute}, null, App.baseUrl + '?route=' + route);
        }
    }
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

App.setMessage = function (message, type) {
    $.when(App.hideMessage()).done(function () {
        message = $('<div class="container">').append(message);
        App.messagesContainer.html('').append(message);
        if (!type) {
            type = 'info';
        } else {
            type = type.toLowerCase();
            if (!$.inArray(type, ['success', 'info', 'warning', 'danger'])) {
                type = 'info';
            }
        }
        App.messagesContainer.attr('class', 'bg-' + type);
        App.messagesContainer.slideDown(App.animationsDurationMs);
    });
};

App.hideMessage = function () {
    return App.messagesContainer.slideUp(App.animationsDurationMs);
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

App.getApiUrl = function (action) {
    if (!App.apiActions[action]) {
        var error = 'Api action [' + action + '] not defined';
        App.setMessage(error, 'danger');
        throw error;
    }
    return App.apiUrl + action;
};

App.isValidationErrors = function (xhr) {
    return xhr.statusCode === 400;
};

App.isAuthorisationFailure = function (xhr) {
    return xhr.statusCode === 401;
};

App.isNotAuthorisationFailure = function (xhr) {
    if (App.isAuthorisationFailure(xhr)) {
        var json = JSON.parse(xhr.responseText);
        App.setRoute('login', false, json.message ? {type: 'error', message: json.message} : null);
        return false;
    }
    return true;
};

App.removeFormValidationErrors = function (form, hideMessage) {
    if (!!hideMessage) {
        App.hideMessage();
    }
    form.find('.has-error').removeClass('has-error');
    return form.find('.error-text').slideUp(App.animationsDurationMs);
};

App.applyFormValidationErrors = function (form, xhr) {
    $.when(App.removeFormValidationErrors(form, true)).done(function () {
        var response = JSON.parse(xhr.responseText);
        if (!response) {
            App.setMessage(xhr.responseText);
        } else {
            if (response.message) {
                App.setMessage(response.message, 'danger');
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
        }
    });
};

App.displayNavigationMenu = function (section, rerender) {
    if (App.navigationMenus.viewsUrls[section]) {
        if (App.navigationMenus.templates[section]) {
            if (App.navigationMenus.templates[section] === true) {
                // already loading a template
            } else if (section !== App.navigationMenus.currentSection || rerender) {
                var html = App.navigationMenus.templates[section]({user: App.getUser()});
                App.navigationMenus.container.html('').append(html);
                App.activateNavigationMenuButton(App.currentRoute);
                App.navigationMenus.currentSection = section;
            }
            return true;
        } else {
            App.navigationMenus.templates[section] = true;
            $.ajax({
                url: App.navigationMenus.viewsUrls[section],
                cache: true
            }).done(function (html) {
                App.navigationMenus.templates[section] = doT.template(html);
                var navHtml = App.navigationMenus.templates[section]({user: App.getUser()});
                App.navigationMenus.container.html('').append(navHtml);
                App.activateNavigationMenuButton(App.currentRoute);
                App.navigationMenus.currentSection = section;
            }).fail(function (xhr) {
                if (!App.isAuthorisationFailure(xhr)) {
                    App.setMessage(xhr.responseText, 'danger');
                }
            });
            return true;
        }
    } else {
        App.navigationMenus.currentSection = null;
        App.navigationMenus.container.html('');
    }
    return false;
};

App.activateNavigationMenuButton = function (route) {
    App.navigationMenus.container.find('li.active').removeClass('active').end()
        .find('li a[href*="?route=' + route + '"]').closest('li').addClass('active');
};