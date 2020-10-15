<?php

namespace app\models;

class ImageWorker extends \yii\base\Object {

	public function savePhotoFromUrl($url) {
		$ch = curl_init ( $url );
		
		if (defined ( 'CURLOPT_IPRESOLVE' ) && defined ( 'CURL_IPRESOLVE_V4' )) {
			curl_setopt ( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		}

		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_BINARYTRANSFER, 1 );
		$raw = curl_exec ( $ch );
		curl_close ( $ch );
		
		$parts = pathinfo ( $url );
		
		if (! isset ( $parts ['extension'] )) {
			$type = exif_imagetype ( $url );
			switch ($type) {
				case IMAGETYPE_JPEG :
					$parts ['extension'] = "jpg";
					break;
				case IMAGETYPE_PNG :
					$parts ['extension'] = "png";
					break;
				default :
					$parts ['extension'] = "jpg";
					break;
			}
		}

		$unique_hash = uniqid ( rand (), true );
		$first_folder = substr ( $unique_hash, 0, 2 );
		$second_folder = substr ( $unique_hash, 2, 4 );
		$photo_filename = substr ( $unique_hash, 4 ) . "." . $parts ["extension"];
		
		$image_path = 'images/' . $first_folder . '/' . $second_folder . '/' . $photo_filename;
		$saveto = ASSETS_PATH . $image_path;
		$saveto_dir = ASSETS_PATH . 'images/' . $first_folder . '/' . $second_folder . '/';
		
		if (file_exists ( $saveto )) {
			unlink ( $saveto );
		}

		if (! file_exists ( $saveto_dir )) {
			mkdir ( $saveto_dir, 0755, true );
		}

		$fp = fopen ( $saveto, 'x' );
		fwrite ( $fp, $raw );
		fclose ( $fp );
		
		chmod ( $saveto, 0644 );
		
		return $image_path;
	}

}