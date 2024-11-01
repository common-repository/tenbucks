<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.tenbucks.io
 * @since      1.0.0
 *
 * @package    Tenbucks
 * @subpackage Tenbucks/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2 class="clear"><?php _e('Tenbucks', 'tenbucks'); ?></h2>
	<?php foreach ($this->notice as $notice) { ?>
		<div class="notice notice-<?php echo $notice['type']; ?>">
			<p><?php echo $notice['message']; ?></p>
		</div>
	<?php } ?>

	<iframe id="tenbucks-iframe" name="tenbucks-iframe" src="<?php echo $iframe_url; ?>" frameborder="0"></iframe>
	<hr />
	<p><a href="<?php echo $standalone_url; ?>" target="_blank"><?php _e('Use addons on our website', 'tenbucks'); ?></a>. <small>(<?php _e('No iframe', 'tenbucks'); ?>)</small></p>
        <hr />
	<?php echo sprintf( __( 'If you like <strong>tenbucks.</strong> please leave us a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. A huge thank you from tenbucks. team in advance!', 'tenbucks' ), '<a href="https://wordpress.org/support/view/plugin-reviews/tenbucks?filter=5#postform" target="_blank" class="wc-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'tenbucks' ) . '">', '</a>' ); ?>
</div>
