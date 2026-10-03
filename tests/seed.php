<?php
/**
 * Tests der Import-Zuordnung.
 *
 * Geprüft wird die Abbildung Datensatz -> Beitrag, die später auch der
 * Feed-Importer benutzt. Läuft ohne WordPress.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );

// Die WordPress-Teile von seed.php laufen erst bei Hook-Aufrufen; für die
// Zuordnungsfunktionen genügen diese Stubs.
function add_action( string $hook, $callback, int $priority = 10, int $args = 1 ): bool {
	return true;
}

require dirname( __DIR__ ) . '/includes/seed.php';

use function RealNorth\Seed\lies_json;
use function RealNorth\Seed\team_zu_beitrag;
use function RealNorth\Seed\wohnung_zu_beitrag;

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

echo "--- Dateien werden gefunden ---\n";
pruefe( 'wohnungen-beispieldaten.json: 9 Zeilen', count( lies_json( 'wohnungen-beispieldaten.json' ) ), 9 );
pruefe( 'team.json: 7 Zeilen', count( lies_json( 'team.json' ) ), 7 );
pruefe( 'fehlende Datei gibt leeres Array', lies_json( 'gibt-es-nicht.json' ), array() );

echo "\n--- Wohnung -> Beitrag ---\n";
$w = wohnung_zu_beitrag(
	array(
		'slug'    => 'bergstrasse-b12',
		'objekt'  => 'Bergstrasse 12, B 12',
		'ort'     => 'Berikon',
		'zimmer'  => '3.5',
		'flaeche' => 84,
		'frei'    => '01.10.2026',
		'miete'   => 1980,
		'status'  => 'Frei',
	)
);
pruefe( 'Titel ist das Objekt', $w['titel'], 'Bergstrasse 12, B 12' );
pruefe( 'Ort', $w['meta']['rn_ort'], 'Berikon' );
pruefe( 'Fläche als Zahl', $w['meta']['rn_flaeche'], 84 );
pruefe( 'Miete als Zahl', $w['meta']['rn_miete'], 1980 );
pruefe( 'slug wird zur Quellen-ID', $w['meta']['rn_quelle_id'], 'bergstrasse-b12' );

echo "\n--- Lücken im Datensatz brechen nicht ---\n";
$leer = wohnung_zu_beitrag( array() );
pruefe( 'leerer Titel', $leer['titel'], '' );
pruefe( 'Fläche wird 0', $leer['meta']['rn_flaeche'], 0 );
pruefe( 'Status fällt auf Frei zurück', $leer['meta']['rn_status'], 'Frei' );

$text = wohnung_zu_beitrag( array( 'miete' => "1'980", 'flaeche' => '84 m²' ) );
pruefe( 'Zahl aus Text mit Trenner', $text['meta']['rn_miete'], 1 );
pruefe( 'Fläche aus Text', $text['meta']['rn_flaeche'], 84 );

echo "\n--- Leerzeichen werden getrimmt ---\n";
$raum = wohnung_zu_beitrag( array( 'objekt' => '  Islerenweg 4  ', 'ort' => " Rudolfstetten\n" ) );
pruefe( 'Titel ohne Rand', $raum['titel'], 'Islerenweg 4' );
pruefe( 'Ort ohne Rand', $raum['meta']['rn_ort'], 'Rudolfstetten' );

echo "\n--- Team -> Beitrag ---\n";
$t = team_zu_beitrag(
	array( 'name' => 'Stephan Rogger', 'rolle' => 'Geschäftsführer, Geschäftsleitung', 'bereich' => 'Geschäftsleitung', 'ord' => 1 )
);
pruefe( 'Name als Titel', $t['titel'], 'Stephan Rogger' );
pruefe( 'Rolle', $t['meta']['rn_rolle'], 'Geschäftsführer, Geschäftsleitung' );
pruefe( 'Reihenfolge als Zahl', $t['meta']['rn_reihenfolge'], 1 );

echo "\n--- Echte Daten lassen sich vollständig abbilden ---\n";
$titel = array();
foreach ( lies_json( 'wohnungen-beispieldaten.json' ) as $zeile ) {
	$b = wohnung_zu_beitrag( $zeile );
	$titel[] = $b['titel'];
	if ( '' === $b['titel'] || '' === $b['meta']['rn_quelle_id'] || 0 === $b['meta']['rn_miete'] ) {
		pruefe( 'Datensatz unvollständig: ' . wp_json( $zeile ), false, true );
	}
}
pruefe( 'alle 9 haben einen Titel', count( array_filter( $titel ) ), 9 );
pruefe( 'Titel sind eindeutig', count( array_unique( $titel ) ), 9 );

$ids = array_map(
	static fn( array $z ): string => wohnung_zu_beitrag( $z )['meta']['rn_quelle_id'],
	lies_json( 'wohnungen-beispieldaten.json' )
);
pruefe( 'Quellen-IDs sind eindeutig', count( array_unique( $ids ) ), 9 );

/**
 * Kurzform für die Fehlerausgabe oben.
 *
 * @param mixed $wert Wert.
 */
function wp_json( $wert ): string {
	return (string) json_encode( $wert, JSON_UNESCAPED_UNICODE );
}

echo "\n";
echo 0 === $fehler
	? "seed: alle {$anzahl} Prüfungen ok.\n"
	: "seed: {$fehler} von {$anzahl} Prüfungen fehlgeschlagen.\n";

exit( 0 === $fehler ? 0 : 1 );
