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
	 * @return boolean
	 */
	public function isActive() {
		return apply_filters( Plugin::FILTER_IS_CUR_WATCHER_ACTIVE, true );
	}


	/**
	 * @param $user_id
	 * @param $post_id
	 * @param $typeState_id
	 */
	public function add_relation( $user_id, $post_id, $typeState_id ) {

		if ( ! $this->isActive() ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_CREATE )
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

		if ( ! $this->isActive() ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_DELETE )
			          ->setMessage( "content user relation removed" )
			          ->setAffectedUser( $user_id )
			          ->setAffectedPost( $post_id )
			          ->setChangedDataField( "typestate_id" )
			          ->setChangedDataValueOld( $typeState_id )
		);
	}
}