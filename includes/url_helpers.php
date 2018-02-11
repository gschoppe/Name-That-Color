<?php
class URLHelpers {
	public static function url_origin( $s, $use_forwarded_host = false ) {
		$ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		$sp       = strtolower( $s['SERVER_PROTOCOL'] );
		$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
		$port     = $s['SERVER_PORT'];
		$port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
		$host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
		$host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
		return $protocol . '://' . $host;
	}

	public static function full_url( $s, $use_forwarded_host = false ) {
		return self::url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
	}

	public static function folder_path( $s, $use_forwarded_host = false ) {
		$path_parts = explode( '?', $s['REQUEST_URI'] );
		$path = $path_parts[0];
		if( self::ends_with( $path, '.php' ) ) {
			$path = dirname( $path );
		}
		if( !self::ends_with( $path, '/' ) ) {
			$path .= '/';
		}
		return self::url_origin( $s, $use_forwarded_host ) . $path;
	}

	public static function ends_with($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}
}
