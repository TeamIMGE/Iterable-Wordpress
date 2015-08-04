<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/list.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>

<a class="button add-new-h2" href="<?= admin_url( 'admin.php?page=iterable_feed_edit' ) ?>">Add New</a>
<table class="widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" class="manage-column">Form</th>
			<th scope="col" class="manage-column">List</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( IterableData::get_feeds() as $feed ): ?>
		<tr data-id='<?= $feed[ 'id' ] ?>'>
			<td>
				<a title='edit' href='<?= admin_url( 'admin.php?page=iterable_feed_edit&id=' . $feed[ 'id' ] ) ?>'><?= $feed[ 'form_title' ] ?></a>
				<div class="row-actions">
					<span class="edit">
					<a href="<?= admin_url( 'admin.php?page=iterable_feed_edit&id=' . $feed[ 'id' ] ) ?>" title="Edit">Edit</a>
					|
					</span>
					<span class="trash">
					<a title="Delete" href="#" class='delete_feed' data-id='<?= $feed[ 'id' ] ?>'>Delete</a>
					</span>
				</div>
			</td>
			<td><?= $feed[ 'meta' ][ 'iterablelist_name' ]; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
