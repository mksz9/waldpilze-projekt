<?php
/*
 * Plugin Name: Pilzerkennungsanfragen
 * Plugin URI: localhost/wordpress
 * Description: Bietet eine Kommunikationsmöglichkeit, wo sich Benutzer gegenseitig helfen können beim Identifizieren und Klassifizieren von Pilzen. Shortcode für das HTML-Formular [pilzerkennungsformular]. Hat außerdem ein Widget.
 * Version: 0.5
 */

defined( 'ABSPATH' ) or die( 'Zugriff nur innerhalb von Wordpress gestattet.' );

load_plugin_textdomain('wp_pea', false, dirname( plugin_basename( __FILE__) ). '/lang/');

/*
 * Diese Funktion erstellt einen neuen Custom-Post-Type: 'PEAnfrage'.
 */
add_action( 'init', 'wp_pea_create_post_type');
function wp_pea_create_post_type() {
    register_post_type( 'peanfrage',
        array(
            'labels' => array (
                'name' => __('Pilzerkennungsanfragen', 'wp_pea'),
                'singular_name' => __('Pilzerkennungsanfrage', 'wp_pea'),
                'menu_name' => __('PE-Anfrage', 'wp_pea'),
                'add_new_item' => __('Neue Pilzerkennungsanfrage erstellen', 'wp_pea'),
                'edit_item' => __('Pilzerkennungsanfrage bearbeiten', 'wp_pea')
            ),
            'description' => __('Durch Pilzerkennungsanfragen können sich Anwender gegenseitig beim Identifizieren von Pilzen unterstützen.', 'wp_pea'),
            'public' => true,
            'show_ui' => true,
            'has_archive' => true,
            'supports' => array(
                'title', 'editor', 'author', 'comments'
            )
        )
    );
}

/*
 * Erstellt eine kleine Metabox im Admin-Bereich für den Post-Type 'Pilzerkennungsanfrage'.
 * Diese Metabox soll aktuelle Status-Informationen über die 'Pilzerkennungsanfrage' ausgeben.
 * Diese Funktion ruft für den Inhalt der Metabox die Funktion 'wp_pea_metaboxContent' auf.
 */
add_action( 'add_meta_boxes', 'wp_pea_status_addMetabox');
function wp_pea_status_addMetabox() {
    add_meta_box('status', __('Aktueller Status', 'wp_pea'), 'wp_pea_metaboxContent', 'peanfrage', 'side');
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
        echo '<input type="checkbox" name="isSolved" id="isSolved" checked> '. __('Gelöst', 'wp_pea') .' <br/>
              </label> <br/>';

        // Falls gelöst, dann gib ebenfalls die KommentarID aus (Post-Meta-Daten auslesen)
        $solutionCommentID = get_post_meta(get_the_ID(), '_solutionCommentID', true);
        if($solutionCommentID != "")
            echo __('Lösung (KommentarID)', 'wp_pea') . ':<br/>' . $solutionCommentID;
    }
    // Ungelöst
    else {
        echo '<input type="checkbox" name="isSolved" id="isSolved"> '.__('Gelöst', 'wp_pea').'
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
    if(!$postType == 'peanfrage')
        return;

    // Status speichern, gelöst oder nicht gelöst (Checkbox)

    $postID =  (isset($_POST["post_ID"]) && !empty($_POST["post_ID"])) ? $_POST["post_ID"] : false;
    $solved =  (isset($_POST["isSolved"]) && !empty($_POST["isSolved"])) ? $_POST["isSolved"] : false;
    update_post_meta($postID, '_isSolved', $solved, false);

    // Falls status = 'nicht gelöst' -> dann die KommentarID, die als gelöst markiert war, ebenfalls löschen
    if($solved == null)
        update_post_meta($postID, '_solutionCommentID', "", false);
}

/*
 * Falls bei einer Pilzerkennungsanfrage ein Kommentar als gelöst markiert wird, erfolgt hier die Validierung und
 * Speicherung der übermittelten Daten.
 * Der Hook 'the_content' wurde ausgewählt, da hier eine passende Rückmeldung an den Anwender erfolgen kann.
 */
