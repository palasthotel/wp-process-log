<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.18
 * Time: 13:28
 */


use Palasthotel\ProcessLog\ProcessLog;
use PHPUnit\Framework\TestCase;


class ProcessLogTest extends TestCase {

	public function testLogArgs(){
		$log = ProcessLog::build()->setMessage("test");

		$this->assertEquals('test', $log->getMessage());
		$this->assertEquals(22, count($log->args()));
	}

}