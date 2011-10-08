<?php
$cb = null;

function listener($e) 
{
	global $cb;
	$cb->receiveCallback($e);
}

class TestListener
{
	protected function callback($e, $method)
	{
		global $cb;
		$e['method'] = $method;
		$cb->receiveCallback($e);
	}
}

class MyBlankListener extends TestListener
{
	public function handleEvent($e)
	{
		$this->callback($e, 'handleEvent');
	}
}

class MyListener extends TestListener 
{
	public function handleEvent($e) 
	{
		$this->callback($e, 'handleEvent');
	}
	
	public function onPostSave($e) 
	{
		$this->callback($e, 'onPostSave');
	}
	
	public function listener($e) 
	{
		$this->callback($e, 'listener');
	}
}

/* Workaround to test RecordListeners */
class OtherTableListenerPeer extends OtherTablePeer
{
	const CLASS_DEFAULT = 'listener-behavior.OtherTableListener';
	
	/** the related Propel class for this table */
	const OM_CLASS = 'OtherTableListener';
	
	public static function getOMClass($withPrefix = true)
	{
		return $withPrefix ? OtherTableListenerPeer::CLASS_DEFAULT : OtherTableListenerPeer::OM_CLASS;
	}
	
	public static function populateObjects(PDOStatement $stmt)
	{
		$results = array();
	
		// set the class once to avoid overhead in the loop
		$cls = OtherTableListenerPeer::getOMClass(false);
		// populate the object(s)
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key = OtherTablePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj = OtherTablePeer::getInstanceFromPool($key))) {
				// We no longer rehydrate the object, since this can cause data loss.
				// See http://www.propelorm.org/ticket/509
				// $obj->hydrate($row, 0, true); // rehydrate
				$results[] = $obj;
			} else {
				$obj = new $cls();
				$obj->hydrate($row);
				$results[] = $obj;
				OtherTablePeer::addInstanceToPool($obj, $key);
			} // if key exists
		}
		$stmt->closeCursor();
		return $results;
	}
	
	public static function populateObject($row, $startcol = 0)
	{
		$key = OtherTablePeer::getPrimaryKeyHashFromRow($row, $startcol);
		if (null !== ($obj = OtherTablePeer::getInstanceFromPool($key))) {
			// We no longer rehydrate the object, since this can cause data loss.
			// See http://www.propelorm.org/ticket/509
			// $obj->hydrate($row, $startcol, true); // rehydrate
			$col = $startcol + OtherTablePeer::NUM_HYDRATE_COLUMNS;
		} else {
			$cls = OtherTablePeerListener::OM_CLASS;
			$obj = new $cls();
			$col = $obj->hydrate($row, $startcol);
			OtherTablePeer::addInstanceToPool($obj, $key);
		}
		return array($obj, $col);
	}
}

class OtherTableListenerQuery extends OtherTableQuery
{
	public function __construct($dbName = null, $modelName = null, $modelAlias = null)
	{
		parent::__construct();
		$this->modelName = 'OtherTableListener';
		$this->modelPeerName = constant($this->modelName . '::PEER');
	}
	
	public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof OtherTableQuery) {
            return $criteria;
        }
        $query = new OtherTableListenerQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }
        return $query;
    }
}

/* Workaround end */

class OtherTableListener extends OtherTable
{
	const PEER = 'OtherTableListenerPeer';
	
	protected function callback($e, $method)
	{
		global $cb;
		$e['method'] = $method;
		$cb->receiveCallback($e);
	}
	
	public function handleEvent($e)
	{
		$this->callback($e, 'handleEvent');
	}
	
	public function onPostSave($e)
	{
		$this->callback($e, 'onPostSave');
	}
	
	public function listener($e)
	{
		$this->callback($e, 'listener');
	}
}
?>
