<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_formPrinterAndReader
{
    const input_submit_name = 'pf_submit';
    const input_title_name = 'pf_name';
    const input_title_content = 'pf_content';
    const input_thumbnail = 'pf_thumbnail';
    const input_nonce_filed = 'pf_nonce_field';
    const input_toxic_name = 'pf_toxic';

    public function printHtmlFormWithValidationError($errorMessages)
    {
        echo $errorMessages;
        $this->printHtmlForm();

    }

    public function printHtmlForm()
    {
        $data = $this->getFromRawData();

        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" enctype="multipart/form-data">';

        wp_nonce_field(self::input_submit_name, self::input_nonce_filed);

        echo '<p>';
        echo 'Name: <br />';
        echo '<input type="text" name="' . self::input_title_name . '" pattern="[a-zA-Z0-9 öäüÖÜÄ]+" value="' . esc_attr($data->getTitle()) . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Giftig oder giftig: <br />';
        echo '<input type="radio" id="toxic" name="' . self::input_toxic_name . '" value="giftig"><label for="toxic"> Giftig</label><br> ';
        echo '<input type="radio" id="atoxic" name="' . self::input_toxic_name . '" value="ungiftig"><label for="atoxic">  Ungiftig</label><br> ';
        echo ' </p>';

        echo '<p>';
        echo 'Beschreibung: <br />';
        echo '<textarea type="text" name="' . self::input_title_content . '" pattern="[a-zA-Z0-9 ]+" size="200" >' . esc_attr($data->getContent()) . '</textarea>';
        echo '</p>';

        echo '<p>';
        echo '<input type="file" name="' . self::input_thumbnail . '" multiple="false" />';
        echo '</p>';

        echo '<img src="' . esc_url(gcms_cap_captcha::getInstance()->getCaptachaImageUrl()) . ' alt="" />';
        echo '<p>Captcha: <br /><input type="text" name="captcha" id="captcha" autocomplete="off" /></p>';

        echo '<p><input type="submit" name="' . self::input_submit_name . '" value="Pilz absenden"/></p>';
        echo '</form>';
    }

    public function hasSubmited()
    {
        if (isset($_POST[self::input_submit_name])) {
            return true;
        }

        return false;
    }

    public function hasPostValidData()
    {
        if ($this->getInvalidDataMessage() === false) {
            return true;
        }

        return false;
    }

    public function getInvalidDataMessage()
    {
        $hasError = false;
        $resultMessage = '<ul>';
        $data = $this->getFromRawData();

        if (strlen($data->getTitle()) < 6) {
            $resultMessage .= '<li>Der Title muss mindestens 6 Zeichen lang sein.</li>';
            $hasError = true;
        }

        if (strlen($data->getContent()) < 50) {
            $resultMessage .= '<li>Der Content muss mindestens 50 Zeichen lang sein.</li>';
            $hasError = true;
        }

        if (!(gcms_cap_captcha::getInstance()->isValidCaptchaText(trim($_POST['captcha'])))) {
            $resultMessage .= '<li>Der Captcha-Inhalt stimmt nicht mit dem Bild überein.</li>';
            $hasError = true;
        }

        if($_FILES[self::input_thumbnail]['size'] == 0)
        {
            $resultMessage .= '<li>Sie müssen ein Bild angben.</li>';
            $hasError = true;
        }
        else
        {
            $fileType = strtolower($_FILES[self::input_thumbnail]['type']);
            if(!($fileType == 'image/jpeg' || $fileType == 'image/png'))
            {
                $resultMessage .= '<li>Das Bild muss eine jpg, jpeg oder png Datei sein.</li>';
                $hasError = true;
            }
        }

        if (!(isset($_POST[self::input_nonce_filed]) && isset($_POST[self::input_submit_name]) &&
            wp_verify_nonce($_POST[self::input_nonce_filed], self::input_submit_name) === 1)
        ) {
            $resultMessage .= '<li>Es ist ein interner Fehler aufgetreten.</li>';
            $hasError = true;
        }

        $resultMessage .= '</ul>';

        if ($hasError === false) {
            return false;
        } else {
            return $resultMessage;
        }
    }

    public function getFromData()
    {
        if ($this->hasPostValidData() === true) {
            return $this->getFromRawData();
        }

        return null;
    }

    private function getFromRawData()
    {
        $data = new gcms_pf_formData();

        if (isset($_POST[self::input_title_content])) {
            $data->setContent(sanitize_text_field(trim($_POST[self::input_title_content])));
        }

        if (isset($_POST[self::input_title_name])) {
            $data->setTitle(sanitize_text_field(trim($_POST[self::input_title_name])));
        }


        return $data;
    }
}

