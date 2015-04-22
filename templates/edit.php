<?php
	$feed = false;
	if( isset( $_REQUEST[ 'id' ] ) && $_REQUEST[ 'id' ] > 0 ) {
		$feed = IterableData::get_feed( $_REQUEST[ 'id' ] );	
	}
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/underscore.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>
<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/edit.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>
<script type='text/template' class='gravityform_field_template'>
<% _.each( gravityform_fields, function( gf_field ) { %>
	<tr class='gravityform_field' data-id='<%= gf_field.id %>'>
		<td><%= gf_field.name %></td>
		<td>
			<select class='iterable_field'>
				<option></option>
				<% _.each( iterable_fields, function( i_field ) { %>
					<option value="<%= i_field %>"><%= i_field %></option>
				<% } ); %>
			</select>
		</td>
        <td><input class="override_field" type="checkbox" /></td>
	</tr>
<% } ); %>
</script>
<script type='text/javascript'>
<?php $user_fields = $iterable->user_fields(); ?>
<?php if( $user_fields[ 'success' ] ): ?>
iterable_fields = <?= json_encode( $user_fields[ 'content' ] ) ?>;
<?php endif; ?>
<?php if( $feed ): ?>
feed = <?= json_encode( $feed ) ?>;
<?php endif; ?>
</script>
<form method='post'>
	<input type='hidden' name='feed_id' id='feed_id' value='<?= ( isset( $_REQUEST[ 'id' ] ) ) ? $_REQUEST[ 'id' ] : 0 ?>' />
	<table class='form-table'>
		<tr valign='top'>
			<th scope='row'><label for='list' class='left_header'>Contact List</label></th>
			<td>
				<?php $iterable_lists = $iterable->lists(); ?>
				<?php if( $iterable_lists[ 'success' ] ): ?>
				<select name='iterablelist' id='iterablelist'>
					<option></option>
					<?php foreach( $iterable_lists[ 'content' ] as $l ): ?>
					<option value='<?= $l[ 'id' ] ?>'><?= $l[ 'name' ] ?></option>
					<?php endforeach; ?>
				</select>
				<?php else: ?>
				<p style='font-weight: bold; color: red'>Error accessing Iterable. Check API key.</p>	
				<?php endif; ?>
			</td>
		</tr>
		<tr valign='top'>
			<th scope='row'><label for='gravityform' class='left_header'>Gravity Form</label></th>
			<td>
				<select name='gravityform' id='gravityform'>
					<option></option>
					<?php foreach( RGFormsModel::get_forms() as $form ): ?>
					<option value="<?= $form->id ?>"><?= esc_html( $form->title ) ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr valign='top'>
			<th scope='row'><label for='mapfields'>Map Fields</label></th>
			<td>
				<table>
					<tr>
						<th style='padding-top: 0px;'>Form Fields</th>
						<th style='padding-top: 0px;'>List Fields</th>
						<th style='padding-top: 0px;'>Override Existing Value?</th>
					</tr>
					<tbody id='map'>
					</tbody>
	                        </table>
			</td>
		</tr>
		<tr valign='top'>
			<th scope='row'><label for='resubscribe'>Resubscribe</label></th>
			<td><input type='checkbox' name='resubscribe' id='resubscribe' /></td>
		</tr>
		<tr valign='top'>
			<th scope='row'><label for='listwise'>Validate with Listwise</label></th>
			<td><input type='checkbox' name='listwise' id='listwise' /></td>
		</tr>
		<tr valign='top'>
			<th scope='row'><label for='require_checked'>Only subscribe when field is checked</label></th>
			<td><select name='require_checked' id='require_checked'></select></td>
		</tr>
	</table>
	<div>
		<input type='submit' value='Save' id='save_feed' class='button-primary' />
		<a href='<?= admin_url( 'admin.php?page=iterable_feed' ) ?>' class='button'>Back</a>
	</div>
</form>
