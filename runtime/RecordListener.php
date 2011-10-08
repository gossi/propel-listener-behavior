<?php

require_once 'ListenerConfigInterface.php';

class RecordListener implements ListenerConfigInterface 
{
	private $cfg;
	
	public function __construct($cfg = array()) 
	{
		$this->cfg = array(
			'on' => 'RecordListener'
		);
		
		if (array_key_exists('event', $cfg)) {
			$this->cfg['event'] = $cfg['event'];
			unset($cfg['event']);
		}
		
		$this->cfg['params'] = $cfg;
	}

	public function getListenerConfig() 
	{
		return $this->cfg;
	}
	
	public function handleEvent($e) 
	{
		$target = array_key_exists('target', $e['params']) ? $e['params']['target'] : null;
		$find = array_key_exists('find', $e['params']) ? $e['params']['find'] : null;
		$param = array_key_exists('param', $e['params']) ? $e['params']['param'] : null;
		$method = '';
		
		if (array_key_exists('method', $e['params'])) {
			$method = $e['params']['method'];
		}
		
		if ($target && $find) {
			$class = $target.'Query';
			$o = $class::create()->$find($param);
			
			// some testing for tests
// 			$o = $class::create();
// 			echo $o->getModelName();
// 			$o = $o->$find($param);

			if (!method_exists($o, $method)) {
				$event = $e['event'];
				$event[0] = strtoupper($event);
				$method = 'on'.$event;
			}

			if (method_exists($o, $method)) {
				$o->$method($e);
			} else if (method_exists($o, 'handleEvent')) {
				$o->handleEvent($e);
			}
		}
	}
}
?>
