<?php

require('../../../../wp-load.php');

class CronImages {

	public function __construct() 
	{ 
		$this->careImages();
		$this->createImagePackage();
	}

	public function createImagePackage()
	{
		global $wpdb;
		$pf = $wpdb->prefix;

		$query = $wpdb->get_results("
			SELECT
			`postId` AS ids,
			( SELECT COUNT(id) FROM `{$pf}cron_insert_image` WHERE `postid` = ids) AS total,
			( SELECT count(id) FROM `{$pf}cron_insert_image` WHERE `status` = 1 AND `postid` = ids ) AS done
			FROM `{$pf}cron_insert_image` WHERE postId != 0 GROUP BY `postId`
		");

		$this->careImagePackage($query);
	}

	public function careImagePackage($query)
	{
		if( is_array($query) && count($query) > 0 ):

			foreach ( $query as $key => $pics ):

				if( ( $pics->total == $pics->done ) && ( $pics->total > 0 ) ):

					$allImages = $this->getArrayImagePackage($pics->ids);
					//alterar post status
					//posts_status = sold
					//finn = sold
					
					wp_update_post(array(
						'ID'			=> $pics->ids,
						'post_status'	=> 'publish'
					) );

					


					global $wpdb;
					$pf = $wpdb->prefix;

					$wpdb->update(
						"{$pf}cron_insert_image",
						array( 'status' => 2 ),
						array( 'postId' => $pics->ids )
					);

				endif;	

			endforeach;

		endif;

	}

	public function getArrayImagePackage($postId)
	{
		global $wpdb;
		$pf = $wpdb->prefix;

		$query = $wpdb->get_results("
			SELECT * FROM {$pf}cron_insert_image WHERE postId = {$postId}
		");

		$imArray = array();
		if( is_array($query) && count($query) > 0 ):
			foreach ( $query as $key => $img ) :
				$imArray[$key]['thumbnail'] = $img->imageThumbnail;
				$imArray[$key]['alt'] = '';
				$imArray[$key]['desc'] = '';
				$imArray[$key]['title'] = $img->imageTitle;
				$imArray[$key]['caption'] = '';
				$imArray[$key]['id'] = $img->attachmentId;
				$imArray[$key]['url'] = $img->imageUrl;
			endforeach;
		endif;

		return $imArray;
	}



	public function getCronImagesFive()
	{
		global $wpdb;
		$pf = $wpdb->prefix;

		return $wpdb->get_results("
			SELECT * FROM `{$pf}cron_insert_image` WHERE `status` = 0 LIMIT 0, 5;
		");
	}

	public function careImages()
	{
		$images = $this->getCronImagesFive();

		if( is_array( $images ) ):

			foreach($images as $key => $image):

				$this->generate_each_image( $image );//->

			endforeach;

		endif;
	}

	public function generate_each_image($image)
	{
		$imageArray = $this->crb_insert_attachment_from_url($image->oldUrl, $image->postId);

		$this->updateAllNewImage($imageArray, $image->imageId, $image->postId);
	}


	public function updateAllNewImage( $image, $imageId, $postId )
	{
		if( isset($image) && is_array($image) ):

			global $wpdb;
			$pf = $wpdb->prefix;

			$query = $wpdb->update(
				"{$pf}cron_insert_image",
				array(
					'attachmentId' => $image['id'],
					'imageThumbnail' => $image['thumbnail'],
					'imageTitle' => $image['title'],
					'imageUrl' => $image['url'],
					'status' => 1
				),
				array(
					'imageId' => $imageId,
					'postId' => $postId
				)
			);

		endif;
	}


	/**
	 * Insert an attachment from an URL address.
	 *
	 * @param  String $url
	 * @param  Int    $post_id
	 * @param  Array  $meta_data
	 * @return Int    Attachment ID
	 */
	function crb_insert_attachment_from_url($url, $post_id = null) {

		if( !class_exists( 'WP_Http' ) )
			include_once( ABSPATH . WPINC . '/class-http.php' );

		$http = new WP_Http();
		$response = $http->request( $url );

		if( is_wp_error( $response ) ) {
			return false;
		}

		$upload = wp_upload_bits( strtotime(date('Y-m-d H:i:s')).".jpg", null, $response['body'] );
		if( !empty( $upload['error'] ) ) {
			return false;
		}

		$file_path = $upload['file'];
		$file_name = basename( $file_path );
		$file_type = wp_check_filetype( $file_name, null );
		$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
		$wp_upload_dir = wp_upload_dir();

		$post_info = array(
			'guid'				=> $wp_upload_dir['url'] . '/' . $file_name,
			'post_mime_type'	=> $file_type['type'],
			'post_title'		=> $attachment_title,
			'post_content'		=> '',
			'post_status'		=> 'inherit',
			'post_parent'		=> $post_id,
		);

		// Create the attachment
		$attach_id = wp_insert_attachment( $post_info, $file_path, $post_id );

		// Include image.php
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id,  $attach_data );

		$image = array(
			'thumbnail' => $wp_upload_dir['url'] . '/' . $attach_data['sizes']['thumbnail']['file'],
			'alt' => '',
			'desc' => '',
			'title' => $file_name,
			'caption' => '',
			'id' => $attach_id,
			'url' => $post_info['guid'],
		);

		return $image;

	}

}
new CronImages;