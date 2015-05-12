var App = {
    container: '#page-content',
    messagesContainer: '#page-messages',
    baseUrl: '/',
    viewsUrl: '/get_view.php?view=',
    apiUrl: '/api.php?action=',
    urlArgs: {},
    routes: {},
    loadedRoutes: {},
    apiActions: {
        status: 'status',
        login: 'login'
    },
    currentRoute: null,
    animationsDurationMs: 150,
    userInfo: {},
    baseBrowserTitle: ''
};

App.init = function (urlArgs) {
    if (!!urlArgs && $.isPlainObject(urlArgs)) {
        App.urlArgs = urlArgs;
    }
    
    App.routes = {
        login: {
            url: App.viewsUrl + 'login.form',
            handlebars: false,
            controller: AppController.loginForm,
            cache: true
        },
        admin_dashboard: {
            url: App.viewsUrl + 'admin.dashboard',
            handlebars: false,
            controller: AppController.adminDashboard,
            cache: true
        }
    };

    $(document).ready(function () {
        App.baseBrowserTitle = document.title;
        App.container = $(App.container);
        App.messagesContainer = $('<div id="page-messages">').hide();
        $(document.body).prepend(App.messagesContainer);

        window.addEventListener('popstate', function(event){
            if (event.state && event.state.route) {
                App.setRoute(event.state.route);
            }
        }, false);

        App.isLoggedIn();
    });
};

App.isLoggedIn = function () {
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
        }
    }).fail(function (xhr) {
        App.setRoute('login');
    });
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
        throw error;
    }
    if (App.currentRoute !== route) {
        App.currentRoute = route;
        var routeInfo = App.routes[route];
        if (!App.loadedRoutes[route]) {
            App.container.addClass('loading');
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
                if (!!routeInfo.handlebars) {
                    template = Handlebars.compile(template);
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
                if (!App.isAuthorisationFailure(xhr)) {
                    App.setMessage(xhr.responseText, 'danger');
                }
            }).always(function () {
                setTimeout(function () {
                    App.container.removeClass('loading');
                }, 500);
            });
        } else {
            document.title = App.loadedRoutes[routeInfo.url].template.browserTitle;
            routeInfo.controller(App.loadedRoutes[routeInfo.url].template, true);
        }
        if (!doNotChangeUrl) {
            window.history.pushState({route: App.currentRoute}, null, App.baseUrl + '?route=' + route);
        }
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

App.setUser = function (userInfo) {
    App.userInfo = userInfo;
};