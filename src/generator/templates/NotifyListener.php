/**
* Adds a listener to the collection
*
* @param String $event the event at which the listener fires
*/
private function notifyListener($event) {
	$allListeners = Listener::getListeners();
	$listeners = array();
	$target = '<?php echo $className; ?>';
	
	if (isset($allListeners[$target]) && isset($allListeners[$target][$event])) {
		$listeners = $allListeners[$target][$event];
	}
	
	if (isset($allListeners[$target][Listener::ALL])) {
		$listeners = array_merge($listeners, $allListeners[$target][Listener::ALL]);
	}
	
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

