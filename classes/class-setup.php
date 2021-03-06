<?php
namespace Me\Stenberg\Content\Staging;

use Me\Stenberg\Content\Staging\XMLRPC\Client;

class Setup {

	private $router;
	private $plugin_url;

	public function __construct( Router $router, $plugin_url ) {
		$this->router     = $router;
		$this->plugin_url = $plugin_url;
	}

	/**
	 * Load assets.
	 */
	public function load_assets() {

		/*
		 * Register script files to be linked to a page later on using the
		 * wp_enqueue_script() function, which safely handles any script
		 * dependencies.
		 */
		wp_register_script( 'content-staging', $this->plugin_url . '/assets/js/content-staging.js', array( 'jquery' ), '1.2.6', false );

		// Register CSS stylesheet files for later use with wp_enqueue_style().
		wp_register_style( 'content-staging', $this->plugin_url . '/assets/css/content-staging.css', array(), '1.0.4' );

		/*
		 * Link script files to the generated page at the right time according to
		 * the script dependencies.
		 */
		wp_enqueue_script( 'content-staging' );

		// Add/enqueue CSS stylesheet files to the WordPress generated page.
		wp_enqueue_style( 'content-staging' );
	}

	/**
	 * Create custom post types.
	 *
	 * Should only be invoked through the 'init' action. It will not work if
	 * called before 'init' and aspects of the newly created post type will
	 * work incorrectly if called later.
	 */
	public function register_post_types() {

		// Arguments for content batch post type
		$batch = array(
			'label'  => __( 'Content Batches', 'sme-content-staging' ),
			'labels' => array(
				'singular_name'      => __( 'Content Batch', 'sme-content-staging' ),
				'add_new_item'       => __( 'Add New Content Batch', 'sme-content-staging' ),
				'edit_item'          => __( 'Edit Content Batch', 'sme-content-staging' ),
				'new_item'           => __( 'New Content Batch', 'sme-content-staging' ),
				'view_item'          => __( 'View Content Batch', 'sme-content-staging' ),
				'search_items'       => __( 'Search Content Batches', 'sme-content-staging' ),
				'not_found'          => __( 'No Content Batches found', 'sme-content-staging' ),
				'not_found_in_trash' => __( 'No Content Batches found in Trash', 'sme-content-staging' )
			),
			'description' => __( 'Content is divided into batches. Content Batches is a post type where each Content Batch is its own post.', 'sme-content-staging' ),
			'public'      => false,
			'supports'    => array( 'title', 'editor' ),
		);

		register_post_type( 'sme_content_batch', $batch );
	}

	public function register_menu_pages() {
		add_menu_page( 'Content Staging', 'Content Staging', 'manage_options', 'sme-list-batches', array( $this->router, 'batch_list' ) );
		add_submenu_page( 'sme-list-batches', 'History', 'History', 'manage_options', 'sme-batch-history', array( $this->router, 'batch_history' ) );
		add_submenu_page( 'sme-list-batches', 'Settings', 'Settings', 'manage_options', 'sme-settings', array( $this->router, 'settings_view' ) );
		add_submenu_page( null, 'Edit Batch', 'Edit', 'manage_options', 'sme-edit-batch', array( $this->router, 'batch_edit' ) );
		add_submenu_page( null, 'Delete Batch', 'Delete', 'manage_options', 'sme-delete-batch', array( $this->router, 'batch_confirm_delete' ) );
		add_submenu_page( null, 'Pre-Flight Batch', 'Pre-Flight', 'manage_options', 'sme-preflight-batch', array( $this->router, 'batch_prepare' ) );
		add_submenu_page( null, 'Quick Deploy Batch', 'Quick Deploy', 'manage_options', 'sme-quick-deploy-batch', array( $this->router, 'batch_deploy_quick' ) );
		add_submenu_page( null, 'Deploy Batch', 'Deploy', 'manage_options', 'sme-send-batch', array( $this->router, 'batch_deploy' ) );
	}

	/**
	 * Display a "Deploy To Production" button whenever a post is updated.
	 */
	public function quick_deploy_batch() {
		if ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && isset( $_GET['message'] ) && $_GET['action'] == 'edit' ) {
			?>
			<div class="updated">
				  <p><?php echo '<a href="' . admin_url( 'admin-post.php?action=sme-quick-deploy-batch&post_id=' . $_GET['post'] ) . '">Deploy To Production</a>'; ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Register XML-RPC methods.
	 *
	 * @param array $methods
	 * @return array
	 */
	public function register_xmlrpc_methods( $methods ) {

		$methods['smeContentStaging.verify'] = array( $this->router, 'batch_verify' );
		$methods['smeContentStaging.import'] = array( $this->router, 'batch_import' );
		$methods['smeContentStaging.importStatus'] = array( $this->router, 'batch_import_status' );

		return $methods;
	}

	public function set_postmeta_post_relation_keys( $meta_keys ) {
		if ( ! in_array( '_thumbnail_id', $meta_keys ) ) {
			$meta_keys[] = '_thumbnail_id';
		}

		return $meta_keys;
	}

}