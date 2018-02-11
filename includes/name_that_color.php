<?php
require_once('color.php');

class NameThatColor {
	private $colors;

	public static function Instance() {
		static $instance = null;
		if ($instance === null) {
			$instance = new self();
		}
		return $instance;
	}

	private function __construct() {
		$this->init();
	}
	// Public functions
	public function name_color( $color ) {
		if( $color === false ) {
			return array( false, "Invalid Color", false );
		}
		$match = new Color();

		$best_index = -1;
		$best_dist  = -1;

		for( $i = 0; $i < count( $this->colors ); $i++) {
			if( $color->toHex() == $this->colors[$i][0] ) {
				return array( $color, $this->colors[$i][1], true );
			}
			$distance = $color->getDistanceLabFrom( $this->colors[$i][2] );
			if( $best_dist < 0 || $distance < $best_dist ) {
				$best_dist  = $distance;
				$best_index = $i;
			}
		}
		if( $best_index < 0 ) {
			return array( false, "Invalid Color", false );
		}
		$best = $this->colors[$best_index];
		$match->fromHex( $best[0] );
		return array( $match , $best[1], false );
	}

	public function color_from_name( $raw ) {
		$name = $this->sanitize_color_name( $raw );

		$match = new Color();
		$best_index = -1;
		$best_dist  = -1;

		for( $i = 0; $i < count( $this->colors ); $i++) {
			if( $name == $this->colors[$i][3] ) {
				$match->fromHex( $this->colors[$i][0] );
				return array( $match, $this->colors[$i][1], true );
			}
			$distance = levenshtein( $name, $color_name );
			if( $best_dist < 0 || $distance < $best_dist ) {
				$best_dist  = $distance;
				$best_index = $i;
			}
		}
		if( $best_index < 0 ) {
			return array( false, "Invalid Name", false );
		}
		$best = $this->colors[$best_index];
		$match->fromHex( $best[0] );
		return array( $match, $best[1], false );
	}

	public function sanitize_color_name( $raw, $separator = "_" ) {
		$color = trim( strtolower( $raw ) );
		$color = preg_replace('/[^a-z\d]/', $separator, $color);
		return $color;
	}

	public function normalize_hex( $raw ) {
		$hex = trim( strtoupper( $raw ) );
		$hex = ltrim( $hex, '#' );
		if(strlen( $hex ) != 3 && strlen( $hex ) != 6 ) {
			return false;
		}
		if( !ctype_xdigit( $hex ) ) {
			return false;
		}
		if( strlen( $hex ) == 3 ) {
			$hex = implode( '', array_map( array( $this, 'double_char' ), str_split( $hex ) ) );
		}
		return $hex;
	}

	public function format_color( $color, $format='hex' ) {
		$format = strtolower( $format );
		switch( $format ) {
			case 'hex':
				return '#' . $color->toHex();
			case 'rgb':
				$rgb = $color->toRgb();
				return 'rgb(' . implode( ',', $rgb ) . ')';
			case 'hsl':
				$hsl = $color->toHsl();
				$hsl['sat'] *= 100;
				$hsl['lightness'] *= 100;
				return 'hsl(' . $hsl['hue'] . ',' . $hsl['sat'] . '%,' . $hsl['lightness'] . '%)';
			case 'cmyk':
				$cmyk = $color->toCmyk();
				$cmyk = array_map(function( $a ) {
					return ( $a * 100 ) . '%';
				}, $cmyk);
				return 'cmyk(' . implode( ',', $cmyk ) . ')';
		}
		return false;
	}

	public function parse_colors( $str ) {
		$str = strtolower( $str ) . ' ';
		$regex_parts = array(
			'rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\)', // rgb(127,255,0)
			'hsl\(\s*\d{1,3}(?:.\d+)?\s*,\s*\d{1,3}(?:.\d+)?%\s*,\s*\d{1,3}(?:.\d+)?%\s*\)', // hsl(359,0%,0%)
			'cmyk\(\s*\d{1,3}(?:.\d+)?%\s*,\s*\d{1,3}(?:.\d+)?%\s*,\s*\d{1,3}(?:.\d+)?%\s*,\s*\d{1,3}(?:.\d+)?%\s*\)', // cmyk(50%,0%,0%,100%)
			// 'lab\(\s*\d{1,3}(?:.\d+)?\s*,\s*-?\d{1,3}(?:.\d+)?\s*,\s*-?\d{1,3}(?:.\d+)?\s*\)', // lab(50,-120,100)
			'#?[a-f\d]{6}', // #fff
			'#?[a-f\d]{3}' // #ff6600
		);
		$regex = '/(' . implode( '\s|', $regex_parts ) . ')/';
		$matches = array();
		preg_match_all( $regex, $str, $matches );
		$colors = array();
		if( !empty( $matches[1] ) ) {
			foreach( $matches[1] as $match ) {
				$match = trim( $match );
				$color = false;
				switch( substr( $match, 0, 3 ) ) {
					case "rgb":
						$color = $this->parse_rgb( $match );
						break;
					case "hsl":
						$color = $this->parse_hsl( $match );
						break;
					case "cmy":
						$color = $this->parse_cmyk( $match );
						break;
					/*
					case "lab":
						$color = $this->parse_lab( $match );
						break;
					*/
					default :
						$color = $this->parse_hex( $match );
				}
				$colors[] = $color;
			}
		}
		return $colors;
	}

