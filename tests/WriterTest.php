<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.18
 * Time: 13:28
 */


use PHPUnit\Framework\TestCase;


class WriterTest extends TestCase {

	public function testTest() {
		$this->assertNull( null );
	}

	public function writerClassExists(){
		$writer = new \Palasthotel\ProcessLog\Writer();
		assertNotNull($writer);
	}

}