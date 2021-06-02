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

const BLACKLIST_POST_METAS = array(
	"_edit_lock",
	"_edit_last",
);

/**
 * @property Writer writer
 */
class PostWatcher {

	/**
	 * User constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;
		add_action( 'post_updated', array( $this, 'post_updated' ), 10, 3 );
		add_action( "add_post_meta", array( $this, 'add_meta' ), 10, 3 );
		add_action( "update_post_meta", array( $this, 'update_meta' ), 10, 4 );
		add_action( "delete_post_meta", array( $this, 'delete_meta' ), 10, 4 );

	}

	/**
	 * @param null|int $post_id
	 *
	 * @return boolean
	 */
	public function isActive( $post_id = NULL ) {
		return apply_filters( Plugin::FILTER_IS_POST_WATCHER_ACTIVE, true, $post_id );
	}

	/**
	 * @param $post_id
	 * @param \WP_Post $post_after
	 * @param \WP_Post $post_before
	 */
	public function post_updated( $post_id, $post_after, $post_before ) {

		if ( ! $this->isActive( $post_id ) ) {
			return;
		}

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
					          ->setEventType( Plugin::EVENT_TYPE_UPDATE )
					          ->setMessage( "update post" )
					          ->setAffectedPost( $post_id )
					          ->setLinkUrl( \get_edit_post_link( $post_id ) )
					          ->setChangedDataField( $attr )
					          ->setChangedDataValueOld( $post_before->{$attr} )
					          ->setChangedDataValueNew( $post_after->{$attr} )
				);
			}
		}
	}

	/**
	 * @param int $post_id
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	public function ignorePostMeta( $post_id, $meta_key ) {
		return apply_filters( Plugin::FILTER_IGNORE_POST_META, in_array( $meta_key, BLACKLIST_POST_METAS ), $post_id, $meta_key );
	}

	/**
	 * @param string $object_id post ID
	 * @param string $meta_key
	 * @param mixed $_meta_value
	 */
	public function add_meta( $object_id, $meta_key, $_meta_value ) {

		if ( ! $this->isActive( $object_id ) ) {
			return;
		}

		if ( $this->ignorePostMeta( $object_id, $meta_key ) ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_CREATE )
			          ->setMessage( "post meta add" )
			          ->setAffectedPost( $object_id )
			          ->setLinkUrl( \get_edit_post_link( $object_id ) )
			          ->setChangedDataField( $meta_key )
			          ->setChangedDataValueOld( ( is_array( $_meta_value ) || is_object( $_meta_value ) ) ?
				          json_encode( $_meta_value ) : $_meta_value )
		);
	}

	/**
	 * @param $meta_id
	 * @param $object_id
	 * @param $meta_key
	 * @param $_meta_value
	 *
	 */
	public function update_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {

		if ( ! $this->isActive( $object_id ) ) {
			return;
		}

		if ( $this->ignorePostMeta( $object_id, $meta_key ) ) {
			return;
		}

		$note = "";
		if ( function_exists( 'get_post_meta_by_id' ) ) {
			$meta       = \get_post_meta_by_id( $meta_id );
			$prev_value = $meta->meta_value;
		} else {
			$prev_value = \get_post_meta( $object_id, $meta_key );
			if ( is_countable( $prev_value ) && count( $prev_value ) == 1 ) {
				$prev_value = $prev_value[0];
			}
			$note = "get_post_meta_by_id function not available. Used get_post_meta instead.";
		}

		if ( $prev_value == $_meta_value ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_UPDATE )
			          ->setMessage( "post meta update" )
			          ->setAffectedPost( $object_id )
			          ->setLinkUrl( \get_edit_post_link( $object_id ) )
			          ->setChangedDataField( $meta_key )
			          ->setChangedDataValueOld( ( is_array( $prev_value ) || is_object( $prev_value ) ) ?
				          json_encode( $prev_value ) : $prev_value )
			          ->setChangedDataValueNew( ( is_array( $_meta_value ) || is_object( $_meta_value ) ) ?
				          json_encode( $_meta_value ) : $_meta_value )
			          ->setNote( $note )
		);


	}

	/**
	 * @param $meta_ids
	 * @param $object_id
	 * @param $meta_key
	 * @param $_meta_value
	 */
	public function delete_meta( $meta_ids, $object_id, $meta_key, $_meta_value ) {

		if ( ! $this->isActive( $object_id ) ) {
			return;
		}

		if ( $this->ignorePostMeta( $object_id, $meta_key ) ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_DELETE )
			          ->setMessage( "post meta delete " . count( $meta_ids ) . " entries" )
			          ->setAffectedPost( $object_id )
			          ->setLinkUrl( \get_edit_post_link( $object_id ) )
			          ->setChangedDataField( $meta_key )
			          ->setChangedDataValueOld( ( is_array( $_meta_value ) || is_object( $_meta_value ) ) ?
				          json_encode( $_meta_value ) : $_meta_value )
		);
	}
}