/**
* Stores dynamic listeners
* @type array
*/
private $listeners = null;

/**
* Stores dynamic listeners queue
* @type array
*/
private $listenerQueue = null;

/**
* Saves a listener in the database
*
* @param mixed $listener information about the listener
*/
private static function saveListener($listener, $refId = null)
{
	$listener = <?php echo $listenerName; ?>::getListenerInfo($listener, get_called_class(), $refId);

	// prepare for saving
	$todb = array();
	foreach (array_keys($listener) as $key) {
		$todb[ucfirst($key)] = $listener[$key];
	}

	// save listener
	$l = <?php echo $listenerName; ?>Query::create()->findOneById($listener['id']);
	if (is_null($l)) {
		$l = new <?php echo $listenerName; ?>();
		$l->fromArray($todb);
		if (array_key_exists('Params', $todb)) {
			$l->setParams($todb['Params']);
		}
		$l->save();
	}

	return $l;
}

/**
* Adds a global listener to the collection
*
* @param mixed $listener information about the listener
*/
public static function addGlobalListener($listener)
{
	<?php echo $listenerName; ?>::addListenerToRuntime(self::saveListener($listener));
}

/**
* Adds a local listener to the collection
*
* @param mixed $listener information about the listener
*/
public function addListener($listener)
{
	if ($this->_deleted) {
		return false;
	}
	
	if (is_array($this->getPrimaryKey())) {
		throw new PropelException('Cannot add dynamic listener on an object with a composite primary key');
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
		$listeners = $this->getListeners();
		$this->listeners[] = self::saveListener($listener, $this->getPrimaryKey());
	}
}

/**
* Removes a global listener from the collection
*
* @param mixed $listener information about the listener
*/
public static function removeGlobalListener($listener)
{
	$listener = $this->getListenerInfo($listener, get_called_class());
	
	$l = <?php echo $listenerName; ?>Query::create()->findOneById($listener['id']);
	
	if ($l) {
		<?php echo $listenerName; ?>::removeListenerFromRuntime($l);
		$l->delete();
	}
}

/**
* Removes a local listener from the collection
*
* @param mixed $listener information about the listener
*/
public function removeListener($listener) {
	$listener = $this->getListenerInfo($listener, get_class($this));
	
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
	
	// grab dynamic listeners
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
				$method = $l->getCallback();
				if ($method == '') {
					$method = 'handleEvent';
				}
				$c = new $cls();
				$c->$method($e);
			} else {
				$cb = $l->getCallback();
				$cb($e);
			}
		}
	}
}

/**
* Returns the dynamic listeners
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
			$this->listeners = array_merge($this->listeners, self::saveListener($l, $this->getPrimaryKey()));
		}
	}
}

