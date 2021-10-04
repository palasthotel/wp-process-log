<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 12:21
 */

namespace Palasthotel\ProcessLog\Model;


use Palasthotel\ProcessLog\Plugin;

class ProcessLog extends DatabaseItem {

	var $id = NULL;
	var $process_id = NULL;
	var $created = NULL;

	var $event_type = Plugin::EVENT_TYPE_GENERAL;
	var $active_user = 0;
	var $message = "";
	var $note = "";
	var $comment = "";
	var $severity = Plugin::SEVERITY_TYPE_INFO;
	var $link_url = NULL;
	var $location_path = NULL;
	var $affected_post = NULL;
	var $affected_term = NULL;
	var $affected_user = NULL;
	var $affected_comment = NULL;

	var $expires = 0;

	var $changed_data_field = NULL;
	var $changed_data_value_old = NULL;
	var $changed_data_value_new = NULL;

	var $variables = NULL;
	var $blobdata = NULL;

	/**
	 * @return ProcessLog
	 */
	public static function build(){
		return new ProcessLog();
	}

	/**
	 * ProcessLog constructor.
	 */
	public function __construct( ) {

		$expires = time() + DAY_IN_SECONDS * 14;
		$this->expires = apply_filters(Plugin::FILTER_LOG_ITEM_EXPIRES, $expires, $expires );


		$this->created = $this->getTimestamp();
		
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			require_once ABSPATH . WPINC . '/pluggable.php';
		}
		$user = wp_get_current_user();
		if ( $user instanceof \WP_User ) {
			$this->active_user = $user->ID;
		}

