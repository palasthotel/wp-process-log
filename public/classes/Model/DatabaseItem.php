<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-01-15
 * Time: 12:18
 */

namespace Palasthotel\ProcessLog\Model;


#[\AllowDynamicProperties]
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
		// current_time() also covers sites configured with a UTC offset instead of a
		// named timezone, where timezone_string is empty and DateTimeZone("") throws.
		return current_time( 'mysql' );
	}

	public function isArg($key){
		return true;
	}

	public function prepareForInsert($value){
		if(is_array($value) || is_object($value)) return json_encode($value);
		return $value;
	}
}