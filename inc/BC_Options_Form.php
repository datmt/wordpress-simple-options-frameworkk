<?php
/**
 * Created by PhpStorm.
 * User: MYN
 * Date: 5/11/2019
 * Time: 10:10 AM
 */

namespace BinaryCarpenter\BC_OFW;

use BinaryCarpenter\BC_OFW\Config as Config;

/**
 * Class BC_Options_Form
 * @package BinaryCarpenter\BC_TK
 * This class, will be used across multiple BC plugins.
 * They all share one common custom post type to store
 * plugins' settings
 *
 */
class BC_Options_Form
{
    private $option_name, $option_post_id, $form_css_id;
    private $options;
    const BC_OPTION_COMMON_AJAX_ACTION = 'bc_1378x_aj_action_bc_tk';
    const REDIRECT_URL = 'redirect_url';


    /**
     * BC_Options_Form constructor.
     * @param $option_name
     */
    public function __construct($option_name, $option_post_id)
    {
        $this->option_name = $option_name;
        $this->form_css_id = uniqid("bc-form-");
        $this->option_post_id = $option_post_id;
        $this->options = new BC_Options($this->option_name, $option_post_id);

        //update the $option_post_id in case the id passed in is 0, the BC_Options class will create a new post
        $this->option_post_id = $this->options->get_post_id();

    }

    public static function get_action_name()
    {
        return sprintf('%1$s', self::BC_OPTION_COMMON_AJAX_ACTION);
    }


    public function get_option_post_id()
    {
        return $this->option_post_id;
    }


    public static function handle_post_save_options()
    {
        if (Config::IS_PRO && !Activation::is_activated()) {
            wp_send_json(array(
                'error' => true,
                'message' => 'Plugin is not activated. Please activate it now'
            ));
            die();
        }
        //save the option to the post ID
        if (!current_user_can('edit_posts')) {
            echo __('You have no right to perform this action.', Config::PLUGIN_TEXT_DOMAIN);
            die();
        }

        //check nonce and update the options
        if (!wp_verify_nonce(sanitize_text_field($_POST['bc2018fw-form-security']), sanitize_text_field($_POST['action']))) {
            wp_send_json(array(
                'status' => 'Error',
                'message' => 'You do not have the necessary rights to perform this action'
            ));
            die();
        }

        $option_name = sanitize_text_field($_POST['option_name']);
        error_log($option_name);

        $option_post_id = intval($_POST['option_post_id']);

        $all_fields = $_POST['all_fields_name'];

        $option_object = new BC_Options($option_name, $option_post_id);

        //delete all fields of the form first before updating
        foreach ($all_fields as $key => $value) {

            error_log($key . ' deleting field: ' . $value);

        }
        $option_object->delete_all_fields($all_fields);


        //save the settings
        foreach ($_POST[$option_name] as $key => $value) {
            $option_object->set($key, $value);

            if ($key == 'title') {
                wp_update_post(
                    array(
                        'ID' => $option_post_id,
                        'post_title' => sanitize_text_field($value)
                    )
                );
            }
        }


        $option_object->set_option_name($option_name);

        $data = array(
            'status' => 'Success',
            'message' => 'Settings saved successfully');

        if (isset($_POST[self::REDIRECT_URL])) {
            $data[self::REDIRECT_URL] = esc_url($_POST[self::REDIRECT_URL]);
        }
        wp_send_json(
            $data
        );
        die();
    }

    /**
     * output nonce, action ...
     */
    public function setting_fields()
    {
        $html = sprintf('<input type="hidden" name="action" value="%1$s" />', $this->get_action_name());
        $html .= sprintf('<input type="hidden" name="option_post_id" value="%1$s" />', $this->option_post_id);
        $html .= sprintf('<input type="hidden" name="option_name" value="%1$s" />', $this->option_name);
        $html .= wp_nonce_field($this->get_action_name(), "bc2018fw-form-security", true, false);

        return $html;
    }


    private function get_option_value($option_form_field, $type = 'string')
    {
        switch ($type) {
            case 'string':
                return $this->options->get_string($option_form_field);
                break;
            case 'int':
                return $this->options->get_int($option_form_field);
                break;
            case 'float':
                return $this->options->get_float($option_form_field);
                break;
            case 'bool':
                return $this->options->get_bool($option_form_field);
                break;
            case 'array':
                return $this->options->get_array($option_form_field);
                break;
            default:
                return $this->options->get_string($option_form_field);
                break;
        }
    }

    /**
     * @param $option_form_field string: the actual field name in the form, will be prepend by $option_level_1[$option_level_2]
     */
    private function generate_form_field($option_form_field)
    {
        return sprintf('%1$s[%2$s]', $this->option_name, $option_form_field);
    }

