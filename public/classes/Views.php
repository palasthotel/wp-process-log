<?php


namespace Palasthotel\ProcessLog;


use Palasthotel\ProcessLog\View\CommentMetaBoxView;

/**
 * @property CommentMetaBoxView commentMetaBox
 */
#[\AllowDynamicProperties]
class Views extends Component\Component {

	function onCreate() {
		$this->commentMetaBox = new CommentMetaBoxView($this->plugin);
	}
}