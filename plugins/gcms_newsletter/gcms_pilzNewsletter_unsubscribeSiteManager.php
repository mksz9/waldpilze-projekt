<?php

    class gcms_pilzNewsletter_unsubscribeSiteManager
    {
        const title = 'Unsubscribe';

        function __construct()
        {

        }

        function getURLOfUnsubscribeSite()
        {
            return get_page_by_path(self::title)->guid;
        }

        function addUnsubscribeInfoPageToWordpressPages()
        {
            global $user_ID;

            $page['post_type']    = 'page';
            $page['post_content'] = 'You are no more recipient from our newsletter!';
            $page['post_parent']  = 0;
            $page['post_author']  = $user_ID;
            $page['post_status']  = 'publish';
            $page['post_title']   = self::title;

            if(!$this->pageAlreadyExists())
            {
                wp_insert_post ($page);
            }
        }

        function pageAlreadyExists()
        {
            if(get_page_by_title(self::title) != NULL)
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