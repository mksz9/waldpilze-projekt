<?php
/*
 * Plugin Name: Pilzerkennungsanfragen
 * Plugin URI: localhost/wordpress
 * Description: Bietet eine Kommunikationsmöglichkeit, wo sich Benutzer gegenseitig helfen können beim Identifizieren und Klassifizieren von Pilzen. Shortcode für das HTML-Formular [pilzerkennungsformular]. Hat außerdem ein Widget.
 * Version: 0.3
 */

defined( 'ABSPATH' ) or die( 'Zugriff nur innerhalb von Wordpress gestattet.' );

/*
 * Diese Funktion erstellt einen neuen Custom-Post-Type: 'PEAnfrage'.
 */
add_action( 'init', 'wp_pea_create_post_type');
function wp_pea_create_post_type() {
    register_post_type( 'PEAnfrage',
        array(
            'labels' => array (
                'name' => 'Pilzerkennungsanfragen',
                'singular_name' => 'Pilzerkennungsanfrage',
                'menu_name' => 'PE-Anfrage',
                'add_new_item' => 'Neue Pilzerkennungsanfrage erstellen',
                'edit_item' => 'Pilzerkennungsanfrage bearbeiten',
            ),
            'description' => 'Durch Pilzerkennungsanfragen können sich Anwender gegenseitig unterstützen beim Identifizieren von Pilzen.',
            'public' => true,
            'show_ui' => true,
            'has_archive' => true,
            'supports' => array(
                'title', 'editor', 'author', 'comments', 'revisions', 'custom-fields'
            )
        )
    );
}

/*
 * Falls bei einer Pilzerkennungsanfrage ein Kommentar als gelöst markiert wird, erfolgt hier die Validierung und
 * Speicherung der übermittelten Daten.
 * Der Hook 'the_content' wurde ausgewählt, da hier eine passende Rückmeldung an den Anwender erfolgen kann.
 */
add_filter( 'the_content', 'wp_pea_saveCommentAsSolution' );
function wp_pea_saveCommentAsSolution($content) {
    $solutionCommentID = (isset($_GET["solutionCommentID"]) && !empty($_GET["solutionCommentID"])) ? $_GET["solutionCommentID"] : false;
    $postID = (isset($_GET["postID"]) && !empty($_GET["postID"])) ? $_GET["postID"] : false;

    // Falls beide Variablen übermittelt worden sind und beide nicht leer waren
    if($solutionCommentID !== false && $postID !== false){
        // Entsprechenden Post holen, falls existiert
        $post = get_post($postID);
        if($post == null) {
            return 'Die übermittelte PostID existiert nicht. <br/>' . $content;
        }

        // Nur der Ersteller der Pilzerkennungsanfrage ist berechtigt eine Anfrage als gelöst zu markieren
        // Soll-Ist-Vergleich: Eingeloggter User und Post-Ersteller
        $currentUser = get_current_user_id();
        $postCreator = intval($post->post_author);
        if($postCreator === $currentUser){
            // Übermittelte Daten als Lösung in den postMetaDaten speichern
            update_post_meta($postID, '_isSolved', 'on', false);
            update_post_meta($postID, '_solutionCommentID', $solutionCommentID, false);

            // Rückmeldung über die erfolgreiche Aktion
            return 'Der Kommentar wurde als Lösung vorgemerkt. Bitte klicken Sie <a href="'.get_permalink($postID).'">hier</a><br/> um weitergeleitet zu werden.';
        }
        else {
            // Rückmeldung über die fehlgeschlagene Aktion
            return 'Sie sind nicht der Author von diesem Post. <br/>' . $content;
        }
    }
    return $content;
}

/*
 * Erstellt eine kleine Metabox im Admin-Bereich für den Post-Type 'Pilzerkennungsanfrage'.
 * Diese Metabox soll aktuelle Status-Informationen über die 'Pilzerkennungsanfrage' ausgeben.
 * Diese Funktion ruft für den Inhalt der Metabox die Funktion 'wp_pea_metaboxContent' auf.
 */
add_action( 'add_meta_boxes', 'wp_pea_status_addMetabox');
function wp_pea_status_addMetabox() {
    add_meta_box('status', 'Aktueller Status', 'wp_pea_metaboxContent', 'PEAnfrage', 'side');
}

