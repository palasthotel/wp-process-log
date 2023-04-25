<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-01-15
 * Time: 15:51
 */

namespace Palasthotel\ProcessLog\Watcher;


use Palasthotel\ProcessLog\Plugin;
use Palasthotel\ProcessLog\Model\ProcessLog;
use Palasthotel\ProcessLog\Writer;

/**
 * @property Writer writer
 */
class OptionsWatcher {
	public function __construct(Plugin $plugin) {
		$this->writer = $plugin->writer;
		add_action('added_option', array($this, 'added'),10,2);
		add_action('updated_option', array($this, 'updated'), 10, 3 );
		add_action('delete_option', array($this, 'delete'));
	}

	/**
	 * @return boolean
	 */
	public function isActive() {
		return apply_filters( Plugin::FILTER_IS_OPTION_WATCHER_ACTIVE, true );
	}

	public function ignore($option_name):bool {
		$isTransient = str_starts_with($option_name, "_transient") || str_starts_with($option_name, "_site_transient");
		return apply_filters(Plugin::FILTER_IGNORE_OPTION, $isTransient, $option_name);
	}

	public function added($option, $value){

		if ( ! $this->isActive() || $this->ignore($option) ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_CREATE )
			          ->setMessage( "added option" )
			          ->setChangedDataField( $option )
			          ->setChangedDataValueNew( $value )
		);
	}

	public function updated($option, $old_value, $value){

		if ( ! $this->isActive() || $this->ignore($option) ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_UPDATE )
			          ->setMessage( "update option" )
			          ->setChangedDataField( $option )
			          ->setChangedDataValueOld( $old_value )
			          ->setChangedDataValueNew( $value )
		);
	}

	public function delete($option){

		if ( ! $this->isActive() || $this->ignore($option) ) {
			return;
		}

		$this->writer->addLog(
			ProcessLog::build()
			          ->setEventType( Plugin::EVENT_TYPE_DELETE )
			          ->setMessage( "delete option" )
			          ->setChangedDataField( $option )
			          ->setChangedDataValueOld(get_option($option))
		);
	}
}
