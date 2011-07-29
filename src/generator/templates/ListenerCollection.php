private static $globalListeners;
const ALL = 'all';

/**
* Recursively implodes an array
*/
private static function implode_recursive($glue, $pieces)
{
	$retVal = array();
	foreach ($pieces as $piece)
	{
		if (is_array($piece)) {
			$retVal[] = self::implode_recursive($glue, $piece);
		} else {
			$retVal[] = $piece;
		}
	}
	return implode($glue, $retVal);
}

/**
* Returns the cleaned up listener info
*
* @private
* @param mixed $listener information about the listener
* @param String $event the event at which the listener fires
*/
public static function getListenerInfo($listener, $target, $refId = null)
{
	$default = array(
		'on' => null,
		'callback' => null,
		'params' => array(),
		'event' => self::ALL,
		'target' => $target,
		'ref_id' => $refId,
	);
	
	if ($listener instanceof ListenerInfo) {
		$listener = $listener->getListenerInfo();
	} else if (is_string($listener) && function_exists($listener)) {
		$listener = array('callback' => $listener);
	} else if (is_string($listener) && class_exists($listener)) {
		$listener = array('on' => $listener);
	}
	
	$listener = array_merge($default, $listener);
	$listener['id'] = md5(self::implode_recursive(',', $listener));
	
	return $listener;
}

/**
* Returns all listeners stored in the database
*/
public static function getGlobalListeners($target = null)
{
	if (is_null(self::$globalListeners)) {
		self::$globalListeners = array();
	}
	
	if (is_null(self::$globalListeners)) {
		$listeners = <?php echo $listenerName; ?>Query::create()->where('ref_id IS NULL')->find();
		
		foreach ($listeners as $l) {
			self::addListenerToRuntime($l);
		}
	}
	
	if (!is_null($target)) {
		if (!array_key_exists($target, self::$globalListeners)) {
			self::$globalListeners[$target] = array();
		}
		return self::$globalListeners[$target];
	}
	
	return self::$globalListeners;
}

/**
* Adds a listener to the static listener runtime collection
*
* @private
* @param <?php echo $listenerName; ?> $l
*/
public static function addListenerToRuntime(<?php echo $listenerName; ?> $l) {
	if (is_null(self::$globalListeners)) {
		self::$globalListeners = array();
	}

	if (!array_key_exists($l->getTarget(), self::$globalListeners)) {
		self::$globalListeners[$l->getTarget()] = array();
	}
	
	if (!array_key_exists($l->getEvent(), self::$globalListeners[$l->getTarget()])) {
		self::$globalListeners[$l->getTarget()][$l->getEvent()] = array();
	}

	if (!in_array($l, self::$globalListeners[$l->getTarget()][$l->getEvent()])) { 	
		self::$globalListeners[$l->getTarget()][$l->getEvent()][] = $l;
	}
}

/**
* Removes a listener from the static listener collection
*
* @private
* @param <?php echo $listenerName; ?> $l
*/
public static function removeListenerFromRuntime(<?php echo $listenerName; ?> $l) {
	try {
		self::getGlobalListeners();
		$key = array_search($l, self::$globalListeners[$l->getTarget()][$l->getEvent()]);
		if ($key) {
			unset(self::$globalListeners[$l->getTarget()][$l->getEvent()][$key]);
		}
	} catch (Exception $e) {}
}

/**
 * The value for the params field.
 * @var        array
 */
private $params = null;

/**
 * Get the [params] column value.
 * 
 * @return     array
 */
public function getParams() {
	if (is_null($this->params)) {
		$this->params = json_decode($this->json_params, true);
	}
	return $this->params;
}

/**
 * Set the value of [params] column.
 * 
 * @param      array $v new value
 * @return     <?php echo $listenerName; ?> The current object (for fluent API support)
 */
public function setParams($v)
{
	$this->params = $v;
	$this->setJsonParams(json_encode($v));

	return $this;
}

/**
* Adds a listener to the static collection, to keep the collection in sync during runtime.
*/
public function postInsert(PropelPDO $con = null) {
	if ($this->target == '') {
		self::addListenerToRuntime($this);
	}
}

/**
* Removes a listener from the static collection, to keep the collection in sync during runtime.
*/
public function preDelete(PropelPDO $con = null) {
	if ($this->target == '') {
		self::removeListenerFromRuntime($this);
	}
	return true;
}