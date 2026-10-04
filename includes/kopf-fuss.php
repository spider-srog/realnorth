<?php
/**
 * Kopf- und Fusszeile.
 *
 * Ohne Elementor Pro gibt es keinen Theme Builder — Kopf und Fuss muss
 * also etwas anderes liefern. Hier das Plugin: so liegen sie in Git,
 * sind reviewbar, und ein Theme-Update kann sie nicht überschreiben.
 *
 * Ausgegeben werden sie von der Seitenvorlage
 * templates/seite-realnorth.php, nicht von einem Hook. Damit bleibt die
 * übrige Seite (wp-admin, Anmeldung, Seiten mit der Theme-Vorlage)
 * unverändert — die Umstellung ist pro Seite umkehrbar.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\KopfFuss;

use RealNorth\Bilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Einen Bereich aus dem Theme Builder von Elementor Pro ausgeben.
 *
 * Gibt true zurück, wenn dort eine Vorlage zugewiesen ist und ausgegeben
 * wurde. Dann hält sich das Plugin heraus. Erst wenn nichts kommt —
 * Elementor Pro fehlt, oder für diesen Bereich ist nichts hinterlegt —
 * springt die Fassung aus dem Plugin ein. So gibt es keinen Stichtag,
 * an dem beides gleichzeitig umgestellt sein muss.
 *
 * @param string $bereich 'header' oder 'footer'.
 */
function elementor_bereich( string $bereich ): bool {
	if ( ! function_exists( 'elementor_theme_do_location' ) ) {
		return false;
	}

	return (bool) elementor_theme_do_location( $bereich );
}

/**
 * Die Hauptnavigation.
 *
 * Reine Datenstruktur, ohne WordPress-Aufruf — damit testbar. Die
 * Adressen sind Pfade relativ zur Startseite; erst beim Ausgeben wird
 * home_url() davorgesetzt.
 *
 * Über den Filter `realnorth_navigation` lässt sich das Menü anpassen,
 * ohne diese Datei zu ändern.
 *
 * @return array<int, array{titel: string, pfad: string, kinder?: array<int, array{titel: string, pfad: string}>}>
 */
function navigation(): array {
	return array(
		array(
			'titel' => 'Wohnungen',
			'pfad'  => '/wohnungen/',
		),
		array(
			'titel'  => 'Liegenschaften',
			'pfad'   => '/liegenschaften/',
			'kinder' => array(
				array( 'titel' => 'Mietwohnungen', 'pfad' => '/liegenschaften/' ),
				array( 'titel' => 'Rennweg 14/16', 'pfad' => '/liegenschaften/rennweg-14-16/' ),
			),
		),
		array(
			'titel' => 'Entwicklung',
			'pfad'  => '/entwicklung/',
		),
		array(
			'titel' => 'Über uns',
			'pfad'  => '/ueber-uns/',
		),
		array(
			'titel' => 'Mieterservice',
			'pfad'  => '/mieterservice/',
		),
	);
}

/**
 * Die Adresse im Fuss und auf der Kontaktseite.
 *
 * @return array<string, string>
 */
function kontakt(): array {
	return array(
		'firma'    => 'Real North AG',
		'strasse'  => 'Unternehmer-Park 3',
		'ort'      => '6340 Baar',
		'telefon'  => '+41 41 552 53 63',
		'mail'     => 'verwaltung@realnorth.ch',
	);
}

/**
 * Prüfen, ob ein Menüpunkt zur aktuellen Adresse gehört.
 *
 * Verglichen werden nur die Pfade, und ein Punkt gilt auch dann als
 * aktiv, wenn die aktuelle Seite darunter liegt (`/liegenschaften/` ist
 * aktiv, während man auf `/liegenschaften/rennweg-14-16/` steht). Die
 * Startseite ist davon ausgenommen, sonst wäre sie immer aktiv.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar.
 *
 * @param string $pfad    Pfad des Menüpunkts.
 * @param string $aktuell Pfad der aufgerufenen Seite.
 */
function ist_aktiv( string $pfad, string $aktuell ): bool {
	$pfad    = '/' . trim( $pfad, '/' ) . '/';
	$aktuell = '/' . trim( $aktuell, '/' ) . '/';

	if ( '//' === $pfad ) {
		return '//' === $aktuell;
	}

	return str_starts_with( $aktuell, $pfad );
}

/**
 * Den Pfad der aufgerufenen Seite ermitteln.
 */
