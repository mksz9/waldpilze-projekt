<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_cap_constant {
    const captcha_options = 'captcha_options';
    const captcha_height = 'captcha_height';
    const captcha_width = 'capcha_width';
    const captcha_letterCount = 'captcha_letterCount';
    const captcha_textSize = 'captcha_textSize';
    const captcha_disturbance = 'captcha_disturbance';

    const captcha_localization = 'gcmsCaptcha';
}