/*
 * Generiert den Inhalt der Status-Metabox:
 * 1. Nicht gelöst oder gelöst (als Checkbox)
 * 2. Falls gelöst, dann ebenfalls die KommentarID als Label
 * Der Status einer Anfrage ist veränderbar (Gelöst/Nicht gelöst). KommentarID ist fest. Falls eine gelöste Anfrage
 * als nicht gelöst markiert wird, dann wird die KommentarID gelöscht.
 */
function wp_pea_metaboxContent()  {
    // Aktuellen Status auslesen (Post-Meta-Daten)
    $status = get_post_meta(get_the_ID(), '_isSolved', true);

    // Ausgabe:

    echo   '<label for="isSolved">';
    // Gelöst
    if($status == 'on'){
        echo '<input type="checkbox" name="isSolved" id="isSolved" checked> Gelöst <br/>
              </label> <br/>';

        // Falls gelöst, dann gib ebenfalls die KommentarID aus (Post-Meta-Daten auslesen)
        $solutionCommentID = get_post_meta(get_the_ID(), '_solutionCommentID', true);
        if($solutionCommentID != "")
            echo 'Lösung (KommentarID):<br/>' . $solutionCommentID;
    }
    // Ungelöst
    else {
        echo '<input type="checkbox" name="isSolved" id="isSolved"> Gelöst
              </label> <br/> <br/>';
    }
}

/*
 * Speichert die Daten die in der Metabox übermittelt wurden in der Datenbank
 */
add_action( 'save_post', 'wp_pea_save_metaboxesData');
function wp_pea_save_metaboxesData() {
    // Nur für den PostType 'PEAnfrage'
    $postType = get_post_type();
    if(!$postType == 'PEAnfrage')
        return;

    // Status speichern, gelöst oder nicht gelöst (Checkbox)
    $postID = $_POST['post_ID'];
    $solved = $_POST['isSolved'];
    update_post_meta($postID, '_isSolved', $solved, false);

    // Falls status = 'nicht gelöst' -> dann die KommentarID, die als gelöst markiert war, ebenfalls löschen
    if($solved == null)
        update_post_meta($postID, '_solutionCommentID', "", false);
}

/*
 * In den Kommentaren von 'PEAnfrage'...
 * 1. Einen Link erstellen mit dem ein Kommentar als richtig markiert und die Anfrage als gelöst werden kann
 * 2. Falls ein Kommentar als gelöst markiert ist, dann dort  eine Information mit Bild hinterlassen
 */
add_filter( 'comments_array' , 'wp_pea_modify_comments' , 10, 2 ); //TODO 10, 2?
function wp_pea_modify_comments($comments , $post_id) {
    $postType = get_post_type();
    foreach($comments as $comment){
        if($postType == 'PEAnfrage'){
            // 1. Link erstellen, damit ein Kommentar als richtig markiert werden kann
            // Link nur erstellen, falls der 'eingeloggter Benutzer = Ersteller des Posts'
            global $post;
            $currentUser = get_current_user_id();
            $postCreator = intval($post->post_author);

            if($postCreator === $currentUser){
                $arr_params = array('solutionCommentID' => $comment->comment_ID, 'postID' => $comment->comment_post_ID);
                $url = esc_url( add_query_arg( $arr_params) );
                $link = '<small><p align="right"><a href="'.$url.'">Kommentar als Lösung markieren</a></p></small>';
                $comment->comment_content = $comment->comment_content . $link ;
            }


            // 2. Falls ein Kommentar als richtig markiert ist -> Bild anzeigen
            $solution_commentID = get_post_meta(get_the_ID(), '_solutionCommentID', true);
            if($solution_commentID == $comment->comment_ID) {
                $pictureURL = plugins_url( 'solved.png', __FILE__ );
                $picture = '<img src="'.$pictureURL.'" align="middle"/>';
                $comment->comment_content = $comment->comment_content . '<br/><br/>' . $picture . ' Diese Antwort wurde als richtig markiert.';
            }
        }
    }
    return $comments;
}

/*
 * Erstellt ein HTML-Formular per Shortcode ins Frontend mit dem Posts vom Typ 'PEAnfragen' erstellt
 * werden können
 */
