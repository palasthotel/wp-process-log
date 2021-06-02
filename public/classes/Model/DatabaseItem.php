<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-01-15
 * Time: 12:18
 */

namespace Palasthotel\ProcessLog\Model;


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
			$args[ $key ] = $this->prepareForInsert($value);
		}

		return $args;
	}

	public function getTimestamp(){
		$now = new \DateTime();
		$now->setTimezone( new \DateTimeZone( get_option("timezone_string") ) );
		return $now->format('Y-m-d H:i:s');
	}

	public function isArg($key){
		return true;
	}

	public function prepareForInsert($value){
		if(is_array($value) || is_object($value)) return json_encode($value);
		return $value;
	}
}