<?php

class Num_FormatTest extends extends \WP_Mock\Tools\TestCase {

	public function setUp(): void {
		parent::setUp();

		$GLOBALS['wp_locale'] = (object) [
			'number_format' => [
				'thousands_sep' => ' ',
				'decimal_point' => ',',
			],
		];

		\WP_Mock::userFunction( 'number_format_i18n' )->andReturnUsing( static function( $number, $decimals = 0 ) {
			global $wp_locale;

			return number_format( $number, absint( $decimals ), $wp_locale->number_format['decimal_point'], $wp_locale->number_format['thousands_sep'] );
		} );
	}

	public function tearDown(): void {
		unset( $GLOBALS['wp_locale'] );
		parent::tearDown();
	}

	/**
	 * @covers Num_Format::human_abbr
	 */
	public function test__human_abbr(): void {
		$this->assertSame( '0', ( new Num_Format() )->human_abbr( false ) );
		$this->assertSame( '0', ( new Num_Format() )->human_abbr( null ) );
		$this->assertSame( '0', ( new Num_Format() )->human_abbr( 0 ) );
		$this->assertSame( '1,1', ( new Num_Format() )->human_abbr( 1.0654 ) );
		$this->assertSame( '16', ( new Num_Format() )->human_abbr( 16 ) );
		$this->assertSame( '1,7 тыс.', ( new Num_Format() )->human_abbr( 1654 ) );
		$this->assertSame( '16,5 тыс.', ( new Num_Format() )->human_abbr( 16504.234 ) );
		$this->assertSame( '16,5 тыс.', ( new Num_Format() )->human_abbr( 16504.0000234 ) );
		$this->assertSame( '254,9 млн.', ( new Num_Format() )->human_abbr( 254854564 ) );
		$this->assertSame( '-254,9 млн.', ( new Num_Format() )->human_abbr( -254854564 ) );
	}

	/**
	 * @covers Num_Format::human_short
	 */
	public function test__human_short(): void {
		$this->assertSame( '16,5K', ( new Num_Format() )->human_short( 16504.0000234 ) );
		$this->assertSame( '254,9M', ( new Num_Format() )->human_short( 254854564 ) );
		$this->assertSame( '25,5B', ( new Num_Format() )->human_short( 25485456411 ) );
		$this->assertSame( '2,5T', ( new Num_Format() )->human_short( 2548545641111 ) );
		$this->assertSame( '2,5Q', ( new Num_Format() )->human_short( 2548545641111999 ) );
	}

	/**
	 * @covers Num_Format::human_k
	 */
	public function test__human_k(): void {
		$this->assertSame( '0', ( new Num_Format() )->human_k( null ) );
		$this->assertSame( '0', ( new Num_Format() )->human_k( false ) );
		$this->assertSame( '0', ( new Num_Format() )->human_k( 0 ) );
		$this->assertSame( '16,6kk', ( new Num_Format() )->human_k( 16565404.0000234 ) );
		$this->assertSame( '254,9kkk', ( new Num_Format() )->human_k( 254856544564 ) );

		$this->assertSame( '2,0000023', ( new Num_Format() )->human_k( 2.00000231, '2 smart' ) );
		$this->assertSame( '2,00', ( new Num_Format() )->human_k( 2.00000231, '2 fixed' ) );
		$this->assertSame( '2', ( new Num_Format() )->human_k( 2.00000231, '2 flex' ) );
		$this->assertSame( '2,232', ( new Num_Format() )->human_k( 2.2317, '3 smart' ) );
		$this->assertSame( '2,232', ( new Num_Format() )->human_k( 2.2317, '3 fixed' ) );
		$this->assertSame( '2,232', ( new Num_Format() )->human_k( 2.2317, '3 flex' ) );
		$this->assertSame( '-2,232', ( new Num_Format() )->human_k( -2.2317, '3 flex' ) );
	}

	/**
	 * @covers Num_Format::fixed
	 */
	public function test__fixed(): void {
		$this->assertSame( '0,000', ( new Num_Format() )->fixed( 0.000111, 3 ) );
		$this->assertSame( '1,011', ( new Num_Format() )->fixed( 1.0111, 3 ) );
		$this->assertSame( '254,00', ( new Num_Format() )->fixed( 254 ) );
	}

	/**
	 * @covers Num_Format::smart
	 */
	public function test__smart(): void {
		$this->assertSame( '0,00011', ( new Num_Format() )->smart( 0.0001111, 2 ) );
		$this->assertSame( '1,00011', ( new Num_Format() )->smart( 1.0001111, 2 ) );
		$this->assertSame( '254 854 564', ( new Num_Format() )->smart( 254854564 ) );

		// zero decimal
		$this->assertSame( '24', ( new Num_Format() )->smart( 23.54, 0 ) );
		$this->assertSame( '24', ( new Num_Format() )->smart( 23.5, 0 ) );
		$this->assertSame( '1', ( new Num_Format() )->smart( 1.2, 0 ) );
		$this->assertSame( '0,9', ( new Num_Format() )->smart( 0.85, 0 ) );
		$this->assertSame( '0,8', ( new Num_Format() )->smart( 0.83, 0 ) );
		$this->assertSame( '0,07', ( new Num_Format() )->smart( 0.073, 0 ) );
		$this->assertSame( '0,0063', ( new Num_Format() )->smart( 0.0063, 0 ) );
		$this->assertSame( '0,003', ( new Num_Format() )->smart( 0.00301, 0 ) );
		$this->assertSame( '0,00053', ( new Num_Format() )->smart( 0.00053, 0 ) );
		$this->assertSame( '0,000063', ( new Num_Format() )->smart( 0.000063, 0 ) );
		$this->assertSame( '0,0000073', ( new Num_Format() )->smart( 0.0000073, 0 ) );
		$this->assertSame( '0,00000083', ( new Num_Format() )->smart( 0.00000083, 0 ) );
		$this->assertSame( '0,000000093', ( new Num_Format() )->smart( 0.000000093, 0 ) );
		$this->assertSame( '0,0000000013', ( new Num_Format() )->smart( 0.0000000013, 0 ) );
	}

	/**
	 * @covers Num_Format::flex
	 */
	public function test__flex(): void {
		$this->assertSame( '16 504', ( new Num_Format() )->flex( 16504.0000234 ) );
		$this->assertSame( '16 504,01', ( new Num_Format() )->flex( 16504.0100 ) );
		$this->assertSame( '254 854 564', ( new Num_Format() )->flex( 254854564 ) );
	}
}