add_shortcode( 'pilzerkennungsformular', 'wp_pea_HTML_Form' );
function wp_pea_HTML_Form( $atts ) {
    /*global $post;
    $a = shortcode_atts( array(
        'count' => 'something',
        'beginAtPosition' => 'something else',
    ), $atts );*/

    $htmlForm = '';
    if(is_user_logged_in()) {
        // Formular referenziert die aktuelle Seite
        $currentURL = get_permalink();
        // Die aktuelle zulässige Upload-Größe des Servers
        $maxSizeUploadMB = max_file_upload_in_bytes() / (1024 * 1024);

        // HTML-Formular erstellen
        $htmlForm = '<form id="mushroomIdentificationRequestID" name="mushroomIdentificationRequest" action="'.$currentURL.'" method="post" enctype="multipart/form-data">
                    <p>
                        <label>Titel</label>
                        <input type="text" id="mushroomIdentificationRequestTitleID" name="mushroomIdentificationRequestTitle" size="30" maxlength="30"/>
                    </p>
                    <p>
                        <label>Inhalt</label>
                        <textarea id="mushroomIdentificationRequestContentID" name="mushroomIdentificationRequestContent" cols="50" rows="10"></textarea>
                    </p>
                    <p>
                        <label>Anhang</label>
                        <input type="file" id="mushroomIdentificationRequestFileID" name="mushroomIdentificationRequestFile[]" multiple> <br/>
                        <label>Maximale Dateigröße pro Datei: '.$maxSizeUploadMB.' MB</label>
                    </p>
                    <input type="hidden" name="mushroomIdentificationRequestHidden" value="post" />
                    '.wp_nonce_field( 'mushroomIdentificationRequestNonceID', 'mushroomIdentificationRequestNonce' ).'';

        // Captcha einfügen
        if(class_exists('gcms_cap_captcha'))
        {
            $htmlForm = $htmlForm .  '<img src="'.esc_url(gcms_cap_captcha::getInstance()->getCaptachaImageUrl()).'" />
                    <p>Captcha:<br /><input name="captcha" type="text" autocomplete="off" /></p>';
        }

        // HTML-Formular vervollständigen
       $htmlForm = $htmlForm .
           '<button type="submit">Thread erstellen</button>
           </form>';
    }
    else
        $htmlForm = 'Sie müssen sich einloggen um eine neue Pilzerkennungsanfrage zu starten. <br/>';

    return $htmlForm;
}

/*
 * Falls Daten für eine neue 'PEAnfrage' übermittelt wurden, dann werden diese hier
 * validiert und in die Datenbank abgespeichert
 */