	public function get_visual_luma( $color ) {
		$color = $color->toRgbInt();
		$color = array_map( function( $a ) {
			return $a / 255;
		}, $color );
		$gamma = 2.2;
		$luma  = pow( sqrt(
			pow( $color['red'  ], 2 ) * 0.299 +
			pow( $color['green'], 2 ) * 0.587 +
			pow( $color['blue' ], 2 ) * 0.114
		), $gamma );

		return $luma;
}

	// Private functions
	private function init() {
		$dir = dirname( __FILE__ );
		$color_data = file_get_contents( $dir."/color_data.json" );
		$this->colors = json_decode( $color_data, true );
		for( $i = 0; $i < count( $this->colors ); $i++ ) {
			$color = new Color();
			$color->fromHex( $this->colors[$i][0] );
			$sanitized_name = $this->sanitize_color_name( $this->colors[$i][1] );
			array_push( $this->colors[$i], $color, $sanitized_name );
		}
	}

	private function parse_rgb( $str ) {
		$str = strtoupper( trim( $str, ' rgb()' ) );
		$parts = array_map( function( $a ) {
			return intval( trim( $a ) );
		}, explode( ',', $str ) );
		$parts = array_filter( $parts, function( $a ) {
			return $a <= 255 && $a >= 0;
		} );
		if( count( $parts ) !== 3 ) {
			return false;
		}
		$color = new Color();
		$color->fromRgbInt( $parts[0], $parts[1], $parts[2] );
		return array(
			'type'   => 'rgb',
			'string' => 'rgb(' . implode( ',', $parts ) . ')',
			'object' => $color
		);
	}

	private function parse_hsl( $str ) {
		$str = strtoupper( trim( $str, ' hsl()' ) );
		$parts = array_map( function( $a ) {
			return floatval( trim( $a, ' %' ) );
		}, explode( ',', $str ) );
		if( count( $parts ) !== 3 ) {
			return false;
		}
		if( $parts[0] < 0 || $parts[0] > 360 ) {
			return false;
		}
		if( $parts[1] < 0 || $parts[1] > 100 ) {
			return false;
		}
		if( $parts[2] < 0 || $parts[2] > 100 ) {
			return false;
		}
		$color = new Color();
		$color->fromHsl( $parts[0], $parts[1]/100, $parts[2]/100 );
		return array(
			'type'   => 'hex',
			'string' => 'hsl(' . implode( '%,', $parts ) . '%)',
			'object' => $color
		);
	}

	private function parse_cmyk( $str ) {
		$str = strtoupper( trim( $str, ' cmyk()' ) );
		$parts = array_map( function( $a ) {
			return floatval( trim( $a, ' %' ) );
		}, explode( ',', $str ) );
		$parts = array_filter( $parts, function( $a ) {
			return $a <= 100 && $a >= 0;
		} );
		if( count( $parts ) !== 4 ) {
			return false;
		}
		$color = new Color();
		$color->fromCmyk( $parts[0]/100, $parts[1]/100, $parts[2]/100, $parts[3]/100 );
		return array(
			'type'   => 'cmyk',
			'string' => 'cmyk(' . implode( '%,', $parts ) . '%)',
			'object' => $color
		);
	}

	private function parse_hex( $str ) {
		$str = strtoupper( trim( $str, ' #' ) );
		if( strlen( $str == 3 ) ) {
			$str = implode( '', array_map( array( $this, 'double_char' ), str_split( $hex ) ) );
		}
		$color = new Color();
		$color->fromHex( $str );
		return array(
			'type'   => 'hex',
			'string' => '#' . $str,
			'object' => $color
		);
	}

	private function double_char( $a ) {
		$a .= $a;
		return $a;
	}

}
NameThatColor::Instance();
