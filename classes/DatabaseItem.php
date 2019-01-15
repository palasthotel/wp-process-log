<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-01-15
 * Time: 12:18
 */

namespace Palasthotel\ProcessLog;


class DatabaseItem {

	/**
	 * @return array
	 */
	public function insertArgs() {
		$args = array();
		foreach ( $this as $key => $value ) {
			// let database decide the created time
			if ( !$this->isArg($key)) {
				continue;
			}
			$args[ $key ] = $value;
		}

		return $args;
	}

	public function isArg($key){
		return true;
	}
}