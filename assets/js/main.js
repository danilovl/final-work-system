function findSectionMenuLink(sidebarMenuElement, currentUrl) {
    const minimumSharedSegments = 2;
    const pathSegments = url => new URL(url, window.location.origin).pathname.split('/').filter(segment => segment !== '');
    const currentSegments = pathSegments(currentUrl);

    let bestLink = null;
    let bestScore = minimumSharedSegments - 1;

    sidebarMenuElement.find('a[href]').each(function () {
        const linkSegments = pathSegments(this.href);
        let score = 0;

        while (score < linkSegments.length && linkSegments[score] === currentSegments[score]) {
            score++;
        }

        if (score > bestScore) {
            bestScore = score;
            bestLink = this;
        }
    });

    return bestLink === null ? $() : $(bestLink);
}

function initSidebar() {
    const CURRENT_URL = window.location.href.split('#')[0].split('?')[0];
    const bodyElement = $('body');
    const menuToggleElement = $('#menu_toggle');
    const sidebarMenuElement = $('#sidebar-menu');

    let setContentHeight = function () {};

    sidebarMenuElement.find('a').on('click', function () {
        let $li = $(this).parent();

        if ($li.is('.active')) {
            $li.removeClass('active active-sm');
            $('ul:first', $li).slideUp(function () {
                setContentHeight();
            });
        } else {
            if (!$li.parent().is('.child_menu')) {
                sidebarMenuElement.find('li').removeClass('active active-sm');
                sidebarMenuElement.find('li ul').slideUp();
            } else {
                if (bodyElement.is(".nav-sm")) {
                    sidebarMenuElement.find("li").removeClass("active active-sm");
                    sidebarMenuElement.find("li ul").slideUp();
                }
            }
            $li.addClass('active');

            $('ul:first', $li).slideDown(function () {
                setContentHeight();
            });
        }
    });

    menuToggleElement.on('click', function () {
        console.log('clicked - menu toggle');

        if (bodyElement.hasClass('nav-md')) {
            sidebarMenuElement.find('li.active ul').hide();
            sidebarMenuElement.find('li.active').addClass('active-sm').removeClass('active');
        } else {
            sidebarMenuElement.find('li.active-sm ul').show();
            sidebarMenuElement.find('li.active-sm').addClass('active').removeClass('active-sm');
        }

        bodyElement.toggleClass('nav-md nav-sm');

        setContentHeight();

        $('.dataTable').each(function () {
            $(this).dataTable().fnDraw();
        });
    });

    let currentLinks = sidebarMenuElement.find('a').filter(function () {
        return this.href === CURRENT_URL;
    });

    if (currentLinks.length === 0) {
        currentLinks = findSectionMenuLink(sidebarMenuElement, CURRENT_URL);
    }

    currentLinks.parent('li').addClass('current-page').parents('ul').slideDown(function () {
        setContentHeight();
    }).parent().addClass('active');

    setContentHeight();

}

let originalLeave = $.fn.popover.Constructor.prototype.leave;
$.fn.popover.Constructor.prototype.leave = function (obj) {
    let self = obj instanceof this.constructor ?
        obj : $(obj.currentTarget)[this.type](this.getDelegateOptions()).data('bs.' + this.type);
    let container, timeout;

    originalLeave.call(this, obj);

    if (obj.currentTarget) {
        container = $(obj.currentTarget).siblings('.popover');
        timeout = self.timeout;
        container.one('mouseenter', function () {
            clearTimeout(timeout);

            container.one('mouseleave', function () {
                $.fn.popover.Constructor.prototype.leave.call(self, self);
            });
        });
    }
};

