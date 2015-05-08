<?php

class gcms_pf_formData
{
    private $content = '';
    private $title = '';

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }
}