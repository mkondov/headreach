<?php
/**
 * Mask is a helper class which could convert a basic ID into a masked string.
 * The helper consists of 2 main methods which would allow both masking and unmasking of the ID.
 */

namespace app\models;

class IDMasking
{

	# Masking method
	public static function maskID($original_id){
		//Boost the number to look higher
		$new_id = ($original_id * 3) + 1000000;

		//convert from 10 to 8 base number
		$new_id = decoct($new_id);

		//avoid using 0 and 1 and use encryption matrix instead to mask the real ID
		$digits_translation_matrix_from = array('0', '1', '2', '3', '4', '5', '6', '7');
		$digits_translation_matrix_to   = array('9', '7', '6', '5', '4', '3', '2', '8'); //Adjust the matrix for better performance and masking

		$digits_translation_matrix_mapped = array_combine($digits_translation_matrix_from, $digits_translation_matrix_to);
		
		$new_id = (string)$new_id;
		$new_id_temp = "";
		foreach (str_split($new_id) as $i => $val){
			$new_id_temp = $new_id_temp . $digits_translation_matrix_mapped[$val];
		}
		$new_id = $new_id_temp;

		
		//Generate a checksum letter
		$letters_translation_matrix_from = array('0', '1', '2', '3', '4', '5', '6', '7');
		$letters_translation_matrix_to   = array('E', 'D', 'H', 'C', 'G', 'F', 'B', 'A');
		
		$checksum_num = $original_id % 8;
		$checksum_letter = $letters_translation_matrix_to[$checksum_num];
		
		$new_id = $new_id . $checksum_letter;
		
		return $new_id;
	}


	# Unmasking method
	public static function unmaskID($masked_id){
		$return = "ok";

		//get checksum letter
		$checksum_letter = substr($masked_id, -1);

		//Remove checksum letter from the masked id
		$id_no_checksum_letter = substr($masked_id, 0, strlen($masked_id) - 1);

		//First integrity checkup - we don't have 0 or 1 symbols
		if ($return == "ok" && strpos($id_no_checksum_letter, '0') !== false){
			$return = false;
		}
		if ($return == "ok" && strpos($id_no_checksum_letter, '1') !== false){
			$return = false;
		}

		//Get the checksum number by reverse translation matrix
		$letters_translation_matrix_from   = array('E', 'D', 'H', 'C', 'G', 'F', 'B', 'A');
		$letters_translation_matrix_to 	   = array('0', '1', '2', '3', '4', '5', '6', '7');
		
		$checksum_number = str_replace($letters_translation_matrix_from, $letters_translation_matrix_to, $checksum_letter);
		
		
		//Revet back to normal octal number, using reverted translation matrix
		$digits_translation_matrix_from  = array('9', '7', '6', '5', '4', '3', '2', '8'); //Adjust the matrix for better performance and masking
		$digits_translation_matrix_to    = array('0', '1', '2', '3', '4', '5', '6', '7');
		
		$digits_translation_matrix_mapped = array_combine($digits_translation_matrix_from, $digits_translation_matrix_to);
		
		$ocal_id = "";
		foreach (str_split($id_no_checksum_letter) as $i => $val){
			$ocal_id = $ocal_id . $digits_translation_matrix_mapped[$val];
		}
		
		$decimal_id = octdec($ocal_id);
		
		
		//Secondary integrity checkup
		if ((($decimal_id - 1000000) % 3) != 0){
			$return = false;
		}
		//Remove boosting from number
		$decimal_id = ($decimal_id - 1000000) / 3;

		
		//Third integrity checkup
		$expected_checksum_number = $decimal_id % 8;
		if ($checksum_number != $expected_checksum_number){
			$return = false;
		}
		
		if (!$return){
			$return_id = "ERROR_INVALID_ID";
		}else{
			$return_id = $decimal_id;
		}
		
		return $return_id;
	}
	 
}