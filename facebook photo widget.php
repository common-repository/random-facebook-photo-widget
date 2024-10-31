<?php
/*
Plugin Name: Random Facebook Photo Widget
Plugin URI: http://mackerelsky.co.nz
Description: Displays a random public photo from a user or page's Facebook album in a widget. 
Version: 1.0
Author: Ed Goode
Author URI: http://mackerelsky.co.nz
License: GPL2
*/


//code for widget 
class facebook_photo_widget extends WP_Widget {

	//constructor
	function facebook_photo_widget() {
		$widget_ops = array( 'classname' => 'facebook_photo_widget', 'description' => 'Displays a random photo from your Facebook albums' ); // Widget Settings
		$control_ops = array( 'id_base' => 'facebook_photo_widget' ); // Widget Control Settings
		$this->WP_Widget( 'facebook_photo_widget', 'Facebook Photos', $widget_ops, $control_ops ); // Create the widget
	}

 	public function form( $instance ) {
		// outputs the options form on admin
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = $instance['title']; ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>'" type="text" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('image_size'); ?>">Image Size (should be a number between 1 (biggest) and 6(smallest)):</label>
			<input class="widefat" id="<?php echo $this->get_field_id('image_size'); ?>" name="<?php echo $this->get_field_name('image_size'); ?>'" type="text" value="<?php echo $instance['image_size']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('max_width'); ?>">(Optional) Max width for your image (in pixels):</label>
			<input class="widefat" id="<?php echo $this->get_field_id('max_width'); ?>" name="<?php echo $this->get_field_name('max_width'); ?>'" type="text" value="<?php echo $instance['max_width']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('facebook_page'); ?>">Facebook Page/Person ID:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('facebook_page'); ?>" name="<?php echo $this->get_field_name('facebook_page'); ?>'" type="text" value="<?php echo $instance['facebook_page']; ?>" />
		</p>
		<p>If you go to the facebook person or page you want to pull photos from and the url is <i>https://www.facebook.com/(name)</i>, then the id is (name).  If the url is <i>https://facebook.com/(name)/(number)</i>, then the id is (number)</p>

	
	<?php
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['image_size'] = strip_tags($new_instance['image_size']);
		$instance['max_width'] = strip_tags($new_instance['max_width']);
		$instance['facebook_page'] = strip_tags($new_instance['facebook_page']);
		return $instance;
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = $instance['title'];
		$size = $instance['image_size'];
		$max_width = $instance['max_width'];
		$page = $instance['facebook_page'];
		
		//widget display code
		// Before widget

		echo $before_widget;

		// Widget title

		if ( $title ) { echo $before_title . $title . $after_title; }

		// Widget output 

		//get list of photo albums
		try {
		$albums = call_graph_api($page.'/albums');
		$albums = json_decode($albums['body']);
		$data = $albums->data;
		//select one at random
		$selected_album = $data[array_rand($data)];
		$album_id = $selected_album->id;
		//get list of photos in that album
		$photos = call_graph_api($album_id.'/photos');
		$photos = json_decode($photos['body']);
		$data = $photos->data;
		//select one at random
		$selected_photo = $data[array_rand($data)];
		$images = $selected_photo->images;
		$arrsize = sizeof($images);
		if ($arrsize>=$size) {
		$image = $images[$size];
		}
		else
		{
		$size = count($images) - 1;
		$image = $images[$size];
		}
		$source = $image->source;
		//bundle that photo's URL in img tags for display
		echo "<a href='http://www.facebook.com/".$page."'><img src=".$source." width='".$max_width."'></a>";
		}
		catch (Exception $e) {
			echo "Error: ".$e->getMessage();
		}
		// After widget

		echo $after_widget;
	
	}

}
add_action( 'widgets_init', create_function('', 'return register_widget("facebook_photo_widget");') );

function call_graph_api($url_ending) {	

	
	//get the url
	$url = "http://graph.facebook.com/".$url_ending;
	//call the API and return the results
	$response = wp_remote_request($url);
	
	return $response;

}

?>
