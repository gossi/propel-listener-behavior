<?php 
include_once 'bootstrap.php';

head();
?>
	<h2>Installed Listeners</h2>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Target</th>
				<th>Event</th>
				<th>On (class)</th>
				<th>Callback</th>
				<th>Params</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach (Listener::getListeners() as $t) {
				foreach ($t as $e) {
					foreach ($e as $l) {
						printf('<tr>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td><pre>%s</pre></td>
						</tr>',
						$l->getId(), $l->getTarget(), $l->getEvent(),
						$l->getOn(), $l->getCallback(), print_r($l->getParams(), true));
					}
				}
			}
			?>
		</tbody>
	</table>
<?php 
foot();
?>