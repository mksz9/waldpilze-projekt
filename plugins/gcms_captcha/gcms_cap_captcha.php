<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_cap_captcha
{
    const session_name = 'captchacode';
    private $options;

    static private $instance = null;

    static public function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    function initRedirection()
    {
        session_start();
        add_filter('template_include', array($this, 'my_callback'));
    }

    function my_callback($original_template)
    {
        if (isset($_GET['captcha']) && $_GET['captcha'] == true) {
            return plugin_dir_path(__FILE__) . '\gcms_cap_captchaImage.php';
        } else {
            return $original_template;
        }
    }

    function isValidCaptchaText($input)
    {
        return $_SESSION[self::session_name] === hash('sha256', $input);
    }

    function getCaptachaImageUrl()
    {
        //return plugin_dir_url( __FILE__ ).'gcms_cap_captchaImage.php';
        return $_SERVER['PHP_SELF'] . '?captcha=true';
    }

    public function generateCaptchaImage()
    {
        $this->options = get_option(gcms_cap_constant::captcha_options);

        //error_reporting(E_ALL);
        $captchaText = $this->generateCaptchaText();
        $captchaImage = $this->generatePNG($captchaText);

        header("Cache-Control: no-cache, must-revalidate");
        header("Content-type: image/png"); // Header für ein PNG Bild setzen

        $_SESSION[self::session_name] = hash('sha256', $captchaText); // Den Code in die Session mit dem Sessionname speichern für die Überprüfung

        imagepng($captchaImage); // Ausgaben des Bildes
        imagedestroy($captchaImage);
    }

    private function generatePNG($catchaText)
    {
        $imageWidth = intval($this->options[gcms_cap_constant::captcha_width]);
        $imageHeight = intval($this->options[gcms_cap_constant::captcha_height]);
        $fontSize = intval($this->options[gcms_cap_constant::captcha_textSize]);

        $font = plugin_dir_path(__FILE__) . "Squid.ttf"; // TTF Schriftart für Captcha

        $captchaImage = imagecreate($imageWidth, $imageHeight);
        imagecolorallocate($captchaImage, 255, 255, 255);

        for ($i = 0; $i < strlen($catchaText); $i++) {
            $y = 45 + rand(-4, 4);
            $x = ($imageWidth / (2 * strlen($catchaText) + 1)) * (2 * $i + 1);
            imagettftext($captchaImage, $fontSize, rand(-20, 20), $x, $y, imagecolorallocate($captchaImage, 69, 103, 137), $font, $catchaText[$i]);
        }
        return $captchaImage;
    }

    private function generateCaptchaText()
    {
        $signs = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNpPqQrRsStTuUvVwWxXyYzZ123456789';

        $captchaText = '';
        $letterCount = intval($this->options[gcms_cap_constant::captcha_letterCount]);

        for ($i = 1; $i <= $letterCount; $i++) {
            $sign = $signs{rand(0, strlen($signs) - 1)};
            $captchaText .= $sign;
        }

        return $captchaText;
    }
}

