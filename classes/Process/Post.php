<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 16:37
 */

namespace Palasthotel\ProcessLog\Process;

use Palasthotel\ProcessLog\Plugin;
use Palasthotel\ProcessLog\ProcessLog;

/**
 * @property \Palasthotel\ProcessLog\Writer writer
 */
class Post {

	/**
	 * User constructor.
	 *
	 * @param \Palasthotel\ProcessLog\Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;
		// TODO: create, save, delete post
	}

	/**
	 * new user registered
	 *
	 * @param $user_id
	 */
	public function user_register( $user_id ) {
		$this->writer->addLog(
			ProcessLog::build()
			          ->setMessage( "user register" )
			          ->setAffectedUser( $user_id )
			          ->setLinkUrl( get_edit_user_link( $user_id ) )
		);
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