<?php
require_once 'ListenerBehaviorTestBase.php';
require_once 'listeners.php';

class ListenerTests extends ListenerBehaviorTestBase
{
	protected $asserts = array();
	protected $calls = 0;
	protected $boink = null;
	
	public function setUp()
	{
		global $cb; // woudln't work anywhere else
		$cb = $this;

		$this->asserts = array();
		ListenerQuery::create()->deleteAll();
		TableQuery::create()->deleteAll();
	}
	
	// helpers
	public function receiveCallback($e)
	{
		$asserts = array_shift($this->asserts); 
		
		$this->assertNotNull($asserts);
		
		foreach ($asserts as $k => $v) {
			$this->assertEquals($v, $e[$k]);
		}
		$this->calls++;
	}
}
?>