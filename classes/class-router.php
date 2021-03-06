<?php
namespace Me\Stenberg\Content\Staging;

use Me\Stenberg\Content\Staging\Controllers\Batch_Ctrl;
use Me\Stenberg\Content\Staging\Controllers\Batch_History_Ctrl;
use Me\Stenberg\Content\Staging\Controllers\Settings_Ctrl;

class Router {

	private $batch_ctrl;
	private $batch_history_ctrl;
	private $settings_ctrl;

	public function __construct( Batch_Ctrl $batch_ctrl, Batch_History_Ctrl $batch_history_ctrl, Settings_Ctrl $settings_ctrl ) {
		$this->batch_ctrl         = $batch_ctrl;
		$this->batch_history_ctrl = $batch_history_ctrl;
		$this->settings_ctrl 	  = $settings_ctrl;
	}

	public function batch_history() {
		$this->batch_history_ctrl->init();
	}

	public function batch_list() {
		$this->batch_ctrl->list_batches();
	}

	public function batch_edit() {
		$this->batch_ctrl->edit_batch();
	}

	public function batch_save() {
		$this->batch_ctrl->save_batch();
	}

	public function batch_delete() {
		$this->batch_ctrl->delete_batch();
	}

	public function batch_confirm_delete() {
		$this->batch_ctrl->confirm_delete_batch();
	}

	public function batch_prepare( $batch = null ) {
		$this->batch_ctrl->prepare( $batch );
	}

	public function batch_verify( array $args ) {
		return $this->batch_ctrl->verify( $args );
	}

	public function batch_deploy() {
		$this->batch_ctrl->deploy();
	}

	public function batch_deploy_quick() {
		$this->batch_ctrl->quick_deploy();
	}

	public function batch_import( array $args ) {
		return $this->batch_ctrl->import( $args );
	}

	public function batch_import_status( array $args ) {
		return $this->batch_ctrl->import_status( $args );
	}

	public function settings_view() {
		$this->settings_ctrl->init();
	}

	public function settings_save() {
		$this->settings_ctrl->save_settings();
	}

	public function ajax_preflight() {
		$this->batch_ctrl->preflight_status();
	}

	public function ajax_batch_import() {
		$this->batch_ctrl->import_status_request();
	}

}