<?php
interface ListenerInfo {
	public function getListenerInfo();
}

class RecordListener implements ListenerInfo {
	
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

	public function getListenerInfo() {
		return $this->cfg;
	}
	
	public function handleEvent($e) {
		print_r($e);
		$target = array_key_exists('target', $e['params']) ? $e['params']['target'] : null;
		$find = array_key_exists('find', $e['params']) ? $e['params']['find'] : null;
		$param = array_key_exists('param', $e['params']) ? $e['params']['param'] : null;
		
		if (array_key_exists('method', $e['params'])) {
			$method = $e['params']['method'];
		} else {
			$event = $e['event'];
			$event[0] = strtoupper($event);
			$method = 'on'.$event;
		}
		
		if ($target && $find) {
			$class = $target.'Query';
			$o = $class::create()->$find($param);
			
			if (method_exists($o, $method)) {
				$o->$method($e);
			} else if (method_exists($o, 'handleEvent')) {
				$o->handleEvent($e);
			}
		}
	}
}
?>
