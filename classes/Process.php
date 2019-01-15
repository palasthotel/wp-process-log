<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 12:21
 */

namespace Palasthotel\ProcessLog;

class Process extends DatabaseItem {

	var $id = NULL;
	var $created = NULL;

	var $location_url = NULL;
	var $referer_url = NULL;
	var $hostname = NULL;
	var $active_user = NULL;

	/**
	 * Process constructor.
	 */
	public function __construct() {

		$user = wp_get_current_user();
		if ( $user instanceof \WP_User ) {
			$this->active_user = $user->ID;
		}

		if ( isset( $_SERVER ) && is_array( $_SERVER ) ) {
			$this->location_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			$this->referer_url  = isset( $_SERVER["HTTP_REFERER"] ) ? $_SERVER["HTTP_REFERER"] : NULL;
			$this->hostname     = isset( $_SERVER['REMOTE_HOST'] ) ? $_SERVER['REMOTE_HOST'] : NULL;
		}
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function isArg( $key ) {
		return ( $key != "created" );
	}

	/**
	 * @return Process
	 */
	public static function build(){
		return new Process();
	}

	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return null
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @return null|string
	 */
	public function getLocationUrl() {
		return $this->location_url;
	}

	/**
	 * @return null|string
	 */
	public function getRefererUrl() {
		return $this->referer_url;
	}

	/**
	 * @return null|string
	 */
	public function getHostname() {
		return $this->hostname;
	}
}