(function ($) {
    'use strict';

    // Central UI state used by layer controls and canvas renderer.
    var state = {
        selectedPhoto: '',
        selectedFrame: '',
        currentWidth: 200,
        currentHeight: 200,
        validExtensions: ['jpg', 'jpeg', 'jfif', 'png', 'gif'],
        previewTimeout: null
    };

    function getConfig() {
        var configEl = document.getElementById('appConfig');
        if (!configEl) {
            return;
        }

        state.selectedPhoto = configEl.getAttribute('data-selected-photo') || '';
        state.selectedFrame = configEl.getAttribute('data-default-frame') || '';
        state.currentWidth = parseInt(configEl.getAttribute('data-width'), 10) || 200;
        state.currentHeight = parseInt(configEl.getAttribute('data-height'), 10) || 200;
    }

    function markSelected(selector, src) {
        $(selector).removeClass('selected');
        if (!src) {
            return;
        }
        $(selector).filter(function () {
            return $(this).attr('src') === src;
        }).addClass('selected');
    }

    function renderCanvas() {
        var canvas = document.getElementById('testCanvas');
        if (!canvas || !state.selectedPhoto) {
            return;
        }

        $('#testCanvas').css({
            width: state.currentWidth + 'px',
            height: state.currentHeight + 'px'
        });

        canvas.width = state.currentWidth;
        canvas.height = state.currentHeight;

        var frameThickness = 15;
        var matThickness = 40;

        var matWidth = state.currentWidth - frameThickness * 2;
        var matHeight = state.currentHeight - frameThickness * 2;

        var imageWidth = Math.max(matWidth - matThickness * 2, 50);
        var imageHeight = Math.max(matHeight - matThickness * 2, 50);

        // Delegate frame/mount/photo composition to the existing Frame engine.
        new Frame({
            canvas: $('#testCanvas'),
            pxPerMM: 1,
            frame: {
                file: state.selectedFrame,
                thickness: frameThickness
            },
            mount: {
                layers: [
                    {
                        color: '#f6efe6',
                        padding: {
                            top: matThickness,
                            bottom: matThickness,
                            left: matThickness,
                            right: matThickness
                        }
                    }
                ],
                sections: [[{ width: imageWidth, height: imageHeight }]]
            },
            photos: [state.selectedPhoto]
        });
    }

    function updatePreview() {
        var newWidth = parseInt($('#inputWidth').val(), 10);
        var newHeight = parseInt($('#inputHeight').val(), 10);

        if (newWidth > 0 && newHeight > 0) {
            state.currentWidth = newWidth;
            state.currentHeight = newHeight;
            renderCanvas();
        }
    }

    function handleFileValidation(input) {
        if (!input.files || !input.files[0]) {
            return;
        }

        var fileName = input.files[0].name;
        var extension = fileName.slice(fileName.lastIndexOf('.') + 1).toLowerCase();
        var notice = $('#fileTypeNotice');

        if ($.inArray(extension, state.validExtensions) === -1) {
            input.value = '';
            notice.text('Allowed file formats: ' + state.validExtensions.join(', ')).stop(true, true).fadeIn(100);
            setTimeout(function () {
                notice.fadeOut(300);
            }, 2500);
            return;
        }

        notice.hide();
        var reader = new FileReader();
        reader.onload = function (e) {
            state.selectedPhoto = e.target.result;
            renderCanvas();
        };
        reader.readAsDataURL(input.files[0]);
    }

    function bindEvents() {
        // Use delegated handlers so dynamically rendered options also work.
        $(document).on('click', '.frame-option', function () {
            state.selectedFrame = $(this).data('frame') || this.src;
            $('.frame-option').removeClass('selected');
            $(this).addClass('selected');
            renderCanvas();
        });

        $(document).on('click', '.sample-photo, .uploaded-photo', function () {
            state.selectedPhoto = $(this).data('photo') || this.src;
            markSelected('.sample-photo, .uploaded-photo', state.selectedPhoto);
            renderCanvas();
        });

        $('#updatePreviewBtn').on('click', function (e) {
            e.preventDefault();
            updatePreview();
        });

        $('#inputWidth, #inputHeight').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                updatePreview();
            }
        });

        $('#inputWidth, #inputHeight').on('input', function () {
            clearTimeout(state.previewTimeout);
            state.previewTimeout = setTimeout(updatePreview, 120);
        });

        $('#fileInput').on('change', function () {
            handleFileValidation(this);
        });
    }

    function setDefaults() {
        var firstFrame = $('.frame-option').first();
        if (!state.selectedFrame && firstFrame.length) {
            state.selectedFrame = firstFrame.attr('src');
        }
        if (state.selectedFrame) {
            markSelected('.frame-option', state.selectedFrame);
        }

        var firstSample = $('.sample-photo').first();
        var firstUploaded = $('.uploaded-photo').first();

        if (!state.selectedPhoto && firstSample.length) {
            state.selectedPhoto = firstSample.attr('src');
        } else if (!state.selectedPhoto && firstUploaded.length) {
            state.selectedPhoto = firstUploaded.attr('src');
        }

        if (state.selectedPhoto) {
            markSelected('.sample-photo, .uploaded-photo', state.selectedPhoto);
        }
    }

    $(function () {
        getConfig();
        bindEvents();
        setDefaults();
        renderCanvas();
    });
})(jQuery);
