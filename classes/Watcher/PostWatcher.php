<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 16:37
 */

namespace Palasthotel\ProcessLog;

use Palasthotel\ProcessLog\Plugin;
use Palasthotel\ProcessLog\ProcessLog;
use Palasthotel\ProcessLog\Writer;

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
		// TODO: create, save, delete post
		add_action( 'post_updated', array( $this, 'post_updated' ), 10, 3 );
	}

	/**
	 * @param null|int $post_id
	 *
	 * @return boolean
	 */
	public function isActive($post_id = null){
		return apply_filters(Plugin::FILTER_IS_POST_WATCHER_ACTIVE, true, $post_id);
	}

	/**
	 * @param $post_id
	 * @param \WP_Post $post_after
	 * @param \WP_Post $post_before
	 */
	public function post_updated( $post_id, $post_after, $post_before ) {

		if(!$this->isActive($post_id)) return;

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
				$result = $this->writer->addLog(
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
}