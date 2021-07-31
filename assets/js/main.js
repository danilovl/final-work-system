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

    // smartresize
    jQuery.fn[sr] = function (fn) {
        return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr);
    };

})(jQuery, 'smartresize');

// Sidebar
function initSidebar() {
    let CURRENT_URL = window.location.href.split('#')[0].split('?')[0],
        $BODY = $('body'),
        $MENU_TOGGLE = $('#menu_toggle'),
        $SIDEBAR_MENU = $('#sidebar-menu'),
        $SIDEBAR_FOOTER = $('.sidebar-footer'),
        $LEFT_COL = $('.left_col'),
        $RIGHT_COL = $('.right_col'),
        $NAV_MENU = $('.nav_menu'),
        $FOOTER = $('footer');

    let setContentHeight = function () {
        // reset height
        $RIGHT_COL.css('min-height', $(window).height());

        let bodyHeight = $BODY.outerHeight(),
            footerHeight = $BODY.hasClass('footer_fixed') ? -10 : $FOOTER.height(),
            leftColHeight = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
            contentHeight = bodyHeight < leftColHeight ? leftColHeight : bodyHeight;

        // normalize content
        contentHeight -= $NAV_MENU.height() + footerHeight;

        $RIGHT_COL.css('min-height', contentHeight);
    };

    $SIDEBAR_MENU.find('a').on('click', function (ev) {
        console.log('clicked - sidebar_menu');
        let $li = $(this).parent();

        if ($li.is('.active')) {
            $li.removeClass('active active-sm');
            $('ul:first', $li).slideUp(function () {
                setContentHeight();
            });
        } else {
            // prevent closing menu if we are on child menu
            if (!$li.parent().is('.child_menu')) {
                $SIDEBAR_MENU.find('li').removeClass('active active-sm');
                $SIDEBAR_MENU.find('li ul').slideUp();
            } else {
                if ($BODY.is(".nav-sm")) {
                    $SIDEBAR_MENU.find("li").removeClass("active active-sm");
                    $SIDEBAR_MENU.find("li ul").slideUp();
                }
            }
            $li.addClass('active');

            $('ul:first', $li).slideDown(function () {
                setContentHeight();
            });
        }
    });

// toggle small or large menu
    $MENU_TOGGLE.on('click', function () {
        console.log('clicked - menu toggle');

        if ($BODY.hasClass('nav-md')) {
            $SIDEBAR_MENU.find('li.active ul').hide();
            $SIDEBAR_MENU.find('li.active').addClass('active-sm').removeClass('active');
        } else {
            $SIDEBAR_MENU.find('li.active-sm ul').show();
            $SIDEBAR_MENU.find('li.active-sm').addClass('active').removeClass('active-sm');
        }

        $BODY.toggleClass('nav-md nav-sm');

        setContentHeight();

        $('.dataTable').each(function () {
            $(this).dataTable().fnDraw();
        });
    });

    // check active menu
    $SIDEBAR_MENU.find('a[href="' + CURRENT_URL + '"]').parent('li').addClass('current-page');

    $SIDEBAR_MENU.find('a').filter(function () {
        return this.href == CURRENT_URL;
    }).parent('li').addClass('current-page').parents('ul').slideDown(function () {
        setContentHeight();
    }).parent().addClass('active');

    // recompute content when resizing
    $(window).smartresize(function () {
        setContentHeight();
    });

    setContentHeight();

    // fixed sidebar
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

//hover and retain popover when on popover content
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
            //We entered the actual popover – call off the dogs
            clearTimeout(timeout);
            //Let's monitor popover content instead
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
    console.log('initInputMask');

    $(":input").inputmask();
}

/* VALIDATOR */
function initValidator() {

    if (typeof (validator) === 'undefined') {
        return;
    }
    console.log('initValidator');

    // initialize the validator function
    validator.message.date = 'not a real date';

    // validate a field on "blur" event, a 'select' on 'change' event & a '.reuired' classed multifield on 'keyup':
    $('form')
        .on('blur', 'input[required], input.optional, select.required', validator.checkField)
        .on('change', 'select.required', validator.checkField)
        .on('keypress', 'input[required][pattern]', validator.keypress);

    $('.multi.required').on('keyup blur', 'input', function () {
        validator.checkField.apply($(this).siblings().last()[0]);
    });

    $('form').submit(function (e) {
        e.preventDefault();
        let submit = true;

        // evaluate the form using generic validaing
        if (!validator.checkAll($(this))) {
            submit = false;
        }

        if (submit)
            this.submit();

        return false;
    });
}

