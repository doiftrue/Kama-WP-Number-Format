<?php

namespace Kama\WP;

/**
 * @see    number_format_i18n()
 *
 * @version 2.0
 */
class Num_Format {

	/**
	 * Format number. Thousands become k: 23 000 > 23k.
	 *
	 * @param float|string $number
	 * @param int|string   $decimals  Optional. Precision of number of decimal places. Default 0.
	 *                                Specify string as 'decimal float_round_type': '3 flex', '2 smart', '3 fixed'
	 *                                to set float round function. {@see _process_unit}.
	 *
	 * @return string
	 */
	public static function human_k( $number, $decimals = 1 ): string {

		static $names;
		$names || $names = [ '', 'k', 'kk', 'kkk', 'kkkk', 'kkkkk' ];

		return self::_process_unit( $number, $decimals, $names, '%s' );
	}

	/**
	 * Format number. Example: 23 000 000 > 23M.
	 *
	 * @param float|string $number
	 * @param int|string   $decimals  Optional. Precision of number of decimal places. Default 0.
	 *                                Specify string as 'decimal float_round_type': '3 flex', '2 smart', '3 fixed'
	 *                                to set float round function. {@see _process_unit}.
	 *
	 * @return string
	 */
	public static function human_short( $number, $decimals = 1 ): string {

		static $names;
		$names || $names = [ '', 'K', 'M', 'B', 'T', 'Q' ];

		return self::_process_unit( $number, $decimals, $names, '%s' );
	}

	/**
	 * Convert big number to readable format.
	 *
	 * @param int|string $num       Original Number.
	 * @param int|string $decimals  Optional. Precision of number of decimal places. Default 0.
	 *                              Specify string as 'decimal float_round_type': '3 flex', '2 smart', '3 fixed'
	 *                              to set float round function. {@see _process_unit}.
	 *
	 * @return string
	 */
	public static function human_abbr( $number, $decimals = 1 ): string {

		static $names;
		$names || $names = [ '',
			__( 'тыс.', 'hl' ),
			__( 'млн.', 'hl' ),
			__( 'млрд.', 'hl' ),
			__( 'трлн.', 'hl' ),
			__( 'квдрлн.', 'hl' ),
		];

		return self::_process_unit( $number, $decimals, $names, ' %s' );
	}

	/**
	 *
	 * @param float|string $number    Original Number.
	 * @param int|string   $decimals  Optional. Precision of number of decimal places. Default 0.
	 *                                Specify string as 'decimal float_round_type': '3 flex', '2 smart', '3 fixed'
	 *                                to set float round function.
	 * @param array        $names
	 * @param string       $unit_patt Pattern to display units.
	 *
	 * @return string
	 */
	private static function _process_unit( $number, $decimals, array $names, string $unit_patt = '' ): string {

		[ $number, $depth ] = self::_unit_depth( $number );

		if( ! $number ){
			return '0';
		}

		[ $decimals, $floats_round_type ] = explode( ' ', $decimals ) + [ 2, '' ];

		$unit_suffix = $depth ? sprintf( $unit_patt, $names[ $depth ] ) : '';

		switch( $floats_round_type ){

			case 'fixed':
				return self::format_fixed( $number, $decimals ) . $unit_suffix;

			case 'smart':
				return self::format_smart( $number, $decimals ) . $unit_suffix;

			default:
				return self::format_flex( $number, $decimals ) . $unit_suffix;
		}

	}

	/**
	 * Convert big number to readable format.
	 * Can be used as root function. I.e. wrap it to your
	 * own function where pass $names and specify desired output.
	 *
	 * @param float|string $number  Original Number.
	 * @param int          $depth   Internal.
	 *
	 * @return array
	 */
	private static function _unit_depth( $number, int $depth = 0 ): array {

		if( $number >= 1000 ){
			return self::_unit_depth( $number / 1000, ++$depth );
		}

		return [ $number, $depth ];
	}

	/**
	 * Format number. Smart calculate zeros after dot and
	 * leave specified $show_decimals numbers of digits.
	 *
	 * Example:
	 * - 0.0000000123 > 0.000000012;
	 * - 0.0123 > 0.012;
	 * - 2.999951132432 > 2.999951;
	 *
	 * @param float|string $number
	 * @param int|null     $show_decimals
	 *
	 * @return string
	 */
	public static function format_smart( $number, int $show_decimals = 2 ): string {

		if( ! $number ){
			return '';
		}

		$decimals = $show_decimals;

		// use simple formats fo big numbers
		if( $number < 30 && $number > -30 ){

			$number = (float) $number;
			// increase $decimals for numbers like: n.000nnn | n.999nnn
			preg_match( "/\d+\.((?:0{2,}|9{2,})\d{1,$show_decimals})/", sprintf( '%.12f', $number ), $mm );
			if( $mm ){
				$decimals = strlen( $mm[1] );
			}
		}

		return self::format_flex( $number, $decimals );
	}

	/**
	 * Format number and trim ending zeros.
	 *
	 * @param float|string $number
	 * @param int          $decimals  Optional. Precision of number of decimal places. Default 0.
	 *
	 * @return string
	 * @see number_format_i18n()
	 */
	public static function format_flex( $number, int $decimals = 2 ): string {

		if( ! $number ){
			return '';
		}

		$number = self::format_fixed( $number, $decimals );

		// 38 020.00 > 38 020
		// 38 020.00100 > 38 020.001
		// 38 020 > 38 020
		$float_sign = $GLOBALS['wp_locale']->number_format['decimal_point'];
		if( strpos( $number, $float_sign ) ){
			$number = rtrim( $number, '0' );
			$number = rtrim( $number, $float_sign );
		}

		return $number;
	}

	/**
	 * Format number and always leave specified ending decimals.
	 *
	 * @param float|string $number
	 * @param int          $decimals  Optional. Precision of number of decimal places. Default 0.
	 *
	 * @return string
	 * @see number_format_i18n()
	 */
	public static function format_fixed( $number, int $decimals = 2 ): string {

		if( ! $number ){
			return '';
		}

		return number_format_i18n( $number, $decimals );
	}

}