		$bt = debug_backtrace();
		foreach ($bt as $trace){
			if(!isset($trace["file"])) continue;
			$file = $trace["file"];
			if(
				strpos($file, PROCESS_LOG_HANDLERS_DIR) === 0
				||
				( strpos($file, ABSPATH."wp-content") === 0 && strpos($file, PROCESS_LOG_DIR) === false )
			){
				$docroot_relative_file = "/".str_replace(ABSPATH, "", $trace["file"]);
				$this->location_path = $docroot_relative_file." Line ".$trace["line"];
				break;
			}

		}
	}

	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int|null
	 */
	public function getProcessId() {
		return $this->process_id;
	}

	/**
	 * @return null
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @return string
	 */
	public function getEventType(): string {
		return $this->event_type;
	}

	/**
	 * @return int
	 */
	public function getActiveUser(): int {
		return $this->active_user;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string {
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function getNote(): string {
		return $this->note;
	}

	/**
	 * @return string
	 */
	public function getComment(): string {
		return $this->comment;
	}

	/**
	 * @return string
	 */
	public function getSeverity(): string {
		return $this->severity;
	}

	/**
	 * @return null|string
	 */
	public function getLinkUrl() {
		return $this->link_url;
	}


	/**
	 * @return null|string
	 */
	public function getLocationPath() {
		return $this->location_path;
	}

	/**
	 * @return null|int
	 */
	public function getAffectedPost() {
		return $this->affected_post;
	}

	/**
	 * @return null|int
	 */
	public function getAffectedTerm() {
		return $this->affected_term;
	}

	/**
	 * @return null|int
	 */
	public function getAffectedUser() {
		return $this->affected_user;
	}

	/**
	 * @return null|int
	 */
	public function getAffectedComment() {
		return $this->affected_comment;
	}

	/**
	 * @return null|int
	 */
	public function getExpires() {
		return $this->expires;
	}

	/**
	 * @return null|string
	 */
	public function getChangedDataField() {
		return $this->changed_data_field;
	}

	/**
	 * @return null|string
	 */
	public function getChangedDataValuesOld() {
		return $this->changed_data_value_old;
	}

	/**
	 * @return null|string
	 */
	public function getChangedDataValuesNew() {
		return $this->changed_data_value_new;
	}

	/**
	 * @return object|null
	 */
	public function getVariables() {
		return $this->variables;
	}

	/**
	 * @return null|object
	 */
	public function getBlobdata() {
		return $this->blobdata;
	}

	/**
	 * @param int $id
	 *
	 * @return ProcessLog
	 */
	public function setId( $id ) {
		$this->id = $id;

		return $this;
	}

	/**
	 * @param int|null $id
	 *
	 * @return ProcessLog
	 */
	public function setProcessId( $id ) {
		$this->process_id = $id;

		return $this;
	}

	/**
	 * @param int $created
	 *
	 * @return ProcessLog
	 */
	public function setCreated( $created ) {
		$this->created = $created;

		return $this;
	}

	/**
	 * @param string $event_type
	 *
	 * @return ProcessLog
	 */
	public function setEventType( $event_type ) {
		$this->event_type = $event_type;

		return $this;
	}

	/**
	 * @param int $active_user
	 *
	 * @return ProcessLog
	 */
	public function setActiveUser( $active_user ) {
		$this->active_user = $active_user;

		return $this;
	}

	/**
	 * @param string $message
	 *
	 * @return ProcessLog
	 */
	public function setMessage( $message ) {
		$this->message = $message;

		return $this;
	}

	/**
	 * @param string $note
	 *
	 * @return ProcessLog
	 */
	public function setNote( $note ) {
		$this->note = $note;

		return $this;
	}

	/**
	 * @param string $comment
	 *
	 * @return ProcessLog
	 */
	public function setComment( $comment ) {
		$this->comment = $comment;

		return $this;
	}

	/**
	 * @param string $severity
	 *
	 * @return ProcessLog
	 */
	public function setSeverity( $severity ) {
		$this->severity = $severity;

		return $this;
	}

	/**
	 * @param string|null $link_url
	 *
	 * @return ProcessLog
	 */
	public function setLinkUrl( $link_url ) {
		$this->link_url = $link_url;

		return $this;
	}

	/**
	 * @param null $location_path
	 *
	 * @return ProcessLog
	 */
	public function setLocationPath( $location_path ) {
		$this->location_path = $location_path;
		return $this;
	}

	/**
	 * @param null $affected_post
	 *
	 * @return ProcessLog
	 */
	public function setAffectedPost( $affected_post ) {
		$this->affected_post = $affected_post;

		return $this;
	}

	/**
	 * @param null $affected_term
	 *
	 * @return ProcessLog
	 */
	public function setAffectedTerm( $affected_term ) {
		$this->affected_term = $affected_term;

		return $this;
	}

	/**
	 * @param null $affected_user
	 *
	 * @return ProcessLog
	 */
	public function setAffectedUser( $affected_user ) {
		$this->affected_user = $affected_user;

		return $this;
	}

	/**
	 * @param null $affected_comment
	 *
	 * @return ProcessLog
	 */
	public function setAffectedComment( $affected_comment ) {
		$this->affected_comment = $affected_comment;

		return $this;
	}

	/**
	 * @param float|int $expires
	 *
	 * @return ProcessLog
	 */
	public function setExpires( $expires ) {
		$this->expires = $expires;

		return $this;
	}

	/**
	 * @param string $changed_data_field
	 *
	 * @return ProcessLog
	 */
	public function setChangedDataField( $changed_data_field ) {
		$this->changed_data_field = $changed_data_field;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return ProcessLog
	 */
	public function setChangedDataValueOld( $value ) {
		$this->changed_data_value_old = $value;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return ProcessLog
	 */
	public function setChangedDataValueNew( $value ) {
		$this->changed_data_value_new = $value;

		return $this;
	}

	/**
	 * @param mixed $variables
	 *
	 * @return ProcessLog
	 */
	public function setVariables( $variables ) {
		$this->variables = $variables;

		return $this;
	}

	/**
	 * @param null $blob_data
	 *
	 * @return ProcessLog
	 */
	public function setBlobData( $blob_data ) {
		$this->blobdata = $blob_data;

		return $this;
	}

	/**
	 * use path of which file this call was triggered
	 * @return ProcessLog
	 */
	public function useLocationPath(){
		$bt = debug_backtrace();
		if(count($bt) > 0){
			$this->location_path = "/".str_replace(ABSPATH, "", $bt[0]["file"])." Line: ".$bt[0]["line"];
		}

		return $this;
	}
}
