/**
* recursively implodes an array
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
* @param mixed $listener information about the listener
* @param String $event the event at which the listener fires
*/
private function getListenerInfo($listener)
{
	$default = array(
		'on' => null,
		'callback' => null,
		'params' => array(),
		'event' => Listener::ALL,
		'target' => get_class($this),
	);
	
	if ($listener instanceof ListenerDescriptor) {
		$listener = $listener->getDescription();
	} else if (is_string($listener)) {
		$listener = array('callback' => $listener);
	}
	
	$listener = array_merge($default, $listener);
	$listener['id'] = md5(self::implode_recursive(',', $listener));
	
	return $listener;
}