function initValidator() {
    if (typeof (validator) === 'undefined') {
        return;
    }

    validator.message.date = 'not a real date';

    const form = $('form');

    form
        .on('blur', 'input[required], input.optional, select.required', validator.checkField)
        .on('change', 'select.required', validator.checkField)
        .on('keypress', 'input[required][pattern]', validator.keypress);

    $('.multi.required').on('keyup blur', 'input', function () {
        validator.checkField.apply($(this).siblings().last()[0]);
    });

    form.on('submit', (function (e) {
        e.preventDefault();
        let submit = true;

        if (!validator.checkAll($(this))) {
            submit = false;
        }

        if (submit)
            this.submit();

        return false;
    }));
}

function notifyMessage(notifyType, notifyText) {
    const alertTypes = {
        error: 'danger',
        notice: 'warning'
    };
    const alertType = alertTypes[notifyType] || notifyType;

    let container = document.getElementById('notify-container');
    if (container === null) {
        container = document.createElement('div');
        container.id = 'notify-container';
        container.className = 'notify-container';
        document.body.appendChild(container);
    }

    const item = document.createElement('div');
    item.className = `alert alert-${alertType} notify-item`;
    item.setAttribute('role', 'alert');
    item.textContent = notifyText;
    item.addEventListener('click', () => item.remove());
    container.appendChild(item);

    setTimeout(() => {
        item.classList.add('notify-item-hide');
        setTimeout(() => item.remove(), 300);
    }, 3000);
}

function showGoogleDocs(id, url) {
    let container = $(id);

    if (container.length === 0 || !container.is(':empty')) {
        return;
    }

    let iframe = $('<iframe width="100%" height="780" style="border: none;"></iframe>');
    iframe.attr('src', 'https://docs.google.com/viewer?embedded=true&url=' + encodeURIComponent(url));
    container.append(iframe);
}

function showArchiveContent(archiveUrl, idContent, idContentInfo) {
    let contentElement = $(idContent);
    let fileContentElement = $("<ul>");
    let zipInfoElement = $(idContentInfo);

    contentElement.append(fileContentElement);

    let promise = new JSZip.external.Promise(function (resolve, reject) {
        JSZipUtils.getBinaryContent(archiveUrl, function (err, data) {
            if (err) {
                reject(err);
            } else {
                resolve(data);
            }
        });
    });

    promise.then(JSZip.loadAsync)
        .then(function (zip) {
            zip.forEach(function (relativePath, zipEntry) {
                fileContentElement.append($("<li>", {
                    text: zipEntry.name
                }));
            });
        })
        .then(function success(text) {
            zipInfoElement.append($("<p>", {
                "class": "alert alert-success",
                text: "loaded, content = " + text
            }));
        }, function error(e) {
            zipInfoElement.append($("<p>", {
                "class": "alert alert-danger",
                text: e
            }));
        });
}

const processCalendarManagerCreateNotValidFormResponse = function (response) {
    const form = $('#calendar-manager-create-form');
    console.log(form)
    processNotValidFormResponse(form, response)
}

const processNotValidFormResponse = function (form, response) {
    if (!response.data) {
        return
    }

    for (const key in response.data) {
        $(form.find('[name*="' + key + '"]')[0]).after('<div class="col-md-12 col-sm-12 col-xs-12 validation-error red">' + response.data[key] + '</div>');
    }
}

function createEditContentAjax(buttonId, close = false, reload = false) {
    let buttonCreate = $(buttonId),
        loadingIcon = $(buttonCreate.children()[0]),
        form = buttonCreate.parents('form');

    const processResponse = function (response) {
        if (response.valid === false) {
            processNotValidFormResponse(form, response)
        }

        for (let type in response.notifyMessage) {
            notifyMessage(type, response.notifyMessage[type]);
        }
    }

    buttonCreate.on('click', (function (e) {
        e.preventDefault();

        let data = new FormData(form[0]);
        buttonCreate.prop('disabled', true);
        loadingIcon.removeClass('hide');

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: data,
            timeout: 10000,
            async: true,
            processData: false,
            contentType: false,
            cache: false,
            dataType: "json",
            success: function (response) {
                processResponse(response)

                if (response.valid === true) {
                    if (reload) {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }

                    if (close) {
                        window.automaticModalWindowClose = true;
                        setTimeout(function () {
                            $('#modalWindow').modal('toggle');
                        }, 1000);
                    }
                }
            },
            error: function (response) {
                processResponse(response.responseJSON)
            }
        }).done(function () {
            loadingIcon.addClass('hide');
            buttonCreate.prop('disabled', false);
            setTimeout(function () {
                $('.validation-error').remove();
            }, 3000);

            if (close) {
                window.automaticModalWindowClose = true;
            }
        });
    }));
}

