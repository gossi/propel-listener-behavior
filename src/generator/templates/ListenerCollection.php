private static $listeners;
const ALL = 'all';

/**
* Returns all listeners stored in the database
*/
public static function getListeners()
{
	if (is_null(self::$listeners)) {
		self::$listeners = array();
		$listeners = ListenerQuery::create()->find();
		
		foreach ($listeners as $l) {
			self::addListener($l);
		}
	}
	
	return self::$listeners;
}

/**
* Adds a listener to the static listener collection
*
* @param Listener $l
*/
private static function addListener(Listener $l) {
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
* Removes a listener from the static listener collection
*
* @param Listener $l
*/
private static function removeListener(Listener $l) {
	try {
		self::getListeners();
		$key = array_search($l, self::$listeners[$l->getTarget()][$l->getEvent()]);
		if ($key) {
			unset(self::$listeners[$l->getTarget()][$l->getEvent()][$key]);
		}
	} catch (Exception $e) {}
}

/**
* Adds a listener to the static collection, to keep the collection in sync during runtime.
*/
public function postInsert(PropelPDO $con = null) {
	self::addListener($this);
}

/**
* Removes a listener from the static collection, to keep the collection in sync during runtime.
*/
public function preDelete(PropelPDO $con = null) {
	self::removeListener($this);
	return true;
}

