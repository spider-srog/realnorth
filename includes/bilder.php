<?php
/**
 * Bildplätze.
 *
 * Der Entwurf bringt Fotos als eingebettete Daten mit. Im Repo haben sie
 * nichts verloren: es wird direkt in den Plugin-Ordner der Live-Seite
 * deployt, ist öffentlich, und jedes Bild läge dann zweimal auf dem
 * Server. Die Bilder gehören in die Mediathek.
 *
 * Damit die Abschnitte trotzdem wissen, welches Bild wohin gehört, gibt
 * es benannte Plätze. Unter «Design -> Bilder» wird jedem Platz eine
 * Datei aus der Mediathek zugewiesen. Ist noch keine da, erscheint ein
 * beschrifteter Platzhalter statt einer leeren Fläche — ein fehlendes
 * Bild soll auffallen.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Bilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Name der Option, unter der die Zuordnung Platz -> Anhang-ID liegt.
 */
const OPTION = 'realnorth_bilder';

/**
 * Alle Plätze mit Beschriftung und Alternativtext.
 *
 * Der Alternativtext ist der aus dem Entwurf. Er wird nur benutzt, wenn
 * der Anhang in der Mediathek selber keinen hat — dort gepflegt ist er
 * besser, weil er dann auch anderswo stimmt.
 *
 * @return array<string, array{titel: string, alt: string}>
 */
function plaetze(): array {
	return array(
		'logo'            => array(
			'titel' => 'Wortmarke hell (Kopf- und Fusszeile)',
			'alt'   => 'realnorth',
		),
		'baer'            => array(
			'titel' => 'Eisbär (Wasserzeichen Fusszeile)',
			'alt'   => '',
		),
		'buehne'          => array(
			'titel' => 'Startseite: Bühnenbild',
			'alt'   => 'Architekturvisualisierung des Projekts Im Birkenhain in Rudolfstetten',
		),
		'birkenhain'      => array(
			'titel' => 'Im Birkenhain: Übersicht',
			'alt'   => 'Visualisierung des Quartiers Im Birkenhain am Isleren-Wald',
		),
		'birkenhain-gasse' => array(
			'titel' => 'Im Birkenhain: Quartierstrasse',
			'alt'   => 'Quartierstrasse Im Birkenhain',
		),
		'birkenhain-pool' => array(
			'titel' => 'Im Birkenhain: Naturpool',
			'alt'   => 'Naturpool und Liegewiese am Waldrand',
		),
		'birkenhain-dach' => array(
			'titel' => 'Im Birkenhain: Dachgarten',
			'alt'   => 'Dachgarten mit Weitblick',
		),
		'standortkarte'   => array(
			'titel' => 'Liegenschaften: Standortkarte',
			'alt'   => 'Karte der Standorte Mutschellen, Zürich und Winterthur',
		),
		'kontaktkarte'    => array(
			'titel' => 'Kontakt: Karte Hauptsitz',
			'alt'   => 'Karte des Hauptsitzes in Baar',
		),
		'ueber-uns'       => array(
			'titel' => 'Über uns: Bild im Kopf',
			'alt'   => '',
		),
		'rennweg-schnitt' => array(
			'titel' => 'Rennweg 14/16: Gebäudeschnitt',
			'alt'   => 'Gebäudeschnitt Rennweg 14/16 als Illustration in Schwarz, Weiss und Grau',
		),
		'rennweg-residenz' => array(
			'titel' => 'Rennweg 14/16: Residenzen',
			'alt'   => 'Wohnbereich der Luxury Residences mit grünen Samtsofas',
		),
	);
}

/**
 * Zugewiesene Anhang-IDs.
 *
 * @return array<string, int>
 */
function zuordnung(): array {
	$roh = get_option( OPTION, array() );

	if ( ! is_array( $roh ) ) {
		return array();
	}

	$sauber = array();

	foreach ( plaetze() as $platz => $_ ) {
		$id = (int) ( $roh[ $platz ] ?? 0 );
		if ( $id > 0 ) {
			$sauber[ $platz ] = $id;
		}
	}

	return $sauber;
}

/**
 * Die Anhang-ID eines Platzes, oder 0.
 *
 * @param string $platz Name des Platzes.
 */
function id( string $platz ): int {
	return zuordnung()[ $platz ] ?? 0;
}

