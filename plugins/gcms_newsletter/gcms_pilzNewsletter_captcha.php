<?php

    class gcms_pilzNewsletter_captcha
    {
        function printCaptcha($captchaValueName)
        {
            ?>
                <div> 
                <p>Captcha:<br /></p>
                <p><img src="<?php echo esc_url(gcms_cap_captcha::getInstance()->getCaptachaImageUrl()) ?>" /></p>
                <p><input name="<?php echo $captchaValueName ?>" type="text" autocomplete="off" /></p>
                </div>
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
