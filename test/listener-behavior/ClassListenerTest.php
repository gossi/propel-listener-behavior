<?php
require_once 'helpers/listenerTests.php';

class ClassListenerTest extends ListenerTests
{
	// with listeners added during runtime
	public function testHandleEvent()
	{
		Table::addGlobalListener(array('on' => 'MyBlankListener'));
		
		$this->asserts = array(
			array('event' => 'preSave', 'method' => 'handleEvent'),
			array('event' => 'preInsert', 'method' => 'handleEvent'),
			array('event' => 'postInsert', 'method' => 'handleEvent'),
			array('event' => 'postSave', 'method' => 'handleEvent'),
		);

		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		$this->asserts = array(
			array('event' => 'preSave', 'method' => 'handleEvent'),
			array('event' => 'preUpdate', 'method' => 'handleEvent'),
			array('event' => 'postUpdate', 'method' => 'handleEvent'),
			array('event' => 'postSave', 'method' => 'handleEvent'),
		);
		
		$t->setName('test2');
		$t->save();
		
		$this->asserts = array(
			array('event' => 'preDelete', 'method' => 'handleEvent'),
			array('event' => 'postDelete', 'method' => 'handleEvent'),
		);
		
		$t->delete();
		
		Table::removeGlobalListener(array('on' => 'MyBlankListener'));
	}
	
	public function testEventHandle()
	{
		Table::addGlobalListener(array('on' => 'MyListener'));
	
		$this->asserts = array(
		array('event' => 'preSave', 'method' => 'handleEvent'),
		array('event' => 'preInsert', 'method' => 'handleEvent'),
		array('event' => 'postInsert', 'method' => 'handleEvent'),
		array('event' => 'postSave', 'method' => 'onPostSave'),
		);
	
		$t = new Table();
		$t->setName('test1');
		$t->save();
	
		$this->asserts = array(
		array('event' => 'preSave', 'method' => 'handleEvent'),
		array('event' => 'preUpdate', 'method' => 'handleEvent'),
		array('event' => 'postUpdate', 'method' => 'handleEvent'),
		array('event' => 'postSave', 'method' => 'onPostSave'),
		);
	
		$t->setName('test2');
		$t->save();
	
		$this->asserts = array(
		array('event' => 'preDelete', 'method' => 'handleEvent'),
		array('event' => 'postDelete', 'method' => 'handleEvent'),
		);
	
		$t->delete();
	
		Table::removeGlobalListener(array('on' => 'MyListener'));
	}
	
	public function testFallbacks()
	{
		// fall back to handle event
		Table::addGlobalListener(array('on' => 'MyListener', 'event' => 'preSave', 'callback' => 'notPresent'));
		
		$this->asserts = array(
		array('event' => 'preSave', 'method' => 'handleEvent'),
		);
		
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		Table::removeGlobalListener(array('on' => 'MyListener', 'event' => 'preSave', 'callback' => 'notPresent'));
		
		
		// fall back to onEvent
		Table::addGlobalListener(array('on' => 'MyListener', 'event' => 'postSave', 'callback' => 'notPresent'));
		
		$this->asserts = array(
		array('event' => 'postSave', 'method' => 'onPostSave'),
		);
		
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		Table::removeGlobalListener(array('on' => 'MyListener', 'event' => 'postSave', 'callback' => 'notPresent'));
		
		
		// no fallback
		Table::addGlobalListener(array('on' => 'MyListener', 'event' => 'postSave', 'callback' => 'listener'));
		
		$this->asserts = array(
		array('event' => 'postSave', 'method' => 'listener'),
		);
		
		$t = new Table();
		$t->setName('test1');
		$t->save();
		
		Table::removeGlobalListener(array('on' => 'MyListener', 'event' => 'postSave', 'callback' => 'listener'));
	}
}
?>