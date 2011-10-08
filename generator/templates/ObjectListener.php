/**
* Stores local listeners
* @type array
*/
private $listeners = null;

/**
* Stores local listeners queue
* @type array
*/
private $listenerQueue = null;

/**
* Saves a listener in the database
*
* @param mixed $listener listener-config
* @param int $refId reference id for binding a local listener to a tupel
*/
private static function saveListener($listener, $refId = null)
{
	$listener = <?php echo $listenerName; ?>::getListenerConfig($listener, get_called_class(), $refId);
	
	// save listener
	$l = <?php echo $listenerName; ?>Query::create()->findOneById($listener['id']);
	if (is_null($l)) {
		$l = new <?php echo $listenerName; ?>();
		$l->fromArray($listener, BasePeer::TYPE_FIELDNAME);
		if (array_key_exists('params', $listener)) {
			$l->setParams($listener['params']);
		}
		$l->save();
	}

	return $l;
}

/**
* Adds a global listener to the collection
*
* @param mixed $listener listener-config
*/
public static function addGlobalListener($listener)
{
	<?php echo $listenerName; ?>::addListenerToRuntime(self::saveListener($listener));
}

/**
* Adds a local listener to the collection
*
* @param mixed $listener listener-config
*/
public function addListener($listener)
{
	if ($this->_deleted) {
		return false;
	}
	
	if (is_array($this->getPrimaryKey())) {
		throw new PropelException('Cannot add local listener on an object with a composite primary key');
	}
	
	// pre fill $this->listeners. So new listeners won't be saved here once the object isn't saved itself
	if (is_null($this->listeners)) {
		$this->getListeners();
	}

	// enqueue listener, if this is new
	if ($this->_new) {
		if (is_null($this->listenerQueue)) {
			$this->listenerQueue = array();
		}
		$this->listenerQueue[] = $listener;
	}
	
	// anyway save it
	else {
		$this->listeners[] = self::saveListener($listener, $this->getPrimaryKey());
	}
}

/**
* Removes a global listener from the collection
*
* @param mixed $listener listener-config
*/
public static function removeGlobalListener($listener)
{
	$listener = <?php echo $listenerName; ?>::getListenerConfig($listener, get_called_class());
	
	$l = <?php echo $listenerName; ?>Query::create()->findOneById($listener['id']);
	
	if ($l) {
		<?php echo $listenerName; ?>::removeListenerFromRuntime($l);
		$l->delete();
	}
}

/**
* Removes a local listener from the collection
*
* @param mixed $listener listener-config
*/
public function removeListener($listener) {
	$listener = <?php echo $listenerName; ?>::getListenerConfig($listener, get_class($this), $this->getPrimaryKey());
	
	$l = <?php echo $listenerName; ?>Query::create()->findOneById($listener['id']);
	
	if ($l) {
		$key = array_search($l, $this->getListeners());
		if ($key) {
			unset($this->listeners[$key]);
		}
		$l->delete();
	}
}

/**
* Notifies listeners about a certain occured event
*
* @param String $event the event at which the listener fires
*/
private function notifyListener($event) {
	$target = '<?php echo $className; ?>';
	$allListeners = <?php echo $listenerName; ?>::getGlobalListeners($target);
	$listeners = array();

	// grab static listeners that will receive notification based on the occured event	
	if (array_key_exists($event, $allListeners)) {
		$listeners = $allListeners[$event];
	}

	// grab static listeners that will receive notifications on all events
	if (isset($allListeners[<?php echo $listenerName; ?>::ALL])) {
		$listeners = array_merge($listeners, $allListeners[<?php echo $listenerName; ?>::ALL]);
	}
	
	// grab local listeners
	$listeners = array_merge($listeners, $this->getListeners());
	
	if (count($listeners)) {
		$e = array(
			'event' => $event,
			'target' => $this,
		);
		
		foreach ($listeners as $l) {
			$e['params'] = $l->getParams() ? $l->getParams() : null; 

			if ($l->getOn()) {
				$cls = $l->getOn();
				$c = new $cls();
				
				$method = $l->getCallback();
				if (!method_exists($c, $method)) {
					$method = sprintf('on%s%s', 
						strtoupper(substr($e['event'], 0, 1)),
						substr($e['event'], 1));
					
					if (!method_exists($c, $method)) {
						$method = 'handleEvent';
					}
				}
				
				if (method_exists($c, $method)) {
					$c->$method($e);
				}
			} else {
				$cb = $l->getCallback();
				$cb($e);
			}
		}
	}
}

/**
* Returns the local listeners
*
* @return array
*/
private function getListeners() {
	if (is_null($this->listeners)) {
		$listeners = <?php echo $listenerName; ?>Query::create()
			->filterByTarget('<?php echo $listenerName; ?>')
			->where('ref_id IS NOT NULL')
			->find();
			
		$this->listeners = is_array($listeners) ? $listeners : array();
	}

	return $this->listeners;
}

private function saveEnqueuedListeners() {
	if (is_array($this->listenerQueue)) {
		$this->getListeners();
		foreach ($this->listenerQueue as $l) {
			$this->listeners[] = self::saveListener($l, $this->getPrimaryKey());
		}
		$this->listenerQueue = null;
	}
}

