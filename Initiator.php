<?php
/**
 * Plugin Name: BC Option Framework
 * Plugin URI:  https://www.binarycarpenter.com
 * Description: A framework to create options page for WordPress plugins
 * Version: 1.0.0
 * Author: BinaryCarpenter.com
 * Author URI: https://www.binarycarpenter.com/
 * License: GPL2
 * Text Domain: bc_option_framework
 */

namespace BinaryCarpenter\BC_FW;

require __DIR__ . '/inc/Core.php';
require __DIR__ . '/inc/Config.php';
require __DIR__ . '/inc/BC_Options.php';
require __DIR__ . '/inc/BC_Options_Form.php';
require __DIR__ . '/inc/Main.php';

class Initiator
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_styles_and_scritps'), 10);
        add_action('wp_ajax_' . BC_Options_Form::BC_OPTION_COMMON_AJAX_ACTION, array(BC_Options_Form::class, 'handle_post_save_options'));
    }

    public function load_admin_styles_and_scritps()
    {
        $screen = get_current_screen();
        error_log($screen->id);
        if (stripos($screen->id, Config::PLUGIN_SLUG)) {
            wp_enqueue_style('bc-ofw-admin-uikit-style', plugins_url('assets/css/uikit.min.css', __FILE__));
            wp_enqueue_style('bc-ofw-admin-style', plugins_url('assets/css/admin.css', __FILE__));
            wp_enqueue_script('bc-ofw-admin-sweetalert-script', plugins_url('assets/js/sweetalert.min.js', __FILE__));
            wp_enqueue_script('bc-ofw-admin-uikit-script', plugins_url('assets/js/uikit.min.js', __FILE__));
            wp_enqueue_script('bc-ofw-admin-uikit-icon-script', plugins_url('assets/js/uikit-icons.min.js', __FILE__));
            wp_enqueue_media();
            wp_enqueue_script('bc-ofw-admin-form-manager-script', plugins_url('assets/js/bc-form-manager.js', __FILE__),
                array('jquery', 'bc-ofw-admin-uikit-script', 'bc-ofw-admin-uikit-icon-script', 'bc-ofw-admin-sweetalert-script'));
        }
    }

    public function add_menu()
    {
        $core = new Core();
        $core->admin_menu();
        add_submenu_page(
            Core::MENU_SLUG,
            __(Config::PLUGIN_NAME, Config::PLUGIN_TEXT_DOMAIN),
            __('<img style="max-width: 1rem; max-height: 1rem;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAA7AAAAOwBeShxvQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAARZSURBVFiFxZdbbBRVHIe/uex0ZrcLpcWWiIUiEawahBhu9dIHvKAG0jsYiAiJCSESiShCoIAtKAR8Kcb4UlBo2lIqFwFpK8QQEFhMFHlBH0pBrluw6ba0u+3OzPFhsW7dtrsNW/y9TDLnzHzfnPOfOXPgf450/ygDw8PO3wOCQFJYn3jHB9iSZhgFsqyUJ+hGT8uU7FcaX5xbdKVs5buz4oaTkGRZVVBVB0KIwL22oGUGF0lGorul7IdfRqSNyYgba6Dc7RK0m9ByrYntudN9smUGjYcFB0hUQzOanD4O0zJd8kMj348UVlESoeIbVDraWjGD3XETilngfMNhls1IZ0X2OBY9k0L56rfx32t/OAKeukPs2/o+31UUcOPcfO6cyiZT/5nt78xC2PbQCggh2LlhOfvK83hqYhqmkY6m63y24glSHF7OHjs4tAKtd7woks2E8SNDJyQZoboBmJ01nMsXzg6tgJHoxufrwLLChtoOAuBtMXGOSO332h9rK+hoa30wAd3pYtrLs/n8y9CTSmYHstnGdW+AiqPNvDCnsM/rdhe/x/4NyyiZM31ACTWaAMCSki/YtPBVfr1Yw+ypCre8nZQf9LJgXRmp6RkR/Ss2r+LP499yIv9J9ly6y8Y3p7L+sAd3UnJEX0nTdX9tY7seTcIyTU4eqOTqb6dwjRzDS7kLGTV2XJ/wpvpqavIycZpd2P4AX11spvq2wvrDHrRhydz0CwA+npJsxiwQS8LhLk0BQHR29pJYfchDW8KIHoF+a0AIMSj4Nxs/4NrxGmrz/4UDSE4nsqGzdFIq+akmW3Nn4g+riT4Fqrd8yIrsx2nx3owZfvPkfqpzJmI4lIj2fySWT04jb2Q3O+fN6JGIEKjcsorrnipWvjWcksKsqBLR4H1JFDwSkrBMU+71FlRtW0uT5wh1u+aSxA2chkJJYRbFNT+RMmp0xE0rNn3EtRO17M3PHBDeS0LA8slpOC4280ljQO4Zgapta7l85gBHq+ZjpIzFdGWwOGc065akUFKYxV+3b/SGb15FU8Ne9v5nzqMmQQNg6aRU3LomFEVV11ndHerlMwc4UjkPd2ICQOhzKys8N95mmNOidM0Opr1RhDNxWJ/VHnNsG9HVBcCOC82o3YGAfuXcQb6vno/LqfXqa+mPArA4B4SA0qLnyZz5Gi3n66mNcdijRU7QNetI5bwIeLiE6cpgSe5oJozqxuc5GrXgBiWgKrLoDx4usbrsFr5bJntyno4bHGJcC4pL6zl9/DqV+VMGP+dREnU1LC6t53Td71TmTYo7PKrA+tJ6Th+7NGRwAFlSHJ2NV30RDWs2HONswx9UFzwbX3gg9Ed9pb0LRUvolDTDyNcc6tfuRE2TpdA+0DQtvN5WR1qS09bUAT6vkpAkQotW0JJNW0DmY2n+Txe9frtoy+6MoGX37AJkQAJZFrYsJIm2oOgKWPaC/janDxI/EADcDFzkPuDBfqnjkb8BHB2pCGCM/lgAAAAASUVORK5CYII="  alt="bc auto thumb"> ' . Config::PLUGIN_MENU_NAME, 'bc-menu-cart-woo'),
            'manage_options',
            Config::PLUGIN_SLUG,
            array('\BinaryCarpenter\BC_FW\Main', 'ui')
        );
    }
}

new Initiator();