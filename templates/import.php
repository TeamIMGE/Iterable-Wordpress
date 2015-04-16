<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/underscore.js', __FILE__ ) ?>"></script>
<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/papaparse.min.js', __FILE__ ) ?>"></script>
<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/import.js', __FILE__ ) ?>"></script>
<script type='text/template' class='error_template'>
<div class="error import-response-message" style="margin-top: 30px;" id="import_errors" role="alert">
    <p><%= message %>:</p>
    <ul>
    <% _.each( errors, function( error ) { %>
        <li><%= JSON.stringify( error ) %>
    <% } ); %>
    </ul>
</div>
</script>
<script type='text/template' class='csv_field_template'>
<% _.each( csv_fields, function( csv_field ) { %>
    <tr class='gravityform_field' data-id='<%= csv_field %>'>
        <td><%= csv_field %></td>
        <td>
            <select class='iterable_field'>
                <option></option>
                <option value="<%= csv_field %>">ADD THIS FIELD</option>
                <% _.each( iterable_fields, function( i_field ) { %>
                    <option value="<%= i_field %>"><%= i_field %></option>
                <% } ); %>
            </select>
        </td>
        <td><input class="override_field" type="checkbox" /></td>
    </tr>
<% } ); %>
</script>

<h2 style="margin-top: 20px;">Import a List</h2>
<script type='text/javascript'>
<?php $user_fields = $iterable->user_fields(); ?>
<?php if( $user_fields[ 'success' ] ): ?>
iterable_fields = <?= json_encode( $user_fields[ 'content' ] ) ?>;
<?php endif; ?>
</script>
<form method='post'>
	<input type='hidden' name='feed_id' id='feed_id' value='<?= ( isset( $_REQUEST[ 'id' ] ) ) ? $_REQUEST[ 'id' ] : 0 ?>' />
	<table class='form-table'>
        <tr valign='top'>
            <th scope='row'><label for='csv' class='left_header'>CSV File</label></th>
            <td><input type='file' id='csv' label='csv' accept=".csv" /></td>
        </tr>
		<tr valign='top'>
			<th scope='row'><label for='list' class='left_header'>Iterable List</label></th>
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
			<th scope='row'><label>Attribution</label></th>
            <td>
                <table>
                    <tr>
			            <td><input type='text' name='source' id='source' placeholder='Source' /></td>
			            <td><input type='text' name='campaign' id='campaign' placeholder='Campaign' /></td>
			            <td><input type='text' name='medium' id='medium' placeholder='Medium' /></td>
                    </tr>
                </table>
            </td>
        </tr>
		<tr valign='top'>
			<th scope='row'><label for='resubscribe'>Resubscribe</label></th>
			<td><input type='checkbox' name='resubscribe' id='resubscribe' /></td>
		</tr>
	</table>
	<div>
		<input type='submit' value='Import' id='import_list' class='button-primary' />
	</div>
</form>
