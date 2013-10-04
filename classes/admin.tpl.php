<?php
/**
 *  Шаблон админки ВСЕХ заказов
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
    <div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
        <div id="side-info-column" class="inner-sidebar">
            <?php do_meta_boxes($this->pagehook, 'side', $data); ?>
        </div>
        <div id="post-body" class="has-sidebar">
            <div id="post-body-content" class="has-sidebar-content">
                <?php do_meta_boxes($this->pagehook, 'normal', $data); ?>
                <?php do_meta_boxes($this->pagehook, 'additional', $data); ?>
            </div>
        </div>
        <br class="clear"/>

    </div>
</form>
</div>
