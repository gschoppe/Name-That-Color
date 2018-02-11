<?php
require_once("includes/name_that_color.php");
$ntc = NameThatColor::Instance();

// config
$block_dimensions = array(150, 100);
$margin = 5;
$display = 'vertical';

// given a hex value, return a pair with the user-submitted color and the closest named color
function get_pair_from_hex( $hex ) {
	$ntc = NameThatColor::Instance();
	$a = new Color();
	$a->fromHex( $hex );
	$canonical = $ntc->name_color( $a );
	$b = $canonical[0];
	return array(
		array( $a, '[user-sumbmitted]', '#' . $a->toHex() ),
		array( $b, '#' . $canonical[0]->toHex(), $canonical[1] )
	);
}

// given a hex value, return a Color object
function get_color_from_hex( $hex ) {
	$color = new Color();
	$color->fromHex( $hex );
	return $color;
}

// given a hex value, return a Color object that will strongly contrast with it
function get_text_color_from_hex( $hex ) {
	$color = get_color_from_hex( $hex );
	$text_color = rgb_best_contrast( $color );
	return $text_color;
}

// generate fg and shadow/stroke colors for arbitrary color
function make_text_colors( $im, $color ) {
	$ntc   = NameThatColor::Instance();
	$white = imagecolorallocate( $im, 255, 255, 255 );
	$black = imagecolorallocate( $im, 0, 0, 0 );
	$luma  = $ntc->get_visual_luma( $color );
	if( $luma > 0.5 ) {
		return array(
			'text'       => $black,
			'shadow'     => $white,
		);
	}
	return array(
		'text'       => $white,
		'shadow'     => $black,
	);
}

// print text on solid background in readable color
function print_image_text( $im, $x, $y, $color, $text ) {
	// set up our color variables
	$colors = make_text_colors( $im, $color );
	// finally draw everything
	imagestring( $im, 2, $x, $y, $text, $colors['shadow'] );
	imagestring( $im, 2, $x + 2, $y, $text, $colors['shadow'] );
	imagestring( $im, 2, $x, $y + 2, $text, $colors['shadow'] );
	imagestring( $im, 2, $x + 2, $y + 2, $text, $colors['shadow'] );
	imagestring( $im, 2, $x+1, $y+1, $text, $colors['text'] );
}

$colors = "";
if( !empty( $_GET['colors'] ) ) {
	$colors = $_GET['colors'];
}
$colors = explode( ',', $colors );
// from each color, make a pair with the user-submitted color and the closest named color
$colors = array_map( "get_pair_from_hex", $colors );
$colors = array_filter( $colors );

// figure out grid size
$columns = ceil( sqrt( count( $colors ) ) );
if( !empty( $_GET['columns'] ) ) {
	$columns = abs( intval( $_GET['columns'] ) );
}
$rows = ceil( count( $colors ) / $columns );

// make blank canvas the size of the grid
$imgx  = $columns * ( $block_dimensions[0] + $margin ) + $margin;
$imgy  = $rows * ( $block_dimensions[1] + $margin ) + $margin;
$im    = imagecreatetruecolor( $imgx, $imgy );
$white = imagecolorallocate( $im, 255, 255, 255 );
imagefill( $im, 0, 0, $white );

// loop through the array of colors
for( $i = 0; $i < count( $colors ); $i++ ) {
	// get index in cols/rows
	$x_block = $i % $columns;
	$y_block = floor( $i / $columns );

	// get starting pixel position of this grid block
	$x_origin = $margin + ( $x_block * ( $block_dimensions[0] + $margin ) );
	$y_origin = $margin + ( $y_block * ( $block_dimensions[1] + $margin ) );

	// draw each of the colors in this pair, within the grid
	$color_pair = $colors[$i];
	for( $j = 0; $j < count( $color_pair ); $j++ ) {
		// position this color block within grid square
		$x = $x_origin;
		$y = $y_origin;
		$w = $block_dimensions[0];
		$h = $block_dimensions[1];
		if( $display == 'horizontal') {
			$w  = $block_dimensions[0] / 2;
			$x += $j * $w;
		} else {
			$h  = $block_dimensions[1] / 2;
			$y += $j * $h;
		}
		// set up our color variables
		$color     = $color_pair[$j][0];
		$bg_color  = $color->toRgbInt();
		$bg_color  = imagecolorallocate( $im, $bg_color['red'], $bg_color['green'], $bg_color['blue'] );
		// set up the text for this color block
		$line1 = $color_pair[$j][1];
		$line2 = $color_pair[$j][2];
		// finally draw everything
		imagefilledrectangle( $im, $x, $y, $x+$w, $y+$h, $bg_color );
		print_image_text( $im, $x + $margin, $y + $margin, $color, $line1 );
		print_image_text( $im, $x + $margin, $y + $margin + 15, $color, $line2 );
	}
}
header("Content-type: image/png");
ImagePNG($im);
