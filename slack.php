<?php
require_once("includes/name_that_color.php");
require_once("includes/url_helpers.php");
$ntc = NameThatColor::Instance();

$url_path     = URLHelpers::folder_path( $_SERVER );
$icon_url     = $url_path . 'includes/ntc.json';
$preview_url  = $url_path . 'preview.php';

$command      = ( !empty( $_POST['command'] ) ) ? $_POST['command'] : "";
$channel_id   = ( !empty( $_POST['channel_id'] ) ) ? $_POST['channel_id'] : "";
$channel_name = ( !empty( $_POST['channel_id'] ) ) ? $_POST['channel_id'] : "";
$user_name    = ( !empty( $_POST['user_name'] ) ) ? $_POST['user_name'] : "";
$user_id      = ( !empty( $_POST['user_id'] ) ) ? $_POST['user_id'] : "";
$team_id      = ( !empty( $_POST['team_id'] ) ) ? $_POST['team_id'] : "";
$response_url = ( !empty( $_POST['response_url'] ) ) ? $_POST['response_url'] : "";
$text         = ( !empty( $_REQUEST['text'] ) ) ? trim( $_REQUEST['text'] ) : "";

$attributes   = array_filter( explode( ' ', $text ) );

$flags = array();
$force_format = "hex";
while( !empty( $attributes[0] ) && strlen( $attributes[0] ) > 1 && $attributes[0][0] == '-' ) {
	$flag = array_shift( $attributes );
	if(substr($flag, 0, 3) == '-o=') {
		$force_format = substr($flag, 3);
	} elseif(substr($flag, 0, 8) == '-output=') {
		$force_format = substr($flag, 8);
	} elseif( $flag[1] == '-' ) {
		switch( $flag ) {
			case '--css' :
				$flags[] = 'c';
				break;
			case '--ephemeral' :
				$flags[] = 'e';
				break;
			case '--less':
				$flags[] = 'l';
				break;
			case '--scss':
				$flags[] = 's';
				break;
			case '--normalize':
				$flags[] = 'n';
				break;
			case '--reverse':
				$flags[] = 'r';
				break;
			case '--validate':
				$flags[] = 'v';
				break;
			case '--help':
				$flags[] = 'h';
				break;
		}
	} else {
		$flag_str = substr( $flag, 1 );
		$flag_array = str_split( $flag_str );
		$flags = array_merge( $flags, $flag_array );
	}
}
$flags = array_unique( $flags );