function initTinymce(locale, selector) {
    let defaultSelector = 'textarea';

    if (selector) {
        defaultSelector = selector;
    }
    tinymce.baseURL = window.location.origin + '/build/tinymce';
    tinymce.init({
        selector: defaultSelector,
        height: 50,
        theme: 'modern',
        language: locale,
        plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table contextmenu directionality',
            'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc help'
        ],
        toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        toolbar2: 'print preview media | forecolor backcolor emoticons | codesample help',
        image_advtab: true,
        templates: [
            {title: 'Test template 1', content: 'Test 1'},
            {title: 'Test template 2', content: 'Test 2'}
        ],
        content_css: [
            '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
            'tinymce/css/tinymce.css?' + new Date().getTime()
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

function notifyMessage(notify_type, notify_text) {

    if (typeof (PNotify) === 'undefined') {
        return;
    }

    new PNotify({
        title: notify_text,
        type: notify_type,
        hide: true,
        styling: 'bootstrap3'
    });
}

function showGoogleDocs(id, url) {
    let ifram = $(id);

    if (ifram) {
        let googleUrl = 'https://docs.google.com/viewer?embedded=true&url=' + url;

        if (ifram.is(':empty')) {
            let iframe = '<iframe src="' + googleUrl + '" width="100%" height="780" style="border: none;"></iframe>';
            ifram.html(iframe);
        }
    }
}

function showArchiveContent(archiveUrl, idContent, idContentInfo) {
    let content = $(idContent);
    let fileContent = $("<ul>");
    let zipInfo = $(idContentInfo);
    content.append(fileContent);

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
                fileContent.append($("<li>", {
                    text: zipEntry.name
                }));
            });
        })
        .then(function success(text) {
            zipInfo.append($("<p>", {
                "class": "alert alert-success",
                text: "loaded, content = " + text
            }));
        }, function error(e) {
            zipInfo.append($("<p>", {
                "class": "alert alert-danger",
                text: e
            }));
        });
}

function createEditContentAjax(buttonId, close = false, reload = false) {
    let buttonCreate = $(buttonId),
        loadingIcon = $(buttonCreate.children()[0]),
        form = buttonCreate.parents('form');

    buttonCreate.click(function (e) {
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
                if (response.valid === false) {
                    for (let key in response.data) {
                        $(form.find('[name*="' + key + '"]')[0]).after('<div class="col-md-12 col-sm-12 col-xs-12 validation-error red">' + response.data[key] + '</div>');
                    }
                }
                for (let type in response.notifyMessage) {
                    notifyMessage(type, response.notifyMessage[type]);
                }
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
    });
}

function createEditContentFileAjax(buttonId, close = false, reload = false) {
    let buttonCreate = $(buttonId),
        loadingIcon = $(buttonCreate.children()[0]),
        form = buttonCreate.parents('form');

    buttonCreate.click(function (e) {
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
                if (response.valid === false) {
                    for (let key in response.data) {
                        $(form.find('[name*="' + key + '"]')[0]).after('<div class="col-md-12 col-sm-12 col-xs-12 validation-error red">' + response.data[key] + '</div>');
                    }
                }
                for (let type in response.notifyMessage) {
                    notifyMessage(type, response.notifyMessage[type]);
                }
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
            }
        }).done(function () {
            loadingIcon.addClass('hide');
            buttonCreate.prop('disabled', false);
            setTimeout(function () {
                $('.validation-error').remove();
            }, 3000);
        });
    });
}

