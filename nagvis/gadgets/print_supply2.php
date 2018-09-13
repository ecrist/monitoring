<?php
/* Print Supply Gadget
 * Released under BSD License, 2009
 * Eric F Crist (ecrist@secure-computing.net)
 * Secure Computing Networks
 * $Id: print_supply.php 76 2009-07-27 15:52:40Z ecrist $
 * $HeadURL: https://www.secure-computing.net/svn/trunk/nagvis/gadgets/print_supply.php $
 *
*/

// Bare Minimum Perfdata
//$sDummyPerfdata = 'temp=14F;80;90;0;100';

// Standard, Random Perfdata
//$sDummyPerfdata = 'temp=152.8F;55:93;50:98;0;100';

// Ideal Perfdata (Taken from HP LaserJet 2840)
//$sDummyPerfdata = "'Cyan Cartridge'=47%;10;5;0;100 'Magenta Cartridge'=4%;10;5;0;100 'Black Cartridge'=40%;10;5;0;100 'Yellow Cartridge'=78%;10;5;0;100 'Imaging Drum'=7%;10;5;0;100";

//$sDummyPerfdata = "Cyan Cartridge'=0%;10;5;0;100 'Magenta Cartridge'=0%;10;5;0;100 'Black Cartridge'=10%;10;5;0;100 'Yellow Cartridge'=78%;10;5;0;100 'Imaging Drum'=58%;10;5;0;100";
//$sDummyPerfdata = "'Black Cartridge HP CB436A'=74%;10;5;0;100";

// Taken from Sharp MX-2300
$sDummyPerfdata = "'Cyan Toner'=100%;10;5;0;100  'Black Photoconductive Drum'=43%;10;5;0;100  'Magenta Toner'=100%;10;5;0;100  'Black Toner'=75%;10;5;0;100  'Black Developer'=43%;10;5;0;100  'Cyan Photoconductive Drum'=67%;10;5;0;100  'Magenta Developer'=67%;10;5;0;100  'Magenta Photoconductive Drum'=67%;10;5;0;100  'Waste Toner'=0%;10;5;0;100  'Yellow Toner'=25%;10;5;0;100  'Yellow Developer'=67%;10;5;0;100  'Cyan Developer'=67%;10;5;0;100  'Yellow Photoconductive Drum'=67%;10;5;0;100";

// This is an example perfdata from Check_MKs printer_supply check
//$sDummyPerfdata = "pages=90.0;20.0;10.0;0;100.0";

// Load gadget core functions
require('./gadgets_core.php');

$font = "./print_supply/font.ttf";
sort($aPerfdata);

// Get the height and width of the final image
$column_width = 25;
$columns      = count($aPerfdata);
$width        = 2+ ($column_width * $columns);
//$width      = 75;
$height       = 114;

// Set the amount of space between each column
$padding = 2;

// Generate the image variables
$im           = imagecreate($width,$height);
$oBackground  = imagecolorallocate($im, 122, 23, 211);
$white        = imagecolorallocate ($im,0xff,0xff,0xff);
$black        = imagecolorallocate ($im,0x00,0x00,0x00);
$black_lite   = imagecolorallocate ($im,0x7f,0x7f,0x7f);
$black_dark   = $black;
$black_inv    = $white;
$gray         = imagecolorallocate ($im,0xcc,0xcc,0xcc);
$gray_lite    = imagecolorallocate ($im,0xee,0xee,0xee);
$gray_dark    = imagecolorallocate ($im,0x7f,0x7f,0x7f);
$gray_inv     = $black; 
$magenta      = imagecolorallocate ($im,0xff,0x00,0xff);
$magenta_lite = imagecolorallocate ($im,0xee,0x82,0xee);
$magenta_dark = imagecolorallocate ($im,0xcd,0x00,0xcd);
$magenta_inv  = $black;
$cyan_lite    = imagecolorallocate ($im,0xe0,0xff,0xff);
$cyan         = imagecolorallocate ($im,0x00,0xff,0xff);
$cyan_dark    = imagecolorallocate ($im,0x00,0xcd,0xcd);
$cyan_inv     = $black;
$yellow_lite  = imagecolorallocate ($im,0xff,0xff,0xe0);
$yellow       = imagecolorallocate ($im,0xff,0xff,0x00);
$yellow_dark  = imagecolorallocate ($im,0xee,0xee,0x00);
$yellow_inv   = $black;
$color_warn   = imagecolorallocate ($im,0xff,0xa5,0x00);
$color_crit   = imagecolorallocate ($im,0xff,0x00,0x00);

// Fill in the background of the image
imagefilledrectangle($im,0,0,$width,$height,$black);
imagefilledrectangle($im,1,1,$width-2,$height-2,$oBackground);
// Calculate Max
$maxv = 0;
for ($i=0;$i<$columns;$i++){
        if ($aPerfdata[$i]['max'] > $maxv){
    	    $maxv = $aPerfdata[$i]['max'];
        }
}

