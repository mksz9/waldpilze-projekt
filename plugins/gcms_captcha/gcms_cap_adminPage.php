<?php

class gcms_cap_adminPage
{
    const captchaSettingsAdminPage = 'captcha_settings_admin_page';

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Captcha',
            'manage_options',
            self::captchaSettingsAdminPage,
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option(gcms_cap_constant::captcha_options);
        ?>
        <div class="wrap">
            <h2>My Settings</h2>

            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('my_option_group');
                do_settings_sections(self::captchaSettingsAdminPage);
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        $captchaSettingSection = 'captcha_setting_section';

        register_setting(
            'my_option_group', // Option group
            gcms_cap_constant::captcha_options, // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            $captchaSettingSection, // ID
            'My Custom Settings', // Title
            array($this, 'print_section_info'), // Callback
            self::captchaSettingsAdminPage // Page
        );

        add_settings_field(
            gcms_cap_constant::captcha_height, // ID
            'Captcha Image Width', // Title
            array($this, 'id_number_callback'), // Callback
            self::captchaSettingsAdminPage, // Page
            $captchaSettingSection // Section
        );

        add_settings_field(
            gcms_cap_constant::captcha_width,
            'Captcha Image Height',
            array($this, 'title_callback'),
            self::captchaSettingsAdminPage,
            $captchaSettingSection
        );

        add_settings_field(
            gcms_cap_constant::captcha_textSize,
            'Captcha Text Size',
            array($this, 'textSize_callback'),
            self::captchaSettingsAdminPage,
            $captchaSettingSection
        );


        add_settings_field(
            gcms_cap_constant::captcha_letterCount,
            'Captcha Letter Count',
            array($this, 'letterCount_callback'),
            self::captchaSettingsAdminPage,
            $captchaSettingSection
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input[gcms_cap_constant::captcha_width]))
            $new_input[gcms_cap_constant::captcha_width] = absint($input[gcms_cap_constant::captcha_width]);

        if (isset($input[gcms_cap_constant::captcha_height]))
            $new_input[gcms_cap_constant::captcha_height] = absint($input[gcms_cap_constant::captcha_height]);

        if (isset($input[gcms_cap_constant::captcha_textSize]))
            $new_input[gcms_cap_constant::captcha_textSize] = absint($input[gcms_cap_constant::captcha_textSize]);

        if (isset($input[gcms_cap_constant::captcha_letterCount]))
            $new_input[gcms_cap_constant::captcha_letterCount] = absint($input[gcms_cap_constant::captcha_letterCount]);

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    public function id_number_callback()
    {
        printf(
            '<input type="number" id="' . gcms_cap_constant::captcha_width . '" name="' . gcms_cap_constant::captcha_options . '[' . gcms_cap_constant::captcha_width . ']' . '" value="%s" />',
            isset($this->options[gcms_cap_constant::captcha_width]) ? esc_attr($this->options[gcms_cap_constant::captcha_width]) : ''
        );
    }

    public function title_callback()
    {
        printf(
            '<input type="v" id="' . gcms_cap_constant::captcha_height . '" name="' . gcms_cap_constant::captcha_options . '[' . gcms_cap_constant::captcha_height . ']' . '" value="%s" />',
            isset($this->options[gcms_cap_constant::captcha_height]) ? esc_attr($this->options[gcms_cap_constant::captcha_height]) : ''
        );
    }

    public function textSize_callback()
    {
        printf(
            '<input type="number" id="' . gcms_cap_constant::captcha_textSize . '" name="' . gcms_cap_constant::captcha_options . '[' . gcms_cap_constant::captcha_textSize . ']' . '" value="%s" />',
            isset($this->options[gcms_cap_constant::captcha_textSize]) ? esc_attr($this->options[gcms_cap_constant::captcha_textSize]) : ''
        );
    }

    public function letterCount_callback()
    {
        printf(
            '<input type="number" id="' . gcms_cap_constant::captcha_letterCount . '" name="' . gcms_cap_constant::captcha_options . '[' . gcms_cap_constant::captcha_letterCount . ']' . '" value="%s" />',
            isset($this->options[gcms_cap_constant::captcha_letterCount]) ? esc_attr($this->options[gcms_cap_constant::captcha_letterCount]) : ''
        );
    }
}

