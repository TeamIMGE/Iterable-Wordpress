<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/underscore.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>
<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/workflows.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>
<script type='text/template' class='workflows_template'>
<% _.each( workflows, function( workflow ) { %>
<tr class='workflow'>
    <td>
        <select class='list_id'>
            <option></option>
            <% _.each( iterable_lists, function( list ) { %>
                <option value='<%= list.id %>' <% if( list.id == workflow.list_id ) { %>selected<% } %>><%= list.name %></option>
            <% } ); %>
        </select>
        <div class="row-actions">
            <span class="trash">
                <a title="Delete" href="#" class='delete_workflow'>Delete</a>
            </span>
        </div>
    </td>
    <td>
        <input type='number' class='workflow_id' value='<%= workflow.workflow_id %>' required />
    </td>
</tr>
<% } ); %>
</script>
<?php $iterable_lists = $iterable->lists(); ?>
<script type='text/javascript'>
<?php if( $iterable_lists[ 'success' ] ): ?>
    iterable_lists = <?= json_encode( $iterable_lists[ 'content' ] ) ?>;
<?php else: ?>
    iterable_lists = [];
<?php endif; ?>
</script>
<div class='wrap'>
    <h2>Workflows</h2>
    <p>These will be executed once daily.</p>
    <br />
    <a class='button add-new-h2 add_new_workflow' href='#'>Add New</a>
    <form method='post' action='options.php' class='workflows_form'>
        <table class='widefat fixed' cellspacing='0'>
            <thead>
                <tr>
                    <th scope='col' class='manage-column'>List ID</th>
                    <th scope='col' class='manage-column'>Workflow ID</th>
                </tr>
            </thead>
            <tbody id='workflows_body'>
            </tbody>
        </table>
        <?php settings_fields( 'iterable-workflows' ); ?>
        <?php do_settings_sections( 'iterable-workflows' ); ?>
        <input type='hidden' class='workflows' name='workflows' value='<?= esc_attr( get_option( 'workflows' ) ); ?>' />
        <?php submit_button(); ?>
    </form>
</div>