    private function generate_multiple_checkbox_form_field($option_form_field)
    {
        return sprintf('%1$s[%2$s][]', $this->option_name, $option_form_field);
    }


    /**
     * Returns nonce field HTML
     *
     * @param string $action
     * @param string $name
     * @param bool $referer
     * @return string
     * @internal param bool $echo
     */
    public static function nonce_field($action = -1, $name = '_wpnonce', $referer = true)
    {
        $name = esc_attr($name);
        $return = '<input type="hidden" name="' . $name . '" value="' . wp_create_nonce($action) . '" />';

        if ($referer) {
            $return .= wp_referer_field(false);
        }

        return $return;
    }


    /**
     * @param string $content html content
     * @param string $type [error|info|warning|success]
     * @param bool $closable
     * @param bool $echo
     * @return string
     */
    public function notice($content, $type, $closable = false)
    {

        switch ($type) {
            case 'info':
                $type_class = 'bc2018fw-alert-primary';
                break;

            case 'success':
                $type_class = 'bc2018fw-alert-success';
                break;

            case 'warning':
                $type_class = 'bc2018fw-alert-warning';
                break;

            case 'error':
                $type_class = 'bc2018fw-alert-danger';
                break;

            default:
                $type_class = 'bc2018fw-alert-primary';
                break;

        }

        $closable = $closable ? '<a class="bc2018fw-alert-close" bc2018fw-close></a>' : '';

        return sprintf('<div class="%1$s" bc2018fw-alert> %2$s <p>%3$s</p> </div>', $type_class, $closable, $content);

    }


    /**
     * Returns an input text element
     *
     * @param $setting_field_name
     */
    public function hidden($setting_field_name)
    {
        $current_value = $this->get_option_value($setting_field_name, 'string');

        return sprintf('<input type="hidden" name="%1$s" value="%2$s" data-bc2018fw-field="%3$s" />', $this->generate_form_field($setting_field_name), $current_value, $setting_field_name);

    }

    public function raw_hidden($key, $value)
    {
        return sprintf('<input type="hidden" name="%1$s" value="%2$s" />', $key, $value);
    }

    /**
     * Echos an label element
     *
     * @param $field_id
     * @param string $text
     */
    public static function label($field_id, $text)
    {
        return sprintf('<label for="%1$s" class="bc2018fw-form-label">%2$s</label>', $field_id, $text);
    }

    /**
     * Echos an input text element
     *
     * @param $setting_field_name
     * @param string $type
     * @param bool $disabled
     * @return string
     */

    public function open_card($card_id = '', $card_class = '')
    {

        //generate a default card id if not available
        $card_id == '' ? uniqid('bc-card-') : $card_id;
        $html = "<!-- opening $card_id -->";
        $html .= '<div id="' . $card_id . '" class="bc2018fw-card bc2018fw-card-default bc2018fw-card-body ' . $card_class . '">';

        return $html;
    }

    public function close_card($card_id = '')
    {
        $html = '</div>';
        if ($card_id != '') {
            $html .= "<!-- closing $card_id -->";
        }
        return $html;
    }

    public function open_grid($text_center = true)
    {
        $text_center = $text_center ? 'bc2018fw-text-center' : '';
        return sprintf('<div class="bc2018fw-grid %1$s">', $text_center);
    }

    public function close_grid()
    {
        return '</div> <!-- close grid -->';
    }

    /**
     * @param $width auto | expand | 1-2 | 1-3| 1-4 | 1-5 | 1-6 | 2-3...
     * @return string
     */
    public function open_grid_box($width = 'auto') //width in px
    {
        $width_class = 'bc2018fw-width-' . $width;
        return sprintf('<div class="bc2018fw-grid-box %1$s">', $width_class);
    }

    public function close_grid_box()
    {
        return '</div> <!-- close grid box -->';
    }

    public function open_container()
    {
        return '<div class="bc2018fw">';//open a scope
    }

    public function close_container()
    {
        return '</div> <!-- close container -->';
    }

    public function open_form()
    {
        return '<form id="' . $this->form_css_id . '"> <!-- open form -->';
    }

    public function close_form()
    {
        return '</form> <!-- close form -->';
    }

    public function input_field($setting_field_name, $type = 'text', $label = '', $disabled = false, $width = 200)
    {

        $current_value = $this->get_option_value($setting_field_name);
        $disabled = $disabled ? 'disabled' : '';
        $html = '';
        if ($label != '')
            $html .= sprintf('<label class="bc2018fw-form-label" for="%1$s">%2$s</label>', $setting_field_name, $label);
        $html .= sprintf('<div class="bc2018fw-form-controls"><input class="bc2018fw-input" type="%1$s" id="%2$s" name="%2$s" value="%3$s" %4$s style="width: %5$s; data-bc2018fw-field="%6$s"/></div>', $type, $this->generate_form_field($setting_field_name), $current_value, $disabled, $width . 'px', $setting_field_name);

        return $html;
    }


