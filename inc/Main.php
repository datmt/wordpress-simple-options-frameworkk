<?php

namespace BinaryCarpenter\BC_OFW;

use BinaryCarpenter\BC_OFW\Config as Config;

/**
 * Class Main
 * @package BinaryCarpenter\BC_MNC
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
        echo $option_form->open_form();

        echo $option_form->input_field('some_settings', 'text', 'Enter something');
        echo $option_form->single_image_picker('main_image', 'select image', 'Pick any image', false);

        echo $option_form->single_checkbox('food_for_thought', '', 'Check me if you want to');

        echo $option_form->radio('car_choice',
            [
                ['label' => 'BMW', 'value' => 'bmw', 'disabled' => false],
                ['label' => 'Mercedes', 'value' => 'mercedes', 'disabled' => true],
                ['label' => 'Audi', 'value' => 'audi', 'disabled' => false],
            ], 'row', 'Choose your car'
        );

        echo $option_form->multiple_checkbox('selected_sports', false, '', ['football', 'tennis']);

        echo $option_form->setting_fields();
        echo $option_form->submit_button('Save');

        echo $option_form->close_form();
        echo $option_form->close_container();

    }

}