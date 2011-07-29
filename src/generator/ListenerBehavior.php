<?php
$className = null;

class ListenerBehavior extends Behavior {
	protected $parameters = array(
		'table' => 'listener',
		'phpName' => null,
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
		
		
		if (!is_null($this->getParameter('phpName'))) {
			$table->setPhpName($this->getParameter('phpName'));
		}
		ListenerTable::$phpName = $table->getPhpName();
		
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
		
		$ref = new Column('ref_id');
		$ref->setType(PropelTypes::VARCHAR);
		$ref->getDomain()->setSqlType(PropelTypes::VARCHAR);
		$ref->setSize(100);
		
		$table->addColumn($id);
		$table->addColumn($on);
		$table->addColumn($callback);
		$table->addColumn($params);
		$table->addColumn($event);
		$table->addColumn($target);
		$table->addColumn($ref);
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
		global $className;
		$db = $this->getDatabase() == null ? $this->getTable()->getDatabase() : $this->getDatabase();
		$table = $db->getTable($this->getParameter('table'));

		$script = $this->renderTemplate('ObjectListener', array(
			'className' => $this->getTable()->getPhpName(),
			'listenerName' => ListenerTable::$phpName,
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
		return '$this->notifyListener(\'postInsert\');
$this->saveEnqueuedListeners();';
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
		global $className;

		$script = $this->renderTemplate('ListenerCollection', array(
			'listenerName' => ListenerTable::$phpName
		));
		return $script;
	}
}

class ListenerTable extends Table {
	public static $phpName;
}
?>