function createEditContentFileAjax(buttonId, close = false, reload = false) {
    let buttonCreate = $(buttonId);
    let loadingIcon = $(buttonCreate.children()[0]);
    let form = buttonCreate.parents('form');

    const processResponse = function (response) {
        if (response.valid === false) {
            processNotValidFormResponse(form, response)
        }
        for (let type in response.notifyMessage) {
            notifyMessage(type, response.notifyMessage[type]);
        }
    }

    buttonCreate.on('click', (function (e) {
        e.preventDefault();

        let data = new FormData(form[0]);
        buttonCreate.prop('disabled', true);
        loadingIcon.removeClass('hide');

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: data,
            timeout: 10000,
            async: true,
            processData: false,
            contentType: false,
            cache: false,
            dataType: "json",
            success: function (response) {
                processResponse(response)

                if (response.valid === true) {
                    if (reload) {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }

                    if (close) {
                        window.automaticModalWindowClose = true;
                        setTimeout(function () {
                            $('#modalWindow').modal('toggle');
                        }, 1000);
                    }
                }
            },
            error: function (response) {
                processResponse(response.responseJSON)
            }
        }).done(function () {
            loadingIcon.addClass('hide');
            buttonCreate.prop('disabled', false);
            setTimeout(function () {
                $('.validation-error').remove();
            }, 3000);
        });
    }));
}

function initAjaxDeleteContent() {
    let delete_elements = Array.prototype.slice.call(document.querySelectorAll('.delete-element'));
    if (delete_elements.length > 0) {
        const processResponse = function (response) {
            for (const type in response.notifyMessage) {
                notifyMessage(type, response.notifyMessage[type]);
            }
        }

        delete_elements.forEach(function (element) {
            element.onclick = function (e) {
                e.preventDefault();

                let url = element.getAttribute('data-delete-target-url');
                let row = $('tr').filter('[data-delete-row="' + element.getAttribute('data-delete-target-row') + '"]');
                let modalWindow = $('.modal');
                let modalBackdrop = $('.modal-backdrop');

                element.disabled = true;
                let loadingIcon = element.childNodes[1];
                loadingIcon.classList.remove('hide');

                window.automaticModalWindowClose = true;

                $.ajax({
                    type: 'POST',
                    url: url,
                    timeout: 10000,
                    success: function (response) {
                        if (response.delete === true) {
                            $('.modal').modal('hide');
                            setTimeout(function () {
                                $(row).remove();
                            }, 1000);
                        }

                        processResponse(response)
                    },
                    error: function (response) {
                        processResponse(response.responseJSON)
                    }
                }).done(function () {
                    loadingIcon.classList.add('hide');
                    element.disabled = false;
                }).fail(function () {
                    loadingIcon.classList.add('hide');
                    element.disabled = false;

                    if (modalBackdrop.hasClass('in')) {
                        modalBackdrop.removeClass('in');
                    }

                    if (modalWindow.hasClass('in')) {
                        modalWindow.removeClass('in');
                    }
                });
            };
        });
    }
}

function initTooltip() {
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });
}

function initModalResize() {
    $('.modal-content').resizable();
}

function initModalDraggable() {
    $(".modal-dialog").draggable({
        handle: ".modal-header",
        cursor: "move"
    });
}

