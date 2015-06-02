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
        add_filter('template_include', array($this, 'my_callback'));
    }

    function my_callback($original_template)
    {
        if (isset($_GET['captcha']) && $_GET['captcha'] == true) {
            return plugin_dir_path(__FILE__) . 'gcms_cap_captchaImage.php';
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
        return $_SERVER['PHP_SELF'] . '?captcha=true';
    }

    public function generateCaptchaImage()
    {
        //error_reporting(E_ALL);
        $this->options = get_option(gcms_cap_constant::captcha_options);

        $captchaText = $this->generateCaptchaText();
        $captchaImage = $this->generatePNG($captchaText);

        header("Cache-Control: no-cache, must-revalidate");
        header("Content-type: image/png"); // Header für ein PNG Bild setzen

        $_SESSION[self::session_name] = hash('sha256', $captchaText); // Den Code in die Session mit dem Sessionname speichern für die Überprüfung

        imagepng($captchaImage); // Ausgaben des Bildes
        imagedestroy($captchaImage);
    }

    private function generatePNG($captchaText)
    {
        $imageWidth = intval($this->options[gcms_cap_constant::captcha_width]);
        $imageHeight = intval($this->options[gcms_cap_constant::captcha_height]);
        $fontSize = intval($this->options[gcms_cap_constant::captcha_textSize]);

        $font = plugin_dir_path(__FILE__) . "Squid.ttf"; // TTF Schriftart für Captcha

        $captchaImage = imagecreate($imageWidth, $imageHeight);
        imagecolorallocate($captchaImage, 255, 255, 255);

        $this->drawCaptchaText($captchaText, $imageWidth, $captchaImage, $fontSize, $font);

        $this->drawRandomLines($imageWidth, $imageHeight, $captchaImage);

        return $captchaImage;
    }


    private function generateCaptchaText()
    {
        //all letters an numbers without i,o
        $signs = 'aAbBcCdDeEfFgGhHIjJkKlLmMnNpPqQrRsStTuUvVwWxXyYzZ123456789';
        $captchaText = '';
        $letterCount = intval($this->options[gcms_cap_constant::captcha_letterCount]);

        for ($i = 1; $i <= $letterCount; $i++) {
            $sign = $signs{rand(0, strlen($signs) - 1)};
            $captchaText .= $sign;
        }

        return $captchaText;
    }


    private function drawCaptchaText($captchaText, $imageWidth, $captchaImage, $fontSize, $font)
    {
        for ($i = 0; $i < strlen($captchaText); $i++) {
            $y = 45 + rand(-4, 4);
            $x = ($imageWidth / (2 * strlen($captchaText) + 1)) * (2 * $i + 1);
            imagettftext($captchaImage, $fontSize, rand(-20, 20), $x, $y, imagecolorallocate($captchaImage, 69, 103, 137), $font, $captchaText[$i]);
        }
    }

    private function drawRandomLines($imageWidth, $imageHeight, $captchaImage)
    {
        for ($i = 0; $i < 5; $i++) {
            $x1 = rand(0, $imageWidth - 1);
            $x2 = rand(0, $imageWidth - 1);
            $y1 = rand(0, $imageHeight - 1);
            $y2 = rand(0, $imageHeight - 1);

            imageline($captchaImage, $x1, $y1, $x2, $y2, imagecolorallocate($captchaImage, 69, 103, 137));
        }

        for ($i = 0; $i < 400; $i++) {
            $x1 = rand(4, $imageWidth - 4);
            $x2 = $x1 + rand(-3, 3);
            $y1 = rand(4, $imageHeight - 4);
            $y2 = $y1 + rand(-3, 3);

            imageline($captchaImage, $x1, $y1, $x2, $y2, imagecolorallocate($captchaImage, 69, 103, 137));
        }
    }
}

