<?php
/*
 * Plugin Name: Mini-ForumV2
 * Plugin URI: localhost/wordpress
 * Description: Stellt eine Kommunikationsplattform dar, wo sich Benutzer gegenseitig helfen können beim Identifizieren und Klassifizieren von Pilzen
 * Version: 0.2
 */

defined( 'ABSPATH' ) or die( 'Zugriff nur innerhalb von Wordpress gestattet.' );

add_action( 'init', 'mfv2_create_post_type');
add_filter( 'the_content', 'mfv2_saveCommentIdAsSolution' );
add_action( 'add_meta_boxes', 'mfv2_status_addMetabox');
add_action( 'save_post', 'mfv2_save_metaboxes');
add_filter( 'comments_array' , 'mfv2_modify_comments' , 10, 2 );


function mfv2_create_post_type() {
    register_post_type( 'MiniForumV2-Beitrag',
        array(
            'labels' => array (
                'name' => __( 'MiniForumV2-Beiträge' ),
                'singular_name' => __( 'MiniForumV2-Beitrag' ),
                'menu_name' => __( 'MiniForumV2' ),
                'add_new_item' => __( 'Neuen MiniForumV2-Beitrag erstellen' ),
                'edit_item' => __( 'MiniForumV2-Beitrag bearbeiten' ),
            ),
            'description' => 'MiniForumV2-Beiträge unterstützen Anwender bei der Pilzidentifikation.',
            'public' => true,
            'show_ui' => true,
            'has_archive' => true,
            'supports' => array(
                'title', 'editor', 'author', 'comments', 'revisions', 'custom-fields'
            )
        )
    );

}

function mfv2_saveCommentIdAsSolution($content) {
    $commentID_solution = (isset($_GET["markCommentAsSolution"]) && !empty($_GET["markCommentAsSolution"])) ? $_GET["markCommentAsSolution"] : false;
    $postID = (isset($_GET["postID"]) && !empty($_GET["postID"])) ? $_GET["postID"] : false;

    if($commentID_solution !== false && $postID !== false){
        $post = get_post($postID);
        if($post == null) {
            return;
        }

        $currentUser = get_current_user_id();
        $postCreator = intval($post->post_author);

        if($postCreator === $currentUser){
            update_post_meta($postID, '_isSolved', 'on', false);
            update_post_meta($postID, '_solution_commentID', $commentID_solution, false);
        }
        else {
            $content = 'Fehler: Sie sind nicht der Author von diesem Posts. <br/>' . $content;
        }
    }
    return $content;
}

function mfv2_status_addMetabox() {
    add_meta_box('status', 'Aktueller Status', 'mfv2_printStatus', 'MiniForumV2-Beitrag', 'side');
}

function mfv2_printStatus()  {
    $status = get_post_meta(get_the_ID(), '_isSolved', true);
    echo   '<label for="isSolved">';

    if($status == 'on'){
        echo '<input type="checkbox" name="isSolved" id="isSolved" checked> Gelöst <br/>';
        echo '</label> <br/>';

        $currentCommentMarkedAsSolution = get_post_meta(get_the_ID(), '_solution_commentID', true);
        if($currentCommentMarkedAsSolution != "") {
            echo 'Lösung (KommentarID):<br/>' . $currentCommentMarkedAsSolution;
        }

    }
    else {
        echo '<input type="checkbox" name="isSolved" id="isSolved"> Gelöst';
        echo '</label> <br/> <br/>';
    }


}

function mfv2_save_metaboxes() {
    $postID = $_POST['post_ID'];
    $solved = $_POST['isSolved'];
    update_post_meta($postID, '_isSolved', $solved, false);
    if($solved == null){ // if checkbox is unchecked -> then delete the saved solution_commentID
        update_post_meta($postID, '_solution_commentID', "", false);
    }


}

function mfv2_modify_comments($comments , $post_id) {
    $postType = get_post_type();
    foreach($comments as $comment){
        if($postType == 'miniforumv2-beitrag'){
            // Link erstellen, damit ein Kommentar als richtig markiert werden kann
            $currentUser = get_current_user_id();
            global $post;
            $postCreator = intval($post->post_author);

            if($postCreator === $currentUser){
                $arr_params = array(    'markCommentAsSolution' => $comment->comment_ID,
                    'postID' => $comment->comment_post_ID);
                $url = esc_url( add_query_arg( $arr_params) );
                $link = '<small><p align="right"><a href="'.$url.'">Kommentar als Lösung markieren</a></p></small>';
                $comment->comment_content = $comment->comment_content . $link ;
            }


            // Bild anzeigen, falls ein Kommentar als richtig markiert ist
            $solution_commentID = get_post_meta(get_the_ID(), '_solution_commentID', true);
            if($solution_commentID == $comment->comment_ID) {
                $pictureURL = plugins_url( 'solved.png', __FILE__ );
                $picture = '<img src="'.$pictureURL.'" align="middle"/>';
                $comment->comment_content = $comment->comment_content . '<br/><br/>' . $picture . ' Diese Antwort wurde als richtig markiert.';
            }

        }
    }
    return $comments;
}




