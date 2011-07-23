private static $listeners;
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
		'event' => Listener::ALL,
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
public static function getListeners($target = null)
{
	if (is_null(self::$listeners)) {
		$listeners = ListenerQuery::create()->where('ref_id IS NULL')->find();
		
		foreach ($listeners as $l) {
			self::addListenerToRuntime($l);
		}
	}
	
	if (!is_null($target)) {
		if (!array_key_exists($target, self::$listeners)) {
			self::$listeners[$target] = array();
		}
		return self::$listeners[$target];
	}
	
	return self::$listeners;
}

/**
* Adds a listener to the static listener runtime collection
*
* @private
* @param Listener $l
*/
public static function addListenerToRuntime(Listener $l) {
	if (is_null(self::$listeners)) {
		self::$listeners = array();
	}

	if (!array_key_exists($l->getTarget(), self::$listeners)) {
		self::$listeners[$l->getTarget()] = array();
	}
	
	if (!array_key_exists($l->getEvent(), self::$listeners[$l->getTarget()])) {
		self::$listeners[$l->getTarget()][$l->getEvent()] = array();
	}

	if (!in_array($l, self::$listeners[$l->getTarget()][$l->getEvent()])) { 	
		self::$listeners[$l->getTarget()][$l->getEvent()][] = $l;
	}
}

/**
* Removes a listener from the static listener collection
*
* @private
* @param Listener $l
*/
public static function removeListenerFromRuntime(Listener $l) {
	try {
		self::getListeners();
		$key = array_search($l, self::$listeners[$l->getTarget()][$l->getEvent()]);
		if ($key) {
			unset(self::$listeners[$l->getTarget()][$l->getEvent()][$key]);
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
 * @return     Listener The current object (for fluent API support)
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
	self::addListenerToRuntime($this);
}

/**
* Removes a listener from the static collection, to keep the collection in sync during runtime.
*/
public function preDelete(PropelPDO $con = null) {
	self::removeListenerFromRuntime($this);
	return true;
}