<?php
/**
 * Wohnungen und Team im Frontend.
 *
 * Ohne Elementor Pro gibt es kein Loop Grid — alles, was Beiträge
 * ausgibt, kommt von hier. Die Auswahl- und Sortierlogik liegt in
 * includes/wohnungen-logik.php und ist dort getestet; diese Datei
 * übersetzt nur zwischen WordPress und jenen Funktionen.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Ansicht;

use RealNorth\PostTypes;
use RealNorth\Wohnungen;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Die Filternamen in der Adresszeile.
 */
const PARAM_ORT    = 'ort';
const PARAM_ZIMMER = 'zimmer';

/**
 * Einen Beitrag in die Struktur übersetzen, mit der die Logik arbeitet.
 *
 * Rein, ohne WordPress-Aufruf — die Meta-Werte kommen von aussen herein,
 * damit die Zuordnung testbar bleibt.
 *
 * @param string               $titel Beitragstitel.
 * @param array<string, mixed> $meta  Meta-Werte des Beitrags.
 * @return array<string, mixed>
 */
function meta_zu_wohnung( string $titel, array $meta ): array {
	return array(
		'objekt'  => $titel,
		'ort'     => trim( (string) ( $meta['rn_ort'] ?? '' ) ),
		'zimmer'  => trim( (string) ( $meta['rn_zimmer'] ?? '' ) ),
		'flaeche' => (int) ( $meta['rn_flaeche'] ?? 0 ),
		'frei'    => trim( (string) ( $meta['rn_frei'] ?? '' ) ),
		'miete'   => (int) ( $meta['rn_miete'] ?? 0 ),
		'status'  => trim( (string) ( $meta['rn_status'] ?? '' ) ),
	);
}

/**
 * Die Klasse für eine Status-Pille.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar. Ein unbekannter Status
 * bekommt die neutrale Pille statt einer kaputten Klasse.
 *
 * @param string $status Status der Wohnung.
 */
function pille_klasse( string $status ): string {
	$karte = array(
		'Frei'        => 'frei',
		'Reserviert'  => 'reserviert',
		'Vormerkung'  => 'vormerkung',
	);

	$name = $karte[ trim( $status ) ] ?? '';

	return '' === $name ? 'rn-pill' : 'rn-pill rn-pill--' . $name;
}

/**
 * Alle veröffentlichten Wohnungen, sortiert.
 *
 * @return array<int, array<string, mixed>>
 */
