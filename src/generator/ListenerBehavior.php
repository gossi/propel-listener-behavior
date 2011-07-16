<?php
class ListenerBehavior extends Behavior {
	protected $parameters = array(
		'table' => 'listener',
	);
	
	private function createListenerTable() {
		$db = $this->getDatabase() == null ? $this->getTable()->getDatabase() : $this->getDatabase();
		$table = $db->getTable($this->getParameter('table'));

		if ($table != null && !($table instanceof ListenerTable)) {
			throw new InvalidArgumentException(sprintf(
				'The defined table (%s) is already in use, please make use of the "table" parameter.', 
				$this->getParameter('table')
			));
		}

		if ($table != null) {
			return;
		}

		$table = new ListenerTable($this->getParameter('table'));
		$table->addBehavior(new ListenerCollectionBehavior());
		
		$db->addTable($table);
		
		$id = new Column('id');
		$id->setPrimaryKey(true);
		$id->setType(PropelTypes::VARCHAR);
		$id->getDomain()->setSqlType(PropelTypes::VARCHAR);
		$id->setSize(32);
		
		$on = new Column('on');
		$on->setType(PropelTypes::VARCHAR);
		$on->getDomain()->setSqlType(PropelTypes::VARCHAR);
		$on->setSize(100);
		
		$callback = new Column('callback');
		$callback->setType(PropelTypes::VARCHAR);
		$callback->getDomain()->setSqlType(PropelTypes::VARCHAR);
		$callback->setSize(100);

		$params = new Column('json_params');
		
		// arrays don't work properly, see ticket: http://www.propelorm.org/ticket/1482
		// use text and json_encode and json_decode as workaround
// 		$params->getDomain()->copy($db->getPlatform()->getDomainForType(PropelTypes::PHP_ARRAY));
		$params->getDomain()->copy($db->getPlatform()->getDomainForType(PropelTypes::LONGVARCHAR));

		$event = new Column('event');
		$event->setType(PropelTypes::VARCHAR);
		$event->getDomain()->setSqlType(PropelTypes::VARCHAR);
		$event->setSize(100);
		
		$target = new Column('target');
		$target->setType(PropelTypes::VARCHAR);
		$target->getDomain()->setSqlType(PropelTypes::VARCHAR);
		$target->setSize(100);
		
		$table->addColumn($id);
		$table->addColumn($on);
		$table->addColumn($callback);
		$table->addColumn($params);
		$table->addColumn($event);
		$table->addColumn($target);
	}
	
	public function modifyDatabase() {
		$this->createListenerTable();

		// call parent to add this behavior to all tables in the database
		parent::modifyDatabase();
	}
	
	public function modifyTable() {
		$this->createListenerTable();
	}
	
	public function objectMethods() {
		$db = $this->getDatabase() == null ? $this->getTable()->getDatabase() : $this->getDatabase();
		$table = $db->getTable($this->getParameter('table'));
		
		$script = '';
		$script .= $this->renderTemplate('ListenerInfo');
		$script .= $this->renderTemplate('AddListener');
		$script .= $this->renderTemplate('RemoveListener');
		$script .= $this->renderTemplate('NotifyListener', array(
			'className' => $this->getTable()->getPhpName(),
		));
		return $script;
	}
	
	public function preDelete() {
		return '$this->notifyListener(\'preDelete\');';
	}
	
	public function postDelete() {
		return '$this->notifyListener(\'postDelete\');';
	}
	
	public function preInsert() {
		return '$this->notifyListener(\'preInsert\');';
	}
	
	public function postInsert() {
		return '$this->notifyListener(\'postInsert\');';
	}
	
	public function preSave() {
		return '$this->notifyListener(\'preSave\');';
	}
	
	public function postSave() {
		return '$this->notifyListener(\'postSave\');';
	}
	
	public function preUpdate() {
		return '$this->notifyListener(\'preUpdate\');';
	}
	
	public function postUpdate() {
		return '$this->notifyListener(\'postUpdate\');';
	}
}

class ListenerCollectionBehavior extends Behavior {
	public function objectMethods() {
		$script = $this->renderTemplate('ListenerCollection');
		return $script;
	}
}

class ListenerTable extends Table {}
?>
