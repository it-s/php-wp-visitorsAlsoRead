<?php
/**
 * @package People Also Read
 * @version 1.0
 */
/*
Plugin Name: Also Read link
Plugin URI: http://www.likalo.com
Description: Allows to set a list of articles that people who are reading a page might also be interested in.
Author: Eugene Trounev
Version: 1.0
Author URI: http://www.likalo.com
*/
?>
<?php    
    add_action( 'admin_menu', 'its_ar_create_post_meta_box' );
    add_action( 'save_post', 'its_ar_save_post_meta_box', 10, 2 );

function its_ar_create_post_meta_box() {
    add_meta_box( 'its-ar-meta-box', 'Visitor Also Read', 'its_ar_post_meta_box', 'post', 'normal', 'high' );
    add_meta_box( 'its-ar-meta-box', 'Visitor Also Read', 'its_ar_post_meta_box', 'page', 'normal', 'high' );
}

function its_ar_get_message(){
	$message = get_option('its_ar_also_read_pages_message');
	if($message == "")$message = "Visitors who read this article also read the following:";
	return $message;
}

function its_ar_post_meta_box( $object, $box ) { 
	$selected_pages = explode(",",esc_html( get_post_meta( $object->ID, 'also_read_pages', true ), 1 ));
	$message = its_ar_get_message();
	?>
	<style>
	    #its-ar-meta-box ul li>ul {
		margin-left:1em;
	    }
	</style>
    <div id="its-ar-meta-box">
		<p>
			<h4>Section Title:</h4>
			<input name="its_ar_also_read_pages_message" style="width: 97%;" value="<?php echo $message; ?>" />
			<h4>Pages:</h4>
			<ul>
			<?php
				$pages = get_pages('depth=1&parent=0');
				if(count($pages)>0){
					foreach($pages as $page){
						its_sa_render_page_item($page,$selected_pages,$object->ID);
					}
				}else{echo "No pages found.";}
			?>
			</ul>
			<h4>Posts:</h4>
			<ul>
			<?php
				$pages = get_posts();
				if(count($pages)>0){
					foreach($pages as $page){
						its_sa_render_page_item($page,$selected_pages,$object->ID);
					}
				}else{echo "No posts found.";}
			?>
			</ul>
		</p>
		<input type="hidden" name="its_ar_meta_box_nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
    </div>
<?php }

function its_sa_render_page_item($page,$selection_filter,$ignore_self){
	$disabled = ($page->ID==$ignore_self)?"disabled":"";
	$selected = (in_array($page->ID,$selection_filter))?"checked":"";
	echo "<li><input type=\"checkbox\" name=\"also_read_pages[]\" value=\"".$page->ID."\" ".$selected." ".$disabled."> ".$page->post_title."\n";
	$children = get_pages('depth=1&child_of='.$page->ID.'&parent='.$page->ID);
	if(count($children)>0){
		echo "<ul>\n";
		foreach($children as $child){
			its_sa_render_page_item($child,$selection_filter,$ignore_self);
		}
		echo "</ul>\n";
	}
	echo "</li>";
}

function its_ar_save_post_meta_box( $post_id, $post ) {

    if ( !wp_verify_nonce( $_POST['its_ar_meta_box_nonce'], plugin_basename( __FILE__ ) ) )
        return $post_id;

    if ( !current_user_can( 'edit_post', $post_id ) )
        return $post_id;

	if( !isset($_POST['also_read_pages']) ) 
        return $post_id;
    //Saving Data
    
    $meta_value_t = get_post_meta( $post_id, 'also_read_pages', true );
    $new_meta_value_t = implode(",", $_POST['also_read_pages'] );

    if ( $new_meta_value_t && '' == $meta_value_t )
        add_post_meta( $post_id, 'also_read_pages', $new_meta_value_t, true );

    elseif ( $new_meta_value_t != $meta_value_t )
        update_post_meta( $post_id, 'also_read_pages', $new_meta_value_t );

    elseif ( '' == $new_meta_value_t && $meta_value_t )
        delete_post_meta( $post_id, 'also_read_pages', $meta_value_t );
		
	$option_value = $_POST['its_ar_also_read_pages_message'];
	if( $option_value!="" ) {
		update_option( 'its_ar_also_read_pages_message', $option_value ); 
	} else { 
		delete_option( 'its_ar_also_read_pages_message' ); 
	}
}

function its_sa_render_page_list($pages){
	$message = its_ar_get_message();
	echo "<section class=\"\"><h3><i class=\"icon-select icon-info-sign\" name=\"icon-info-sign\"></i> $message</h3><ul class=\"nav nav-tabs nav-stacked\">";
	foreach($pages as $page_id){
		echo "<li><a href=\"".get_page_link($page_id)."\" ><strong>";
		$page = get_page($page_id);
		if(function_exists('get_page_icon_class')&&get_page_icon_class($page_id)) echo '<i class="'.get_page_icon_class($page_id).'"></i> ';
		if($page ->post_parent!=0){
			$page_parent = get_page($page ->post_parent);
			echo $page_parent->post_title." > ";
		}
		echo $page->post_title."</strong>";
		if(function_exists('get_subtitle')){
			$subtitle = get_subtitle($page->ID);
			if($subtitle!="")echo "<blockquote>$subtitle</blockquote>";
		}
		echo "</a></li>";
	}
	echo "</ul></section>";
}

function get_also_read($post_id) {
	return get_post_meta($post_id, 'also_read_pages', true);
}

function has_also_read($post_id){
	return get_also_read($post_id)!='';
}

function the_also_read() {
	global $post;
	if(has_also_read($post->ID))
		its_sa_render_page_list(explode(",",get_also_read($post->ID)));
}

?>