add_filter( 'the_content', 'wp_pea_saveCommentAsSolution' );
function wp_pea_saveCommentAsSolution($content) {
    $currentPostType = get_post_type();
    if($currentPostType !== 'peanfrage')
        return $content;

    $solutionCommentID = wp_pea_getVariable('solutionCommentID', 'GET');
    $postID = wp_pea_getVariable('postID', 'GET');

    // Falls beide Variablen übermittelt worden sind und beide nicht leer waren
    if($solutionCommentID !== false && $postID !== false){
        // Entsprechenden Post holen, falls existiert
		$solutionCommentID_is_numeric = is_numeric($solutionCommentID);
		$postID_is_numeric = is_numeric($postID);
		
		if($solutionCommentID_is_numeric == false || $postID_is_numeric == false)
			return;
		
        $post = get_post($postID);
        if($post == null) {
            // Rückmeldung über die fehlgeschlagene Aktion
            return '<font color="#cd5c5c" size="5">'.__('Es ist ein Fehler aufgetreten', 'wp_pea').':</br></font><font color="#cd5c5c" size="3">' . __('Die übermittelte PostID existiert nicht.', 'wp_pea') . '<br/></font></br></br>'.$content;
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
            return '<font color="#adff2f" size="5">'.__('Erfolgreiche Validierung', 'wp_pea').':</br></font><font color="#adff2f" size="3">'.__('Der Kommentar wurde als Lösung vorgemerkt', 'wp_pea'). '. <br/></font></br></br>'.$content;
        }
        else {
            // Rückmeldung über die fehlgeschlagene Aktion
            return '<font color="#cd5c5c" size="5">'.__('Es ist ein Fehler aufgetreten', 'wp_pea').':</br></font><font color="#cd5c5c" size="3">'.__('Sie sind nicht der Author von diesem Post.', 'wp_pea').'</font></br></br>'.$content;
        }
    }
    return $content;
}

/*
 * In den Kommentaren von 'PEAnfrage'...
 * 1. Einen Link erstellen mit dem ein Kommentar als richtig markiert und die Anfrage gelöst werden kann
 * 2. Falls ein Kommentar als gelöst markiert ist, dann diesen Kommentar mit einem Bild hervorheben
 */
