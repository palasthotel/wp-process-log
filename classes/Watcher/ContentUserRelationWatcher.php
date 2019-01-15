<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 16:37
 */

namespace Palasthotel\ProcessLog;

/**
 * @property Writer writer
 */
class ContentUserRelationWatcher {

	/**
	 * User constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;
		add_action( 'content_user_relations_add_relation_after', array(
			$this,
			'add_relation',
		), 10, 3 );
		add_action( 'content_user_relations_remove_relation_after', array(
			$this,
			'remove_relation',
		), 10, 3 );
	}


	/**
	 * @param $user_id
	 * @param $post_id
	 * @param $typeState_id
	 */
	public function add_relation( $user_id, $post_id, $typeState_id ) {
		$this->writer->addLog(
			ProcessLog::build()
			          ->setMessage( "content user relation added" )
			          ->setAffectedUser( $user_id )
			          ->setAffectedPost( $post_id )
			          ->setChangedDataField( "typestate_id" )
			          ->setChangedDataValueNew( $typeState_id )
		);
	}
	/**
	 * @param $user_id
	 * @param $post_id
	 * @param $typeState_id
	 */
	public function remove_relation( $user_id, $post_id, $typeState_id ) {
		$this->writer->addLog(
			ProcessLog::build()
			          ->setMessage( "content user relation removed" )
			          ->setAffectedUser( $user_id )
			          ->setAffectedPost( $post_id )
			          ->setChangedDataField( "typestate_id" )
			          ->setChangedDataValueOld( $typeState_id )
		);
	}
}