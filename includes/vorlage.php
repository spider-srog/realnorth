<?php
/**
 * Die Seitenvorlage anmelden.
 *
 * Ohne Elementor Pro gibt es keinen Theme Builder, und PopularFX bringt
 * seinen eigenen Kopf und Fuss mit. Statt die Theme-Dateien anzufassen
 * (verboten, siehe CLAUDE.md) meldet das Plugin eine eigene Vorlage an.
 * Seiten, die sie benutzen, werden vollständig von hier gerendert; alle
 * anderen bleiben beim Theme.
 *
 * Das ist der Notausschalter auf Seitenebene: Vorlage im Editor zurück
 * auf «Standard» stellen, und die Seite sieht wieder aus wie vorher.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Vorlage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dateiname der Vorlage, so wie er im Beitrags-Meta steht.
 */
const DATEI = 'templates/seite-realnorth.php';

/**
 * Die Vorlage in der Auswahlliste des Editors anbieten.
 *
 * @param array<string, string> $vorlagen Bisherige Vorlagen.
 * @return array<string, string>
 */
function anbieten( array $vorlagen ): array {
	$vorlagen[ DATEI ] = __( 'realnorth (Vollbreite)', 'realnorth' );

	return $vorlagen;
}
add_filter( 'theme_page_templates', __NAMESPACE__ . '\anbieten' );

/**
 * Statt der Theme-Datei die eigene laden.
 *
 * WordPress sucht Seitenvorlagen nur im Theme. Steht im Meta unsere
 * Datei, liefert es deshalb die Standardvorlage — hier wird sie wieder
 * ersetzt.
 *
 * @param string $vorlage Von WordPress gewählte Datei.
 */
function einsetzen( string $vorlage ): string {
	if ( ! is_singular() ) {
		return $vorlage;
	}

	$id = get_queried_object_id();

	if ( 0 === $id ) {
		return $vorlage;
	}

	if ( DATEI !== get_post_meta( $id, '_wp_page_template', true ) ) {
		return $vorlage;
	}

	$eigene = plugin_dir_path( dirname( __FILE__ ) ) . DATEI;

	return is_readable( $eigene ) ? $eigene : $vorlage;
}
add_filter( 'template_include', __NAMESPACE__ . '\einsetzen', 99 );

/**
 * Prüfen, ob die aktuelle Anfrage über unsere Vorlage läuft.
 */
function ist_aktiv(): bool {
	if ( ! is_singular() ) {
		return false;
	}

	return DATEI === get_post_meta( get_queried_object_id(), '_wp_page_template', true );
}
