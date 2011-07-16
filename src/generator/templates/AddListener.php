/**
* Adds a listener to the collection
*
* @param mixed $listener information about the listener
* @param String $event the event at which the listener fires
*/
public function addListener($listener)
{
	$listener = $this->getListenerInfo($listener);
	
	$todb = array();
	foreach (array_keys($listener) as $key) {
		$todb[ucfirst($key)] = $listener[$key];
	}
	
	try {
		$l = new Listener();
		$l->fromArray($todb);
		if (array_key_exists('Params', $todb)) {
			$l->setParams($todb['Params']);
		}
		$l->save();
	} catch (Exception $e) {}
}