// Now plot each column
// Set error counter for message.
$errs = 0;
for($i=0;$i<$columns;$i++) {
        $nameMatch = $aPerfdata[$i]['label'] . ' ' . $_GET['name2'];

        // Check_MKs printer_supply check has only one perfdata value called
        // pages. Use the service name for these objects
        if($aPerfdata[$i]['label'] == 'pages')
            $label = $_GET['name2'];
        else
            $label = $aPerfdata[$i]['label'];

	// walking the array, parse color, if we can
	// otherwise, set all to grey.
	if (preg_match('/magenta/i', $nameMatch) 
		OR preg_match('/\(M\)/i', $nameMatch)
		OR preg_match('/purpur/i', $nameMatch)){
		$color = $magenta;
		$colord = $magenta_dark;
		$colorl = $magenta_lite;
		$colori = $magenta_inv;
	} elseif (preg_match('/yellow/i', $nameMatch) 
		OR preg_match('/\(Y\)/i', $nameMatch)
		OR preg_match('/gul/i',   $nameMatch)
		OR preg_match('/gelb/i',  $nameMatch)){
		$color = $yellow;
		$colord = $yellow_dark;
		$colorl = $yellow_lite;
		$colori = $yellow_inv;
	} elseif (preg_match('/cyan/i',   $nameMatch)
                OR preg_match('/\(C\)/i', $nameMatch)){
		$color = $cyan;
		$colord = $cyan_dark;
		$colorl = $cyan_lite;
		$colori = $cyan_inv;
	} elseif (preg_match('/black/i',    $nameMatch) 
		OR preg_match('/\(K\)/i',   $nameMatch)
		OR preg_match('/svart/i',   $nameMatch)
		OR preg_match('/schwarz/i', $nameMatch)){
		$color = $black;
		$colord = $black_dark;
		$colorl = $black_lite;
		$colori = $black_inv;
	} else {
		$color = $gray;
		$colord = $gray_dark;
		$colorl = $gray_lite;
		$colori = $gray_inv;
	}
	$column_height = (($height-14) / 100) * (( $aPerfdata[$i]['value'] / $maxv) *100);

	$x1 = 2 +$i*$column_width;
	$y1 = ($height-$column_height);
	$x2 = (($i+1)*$column_width)-$padding;
	$y2 = $height - 2;
	// See if this is a 'WASTE' supply, and invert values
	if (preg_match("/waste/i", $nameMatch) OR preg_match('/resttoner/', $nameMatch)){
		// Draw background color for warning and critical.
		if ($aPerfdata[$i]['value'] >= $aPerfdata[$i]['critical']){
			// draw crit rectangle
			$x1e = 1 + $i * $column_width;
			$y1e = 1;
			imagefilledrectangle($im,$x1e,$y1e,$x2,$y2,$color_crit);
			imagestringup($im,5,$x1e+4,$y2-31,"CRITICAL",$colori);
		} elseif ($aPerfdata[$i]['value'] >= $aPerfdata[$i]['warning']){
			// draw warn rectangle
			$x1e = 1 + $i * $column_width;
			$y1e = 1;
			imagefilledrectangle($im,$x1e,$y1e,$x2,$y2,$color_warn);
			imagestringup($im,5,$x1e+4,$y2-35,"WARNING",$colori);
		}
	} else {
		// Draw background color for warning and critical.
		if ($aPerfdata[$i]['value'] <= $aPerfdata[$i]['critical']){
			// draw crit rectangle
			$x1e = 1 + $i * $column_width;
			$y1e = 1;
			imagefilledrectangle($im,$x1e,$y1e,$x2,$y2,$color_crit);
			imagestringup($im,5,$x1e+4,$y2-31,"CRITICAL",$colori);
		} elseif ($aPerfdata[$i]['value'] <= $aPerfdata[$i]['warning']){
			// draw warn rectangle
			$x1e = 1 + $i * $column_width;
			$y1e = 1;
			imagefilledrectangle($im,$x1e,$y1e,$x2,$y2,$color_warn);
			imagestringup($im,5,$x1e+4,$y2-35,"WARNING",$colori);
		}
	}
	

	imagefilledrectangle($im,$x1,$y1,$x2,$y2,$color);

	// This part is just for 3D effect
	imageline($im,$x1,$y1,$x1,$y2,$colorl);
	imageline($im,$x1,$y2,$x2,$y2,$colorl);
	imageline($im,$x2,$y1,$x2,$y2,$colord);

        // Use percentage values or calculate them        
        if($aPerfdata[$i]['uom'] == '%')
            $value_text = $aPerfdata[$i]['value'] . $aPerfdata[$i]['uom'];
        else
            $value_text = ($aPerfdata[$i]['value'] * 100 / $aPerfdata[$i]['max']) . '%';

	imagestring($im,2,$x1,2,$value_text,$black);
	$repl_pat = array("/Cartridge/", "/Photoconductive/", "/Developer/", "/Supply/");
	$repl_str = array("", "", "Dvlpr");
	if (preg_match('/([a-zA-Z\s]+)/i', $label, $matches)){
		$value_text = preg_replace($repl_pat, $repl_str, $matches[1]);
	} else {
		$value_text = $label;
	}
	imagestringup($im,2,$x1+5,$y2-4, substr($value_text,0, 12), $colori);
}

// Send the PNG header information. Replace for JPEG or GIF or whatever
imagecolortransparent($im, $oBackground);
header ("Content-type: image/png");
imagepng($im);
?>