    public function single_image_picker($setting_field_name, $button_title, $label, $disabled)
    {
        $disabled = $disabled ? 'disabled' : '';
        $current_value = $this->get_option_value($setting_field_name);
        $html = '<div class="bc2018fw-image-picker">';

        $label = $label != '' ? sprintf('<label class="bc2018fw-form-label">%1$s</label>', $label) : '';

        $html .= $label;

        $html .= '<div><img class="bc2018fw-image-preview" src="' . ($current_value > 0 ? wp_get_attachment_image_src($current_value)[0] : '') . '" /></div>';
        $html .= sprintf('<p><a class="bc2018fw-button-small bc2018fw-image-picker-button bc2018fw-button bc2018fw-button-primary" %1$s> <span bc2018fw-icon="image"></span> %2$s</a></p>', $disabled, $button_title);
        $html .= sprintf('<input type="hidden" id="%1$s" class="bc2018fw-image-picker-hidden-input" name="%1$s" value="%3$s" %4$s data-bc2018fw-field="%5$s"  />', $this->generate_form_field($setting_field_name), $this->option_name, $current_value, $disabled, $setting_field_name);

        return $html . '</div>';
    }


    /**
     * Generate a section where one key is associated with an associative array
     * _key => array(
     * 'key' => 'value'
     * )
     */
    public function key_select_select(
        $setting_field_name,
        $key_array,
        $values_array,
        $key_title,
        $value_title,
        $disabled = false)
    {
        //get the current value, this should be an associated array
        $current_value = $this->get_option_value($setting_field_name, 'array');


        if (count($current_value) == 0) {
            $current_value = array(
                '' => ''
            );
        }

        $html = '';
        foreach ($current_value as $key => $value) {


            $html .= self::flex_data_row(array(
                $this->raw_select($key_array, $key, false, $disabled),
                $this->raw_select($values_array, $value, false, $disabled)
            ), true, true);
        }


        //.bc-key-array-assoc-data-field: this is the container for two select fields
        //
        return sprintf('<div class="bc-key-select-select bc-key-array-assoc-data-field" data-name="%1$s" data-bc2018fw-field="%3$s">%2$s</div>', $this->generate_form_field($setting_field_name), $html, $setting_field_name);

    }


    private static function flex_data_row(array $content, $equal_width = false, $display_add_row = false, $display_minus_row = false)
    {
        $html = '';
        $add_sign = $display_add_row ? '<span class="add-data-row">+</span>' : '';
        $minus_sign = $display_add_row ? '<span class="minus-data-row">-</span>' : '';
        foreach ($content as $c) {
            $width_class = $equal_width ? 'bc2018fw-width-1-1' : '';
            $html .= $single_row = sprintf('<div class="%1$s">%2$s</div>', $width_class, $c);

        }


        //the class .bc-single-data-row will be used to collect data on a single row when the form is saved
        return sprintf('<div class="bc-single-data-row bc2018fw-flex">%1$s %2$s %3$s </div>', $html, $minus_sign, $add_sign);
    }


    /**
     * Echos an select element
     *
     * @param $setting_field_name
     * @param $values array associated array of value => text
     * @param bool $disabled
     * @return string
     */
    public function select($setting_field_name, $values, $label = '', $disabled = false, $multiple = false, $width = 400)
    {

        $current_value = $this->get_option_value($setting_field_name);


        $multiple_text = $multiple ? 'multiple' : '';

        $multiple_markup = $multiple ? '[]' : '';
        $disabled = $disabled ? 'disabled' : '';
        $html = sprintf('<select style="width: %6$s" class="bc2018fw-select" %2$s name="%1$s%4$s" %3$s data-bc2018fw-field="%5$s">',
            $this->generate_form_field($setting_field_name), $disabled, $multiple_text, $multiple_markup, $setting_field_name, $width . 'px');

        foreach ($values as $value => $text) {
            if (!$multiple)
                $selected = $value == $current_value ? 'selected' : '';
            else {
                if (is_array($current_value))
                    $selected = in_array($value, $current_value) ? 'selected' : '';
                else
                    $selected = '';
            }
            $html .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $value, $text, $selected);
        }