add_filter( 'comments_array' , 'wp_pea_modify_comments' , 10, 2 ); //TODO 10, 2?
function wp_pea_modify_comments($comments , $post_id) {
    $postType = get_post_type();
    foreach($comments as $comment){
        if($postType == 'peanfrage'){
            // 1. Link erstellen, damit ein Kommentar als richtig markiert werden kann
            // Link nur erstellen, falls der 'eingeloggter Benutzer = Ersteller des Posts'
            global $post;
            $currentUser = get_current_user_id();
            $postCreator = intval($post->post_author);

            if($postCreator === $currentUser){
                $arr_params = array('solutionCommentID' => $comment->comment_ID, 'postID' => $comment->comment_post_ID);
                $url = esc_url( add_query_arg( $arr_params) );
                $link = '<small><p align="right"><a href="'.$url.'">'.__('Kommentar als Lösung markieren', 'wp_pea').'</a></p></small>';
                $comment->comment_content = $comment->comment_content . $link ;
            }


            // 2. Falls ein Kommentar als richtig markiert ist -> Bild anzeigen
            $solution_commentID = get_post_meta(get_the_ID(), '_solutionCommentID', true);
            if($solution_commentID == $comment->comment_ID) {
                $pictureURL = plugins_url( '/img/solved.png', __FILE__ );
                $picture = '<img src="'.$pictureURL.'" align="middle"/>';
                $comment->comment_content = $comment->comment_content . '<br/><br/>' . $picture . __('Diese Antwort wurde als richtig markiert.', 'wp_pea');
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
    // Wurde das Formular übertragen? Dann müssen die Daten verarbeitet werden
    $hasErrors = false;
    $newPost = wp_pea_createNewPEAPost($hasErrors);

    if($hasErrors == true){
        // Im Fehlerfall Hinweis- bzw. Warnmeldung ausgeben.
        echo '<font color="#cd5c5c" size="5">'.__('Es ist ein Fehler aufgetreten', 'wp_pea').':</br></font><font color="#cd5c5c" size="3">' . $newPost . '</font></br></br>';
    }
    else if($newPost != ''){
        // Im Erfolgsfall eine Meldung ausgeben.
        echo '<font color="#adff2f" size="5">'.__('Anfrage erfolgreich erstellt', 'wp_pea').':</br></font><font color="#adff2f" size="3">' . $newPost . '</font></br></br>';
        return;
    }

    $htmlForm = '';

    // Das HTML-Formular für das Anlegen neuer Anfragen erstellen (nur für einloggte Benutzer)
    if(is_user_logged_in()) {
        // Formular referenziert die aktuelle Seite
        $currentURL = get_permalink();

        // Die aktuelle zulässige Upload-Größe des Servers
        $maxSizeUploadMB = wp_pea_max_file_upload_in_bytes() / (1024 * 1024);

        // HTML-Formular erstellen
        $title = (isset($_POST["mushroomIdentificationRequestTitle"])) ? $_POST["mushroomIdentificationRequestTitle"] : '';
        $postContent = (isset($_POST["mushroomIdentificationRequestContent"])) ? $_POST["mushroomIdentificationRequestContent"] : '';

        $htmlForm = '<form id="mushroomIdentificationRequestID" name="mushroomIdentificationRequest" action="'.$currentURL.'" method="post" enctype="multipart/form-data">
                    <p>
                        <label>'.__('Titel', 'wp_pea').':</label></br>
                        <input type="text" id="mushroomIdentificationRequestTitleID" name="mushroomIdentificationRequestTitle" size="30" value="'.esc_attr($title).'" maxlength="30"/>
                    </p>
                    <p>
                        <label>'.__('Inhalt', 'wp_pea').':</label></br>
                        <textarea id="mushroomIdentificationRequestContentID" name="mushroomIdentificationRequestContent" cols="50" rows="10"/>'.esc_attr($postContent).'</textarea>
                    </p>
                    <p>
                        <label>'.__('Anhang', 'wp_pea').':</label></br>
                        <input type="file" id="mushroomIdentificationRequestFileID" name="mushroomIdentificationRequestFile[]" multiple> <br/>
                        <label>'.__('Maximale Dateigröße pro Datei', 'wp_pea').': </br>'.$maxSizeUploadMB.' MB</label>
                    </p>
                    <input type="hidden" name="mushroomIdentificationRequestHidden" value="post" />
                    '.wp_nonce_field( 'mushroomIdentificationRequestNonceID', 'mushroomIdentificationRequestNonce' ).'';

        // Captcha einfügen
        if(class_exists('gcms_cap_captcha'))
        {
            $htmlForm = $htmlForm .  '<img src="'.esc_url(gcms_cap_captcha::getInstance()->getCaptachaImageUrl()).'" />
                    <p>'.__('Captcha', 'wp_pea').':<br /><input name="captcha" type="text" autocomplete="off" /></p>';
        }

        // HTML-Formular vervollständigen
       $htmlForm .= '<input type="submit" value="'.__('Anfrage erstellen', 'wp_pea').'"/>'.
                    '</form>';
    }
    else
        $htmlForm = __('Sie müssen sich einloggen um eine neue Pilzerkennungsanfrage zu erstellen.', 'wp_pea') . '<br/>' ;

    return $htmlForm;
}

/*
 * Falls Daten für eine neue 'PEAnfrage' übermittelt wurden, dann werden diese hier validiert und in die Datenbank
 * abgespeichert. Im Fehlerfall wird der Referenz-Parameter $hasErrors auf true gesetzt und die Fehlermeldung
 * zurückgegeben. Bei Erfolg wird $hasErrors auf false gesetzt und ein Leerstring zurückgegeben.
 */
function wp_pea_createNewPEAPost(&$hasErrors) {
    // Einlesen der per HTML-Formular übermittelten Variablen
    $title = wp_pea_getVariable('mushroomIdentificationRequestTitle', 'POST');
    $postContent = wp_pea_getVariable('mushroomIdentificationRequestContent', 'POST');
    $hidden = wp_pea_getVariable('mushroomIdentificationRequestHidden', 'POST');
	$file = wp_pea_getVariable('mushroomIdentificationRequestFile', 'FILES');
	$captcha = wp_pea_getVariable('captcha', 'POST');

    // Falls wirklich das Formular gesendet wurde
    if($hidden != false) {
        // Annahme, dass bei der Validierung fehler auftreten werden
        $hasErrors = true;

        // Nur einloggte Benutzer
        if ( !is_user_logged_in() )
            return __('Sie müssen eingeloggt sein um ein neue Pilzerkennungsanfrage zu erstellen.', 'wp_pea') . '<br/>';

        // Nur Benutzer mit entsprechender Berechtigung
        if( !current_user_can( 'publish_posts') )
            return __('Sie besitzen nicht die benötigten Berechtigungen', 'wp_pea') . '<br/>';

        // Sicherstellen, dass die Daten vom zuständigen Wordpress-Formular (Seite) übertragen wurden
        if ( isset( $_POST['mushroomIdentificationRequestNonce'] ) == false ||
            wp_verify_nonce( $_POST['mushroomIdentificationRequestNonce'], 'mushroomIdentificationRequestNonceID') == false)
            return '';

        // Captcha-Verfizierung
        if(class_exists('gcms_cap_captcha'))
        {
            $captchaObject = gcms_cap_captcha::getInstance();
            $captchaResult = $captchaObject->isValidCaptchaText($captcha);
            if($captchaResult == false)
                return __('Fehlerhafter Captcha', 'wp_pea') . '<br/>';
        }

        if($title == false)
            return __('Bitte geben Sie einen Titel an.', 'wp_pea') . '<br/>';

        if($postContent == false)
            return __('Bitte geben Sie einen Inhalt ein.', 'wp_pea') . '<br/>';

        $fileCount = $file["size"][0];
        if($fileCount <= 0) {
            return __('Sie müssen mindestens ein Bild hochladen.', 'wp_pea') . '<br/>';
        }

        // Alle Daten Ok -> Eintragen in die Datenbank
        $currentUserID = get_current_user_id();

        // Pilzerkennungsanfrage erstellen (Custom-Post-Type: peanfrage)
        $postContent = $postContent . '<br/> [gallery]';
        $post = array(
                      'post_content'   => wp_strip_all_tags($postContent),
                      'post_title'     => wp_strip_all_tags($title),
                      'post_status'    => 'publish',
                      'post_type'      => 'peanfrage',
                      'post_author'    => $currentUserID,
                      'comment_status' => 'open'
        );
        $result = wp_insert_post( $post );
        if($result == 0)
            return __('Es trat ein Fehler beim Eintragen in die Datenbank auf. Bitte kontaktieren Sie den Administrator.', 'wp_pea') . '<br/>';

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
                    $uploadError = wp_pea_getUploadErrorMsg($file);
                    if($uploadError != "") {
                        // Im Fehlerfall den zugehörigen Post löschen
                        wp_delete_post( $result, true );
                        return $uploadError;
                    }

                    // Array überschreiben mit dem alten Format
                    $_FILES = array("mushroomIdentificationRequestFile" => $file);

                    // Datei hochladen
                    foreach ($_FILES as $file => $array) {
                        $attachment_id = media_handle_upload($file,$result);

                        if ( is_wp_error( $attachment_id ) ) {
                            wp_delete_post( $result, true ); // Nicht in den Papierkorb verschieben
                            return __('Es trat ein Fehler beim Speichern des Bildes auf.', 'wp_pea') . '<br/>';
                        }
                    }
                }
            }
            // Annahme ändern, Keine Fehler aufgetreten
            $hasErrors = false;
            return __('Die Pilzerkennunsgsanfrage wurde erfolgreich erstellt.', 'wp_pea') . '<br/>' . '<a href="'.get_permalink($result).'"> '.__('Bitte klicken Sie hier um weitergeleitet zu werden.', 'wp_pea') .'</a>';
        }
    }
    return '';
}

