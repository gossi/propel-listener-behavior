<?php

class ListenerCollectionBehavior extends Behavior 
{
	public function objectMethods() 
	{
		global $className;

		$script = $this->renderTemplate('ListenerCollection', array(
			'listenerName' => ListenerTable::$phpName
		));
		return $script;
	}
}
?>