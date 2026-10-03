<?php
/**
 * Reine Logik rund um Wohnungen — Filter, Sortierung, Formatierung.
 *
 * Diese Datei ruft bewusst **keine** WordPress-Funktion auf. Damit ist sie
 * ohne WordPress testbar (`tests/wohnungen-logik.php`), und genau hier
 * stecken die Fehler, die man sonst erst live sieht: ein Filter, der zu
 * viel oder zu wenig durchlässt, eine Sortierung, die «sofort» hinter
 * Dezemberterminen einreiht.
 *
 * Die Feldnamen folgen dem Design-Entwurf und `content/wohnungen-beispieldaten.json`.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Wohnungen;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Status, die im Frontend erscheinen dürfen.
 *
 * «Vermietet» fehlt mit Absicht: der Importer setzt verschwundene Objekte
 * auf diesen Status, statt sie zu löschen — die Seite bleibt erreichbar,
 * taucht aber nicht mehr in der Liste auf.
 */
const OEFFENTLICHE_STATUS = array( 'Frei', 'Reserviert', 'Vormerkung' );

/** Wert im Feld «frei», der sofortige Verfügbarkeit bedeutet. */
const SOFORT = 'sofort';

/**
 * Betrag als Schweizer Mietzins ausgeben: 1980 -> «CHF 1'980».
 *
 * Der Hochkomma-Tausendertrenner ist in der Schweiz üblich und steht so
 * auch im Entwurf. Rappen werden nicht angezeigt — Mietzinse in der
 * Vermarktung sind ganze Franken.
 */
function format_miete( int $betrag ): string {
	return 'CHF ' . number_format( $betrag, 0, '.', "'" );
}

/**
 * Fläche ausgeben: 84 -> «84 m²».
 */
function format_flaeche( int $quadratmeter ): string {
	return $quadratmeter . ' m²';
}

/**
 * Zimmerzahl ausgeben: '3.5' -> «3.5 Zimmer».
 */
function format_zimmer( string $zimmer ): string {
	return $zimmer . ' Zimmer';
}

/**
 * Ist das Objekt sofort beziehbar?
 */
function ist_sofort( string $frei ): bool {
	return SOFORT === strtolower( trim( $frei ) );
}

/**
 * Verfügbarkeit als Sortierschlüssel.
 *
 * «sofort» muss vor jedem Datum liegen, deshalb der leere String — er
 * sortiert alphabetisch vor jeder Ziffer. Datumsangaben kommen als
 * TT.MM.JJJJ und werden nach JJJJMMTT gedreht, damit die Zeichenkette in
 * der richtigen Reihenfolge sortiert. Unverständliches landet hinten.
 */
function verfuegbarkeits_schluessel( string $frei ): string {
	$frei = trim( $frei );

	if ( ist_sofort( $frei ) ) {
		return '';
	}

	if ( preg_match( '/^(\d{2})\.(\d{2})\.(\d{4})$/', $frei, $t ) ) {
		return $t[3] . $t[2] . $t[1];
	}

	return '99999999';
}

/**
 * Verfügbarkeit fürs Frontend: «sofort» oder «ab 01.11.2026».
 */
function format_frei( string $frei ): string {
	return ist_sofort( $frei ) ? 'sofort frei' : 'ab ' . trim( $frei );
}

/**
 * Darf das Objekt öffentlich erscheinen?
 */
function ist_oeffentlich( string $status ): bool {
	return in_array( $status, OEFFENTLICHE_STATUS, true );
}

/**
 * Passt die Zimmerzahl zum Filterwert?
 *
 * Der Entwurf kennt die Stufe «4.5+»; sie meint «4.5 Zimmer und mehr»,
 * nicht nur genau 4.5. Alles andere ist ein exakter Vergleich, damit
 * «2.5» keine «12.5» trifft.
 */