class MiniForumV2_Widget extends WP_Widget {

    function MiniForumV2_Widget() {
        // Instantiate the parent object
        parent::__construct( false, 'Neuste MiniForumV2-Beiträge' );
    }

    function widget( $args, $instance ) {
        // Widget output
        $posts = get_posts(
            array('post_type' => 'miniforumv2-beitrag',
                'posts_per_page' => $instance['count'],
                'orderby' => 'date',
                'paged' => $instance['beginAtPosition'],
                'order' => 'DESC'
            )
        );

        global $post;
        echo'<aside id="miniForumV2-Einträge" class="widget widget_recent_entries">		<h2 class="widget-title">Letzte MiniForumV2-Beiträge</h2>		<ul>';
            foreach($posts as $post){
                setup_postdata($post);
                $status = get_post_meta(get_the_ID(), '_isSolved', true);
                $picture = '';
                if($status == 'on') { //solved -> show solved.picture
                    $pictureURL = plugins_url( 'solved.png', __FILE__ );
                    $picture = '<img src="'.$pictureURL.'" align="middle" height="20" width="20"/>';
                }
                else { // not solved -> show notSolved.picture
                    $pictureURL = plugins_url( 'notsolved.png', __FILE__ );
                    $picture = '<img src="'.$pictureURL.'" align="middle" height="20" width="20"/>';
                }
                echo $picture . '<a href="'.get_permalink().'">'.get_the_title().'</a><br/>';
            }
        echo'</ul>
             </aside>';
    }

    function update( $new_instance, $old_instance ) {
        // Save widget options
        return $new_instance;
    }

    function form( $instance ) {
        // Output admin widget options form
        global $wpdb;

        $instance = wp_parse_args(
            (array) $instance, array(
                'title' => 'MiniForum-Eintrag',
                'count' => 5,
                'beginAtPosition' => 0
            )
        );

        $title = esc_attr($instance['title']);
        $count = intval($instance['count']);
        $beginAtPosition = intval($instance['beginAtPosition']);

        echo '  <p>
                    <label for="'.$this->get_field_id( 'count' ).'">Anzahl der angezeigten Beiträge</label>
                    <input type="text" id="'.$this->get_field_id( 'count' ).'" name="'.$this->get_field_name( 'count' ).'" value="'.$count.'" size="3" />
                </p>';

        echo '  <p>
                    <label for="'.$this->get_field_id( 'beginAtPosition' ).'">Anfangsposition</label>
                    <input type="text" id="'.$this->get_field_id( 'beginAtPosition' ).'" name="'.$this->get_field_name( 'beginAtPosition' ).'" value="'.$beginAtPosition.'" size="3" />
                </p>';
    }
}

function miniForumV2_widget() {
    register_widget( 'MiniForumV2_Widget' );
}

add_action( 'widgets_init', 'miniForumV2_widget' );


function printHTML_Form( $atts ) {
    global $post;
    $a = shortcode_atts( array(
        'count' => 'something',
        'beginAtPosition' => 'something else',
    ), $atts );

    $currentURL = get_permalink();
    $maxSizeUploadMB = max_file_upload_in_bytes() / (1024 * 1024);
    $htmlForm = 'Sie müssen sich einloggen um einen neuen Pilzerkennungsbeitrag zu starten';
    if(is_user_logged_in()) {
        $htmlForm = '<form id="newMushroomIdentificationPost" name="newMIPost" action="'.$currentURL.'" method="post" enctype="multipart/form-data">
                    <p>
                        <label>Titel</label>
                        <input type="text" id="newMITitle" name="MI_title" size="30" maxlength="30"/>
                    </p>
                    <p>
                        <label>Inhalt</label>
                        <textarea id="newMIContent" name="MI_content" cols="50" rows="10"></textarea>
                    </p>
                    <p>
                        <label>Anhang</label>
                        <input type="file" id="newMIFile" name="MI_file" size="50" accept="text/*"> <br/>
                        <label>Maximale Dateigröße: '.$maxSizeUploadMB.' MB</label>
                    </p>
                    <input type="hidden" name="MI_hidden" value="post" />
                    '.wp_nonce_field( 'MI_NonceNewMIPostName', 'MI_NonceNewMIPostAction' ).'
                    <button type="submit">Thread erstellen</button>
                </form>';
    }

    return $htmlForm;
}
add_shortcode( 'pilz-identifizieren', 'printHTML_Form' );
add_action('the_content','createNewMIPost');

