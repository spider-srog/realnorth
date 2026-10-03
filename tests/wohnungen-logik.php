<?php
/**
 * Tests der Wohnungs-Logik — Filter, Sortierung, Formatierung.
 *
 * Läuft ohne WordPress. Aufruf über bin/test.sh oder direkt mit
 * `php tests/wohnungen-logik.php`.
 *
 * Datengrundlage sind die echten Beispieldaten aus content/, damit die
 * Tests nicht an einer erfundenen Struktur vorbeiprüfen.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );

require dirname( __DIR__ ) . '/includes/wohnungen-logik.php';

use function RealNorth\Wohnungen\filtern;
use function RealNorth\Wohnungen\format_frei;
use function RealNorth\Wohnungen\format_miete;
use function RealNorth\Wohnungen\ist_oeffentlich;
use function RealNorth\Wohnungen\orte;
use function RealNorth\Wohnungen\passt_zimmer;
use function RealNorth\Wohnungen\sortieren;
use function RealNorth\Wohnungen\verfuegbarkeits_schluessel;

$daten = json_decode(
	(string) file_get_contents( dirname( __DIR__ ) . '/content/wohnungen-beispieldaten.json' ),
	true
);

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

echo "--- Beispieldaten ---\n";
pruefe( 'Datei enthält 9 Wohnungen', count( $daten ), 9 );

echo "\n--- Formatierung ---\n";
pruefe( "1980 -> CHF 1'980", format_miete( 1980 ), "CHF 1'980" );
pruefe( "3450 -> CHF 3'450", format_miete( 3450 ), "CHF 3'450" );
pruefe( '940 ohne Trenner', format_miete( 940 ), 'CHF 940' );
pruefe( '12500 mit einem Trenner', format_miete( 12500 ), "CHF 12'500" );
pruefe( 'sofort', format_frei( 'sofort' ), 'sofort frei' );
pruefe( 'Datum', format_frei( '01.11.2026' ), 'ab 01.11.2026' );

echo "\n--- Zimmerfilter ---\n";
pruefe( "'alle' lässt alles durch", passt_zimmer( '2.5', 'alle' ), true );
pruefe( 'exakter Treffer', passt_zimmer( '3.5', '3.5' ), true );
pruefe( 'kein Treffer', passt_zimmer( '2.5', '3.5' ), false );
pruefe( '4.5+ trifft 4.5', passt_zimmer( '4.5', '4.5+' ), true );
pruefe( '4.5+ trifft 5.5', passt_zimmer( '5.5', '4.5+' ), true );
pruefe( '4.5+ trifft 3.5 nicht', passt_zimmer( '3.5', '4.5+' ), false );
pruefe( '2.5 trifft 12.5 nicht', passt_zimmer( '12.5', '2.5' ), false );

echo "\n--- Verfügbarkeit sortiert richtig ---\n";
pruefe( 'sofort vor Datum', verfuegbarkeits_schluessel( 'sofort' ) < verfuegbarkeits_schluessel( '01.10.2026' ), true );
pruefe( 'Oktober vor November', verfuegbarkeits_schluessel( '01.10.2026' ) < verfuegbarkeits_schluessel( '01.11.2026' ), true );
pruefe( 'Dezember 2026 vor Januar 2027', verfuegbarkeits_schluessel( '01.12.2026' ) < verfuegbarkeits_schluessel( '01.01.2027' ), true );
pruefe( 'Unlesbares landet hinten', verfuegbarkeits_schluessel( 'auf Anfrage' ) > verfuegbarkeits_schluessel( '01.01.2099' ), true );

$sortiert = sortieren( $daten );
pruefe( 'erste Wohnung ist sofort frei', $sortiert[0]['frei'], 'sofort' );

echo "\n--- Status ---\n";
pruefe( 'Frei ist öffentlich', ist_oeffentlich( 'Frei' ), true );
pruefe( 'Reserviert ist öffentlich', ist_oeffentlich( 'Reserviert' ), true );
pruefe( 'Vormerkung ist öffentlich', ist_oeffentlich( 'Vormerkung' ), true );
pruefe( 'Vermietet ist es nicht', ist_oeffentlich( 'Vermietet' ), false );
pruefe( 'Leerer Status ist es nicht', ist_oeffentlich( '' ), false );

echo "\n--- Filter auf den echten Daten ---\n";
pruefe( 'ohne Filter: alle 9', count( filtern( $daten, array() ) ), 9 );
pruefe( 'nur Berikon: 3', count( filtern( $daten, array( 'ort' => 'Berikon' ) ) ), 3 );
pruefe( 'nur Zürich: 2', count( filtern( $daten, array( 'ort' => 'Zürich' ) ) ), 2 );
pruefe( 'nur 4.5 Zimmer: 2', count( filtern( $daten, array( 'zimmer' => '4.5' ) ) ), 2 );
pruefe( '4.5+ erfasst auch 5.5: 3', count( filtern( $daten, array( 'zimmer' => '4.5+' ) ) ), 3 );
pruefe( 'bis 1800: 3 (1540, 1610, 1720)', count( filtern( $daten, array( 'max_miete' => 1800 ) ) ), 3 );
pruefe( 'bis 1600: 1 (nur 1540)', count( filtern( $daten, array( 'max_miete' => 1600 ) ) ), 1 );
pruefe( 'Grenze inklusive: bis 1540 trifft 1540', count( filtern( $daten, array( 'max_miete' => 1540 ) ) ), 1 );
pruefe( 'max_miete 0 heisst egal', count( filtern( $daten, array( 'max_miete' => 0 ) ) ), 9 );
pruefe( 'nur sofort: 2', count( filtern( $daten, array( 'sofort' => true ) ) ), 2 );
pruefe( 'Berikon + sofort: 0', count( filtern( $daten, array( 'ort' => 'Berikon', 'sofort' => true ) ) ), 0 );

$ohne_vermietete = $daten;
$ohne_vermietete[0]['status'] = 'Vermietet';
pruefe( 'Vermietete fallen raus', count( filtern( $ohne_vermietete, array() ) ), 8 );

echo "\n--- Orte für die Filterleiste ---\n";
pruefe(
	'alphabetisch, ohne Dubletten',
	orte( $daten ),
	array( 'Berikon', 'Rudolfstetten', 'Winterthur', 'Zürich' )
);

echo "\n";
echo 0 === $fehler
	? "wohnungen-logik: alle {$anzahl} Prüfungen ok.\n"
	: "wohnungen-logik: {$fehler} von {$anzahl} Prüfungen fehlgeschlagen.\n";

exit( 0 === $fehler ? 0 : 1 );
