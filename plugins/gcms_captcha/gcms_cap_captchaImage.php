<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

include_once('gcms_cap_captcha.php');

gcms_cap_captcha::getInstance()->generateCaptchaImage();