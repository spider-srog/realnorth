<?php
/**
 * Beispieldaten importieren.
 *
 * Füllt die Inhaltstypen aus `content/*.json` — damit beim Aufbau der
 * Seiten echte Datensätze dastehen statt leerer Listen. Der Import ist
 * wiederholbar: er erkennt bestehende Einträge an der Objekt-ID der
 * Quelle (Wohnungen) bzw. am Namen (Team) und aktualisiert sie, statt
 * Dubletten anzulegen.
 *
 * Dieselbe Zuordnung benutzt später der Feed-Importer. Deshalb liegt die
 * Abbildung Datensatz -> Beitrag als reine Funktion vor und ist getestet.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Seed;

use RealNorth\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Einen Wohnungs-Datensatz auf Beitrag und Meta abbilden.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar.
 *
 * @param array<string, mixed> $zeile Datensatz aus der JSON-Datei.
 * @return array{titel: string, meta: array<string, mixed>}
 */
function wohnung_zu_beitrag( array $zeile ): array {
	return array(
		'titel' => trim( (string) ( $zeile['objekt'] ?? '' ) ),
		'meta'  => array(
			'rn_ort'       => trim( (string) ( $zeile['ort'] ?? '' ) ),
			'rn_zimmer'    => trim( (string) ( $zeile['zimmer'] ?? '' ) ),
			'rn_flaeche'   => (int) ( $zeile['flaeche'] ?? 0 ),
			'rn_frei'      => trim( (string) ( $zeile['frei'] ?? '' ) ),
			'rn_miete'     => (int) ( $zeile['miete'] ?? 0 ),
			'rn_status'    => trim( (string) ( $zeile['status'] ?? 'Frei' ) ),
			'rn_quelle_id' => trim( (string) ( $zeile['slug'] ?? '' ) ),
		),
	);
}

/**
 * Einen Team-Datensatz auf Beitrag und Meta abbilden.
 *
 * @param array<string, mixed> $zeile Datensatz aus der JSON-Datei.
 * @return array{titel: string, meta: array<string, mixed>}
 */
function team_zu_beitrag( array $zeile ): array {
	return array(
		'titel' => trim( (string) ( $zeile['name'] ?? '' ) ),
		'meta'  => array(
			'rn_rolle'       => trim( (string) ( $zeile['rolle'] ?? '' ) ),
			'rn_bereich'     => trim( (string) ( $zeile['bereich'] ?? '' ) ),
			'rn_reihenfolge' => (int) ( $zeile['ord'] ?? 0 ),
		),
	);
}

/**
 * JSON-Datei aus content/ lesen.
 *
 * @param string $datei Dateiname ohne Pfad.
 * @return array<int, array<string, mixed>>
 */
