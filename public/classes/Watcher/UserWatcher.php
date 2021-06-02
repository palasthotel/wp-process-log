<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 16:37
 */

namespace Palasthotel\ProcessLog\Watcher;


use Palasthotel\ProcessLog\Plugin;
use Palasthotel\ProcessLog\Model\ProcessLog;
use Palasthotel\ProcessLog\Writer;

/**
 * @property Writer writer
 */
class UserWatcher {

	/**
	 * User constructor.
	 *
	 * @param Plugin $plugin
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
	 * @return boolean
	 */
	public function isActive() {
		return apply_filters( Plugin::FILTER_IS_USER_WATCHER_ACTIVE, true );
	}

	/**
	 * new user registered
	 *
	 * @param $user_id
	 */
	public function user_register( $user_id ) {

		if ( ! $this->isActive() ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_USER_REGISTER )
			          ->setMessage( "user register" )
			          ->setAffectedUser( $user_id )
			          ->setLinkUrl( \get_edit_user_link( $user_id ) )
		);
	}

	/**
	 * @param $user_id
	 * @param \WP_User $old_user_data
	 */
	public function profile_update( $user_id, $old_user_data ) {
		// after profile was saved

		if ( ! $this->isActive() ) {
			return;
		}

		$user = \get_user_by( "id", $user_id );

		$userData = $user->data;
		$oldData  = $old_user_data->data;

		foreach ( $userData as $prop => $value ) {
			if ( $userData->{$prop} != $oldData->{$prop} ) {
				$this->writer->addLog(
					ProcessLog::build()
					          ->setEventType( Plugin::EVENT_TYPE_UPDATE )
					          ->setMessage( "user profile update" )
					          ->setAffectedUser( $user_id )
					          ->setLinkUrl( \get_edit_user_link( $user_id ) )
					          ->setChangedDataField( $prop )
					          ->setChangedDataValueOld( $oldData->{$prop} )
					          ->setChangedDataValueNew( $userData->{$prop} )
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

		if ( ! $this->isActive() ) {
			return;
		}

		$old_value = \get_user_meta( $user_id, $meta_key, true );

		if ( $old_value == $meta_value ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_UPDATE )
			          ->setMessage( "user metadata update" )
			          ->setAffectedUser( $user_id )
			          ->setLinkUrl( \get_edit_user_link( $user_id ) )
			          ->setChangedDataField( $meta_key )
			          ->setChangedDataValueOld( ( is_array( $old_value ) || is_object( $old_value ) ) ?
				          json_encode( $old_value ) : $old_value )
			          ->setChangedDataValueNew( ( is_array( $meta_value ) || is_object( $meta_value ) ) ?
				          json_encode( $meta_value ) : $meta_value )
		);
	}

	/**
	 * @param $user_id
	 */
	public function delete_user( $user_id ) {

		if ( ! $this->isActive() ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_DELETE )
			          ->setMessage( "user delete" )
			          ->setAffectedUser( $user_id )
		);
	}
}