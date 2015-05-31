<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_validationResult
{
    private $hasError = false;
    private $message = '';

    public function appendErrorMessage($message)
    {
        $this->message = $this->message . $message;
    }

    public function setError()
    {
        $this->hasError = true;
    }

    public function hasError()
    {
        return $this->hasError;
    }

    public function getMessage()
    {
        return $this->message;
    }


}