/*
 * Gibt für den Parameter $file (array) eine Fehlermeldung zurück falls vorhanden. Ansonsten ein Leerstring
 * Die Struktur von $file entspricht die eines Objektes vom globalen Array $_FILES z.B.
 *  $file = array(
                        'name'     => 'meinBild.jpg',
                        'type'     => 'image',
                        'tmp_name' => '31020553.jpg',
                        'error'    => 3,
                        'size'     => 323
                    );
 */
function wp_pea_getUploadErrorMsg($file) {
    switch ($file["error"]) {
        case 1: /* Dateigröße (php.ini) */
            return '<p>'.__('Die hochgeladene Datei überschreitet die maximal zulässige Dateigröße.', 'wp_pea') . '<p/>';
        case 2: /* Dateigröße (im HTML Formular mittels der Anweisung MAX_FILE_SIZE festgelegt) */
            return '<p>'.__('Die hochgeladene Datei überschreitet die maximal zulässige Dateigröße.', 'wp_pea') . '<p/>';
        case 3: /* Datei unvollständig übertragen */
            return '<p>'.__('Die zu hochladene Datei wurde unvollständig übertragen.', 'wp_pea') . '<p/>';
        case 4: /* Keine Datei */
            return '<p>'.__('Es muss ein Datei hochgeladen werden.', 'wp_pea') . '<p/>';
        case 6: /* Tmp Ordner nicht vorhanden */
            return '<p>'.__('Tmp Ordner ist nicht vorhanden. Bitte kontaktieren Sie den Administrator.', 'wp_pea') . '<p/>';
        case 7: /* Schreibberechtigung nicht vorhanden */
            return '<p>'.__('Es ist keine Schreibberechtigung vorhanden. Bitte kontaktieren Sie den Administrator.', 'wp_pea') . '<p/>';
        case 8: /* PHP-Erweiterung verbietet den Upload. */
            return '<p>'.__('Eine PHP-Erweiterung verbietet den Upload. Bitte kontaktieren Sie den Administrator.', 'wp_pea') . '<p/>';
    }

    if ( strpos($file['type'], 'image') === false) {
        return __('Es sind nur Bildformate gestattet.', 'wp_pea') . '<br/>';
    }
    return '';
}

