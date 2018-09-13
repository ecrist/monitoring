<?php
/* Thermometer gadget for NagVis
 * A considerable amount of code and imagery was borrowed from
 * PHP Fundraising Thermometer (http://sourceforge.net/projects/phpfundthermo/)
 * Images were modified to support normal, critical, and warning states.
 * Credit to Sairam Suresh sai1138@yahoo.com / www.entropyfarm.org
 *
 * Released under the BSD license, 2009
 * Copyright Eric F Crist (ecrist@secure-computing.net)
 * Secure Computing Networks (http://secure-computing.net) 
 * $Id: thermo.php 132 2010-02-05 21:10:09Z ecrist $
 * $HeadURL: https://www.secure-computing.net/svn/trunk/nagvis/gadgets/thermo/thermo.php $
*/

//$sDummyPerfdata = 'temp=14F;80;90;0;100';
$sDummyPerfdata = 'temp=152.8F;55:93;50:98;0;100';
// Load gadget core functions
require('./gadgets_core.php');


header("Content-Type: image/png"); 
// echo test data to a temp file
$current = $aPerfdata[0]['value'];
$warn = $aPerfdata[0]['warning'];
$warn_min = $aPerfdata[0]['warning_min'];
$warn_max = $aPerfdata[0]['warning_max'];
$crit = $aPerfdata[0]['critical'];
$crit_min = $aPerfdata[0]['critical_min'];
$crit_max = $aPerfdata[0]['critical_max'];
$uom = $aPerfdata[0]['uom'];
$min = $aPerfdata[0]['critical_min'] - 5;
$max = $aPerfdata[0]['critical_max'] + 5;
if (isset($aPerfdata[0]['min'])){
	$min = $aPerfdata[0]['min'];
} elseif (isset($aPerfdata[0]['critical_min'])){
	$min = $aPerfdata[0]['critical_min'] - 5;
} else {
	$min = 0;
}
if (isset($aPerfdata[0]['max'])){
	$max = $aPerfdata[0]['max'];
} elseif (isset($aPerfdata['critical_max'])){
	$max = $aPerfdata[0]['critical_max'] - 5;
} else {
	$max = 100;
}

if ((!empty($aPerfdata[0]['critical_min'])) AND
    (!empty($aPerfdata[0]['critical_max'])) AND
    (!empty($aPerfdata[0]['warning_min'])) AND
    (!empty($aPerfdata[0]['warning_max']))){
	if (($current >= $crit_max) OR ($current <= $crit_min)){
		$filen = "";
	} elseif (($current >= $warn_max) OR ($current <= $warn_min)){
		$filen = "_y";
	} else {
		$filen = "_g";
	}
} else {
	if ($current >= $crit){
		$filen = "";
	} elseif ($current >= $warn){
		$filen = "_y";
	} else {
		$filen = "_g";
	}
}

$font = "./thermo/font.ttf";
$unit = 36; // ascii 36 = $
$t_unit = "";
$t_max = $max;
$t_current = $current;

$finalimagewidth = 56;
$finalimage = imagecreateTrueColor($finalimagewidth,400);

$white = imagecolorallocate ($finalimage, 255, 255, 255);
$black = imagecolorallocate ($finalimage, 0, 0, 0);
$red = imagecolorallocate ($finalimage, 255, 0, 0);

imagefill($finalimage,0,0,$white);
ImageAlphaBlending($finalimage, true); 

$thermImage = imagecreatefrompng("./thermo/therm$filen.png");
imagealphablending($thermImage, false);
imagesavealpha($thermImage, true);
$tix = ImageSX($thermImage);
$tiy = ImageSY($thermImage);
ImageCopy($finalimage,$thermImage,0,0,0,0,$tix,$tiy);

/*
  thermbar pic courtesy http://www.rosiehardman.com/
*/
$thermbarImage = ImageCreateFromjpeg("./thermo/thermbar$filen.jpg"); 
$barW = ImageSX($thermbarImage); 
$barH = ImageSY($thermbarImage); 


$xpos = 5;
$ypos = 327;
$ydelta = 15;
$fsize = 7;


// Set number of $ybars to use, calculated as a factor of current / max.
// first, figure out the total range.
$range = $t_max - $min;
$ybars = round(($t_current -$min)/ ($range / 22));

// Draw each ybar (filled red bar) in successive shifts of $ydelta.
while ($ybars > 1) {
    ImageCopy($finalimage, $thermbarImage, $xpos, $ypos, 0, 0, $barW, $barH); 
    $ypos = $ypos - $ydelta;
    $ybars--;
}

if ($t_current == $t_max) {
    ImageCopy($finalimage, $thermbarImage, $xpos, $ypos, 0, 0, $barW, $barH); 
    $ypos -= $ydelta;
} 

if ($t_current > $t_max) {
    $burstImg = ImageCreateFromjpeg('./thermo/burst.jpg');
    $burstW = ImageSX($burstImg);
    $burstH = ImageSY($burstImg);
    ImageCopy($finalimage, $burstImg, 0,0,0,0,$burstW, $burstH);
}
// If there's a truetype font available, use it
if ($font && (file_exists($font))) {
//    imagettftext ($finalimage, $fsize, 0, 41, 345, $black, $font,$t_unit."0");                 // Write the Zero
    if (!isset($min)){
    	$min = 0;
    }
    $diff = ($t_max - $min)/22;
    $x = 30;
    $working = $t_max;
    while ($working >= $min){
    	$fstmp = $fsize;
    	$wrktxt = round($working);
	if (strlen($wrktxt) > 2){
		$fstmp = 6;
	}
	imagettftext ($finalimage, $fstmp, 0, 41, $x, $black, $font, $wrktxt);
	$working = $working - $diff;
	$x = $x + 15;
    }
    imagettftext ($finalimage, $fsize+2, 270, 4, 100, $black, $font, $t_unit."Current: $t_current$uom");   // Write the current
    
    if ($t_current > $t_max) {
        imagettftext ($finalimage, $fsize+1, 0, 60, $fsize, $black, $font, $t_unit."$t_current!!"); // Current > Max
    } elseif($t_current != 0) {
        if ($t_current == $t_max) {
            imagettftext ($finalimage, $fsize, 0, 60, 10+(2*$fsize), $red, $font, $t_unit."$t_max!");  // Current = Max
        } else {
            if (round($t_current/$t_max) == 1) {
                $ypos += 2*$fsize;
            }
            imagettftext ($finalimage, $fsize, 0, 60, ($t_current > 0) ? ($ypos+$fsize) : ($ypos+(4*$fsize)), ($t_current > 0) ? $black : $red, $font, $t_unit."$t_current");  // Current < Max
        }
    }
}


Imagepng($finalimage);
Imagedestroy($finalimage);
Imagedestroy($thermImage);
Imagedestroy($thermbarImage);
?> 
