<?php
/**
 * Anbindung an Elementor Pro.
 *
 * Die Bausteine aus includes/abschnitte.php gibt es zusätzlich als
 * Elementor-Widgets — gleiche Funktion, gleiche Ausgabe, nur ziehbar.
 * Gerendert wird in beiden Fällen von derselben Funktion; es gibt keine
 * zweite Umsetzung, die auseinanderlaufen könnte.
 *
 * Kopf und Fuss kommen aus dem Theme Builder (siehe
 * templates/seite-realnorth.php), Formulare aus Elementor Forms (siehe
 * den Shortcode rn_formular).
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kennung der eigenen Widget-Gruppe im Builder.
 */
const GRUPPE = 'realnorth';

/**
 * Ist Elementor aktiv?
 */
function vorhanden(): bool {
	return did_action( 'elementor/loaded' ) > 0;
}

/**
 * Eine eigene Gruppe im Widget-Bereich des Builders.
 *
 * @param object $verwaltung Elementors Elements_Manager.
 */
function gruppe_anmelden( $verwaltung ): void {
	$verwaltung->add_category(
		GRUPPE,
		array(
			'title' => __( 'realnorth', 'realnorth' ),
			'icon'  => 'eicon-font',
		)
	);
}
add_action( 'elementor/elements/categories_registered', __NAMESPACE__ . '\gruppe_anmelden' );

/**
 * Die Widgets anmelden.
 *
 * Die Klassen liegen in einer eigenen Datei, weil sie von
 * \Elementor\Widget_Base erben — die gibt es erst, wenn Elementor
 * geladen ist. Ein `require` auf Plugin-Ebene wäre ein fataler Fehler,
 * sobald jemand Elementor deaktiviert.
 *
 * @param object $verwaltung Elementors Widgets_Manager.
 */
function widgets_anmelden( $verwaltung ): void {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/elementor-bausteine.php';

	foreach ( bausteine() as $klasse ) {
		$voll = __NAMESPACE__ . '\\' . $klasse;

		if ( class_exists( $voll ) ) {
			$verwaltung->register( new $voll() );
		}
	}
}
add_action( 'elementor/widgets/register', __NAMESPACE__ . '\widgets_anmelden' );

/**
 * Die Klassennamen der Widgets, in der Reihenfolge des Builders.
 *
 * @return array<int, string>
 */
function bausteine(): array {
	return array(
		'Buehne',
		'Seitenkopf',
		'Abschnittskopf',
		'Schritt',
		'Zahl',
		'Karte',
		'Merkmale',
		'Ablauf',
		'Aufruf',
		'Bild',
		'Stelle',
		'Wohnungen',
		'Team',
	);
}

/**
 * Den Wert eines URL-Feldes von Elementor auf die Adresse reduzieren.
 *
 * Elementor liefert dort ein Feld mit url, is_external und nofollow.
 * Unsere Renderer erwarten eine Zeichenkette und entscheiden selber,
 * ob ein Link extern ist.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar.
 *
 * @param mixed $wert Wert aus den Widget-Einstellungen.
 */
function url_wert( $wert ): string {
	if ( is_array( $wert ) ) {
		return trim( (string) ( $wert['url'] ?? '' ) );
	}

	return trim( (string) $wert );
}

/**
 * Die Bildplätze als Auswahlliste für ein SELECT-Feld.
 *
 * @return array<string, string>
 */
function bildplaetze(): array {
	$liste = array( '' => __( '— kein Bild —', 'realnorth' ) );

	foreach ( \RealNorth\Bilder\plaetze() as $platz => $daten ) {
		$liste[ $platz ] = $daten['titel'];
	}

	return $liste;
}