function aktueller_pfad(): string {
	$angefragt = isset( $_SERVER['REQUEST_URI'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
		: '/';

	$pfad = wp_parse_url( $angefragt, PHP_URL_PATH );

	return is_string( $pfad ) ? $pfad : '/';
}

/**
 * Die Wortmarke — Bild aus der Mediathek, sonst Schriftzug.
 *
 * Der Schriftzug ist kein Notbehelf: Barlow liegt ohnehin im Plugin, und
 * als Text ist die Marke scharf auf jedem Bildschirm und wiegt nichts.
 */
function wortmarke(): string {
	$id = Bilder\id( 'logo' );

	if ( $id > 0 ) {
		$html = wp_get_attachment_image( $id, 'medium', false, array( 'alt' => 'realnorth' ) );
		if ( '' !== $html ) {
			return $html;
		}
	}

	return '<span class="rn-kopf__wortmarke">realnorth</span>';
}

/**
 * Die Kopfzeile ausgeben.
 */
function kopfzeile(): void {
	$aktuell = aktueller_pfad();

	/** Siehe navigation(). */
	$punkte = apply_filters( 'realnorth_navigation', navigation() );

	echo '<a class="rn-skip" href="#rn-inhalt">' . esc_html__( 'Zum Inhalt springen', 'realnorth' ) . '</a>';
	echo '<header class="rn-kopf">';
	echo '<div class="rn-kopf__innen">';

	echo '<a class="rn-kopf__marke" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
	echo wp_kses_post( wortmarke() );
	echo '</a>';

	echo '<nav class="rn-kopf__nav" aria-label="' . esc_attr__( 'Hauptnavigation', 'realnorth' ) . '">';

	foreach ( $punkte as $punkt ) {
		$titel  = (string) ( $punkt['titel'] ?? '' );
		$pfad   = (string) ( $punkt['pfad'] ?? '/' );
		$kinder = is_array( $punkt['kinder'] ?? null ) ? $punkt['kinder'] : array();
		$aktiv  = ist_aktiv( $pfad, $aktuell );

		if ( array() === $kinder ) {
			printf(
				'<a href="%s"%s>%s</a>',
				esc_url( home_url( $pfad ) ),
				$aktiv ? ' aria-current="page"' : '',
				esc_html( $titel )
			);
			continue;
		}

		echo '<details class="rn-nav-dropdown"' . ( $aktiv ? ' open' : '' ) . '>';
		echo '<summary>' . esc_html( $titel ) . ' <span aria-hidden="true">&#8964;</span></summary>';
		echo '<div class="rn-nav-dropdown-panel">';

		foreach ( $kinder as $kind ) {
			printf(
				'<a href="%s">%s</a>',
				esc_url( home_url( (string) ( $kind['pfad'] ?? '/' ) ) ),
				esc_html( (string) ( $kind['titel'] ?? '' ) )
			);
		}

		echo '</div></details>';
	}

	echo '</nav>';

	echo '<div class="rn-kopf__aktion">';
	printf(
		'<a class="rn-knopf" href="%s">%s</a>',
		esc_url( home_url( '/kontakt/' ) ),
		esc_html__( 'Kontakt', 'realnorth' )
	);
	echo '</div>';

	echo '</div></header>';
}

/**
 * Die Fusszeile ausgeben.
 */
function fusszeile(): void {
	$k    = kontakt();
	$jahr = (string) gmdate( 'Y' );

	$spalten = array(
		__( 'Mieten', 'realnorth' )     => array(
			array( 'titel' => __( 'Freie Wohnungen', 'realnorth' ), 'pfad' => '/wohnungen/' ),
			array( 'titel' => __( 'Mieterservice', 'realnorth' ), 'pfad' => '/mieterservice/' ),
			array( 'titel' => __( 'Schaden melden', 'realnorth' ), 'pfad' => '/mieterservice/' ),
		),
		__( 'Unternehmen', 'realnorth' ) => array(
			array( 'titel' => __( 'Liegenschaften', 'realnorth' ), 'pfad' => '/liegenschaften/' ),
			array( 'titel' => __( 'Entwicklung', 'realnorth' ), 'pfad' => '/entwicklung/' ),
			array( 'titel' => __( 'Über uns', 'realnorth' ), 'pfad' => '/ueber-uns/' ),
			array( 'titel' => __( 'Kontakt', 'realnorth' ), 'pfad' => '/kontakt/' ),
		),
	);

	echo '<footer class="rn-fuss">';

	$baer = Bilder\id( 'baer' );

	if ( $baer > 0 ) {
		echo wp_kses_post(
			wp_get_attachment_image( $baer, 'large', false, array( 'class' => 'rn-fuss__baer', 'alt' => '', 'aria-hidden' => 'true' ) )
		);
	}

	echo '<div class="rn-fuss__innen">';
	echo '<div class="rn-fuss__raster">';

	echo '<div>';
	echo wp_kses_post( wortmarke() );
	printf(
		'<p>%s<br>%s<br>%s<br><br><a href="tel:%s">%s</a><br><a href="mailto:%s">%s</a></p>',
		esc_html( $k['firma'] ),
		esc_html( $k['strasse'] ),
		esc_html( $k['ort'] ),
		esc_attr( str_replace( ' ', '', $k['telefon'] ) ),
		esc_html( $k['telefon'] ),
		esc_attr( $k['mail'] ),
		esc_html( $k['mail'] )
	);
	echo '</div>';

	foreach ( $spalten as $titel => $links ) {
		echo '<div class="rn-fuss__spalte"><div>' . esc_html( (string) $titel ) . '</div>';
		echo '<div class="rn-fuss__links">';
		foreach ( $links as $link ) {
			printf(
				'<a href="%s">%s</a>',
				esc_url( home_url( (string) $link['pfad'] ) ),
				esc_html( (string) $link['titel'] )
			);
		}
		echo '</div></div>';
	}

	echo '<div class="rn-fuss__spalte"><div>' . esc_html__( 'Projekte', 'realnorth' ) . '</div>';
	echo '<div class="rn-fuss__links">';
	echo '<a href="https://birkenhain.ch" target="_blank" rel="noopener">birkenhain.ch &#8599;</a>';
	echo '</div></div>';

	echo '</div>';

	echo '<div class="rn-fuss__unten">';
	printf(
		'<span>&copy; %s %s</span>',
		esc_html( $jahr ),
		esc_html( $k['firma'] )
	);
	echo '<div>';
	printf( '<a href="%s">%s</a>', esc_url( home_url( '/impressum/' ) ), esc_html__( 'Impressum', 'realnorth' ) );
	printf( '<a href="%s">%s</a>', esc_url( home_url( '/datenschutz/' ) ), esc_html__( 'Datenschutz', 'realnorth' ) );
	echo '</div></div>';

	echo '</div></footer>';
}
