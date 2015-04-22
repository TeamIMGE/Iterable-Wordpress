<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/underscore.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>
<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/campaigns.js', __FILE__ ) ?>?v=<?= VERSION ?>"></script>
<script type='text/template' class='campaigns_template'>
<% _.each( campaigns, function( campaign ) { %>
<tr class='campaign'>
    <td>
        <input type='text' value='<%= campaign.name %>' class='name' required />
        <div class="row-actions">
            <span class="trash">
                <a title="Delete" href="#" class='delete_campaign'>Delete</a>
            </span>
        </div>
    </td>
    <td>
        <select class='list_id'>
            <option></option>
            <% _.each( iterable_lists, function( list ) { %>
                <option value='<%= list.id %>' <% if( list.id == campaign.list_id ) { %>selected<% } %>><%= list.name %></option>
            <% } ); %>
        </select>
    </td>
    <td>
        <input type='number' value='<%= campaign.template_id %>' class='template_id' required />
    </td>
    <td>
        <select class='suppression_list_ids'>
            <option></option>
            <% _.each( iterable_lists, function( list ) { %>
                <option value='<%= list.id %>' <% if( list.id == campaign.suppression_list_ids ) { %>selected<% } %>><%= list.name %></option>
            <% } ); %>
        </select>
    </td>
    <td>
        <input type='time' value='<%= campaign.send_at %>' class='send_at' required />
        <input type='hidden' value='<%= campaign.last_send %>' class='last_send' />
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
    <h2>Campaigns</h2>
    <p>The Internet is an unpredictable and scary place. The most I can guarantee is that the campaign will be scheduled between X - 1 HOUR and X, where X is the time specified in 'Send At'. Or not at all. This can occur at most once within that time period for a given day. DO NOT SCHEDULE SENDS NEAR MIDNIGHT, this is asking for trouble.</p>
    <br />
    <a class='button add-new-h2 add_new_campaign' href='#'>Add New</a>
    <form method='post' action='options.php' class='campaign_form'>
        <table class='widefat fixed' cellspacing='0'>
            <thead>
                <tr>
                    <th scope='col' class='manage-column'>Name</th>
                    <th scope='col' class='manage-column'>List ID</th>
                    <th scope='col' class='manage-column'>Template ID</th>
                    <th scope='col' class='manage-column'>Supression List IDs</th>
                    <th scope='col' class='manage-column'>Send At</th>
                </tr>
            </thead>
            <tbody id='campaigns_body'>
            </tbody>
        </table>
        <?php settings_fields( 'iterable-campaigns' ); ?>
        <?php do_settings_sections( 'iterable-campaigns' ); ?>
        <input type='hidden' class='campaigns' name='campaigns' value='<?= esc_attr( get_option( 'campaigns' ) ); ?>' />
        <?php submit_button(); ?>
    </form>
</div>
