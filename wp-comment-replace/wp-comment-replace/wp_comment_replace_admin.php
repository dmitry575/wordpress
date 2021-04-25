<?php
function wp_comment_replace_admin(){
	add_options_page('WP Comment Replace Options', 'WP Comment Replace','manage_options', __FILE__, 'wp_comment_replace_options');
	add_action('admin_init','wp_comment_replace_register' );
}
function wp_comment_replace_register(){
	register_setting('was-settings','wp_comment_replace_count');
	register_setting('was-settings','wp_comment_replace_links');



}

function wp_comment_replace_options(){

$wpcomment =  new WPCommentReplace();
$wpcomment->Init();

if(!empty($_POST))
  {
   $wpcomment->SaveUrls($_POST['wp_comment_replace_urls']);
   $wpcomment->SaveAnchors($_POST['wp_comment_replace_anchors']);
   $wpcomment->SaveStopWords($_POST['wp_comment_replace_stopwords']);
   //---
   update_option('wp_comment_replace_count',(int)$_POST['wp_comment_replace_count']);
   update_option('wp_comment_replace_links',(int)$_POST['wp_comment_replace_links']);
   $save=true;
  }
$wp_comment_replace_urls = $wpcomment->GetUrlsStr();
$wp_comment_replace_anchors = $wpcomment->GetAnchorsStr();
$wp_comment_replace_stopwords = $wpcomment->GetStopWordsStr();

?>
<div class="wrap">
	
<?php screen_icon(); ?>
<h2>WP Comment Replace</h2>
<div style="text-align:center;"><a href="/" target="_blank">Homepage</a></div>

<form action="" method="post" enctype="multipart/form-data" name="wp_comment_replace_form">
<?
if($save)
{
?>
<div id="setting-error-settings_updated" class="updated settings-error">
<p>Settings saved.</p>
</div>
<?
}
?>

<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php _e('Количество комментариев в сутки','WP-Replace-Comment'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="wp_comment_replace_count" value="<?php echo get_option('wp_comment_replace_count'); ?>" size="8" style="width:62px;height:24px;" />
			</label>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<?php _e('Количество конвертируемых ссылок в сутки','WP-Replace-Comment'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="wp_comment_replace_links" value="<?php echo get_option('wp_comment_replace_links'); ?>" size="8" style="width:62px;height:24px;" />
			</label>
		
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e('Список урлов в файле "urls.txt"','WP-Replace-Comment'); ?>
		</th>
		<td>
			<label>
				<textarea type="text" name="wp_comment_replace_urls" cols="100" rows="11" style="width:800px;height:180px;font-size:12px;"><?php echo $wp_comment_replace_urls; ?></textarea>
				<br /><?php _e('','WP-Comment-Replace'); ?>
			</label>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<?php _e('Список анкоров в файле "anchors.txt"','WP-Replace-Comment'); ?>
		</th>
		<td>
			<label>
				<textarea type="text" name="wp_comment_replace_anchors" cols="100" rows="11" style="width:800px;height:180px;font-size:12px;"><?php echo $wp_comment_replace_anchors; ?></textarea>
				<br /><?php _e('','WP-Comment-Replace'); ?>
			</label>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<?php _e('Список стоп слов в файле "stop_words.txt"','WP-Replace-Comment'); ?>
		</th>
		<td>
			<label>
				<textarea type="text" name="wp_comment_replace_stopwords" cols="100" rows="11" style="width:800px;height:180px;font-size:12px;"><?php echo $wp_comment_replace_stopwords; ?></textarea>
				<br /><?php _e('','WP-Comment-Replace'); ?>
			</label>
		</td>
	</tr>

</table>

<p class="submit">
<input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Changes'); ?>" />
</p>

</form>


<div style="text-align:center; margin:60px 0 10px 0;">&copy; <?php echo date("Y"); ?> </div>

</div>
<?php 
}
add_action('admin_menu', 'wp_comment_replace_admin');
?>