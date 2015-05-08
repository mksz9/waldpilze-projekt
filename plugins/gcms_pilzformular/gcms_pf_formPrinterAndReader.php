<?php


class gcms_pf_formPrinterAndReader
{
    const input_submit_name = 'pf_submit';
    const input_title_name = 'pf_name';
    const input_title_content = 'pf_content';
    const input_nonce_filed = 'pf_nonce_field';
    const input_toxic_name = 'pf_toxic';

    function printHtmlForm()
    {
        $data = $this->getFromRawData();

        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';

        wp_nonce_field(self::input_submit_name, self::input_nonce_filed);

        echo '<p>';
        echo 'Name: <br />';
        echo '<input type="text" name="' . self::input_title_name . '" pattern="[a-zA-Z0-9 ]+" value="' . esc_attr($data->getTitle())  . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Giftig oder giftig: <br />';
        echo '<input type="radio" id="toxic" name="' . self::input_toxic_name . '" value="giftig"><label for="toxic"> Giftig</label><br> ';
        echo '<input type="radio" id="atoxic" name="' . self::input_toxic_name . '" value="ungiftig"><label for="atoxic">  Ungiftig</label><br> ';
        echo ' </p>';

        echo '<p>';
        echo 'Beschreibung: <br />';
        echo '<textarea type="text" name="' . self::input_title_content . '" pattern="[a-zA-Z0-9 ]+" value="' . esc_attr($data->getContent())  . '" size="200" ></textarea>';
        echo '</p>';

        echo '<p><input type="submit" name="' . self::input_submit_name . '" value="Pilz absenden"/></p>';
        echo '</form>';
    }

    function hastPostContent()
    {
        return isset($_POST[self::input_submit_name]);
    }

    function getFromData()
    {
        if (isset($_POST[self::input_nonce_filed]) && isset($_POST[self::input_submit_name]) &&
            wp_verify_nonce($_POST[self::input_nonce_filed], self::input_submit_name) === 1
        ) {
            return $this->getFromRawData();
        }

        echo '<h2>Sorry, your nonce did not verify.</h2>';
        exit;
    }

    private function getFromRawData()
    {
        $data = new gcms_pf_formData();

        if (isset($_POST[self::input_title_content])) {
            $data->setContent(sanitize_text_field($_POST[self::input_title_content]));
        }

        if (isset($_POST[self::input_title_name])) {
            $data->setTitle(sanitize_text_field($_POST[self::input_title_name]));
        }

        return $data;
    }
}

