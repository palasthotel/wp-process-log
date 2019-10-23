<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 13:35
 */

namespace Palasthotel\ProcessLog;

/**
 * @property Writer writer
 * @property ContentUserRelationWatcher content_user_relations
 * @property PostWatcher post
 * @property UserWatcher user
 * @property \Palasthotel\ProcessLog\TaxonomyWatcher taxonomy
 * @property \Palasthotel\ProcessLog\CommentWatcher comment
 * @property \Palasthotel\ProcessLog\ErrorWatcher error
 * @property \Palasthotel\ProcessLog\OptionsWatcher options
 */
class Watcher {

	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;

		$this->error = new ErrorWatcher($plugin);

		$this->content_user_relations = new ContentUserRelationWatcher($plugin);
		$this->post = new PostWatcher($plugin);
		$this->user = new UserWatcher($plugin);
		$this->taxonomy = new TaxonomyWatcher($plugin);
		$this->comment = new CommentWatcher($plugin);
		$this->options = new OptionsWatcher($plugin);


		add_filter(Plugin::FILTER_IS_USER_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_POST_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_COMMENT_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_OPTION_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_CUR_WATCHER_ACTIVE, array($this, 'core_watchers'));

	}

	public function isActive(){
		return apply_filters(Plugin::FILTER_CORE_WATCHERS_ACTIVE, true);
	}

	public function core_watchers(){
		return $this->isActive();
	}

}