(function ($, sr) {
    let debounce = function (func, threshold, execAsap) {
        let timeout;

        return function debounced() {
            let obj = this, args = arguments;

            function delayed() {
                if (!execAsap)
                    func.apply(obj, args);
                timeout = null;
            }

            if (timeout)
                clearTimeout(timeout);
            else if (execAsap)
                func.apply(obj, args);

            timeout = setTimeout(delayed, threshold || 100);
        };
    };

    jQuery.fn[sr] = function (fn) {
        return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr);
    };

})(jQuery, 'smartresize');

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

    sidebarMenuElement.find('a[href="' + CURRENT_URL + '"]').parent('li').addClass('current-page');

    sidebarMenuElement.find('a').filter(function () {
        return this.href === CURRENT_URL;
    }).parent('li').addClass('current-page').parents('ul').slideDown(function () {
        setContentHeight();
    }).parent().addClass('active');

    $(window).smartresize(function () {
        setContentHeight();
    });

    setContentHeight();

    if ($.fn.mCustomScrollbar) {
        $('.menu_fixed').mCustomScrollbar({
            autoHideScrollbar: true,
            theme: 'minimal',
            mouseWheel: {preventDefault: true}
        });
    }
}

function countChecked() {
    if (checkState === 'all') {
        $(".bulk_action input[name='table_records']").iCheck('check');
    }
    if (checkState === 'none') {
        $(".bulk_action input[name='table_records']").iCheck('uncheck');
    }

    let checkCount = $(".bulk_action input[name='table_records']:checked").length;

    if (checkCount) {
        $('.column-title').hide();
        $('.bulk-actions').show();
        $('.action-cnt').html(checkCount + ' Records Selected');
    } else {
        $('.column-title').show();
        $('.bulk-actions').hide();
    }
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

function initAutosize() {
    if (typeof $.fn.autosize !== 'undefined') {
        autosize($('.resizable_textarea'));
    }
}

function initInputMask() {
    if (typeof ($.fn.inputmask) === 'undefined') {
        return;
    }

    $(":input").inputmask();
}

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

function initTinymce(locale, selector) {
    let defaultSelector = 'textarea';

    if (selector) {
        defaultSelector = selector;
    }

    tinymce.baseURL = window.location.origin + '/build/tinymce';
    tinymce.init({
        selector: defaultSelector,
        height: 300,
        theme: 'silver',
        language: locale,
        image_advtab: true,
        templates: [
            {title: 'Test template 1', content: 'Test 1'},
            {title: 'Test template 2', content: 'Test 2'}
        ],
        content_css: [
            '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
        ],
        setup: function (editor) {
            editor.on("init", function () {
                editor.contentParent = $(this.contentAreaContainer.parentElement);
                editor.contentParent.find("div.mce-toolbar-grp").hide();
            });
            editor.on('focus', function () {
                editor.contentParent.find("div.mce-toolbar-grp").show();
            });
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        }
    });
}

function notifyMessage(notifyType, notifyText) {
    if (typeof (PNotify) === 'undefined') {
        return;
    }

    new PNotify({
        title: notifyText,
        type: notifyType,
        hide: true,
        styling: 'bootstrap3',
        delay: 2000
    });
}

function showGoogleDocs(id, url) {
    let iframe = $(id);

    if (iframe) {
        let googleUrl = 'https://docs.google.com/viewer?embedded=true&url=' + url;

        if (iframe.is(':empty')) {
            let iframe = '<iframe src="' + googleUrl + '" width="100%" height="780" style="border: none;"></iframe>';
            iframe.html(iframe);
        }
    }
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

function initAjaxChangeStatus() {
    if ($(".js-switch")) {
        let switch_elements = Array.prototype.slice.call(document.querySelectorAll('.js-switch'))
            .filter(function (item) {
                return item.style.display !== "none"
            });

        const processResponse = function (response) {
            for (let type in response.notifyMessage) {
                notifyMessage(type, response.notifyMessage[type]);
            }
        }

        switch_elements.forEach(function (element) {
            new Switchery(element, {
                color: '#26B99A',
                secondaryColor: '#ff0000'
            });

            if (element.hasAttribute('data-target-url')) {
                element.onchange = function () {
                    let url = element.getAttribute('data-target-url');
                    let type = {'type': element.getAttribute('data-target-type')};

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: type,
                        timeout: 10000,
                        success: function (response) {
                            processResponse(response)
                        },
                        error: function (response) {
                            processResponse(response.responseJSON)
                        }
                    });
                };
            }
        });
    }
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

function initJsSwitch(selector = '.js-switch') {
    if (!$(selector)) {
        return
    }

    let elems = Array.prototype.slice.call(document.querySelectorAll(selector));
    elems.forEach(function (html) {
        new Switchery(html, {
            color: '#26B99A',
            secondaryColor: '#ff0000'
        });
    });
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

function initChecked() {
    const tableInputElement = $('table input')

    tableInputElement.on('ifChecked', function () {
        checkState = '';
        $(this).parent().parent().parent().addClass('selected');
        countChecked();
    });

    tableInputElement.on('ifUnchecked', function () {
        checkState = '';
        $(this).parent().parent().parent().removeClass('selected');
        countChecked();
    });
}

function initFlat() {
    if (!$("input.flat")[0]) {
        return
    }

    document.addEventListener('DOMContentLoaded', () => {
        $('input.flat').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        });
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

function changeCalendarDate(selector, date) {
    $(selector).data("DateTimePicker").date(date);
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
    let eventSource = new EventSource(url);
    eventSource.onmessage = event => {
        let data = JSON.parse(event.data);
        if (data.message !== undefined) {
            $chat.prepend((data.message));
            initAjaxChangeStatus();
        }
    };
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

function widgetEventSource(url, id) {
    let eventSource = new EventSource(url);
    eventSource.onmessage = event => {
        let data = JSON.parse(event.data);
        if (data.content === undefined) {
            return;
        }

        $(`#${id}`).replaceWith(data.content);
    }
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
