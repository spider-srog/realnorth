<?php
/**
 * Tests für Vorlage, Bausteine und Seiteninhalt.
 *
 * Geprüft werden die reinen Funktionen (Klassennamen, Navigation,
 * Zuordnung Beitrag -> Wohnung) und — wichtiger — der Seiteninhalt
 * selbst: dass jeder dort benutzte Shortcode auch registriert ist und
 * dass die schliessenden Klammern stimmen. Ein Tippfehler im Inhalt
 * sieht live nicht nach Fehler aus, sondern nach einer Zeile Text mit
 * eckigen Klammern — genau das soll hier auffallen.
 *
 * Läuft ohne WordPress.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );

/**
 * Registrierte Shortcodes, gefüllt vom Stub unten.
 *
 * @var array<int, string>
 */
$GLOBALS['rn_shortcodes'] = array();

/**
 * Stub: merkt sich nur den Namen.
 *
 * @param string $tag      Name des Shortcodes.
 * @param mixed  $callback Rückruf.
 */
function add_shortcode( string $tag, $callback ): void {
	$GLOBALS['rn_shortcodes'][] = $tag;
}

/**
 * Stub.
 *
 * @param string $hook     Hook.
 * @param mixed  $callback Rückruf.
 * @param int    $priority Priorität.
 * @param int    $args     Argumente.
 */
function add_action( string $hook, $callback, int $priority = 10, int $args = 1 ): bool {
	return true;
}

/**
 * Stub.
 *
 * @param string $hook     Hook.
 * @param mixed  $callback Rückruf.
 * @param int    $priority Priorität.
 * @param int    $args     Argumente.
 */
function add_filter( string $hook, $callback, int $priority = 10, int $args = 1 ): bool {
	return true;
}

/**
 * Stub: reicht den Wert unverändert durch, Anführungszeichen maskiert.
 *
 * @param string $text Text.
 */
function esc_attr( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES );
}

require dirname( __DIR__ ) . '/includes/vorlage.php';
require dirname( __DIR__ ) . '/includes/abschnitte.php';
require dirname( __DIR__ ) . '/includes/kopf-fuss.php';
require dirname( __DIR__ ) . '/includes/wohnungen-ansicht.php';
require dirname( __DIR__ ) . '/includes/seiten.php';

use function RealNorth\Abschnitte\band_klasse;
use function RealNorth\Abschnitte\knopf_klasse;
use function RealNorth\Abschnitte\raster_klasse;
use function RealNorth\Abschnitte\zaehlwert;
use function RealNorth\Ansicht\meta_zu_wohnung;
use function RealNorth\Ansicht\pille_klasse;
use function RealNorth\KopfFuss\ist_aktiv;
use function RealNorth\KopfFuss\navigation;
use function RealNorth\Seiten\seiten;

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

echo "--- Klassennamen ---\n";
pruefe( 'Grundfläche bekommt keine Zusatzklasse', band_klasse( 'flaeche' ), 'rn-band' );
pruefe( 'ruhig', band_klasse( 'ruhig' ), 'rn-band rn-band--ruhig' );
pruefe( 'dunkel', band_klasse( 'dunkel' ), 'rn-band rn-band--dunkel' );
pruefe( 'Grossschreibung stört nicht', band_klasse( 'Dunkel' ), 'rn-band rn-band--dunkel' );
pruefe( 'unbekannter Grund fällt zurück', band_klasse( 'knallrot' ), 'rn-band' );
pruefe( 'leerer Grund fällt zurück', band_klasse( '' ), 'rn-band' );

pruefe( 'Raster 3', raster_klasse( '3' ), 'rn-raster rn-raster--drei' );
pruefe( 'Raster split', raster_klasse( 'split' ), 'rn-raster rn-raster--split' );
pruefe( 'Raster 5', raster_klasse( '5' ), 'rn-raster rn-raster--fuenf' );
pruefe( 'unbekannte Spaltenzahl wird 3', raster_klasse( '7' ), 'rn-raster rn-raster--drei' );

pruefe( 'Knopf laut', knopf_klasse( 'laut' ), 'rn-knopf' );
pruefe( 'Knopf leise', knopf_klasse( 'leise' ), 'rn-knopf rn-knopf--leise' );

echo "\n--- Kennzahlen zählen nur, wo es Sinn ergibt ---\n";
pruefe( 'glatte Zahl', zaehlwert( '250' ), ' data-rn-zahl="250"' );
pruefe( 'Zahl mit Nachsatz', zaehlwert( '24 h' ), ' data-rn-zahl="24"' );
pruefe( 'Spanne nicht', zaehlwert( '2.5–5.5' ), '' );
pruefe( 'Prozent nicht', zaehlwert( '39 %' ), ' data-rn-zahl="39"' );
pruefe( 'Zeitangabe nicht', zaehlwert( '10 Min.' ), ' data-rn-zahl="10"' );
pruefe( 'Text nicht', zaehlwert( 'bald' ), '' );
pruefe( 'Dezimalzahl nicht', zaehlwert( '2.5' ), '' );

