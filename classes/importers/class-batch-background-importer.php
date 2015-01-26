<?php
namespace Me\Stenberg\Content\Staging\Importers;

use Me\Stenberg\Content\Staging\Apis\Common_API;
use Me\Stenberg\Content\Staging\Background_Process;
use Me\Stenberg\Content\Staging\Helper_Factory;
use Me\Stenberg\Content\Staging\Models\Batch_Import_Job;

class Batch_Background_Importer extends Batch_Importer {

	/**
	 * @var Common_API
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * @param Batch_Import_Job $job
	 */
	public function __construct( Batch_Import_Job $job ) {
		parent::__construct( $job );
		$this->api = Helper_Factory::get_instance()->get_api( 'Common' );
	}

	/**
	 * Start importer background process on production environment.
	 */
	public function run() {

		// Make sure background import for this job is not already running.
		if ( $this->job->get_status() > 0 ) {
			return;
		}

		// Default site path.
		$site_path = '/';

		// Site path in multi-site setup.
		if ( is_multisite() ) {
			$site      = get_blog_details();
			$site_path = $site->path;
		}

		// Trigger import script.
		$import_script      = dirname( dirname( dirname( __FILE__ ) ) ) . '/scripts/import-batch.php';
		$background_process = new Background_Process(
			'php ' . $import_script . ' ' . ABSPATH . ' ' . get_site_url() . ' ' . $this->job->get_id() . ' ' . $site_path . ' ' . $this->job->get_key()
		);

		if ( file_exists( $import_script ) ) {
			$background_process->run();
		}

		if ( $background_process->get_pid() ) {
			// Background import started.
			$this->job->set_status( 1 );
		} else {
			// Failed to start background import.
			$this->api->add_deploy_message( $this->job->get_id(), 'Batch import failed to start.', 'info' );
			$this->job->set_status( 2 );
		}

		$this->import_job_dao->update_job( $this->job );
	}

	/**
	 * Retrieve import status.
	 */
	public function status() {
		// Nothing here atm.
	}

	/**
	 * Import all data in a batch on production environment.
	 */
	public function import() {

		// Get the batch.
		$batch = $this->job->get_batch();

		// Import attachments.
		$this->import_attachments();

		// Create/update users.
		$this->import_users( $batch->get_users() );

		// Create/update posts.
		foreach ( $batch->get_posts() as $post ) {
			$this->import_post( $post );
		}

		// Import postmeta.
		foreach ( $batch->get_posts() as $post ) {
			$this->import_post_meta( $post );
		}

		// Update relationship between posts and their parents.
		$this->update_parent_post_relations();

		// Import custom data.
		$this->import_custom_data( $this->job );

		// Publish posts.
		$this->publish_posts();

		// Perform clean-up operations.
		$this->tear_down();

		/*
		 * Delete importer. Importer is not actually deleted, just set to draft
		 * mode. This is important since we need to access e.g. meta data telling
		 * us the status of the import even after import has finished.
		 */
		$this->import_job_dao->delete_job( $this->job );
	}

}