if( in_array( 'h', $flags ) || !$text || $text == 'help') {
	$flags[]         = 'e'; // help is always ephemeral
	$message_array   = array();
	$message_array[] = "*Name That Color* by Greg Schoppe";
	$message_array[] = "Takes a set of hex colors and assigns them human-readable names for use in palettes, and to ease discussion of color.";
	$message_array[] = "Based on ntc.js by Chirag Mehta, ported to PHP with an improved color distance algorithm that utilizes the CIELAB color space.";
	$message_array[] = "";
	$message_array[] = "*USAGE*";
	$message_array[] = "";
	$message_array[] = "`/ntc -clsnv color1 color2`";
	$message_array[] = "";
	$message_array[] = "*OPTIONS*";
	$message_array[] = "";
	$message_array[] = "`-c` `--css` - Create CSS variable names from the hex colors provided";
	$message_array[] = "`-l` `--less` - Create LESS variable names from the hex colors provided";
	$message_array[] = "`-s` `--sass` - Create SASS variable names from the hex colors provided";
	$message_array[] = "`-n` `--normalize` - Normalize the hex colors to match the closest named tone exactly";
	$message_array[] = "`-o=` `--output=` - Convert all output into a specific colorspace.";
	$message_array[] = "`-r` `--reverse` - Returns the hex codes closest matching the provided comma-separated list of names";
	$message_array[] = "`-v` `--validate` - Generate a validation image, to see each color compared to its named counterpart";
	$message_array[] = "`-e` `--ephemeral` - Do not show the results to anyone else in the current channel";
	$message_array[] = "`-h` `--help` - Display this file";
	$message_array[] = "";
	$message_array[] = "*COLORS*";
	$message_array[] = "";
	$message_array[] = "Name that Color supports both 6 character and 3 character HEX color strings, with or without a leading `#` character";
	$message_array[] = "";
	$message_array[] = "*EXAMPLES*";
	$message_array[] = "";
	$message_array[] = "`/ntc -sv #5500FF #CCCCCC #FF6600` - Create a SASS palette from the three colors provided, and create a validation image.";
	$message_array[] = "`/ntc -l 0FF CCC FF6` - Create a LESS palette from the three colors provided.";
	$message_array[] = "`/ntc -cn #0FF #CCC #FF6` - Replace the three colors provided with their closest named tones, and create a CSS palette.";
	$message_array[] = "`/ntc -rs astronaut, red, fern green` - Create a SASS palette from the three color names provided.";
	$message_array[] = "`/ntc -rs -o=rgb astronaut, red, fern green` - Create a SASS palette from the three color names provided, formatted as RGB colors.";
	$message_array[] = "";
	$message_array[] = "*FUTURE ENHANCEMENTS*";
	$message_array[] = "";
	$message_array[] = "* Web Interface";
	$message_array[] = "* Atom Integration";

	$message_text    = implode( "\n", $message_array );
} elseif( !count( $attributes ) ) {
	$message_text = "INVALID COMMAND: you must include at least one color";
} else {
	if( in_array( 'e', $flags ) ) {
		$message_text   = "you sent the command_ : `/ntc " . $text . "`\n";
	}
	// now we do more involved attribute parsing
	$attributes = implode( ' ', $attributes );
	if( in_array( 'r', $flags ) ) {
		$attributes = explode( ',', $attributes );
	} else {
		$attributes = $ntc->parse_colors( $attributes );
	}
	$message_blocks = array();
	$block_header   = '```';
	$block_header  .= "/* Color palette generated by Name That Color slack integration */\n";
	$block_footer   = "```\n";
	if( in_array( 'c', $flags ) ) {
		$message_blocks['c'] = ":root {\n";
	}
	if( in_array( 'l', $flags ) ) {
		$message_blocks['l'] = "";
	}
	if( in_array( 's', $flags ) ) {
		$message_blocks['s'] = "";
	}
	$verification_colors = array();
	foreach( $attributes as $attribute ) {
		if( !in_array( 'r', $flags ) ) {
			$verification_colors[] = $attribute['object']->toHex();
			if( $force_format == 'input' ) {
				$format = $attribute['type'];
			} else {
				$format = $force_format;
			}
			$color = $ntc->name_color( $attribute['object'] );
			if( in_array( 'n', $flags ) ) {
				$output = $ntc->format_color( $color[0], $format );
			} else {
				$input  = $ntc->format_color( $attribute['object'], $attribute['type'] );
				$output = $ntc->format_color( $attribute['object'], $format );
			}
			$message_text .= "The color " . $input;
			if( $input != $output ) {
				$message_text .= " ( " . $output . " in " . $format . ")";
			}
			$message_text .= " should be named *" . $color[1] . "*\n";
		} else {
			if( $force_format != "input" ) {
				$format = $force_format;
			} else {
				$format = 'hex';
			}
			$color  = $ntc->color_from_name( $attribute );
			$output = $ntc->format_color( $color[0], $format );
			$verification_colors[] =  $color[0]->toHex();
			$message_text .= "The color name *" . $color[1] . "* has the tone #" . $output . "\n";
		}

		if( in_array( 'c', $flags ) ) {
			$message_blocks['c'] .= "  --palette-" . $ntc->sanitize_color_name( $color[1], '-' ) . " : " . $output . "; /* " . $color[1] . " */\n";
		}
		if( in_array( 's', $flags ) ) {
			$message_blocks['s'] .= '$palette_' . $ntc->sanitize_color_name( $color[1] ) . " : " . $output . "; /* " . $color[1] . " */\n";
		}
		if( in_array( 'l', $flags ) ) {
			$message_blocks['l'] .= '@palette_' . $ntc->sanitize_color_name( $color[1] ) . " :#" . $output . "; /* " . $color[1] . " */\n";
		}
	}
	if( in_array( 'c', $flags ) ) {
		$message_blocks['c'] .= "}\n";
	}

	foreach( $message_blocks as $flag => $string ) {
		$message_text .= "\n---\n";
		if( $flag == 'c' ) {
			$message_text .= "\n*CSS Variables*\n";
		} elseif( $flag == 'l' ) {
			$message_text .= "\n*LESS Variables*\n";
		} elseif( $flag == 's' ) {
			$message_text .= "\n*SCSS Variables*\n";
		}
		$message_text .= $block_header;
		$message_text .= $string;
		$message_text .= $block_footer;
	}
}

if( $user_id ) {
	$data = array(
		"response_type" => "in_channel",
		"username"      => "Name That Color",
		"text"          => $message_text,
		"mrkdwn"        => true,
		"icon_url"      => $icon_url
	);
	if( in_array( 'e', $flags ) ) {
		$data['response_type'] = 'ephemeral';
	}
	if( in_array( 'v', $flags ) ) {
		$url = $preview_url . '?colors=' . urlencode( implode( ',', $verification_colors ) );
		$data['attachments'] = array(
			array(
				"fallback"   => 'See a comparison between the colors provided and reference swatches: ' . $url,
				"title"      => "Comparison to Reference Colors",
				"title_link" => $url,
				"text"       => "the top color is the tone you requested. the bottom color is the reference tone that matches the name provided.",
				"image_url"  => $url,
			)
		);
	}
	header('Content-Type: application/json');
	$json_string = json_encode($data);
	echo $json_string;
} else {
	echo $message_text;
}