function passt_zimmer( string $zimmer, string $filter ): bool {
	if ( '' === $filter || 'alle' === $filter ) {
		return true;
	}

	if ( str_ends_with( $filter, '+' ) ) {
		return (float) $zimmer >= (float) rtrim( $filter, '+' );
	}

	return $zimmer === $filter;
}

/**
 * Entspricht eine Wohnung dem Filter?
 *
 * Erwartete Schlüssel im Filter, alle optional:
 *   ort        string  Ortsname oder 'alle'
 *   zimmer     string  '2.5', '4.5+' oder 'alle'
 *   max_miete  int     0 = egal
 *   sofort     bool    nur sofort beziehbare
 *
 * @param array<string, mixed> $wohnung Datensatz mit ort, zimmer, miete, frei, status.
 * @param array<string, mixed> $filter  Filterwerte.
 */
function passt_zum_filter( array $wohnung, array $filter ): bool {
	if ( ! ist_oeffentlich( (string) ( $wohnung['status'] ?? '' ) ) ) {
		return false;
	}

	$ort = (string) ( $filter['ort'] ?? 'alle' );
	if ( '' !== $ort && 'alle' !== $ort && (string) ( $wohnung['ort'] ?? '' ) !== $ort ) {
		return false;
	}

	if ( ! passt_zimmer( (string) ( $wohnung['zimmer'] ?? '' ), (string) ( $filter['zimmer'] ?? 'alle' ) ) ) {
		return false;
	}

	$max = (int) ( $filter['max_miete'] ?? 0 );
	if ( $max > 0 && (int) ( $wohnung['miete'] ?? 0 ) > $max ) {
		return false;
	}

	if ( ! empty( $filter['sofort'] ) && ! ist_sofort( (string) ( $wohnung['frei'] ?? '' ) ) ) {
		return false;
	}

	return true;
}

/**
 * Wohnungen filtern.
 *
 * @param array<int, array<string, mixed>> $wohnungen Datensätze.
 * @param array<string, mixed>             $filter    Filterwerte.
 * @return array<int, array<string, mixed>>
 */
function filtern( array $wohnungen, array $filter ): array {
	return array_values(
		array_filter(
			$wohnungen,
			static fn( array $w ): bool => passt_zum_filter( $w, $filter )
		)
	);
}

/**
 * Wohnungen sortieren: zuerst die frei sind, dann nach Bezugstermin,
 * bei gleichem Termin nach Ort und Objekt — damit die Reihenfolge
 * zwischen zwei Aufrufen stabil bleibt.
 *
 * @param array<int, array<string, mixed>> $wohnungen Datensätze.
 * @return array<int, array<string, mixed>>
 */
function sortieren( array $wohnungen ): array {
	usort(
		$wohnungen,
		static function ( array $a, array $b ): int {
			$sa = verfuegbarkeits_schluessel( (string) ( $a['frei'] ?? '' ) );
			$sb = verfuegbarkeits_schluessel( (string) ( $b['frei'] ?? '' ) );

			return array( $sa, (string) ( $a['ort'] ?? '' ), (string) ( $a['objekt'] ?? '' ) )
				<=> array( $sb, (string) ( $b['ort'] ?? '' ), (string) ( $b['objekt'] ?? '' ) );
		}
	);

	return $wohnungen;
}

/**
 * Die im Bestand vorkommenden Orte, alphabetisch — für die Filterleiste.
 *
 * @param array<int, array<string, mixed>> $wohnungen Datensätze.
 * @return array<int, string>
 */
function orte( array $wohnungen ): array {
	$orte = array();

	foreach ( $wohnungen as $w ) {
		$ort = trim( (string) ( $w['ort'] ?? '' ) );
		if ( '' !== $ort && ist_oeffentlich( (string) ( $w['status'] ?? '' ) ) ) {
			$orte[ $ort ] = true;
		}
	}

	$liste = array_keys( $orte );
	sort( $liste, SORT_NATURAL | SORT_FLAG_CASE );

	return $liste;
}
