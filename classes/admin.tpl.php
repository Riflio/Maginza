<?php
/**
 *
 *
 */
?>


<div class="wrap">
<?php screen_icon('options-general'); ?>
<h2>Заказы</h2>
<form>
    <?php
        wp_nonce_field('howto-metaboxes-general');
        wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
        wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
    ?>

    <input type="hidden" name="action" value="save_howto_metaboxes_general" />

</form>
</div>
