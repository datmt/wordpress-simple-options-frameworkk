(function ($) {

    $(document).ready(function () {
        //save the settings on key press
        $(window).bind('keydown', function (event) {
            if (event.ctrlKey || event.metaKey) {
                switch (String.fromCharCode(event.which).toLowerCase()) {
                    case 's':
                        event.preventDefault();
                        //save all forms
                        _.each($('.bc2018fw-form-submit-button'), function (the_button) {
                            save_form($(the_button));
                        });

                        break;

                }
            }
        });


        $('.bc2018fw-form-submit-button').on('click', function (e) {
            e.preventDefault();
            save_form($(this));
        });

        $(document).on('click', '.add-data-row', function () {
            add_data_row($(this));
        });
        $(document).on('click', '.minus-data-row', function () {
            remove_data_row($(this));
        });


        $('.bc2018fw-image-picker-button').on('click', function (e) {
            const parent = $(this).closest('.bc2018fw-image-picker');
            const image_placeholder_element = parent.find('.bc2018fw-image-preview').first();
            const image_input_field_element = parent.find('.bc2018fw-image-picker-hidden-input').first();
            open_single_media_selector(image_placeholder_element, image_input_field_element)
        });

    });


    function open_multiple_media_selector(images_placeholder_div_id, image_input_field_id) {
        const frame = wp.media({
            title: "select ",
            button: {
                text: "Use these medias",
            },
            multiple: true,
        });

        frame.on("select", function () {
            const attachments = frame.state().get("selection").toJSON();
            //@TODO: implement this
        });

        frame.open();
    }


    function open_single_media_selector(image_placeholder_element, image_input_field_element) {
        const frame = wp.media({
            title: "select an image",
            button: {
                text: "Use this image",
            },
            multiple: false,
        });

        frame.on("select", function () {
            const attachment = frame.state().get("selection").first().toJSON();
            image_placeholder_element.attr('src', attachment.url);
            image_input_field_element.val(attachment.id);
        });

        frame.open();
    }

    function save_form(the_button) {
        const data = {};
        const formId = the_button.attr('data-bcfw-form-id');

        //store all fields name to delete the empty ones in the backend
        _.each($(`#${formId}`).find('input, select, textarea').not('.bc-no-key-field'), function (i) {

            let input = $(i);
            let input_name = (input).attr('name');
            let input_value = undefined;

            //for checkbox, get value of the checked one
            if (input.attr('type') === 'checkbox') {
                data[input_name] = data[input_name] || [];
                if (input.is(':checked')) {
                    input_value = input.val();
                    if (typeof input_value != 'undefined' && input_value.trim() !== '')
                        data[input_name].push(input_value);

                }
            } else if (input.attr('type') === 'radio') {
                //for radio input, since there are many radios share the same name, only get the value of checked radio
                if (input.is(':checked')) {
                    input_value = input.val();
                    if (typeof input_value != 'undefined' && input_value.trim() !== '')
                        data[input_name] = input_value;
                    else
                        data[input_name] = '';
                }
            } else {
                input_value = input.val();
                if (typeof input_value != 'undefined' && input_value.trim() !== '')
                    data[input_name] = input_value;
            }
        });

        _.each(the_button.closest('form').find('.bc-key-array-assoc-data-field'), function (field) {
            var data_rows = {};

            _.each($(field).find('.bc-single-data-row'), function (single_data_row) {

                const data_key = $(single_data_row).find('.bc-single-data-value').eq(0).val();
                const data_value = $(single_data_row).find('.bc-single-data-value').eq(1).val();
                if (data_key !== '')
                    data_rows[data_key] = data_value;
            });

            data[$(field).attr('data-name')] = data_rows;

        });
        const all_fields_name = [];
        _.each(the_button.closest('form').find('input, select, textarea').not('.bc-no-key-field'), function (i) {
            const field_name = $(i).attr('data-bc2018fw-field');
            if (all_fields_name.indexOf(field_name) === -1)
                all_fields_name.push(field_name);
        });
        data['all_fields_name'] = all_fields_name;
        $.post(ajaxurl, data, function (response) {
            Swal.fire(
                '',
                response.message,
                'info'
            )

            if (typeof (response.redirect_url) !== 'undefined') {
                const current_tab = the_button.closest('.bc-single-tab').attr('id');
                window.location.href = response.redirect_url + '&active_tab=' + current_tab;
            }
        });
    }

    //add one more data ro
    function add_data_row(add_button) {
        //clone current row
        const clone = add_button.closest('.bc-single-data-row').clone();
        add_button.closest('[data-name]').append(clone);
    }

    function remove_data_row(remove_button) {
        const current_row = remove_button.closest('.bc-single-data-row');
        //don't remove if it's the last row
        const data_field = remove_button.closest('[data-name]');

        if (data_field.find('.bc-single-data-row').length <= 1)
            return;
        current_row.remove();
    }


})(jQuery);