<?php
namespace Me\Stenberg\Content\Staging\Controllers;

use Me\Stenberg\Content\Staging\View\Template;

class Settings_Ctrl {

	/**
	 * @var Template
	 */
	private $template;

	/**
	 * @param Template $template
	 */
	public function __construct( Template $template ) {

		$this->template  = $template;

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
	}

	public function init() {

		$data = array(
			'endpoint'   => get_option( 'sme_cs_endpoint' ),
			'secret_key' => get_option( 'sme_cs_secret_key' ),
		);

		$this->template->render( 'settings', $data );
	}

	public function register_settings() {
		register_setting( 'content-staging-settings', 'sme_cs_endpoint' );
		register_setting( 'content-staging-settings', 'sme_cs_secret_key' );
	}

}