function initHideShow() {
    $('.collapse-link').on('click', function () {
        let boxPanelElement = $(this).closest('.x_panel');
        let iconElement = $(this).find('i');
        let boxContentElement = boxPanelElement.find('.x_content');

        if (boxPanelElement.attr('style')) {
            boxContentElement.slideToggle(200, function () {
                boxPanelElement.removeAttr('style');
            });
        } else {
            boxContentElement.slideToggle(200);
            boxPanelElement.css('height', 'auto');
        }

        iconElement.toggleClass('fa-chevron-up fa-chevron-down');
    });

    $('.close-link').on('click', (function () {
        $(this).closest('.x_panel').remove();
    }));
}

function initPopover() {
    $('body').popover({
        selector: '[data-popover]',
        trigger: 'click hover',
        delay: {
            show: 50,
            hide: 400
        }
    });
}

function backToTop() {
    let scrollTrigger = 100;
    let scrollTop = $(window).scrollTop();

    if (scrollTop > scrollTrigger) {
        $('#back-to-top').addClass('show');
    } else {
        $('#back-to-top').removeClass('show');
    }
}

function initBackToTop() {
    $('#back-to-top').on('click', function (e) {
        e.preventDefault();
        $('html,body').animate({
            scrollTop: 0
        }, 700);
    });
}

function atob64DecodeUnicode(str) {
    return decodeURIComponent(Array.prototype.map.call(atob(str), function (c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
    }).join(''))
}

function conversationEventSource(url, $chat) {
    const eventSource = new EventSource(url);
    eventSource.onmessage = event => {
        let data = JSON.parse(event.data);
        if (data.message !== undefined) {
            $chat.prepend((data.message));
        }
    };

    return eventSource;
}

(function ($) {
    $.fn.tableFix = function () {
        return this.each(function () {
            let $this = $(this),
                $t_fixed;

            function init() {
                $this.wrap('<div class="container-table-fixed"/>');
                $t_fixed = $this.clone();
                $t_fixed.find("tbody").remove().end().addClass("table-header-fixed").insertBefore($this);
                resizeFixed();
            }

            function resizeFixed() {
                $t_fixed.find("th").each(function (index) {
                    $(this).css("width", $this.find("th").eq(index).outerWidth() + "px");
                });
            }

            function scrollFixed() {
                let offset = $(this).scrollTop(),
                    tableOffsetTop = $this.offset().top,
                    tableOffsetBottom = tableOffsetTop + $this.height() - $this.find("thead").height();
                if (offset < tableOffsetTop || offset > tableOffsetBottom) {
                    $t_fixed.hide();
                } else if (offset >= tableOffsetTop && offset <= tableOffsetBottom && $t_fixed.is(":hidden")) {
                    $t_fixed.show();
                }
            }

            $(window).resize(resizeFixed);
            $(window).scroll(scrollFixed);
            init();
        });
    };
})(jQuery);

(function ($) {
    $.fn.workTableFix = function (tabId) {
        let tab = $('#' + tabId);

        return this.each(function () {
            let $this = $(this),
                $t_fixed;

            function init() {
                $this.wrap('<div class="work-container-table-fixed"/>');
                $t_fixed = $this.clone();
                $t_fixed.find('tbody').remove().end().addClass('work-table-header-fixed').insertBefore($this);
                resizeFixed();
            }

            function resizeFixed() {
                $t_fixed.find('th').each(function (index) {
                    $(this).css('width', $this.find('th').eq(index).outerWidth() + 'px');
                });
            }

            function scrollFixed() {
                if (tab.is(':visible')) {
                    resizeFixed();
                    let offset = $(this).scrollTop(),
                        tableOffsetTop = $this.offset().top,
                        tableOffsetBottom = tableOffsetTop + $this.height() - $this.find('thead').height();
                    if (offset < tableOffsetTop || offset > tableOffsetBottom) {
                        $t_fixed.hide();
                    } else if (offset >= tableOffsetTop && offset <= tableOffsetBottom && $t_fixed.is(':hidden')) {
                        $t_fixed.show();
                    }
                }
            }

            $(window).resize(resizeFixed);
            $(window).scroll(scrollFixed);
            init();
        });
    };
})(jQuery);

