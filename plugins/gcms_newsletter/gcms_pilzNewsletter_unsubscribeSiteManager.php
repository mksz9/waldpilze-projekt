<?php

    class gcms_pilzNewsletter_unsubscribeSiteManager
    {
        private $title;

        function __construct()
        {
            $this->title = __('Unsubscribe', 'gcms_newsletter');
        }

        function getURLOfUnsubscribeSite()
        {
            return get_page_by_path($this->title)->guid;
            //return get_page_by_title(self::title)->guid;
        }

        function addUnsubscribeInfoPageToWordpressPages()
        {
            global $user_ID;

            $page['post_type']    = 'page';
            $page['post_content'] = __('You are no more recipient from our newsletter!', 'gcms_newsletter');
            $page['post_parent']  = 0;
            $page['post_author']  = $user_ID;
            $page['post_status']  = 'publish';
            $page['post_title']   = $this->title;

            if(!$this->pageAlreadyExists())
            {
                wp_insert_post ($page);
            }
        }

        function pageAlreadyExists()
        {
            if(get_page_by_title($this->title) != NULL)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

?>