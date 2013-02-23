<?PHP

/*
Plugin Name: Keyword replace
description: Allows you to automatically replace all category keywords in posts with a link to the category page. The plugin can also replace keywords with links to associated pages.
Version: 0.2
Author: pgogy
Plugin URI: http://www.pgogy.com and http://www.pgogy.com/code/groups/wordpress/keyword-replace/
Author URI: http://www.pgogy.com
*/


class keyword_replace{

	function keywordreplace_options_page() {
	  ?>
		<div class="wrap">
		<h2>Keyword Replace</h2>
		<form method="post" action="">
		<?php 
			
				wp_nonce_field('keywordreplace','keywordreplace');
				settings_fields( 'keywordreplace' );
		
		?><p>Which categories don't you want to replace (a checked box means the category won't be replaced)</p>
		<?PHP
		
			$args=array(
				'orderby' => 'name',
				'order' => 'ASC'
			);
			
			$categories=get_categories($args);
			
			$list = explode(",", get_option('categories_exclude'));
			
			foreach($categories as $category) { 
			
				echo "<p><label>" . $category->name . "</label> - <input name='category_" . $category->term_id . "' type='checkbox' ";
				
				if(in_array($category->term_id, $list)){
				
					echo " checked ";
				
				}
				
				echo "/></p>";
			
			}
		
		?>
		<p>Enter a word, then a comma, then a link to replace it with - say Wordpress,http://wordpress.org</p>
		<p>Separate words with a carriage return (press enter or return)</p>
		<textarea rows="15" cols="100" name="urlpairs" ><?php 
		
																$string = get_option('urlpairs');
																
																if($string==""){
																
																	echo "Wordpress,http://wordpress.org\n";
																
																}else{
																
																	echo $string;
																
																}
																 
																
														?></textarea>    
		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
		</form>
	</div>
	  
	  <?php
	}

	function keywordreplace_postform(){
	
		if (!empty($_POST['keywordreplace'])){

			if(!wp_verify_nonce($_POST['keywordreplace'],'keywordreplace') ){
			
				print 'Sorry, your nonce did not verify.';
				exit;
				
			}else{	

				update_option("urlpairs",$_POST['urlpairs']);
				
				$categories = array();
			
				foreach($_POST as $key => $value){
				
					if($value=="on"){
					
						array_push($categories, substr($key,strlen($key)-1,1));
					
					}
					
				}
				
				update_option("categories_exclude",implode(",", $categories));
								
			}
			
		}
		
	}

	function register_textswap() {
		register_setting( 'keywordreplace', 'urlpairs' );
		register_setting( 'keywordreplace', 'categories_exclude' );
	}

	function swap_text($output){
	
		$replace = explode("\n",get_option('urlpairs')); 
		
		$text = $output;
		
		while($pair = array_shift($replace)){
		
			$data = explode(",",$pair);
			
			$text = preg_replace("/\b(" . $data[0] . ")\b/","<a href=\"" . $data[1] . "\">" . $data[0] . "</a>", $text);
		
		}
		
		$args=array(
			'orderby' => 'name',
			'order' => 'ASC',
			'exclude' => get_option('categories_exclude')
		);
		
		$categories=get_categories($args);
		
		foreach($categories as $category) { 
		
			$text = preg_replace("/\b(" . $category->name . ")\b/"," <a href=\"" . get_category_link( $category->term_id ) . "\">" . $category->name . "</a>", $text);
		
		}
	
		return $text;
	
	}

	function swap_menu_option() {
	  add_options_page('Keyword Replace Options', 'Keyword Options', 'manage_options', 'keywordreplace', array($this,'keywordreplace_options_page'));
	}

}

$keyword_replace = new keyword_replace;

add_action('admin_init', array($keyword_replace,'register_textswap') );
add_action('admin_menu', array($keyword_replace,'swap_menu_option'));
add_filter( "the_content", array($keyword_replace,"swap_text") );
add_action('admin_head', array($keyword_replace,'keywordreplace_postform'));

?>