function widgetEventSource(url, id) {
    const eventSource = new EventSource(url);
    eventSource.onmessage = event => {
        let data = JSON.parse(event.data);
        if (data.content === undefined) {
            return;
        }

        $(`#${id}`).replaceWith(data.content);
    };

    return eventSource;
}

function createImageByWebCamera(id) {
    let webCameraImageContainerElement = $(`#${id}`);
    let idCanvas = webCameraImageContainerElement.data('id-canvas');
    let elementCanvasElement = document.getElementById(idCanvas);
    let idWebCameraVideo = webCameraImageContainerElement.data('id-web-camera-video');
    let videoElement = document.querySelector(`#${idWebCameraVideo}`);
    let url = webCameraImageContainerElement.data('url');
    let idBtnCapture = webCameraImageContainerElement.data('id-button-capture');
    let idBtnSave = webCameraImageContainerElement.data('id-button-save');
    let maxShowWidth = webCameraImageContainerElement.data('max-show-width');

    const mediaStreamConstraints = {
        audio: false,
        video: {
            width: videoElement.offsetWidth,
            height: videoElement.offsetHeight
        }
    };

    function clickBtnCapture(width, height) {
        $(`#${idBtnCapture}`).on('click', (() => {
            if (width > maxShowWidth) {
                width = maxShowWidth;
                height = width / (4 / 3);
            }

            let context = elementCanvasElement.getContext('2d');
            elementCanvasElement.style.setProperty('width', width);
            elementCanvasElement.style.setProperty('height', height);

            elementCanvasElement.height = height;
            elementCanvasElement.width = width;

            context.drawImage(videoElement, 0, 0, width, height);
        }));
    }

    function clickBtnSave(width, height) {
        $(`#${idBtnSave}`).on('click', (() => {
            let context = elementCanvasElement.getContext('2d');
            context.drawImage(videoElement, 0, 0, width, height);

            let imageBase64Data = elementCanvasElement.toDataURL('image/png');
            imageBase64Data = imageBase64Data.replace('data:image/png;base64,', '');

            $.ajax({
                type: 'POST',
                url: url,
                data: JSON.stringify({'imageData': imageBase64Data}),
                timeout: 10000,
                dataType: 'json',
                contentType: 'application/json',
                processData: false,
                success: (response) => {
                    for (let type in response.notifyMessage) {
                        notifyMessage(type, response.notifyMessage[type]);
                    }

                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            });
        }));
    }

    if (navigator.mediaDevices !== undefined && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia(mediaStreamConstraints)
            .then(stream => {
                videoElement.srcObject = stream;
                videoElement.play();

                let {width, height} = stream.getTracks()[0].getSettings();

                clickBtnCapture(width, height);
                clickBtnSave(width, height);
            })
            .catch(() => {
                webCameraImageContainerElement.hide();
            });
    } else {
        webCameraImageContainerElement.hide();
    }
}

const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '';

let frontendConfig = null;

function getFrontendConfig() {
    if (frontendConfig !== null) {
        return frontendConfig;
    }

    const configElement = document.getElementById('frontend-config');
    if (configElement === null) {
        return {messages: {}, flashes: []};
    }

    frontendConfig = JSON.parse(configElement.textContent);

    return frontendConfig;
}

function getErrorMessage(status) {
    const messages = getFrontendConfig().messages;
    const errorMessages = {
        403: messages.error403,
        404: messages.error404,
        500: messages.error500
    };

    return errorMessages[status] || `Status ${status}`;
}

function closeModalWindowOnError() {
    const modalBackdrop = document.querySelector('.modal-backdrop');
    const modalWindow = document.getElementById('modalWindow');
    const modalWindowContent = document.querySelector('#modalWindow .modal-content');

    if (modalBackdrop && modalBackdrop.classList.contains('in')) {
        modalBackdrop.classList.remove('in');
    }

    if (modalWindow && modalWindow.classList.contains('in')) {
        modalWindow.classList.remove('in');
        if (modalWindowContent) {
            modalWindowContent.innerHTML = '';
        }
    }
}

function hideAjaxLoader() {
    document.getElementById('ajax-loader').style.display = 'none';
    initModalResize();
    initModalDraggable();
}

function initLayout() {
    $.ajaxSetup({
        'global': true,
        'headers': {'X-CSRF-Token': csrfToken}
    });
    initSidebar();
    initValidator();
    initTooltip();
    initHideShow();
    initPopover();
    initModalDraggable();
    initBackToTop();
    initAjaxDeleteContent();

    $('body').on('hidden.bs.modal', '.modal', function () {
        $(this).removeData('bs.modal');
        $('#modalWindow .modal-dialog').html('<div class="modal-content"></div>');
        $('.modal-dialog').attr('style', '');
    });

    $(document).on('focusin', function (event) {
        if ($(event.target).closest('.mce-window').length) {
            event.stopImmediatePropagation();
        }
    });

    $('#modalWindow').on('hide.bs.modal', function () {
        if (window.automaticModalWindowClose === true) {
            window.automaticModalWindowClose = false;

            return;
        }

        let empty = true;
        $(':text, :file, select, textarea').each(function () {
            if ($(this).val() !== '') {
                empty = false;
            }
        });

        if (empty === false && !confirm(getFrontendConfig().messages.sureWantClose)) {
            return false;
        }
    });

    $(window).on('scroll', () => {
        backToTop();
    });

    $(document).ajaxStart(() => {
        $('#ajax-loader').show();
    });

    $(document).ajaxStop(() => {
        $('#ajax-loader').hide();
        initModalResize();
        initModalDraggable();
    });

    $(document).ajaxError((event, jqXHR) => {
        const response = jqXHR.responseJSON;
        if (response && response.hasOwnProperty('notifyMessage')) {
            return;
        }

        const message = getErrorMessage(jqXHR.status);
        let modalBackdrop = $('.modal-backdrop');
        let modalWindow = $('#modalWindow');
        let modalWindowContent = $('#modalWindow .modal-content');

        if (modalBackdrop.hasClass('in')) {
            modalBackdrop.removeClass('in');
        }

        if (modalWindow.hasClass('in')) {
            modalWindow.removeClass('in');
            modalWindowContent.empty();
        }

        notifyMessage('error', message);
    });
}

let activeRequests = 0;
const originalFetch = window.fetch;

window.fetch = async (input, init = {}) => {
    activeRequests++;
    document.getElementById('ajax-loader').style.display = 'block';

    init.headers = {
        ...(init.headers || {}),
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token': csrfToken
    };

    try {
        const response = await originalFetch(input, init);
        activeRequests--;

        if (activeRequests === 0) {
            hideAjaxLoader();
        }

        if (!response.ok) {
            try {
                const responseJson = await response.clone().json();
                if (responseJson && 'notifyMessage' in responseJson) {
                    return response;
                }
            } catch (e) {}

            closeModalWindowOnError();
            notifyMessage('error', getErrorMessage(response.status));
        }

        return response;
    } catch (error) {
        activeRequests--;
        if (activeRequests === 0) {
            hideAjaxLoader();
        }

        closeModalWindowOnError();
        notifyMessage('error', 'Network error or invalid response');

        throw error;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('frontend-config') === null) {
        return;
    }

    initLayout();
});

window.addEventListener('load', () => {
    for (const flash of getFrontendConfig().flashes) {
        setTimeout(() => {
            notifyMessage(flash.type, flash.message);
        });
    }
});
