<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}


class gcms_cap_captcha
{
    const sesssion_captchaFileName = 'captchaFileName';
    private $options;
    private $captchaUploadsDir;
    private $filePrefix;

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
        add_action('captchaHourlyEvent', 'deleteFilesOlderThenOneHour');

        $this->captchaUploadsDir = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'gcms_captcha' . DIRECTORY_SEPARATOR;

        $accessFile = $this->captchaUploadsDir . '.htaccess';
        if (!file_exists($accessFile)) {
            file_put_contents($accessFile, '
Order deny,allow
Deny from all
<Files ~ "^[0-9a-z]+\.png$">
    Allow from all
</Files>

');
        }
    }

    private function __clone()
    {
    }

    public function deleteFilesOlderThenOneHour()
    {
        foreach (array_merge(glob($this->captchaUploadsDir . "*.captchaKey"),
            glob($this->captchaUploadsDir . "*.png")) as $file) {
            if (filemtime($file) < time() - 86400) {
                unlink($file);
            }
        }
    }

    public function isValidCaptchaText($captchaText)
    {
        $this->filePrefix = $_SESSION[self::sesssion_captchaFileName];

        $hashFile = $this->captchaUploadsDir . $this->filePrefix . '.captchaKey';

        $fileContent = file_get_contents($hashFile);

        unlink($hashFile);
        unlink($this->captchaUploadsDir . $this->filePrefix . '.png');

        $inputHash = $this->createCaptchaHash($captchaText);

        if (strlen($fileContent) != 32) {
            return false;
        }

        if ($fileContent === $inputHash) {
            return true;
        }

        return false;
    }

    public function getCaptachaImageUrl()
    {
        $this->generateCaptchaImage();
        return esc_url(wp_upload_dir()['baseurl'] . '/gcms_captcha/' . $this->filePrefix . '.png');
    }

    private function generateCaptchaImage()
    {
        $this->options = get_option(gcms_cap_constant::captcha_options);
        $this->createFilePrefix();

        $captchaText = $this->generateCaptchaText();
        $captchaImage = $this->generatePNG($captchaText);

        $this->saveCaptchaTextFile($captchaText);
        $this->saveCaptchaImage($captchaImage);

        imagedestroy($captchaImage);
    }

    public function createCaptchaUploadDir()
    {
        if (!is_dir($this->captchaUploadsDir)) {
            $result = mkdir($this->captchaUploadsDir);
            if ($result === false) {
                trigger_error('The Plugin can´t create folder:' . $this->captchaUploadsDir, E_USER_ERROR);
            }
        }
    }

    private function saveCaptchaTextFile($captchaText)
    {
        $hashFile = $this->captchaUploadsDir . $this->filePrefix . '.captchaKey';
        file_put_contents($hashFile, $this->createCaptchaHash($captchaText));
    }

    private function saveCaptchaImage($captchaImage)
    {
        $path = $this->captchaUploadsDir . $this->filePrefix . '.png';
        imagepng($captchaImage, $path);
    }

    private function createCaptchaHash($data)
    {
        return wp_hash($data, 'nonce');
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
            $sign = $signs{mt_rand(0, strlen($signs) - 1)};
            $captchaText .= $sign;
        }

        return $captchaText;
    }

    private function drawCaptchaText($captchaText, $imageWidth, $captchaImage, $fontSize, $font)
    {
        for ($i = 0; $i < strlen($captchaText); $i++) {
            $y = 45 + mt_rand(-4, 4);
            $x = ($imageWidth / (2 * strlen($captchaText) + 1)) * (2 * $i + 1);
            imagettftext($captchaImage, $fontSize, mt_rand(-20, 20), $x, $y, imagecolorallocate($captchaImage, 69, 103, 137), $font, $captchaText[$i]);
        }
    }

    private function drawRandomLines($imageWidth, $imageHeight, $captchaImage)
    {
        for ($i = 0; $i < 5; $i++) {
            $x1 = mt_rand(0, $imageWidth - 1);
            $x2 = mt_rand(0, $imageWidth - 1);
            $y1 = mt_rand(0, $imageHeight - 1);
            $y2 = mt_rand(0, $imageHeight - 1);

            imageline($captchaImage, $x1, $y1, $x2, $y2, imagecolorallocate($captchaImage, 69, 103, 137));
        }

        for ($i = 0; $i < intval($this->options[gcms_cap_constant::captcha_disturbance]); $i++) {
            $x1 = mt_rand(4, $imageWidth - 4);
            $x2 = $x1 + rand(-3, 3);
            $y1 = mt_rand(4, $imageHeight - 4);
            $y2 = $y1 + mt_rand(-3, 3);

            imageline($captchaImage, $x1, $y1, $x2, $y2, imagecolorallocate($captchaImage, 69, 103, 137));
        }
    }

    private function createFilePrefix()
    {
        $this->filePrefix = $this->createCaptchaHash(uniqid());
        $_SESSION[self::sesssion_captchaFileName] = $this->filePrefix;
    }
}

