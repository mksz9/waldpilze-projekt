<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}


echo gcms_cap_captcha::getInstance()->getCaptachaImageUrl();