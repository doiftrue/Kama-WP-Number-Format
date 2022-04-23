<?php

$format = \Kama\WP\Num_Format::instance();

$test = [
	$format->human_abbr( 0 ),              // 0
	$format->human_abbr( 1.0654 ),         // 1,1
	$format->human_abbr( 16 ),             // 16
	$format->human_abbr( 1654 ),           // 1,7 тыс.
	$format->human_abbr( 16504.234 ),      // 16,5 тыс.
	$format->human_abbr( 16504.0000234 ),  // 16,5 тыс.
	$format->human_abbr( 254854564 ),      // 254,9 млн.
	'',
	$format->human_k( 16565404.0000234 ),  // 16,6kk
	$format->human_k( 254856544564 ),      // 254,9kkk
	'',
	$format->human_k( 2.00000231, '2 smart' ), // 2,0000023
	$format->human_k( 2.00000231, '2 fixed' ), // 2,00
	$format->human_k( 2.00000231, '2 flex' ),  // 2
	$format->human_k( 2.231, '3 smart' ),      // 2,23
	$format->human_k( 2.231, '3 fixed' ),      // 2,23
	$format->human_k( 2.231, '3 flex' ),       // 2,23
	'',
	$format->human_short( 16504.0000234 ), // 16,5K
	$format->human_short( 254854564 ),     // 254,9M
	'',
	$format->flex( 16504.0000234 ),      // 16 504
	$format->flex( 16504.0100 ),         // 16 504,01
	$format->flex( 254854564 ),          // 254 854 564
	'',
	$format->smart( 0.000111, 2 ),  // 0,00011
	$format->smart( 1.000111, 2 ),  // 1,00011
	$format->smart( 254854564 ),    // 254 854 564
	'',
	$format->fixed( 0.000111, 3 ), // 0,000
	$format->fixed( 1.0111, 3 ),   // 1,011
	$format->fixed( 254 ),         // 254,00
];

print_r( $test );
