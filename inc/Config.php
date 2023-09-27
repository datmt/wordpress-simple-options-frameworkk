<?php
/**
 * Created by PhpStorm.
 * User: MYN
 * Date: 5/9/2019
 * Time: 8:57 AM
 */

namespace BinaryCarpenter\BC_FW;


/**
 * Class Config
 * @package BinaryCarpenter\BC_MNC
 */
class Config
{
    const PLUGIN_ID = 'bc-option-framework';
    const PLUGIN_NAME = 'BC Option Framework';
    const PLUGIN_MENU_NAME = self::PLUGIN_NAME;
    const PLUGIN_SLUG = self::PLUGIN_ID;
    const IS_PRO = false;
    const PLUGIN_TEXT_DOMAIN = self::PLUGIN_ID;
    const PLUGIN_OPTION_NAME = 'bc_auto_thumb_option_name';
    const PLUGIN_COMMON_HANDLER = 'bc_auto_thumb_handler';
    const KEY_CHECK_OPTION = 'bc_auto_thumb_key_check';
    const LICENSE_KEY_OPTION = 'bc_auto_thumb_stored_license_key';
    const PLUGIN_VERSION_NUMBER = 100;
    const LICENSE_CHECK_URL = "https://api.gotkey.io/public/activate/30837999853190265244496741031/14346c08-0020-4bba-bbdb-f1f1fae14f8f/30895732338538528261449891785";
    const OPTION_NAME = 'bc_' . self::PLUGIN_ID . '_option_name';

    const UPDATE_CHECK_URL = 'https://www.binarycarpenter.com/updater2/api/';
    const UPDATE_SLUG = 'bc-auto-thumb';

}