function initAjaxChangeStatus() {
    if ($(".js-switch")) {
        let switch_elements = Array.prototype.slice.call(document.querySelectorAll('.js-switch'))
            .filter(function (item, index) {
                return item.style.display !== "none"
            });

        switch_elements.forEach(function (element) {
            new Switchery(element, {
                color: '#26B99A',
                secondaryColor: '#ff0000'
            });

            if (element.hasAttribute('data-target-url')) {
                element.onchange = function () {
                    let method = "POST";
                    let url = element.getAttribute('data-target-url');
                    let type = {'type': element.getAttribute('data-target-type')};

                    $.ajax({
                        type: method,
                        url: url,
                        data: type,
                        timeout: 10000,
                        success: function (obj) {
                            if (obj.valid === false) {
                                for (let key in obj.data) {
                                    $(form.find('[name*="' + key + '"]')[0]).after('<div class="col-md-12 col-sm-12 col-xs-12 validation-error red">' + obj.data[key] + '</div>');
                                }
                            }
                            for (let type in obj.notifyMessage) {
                                notifyMessage(type, obj.notifyMessage[type]);
                            }
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
        delete_elements.forEach(function (element) {
            element.onclick = function (e) {
                e.preventDefault();

                let method = "POST";
                let url = element.getAttribute('data-delete-target-url');
                let row = $('tr').filter('[data-delete-row="' + element.getAttribute('data-delete-target-row') + '"]');
                let modalWindow = $('.modal');
                let modalBackdrop = $('.modal-backdrop');

                element.disabled = true;
                let loadingIcon = element.childNodes[1];
                loadingIcon.classList.remove("hide");

                window.automaticModalWindowClose = true;

                $.ajax({
                    type: method,
                    url: url,
                    timeout: 10000,
                    success: function (obj) {
                        if (obj.delete === true) {
                            $('.modal').modal('hide');
                            setTimeout(function () {
                                $(row).remove();
                            }, 1000);
                        }
                        for (let type in obj.notifyMessage) {
                            notifyMessage(type, obj.notifyMessage[type]);
                        }
                    }
                }).done(function () {
                    loadingIcon.classList.add("hide");
                    element.disabled = false;
                }).fail(function () {
                    loadingIcon.classList.add("hide");
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
    if ($(selector)) {
        let elems = Array.prototype.slice.call(document.querySelectorAll(selector));
        elems.forEach(function (html) {
            new Switchery(html, {
                color: '#26B99A',
                secondaryColor: '#ff0000'
            });
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

function initChecked() {
    $('table input').on('ifChecked', function () {
        checkState = '';
        $(this).parent().parent().parent().addClass('selected');
        countChecked();
    });
    $('table input').on('ifUnchecked', function () {
        checkState = '';
        $(this).parent().parent().parent().removeClass('selected');
        countChecked();
    });
}

function initFlat() {
    if ($("input.flat")[0]) {
        $(document).ready(function () {
            $('input.flat').iCheck({
                checkboxClass: 'icheckbox_flat-green',
                radioClass: 'iradio_flat-green'
            });
        });
    }
}

function initHideShow() {
    $('.collapse-link').on('click', function () {
        let $BOX_PANEL = $(this).closest('.x_panel'),
            $ICON = $(this).find('i'),
            $BOX_CONTENT = $BOX_PANEL.find('.x_content');

        // fix for some div with hardcoded fix class
        if ($BOX_PANEL.attr('style')) {
            $BOX_CONTENT.slideToggle(200, function () {
                $BOX_PANEL.removeAttr('style');
            });
        } else {
            $BOX_CONTENT.slideToggle(200);
            $BOX_PANEL.css('height', 'auto');
        }

        $ICON.toggleClass('fa-chevron-up fa-chevron-down');
    });

    $('.close-link').click(function () {
        let $BOX_PANEL = $(this).closest('.x_panel');

        $BOX_PANEL.remove();
    });
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
    eventSource.onmessage = function (event) {
        let data = event.data;
        if (data !== null && data !== undefined && data.length > 0) {
            $chat.prepend(atob64DecodeUnicode(data));
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

function widgetEventSource(url, widgetsParam) {
    let eventSource = new EventSource(url);
    eventSource.onmessage = function (event) {
        let eventData = event.data;
        if (eventData.length === 0) {
            return;
        }

        let data = $.parseJSON(event.data);
        if (data === null || data.length === 0) {
            return;
        }

        let widgets = Object.entries(widgetsParam);
        for (let widget of widgets) {
            let message = data[widget[0]];
            if (message === null || message === undefined || message.length === 0) {
                continue;
            }

            let id = widget[1];
            $(`#${id}`).replaceWith(message);
        }
    }
}

function createImageByWebCamera(id) {
    let webCameraImageContainer = $(`#${id}`);
    let idCanvas = webCameraImageContainer.data('id-canvas');
    let elementCanvas = document.getElementById(idCanvas);
    let idWebCameraVideo = webCameraImageContainer.data('id-web-camera-video');
    let video = document.querySelector(`#${idWebCameraVideo}`);
    let url = webCameraImageContainer.data('url');
    let idBtnCapture = webCameraImageContainer.data('id-button-capture');
    let idBtnSave = webCameraImageContainer.data('id-button-save');
    let maxShowWidth = webCameraImageContainer.data('max-show-width');

    const mediaStreamConstraints = {
        audio: false,
        video: {
            width: video.offsetWidth,
            height: video.offsetHeight
        }
    };

    function clickBtnCapture(width, height) {
        $(`#${idBtnCapture}`).click(() => {
            if (width > maxShowWidth) {
                width = maxShowWidth;
                height = width / (4 / 3);
            }

            let context = elementCanvas.getContext('2d');
            elementCanvas.style.setProperty('width', width);
            elementCanvas.style.setProperty('height', height);

            elementCanvas.height = height;
            elementCanvas.width = width;

            context.drawImage(video, 0, 0, width, height);
        });
    }

    function clickBtnSave(width, height) {
        $(`#${idBtnSave}`).click(() => {
            let context = elementCanvas.getContext('2d');
            context.drawImage(video, 0, 0, width, height);

            let imageBase64Data = elementCanvas.toDataURL('image/png');
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
        });
    }

    if (navigator.mediaDevices !== undefined && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia(mediaStreamConstraints)
            .then(stream => {
                video.srcObject = stream;
                video.play();

                let {width, height} = stream.getTracks()[0].getSettings();

                clickBtnCapture(width, height);
                clickBtnSave(width, height);
            })
            .catch(() => {
                webCameraImageContainer.hide();
            });
    } else {
        webCameraImageContainer.hide();
    }
}