function alle_wohnungen(): array {
	$beitraege = get_posts(
		array(
			'post_type'      => PostTypes\WOHNUNG,
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	$wohnungen = array();

	foreach ( $beitraege as $beitrag ) {
		$meta = array();

		foreach ( array( 'rn_ort', 'rn_zimmer', 'rn_flaeche', 'rn_frei', 'rn_miete', 'rn_status' ) as $feld ) {
			$meta[ $feld ] = get_post_meta( $beitrag->ID, $feld, true );
		}

		$wohnung             = meta_zu_wohnung( $beitrag->post_title, $meta );
		$wohnung['id']       = $beitrag->ID;
		$wohnung['url']      = (string) get_permalink( $beitrag );
		$wohnungen[]         = $wohnung;
	}

	return Wohnungen\sortieren( $wohnungen );
}

/**
 * Den Filter aus der Adresszeile lesen.
 *
 * Nur lesender Zugriff auf GET-Parameter, deshalb keine Nonce: eine
 * Filterauswahl ist kein Zustandswechsel, und die Werte werden gegen
 * die vorhandenen Orte geprüft.
 *
 * @return array<string, string>
 */
function filter_aus_anfrage(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nur Lesen, kein Zustandswechsel.
	$ort    = isset( $_GET[ PARAM_ORT ] ) ? sanitize_text_field( wp_unslash( $_GET[ PARAM_ORT ] ) ) : 'alle';
	$zimmer = isset( $_GET[ PARAM_ZIMMER ] ) ? sanitize_text_field( wp_unslash( $_GET[ PARAM_ZIMMER ] ) ) : 'alle';
	// phpcs:enable

	return array( 'ort' => $ort, 'zimmer' => $zimmer );
}

/**
 * Die Adresse einer Filterauswahl.
 *
 * @param array<string, string> $filter Aktueller Filter.
 * @param string                $feld   Zu änderndes Feld.
 * @param string                $wert   Neuer Wert.
 */
function filter_url( array $filter, string $feld, string $wert ): string {
	$neu = array_merge( $filter, array( $feld => $wert ) );
	$neu = array_filter( $neu, static fn( string $v ): bool => 'alle' !== $v );

	$basis = remove_query_arg( array( PARAM_ORT, PARAM_ZIMMER ) );

	return array() === $neu ? $basis : add_query_arg( $neu, $basis );
}

/**
 * Die Filterleiste.
 *
 * @param array<int, array<string, mixed>> $wohnungen Alle Wohnungen.
 * @param array<string, string>            $filter    Aktueller Filter.
 * @param int                              $treffer   Anzahl Treffer.
 */
function filterleiste( array $wohnungen, array $filter, int $treffer ): string {
	$orte   = array_merge( array( 'alle' ), Wohnungen\orte( $wohnungen ) );
	$zimmer = array( 'alle', '2.5', '3.5', '4.5+' );

	$reihe = static function ( array $werte, string $feld, array $filter ): string {
		$html = '';

		foreach ( $werte as $wert ) {
			$aktiv  = ( $filter[ $feld ] ?? 'alle' ) === $wert;
			$text   = 'alle' === $wert ? __( 'Alle', 'realnorth' ) : $wert;
			$html  .= sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( filter_url( $filter, $feld, $wert ) ),
				$aktiv ? ' aria-current="true"' : '',
				esc_html( $text )
			);
		}

		return $html;
	};

	return sprintf(
		'<div class="rn-filter">'
			. '<div><div class="rn-filter__titel">%s</div><div class="rn-filter__reihe">%s</div></div>'
			. '<div><div class="rn-filter__titel">%s</div><div class="rn-filter__reihe">%s</div></div>'
			. '<div class="rn-filter__ergebnis">%s</div>'
		. '</div>',
		esc_html__( 'Standort', 'realnorth' ),
		$reihe( $orte, PARAM_ORT, $filter ),
		esc_html__( 'Zimmer', 'realnorth' ),
		$reihe( $zimmer, PARAM_ZIMMER, $filter ),
		esc_html(
			sprintf(
				/* translators: %d: Anzahl Treffer */
				_n( '%d Wohnung gefunden', '%d Wohnungen gefunden', $treffer, 'realnorth' ),
				$treffer
			)
		)
	);
}

/**
 * Eine Wohnung als Karte.
 *
 * @param array<string, mixed> $w Datensatz.
 */
function karte( array $w ): string {
	$bild = has_post_thumbnail( (int) $w['id'] )
		? '<figure class="rn-wohnung__bild">' . get_the_post_thumbnail( (int) $w['id'], 'medium_large' ) . '</figure>'
		: '';

	return sprintf(
		'<article class="rn-wohnung">%s<div class="rn-wohnung__ort">%s</div><h3>%s</h3>'
			. '<dl class="rn-wohnung__daten">'
			. '<dt>%s</dt><dd>%s</dd>'
			. '<dt>%s</dt><dd>%s</dd>'
			. '<dt>%s</dt><dd>%s</dd>'
			. '<dt>%s</dt><dd>%s</dd>'
			. '</dl>'
			. '<a class="rn-knopf rn-knopf--leise rn-knopf--breit" href="%s">%s</a></article>',
		$bild,
		esc_html( (string) $w['ort'] ),
		esc_html( (string) $w['objekt'] ),
		esc_html__( 'Zimmer', 'realnorth' ),
		esc_html( Wohnungen\format_zimmer( (string) $w['zimmer'] ) ),
		esc_html__( 'Fläche', 'realnorth' ),
		esc_html( Wohnungen\format_flaeche( (int) $w['flaeche'] ) ),
		esc_html__( 'Verfügbar', 'realnorth' ),
		esc_html( Wohnungen\format_frei( (string) $w['frei'] ) ),
		esc_html__( 'Miete brutto', 'realnorth' ),
		esc_html( Wohnungen\format_miete( (int) $w['miete'] ) ),
		esc_url( (string) $w['url'] ),
		esc_html__( 'Details und Bewerbung', 'realnorth' )
	);
}

/**
 * Wohnungen als Tabelle.
 *
 * @param array<int, array<string, mixed>> $wohnungen Datensätze.
 */
function tabelle( array $wohnungen ): string {
	$kopf = array(
		__( 'Objekt', 'realnorth' ),
		__( 'Standort', 'realnorth' ),
		__( 'Zimmer', 'realnorth' ),
		__( 'Fläche', 'realnorth' ),
		__( 'Verfügbar', 'realnorth' ),
		__( 'Miete brutto', 'realnorth' ),
		__( 'Status', 'realnorth' ),
	);

	$html = '<table class="rn-tabelle"><thead><tr>';

	foreach ( $kopf as $spalte ) {
		$html .= '<th scope="col">' . esc_html( $spalte ) . '</th>';
	}

	$html .= '</tr></thead><tbody>';

	foreach ( $wohnungen as $w ) {
		$html .= sprintf(
			'<tr><td><a href="%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>'
				. '<td><span class="%s">%s</span></td></tr>',
			esc_url( (string) $w['url'] ),
			esc_html( (string) $w['objekt'] ),
			esc_html( (string) $w['ort'] ),
			esc_html( Wohnungen\format_zimmer( (string) $w['zimmer'] ) ),
			esc_html( Wohnungen\format_flaeche( (int) $w['flaeche'] ) ),
			esc_html( Wohnungen\format_frei( (string) $w['frei'] ) ),
			esc_html( Wohnungen\format_miete( (int) $w['miete'] ) ),
			esc_attr( pille_klasse( (string) $w['status'] ) ),
			esc_html( (string) $w['status'] )
		);
	}

	return $html . '</tbody></table>';
}

/**
 * [rn_wohnungen darstellung="karten" anzahl="3" filter="nein"]
 *
 * @param array<string, string>|string $attr Attribute.
 */
function wohnungen( $attr ): string {
	$a = shortcode_atts(
		array(
			'darstellung' => 'karten',
			'anzahl'      => '0',
			'filter'      => 'nein',
		),
		$attr,
		'rn_wohnungen'
	);

	$alle   = alle_wohnungen();
	$filter = 'ja' === strtolower( $a['filter'] ) ? filter_aus_anfrage() : array( 'ort' => 'alle', 'zimmer' => 'alle' );
	$treffer = Wohnungen\filtern( $alle, $filter );

	$html = 'ja' === strtolower( $a['filter'] )
		? filterleiste( $alle, $filter, count( $treffer ) )
		: '';

	$anzahl = (int) $a['anzahl'];

	if ( $anzahl > 0 ) {
		$treffer = array_slice( $treffer, 0, $anzahl );
	}

	if ( array() === $treffer ) {
		return $html . '<p class="rn-tabelle__leer">' . esc_html__(
			'Für diese Auswahl ist derzeit nichts frei. Ein Suchabo meldet neue Objekte, sobald sie ausgeschrieben werden.',
			'realnorth'
		) . '</p>';
	}

	if ( 'tabelle' === strtolower( $a['darstellung'] ) ) {
		return $html . tabelle( $treffer );
	}

	$karten = '';

	foreach ( $treffer as $w ) {
		$karten .= karte( $w );
	}

	return $html . '<div class="rn-raster rn-raster--drei">' . $karten . '</div>';
}
add_shortcode( 'rn_wohnungen', __NAMESPACE__ . '\wohnungen' );

/**
 * [rn_wohnungszahl] — die Zahl der freien Wohnungen, für Fliesstext.
 */
function wohnungszahl(): string {
	$frei = Wohnungen\filtern( alle_wohnungen(), array( 'ort' => 'alle', 'zimmer' => 'alle' ) );

	return (string) count( $frei );
}
add_shortcode( 'rn_wohnungszahl', __NAMESPACE__ . '\wohnungszahl' );

/**
 * [rn_team] — das Team als Raster.
 *
 * @param array<string, string>|string $attr Attribute.
 */
function team( $attr ): string {
	$a = shortcode_atts( array( 'spalten' => '4' ), $attr, 'rn_team' );

	$mitglieder = get_posts(
		array(
			'post_type'      => PostTypes\TEAM,
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'meta_key'       => 'rn_reihenfolge', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- kleine, feste Menge.
			'orderby'        => 'meta_value_num title',
			'order'          => 'ASC',
		)
	);

	if ( array() === $mitglieder ) {
		return '';
	}

	$html = '';

	foreach ( $mitglieder as $m ) {
		$portrait = has_post_thumbnail( $m->ID )
			? get_the_post_thumbnail( $m->ID, 'medium_large' )
			: '';

		$html .= sprintf(
			'<div><figure class="rn-team-portrait">%s</figure><h3>%s</h3><div class="rn-team__rolle">%s</div></div>',
			$portrait,
			esc_html( $m->post_title ),
			esc_html( (string) get_post_meta( $m->ID, 'rn_rolle', true ) )
		);
	}

	return sprintf(
		'<div class="rn-team %s">%s</div>',
		esc_attr( \RealNorth\Abschnitte\raster_klasse( $a['spalten'] ) ),
		$html
	);
}
add_shortcode( 'rn_team', __NAMESPACE__ . '\team' );

/**
 * [rn_formular id="12" titel="…"]
 *
 * Formulare baut WPForms, nicht dieses Plugin: Versand, Spamschutz und
 * Datenhaltung sind dort gelöst und müssten hier neu gebaut werden.
 * Fehlt die ID, steht ein Hinweis da statt eines toten Formulars.
 *
 * @param array<string, string>|string $attr Attribute.
 */
function formular( $attr ): string {
	$a = shortcode_atts( array( 'id' => '', 'titel' => '' ), $attr, 'rn_formular' );

	$id = (int) $a['id'];

	if ( $id > 0 && shortcode_exists( 'wpforms' ) ) {
		return '<div class="rn-formular">' . do_shortcode( '[wpforms id="' . $id . '"]' ) . '</div>';
	}

	return sprintf(
		'<div class="rn-formular"><p><strong>%s</strong></p><p>%s</p></div>',
		esc_html( '' !== $a['titel'] ? $a['titel'] : __( 'Formular', 'realnorth' ) ),
		esc_html__(
			'Hier gehört das WPForms-Formular hin: Formular in WPForms anlegen und dessen ID im Shortcode rn_formular eintragen.',
			'realnorth'
		)
	);
}
add_shortcode( 'rn_formular', __NAMESPACE__ . '\formular' );
