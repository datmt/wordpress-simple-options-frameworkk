<?php

namespace BinaryCarpenter\BC_FW;

use BinaryCarpenter\BC_FW\Config as Config;

/**
 * Class Main
 * @package BinaryCarpenter\BC_FW
 */
class Main
{

    /**
     * Build the options page.
     * This is a demo page showing you how to build your settings pages
     */
    public static function ui()
    {
        $all_options = BC_Options::get_all_options(Config::OPTION_NAME);

        $option_id = 0;
        if ($all_options->have_posts())
            $option_id = $all_options->get_posts()[0]->ID;
        $option_form = new BC_Options_Form(Config::OPTION_NAME, $option_id);

        echo $option_form->open_container();


        $first_tab_content =
            $option_form->open_form()

            . $option_form->input_field('some_settings', 'text', 'Enter something')
            . $option_form->single_image_picker('main_image', 'select image', 'Pick any image', false)

            . $option_form->multiple_image_picker('multiple_images', 'select images', 'Pick many images', false, 100)


            . $option_form->single_checkbox('food_for_thought', '', 'Check me if you want to')

            . $option_form->radio('car_choice',
                [
                    ['label' => 'BMW', 'value' => 'bmw', 'disabled' => false],
                    ['label' => 'Mercedes', 'value' => 'mercedes', 'disabled' => true],
                    ['label' => 'Audi', 'value' => 'audi', 'disabled' => false],
                ], 'row', 'Choose your car'
            )

            . $option_form->multiple_checkbox('selected_sports', false, 'Your favorite sports', ['football', 'tennis'])

            . $option_form->textarea('some_textarea', 'Enter something', 'This is a placeholder')

            . $option_form->select('select_something', ['a' => 'A', 'b' => 'B', 'c' => 'C'], 'Select something', false, true)


            . $option_form->setting_fields()
            . $option_form->submit_button('Save')

            . $option_form->close_form();

        echo $option_form->open_tabs([['heading' => 'First', 'content' => $first_tab_content], ['heading' => 'Second tab', 'content' => '<h1>Second tab</h1>']]);

        echo $option_form->close_container();

    }

}