/* Berechnet die maximale zulässige Dateigröße für den Upload
 * The upload is limited by three options: upload_max_filesize, post_max_size and memory_limit.
 * Your upload is only done if it doesn't exeed one of them.
 * Quelle: http://www.kavoir.com/2010/02/php-get-the-file-uploading-limit-max-file-size-allowed-to-upload.html
 */
function wp_pea_max_file_upload_in_bytes() {
    //select maximum upload size
    $max_upload = wp_pea_return_bytes(ini_get('upload_max_filesize'));
    //select post limit
    $max_post = wp_pea_return_bytes(ini_get('post_max_size'));
    //select memory limit
    $memory_limit = wp_pea_return_bytes(ini_get('memory_limit'));
    // return the smallest of them, this defines the real limit
    return min($max_upload, $max_post, $memory_limit);
}

/* Berechnet aus einer Byte-Größe (MB, KB, GB), die Größe in bytes.
 * Zum Beispiel: "2M" als Eingabe -> Rückgabe "2 * 1024 * 1024"
    * Quelle: http://www.kavoir.com/2010/02/php-get-the-file-uploading-limit-max-file-size-allowed-to-upload.html
 */
function wp_pea_return_bytes($val) {
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

add_action( 'widgets_init', 'wp_pea_register_widget' );
function wp_pea_register_widget() {
    register_widget( 'wp_pea_Widget' );
}
/*
 * Erstellt ein Widget, dass in aktuelle Pilzanfragen ausgibt
 */
class wp_pea_Widget extends WP_Widget {

    function wp_pea_Widget() {
        parent::__construct( false, 'Pilzerkennungsanfragen' );
    }

    /*
     * Gibt das Widget im Frontend aus
     */
    function widget( $args, $instance ) {
        // Widget-Parameter einlesen
        $postFilter = esc_attr($instance['postFilter']);
        $postsPerPage = esc_attr($instance['postsperpage']);
        $beginAtPosition = esc_attr($instance['beginAtPosition']);
        $title = esc_attr($instance['title']);

        // Abhängige Posts einlesen
        $posts;
        if($postFilter == 'showsolvedpostonly')
            $posts = wp_pea_getAllSolvedRequests($postsPerPage, $beginAtPosition);
        if($postFilter == 'showunsolvedpostonly')
            $posts = wp_pea_getAllUnSolvedRequests($postsPerPage, $beginAtPosition);
        if($postFilter == 'nofilter')
            $posts = wp_pea_getAllRequests($postsPerPage, $beginAtPosition);

        // HTML-Ausgabe
        echo '<aside id="Pilzerkennungsanfragen" class="widget widget_recent_entries">'.
             '<h4 class="widget-title">'.$title.'</h4>';

        // Globale post-Variable laden (dadurch können bestimmte Funktionen in der gleich folgenden foreach Schleife verwendet werden)
        global $post;

        foreach($posts as $post){
            setup_postdata($post);

            // Status aus den Post-Meta-Daten einlesen (Gelöst/Ungelöst)
            $status = get_post_meta(get_the_ID(), '_isSolved', true);

            // Einlesen des Bildes abhängig vom Status (Gelöst/Ungelöst)
            $picture = '';
            if($status == 'on') { //solved -> show solved.picture
                $pictureURL = plugins_url( '/img/solved.png', __FILE__ );
                $picture = '<img src="'.$pictureURL.'" align="middle" height="20" width="20"/>';
            }
            else { // not solved -> show notSolved.picture
                $pictureURL = plugins_url( '/img/notsolved.png', __FILE__ );
                $picture = '<img src="'.$pictureURL.'" align="middle" height="20" width="20"/>';
            }

            // HTML-Ausgabe (Bild mit Link)
            echo '<div style="width: 100%; height: 20px;">'.
                 '<div style="margin-right:5px; width:20px; height:20px; float:left;">' . $picture . '</div>'.
                 '<div style="width:auto;float:left;"><a href="'.get_permalink().'">'.get_the_title().'</a></div>'.
                 '</div>';
        }
        echo'</aside></br>';
    }

    /*
     * Valdiert und speichert die Objekte in der Backend-Verwaltung
     */
    function update( $new_instance, $old_instance ) {
        // Save widget options
        $new_instance['title'] = esc_attr($new_instance['title']);
        $new_instance['postsperpage'] = intval($new_instance['postsperpage']);
        $new_instance['beginAtPosition'] = intval($new_instance['beginAtPosition']);

        // Einen der drei Variablen setzen (Nur gelöste Anfragen, Nur ungelöste Anfragen, Kein Filter (alle Anfragen) )
        $postFilter = esc_attr($new_instance['postFilter']);
        $new_instance['showsolvedpostonly'] = '';
        $new_instance['showunsolvedpostonly'] = '';
        $new_instance['nofilter'] = '';

        if($postFilter == 'showsolvedpostonly')
            $new_instance['showsolvedpostonly'] = 'checked';
        if($postFilter == 'showunsolvedpostonly')
            $new_instance['showunsolvedpostonly'] = 'checked';
        if($postFilter == 'nofilter')
            $new_instance['nofilter'] = 'checked';

        return $new_instance;
    }

    /*
     * Erstellt die Anzeige für die Backend-Verwaltung
     */
    function form( $instance ) {
        // Parameter laden falls vorhanden. Wenn nicht vorhanden dann folgende Default-Werte benutzen
        $instance = wp_parse_args(
            (array) $instance, array(
                'title' => 'PE-Anfrage',
                'postsperpage' => 5,
                'beginAtPosition' => 0,
                'showunsolvedpostonly' => '',
                'showsolvedpostonly' => '',
                'nofilter' => 'checked'
            )
        );

        // Titeleingabe
        echo '  <p>
                    <label for="'.$this->get_field_id( 'title' ).'">'.__('Titel', 'wp_pea').':</label></br>
                    <input type="text" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" value="'.$instance['title'].'" />
                </p>';

        // Anzahl der Posts
        echo '  <p>
                    <label for="'.$this->get_field_id( 'postsperpage' ).'">'.__('Anzahl der angezeigten Anfragen', 'wp_pea').':</label></br>
                    <input type="text" id="'.$this->get_field_id( 'postsperpage' ).'" name="'.$this->get_field_name( 'postsperpage' ).'" value="'.$instance['postsperpage'].'"/>
                </p>';

        // Offset - Startposition
        echo '  <p>
                    <label for="'.$this->get_field_id( 'beginAtPosition' ).'">'.__('Anfangsposition der Anfragen', 'wp_pea').':</label></br>
                    <input type="text" id="'.$this->get_field_id( 'beginAtPosition' ).'" name="'.$this->get_field_name( 'beginAtPosition' ).'" value="'.$instance['beginAtPosition'].'" />
                </p>';

        // Filter (Gelöste/Ungelöste PE-Anfragen)
        echo '  <p>
                    <label for="'.$this->get_field_id( 'postFilter' ).'">'.__('Anfragen filtern', 'wp_pea').':</label>
                    <fieldset>
                       <input type="radio" name="'.$this->get_field_name( 'postFilter' ).'" value="showsolvedpostonly"  '.$instance['showsolvedpostonly'].'><label for="mc">'.__('Nur gelöste Anfragen', 'wp_pea').'</label><br>
                       <input type="radio" name="'.$this->get_field_name( 'postFilter' ).'" value="showunsolvedpostonly" '.$instance['showunsolvedpostonly'].'><label for="vi">'.__('Nur ungelöste Anfragen', 'wp_pea').'</label><br>
                       <input type="radio" name="'.$this->get_field_name( 'postFilter' ).'" value="nofilter" '.$instance['nofilter'].'><label for="ae">'.__('Kein Filter', 'wp_pea').'</label>
                    </fieldset>
                </p>';
    }
}

/*
 * Erstellt eine Übersichtsseite für PE-Anfragen per Shortcode ins Frontend.
 */
add_shortcode( 'pilzerkennungsuebersicht', 'wp_pea_requestsOverview' );
/*
 * $atts Übergebene Parameter aus dem Frontend. Zum Beispiel: [pilzerkennungsuebersicht showunsolvedpostonly="true"]
 */
function wp_pea_requestsOverview( $atts ) {
     $attribute = shortcode_atts( array(
         'showunsolvedpostonly' => 'false',
         'showsolvedpostonly' => 'false',
         'postsperpage' => '-1', // -1 = Alle Posts
     ), $atts );

    // Attribute einlesen
    $posts_per_page = $attribute['postsperpage'];;
    $showUnsolvedPostOnly = $attribute['showunsolvedpostonly'];
    $showSolvedPostOnly = $attribute['showsolvedpostonly'];

    // Variable zur Aufnahme der Post-Types (peanfragen)
    $posts;

    // Einlesen der Posts abhängig der eingelesen Attribute
    if($showUnsolvedPostOnly == 'true'){
        $posts = wp_pea_getAllUnsolvedRequests($posts_per_page);
    }
    else if($showSolvedPostOnly == 'true') {
        $posts = wp_pea_getAllSolvedRequests($posts_per_page);
    }
    else {
        $posts = wp_pea_getAllRequests($posts_per_page);
    }

    // HTML-Formatierung
    $htmlOutput =  '<aside id="Pilzerkennungsanfragen" class="widget widget_recent_entries">'.
                    '<h4 class="widget-title">PE-Anfragen</h4>';

    // Globale post-Variable laden (dadurch können bestimmte Funktionen in der gleich folgenden foreach Schleife verwendet werden)
    global $post;

    foreach($posts as $post){
        setup_postdata($post);

        // Benötigte Variablen laden
        $comments = wp_count_comments(get_the_ID()); // Gibt ein Array mit Kommentar-Informationen zum aktuellen Post zurück
        $commentCount = $comments->total_comments; // Die Anzahl der Kommentare für den aktuellen Post

        $status = get_post_meta(get_the_ID(), '_isSolved', true); // Status aus den Post-Meta-Daten einlesen (Gelöst/Ungelöst)

        // Einlesen des Bildes abhängig vom Status (Gelöst/Ungelöst)
        $picture;
        if($status == 'on') { // Gelöst
            $pictureURL = plugins_url( '/img/solved.png', __FILE__ );
            $picture = '<img src="'.$pictureURL.'" align="middle" height="50" width="50"/>';
        }
        else { // Ungelöst
            $pictureURL = plugins_url( '/img/notsolved.png', __FILE__ );
            $picture = '<img src="'.$pictureURL.'" align="middle" height="50" width="50"/>';
        }

        $htmlOutput .=  '<div style="width: 100%; height: 50px;">'.
                        '<div style="margin-right:5px; width:50px; height:50px; float:left;">' . $picture . '</div>'.
                        '<div style="width:auto;float:left;"><a href="'.get_permalink().'">'.get_the_title().'</a> </div>'.
                        '<div style="width:auto;float:right;">   </div>'.
                        '<div style="width:180px;float:right;">'.get_the_date().' '.get_the_time().' '.get_the_author().'</div>'.
                        '<div style="width:140px;float:right;">'.$commentCount.' Kommentare</div>'.
                        '</div>';
    }
    $htmlOutput .= '</aside>';
    return $htmlOutput;
}

/*
 * Funktion gibt alle ungelösten PE-Anfragen zurück
 * $postsPerPage Gibt die Anzahl der ausgewählten Anfragen an
 * $beginAtPosition Gibt von den ausgewählten Anfragen den Offset an
 */
function wp_pea_getAllUnsolvedRequests($postsPerPage, $beginAtPosition=0) {
    $posts = get_posts(
        array('post_type' => 'peanfrage',
            'posts_per_page' => $postsPerPage,
            'orderby' => 'date',
            'paged' => $beginAtPosition,
            'order' => 'DESC',
            'meta_key' => '_isSolved',
            'meta_value' => 'on',
            'meta_compare' => '!=',
            'meta_key' => '_isSolved',
            'meta_value' => '',
            'meta_compare' => 'NOT EXISTS'
        )
    );
    return $posts;
}

/*
 * Funktion gibt alle gelösten PE-Anfragen zurück
 * $postsPerPage Gibt die Anzahl der ausgewählten Anfragen an
 * $beginAtPosition Gibt von den ausgewählten Anfragen den Offset an
 */
function wp_pea_getAllSolvedRequests($postsPerPage, $beginAtPosition=0) {
    $posts = get_posts(
        array('post_type' => 'peanfrage',
            'posts_per_page' => $postsPerPage,
            'orderby' => 'date',
            'paged' => $beginAtPosition,
            'order' => 'DESC',
            'meta_key' => '_isSolved',
            'meta_value' => 'on',
        )
    );
    return $posts;
}
/*
 * Funktion gibt alle PE-Anfragen (ungelöste/gelöste) zurück
 * $postsPerPage Gibt die Anzahl der ausgewählten Anfragen an
 * $beginAtPosition Gibt von den ausgewählten Anfragen den Offset an
 */
function wp_pea_getAllRequests($postsPerPage, $beginAtPosition=0) {
    $posts = get_posts(
        array('post_type' => 'peanfrage',
            'posts_per_page' => $postsPerPage,
            'orderby' => 'date',
            'paged' => $beginAtPosition,
            'order' => 'DESC'
        )
    );
    return $posts;
}

function wp_pea_getVariable($name, $typ) {
    if($typ == 'POST')
        return (isset($_POST[$name]) && !empty($_POST[$name])) ? $_POST[$name] : false;
    else if ($typ == 'GET')
        return (isset($_GET[$name]) && !empty($_GET[$name])) ? $_GET[$name] : false;
    else
        return (isset($_FILES[$name]) && !empty($_FILES[$name])) ? $_FILES[$name] : false;
}

?>