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
        $option_form->js_post_form();
        $option_form->setting_fields();
        $option_form->submit_button('Save');

        echo $option_form->close_form();
        echo $option_form->close_container();

    }

}