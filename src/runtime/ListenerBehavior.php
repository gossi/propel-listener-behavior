<?php
interface ListenerDescriptor {
	public function getDescription();
}

class RecordListener implements ListenerDescriptor {
	
	private $cfg;
	
	public function __construct($cfg = array()) {
		$this->cfg = array(
			'on' => 'RecordListener',
			'params' => $cfg,
		);
		
		if (array_key_exists('event', $cfg)) {
			$this->cfg['event'] = $cfg['event'];
			unset($cfg['event']);
		}
	}

	public function getDescription() {
		return $this->cfg;
	}
	
	public function handleEvent($e) {
		echo '<pre>';
		print_r($e);
		$target = array_key_exists('target', $e['params']) ? $e['params']['target'] : null;
		$find = array_key_exists('find', $e['params']) ? $e['params']['find'] : null;
		$param = array_key_exists('param', $e['params']) ? $e['params']['param'] : null;
		$event = $e['event'];
		$event[0] = strtoupper($event);
		$method = 'on'.$event;
		
		echo $target . ' - ' . $find;
		
		if ($target && $find) {
			$class = $target.'Query';
			$o = $class::create()->$find($param);
			
			if (method_exists($o, $method)) {
				$o->$method($e);
				break;
			}
			
			if (method_exists($o, 'handleEvent')) {
				$o->handleEvent($e);
			}
		}
	}
}
?>
