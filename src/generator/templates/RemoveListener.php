/**
* Revmoes a listener from the collection
*
* @param mixed $listener information about the listener
* @param String $event the event at which the listener fires
*/
public function removeListener($listener)
{
	$listener = $this->getListenerInfo($listener);
	
	$l = ListenerQuery::create()->findOneById($listener['id']);
	
	if ($l) {
		$l->delete();
	}
}

