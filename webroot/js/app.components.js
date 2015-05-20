var AppComponents = {
    messagesContainer: '#page-messages',
    navigationMenus: {
        viewsUrls: {},
        currentSection: null,
        templates: {},
        container: '#page-navigation'
    },
    paginationTpl: null
};

AppComponents.init = function () {
    AppComponents.navigationMenus.container = $('<div id="page-navigation"></div>');
    App.container.before(AppComponents.navigationMenus.container);

    AppComponents.messagesContainer = $('<div id="page-messages"></div>').hide();
    $(document.body).prepend(AppComponents.messagesContainer);
};

AppComponents.setMessage = function (message, type) {
    $.when(AppComponents.hideMessage()).done(function () {
        message = $('<div class="container">').append(message);
        AppComponents.messagesContainer.html('').append(message);
        if (!type) {
            type = 'info';
        } else {
            type = type.toLowerCase();
            if (!$.inArray(type, ['success', 'info', 'warning', 'danger'])) {
                type = 'info';
            }
        }
        AppComponents.messagesContainer.attr('class', 'bg-' + type);
        AppComponents.messagesContainer.slideDown(App.animationsDurationMs);
    });
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
                AppComponents.navigationMenus.container.html('').append(html);
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
                AppComponents.navigationMenus.container.html('').append(navHtml);
                AppComponents.activateNavigationMenuButton(App.currentRoute);
                AppComponents.navigationMenus.currentSection = section;
            }).fail(function (xhr) {
                if (!App.isAuthorisationFailure(xhr)) {
                    AppComponents.setMessage(xhr.responseText, 'danger');
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

AppComponents.getPaginatorTemplate = function () {

};