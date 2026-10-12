(function ($) {
    'use strict';

    $(function () {

        // ---- Tabs ----
        $('.laxcc-ag-tab').on('click', function () {
            var key = $(this).data('tab');
            $('.laxcc-ag-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $('.laxcc-ag-panel').removeClass('is-active');
            $('.laxcc-ag-panel[data-panel="' + key + '"]').addClass('is-active');
        });

        // ---- WP color picker ----
        if ($.fn.wpColorPicker) {
            $('.laxcc-ag-color').wpColorPicker();
        }

        // ---- Background type toggle (image vs video field groups) ----
        function syncBgFields() {
            var val = $('input[name="laxcc_ag[background_type]"]:checked').val();
            $('.laxcc-ag-bg-image-fields').toggle(val === 'image');
            $('.laxcc-ag-bg-video-fields').toggle(val === 'video');
        }
        $('input[name="laxcc_ag[background_type]"]').on('change', syncBgFields);
        syncBgFields();

        // ---- Overlay range live label ----
        var $overlayInput = $('#laxcc-ag-overlay-input');
        var $overlayVal   = $('#laxcc-ag-overlay-val');
        if ($overlayInput.length) {
            $overlayInput.on('input', function () {
                $overlayVal.text(this.value + '%');
            });
        }

        // ---- Media uploader ----
        $('.laxcc-ag-media').each(function () {
            var $wrap     = $(this);
            var mediaType = $wrap.data('media-type'); // image | video
            var $hidden   = $wrap.find('.laxcc-ag-media-url');
            var $preview  = $wrap.find('.laxcc-ag-media-preview');
            var $pickBtn  = $wrap.find('.laxcc-ag-media-pick');
            var $clearBtn = $wrap.find('.laxcc-ag-media-clear');
            var frame;

            $pickBtn.on('click', function (e) {
                e.preventDefault();

                if (frame) { frame.open(); return; }

                frame = wp.media({
                    title: mediaType === 'video' ? 'Select Video' : 'Select Image',
                    button: { text: 'Use this ' + mediaType },
                    library: { type: mediaType },
                    multiple: false
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $hidden.val(attachment.url);

                    var html;
                    if (mediaType === 'video') {
                        html = '<video muted></video>';
                        $preview.html(html);
                        $preview.find('video').attr('src', attachment.url);
                    } else {
                        html = '<img alt="">';
                        $preview.html(html);
                        $preview.find('img').attr('src', attachment.url);
                    }

                    $pickBtn.text('Replace');
                    $clearBtn.removeAttr('hidden');
                });

                frame.open();
            });

            $clearBtn.on('click', function (e) {
                e.preventDefault();
                $hidden.val('');
                var emptyText = $preview.data('empty-text') || 'No media selected';
                var icon = mediaType === 'video' ? 'video-alt3' : 'format-image';
                $preview.html(
                    '<span class="laxcc-ag-media-empty">' +
                        '<span class="dashicons dashicons-' + icon + '"></span>' +
                        '<em>' + emptyText + '</em>' +
                    '</span>'
                );
                $pickBtn.text('Choose ' + (mediaType === 'video' ? 'Video' : 'Image'));
                $clearBtn.attr('hidden', true);
            });
        });

    });

})(jQuery);
