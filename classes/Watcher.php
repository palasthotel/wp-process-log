<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 13:35
 */

namespace Palasthotel\ProcessLog;

use Palasthotel\ProcessLog\Request\TaxonomyWatcher;

/**
 * @property Writer writer
 * @property ContentUserRelationWatcher content_user_relations
 * @property PostWatcher post
 * @property UserWatcher user
 * @property \Palasthotel\ProcessLog\Request\TaxonomyWatcher taxonomy
 * @property \Palasthotel\ProcessLog\CommentWatcher comment
 */
class Watcher {

	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;

		$this->content_user_relations = new ContentUserRelationWatcher($plugin);
		$this->post = new PostWatcher($plugin);
		$this->user = new UserWatcher($plugin);
		$this->taxonomy = new TaxonomyWatcher($plugin);
		$this->comment = new CommentWatcher($plugin);


		add_filter(Plugin::FILTER_IS_USER_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_POST_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_CUR_WATCHER_ACTIVE, array($this, 'core_watchers'));
	}

	public function isActive(){
		return apply_filters(Plugin::FILTER_CORE_WATCHERS_ACTIVE, true);
	}

	public function core_watchers(){
		return $this->isActive();
	}

}