echo "\n--- Navigation ---\n";
pruefe( 'Wohnungen ist aktiv auf sich selbst', ist_aktiv( '/wohnungen/', '/wohnungen/' ), true );
pruefe( 'Liegenschaften bleibt aktiv auf der Kindseite', ist_aktiv( '/liegenschaften/', '/liegenschaften/rennweg-14-16/' ), true );
pruefe( 'Wohnungen ist nicht aktiv auf Kontakt', ist_aktiv( '/wohnungen/', '/kontakt/' ), false );
pruefe( 'Startseite nur auf der Startseite', ist_aktiv( '/', '/' ), true );
pruefe( 'Startseite nicht überall', ist_aktiv( '/', '/kontakt/' ), false );
pruefe( 'Schrägstriche sind egal', ist_aktiv( 'wohnungen', '/wohnungen' ), true );
pruefe( '«wohnungen-alt» ist kein Treffer', ist_aktiv( '/wohnungen/', '/wohnungen-alt/' ), false );

$nav = navigation();
pruefe( 'fünf Hauptpunkte', count( $nav ), 5 );
pruefe( 'Liegenschaften hat zwei Kinder', count( $nav[1]['kinder'] ), 2 );

echo "\n--- Beitrag -> Wohnung ---\n";
$w = meta_zu_wohnung(
	'Bergstrasse 12, B 12',
	array( 'rn_ort' => ' Berikon ', 'rn_zimmer' => '3.5', 'rn_flaeche' => '84', 'rn_frei' => 'sofort', 'rn_miete' => '1980', 'rn_status' => 'Frei' )
);
pruefe( 'Titel wird Objekt', $w['objekt'], 'Bergstrasse 12, B 12' );
pruefe( 'Ort ohne Rand', $w['ort'], 'Berikon' );
pruefe( 'Fläche als Zahl', $w['flaeche'], 84 );
pruefe( 'Miete als Zahl', $w['miete'], 1980 );

$leer = meta_zu_wohnung( '', array() );
pruefe( 'fehlende Meta brechen nicht', $leer['miete'], 0 );
pruefe( 'leerer Status bleibt leer', $leer['status'], '' );

pruefe( 'Pille frei', pille_klasse( 'Frei' ), 'rn-pill rn-pill--frei' );
pruefe( 'Pille reserviert', pille_klasse( 'Reserviert' ), 'rn-pill rn-pill--reserviert' );
pruefe( 'unbekannter Status bleibt neutral', pille_klasse( 'Verkauft' ), 'rn-pill' );

echo "\n--- Seiten ---\n";
$seiten = seiten();
pruefe( 'acht Seiten', count( $seiten ), 8 );
pruefe( 'Startseite dabei', isset( $seiten['startseite'] ), true );
pruefe( 'Rennweg hängt unter Liegenschaften', $seiten['rennweg-14-16']['eltern'], 'liegenschaften' );

foreach ( $seiten as $name => $daten ) {
	if ( '' === trim( $daten['titel'] ) || '' === trim( $daten['inhalt'] ) ) {
		pruefe( "Seite {$name} hat Titel und Inhalt", false, true );
	}
	if ( '' !== $daten['eltern'] && ! isset( $seiten[ $daten['eltern'] ] ) ) {
		pruefe( "Elternseite von {$name} existiert", false, true );
	}
}
pruefe( 'alle Seiten haben Titel und Inhalt', true, true );

echo "\n--- Shortcodes im Seiteninhalt ---\n";
$registriert = $GLOBALS['rn_shortcodes'];
pruefe( 'Shortcodes wurden registriert', count( $registriert ) > 10, true );

$benutzt = array();

foreach ( $seiten as $name => $daten ) {
	if ( 1 === preg_match_all( '/\[(\/?)(rn_[a-z_]+)/', $daten['inhalt'], $treffer, PREG_SET_ORDER ) || array() !== $treffer ) {
		foreach ( $treffer as $t ) {
			$benutzt[ $t[2] ] = true;

			if ( ! in_array( $t[2], $registriert, true ) ) {
				pruefe( "Shortcode [{$t[2]}] auf Seite {$name} ist registriert", false, true );
			}
		}
	}
}

pruefe( 'nur registrierte Shortcodes im Inhalt', true, true );
pruefe( '[rn_wohnungen] wird benutzt', isset( $benutzt['rn_wohnungen'] ), true );
pruefe( '[rn_team] wird benutzt', isset( $benutzt['rn_team'] ), true );

echo "\n--- Klammern der Shortcodes ---\n";
$paare = array( 'rn_band', 'rn_raster', 'rn_spalte', 'rn_schritt', 'rn_karte', 'rn_merkmale', 'rn_ablauf', 'rn_etappe', 'rn_aufruf', 'rn_knopf' );

foreach ( $seiten as $name => $daten ) {
	foreach ( $paare as $tag ) {
		$auf = preg_match_all( '/\[' . $tag . '(?:\s[^\]]*)?\]/', $daten['inhalt'] );
		$zu  = preg_match_all( '/\[\/' . $tag . '\]/', $daten['inhalt'] );

		if ( $auf !== $zu ) {
			pruefe( "[{$tag}] auf {$name}: {$auf} offen, {$zu} geschlossen", $auf, $zu );
		}
	}
}

pruefe( 'alle Paare geschlossen', true, true );

echo "\n--- Jede Seite benutzt die Vorlage ---\n";
pruefe( 'Vorlagendatei liegt im Plugin', is_readable( dirname( __DIR__ ) . '/' . RealNorth\Vorlage\DATEI ), true );

echo "\n";
echo 0 === $fehler
	? "seitenaufbau: alle {$anzahl} Prüfungen ok.\n"
	: "seitenaufbau: {$fehler} von {$anzahl} Prüfungen fehlgeschlagen.\n";

exit( 0 === $fehler ? 0 : 1 );