function createNewMIPost($content) {
    if ( !is_user_logged_in() )
        return $content;

    if( !current_user_can( 'publish_posts') ) {
        return;
    }

    if ( isset( $_POST['MI_NonceNewMIPostAction'] ) == false ||
        wp_verify_nonce( $_POST['MI_NonceNewMIPostAction'], 'MI_NonceNewMIPostName') == false)
    {
        return $content;
    }

    $title = (isset($_POST["MI_title"]) && !empty($_POST["MI_title"])) ? $_POST["MI_title"] : false;
    $postContent = (isset($_POST["MI_content"]) && !empty($_POST["MI_content"])) ? $_POST["MI_content"] : false;
    $hidden = (isset($_POST["MI_hidden"]) && !empty($_POST["MI_hidden"])) ? $_POST["MI_hidden"] : false;
    $file = $_FILES["MI_file"];

    if($hidden != false) { // form sended
        $error = false;
        if($title == false) {
            echo 'Bitte geben Sie einen Titel an. <br/>';
            $error = true;
        }
        if($content == false) {
            echo 'Bitte geben Sie Inhalt ein. <br/>';
            $error = true;
        }
        $uploadError = getUploadErrorMsg($file);
        if($uploadError != "") {
            $content = $uploadError . $content;
            $error = true;
        }
        if($error) {
            return $content;
        }

        $currentUserID = get_current_user_id();

        $postContent = $postContent . '<br/> [gallery]';
        $post = array(
                      'post_content'   => $postContent,
                      'post_title'     => $title,
                      'post_status'    => 'publish',
                      'post_type'      => 'MiniForumV2-Beitrag',
                      'post_author'    => $currentUserID,
                      'comment_status' => 'open'
        );
        $result = wp_insert_post( $post );
        if($result == 0) {
            "Es trat ein Fehler beim Eintragen in die Datenbank auf. Bitte kontaktieren Sie den Administrator.";
        }
        else { // No errors? -> Try to save the image
            // These files need to be included as dependencies when on the front end.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            // Let WordPress handle the upload.
            // Remember, 'my_image_upload' is the name of our file input in our form above.
            $attachment_id = media_handle_upload( 'MI_file', $result );

            if ( is_wp_error( $attachment_id ) ) {
                $content = 'Es trat ein Fehler beim Speichern des Bildes auf. <br/>' . $content;
            }
        }
    }
    return $content;
}




function getUploadErrorMsg($file)
{
    switch ($file["error"]) {
        case 1: /* Dateigröße (php.ini) */
            return '<p>Die hochgeladene Datei überschreitet die maximal zulässige Dateigröße.<p/>';
        case 2: /* Dateigröße (im HTML Formular mittels der Anweisung MAX_FILE_SIZE festgelegt) */
            return '<p>Die hochgeladene Datei überschreitet die maximal zulässige Dateigröße.<p/>';
        case 3: /* Datei unvollständig übertragen */
            return '<p>Die zu hochladene Datei wurde unvollständig übertragen.<p/>';
        case 4: /* Keine Datei */
            return '<p>Es muss ein Datei hochgeladen werden.<p/>';
        case 6: /* Tmp Ordner nicht vorhanden */
            return '<p>Tmp Ordner ist nicht vorhanden. Bitte kontaktieren Sie den Administrator.<p/>';
        case 7: /* Schreibberechtigung nicht vorhanden */
            return '<p>Es ist keine Schreibberechtigung vorhanden. Bitte kontaktieren Sie den Administrator.<p/>';
        case 8: /* PHP-Erweiterung verbietet den Upload. */
            return '<p>Eine PHP-Erweiterung verbietet den Upload. Bitte kontaktieren Sie den Administrator.<p/>';
    }
    return "";
}

/* Berechnet die maximale zulässige Dateigröße für den Upload
     * The upload is limited by three options: upload_max_filesize, post_max_size and memory_limit.
     * Your upload is only done if it doesn't exeed one of them.
     * Quelle: http://www.kavoir.com/2010/02/php-get-the-file-uploading-limit-max-file-size-allowed-to-upload.html
     */
function max_file_upload_in_bytes() {
    //select maximum upload size
    $max_upload = return_bytes(ini_get('upload_max_filesize'));
    //select post limit
    $max_post = return_bytes(ini_get('post_max_size'));
    //select memory limit
    $memory_limit = return_bytes(ini_get('memory_limit'));
    // return the smallest of them, this defines the real limit
    return min($max_upload, $max_post, $memory_limit);
}

/* Berechnet aus einer Eingabe, die Größe in bytes.
 * Zum Beispiel: "2M" als Eingabe -> Rückgabe "2 * 1024 * 1024"
 * Quelle: http://www.kavoir.com/2010/02/php-get-the-file-uploading-limit-max-file-size-allowed-to-upload.html
 */
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last)
    {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}


?>