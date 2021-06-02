<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-01-15
 * Time: 15:51
 */

namespace Palasthotel\ProcessLog\Watcher;


use Palasthotel\ProcessLog\Plugin;
use Palasthotel\ProcessLog\Model\ProcessLog;
use Palasthotel\ProcessLog\Writer;

/**
 * @property Writer writer
 */
class CommentWatcher {

	public function __construct(Plugin $plugin) {
		$this->writer = $plugin->writer;
		add_action('wp_insert_comment', array($this, 'wp_insert_comment'), 10 , 2);
		add_action('transition_comment_status', array($this, 'transition_comment_status'),10, 3);
		add_filter('wp_update_comment_data', array($this, 'wp_update_comment_data'), 10 , 3);
	}

	/**
	 * @param null|int $comment_id
	 *
	 * @return boolean
	 */
	public function isActive( $comment_id = NULL ) {
		return apply_filters( Plugin::FILTER_IS_COMMENT_WATCHER_ACTIVE, true, $comment_id );
	}

	/**
	 * @param int $comment_id
	 * @param \WP_Comment $comment
	 */
	public function wp_insert_comment($comment_id, $comment){
		if(!$this->isActive($comment_id)) return;

		$log = ProcessLog::build()
		                 ->setEventType( Plugin::EVENT_TYPE_CREATE )
		                 ->setMessage( "insert comment" )
		                 ->setAffectedPost($comment->comment_post_ID)
		                 ->setAffectedComment($comment_id)
		                 ->setLinkUrl( \get_comment_link( $comment ) );

		if($comment->user_id > 0){
			$log->setAffectedUser($comment->user_id);
		}

		$this->writer->addLog($log);


	}

	/**
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Comment $comment
	 */
	public function transition_comment_status($new_status, $old_status, $comment){
		if(!$this->isActive($comment->comment_ID)) return;

		$log = ProcessLog::build()
		                 ->setEventType( Plugin::EVENT_TYPE_UPDATE )
		                 ->setMessage( "update comment status" )
		                 ->setAffectedPost($comment->comment_post_ID)
		                 ->setAffectedComment($comment->comment_ID)
		                 ->setLinkUrl( \get_comment_link( $comment ) )
		                 ->setChangedDataField("comment_approved")
						 ->setChangedDataValueOld($old_status)
		                 ->setChangedDataValueNew( $new_status );

		if($comment->user_id > 0){
			$log->setAffectedUser($comment->user_id);
		}

		$this->writer->addLog( $log );
	}

	/**
	 * @param array $data
	 * @param array $oldComment
	 * @param array $rawComment
	 *
	 * @return array
	 */
	public function wp_update_comment_data($data, $oldComment, $rawComment){
		$comment_id = $rawComment["comment_ID"];
		if($this->isActive($rawComment["comment_ID"])){

			$attributes = array(
				"comment_post_ID",
				"comment_author",
				"comment_author_email",
				"comment_author_url",
				"comment_author_IP",
				"comment_date",
				"comment_date_gmt",
				"comment_content",
				"comment_karma",
				"comment_approved",
				"comment_agent",
				"comment_type",
				"comment_parent",
				"user_id"
			);
			$post_id = $rawComment['comment_post_ID'];

			foreach ( $attributes as $attr ) {
				if(!isset($oldComment[$attr]) || !isset($data[$attr])) continue;
				if($oldComment[$attr] !== $data[$attr]){
					$this->writer->addLog(
						ProcessLog::build()
						          ->setEventType( Plugin::EVENT_TYPE_UPDATE )
						          ->setMessage( "update comment" )
						          ->setAffectedPost( $post_id )
						          ->setAffectedComment($comment_id)
						          ->setLinkUrl( \get_comment_link( $comment_id ) )
						          ->setChangedDataField( $attr )
						          ->setChangedDataValueOld( $oldComment[$attr] )
						          ->setChangedDataValueNew( $data[$attr] )
					);
				}
			}

		}

		return $data;
	}

}
