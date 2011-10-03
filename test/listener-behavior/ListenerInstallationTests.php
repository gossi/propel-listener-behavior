<?php

require_once 'helpers/ListenerBehaviorTestBase.php';

class ListenerInstallationTest extends ListenerBehaviorTestBase
{
	public function setUp()
	{
		ListenerQuery::create()->deleteAll();
		TableQuery::create()->deleteAll();
	}
	
	// Helper Functions
	
	public function countGlobalListeners() 
	{
		$ids = array();
		$listeners = Listener::getGlobalListeners();
		
		if (!count($listeners)) {
			return 0;
		}
		
		foreach ($listeners as $targets) {
			foreach ($targets as $events) {
				foreach ($events as $l) {
					$ids[] = $l->getId();
				}
			}
		}
		
		return count(array_unique($ids));
	}
	
	// Tests
	
	public function testGlobalListener()
	{
		// add
		Table::addGlobalListener('listener');
		
		$this->assertEquals(1, ListenerQuery::create()->count());
		$this->assertEquals(1, $this->countGlobalListeners());
		
		$l = ListenerQuery::create()->findOne();
		
		$this->assertEquals('Table', $l->getTarget());
		$this->assertEquals('all', $l->getEvent());
		
		// remove
		Table::removeGlobalListener('listener');

		$this->assertEquals(0, ListenerQuery::create()->count());
		$this->assertEquals(0, $this->countGlobalListeners());
	}
	
	public function testGlobalListenerOn()
	{
		// add
		Table::addGlobalListener(array('on' => 'Listener'));
		
		$this->assertEquals(1, ListenerQuery::create()->count());
		$this->assertEquals(1, $this->countGlobalListeners());
		
		$l = ListenerQuery::create()->findOne();
		$this->assertEquals('Listener', $l->getOn());
		
		// remove
		Table::removeGlobalListener(array('on' => 'Listener'));
		
		$this->assertEquals(0, ListenerQuery::create()->count());
		$this->assertEquals(0, $this->countGlobalListeners());
	}
	
	public function testGlobalListenerEvent()
	{
		// add
		Table::addGlobalListener(array(
			'callback' => 'listener',
			'event' => 'postUpdate' 
		));
		
		$this->assertEquals(1, ListenerQuery::create()->count());
		$this->assertEquals(1, $this->countGlobalListeners());
	
		$l = ListenerQuery::create()->findOne();
		$this->assertEquals('postUpdate', $l->getEvent());
		
		// remove
		Table::removeGlobalListener(array(
			'callback' => 'listener',
			'event' => 'postUpdate' 
		));
		
		$this->assertEquals(0, ListenerQuery::create()->count());
		$this->assertEquals(0, $this->countGlobalListeners());
	}
	
	public function testGlobalListenerParams()
	{
		// remove
		Table::addGlobalListener(array(
			'callback' => 'listener',
			'params' => array(
				'foo' => 'bar',
				'baz' => 'boink'
			)
		));
	
		$this->assertEquals(1, ListenerQuery::create()->count());
		$this->assertEquals(1, $this->countGlobalListeners());
		
		$l = ListenerQuery::create()->findOne();
		$this->assertEquals(array(
				'foo' => 'bar',
				'baz' => 'boink'
			), $l->getParams());
		
		// remove
		Table::removeGlobalListener(array(
			'callback' => 'listener',
			'params' => array(
				'foo' => 'bar',
				'baz' => 'boink'
			)
		));
		
		$this->assertEquals(0, ListenerQuery::create()->count());
		$this->assertEquals(0, $this->countGlobalListeners());
	}
	
	public function testLocalListenerSimpleWay()
	{
		// add
		$t = new Table();
		$t->save();
		$t->addListener('listener');
		
		$l = ListenerQuery::create()->findOne();
		$this->assertEquals(1, ListenerQuery::create()->count());
		$this->assertEquals($t->getId(), $l->getRefId());
		
		// remove
		$t->removeListener('listener');
		$this->assertEquals(0, ListenerQuery::create()->count());
	}
	
	public function testLocalListener()
	{
		// add
		$t = new Table();
		$t->addListener('listener');
	
		$this->assertEquals(0, ListenerQuery::create()->count());
	
		$t->save();
		
		$l = ListenerQuery::create()->findOne();
		$this->assertEquals(1, ListenerQuery::create()->count());
		$this->assertEquals($t->getId(), $l->getRefId());

		// remove
// 		$t->removeListener('listener');
// 		$this->assertEquals(0, ListenerQuery::create()->count());
	}
}
?>