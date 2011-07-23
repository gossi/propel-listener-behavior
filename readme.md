# Propel-Listener-Behavior

The Propel Listener Behavior let's you add listeners to propel generated objects. Thus it is
possible that these listeners are notified when a specific event on a propel generated object 
occurs. The listeners design is inspired by the observer pattern and the W3C EventTarget 
Interface.

E.g. if your model consists of an Event object an your app is connected to a google calender
you wish those two events stay in sync. So you register a listener at your Event object and
whenever something gets changed there, the listener gets notified and can change the event
at the google calender accordingly.

## Installation

### Generator
At first you need the [src/generator](propel-listener-behavior/tree/master/src/generator) folder from github.  In your `build.properties` you need
to add `propel.behavior.listener.class` and point it to the `src/generator/ListenerBehavior`
file, using the dot-notation.

### Runtime
The Propel Listener Behavior comes with it's own runtime to make it work correctly and with
all its features.
Include the [src/runtime/ListenerBehavior.php](propel-listener-behavior/tree/master/src/runtime/ListenerBehavior.php) file in your
source code.

## Usage

### Schema
The behavior can be added to either the database or a specific table.

Database:

	<database name="...">
		<behavior name="listener"/>
		<table>
			...
		</table>
	</database>
	
Table: 

	<database name="...">
		<table>
			<column .../>
			<behavior name="listener"/>
			...
		</table>
	</database>
	
However, this will create a new table (and thus a new object) in your model to store your 
listeners. By default this table is `listener`. You can change this by passing the `table`
parameter to the behavior, to prevent interfering usage with your model, like this:

	<behavior name="listener">
		<param name="table" value="my_listener_table"/>
	</behavior>
	
*Note: Obviously this behavior can be added to tables, there is not much sense in it. This 
behavior is best placed as a database behavior.*
	
### Listener Events

You can add listeners very differently, according to your needs, offering more and more 
features the more advanced it gets. Listeners can be attached and listen to differend events,
these are:

* preInsert
* postInsert
* preUpdate
* postUpdate
* preSave
* postSave
* preDelete
* postDelete

### Global and Local Listeners

Listeners can be added in two forms. As **global**, then they were added (statically) on a
record like `Table::addGlobalListener(...)`. Global listeners react on each object 
instantiated from the attached class.

**Local** listeners instead are attached to specific objects. E.g.

	$t = new Table();
	$t->addListener(...);
	
These listeners will only react, when an event on this specific object occurs.

### Attaching Listeners

For the demos below, let's assume our object where we can attach
listeners is named `Table`. In the demos, local listeners are used but global listeners
working exactly the same using the static `addGlobalListener` and `removeGlobalListener`
methods.

#### Simplest Method: Simple Function

	function myListenerFunction($e) {
		// ... do something here
	}

	$t = new Table();
	$t->addListener('myListenerFunction');

The simplest one is a function that is passed as a string. This function will react on each 
of the above mentioned events.

#### Function that reacts on a specific Event

	function myListenerFunction($e) {
		// ... do something here
	}

	$t = new Table();
	$t->addListener(array(
		'callback' => 'myListenerFunction',
		'event' => 'postSave'
	));

Now we extended the functionality by adding the event, at which the listener should be 
notified.

#### Passing Parameters through Listeners

	function myListenerFunction($e) {
		// ... do something here
		echo $e['params']['my_super_important_id'];
	}

	$t = new Table();
	$t->addListener(array(
		'callback' => 'myListenerFunction',
		'event' => 'postSave',
		'params' => array(
			'my_super_important_id' => 42
		)
	));
	
This way you can pass information to your listener, using the params attribute of your 
configuration array. The params will later be accessible from your listener.

#### Using Classes as Listeners

In an object oriented application functions are seen very rare. Thus it is possible to get
classes notified about occuring events (This example is the same as the simplest method above
but for classes).

	class MyListener {
		public function handleEvent($e) {
			// ... do something here
		}
	}
	
	$t = new Table();
	$t->addListener('MyListener');

*Note: Internally when the event occurs a new object of MyListener is instantiated, as:*
	
	$obj = new MyListener();
	$obj->handleEvent($e);

*Note 2: If no callback is given or callback is no method on the class, `handleEvent` is 
called (see next section, too)*
	
#### Using Classes with more Sugar

If you have a proper method on your class, you can use that for sure.

	class MyListener {
		public function tableGotUpdated($e) {
			// ... do something here
		}
	}
	
	$t = new Table();
	$t->addListener(array(
		'on' => 'MyListener',
		'callback' => 'tableGotUpdated',
		'event' => 'postUpdate'
	));

#### Using the ListenerInfo Interface

If you already have an internal dispatcher for your app and you want to use that, the
`ListenerInfo` interface is the right one for you.

	class MyListenerInfo implements ListenerInfo {
		// ... other stuff here
		
		public function getListenerInfo() {
			return array(
				'on' => 'MyCoolClassThatReactsOnListenerEvents',
				'callback' => 'sooperDooperHaandler'
			);		
		}
		
		// ... another stuff there
	}
	
	$t = new Table();
	$t->addListener(new MyListenerInfo());
	
#### Using the bundled RecordListener

The bundled RecordListener ships with the propel listener behavior and is part of its runtime.
With the RecordListener you can attach Listeners on an object that passes the reaction to
other propel objects. Sounds complicated? Example code:

	$t = new Table();
	$t->setName('Unchanged Record');
	$t->save();
	
	$o = new OtherTable();
	$o->setOther($t->getId());
	$o->save();
	
	$t->addListener(new RecordListener(array(
		'event' => 'postUpdate',
		'target' => 'OtherTable',
		'find' => 'findOneByOther',
		'param' => $t->getId()
	)));
	
Explanation: We have two propel objects $t from `Table` and $o from `OtherTable`. A listener
is added on `Table` that reacts on the *postUpdate* event. The target, find and param 
parameters that are passed to the RecordListener are placed that way:

	function postUpdateEventOnTableHappened($e) {
		$target = {target}Query::create();
		$result = $target->{find}({param});
	}
	
and results in that code:

	function postUpdateEventOnTableHappened($e) {
		$target = OtherTableQuery::create();
		$result = $target->findByOther($t->getId());
	}

this will get evaluated. Thus `$result` contains our `OtherTable` object `$o` from above and
`handleEvent` will be invoked.

#### Removing listeners

Removing listeners is been done by either the static `removeGlobalListener` or the 
`removeListener` method. It is absolutely necessary to pass the same arguments for the removal
as you passed for the adding. This is how listeners get identified internally.

## References

Full references.

### addListener/getListenerInfo Parameters

`addListener` takes an array as argument, where `getListenerInfo` should return an array.
Both use the same keys. They are:
	
#### (string) callback

Function or method name.

#### (string) on

A class name.

#### (string) event

The event on which the listener should occur. See the list from "Listener Events" above.

#### (array) params

An array for user-defined params.

### Event

When an event occurs, the callback function/method is invoked and array is passed with
more information about the event.

#### (string) event

The event which occured.

#### (object) target

Propel-object on which the event occured.

### RecordListener

The `RecordListener` takes the parameters as an array in the constructor. They are:

#### (string) target

Which propel object should the event passed on.

#### (string) find

The find method, that will be invoked on the target-query object.

#### (string) param

The search param for the find method.

#### (string) method

The method that will be invoked on the target object. To find the right method in the target
object, the listener-behavior tries the following steps:

1. method is passed as param
2. on`Event` means if `postUpdate` is invoked, the method `onPostUpdate` will be tried to 
call.
3. If none of the above methods is found in the target object, `handleEvent` is called.

  