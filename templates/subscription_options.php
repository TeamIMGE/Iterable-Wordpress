<div class='container subscription_options'>
    <form method='post' action='<?= admin_url( 'admin-ajax.php' ); ?>'>
        <input type='hidden' name='email' id='email' value='<?= $_REQUEST[ 'email' ] ?>' />
        <input type='hidden' name='list' id='list' value='<?= $_REQUEST[ 'list' ] ?>' />
        <div class='row'>
            <div class='col-md-4'>
                <div class='subscription_container'>
                    <h2>All of our Sends</h2>
                    <?php foreach( $all_channels as $channel ): ?>
                    <div class='checkbox' data-name="<?= $channel->name ?>">
                        <label>
                            <input type='checkbox' value='<?= $channel->id ?>' <?= isset( $unsubscribed_ids[ $channel->id ] ) ? '' : 'checked' ?> />
                            <?= $channel->name ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class='col-md-8'>
                <div class='subscription_container'>
                    <h2>Sends You've Subscribed To</h2>
                    <p class='subscribed_sends'></p>
                    <img class='sad_koala' src='<?= plugins_url( 'assets/sad_koala.png', __FILE__ ) ?>' caption='sad koala' />
                    <button class='btn btn-primary' id='save_changes'>Save Changes</button>
                    <input type='submit' style='display: none;' />
                </div>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-12 unsubscribe-all'>
                <h3>If you'd rather stop receiving all of our emails, click the button below to unsubscribe</h3>
                <div class='btn btn-danger btn-unsubscribe-all'>Unsubscribe from Everything</div>
            </div>
        </div>
    </form>
</div>
