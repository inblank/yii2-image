(function () {
    function setImage($parent, image) {
        $parent[(image ? 'remove' : 'add') + 'Class']('upload-image-empty')
            .find('.upload-image-preview')
            .css('backgroundImage', 'url(' + (image ? image : $parent.data('image-empty')) + ')');
    }

    $(function () {
        // image change button
        $('.upload-image-change').on('click', function (e) {
            var $input = $(this).find('input[type=file]');
            if (e.target !== $input[0]) {
                $input.trigger('click');
            }
        }).find('input[type=file]').on('change', function () {
            var input = this, $parent = $(input).parents('.upload-image');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    setImage($parent, e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
                if ($parent.data('image')) {
                    // reset show only if exist initial image
                    $parent.find('.upload-image-reset').show();
                }
                $parent.find('[type=hidden]').val('');
            }
            return false;
        });

        // image reset button
        $('.upload-image-reset').on('click', function () {
            var $parent = $(this).parents('.upload-image');
            $parent.find('input').val('');
            setImage($parent, $parent.data('image'));
            $parent.find('.upload-image-reset').hide();
            return false;
        });

        // image clear button
        $('.upload-image-clear').on('click', function () {
            var $parent = $(this).parents('.upload-image');
            $parent.find('[type=file]').val('');
            $parent.find('[type=hidden]').val('empty');
            setImage($parent, null);
            if ($parent.data('image')) {
                $parent.find('.upload-image-reset').show();
            }
            return false;
        })
    });

})(jQuery);
