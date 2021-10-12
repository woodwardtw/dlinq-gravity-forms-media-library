<?php 
/*
Plugin Name: DLINQ Gravity Forms images to media library
Plugin URI:  https://github.com/
Description: Gravity forms doesn't put images and things in the media library. This should make it do that. 
Version:     1.0
Author:      DLINQ
Author URI:  http://dlinq.middcreate.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


//from https://infinitesynergysolutions.com/learning-center/add-gravity-forms-file-uploads-and-image-uploads-to-the-media-library/

add_action( 'gform_after_create_post', 'set_post_content', 10, 3 );

//add_action( 'gform_after_submission', 'iss_gf_after_submission', 10, 2 );
function iss_gf_after_submission($post_id, $entry, $form) {
    //Walk through the form fields and find any file upload fields
    foreach ($form['fields'] as $field) {
      if ($field->type == 'fileupload') {
      //See if an image was submitted with this entry
        if (isset($entry[$field->id])) {
          $fileurl = $entry[$field->id];

          // The ID of the post this attachment is for. Use 0 for unattached.
          $parent_post_id = $post_id;

          // Check the type of file. We'll use this as the 'post_mime_type'.
          $filetype = wp_check_filetype( basename( $fileurl ), null );

          // Get the path to the upload directory.
          $wp_upload_dir = wp_upload_dir();

          //Gravity forms often uses its own upload folder, so we're going to grab whatever location that is
          $parts = explode('uploads/', $entry[$field->id]);
          $filepath = $wp_upload_dir['basedir'].'/'.$parts[1];
          $fileurl = $wp_upload_dir['baseurl']. '/'.$parts[1];

      // Prepare an array of post data for the attachment.
      $attachment = array(
      'guid' => $fileurl,
      'post_mime_type' => $filetype['type'],
      'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $fileurl) ),
      'post_content' => '',
      'post_status' => 'inherit'
      );

      // Insert the attachment.
      $attach_id = wp_insert_attachment( $attachment, $filepath, $parent_post_id );

      //Image manipulations are usually an admin side function. Since Gravity Forms is a front of house solution, we need to include the image manipulations here.
      require_once( ABSPATH . 'wp-admin/includes/image.php' );

      // Generate the metadata for the attachment, and update the database record.
      if ($attach_data = wp_generate_attachment_metadata($attach_id, $filepath)) {
          wp_update_attachment_metadata($attach_id, $attach_data);
          } else {
        echo '
        <div id="message" class="error">
        <h1>Failed to create Meta-Data</h1>
        </div>
        ';
        }
      }
    }
  }
}




//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

  //print("<pre>".print_r($a,true)."</pre>");
