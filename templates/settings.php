<div class='wrap'>
    <h2>Iterable Settings</h2>
    <form method='post' action='options.php'>
        <?php settings_fields( 'iterable-settings' ); ?>
        <?php do_settings_sections( 'iterable-settings' ); ?>
        <table class='form-table'>
            <tr valign='top'>
                <th scope='row'>Iterable API Key</th>
                <td><input type='text' name='api_key' value='<?= esc_attr( get_option( 'api_key' ) ); ?>' /></td>
            </tr>
            <tr valign='top'>
                <th scope='row'>Listwise API Key</th>
                <td><input type='text' name='listwise_key' value='<?= esc_attr( get_option( 'listwise_key' ) ); ?>' /></td>
            </tr>
            <tr valign='top'>
                <th scope='row'>Plugin Error Email</th>
                <td><input type='text' name='error_email' value='<?= esc_attr( get_option( 'error_email' ) ); ?>' /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
