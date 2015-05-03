<?php

/**
 * Plugin Name: Import-Pilz-Formular
 * Description:
 * Version: 0.1
 * Author: Grundlagen CMS
 */
class gcms_pilzformular {
	const input_submit_name = 'pf_submit';
	const input_title_name = 'pf_name';
	const input_title_content = 'pf_content';
	const input_nonce_filed = 'pf_nonce_field';
	const input_toxic_name = 'pf_toxic';

	public function __construct() {
		add_shortcode( 'pilzformular', array( $this, 'managePilzFormShortcode' ) );
	}

	function createNewPilz() {
		if ( ! isset( $_POST[ self::input_nonce_filed ] )
		     || ! wp_verify_nonce( $_POST[ self::input_nonce_filed ], self::input_submit_name )
		) {
			echo '<h2>Sorry, your nonce did not verify.</h2>';
			exit;
		}

		if ( isset( $_POST[ self::input_submit_name ] ) ) {

			$pf_title   = sanitize_text_field( $_POST[ self::input_title_name ] );
			$pf_content = sanitize_text_field( $_POST[ self::input_title_content ] );

			$my_post = array(
				'post_title'   => $pf_title,
				'post_content' => $pf_content,
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => pilzDb::_POST_TYPE_NAME
			);

			// Insert the post into the database
			wp_insert_post( $my_post );
		}

		echo '<h2>Ihr Pilz wurde erfolgreich an die Pilzredaktion versendet.</h2>';
	}

	function printHtmlForm() {
		echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';

		wp_nonce_field( self::input_submit_name, self::input_nonce_filed );

		echo '<p>';
		echo 'Name: <br />';
		echo '<input type="text" name="' . self::input_title_name . '" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST[ self::input_title_name ] ) ? esc_attr( $_POST[ self::input_title_name ] ) : '' ) . '" size="40" />';
		echo '</p>';

		echo '<p>';
		echo 'Giftig oder giftig: <br />';
		echo '<input type="radio" id="toxic" name="' . self::input_toxic_name . '" value="giftig"><label for="toxic"> Giftig</label><br> ';
		echo '<input type="radio" id="atoxic" name="' . self::input_toxic_name . '" value="ungiftig"><label for="atoxic">  Ungiftig</label><br> ';
		echo ' </p>';

		echo '<p>';
		echo 'Beschreibung: <br />';
		echo '<textarea type="text" name="' . self::input_title_content . '" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST[ self::input_title_content ] ) ? esc_attr( $_POST[ self::input_title_content ] ) : '' ) . '" size="200" ></textarea>';
		echo '</p>';

		echo '<p><input type="submit" name="' . self::input_submit_name . '" value="Pilz absenden"/></p>';
		echo '</form>';
	}

	function hastPostContent() {
		return isset( $_POST[ self::input_submit_name ] );
	}

	function managePilzFormShortcode() {
		ob_start();

		if ( $this->hastPostContent() ) {
			$this->createNewPilz();
		} else {
			$this->printHtmlForm();
		}

		return ob_get_clean();
	}
}

$gcms_pilzformular = new gcms_pilzformular();