/**
 * Ein Bild ausgeben.
 *
 * Fehlt die Zuweisung, kommt ein beschrifteter Platzhalter zurück statt
 * einer leeren Fläche.
 *
 * @param string $platz  Name des Platzes.
 * @param string $klasse CSS-Klasse für das <img>.
 * @param string $groesse Bildgrösse im Sinne von WordPress.
 */
function bild( string $platz, string $klasse = '', string $groesse = 'large' ): string {
	$plaetze = plaetze();

	if ( ! isset( $plaetze[ $platz ] ) ) {
		return '';
	}

	$id = id( $platz );

	if ( 0 === $id ) {
		return platzhalter( $plaetze[ $platz ]['titel'] );
	}

	$attribute = array( 'loading' => 'lazy', 'decoding' => 'async' );

	if ( '' !== $klasse ) {
		$attribute['class'] = $klasse;
	}

	$vorhandenes_alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );

	if ( '' === trim( $vorhandenes_alt ) ) {
		$attribute['alt'] = $plaetze[ $platz ]['alt'];
	}

	$html = wp_get_attachment_image( $id, $groesse, false, $attribute );

	return '' !== $html ? $html : platzhalter( $plaetze[ $platz ]['titel'] );
}

/**
 * Platzhalter für einen noch leeren Platz.
 *
 * @param string $titel Beschriftung.
 */
function platzhalter( string $titel ): string {
	return sprintf(
		'<div class="rn-bildplatz"><span>%s<br>%s</span></div>',
		esc_html( $titel ),
		esc_html__( 'Bild unter Design → Bilder zuweisen', 'realnorth' )
	);
}

/**
 * Menüpunkt unter «Design».
 */
function add_admin_page(): void {
	add_submenu_page(
		'themes.php',
		__( 'realnorth: Bilder', 'realnorth' ),
		__( 'Bilder', 'realnorth' ),
		'manage_options',
		'realnorth-bilder',
		__NAMESPACE__ . '\render_admin_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\add_admin_page' );

/**
 * Die Zuweisungsseite.
 */
function render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'realnorth' ) );
	}

	$meldung = '';

	if ( isset( $_POST['realnorth_bilder_speichern'] )
		&& check_admin_referer( 'realnorth_bilder', 'realnorth_bilder_nonce' ) ) {
		$neu = array();

		foreach ( plaetze() as $platz => $_ ) {
			$feld = 'rn_bild_' . $platz;
			$wert = isset( $_POST[ $feld ] ) ? absint( wp_unslash( $_POST[ $feld ] ) ) : 0;
			if ( $wert > 0 ) {
				$neu[ $platz ] = $wert;
			}
		}

		update_option( OPTION, $neu );
		$meldung = __( 'Gespeichert.', 'realnorth' );
	}

	$aktuell = zuordnung();

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'realnorth: Bilder', 'realnorth' ) . '</h1>';

	if ( '' !== $meldung ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $meldung ) . '</p></div>';
	}

	echo '<p>' . esc_html__(
		'Jeder Platz im Layout bekommt hier eine Datei aus der Mediathek. Die Zahl ist die ID des Anhangs — sie steht in der Mediathek in der Adresszeile (post=…). Ohne Zuweisung zeigt die Seite einen beschrifteten Platzhalter.',
		'realnorth'
	) . '</p>';

	echo '<form method="post"><table class="form-table" role="presentation"><tbody>';

	foreach ( plaetze() as $platz => $daten ) {
		$id  = $aktuell[ $platz ] ?? 0;
		$url = $id > 0 ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';

		echo '<tr>';
		echo '<th scope="row"><label for="rn_bild_' . esc_attr( $platz ) . '">' . esc_html( $daten['titel'] ) . '</label></th>';
		echo '<td>';
		echo '<input type="number" min="0" step="1" class="small-text" id="rn_bild_' . esc_attr( $platz ) . '" name="rn_bild_' . esc_attr( $platz ) . '" value="' . esc_attr( (string) $id ) . '"> ';
		echo '<code>' . esc_html( $platz ) . '</code>';

		if ( '' !== (string) $url ) {
			echo '<br><img src="' . esc_url( (string) $url ) . '" alt="" style="margin-top:8px;max-width:150px;height:auto">';
		}

		echo '</td></tr>';
	}

	echo '</tbody></table>';
	wp_nonce_field( 'realnorth_bilder', 'realnorth_bilder_nonce' );
	submit_button( __( 'Speichern', 'realnorth' ), 'primary', 'realnorth_bilder_speichern' );
	echo '</form></div>';
}
