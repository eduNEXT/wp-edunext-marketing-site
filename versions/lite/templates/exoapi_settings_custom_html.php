<div class="eoxapis-container">
    <h2>Token information</h2>
    <p><b>token: </b>
    <?php
    $wp_edunext_token  = get_option( 'wpt_eox_token', '' );
    $wp_edunext_lenght = strlen( $wp_edunext_token );
    echo substr( $wp_edunext_token, 0, $wp_edunext_lenght / 5 ) . '*******' . substr( $wp_edunext_token, $wp_edunext_lenght * 4 / 5, $wp_edunext_lenght );
    ?>
    <a id="token-refresh" href="#">Refresh</a> </p>
    <p><b>last check: </b><?php echo date( DATE_ATOM, get_option( 'last_checked_working', 0 ) ); ?></p>
</div>
<script>
jQuery(function ($) {
    $('#token-refresh').click(function (e) {
        var data = {
            'action': 'refresh_token',
            '_ajax_nonce': "<?php echo wp_create_nonce( 'eoxapi' ); ?>"
        };
        jQuery.post(ajaxurl, data, function(html) {
            $('.notice').remove();
            $('#wp-edunext-marketing-site_settings > h2').first().after(html);
        });
    });
})
</script>
