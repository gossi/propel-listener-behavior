<?php
require_once 'helpers/listenerTests.php';

class FunctionListenerTest extends ListenerTests
{	
	// with listeners added during runtime
	public function testGlobalAllEvents()
	{
		Table::addGlobalListener('listener');
		
		$this->asserts = array(
			array('event' => 'preSave'),
			array('event' => 'preInsert'),
			array('event' => 'postInsert'),
			array('event' => 'postSave'),
		);

		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		$this->asserts = array(
			array('event' => 'preSave'),
			array('event' => 'preUpdate'),
			array('event' => 'postUpdate'),
			array('event' => 'postSave'),
		);
		
		$t->setName('test2');
		$t->save();
		
		$this->asserts = array(
			array('event' => 'preDelete'),
			array('event' => 'postDelete'),
		);
		
		$t->delete();
		
		Table::removeGlobalListener('listener');
	}
	
	public function testGlobalNoEvents()
	{
		Table::addGlobalListener('listener');
		Table::removeGlobalListener('listener');
		
		$this->calls = 0;
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		$this->assertEquals(0, $this->calls);
		
		$this->calls = 0;
		$t->setName('test2');
		$t->save();
		
		$this->assertEquals(0, $this->calls);
		
		$this->calls = 0;
		$t->delete();
		$this->assertEquals(0, $this->calls);
	}
	
	public function testSingleEvents()
	{
		// preSave - preDelete
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'preSave'));
		
		$this->asserts = array(
		array('event' => 'preSave'),
		);
		
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'preSave'));
		
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'preDelete'));
		
		$this->asserts = array(
		array('event' => 'preDelete'),
		);
		$t->delete();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'preDelete'));
		
		// postSave - postDelete
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'postSave'));
		
		$this->asserts = array(
		array('event' => 'postSave'),
		);
		
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'postSave'));
		
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'postDelete'));
		
		$this->asserts = array(
		array('event' => 'postDelete'),
		);
		$t->delete();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'postDelete'));
		
		
		// preInsert - preUpdate
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'preInsert'));
		
		$this->asserts = array(
		array('event' => 'preInsert'),
		);
		
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'preInsert'));
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'preUpdate'));
		
		$this->asserts = array(
		array('event' => 'preUpdate'),
		);
		$t->delete();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'preUpdate'));
		
		// postInsert - postUpdate
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'postInsert'));
		
		$this->asserts = array(
		array('event' => 'postInsert'),
		);
		
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'postInsert'));
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'postUpdate'));
		
		$this->asserts = array(
		array('event' => 'postUpdate'),
		);
		$t->delete();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'postUpdate'));
	}
	
	public function testParams()
	{
		Table::addGlobalListener(array('callback' => 'listener', 'event' => 'preSave', 'params' => array(
			'foo' => 'bar',
			'baz' => 0
		)));
		
		$this->asserts = array(
		array('event' => 'preSave', 'params' => array(
			'foo' => 'bar',
			'baz' => 0
		)));
		
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		Table::removeGlobalListener(array('callback' => 'listener', 'event' => 'preSave', 'params' => array(
			'foo' => 'bar',
			'baz' => 0
		)));
	}
	
	public function testLocalListeners()
	{
		// preSave - preDelete
		$this->asserts = array(
		array('event' => 'preSave'),
		);
		
		$t = new Table();
		$t->setName('test1');
		$t->addListener(array('callback' => 'listener', 'event' => 'preSave'));
		$t->save();
		
		$t->removeListener(array('callback' => 'listener', 'event' => 'preSave'));
	}
}
?>