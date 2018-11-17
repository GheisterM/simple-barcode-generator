<?php

	//Table to turn numbers into bars, where "0" is a blank space and "1" is a bar.
	$guides = array(
		//							0					1					2				3					4				5					6				7					8				9
		"A" => array("0001101", "0011001", "0010011", "0111101", "0100011", "0110001", "0101111", "0111011","0110111", "0001011"),
		"B" => array("0100111", "0110011", "0011011", "0100001", "0011101", "0111001", "0000101", "0010001","0001001", "0010111"),
		"C" => array("1110010", "1100110", "1101100", "1000010", "1011100", "1001110", "1010000", "1000100","1001000", "1110100")
	);
	
	//Table to choose number encoding.
	$encoding = array(
		array(1 => "A", 2 => "A", 3 => "A", 4 => "A", 5 => "A", 6 => "A"), //0
		array(1 => "A", 2 => "A", 3 => "B", 4 => "A", 5 => "B", 6 => "B"), //1
		array(1 => "A", 2 => "A", 3 => "B", 4 => "B", 5 => "A", 6 => "B"), //2
		array(1 => "A", 2 => "A", 3 => "B", 4 => "B", 5 => "B", 6 => "A"), //3
		array(1 => "A", 2 => "B", 3 => "A", 4 => "A", 5 => "B", 6 => "B"), //4
		array(1 => "A", 2 => "B", 3 => "B", 4 => "A", 5 => "A", 6 => "B"), //5
		array(1 => "A", 2 => "B", 3 => "B", 4 => "B", 5 => "A", 6 => "A"), //6
		array(1 => "A", 2 => "B", 3 => "A", 4 => "B", 5 => "A", 6 => "B"), //7
		array(1 => "A", 2 => "B", 3 => "A", 4 => "B", 5 => "B", 6 => "A"), //8
		array(1 => "A", 2 => "B", 3 => "B", 4 => "A", 5 => "B", 6 => "A"), //9
	);
	
	//Function to draw the .png file containing the barcode.
	function drawCode ($cod, $binary){
	
		//We turn the strings to arrays so we can go over each digit
		$sArray = str_split($binary);
		$cArray = str_split($cod);
		
		//We create a new image. Dimensions should be the official for the EAN-13 barcode
		header('Content-Type: image/png');
		$im = @imagecreate(440, 326) or die("Cannot Initialize new GD image stream");
		
		//white background
		$white = imagecolorallocate($im, 255, 255, 255);
		imagefill($im, 0, 0, $white);
		
		//We choose black for the bars color.
		$black = imagecolorallocate($im, 0, 0, 0);
		
		//We go over the "binary" array. Where is a "0" we don't do anything, but where is a "1" we draw a line 4 pixels wide.
		for ($i = 0; $i < strlen($binary); $i++) {
			//Bars start at x pixel 25, and are 4 pixels wide.
			$xPos = 25 + ($i * 4);
			//For most bars, line ends at y pixel 280...
			$maxY = 280;
			//except for first, middle and last bars. These are a little longer.
			if($i<6 or ($i>47 and $i<53) or $i>94){
				$maxY = 298;
			}
			
			//And as said before, where is a "1" we draw a bar 4 pixels wide, starting at y point 10, and ending in the max point selected before.
			if($sArray[$i]=="1"){
				imageline($im, $xPos, 10, $xPos, $maxY, $black);
				imageline($im, $xPos+1, 10, $xPos+1, $maxY, $black);
				imageline($im, $xPos+2, 10, $xPos+2, $maxY, $black);
				imageline($im, $xPos+3, 10, $xPos+3, $maxY, $black);
			}
		}	
		
		//We select the font. Remember to have it in the same folder as the script.
		$font = dirname(__FILE__) . '/Arial.ttf';
		//We draw the first character
		imagettftext($im, 27, 0, 15, 316, $black, $font, $cArray[0]);
		//And the following ones.
		for ($i = 1; $i < strlen($cod); $i++) {
			$xPos = 31 + ($i * 27);
			if($i<7){
				imagettftext($im, 27, 0, $xPos, 316, $black, $font, $cArray[$i]);
			}
			else{
				$xPos = 51 + ($i * 27);
				imagettftext($im, 27, 0, $xPos, 316, $black, $font, $cArray[$i]);
			}
		}		
		
		//Finally, we set up the content for download.
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-image');
		header("Content-disposition: attachment; filename=barcode.png");
		
		imagepng($im);
		
		imagedestroy($im);		
	}

	//Here we set some variables
	function barcode ($cod){
		global $guides;
		global $encoding;
		
		//We split the code into an array to go over it and set the bar and space combination.
		$sArray = str_split($cod);
		
		//First bars. Fixed position.
		$codeGuide = "000101";
		
		//We go over every single number to select the appropriate bars and space combination.
		for ($i = 1; $i < strlen($cod); $i++) {
			$digit = (int) $sArray[$i];
			
			if($i<7){
				$codeGuide .= $guides[$encoding[$sArray[0]][$i]][$digit];
				if($i==6){
					//Middle bars. Fixed position.
					$codeGuide.="01010";
				}
			}
			else{
				$codeGuide .= $guides["C"][$digit];
			}
		}
		
		//Final bars. Fixed position.
		$codeGuide .= "10100";
		
		//We send the code and the bars and space combination to finally draw the barcode.
		drawCode($cod, $codeGuide);
	}
?>