add_action('the_content','createNewMIPost');
function createNewMIPost($content) {
    $title = (isset($_POST["mushroomIdentificationRequestTitle"]) && !empty($_POST["mushroomIdentificationRequestTitle"])) ? $_POST["mushroomIdentificationRequestTitle"] : false;
    $postContent = (isset($_POST["mushroomIdentificationRequestContent"]) && !empty($_POST["mushroomIdentificationRequestContent"])) ? $_POST["mushroomIdentificationRequestContent"] : false;
    $hidden = (isset($_POST["mushroomIdentificationRequestHidden"]) && !empty($_POST["mushroomIdentificationRequestHidden"])) ? $_POST["mushroomIdentificationRequestHidden"] : false;
	$file = (isset($_FILES["mushroomIdentificationRequestFile"]) && !empty($_FILES["mushroomIdentificationRequestFile"])) ? $_FILES["mushroomIdentificationRequestFile"] : false;
	$captcha = (isset($_POST["captcha"]) && !empty($_POST["captcha"])) ? $_POST["captcha"] : false;

    $result = null;
    if($hidden != false) {
        // Das Formular wurde übertragen -> Validierung nötig
        if ( !is_user_logged_in() )
            return 'Sie müssen eingeloggt sein um ein neue Pilzerkennungsanfrage zu erstellen. </br>' . $content;

        if( !current_user_can( 'publish_posts') )
            return 'Sie besitzen nicht die benötigten Berechtigungen. </br>' . $content;

        // Es wurde nicht das zugehörige HTML-Formular verwendet
        if ( isset( $_POST['mushroomIdentificationRequestNonce'] ) == false ||
            wp_verify_nonce( $_POST['mushroomIdentificationRequestNonce'], 'mushroomIdentificationRequestNonceID') == false)
            return $content;

        // Captcha-Verfizierung
        if(class_exists('gcms_cap_captcha'))
        {
            $captchaObject = gcms_cap_captcha::getInstance();
            $captchaResult = $captchaObject->isValidCaptchaText($captcha);
            if($captchaResult == false)
                return 'Fehlerhafter Captcha. <br/>' . $content;
        }

        if($title == false)
            return 'Bitte geben Sie einen Titel an. <br/>' . $content;

        if($content == false)
            return 'Bitte geben Sie einen Inhalt ein. <br/>' . $content;

        $fileCount = count($file["name"]);
        if($fileCount <= 0) {
            return 'Sie müssen mindestens ein Bild hochladen. <br/>' . $content;
        }

        // Alle Daten Ok -> Eintragen in die Datenbank
        $currentUserID = get_current_user_id();

        // Pilzerkennungsanfrage erstellen
        $postContent = $postContent . '<br/> [gallery]';
        $post = array(
                      'post_content'   => $postContent,
                      'post_title'     => $title,
                      'post_status'    => 'publish',
                      'post_type'      => 'PEAnfrage',
                      'post_author'    => $currentUserID,
                      'comment_status' => 'open'
        );
        $result = wp_insert_post( $post );
        if($result == 0)
            return "Es trat ein Fehler beim Eintragen in die Datenbank auf. Bitte kontaktieren Sie den Administrator. </br>" . $content;

        else { // Keine Fehler -> Dateiupload handeln
            // These files need to be included as dependencies when on the front end.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            // $_FILES hat nicht das erwartete Format (Multiple-Uploads)
            // Format muss hergestellt werden:
            // Idee: Lese $_FILES aus und überschreibe es für jede Datei mit dem alten Format. Anschließend hat
            // $_FILES die vorgegebene Struktur und kann zum Uploaden der Datei genutzt werden.
            $files = $_FILES['mushroomIdentificationRequestFile'];
                foreach ($files['name'] as $key => $value) { // Für jede hochzuladene Datei
                if ($files['name'][$key]) {
                    // Altes Format erstellen
                    $file = array(
                        'name'     => $files['name'][$key],
                        'type'     => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error'    => $files['error'][$key],
                        'size'     => $files['size'][$key]
                    );

                    // Jede Datei wird einzeln validiert, bevor sie in die Datenbank übermittelt wird
                    $uploadError = getUploadErrorMsg($file);
                    if($uploadError != "") {
                        wp_delete_post( $result, true );
                        return $uploadError . $content;
                    }

                    // Array überschreiben mit dem alten Format
                    $_FILES = array("mushroomIdentificationRequestFile" => $file);

                    // Datei hochladen
                    foreach ($_FILES as $file => $array) {
                        $attachment_id = media_handle_upload($file,$result);

                        if ( is_wp_error( $attachment_id ) ) {
                            wp_delete_post( $result, true ); // Nicht in den Papierkorb verschieben
                            return 'Es trat ein Fehler beim Speichern des Bildes auf. <br/>' . $content;
                        }
                    }
                }
            }
            return 'Die Pilzerkennunsgsanfrage wurde erfolgreich erstellt. Bitte klicken Sie <a href="'.get_permalink($result).'">hier</a> um weitergeleitet zu werden.';
        }
    }
    return $content;
}




function getUploadErrorMsg($file) {
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

    if ( strpos($file['type'], 'image') === false) {
        return 'Es sind nur Bildformate gestattet. <br/>';
    }
    return '';
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



add_action( 'widgets_init', 'miniForumV2_widget' );
function miniForumV2_widget() {
    register_widget( 'MiniForumV2_Widget' );
}

class MiniForumV2_Widget extends WP_Widget {

    function MiniForumV2_Widget() {
        // Instantiate the parent object
        parent::__construct( false, 'Neuste MiniForumV2-Beiträge' );
    }

    function widget( $args, $instance ) {
        // Widget output
        $posts = get_posts(
            array('post_type' => 'PEAnfrage',
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
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        require_once( ABSPATH . 'wp-includes/pluggable.php' );
        require_once( ABSPATH . 'wp-includes/capabilities.php' );
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



?>