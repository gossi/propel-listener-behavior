<?php
require_once 'helpers/listenerTests.php';
require_once '../../runtime/RecordListener.php';

class RecordListenerTest extends ListenerTests
{
	// with listeners added during runtime
	public function testHandleEvent()
	{
		$t = new Table();
		$t->setName('Unchanged Record');
		$t->save();
		
		$o = new OtherTable();
		$o->setOther($t->getId());
		$o->save();
		
		$t->addListener(new RecordListener(array(
			'event' => 'postUpdate',
			'target' => 'OtherTableListener',
			'find' => 'findOneByOther',
			'param' => $t->getId()
		)));

		// this won't assert right now
		$this->asserts = array(
			array('event' => 'postUpdate', 'method' => 'handleEvent')
		);
		
		$t->setName('Changed Record');
		$t->save();
		
		$t->removeListener(new RecordListener(array(
			'event' => 'postUpdate',
			'target' => 'OtherTableListener',
			'find' => 'findOneByOther',
			'param' => $t->getId()
		)));
	}
}
?>