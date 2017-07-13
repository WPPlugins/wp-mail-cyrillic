<?php
/*
Plugin Name: wp_mail Cyrillic
Version: 0.52
Plugin URI: http://uplift.ru/projects/wp-mail-cyrillic/
Description: Allows to receive e-mail messages in character sets different from the blog charset. Based on the original plugin by Anton Skorobogatov.
Author: Sergey Biryukov
Author URI: http://sergeybiryukov.ru/
*/

function wp_mail_cyr_filter($data) {
	$charset = wp_mail_cyr_get_charset();
	mb_internal_encoding($charset);

	if ( eregi('=\\?([^?]+)\\?(q|b)\\?([^?]+)\\?=', $data['headers']) ) {
		$pattern = '/From: (.+) <(.+)>/si';
		if ( preg_match($pattern, $data['headers'], $matches) ) {
			$replacement = 'From: ' . mb_decode_mimeheader($matches[1]) . ' <$2>';
			$data['headers'] = preg_replace($pattern, $replacement, $data['headers']);
		}
	}

	if ( eregi('=\\?([^?]+)\\?(q|b)\\?([^?]+)\\?=', $data['subject']) )
		$data['subject'] = mb_decode_mimeheader($data['subject']);

	$data['headers'] = mb_convert_encoding($data['headers'], $charset, 'auto');

	$data['subject'] = mb_convert_encoding($data['subject'], $charset, 'auto');
	$data['subject'] = mb_encode_mimeheader($data['subject'], $charset, 'B');

	$data['message'] = mb_convert_encoding($data['message'], $charset, 'auto');

	return $data;
}
add_filter('wp_mail', 'wp_mail_cyr_filter');

function wp_mail_cyr_from_name_filter($name) {
	$charset = wp_mail_cyr_get_charset();
	mb_internal_encoding($charset);

	$name = mb_convert_encoding($name, $charset, 'auto');

	return mb_encode_mimeheader($name, $charset, 'B', '');;
}
add_filter('wp_mail_from_name', 'wp_mail_cyr_from_name_filter', 11);

function wp_mail_cyr_get_charset($blog_charset = 'UTF-8') {
	global $phpmailer;

	$charset = get_option('wp_mail_cyr_charset');
	if ( empty($charset) )
		$charset = $blog_charset;

	$phpmailer->Encoding = (!strcasecmp($charset, 'UTF-8') ? 'base64' : '8bit');

	return $charset;
}
add_filter('wp_mail_charset', 'wp_mail_cyr_get_charset');

function wp_mail_cyr_options_page() {
?>
<div class="wrap">
<h2><?php _e('Setup E-mail Charset', 'wp-mail'); ?></h2>
<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<p>
<?php _e('Send messages in this charset:', 'wp-mail'); ?>

<select name="wp_mail_cyr_charset" style="vertical-align: middle">
<?php
$charsets = array('UTF-8', 'Windows-1251', 'KOI8-R');
$current_charset = wp_mail_cyr_get_charset();
foreach ( $charsets as $charset ) :
	$selected = (!strcasecmp($charset, $current_charset) ? ' selected="selected"' : '');
	echo "<option value='" . strtolower($charset) . "'$selected>$charset</option>";
endforeach;
?>
</select>
</p>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="wp_mail_cyr_charset" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-mail'); ?>" />
</p>

</form>
</div>
<?
}

function wp_mail_cyr_add_menu() {
	load_plugin_textdomain('wp-mail', PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)));
	add_options_page(__('E-mail Charset', 'wp-mail'), __('E-mail Charset', 'wp-mail'), 'administrator', basename(__FILE__), 'wp_mail_cyr_options_page');
}
add_action('admin_menu', 'wp_mail_cyr_add_menu');
?>