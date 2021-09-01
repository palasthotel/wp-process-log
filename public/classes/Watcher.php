<?php

namespace Palasthotel\ProcessLog;

use Palasthotel\ProcessLog\Watcher\CommentWatcher;
use Palasthotel\ProcessLog\Watcher\ContentUserRelationWatcher;
use Palasthotel\ProcessLog\Watcher\ErrorWatcher;
use Palasthotel\ProcessLog\Watcher\OptionsWatcher;
use Palasthotel\ProcessLog\Watcher\PostWatcher;
use Palasthotel\ProcessLog\Watcher\TaxonomyWatcher;
use Palasthotel\ProcessLog\Watcher\UserWatcher;
use Palasthotel\ProcessLog\Watcher\WPMailWatcher;

/**
 * @property Writer writer
 * @property ContentUserRelationWatcher content_user_relations
 * @property PostWatcher post
 * @property UserWatcher user
 * @property TaxonomyWatcher taxonomy
 * @property CommentWatcher comment
 * @property ErrorWatcher error
 * @property OptionsWatcher options
 * @property WPMailWatcher $wpMail
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
		$this->wpMail = new WPMailWatcher($plugin);


		add_filter(Plugin::FILTER_IS_USER_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_POST_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_COMMENT_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_OPTION_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_CUR_WATCHER_ACTIVE, array($this, 'core_watchers'));
		add_filter(Plugin::FILTER_IS_MAIL_WATCHER_ACTIVE, array($this, 'core_watchers'));

	}

	public function isActive(){
		return apply_filters(Plugin::FILTER_CORE_WATCHERS_ACTIVE, true);
	}

	public function core_watchers(){
		return $this->isActive();
	}

}
