<?php
session_start();


/**
 * Written in saveform.php:
 * $captcha_length = xx;
 * $captcha_format = lettersandnumbers / letters / numbers
 */


$captcha_length = (isset($captcha_length) && $captcha_length)?$captcha_length:6;

if(isset($_GET['length']) && ctype_digit($_GET['length'])) $captcha_length = $_GET['length'];

if(isset($_GET['format']) && in_array($_GET['format'], array('lettersandnumbers', 'letters', 'numbers')) ) $captcha_format = $_GET['format'];


$captcha_characters = array();

$captcha_number = array(1,2,3,4,5,6,7,8,9);

$captcha_letter = array('a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z');



/**
 * DEFAULT NUMBER OF LETTERS AND NUMBERS
 */
$nb_numbers = ceil($captcha_length/2);
$nb_letters = $captcha_length-ceil($captcha_length/2);

/**
 * NUMBERS IN THE CAPTCHA
 */

if(isset($captcha_format) && $captcha_format == 'numbers')
{
	$captcha_characters = array();
	$nb_numbers = $captcha_length;
}

$captcha_rand_number = array_rand($captcha_number, $nb_numbers);



if(!is_array($captcha_rand_number)) // If you are picking only one entry, array_rand() returns the key for a random entry.
{
	$captcha_rand_number = array($captcha_rand_number);
}

//var_dump($captcha_rand_number);


foreach($captcha_rand_number as $value)
{
	$captcha_characters[] = $captcha_number[$value];
}


/**
 * LETTERS IN THE CAPTCHA
 */

if(isset($captcha_format) && $captcha_format == 'letters')
{
	$captcha_characters = array();
	$nb_letters = $captcha_length;
}


if(count($captcha_characters)<$captcha_length)
{
	$captcha_rand_letter = array_rand($captcha_letter, $nb_letters);
	
	if(!is_array($captcha_rand_letter)) // If you are picking only one entry, array_rand() returns the key for a random entry.
	{
		$captcha_rand_letter = array($captcha_rand_letter);
	}
	//var_dump($captcha_rand_letter);

	foreach($captcha_rand_letter as $value)
	{
		$captcha_characters[] = $captcha_letter[$value];
	}

}
//var_dump($captcha_characters);

/**
 * SHUFFLE AND IMAGE
 */

shuffle($captcha_characters);

$captcha_img_value = '';

foreach($captcha_characters as $value)
{
	$captcha_img_value .= $value;
}

$_SESSION['captcha_img_string']['UNIQUE_ID'] = $captcha_img_value;



// Create a 100*30 image
$im = imagecreate(120, 25);

// White background and blue text
$bg = imagecolorallocate($im, 238, 238, 238);
$textcolor = imagecolorallocate($im, 0, 0, 255);

imagestring($im, 5, 10, 4, $captcha_img_value, $textcolor);


// LINES
imageline($im, 5, 5, 100, 7, imagecolorallocate($im, 158, 216, 255));

imageline($im, 2, 23, 100, 18, imagecolorallocate($im, 157, 213, 231));


// DASHED LINES
$w   = imagecolorallocate($im, 255, 255, 255);
$red = imagecolorallocate($im, 55, 110, 235);
$style = array($red, $red,  $w, $w);
imagesetstyle($im, $style);


imageline($im, 1, 3, 35, 100, IMG_COLOR_STYLED);
imageline($im, 60, 0, 90, 100, IMG_COLOR_STYLED);
imageline($im, 85, 0, 110, 100, IMG_COLOR_STYLED);


// Output the image
header('Content-type: image/png');


imagepng($im);
imagedestroy($im);
?>