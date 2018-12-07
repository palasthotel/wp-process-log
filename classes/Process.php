<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 13:35
 */

namespace Palasthotel\ProcessLog;


use Palasthotel\ProcessLog\Process\User;

/**
 * @property \Palasthotel\ProcessLog\Writer writer
 * @property \Palasthotel\ProcessLog\Process\User user
 */
class Process {

	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'post_updated', array( $this, 'post_updated' ), 10, 3 );

		$this->user = new User($plugin);


		// TODO: create, save, delete post
		// TODO: create, save, delete user
		// TODO: create, save, delete taxonomy
		// TODO: comments
	}

	public function save_post( $post_id ) {
		//		$this->writer->addLog(
		//			ProcessLog::build()
		//			          ->setMessage("save post")
		//			->setAffectedPost($post_id)
		//			->setLinkUrl(get_permalink($post_id))
		//		);
	}

	/**
	 * @param $post_id
	 * @param \WP_Post $post_after
	 * @param \WP_Post $post_before
	 */
	public function post_updated( $post_id, $post_after, $post_before ) {
		if ( $post_after->post_title != $post_before->post_title ) {
			$this->writer->addLog(
				ProcessLog::build()
				          ->setMessage( "update post" )
				          ->setAffectedPost( $post_id )
				          ->setLinkUrl( get_permalink( $post_id ) )
				          ->setChangedDataField( "post_title" )
				          ->setChangedDataValuesOld( $post_before->post_title )
				          ->setChangedDataValuesNew( $post_after->post_title )
			);
		}
		if ( $post_after->guid != $post_before->guid ) {
			$this->writer->addLog(
				ProcessLog::build()
				          ->setMessage( "update post" )
				          ->setAffectedPost( $post_id )
				          ->setLinkUrl( get_permalink( $post_id ) )
				          ->setChangedDataField( "guid" )
				          ->setChangedDataValuesOld( $post_before->guid )
				          ->setChangedDataValuesNew( $post_after->guid )
			);
		}
	}


}