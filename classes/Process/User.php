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
class User {

	/**
	 * User constructor.
	 *
	 * @param \Palasthotel\ProcessLog\Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;
		add_action( 'user_register', array( $this, 'user_register' ) );
		add_action( 'profile_update', array( $this, 'profile_update' ), 10, 2 );
		add_action( "update_user_meta", array(
			$this,
			'update_user_meta',
		), 10, 4 );
		add_action( 'delete_user', array( $this, 'delete_user' ) );
		// TODO: create, save, delete user
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
					          ->setMessage( "user profile update" )
					          ->setAffectedUser( $user_id )
					          ->setLinkUrl( get_edit_user_link( $user_id ) )
					          ->setChangedDataField( $prop )
					          ->setChangedDataValuesOld( $oldData->{$prop} )
					          ->setChangedDataValuesNew( $userData->{$prop} )
				);
			}
		}

	}

	/**
	 * log user metadata changes
	 *
	 * @param $user_id
	 * @param $meta_key
	 * @param $meta_value
	 *
	 */
	public function update_user_meta( $meta_id, $user_id, $meta_key, $meta_value ) {
		$old_value = get_user_meta( $user_id, $meta_key, true );

		if ( $old_value == $meta_value ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setMessage( "user metadata update" )
			          ->setAffectedUser( $user_id )
			          ->setLinkUrl( get_edit_user_link( $user_id ) )
			          ->setChangedDataField( $meta_key )
			          ->setChangedDataValuesOld( $old_value )
			          ->setChangedDataValuesNew( $meta_value )
		);
	}

	/**
	 * @param $user_id
	 */
	public function delete_user( $user_id ) {
		$this->writer->addLog(
			ProcessLog::build()
			          ->setMessage( "user delete" )
			          ->setAffectedUser( $user_id )
		);
	}
}