function lies_json( string $datei ): array {
	$pfad = dirname( __DIR__ ) . '/content/' . $datei;

	if ( ! is_readable( $pfad ) ) {
		return array();
	}

	$roh = file_get_contents( $pfad ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- lokale Datei im Plugin, kein HTTP.

	if ( false === $roh ) {
		return array();
	}

	$daten = json_decode( $roh, true );

	return is_array( $daten ) ? $daten : array();
}

/**
 * Einen Beitrag anlegen oder aktualisieren.
 *
 * Gefunden wird über ein Meta-Feld (Wohnungen: die Objekt-ID der Quelle)
 * oder über den Titel (Team). Ohne diesen Abgleich legt jeder Lauf alles
 * noch einmal an.
 *
 * @param string               $post_type Inhaltstyp.
 * @param string               $titel     Titel.
 * @param array<string, mixed> $meta      Meta-Felder.
 * @param string               $schluessel Meta-Schlüssel für den Abgleich, leer = über Titel.
 * @return string 'neu' | 'aktualisiert' | 'uebersprungen'
 */
function speichere( string $post_type, string $titel, array $meta, string $schluessel = '' ): string {
	if ( '' === $titel ) {
		return 'uebersprungen';
	}

	$vorhanden = null;

	if ( '' !== $schluessel && '' !== (string) ( $meta[ $schluessel ] ?? '' ) ) {
		$treffer = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => $schluessel, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- einmaliger Import, kein Frontend.
				'meta_value'     => (string) $meta[ $schluessel ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'fields'         => 'ids',
			)
		);
		$vorhanden = $treffer[0] ?? null;
	}

	if ( null === $vorhanden ) {
		$treffer = get_page_by_title( $titel, OBJECT, $post_type );
		if ( $treffer instanceof \WP_Post ) {
			$vorhanden = $treffer->ID;
		}
	}

	$daten = array(
		'post_type'   => $post_type,
		'post_title'  => $titel,
		'post_status' => 'publish',
	);

	if ( null !== $vorhanden ) {
		$daten['ID'] = (int) $vorhanden;
		$post_id     = wp_update_post( $daten, true );
		$ergebnis    = 'aktualisiert';
	} else {
		$post_id  = wp_insert_post( $daten, true );
		$ergebnis = 'neu';
	}

	if ( is_wp_error( $post_id ) ) {
		return 'uebersprungen';
	}

	foreach ( $meta as $key => $wert ) {
		update_post_meta( (int) $post_id, $key, $wert );
	}

	return $ergebnis;
}

/**
 * Import ausführen.
 *
 * @return array<string, int> Zählwerte je Ergebnis.
 */
function importiere(): array {
	$zaehler = array( 'neu' => 0, 'aktualisiert' => 0, 'uebersprungen' => 0 );

	foreach ( lies_json( 'wohnungen-beispieldaten.json' ) as $zeile ) {
		$b = wohnung_zu_beitrag( $zeile );
		++$zaehler[ speichere( PostTypes\WOHNUNG, $b['titel'], $b['meta'], 'rn_quelle_id' ) ];
	}

	foreach ( lies_json( 'team.json' ) as $zeile ) {
		$b = team_zu_beitrag( $zeile );
		++$zaehler[ speichere( PostTypes\TEAM, $b['titel'], $b['meta'] ) ];
	}

	return $zaehler;
}

/**
 * Menüpunkt unter «Wohnungen».
 */
function add_admin_page(): void {
	add_submenu_page(
		'edit.php?post_type=' . PostTypes\WOHNUNG,
		__( 'Beispieldaten importieren', 'realnorth' ),
		__( 'Beispieldaten', 'realnorth' ),
		'manage_options',
		'realnorth-seed',
		__NAMESPACE__ . '\render_admin_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\add_admin_page' );

/**
 * Seite mit dem Knopf.
 */
function render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'realnorth' ) );
	}

	$meldung = '';

	if ( isset( $_POST['realnorth_seed'] )
		&& check_admin_referer( 'realnorth_seed', 'realnorth_seed_nonce' ) ) {
		$z       = importiere();
		$meldung = sprintf(
			/* translators: 1: neu, 2: aktualisiert, 3: übersprungen */
			__( '%1$d neu angelegt, %2$d aktualisiert, %3$d übersprungen.', 'realnorth' ),
			$z['neu'],
			$z['aktualisiert'],
			$z['uebersprungen']
		);
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Beispieldaten importieren', 'realnorth' ) . '</h1>';

	if ( '' !== $meldung ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $meldung ) . '</p></div>';
	}

	echo '<p>' . esc_html__(
		'Liest content/wohnungen-beispieldaten.json und content/team.json aus dem Plugin und legt daraus Wohnungen und Teammitglieder an. Der Lauf ist wiederholbar: bestehende Einträge werden aktualisiert, nicht verdoppelt.',
		'realnorth'
	) . '</p>';

	echo '<form method="post">';
	wp_nonce_field( 'realnorth_seed', 'realnorth_seed_nonce' );
	submit_button( __( 'Jetzt importieren', 'realnorth' ), 'primary', 'realnorth_seed' );
	echo '</form></div>';
}