        if ($label != '')
            $html = sprintf('<div><label for="%1$s">%2$s</label></div>', $setting_field_name, $label) . $html;
        return '<div>'. $html . '</select></div>';
    }

    /**
     * @param string $content HTML content of the heading, usually just text
     * @param int $level heading level, similar to h1 to h6 but with smaller text. There are only three levels
     * with text size 38px, 24px and 18px
     *
     * @return string
     *
     */
    public function heading($content, $level = 1)
    {
        return sprintf('<div class="bc-doc-heading-%1$s">%2$s</div>', $level, $content);
    }


    /**
     * Echos a group of radio elements
     * values: value => label pair or
     *  value => array(label, disabled, postfix)
     * @param $setting_field_name
     * @param $values array of  associative array[['label' => '', 'value' => '', 'disabled' => false, 'postfix' => ''
     * @param string $layout
     * @param $label_type string either: text (normal text), image(image url), icon_font (icon class)
     * @param string $title
     * @param array $dimensions width and height of image or icon, default 16 x 16
     * @return string
     */
    public function radio($setting_field_name, $values, $layout = 'row', $title = '')
    {
        $current_value = $this->get_option_value($setting_field_name);
        $html = sprintf('<div class="bc2018fw-radio-group bc2018fw-radio-group-%1$s"><div>%2$s</div>', $layout, $title);
        foreach ($values as $singleRadio) {
            $value = $singleRadio['value'];
            $label = $singleRadio['label'];
            $disabled = $singleRadio['disabled'];

            $checked = $value == $current_value ? 'checked' : '';

            $radio = sprintf('<input class="bc2018fw-radio bc2018-single-radio" type="radio" name="%1$s" value="%2$s" %3$s %4$s data-bc2018fw-field="%5$s"/> ', $this->generate_form_field($setting_field_name), $value, $checked, $disabled, $setting_field_name);

            $html .= sprintf('<span>%1$s %2$s&nbsp;&nbsp;</span>', $radio, $label);
        }

        return $html . '</div>';

    }


    /**
     * Echos an input text element
     *
     * @param $setting_field_name
     * @param string $placeholder
     * @param bool $disabled
     */
    public function textarea($setting_field_name, $placeholder = '', $label = '', $disabled = false, $width = 400, $rows = 5)
    {
        $current_value = $this->get_option_value($setting_field_name);

        $disabled = $disabled ? 'disabled' : '';
        $template = '<div>
                 <div><label>%8$s</label></div>
                <textarea  style="width: %6$s" rows="%7$s" name="%1$s" placeholder="%4$s" class="bc2018fw-textarea"  %3$s data-bc2018fw-field="%5$s">%2$s</textarea></div>';
        return sprintf($template, $this->generate_form_field($setting_field_name), $current_value, $disabled, $placeholder, $setting_field_name, $width.'px', $rows, $label);
    }


    /**
     * Echos an input checkbox element
     *
     * @param $setting_field_name
     * @param bool $disabled
     * @param string $label
     * @return string
     */
    public function single_checkbox($setting_field_name, $disabled = false, $label = '')
    {

        $current_value = $this->get_option_value($setting_field_name, 'array');

        $disabled = $disabled ? 'disabled' : '';
        $state = checked(1, count($current_value) > 0 ? $current_value[0] : 0, false);
        return
            sprintf('<label class="bc2018fw-form-label" for="%1$s">
                            <input type="checkbox" name="%1$s" %2$s %3$s class="bc2018fw-checkbox" value="1" id="%2$s" data-bc2018fw-field="%5$s" /> %4$s &nbsp;&nbsp;</label>',
                $this->generate_form_field($setting_field_name), $state, $disabled, $label, $setting_field_name);


    }

    public function multiple_checkbox($setting_field_name, $disabled = false, $label = '', $values = array())
    {
        $current_value = $this->get_option_value($setting_field_name, 'array');

        $disabled = $disabled ? 'disabled' : '';
        $html = '';
        foreach ($values as $value) {

            error_log('typeof value' . gettype($value));
            $checked = in_array($value, $current_value) ? 'checked' : '';
            $html .= sprintf('<label class="bc2018fw-form-label" for="%1$s">
                            <input type="checkbox" name="%1$s[]" %2$s %3$s class="bc2018fw-checkbox" value="%4$s" id="%1$s" data-bc2018fw-field="%5$s" /> %4$s &nbsp;&nbsp;</label>',
                $this->generate_form_field($setting_field_name), $checked, $disabled, $value, $setting_field_name);
        }
        return $html;
    }

    public function hr()
    {
        return '<hr class="bc2018fw-hr" />';
    }

    public function submit_button($text)
    {
        return sprintf('<span><button data-bcfw-form-id="' . $this->form_css_id . '" name="submit"  type="submit" class="bc2018fw-button-primary  bc2018fw-button bc2018fw-form-submit-button" ><span class="bc2018fw-spinner" style="display: none;" bc2018fw-spinner="ratio: 0.5"></span> %1$s</button></div>', $text);
    }

}