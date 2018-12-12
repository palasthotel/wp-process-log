<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 13:35
 */

namespace Palasthotel\ProcessLog;


use Palasthotel\ProcessLog\Process\ContentUserRelations;
use Palasthotel\ProcessLog\Process\Post;
use Palasthotel\ProcessLog\Process\User;

/**
 * @property \Palasthotel\ProcessLog\Writer writer
 * @property \Palasthotel\ProcessLog\Process\User user
 * @property \Palasthotel\ProcessLog\Process\ContentUserRelations content_user_relations
 * @property \Palasthotel\ProcessLog\Process\Post post
 */
class Process {

	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'post_updated', array( $this, 'post_updated' ), 10, 3 );

		$this->post = new Post($plugin);
		$this->user = new User($plugin);
		$this->content_user_relations = new ContentUserRelations($plugin);


		// TODO: create, save, delete taxonomy
		// TODO: comments
	}



}