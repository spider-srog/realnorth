<?php
/**
 * Tests des Messpunkts für die Anmeldung per Anwendungspasswort.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );

function add_action( string $hook, $callback, int $priority = 10, int $args = 1 ): bool {
	return true;
}

require dirname( __DIR__ ) . '/includes/anmeldung-pruefen.php';

use function RealNorth\Anmeldung\deutung;
use function RealNorth\Anmeldung\header_befund;

$fehler = 0;
$anzahl = 0;

/**
 * Eine Erwartung prüfen.
 *
 * @param string $was      Was geprüft wird.
 * @param mixed  $ist      Tatsächlicher Wert.
 * @param mixed  $erwartet Erwarteter Wert.
 */
function pruefe( string $was, $ist, $erwartet ): void {
	global $fehler, $anzahl;
	++$anzahl;
	$ok = ( $ist === $erwartet );
	if ( ! $ok ) {
		++$fehler;
	}
	printf(
		"%s  %s%s\n",
		$ok ? 'ok  ' : 'FAIL',
		$was,
		$ok ? '' : sprintf( "\n        ist:      %s\n        erwartet: %s", var_export( $ist, true ), var_export( $erwartet, true ) )
	);
}

echo "--- Header-Befund ---\n";
pruefe( 'nichts da: nicht angekommen', header_befund( array() )['header_angekommen'], false );
pruefe( 'leerer Header zählt nicht', header_befund( array( 'HTTP_AUTHORIZATION' => '  ' ) )['header_angekommen'], false );
pruefe( 'HTTP_AUTHORIZATION erkannt', header_befund( array( 'HTTP_AUTHORIZATION' => 'Basic eDp5' ) )['quelle'], 'HTTP_AUTHORIZATION' );
pruefe( 'Schema basic, Gross/klein egal', header_befund( array( 'HTTP_AUTHORIZATION' => 'basic eDp5' ) )['schema'], 'basic' );
pruefe( 'Bearer ist anderes Schema', header_befund( array( 'HTTP_AUTHORIZATION' => 'Bearer abc' ) )['schema'], 'anderes' );
pruefe( 'REDIRECT_ wird gefunden', header_befund( array( 'REDIRECT_HTTP_AUTHORIZATION' => 'Basic eDp5' ) )['quelle'], 'REDIRECT_HTTP_AUTHORIZATION' );
pruefe( 'PHP_AUTH_USER reicht', header_befund( array( 'PHP_AUTH_USER' => 'x' ) )['quelle'], 'PHP_AUTH_USER' );
pruefe( 'Wert wird nie ausgegeben', in_array( 'Basic eDp5', header_befund( array( 'HTTP_AUTHORIZATION' => 'Basic eDp5' ) ), true ), false );

echo "\n--- Deutung ---\n";
pruefe( 'ohne Header: Webserver', str_contains( deutung( false, true, false, null ), 'Webserver' ), true );
pruefe( 'abgeschaltet', str_contains( deutung( true, false, false, null ), 'abgeschaltet' ), true );
pruefe( 'angemeldet', deutung( true, true, true, null ), 'Anmeldung funktioniert.' );
pruefe( 'Fehlercode wird genannt', str_contains( deutung( true, true, false, 'invalid_username' ), 'invalid_username' ), true );
pruefe( 'übersprungen', str_contains( deutung( true, true, false, null ), 'übersprungen' ), true );

printf( "\n%d Prüfungen, %d Fehler\n", $anzahl, $fehler );
exit( $fehler > 0 ? 1 : 0 );
