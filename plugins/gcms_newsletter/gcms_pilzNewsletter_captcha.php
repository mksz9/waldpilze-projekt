<?php

    class gcms_pilzNewsletter_captcha
    {
        function printCaptcha($captchaValueName)
        {
            ?>
                <img src="<?php echo esc_url(gcms_cap_captcha::getInstance()->getCaptachaImageUrl()) ?>" />
                <p>Captcha:<br /><input name="<?php echo $captchaValueName ?>" type="text" autocomplete="off" /></p>
            <?php
        }

        function isCaptchaPluginActive()
        {
            return class_exists('gcms_cap_captcha');
        }

        function isCaptchaValid($captchaValueName)
        {
            return gcms_cap_captcha::getInstance()->isValidCaptchaText(trim($_POST[$captchaValueName]));
        }
    }

?>