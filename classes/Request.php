<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 13:35
 */

namespace Palasthotel\ProcessLog;


/**
 * @property \Palasthotel\ProcessLog\Writer writer
 */
class Request {

	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'post_updated', array( $this, 'post_updated' ), 10, 3 );
		add_action( 'profile_update', array( $this, 'profile_update' ), 10, 2 );
		// TODO: create, save, delete post
		// TODO: create, save, delete user
		// TODO: create, save, delete taxonomy
		// TODO: comments
		//		add_action('shutdown', array($this, 'shutdown'));
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
		$attributes = array(
			"post_title",
			"guid",
			"post_name",
			"post_author",
			"post_excerpt",
			"post_content",
			"post_status",
			"post_date",
			"comment_status",
			"ping_status",
		);
		foreach ( $attributes as $attr ) {
			if ( $post_after->{$attr} != $post_before->{$attr} ) {
				$this->writer->addLog(
					ProcessLog::build()
					          ->setMessage( "update post" )
					          ->setAffectedPost( $post_id )
					          ->setLinkUrl( get_permalink( $post_id ) )
					          ->setChangedDataField( $attr )
					          ->setChangedDataValueOld( $post_before->{$attr} )
					          ->setChangedDataValueNew( $post_after->{$attr} )
				);
			}
		}

	}

	/**
	 * @param $user_id
	 * @param \WP_User $old_user_data
	 */
	public function profile_update( $user_id, $old_user_data ) {
		// after profile was saved

		$user = get_user_by( "id", $user_id );

		$userData = $user->data;
		$oldData  = $old_user_data->data;

		foreach ( $userData as $prop => $value ) {
			if ( $userData->{$prop} != $oldData->{$prop} ) {
				$this->writer->addLog(
					ProcessLog::build()
					          ->setMessage( "update user profile" )
					          ->setAffectedUser( $user_id )
					          ->setLinkUrl( get_edit_user_link( $user_id ) )
					          ->setChangedDataField( $prop )
					          ->setChangedDataValueOld( $oldData->{$prop} )
					          ->setChangedDataValueNew( $userData->{$prop} )
				);
			}
		}

	}

	//	public function shutdown(){
	//		$this->writer->addLog(
	//			ProcessLog::build()->setMessage("shutdown")
	//		);
	//	}

}