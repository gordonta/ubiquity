<?php
use Ubiquity\log\Logger;
use Ubiquity\controllers\Startup;
use Ubiquity\log\LoggerParams;
use Ubiquity\log\LogMessage;

/**
 * UMonolog test case.
 */
class UMonologTest extends BaseTest {

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->config ["debug"] = true;
		Logger::init ( $this->config );
		$this->_startServices ();
		$this->_initRequest ( 'TestController', 'GET' );
	}

	protected function _startServices($what = false) {
		$this->_startCache ();
		$this->_startRouter ( $what );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->config ["debug"] = false;
	}

	/**
	 * Tests Logget::warn()
	 */
	public function navigate() {
		$logs = Logger::asObjects ();
		$this->assertEquals ( 0, sizeof ( $logs ) );
		$this->_initRequest ( 'TestController', 'GET' );
		Startup::run ( $this->config );
		$logs = Logger::asObjects ( false, null, LoggerParams::ROUTER );
		$this->assertEquals ( 1, sizeof ( $logs ) );
		$this->assertInstanceOf ( LogMessage::class, $logs [0] );
	}
}
