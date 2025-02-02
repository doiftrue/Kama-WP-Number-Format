<?php

namespace Kama\WP;

interface Num_Format_Interface {

	public function human_k( $number, $decimals = 1 ): string;
	public function human_short( $number, $decimals = 1 ): string;
	public function human_abbr( $number, $decimals = 1 ): string;

	public function smart( $number, int $show_decimals = 2 ): string;
	public function flex( $number, int $decimals = 2 ): string;
	public function fixed( $number, int $decimals = 2 ): string;
}

/**
 *
 * @see number_format_i18n()
 *
 * @version 3.3
 */
class Num_Format implements Num_Format_Interface {

	/**
	 * Format number. Thousands become k: 23 000 > 23k.
	 *
	 * @see Num_FormatTest::test__human_k()
	 *
	 * @param float|string $number
	 * @param int|string   $decimals  Optional. Precision of number of decimal places. Default 0.
	 *                                Specify string as 'decimal float_round_type': '3 flex', '2 smart', '3 fixed'
	 *                                to set float round function. {@see _human_unit}.
	 */
	public function human_k( $number, $decimals = 1 ): string {

		static $names;
		$names || $names = [ '', 'k', 'kk', 'kkk', 'kkkk', 'kkkkk' ];

		return $this->_human_unit( $number, $decimals, $names, '%s' );
	}

	/**
	 * Format number. Example: 23 000 000 > 23M.
	 *
	 * @see Num_FormatTest::test__human_short()
	 *
	 * @param float|string $number
	 * @param int|string   $decimals  Optional. Precision of number of decimal places. Default 0.
	 *                                Specify string as 'decimal float_round_type': '3 flex', '2 smart', '3 fixed'
	 *                                to set float round function. {@see _human_unit}.
	 */
	public function human_short( $number, $decimals = 1 ): string {
		static $names;
		$names || $names = [ '', 'K', 'M', 'B', 'T', 'Q' ];

		return $this->_human_unit( $number, $decimals, $names, '%s' );
	}

	/**
	 * Convert big number to readable format.
	 *
	 * @see Num_FormatTest::test__human_abbr()
	 *
	 * @param int|string $num       Original Number.
	 * @param int|string $decimals  Optional. Precision of number of decimal places. Default 0.
	 *                              Specify string as 'decimal float_round_type': '3 flex', '2 smart', '3 fixed'
	 *                              to set float round function. {@see _human_unit}.
	 */
	public function human_abbr( $number, $decimals = 1 ): string {

		static $names;
		$names || $names = [ '',
			__( 'тыс.', 'hl' ),    // ths.
			__( 'млн.', 'hl' ),    // mln.
			__( 'млрд.', 'hl' ),   // bln.
			__( 'трлн.', 'hl' ),   // Tn.
			__( 'квдрлн.', 'hl' ), // Qa.
		];

		return $this->_human_unit( $number, $decimals, $names, ' %s' );
	}

	/**
	 * @param float|string $number     Original Number.
	 * @param int|string   $decimals   Optional. Precision of number of decimal places. Default 0.
	 *                                 Specify string as 'decimal float_round_type': '3 flex', '2 smart', '3 fixed'
	 *                                 to set float round function.
	 * @param array        $names
	 * @param string       $unit_patt  Pattern to display units.
	 */
	private function _human_unit( $number, $decimals, array $names, string $unit_patt = '' ): string {

		[ $number, $depth ] = self::_human_unit_depth( $number );

		if( ! $number ){
			return '0';
		}

		[ $decimals, $floats_round_type ] = explode( ' ', $decimals ) + [ 2, '' ];

		$unit_suffix = $depth ? sprintf( $unit_patt, $names[ $depth ] ) : '';

		switch( $floats_round_type ){

			case 'fixed':
				return $this->fixed( $number, $decimals ) . $unit_suffix;

			case 'smart':
				return $this->smart( $number, $decimals ) . $unit_suffix;

			default:
				return $this->flex( $number, $decimals ) . $unit_suffix;
		}
	}

	/**
	 * Convert big number to readable format.
	 * Supports negative numbers.
	 * Can be used as root function. I.e. wrap it to your own function where pass
	 * $names and specify desired output.
	 *
	 * @param float|string $number  Original Number.
	 * @param int          $depth   Internal.
	 */
	private static function _human_unit_depth( $number, int $depth = 0 ): array {

		$sign = $number <=> 0;
		$number = abs( $number );

		if( $number >= 1000 ){
			return self::_human_unit_depth( $number * $sign / 1000, ++$depth );
		}

		return [ $number * $sign, $depth ];
	}

	/**
	 * Format number. Smart calculate zeros after dot and
	 * leave specified $show_decimals numbers of digits.
	 *
	 * @see Num_FormatTest::test__smart()
	 *
	 * Example:
	 * - 0.0000000123 > 0.000000012;
	 * - 0.0123 > 0.012;
	 * - 2.999951132432 > 2.999951;
	 *
	 * @param float|string $number
	 * @param int|null     $show_decimals
	 */
	public function smart( $number, int $show_decimals = 2 ): string {

		if( ! $number ){
			return '';
		}

		$decimals = $show_decimals;
		$abs_number = abs( $number );

		if(     $abs_number < 0.1 ){  $decimals += 2; $show_decimals = 2; }
		elseif( $abs_number < 1   ){  $decimals += 1; $show_decimals = 2; }

		// use simple formats fo big numbers
		if( $abs_number < 30 && $show_decimals > 1 ){
			$number = (float) $number;
			// increase $decimals for numbers like: n.00nnn | n.99nnn
			preg_match( "/\d+\.((?:0{2,}|9{2,})\d{1,$show_decimals})/", sprintf( '%.12f', $number ), $mm );
			if( $mm ){
				$decimals = strlen( $mm[1] );
			}
		}

		return $this->flex( $number, $decimals );
	}

	/**
	 * Format number and trim ending zeros.
	 *
	 * @see Num_FormatTest::test__flex()
	 * @see number_format_i18n()
	 *
	 * @param float|string $number
	 * @param int          $decimals  Optional. Precision of number of decimal places. Default 0.
	 */
	public function flex( $number, int $decimals = 2 ): string {

		if( ! $number ){
			return '';
		}

		$number = $this->fixed( $number, $decimals );

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
	 * @see Num_FormatTest::test__fixed()
	 * @see number_format_i18n()
	 *
	 * @param float|string $number
	 * @param int          $decimals  Optional. Precision of number of decimal places. Default 0.
	 */
	public function fixed( $number, int $decimals = 2 ): string {

		if( ! $number ){
			return '';
		}

		return number_format_i18n( $number, $decimals );
	}

}
