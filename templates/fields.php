<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/underscore.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>
<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/fields.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>
<script type='text/template' id='field_template'>
<% _.each( fields, function( field, index, list ) { %>
<tr class='field'>
    <td><label for='field_<%= index %>'><%= field.name %><label></td>
    <td>
        <input id='field_<%= index %>' type='checkbox' value='<%= field.name %>' <% if( !field.hide ) { %>checked<% } %> class='field' />
    </td>
</tr>
<% } ); %>
</script>
<script type='text/javascript'>
    <?php $user_fields = $iterable->user_fields(); ?>
    <?php if( $user_fields[ 'success' ] ): ?>
        iterable_fields = <?= json_encode( $user_fields[ 'content' ] ) ?>;
    <?php endif; ?>
</script>
<div class='wrap'>
    <h2>Iterable Fields</h2>
    <form method='post' action='options.php' class='fields_form'>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th scope="col" class="manage-column">Name</th>
                    <th scope="col" class="manage-column">Show?</th>
                </tr>
            </thead>
            <tbody id='field_body'>
            </tbody>
        </table>
        <?php settings_fields( 'iterable-supress-fields' ); ?>
        <?php do_settings_sections( 'iterable-supress-fields' ); ?>
        <input type='hidden' class='fields' name='iterable-supress-fields' value='<?= esc_attr( get_option( 'iterable-supress-fields' ) ); ?>' />
        <?php submit_button(); ?>
    </form>
</div>
