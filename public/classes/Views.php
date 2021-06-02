<?php


namespace Palasthotel\ProcessLog;


use Palasthotel\ProcessLog\View\CommentMetaBoxView;

/**
 * @property CommentMetaBoxView commentMetaBox
 */
class Views extends Component\Component {

	function onCreate() {
		$this->commentMetaBox = new CommentMetaBoxView($this->plugin);
	}
}