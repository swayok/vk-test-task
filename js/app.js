var App = {
    container: '#page-content',
    messagesContainer: '#page-messages',
    apiUrl: '/api.php?action=',
    urlArgs: {},
    routes: {},
    loadedRoutes: {},
    apiActions: {
        status: 'status',
        login: 'login'
    },
    currentRoute: null
};

App.init = function (urlArgs) {
    if (!!urlArgs && $.isPlainObject(urlArgs)) {
        App.urlArgs = urlArgs;
    }
    
    App.routes = {
        login: {
            url: '/views/login.form.php',
            handlebars: false,
            controller: AppController.loginForm,
            cache: true
        }
    };

    $(document).ready(function () {
        App.container = $(App.container);
        App.messagesContainer = $('<div id="page-messages">').hide();
        $(document.body).prepend(App.messagesContainer);
        App.isLoggedIn();
    });
};

App.isLoggedIn = function () {
    return $.ajax({
        url: App.apiUrl + App.apiActions.status,
        cache: false,
        dataType: 'json'
    }).done(function (json) {
        if (!!App.urlArgs.route && !!App.routes[App.urlArgs.route]) {
            App.currentRoute = App.urlArgs.route;
        } else if (!!json.route && !!App.routes[json.route]) {
            App.currentRoute = json.route;
        } else {
            App.currentRoute = 'login';
        }
        App.loadCurrentRoute();
    }).fail(function (xhr) {
        App.currentRoute = 'login';
        App.loadCurrentRoute();
    });
};

App.setRoute = function (route, changeUrl, message) {
    if (!!message) {
        if ($.isPlainObject(message)) {
            App.setMessage(message.message, message.type);
        } else {
            App.setMessage(message);
        }
    } else {
        App.hideMessage();
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
        App.messagesContainer.attr('class', 'bg-' + type).fadeIn(200);
    });
};

App.hideMessage = function () {
    return App.messagesContainer.fadeOut(200);
};

App.loadCurrentRoute = function () {
    var routeInfo = App.routes[App.currentRoute];
    if (!App.loadedRoutes[App.currentRoute]) {
        $.ajax({
            url: routeInfo.url,
            cache: true
        }).done(function (html) {
            var template = '<div class="content-wrapper" id="' + App.currentRoute + '-action-container">' + html + '</div>';
            if (!!routeInfo.handlebars) {
                template = Handlebars.compile(template);
            } else {
                template = $.parseHTML(template);
            }
            if (!!routeInfo.cache) {
                App.loadedRoutes[App.currentRoute] = template;
            }
            routeInfo.controller(template, false);
        });
    } else {
        routeInfo.controller(App.loadedRoutes[routeInfo.url], true);
    }
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
        throw 'Api action [' + action + '] not defined';
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

App.removeFormValidationErrors = function (form) {
    form.find('.has-error').removeClass('has-error');
    return form.find('.error-text').slideUp(100);
};

App.applyFormValidationErrors = function (form, xhr) {
    $.when(App.removeFormValidationErrors(form)).done(function () {
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
                        errorEl.slideDown(100);
                    }
                }
            }
        }
    });
};