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
        echo $option_form->input_field('some_settings', 'text', 'Enter something');
        $option_form->js_post_form();
        echo $option_form->close_container();

    }

}