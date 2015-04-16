<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/underscore.js', __FILE__ ) ?>"></script>
<script type='text/javascript' src="<?= plugins_url( 'assets/scripts/message_channels.js', __FILE__ ) ?>"></script>
<script type='text/template' class='message_channel_template'>
<% _.each( message_channels, function( channel ) { %>
<tr class='channel'>
    <td>
        <input type='text' value='<%= channel.name %>' class='name' required />
        <div class="row-actions">
            <span class="trash">
                <a title="Delete" href="#" class='delete_channel'>Delete</a>
            </span>
        </div>
    </td>
    <td>
        <input type='number' value='<%= channel.id %>' class='id' required />
    </td>
</tr>
<% } ); %>
</script>
<div class='wrap'>
    <h2>Message Channels</h2>
    <p>You can use the <span style='font-family: Courier New'>[subscription_options]</span> shortcode to display the message channels preference centre to users.</p>
    <a class="button add-new-h2 add_new_channel" href='#'>Add New</a>
    <form method='post' action='options.php' class='channel_form'>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th scope="col" class="manage-column">Name</th>
                    <th scope="col" class="manage-column">ID</th>
                </tr>
            </thead>
            <tbody id='channel_body'>
            </tbody>
        </table>
        <?php settings_fields( 'iterable-message-channels' ); ?>
        <?php do_settings_sections( 'iterable-message-channels' ); ?>
        <input type='hidden' class='message_channels' name='message_channels' value='<?= esc_attr( get_option( 'message_channels' ) ); ?>' />
        <?php submit_button(); ?>
    </form>
</div>
