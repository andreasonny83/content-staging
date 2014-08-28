<?php
namespace Me\Stenberg\Content\Staging\DB;

use Me\Stenberg\Content\Staging\DB\Mappers\Batch_Importer_Mapper;
use Me\Stenberg\Content\Staging\Models\Batch_Importer;

class Batch_Importer_DAO extends DAO {

	private $importer_mapper;

	public function __construct( $wpdb, Batch_Importer_Mapper $importer_mapper ) {
		parent::__constuct( $wpdb );

		$this->importer_mapper = $importer_mapper;
	}

	/**
	 * Get importer by id.
	 *
	 * @param $id
	 * @return Batch_Importer
	 */
	public function get_importer_by_id( $id ) {
		$query = $this->wpdb->prepare(
			'SELECT * FROM ' . $this->wpdb->posts . ' WHERE ID = %d',
			$id
		);

		return $this->importer_mapper->array_to_importer_object( $this->wpdb->get_row( $query, ARRAY_A ) );
	}

	/**
	 * @param Batch_Importer $importer
	 */
	public function insert_importer( Batch_Importer $importer ) {

		/*
		 * Name (slug) of this importer. Importers are inserted into the posts
		 * table. Every post should have a name.
		 */
		$name = '';

		$importer->set_date( current_time( 'mysql' ) );
		$importer->set_date_gmt( current_time( 'mysql', 1 ) );
		$importer->set_modified( $importer->get_date() );
		$importer->set_modified_gmt( $importer->get_date_gmt() );

		$data = $this->filter_importer_data( $importer );

		$importer->set_id( wp_insert_post( $data['values'] ) );

		/*
		 * If no 'post_name' has been created for this importer, then use the
		 * newly generated ID as 'post_name'.
		 */
		if ( ! $name ) {
			$this->update(
				'posts',
				array(
					'post_name' => $importer->get_id(),
				),
				array(
					'ID' => $importer->get_id(),
				),
				array( '%s' ),
				array( '%s' )
			);
		}
	}

	/**
	 * @param Batch_Importer $importer
	 */
	public function update_importer( Batch_Importer $importer ) {

		$importer->set_modified( current_time( 'mysql' ) );
		$importer->set_modified_gmt( current_time( 'mysql', 1 ) );

		$data = $this->filter_importer_data( $importer );

		$this->update(
			'posts', $data['values'], array( 'ID' => $importer->get_id() ), $data['format'], array( '%d' )
		);
	}

	/**
	 * Delete provided importer.
	 *
	 * Set 'post_status' for provided importer to 'draft'. This will hide the
	 * importer from users, but keep it for future references.
	 *
	 * Empty 'post_content'. Since importers can store huge batches in the
	 * 'post_content' field this is just a precaution so we do not fill the
	 * users database with a lot of unnecessary data.
	 *
	 * @param Batch_Importer $importer
	 */
	public function delete_importer( Batch_Importer $importer ) {

		$this->wpdb->update(
			$this->wpdb->posts,
			array(
				'post_content' => '',
				'post_status'  => 'draft',
			),
			array(
				'ID' => $importer->get_id(),
			),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * @param Batch_Importer $importer
	 * @return array
	 */
	private function filter_importer_data( Batch_Importer $importer ) {

		$values = array(
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_type'      => 'sme_batch_importer'
		);

		$format = array( '%s', '%s', '%s', '%s' );

		if ( $importer->get_creator_id() ) {
			$values['post_author'] = $importer->get_creator_id();
			$format[]              = '%d';
		}

		if ( $importer->get_date() ) {
			$values['post_date'] = $importer->get_date();
			$format[]            = '%s';
		}

		if ( $importer->get_date_gmt() ) {
			$values['post_date_gmt'] = $importer->get_date_gmt();
			$format[]                = '%s';
		}

		if ( $importer->get_modified() ) {
			$values['post_modified'] = $importer->get_modified();
			$format[]                = '%s';
		}

		if ( $importer->get_modified_gmt() ) {
			$values['post_modified_gmt'] = $importer->get_modified_gmt();
			$format[]                    = '%s';
		}

		if ( $importer->get_batch() ) {
			$values['post_content'] = base64_encode( serialize( $importer->get_batch() ) );
			$format[]               = '%s';
		}

		return array(
			'values' => $values,
			'format' => $format,
		);
	}

}