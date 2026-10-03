<?php
/**
 * Die Bausteine des Entwurfs als Shortcodes.
 *
 * Der Entwurf «Realnorth — Bewirtschaftung & Entwicklung» besteht aus
 * einer überschaubaren Zahl wiederkehrender Teile: Band, Raster, Bühne,
 * nummerierte Spalte, Kennzahl, Karte, Merkmalsliste, Zeitstrahl,
 * Aufruf. Hier sind sie einzeln ansprechbar.
 *
 * Warum so und nicht im Builder: ohne Elementor Pro gibt es weder Theme
 * Builder noch Loop Grid; alles, was Daten zeigt, müsste ohnehin von
 * hier kommen. Und was im Builder steckt, liegt in der Datenbank und
 * lässt sich weder reviewen noch zurückrollen. Der Text dagegen bleibt
 * in WordPress — er steht im Seiteninhalt zwischen den Shortcodes und
 * ist dort ganz normal bearbeitbar.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Abschnitte;

use RealNorth\Bilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Erlaubte Gründe eines Bandes.
 */
const GRUENDE = array( 'flaeche', 'weiss', 'ruhig', 'dunkel' );

/**
 * Die Klasse für einen Bandgrund.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar. Ein unbekannter Wert
 * fällt auf die Grundfläche zurück, statt eine kaputte Klasse zu
 * erzeugen.
 *
 * @param string $grund Name des Grundes.
 */
function band_klasse( string $grund ): string {
	$grund = strtolower( trim( $grund ) );

	if ( ! in_array( $grund, GRUENDE, true ) || 'flaeche' === $grund ) {
		return 'rn-band';
	}

	return 'rn-band rn-band--' . $grund;
}

/**
 * Die Klasse für ein Raster.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar.
 *
 * @param string $spalten Spaltenzahl oder 'split'.
 */
function raster_klasse( string $spalten ): string {
	$namen = array(
		'2'     => 'zwei',
		'3'     => 'drei',
		'4'     => 'vier',
		'5'     => 'fuenf',
		'split' => 'split',
	);

	$spalten = strtolower( trim( $spalten ) );

	return isset( $namen[ $spalten ] )
		? 'rn-raster rn-raster--' . $namen[ $spalten ]
		: 'rn-raster rn-raster--drei';
}

/**
 * Die Klasse für einen Knopf.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar.
 *
 * @param string $stil 'leise' für die zurückhaltende Variante.
 */
function knopf_klasse( string $stil ): string {
	return 'leise' === strtolower( trim( $stil ) ) ? 'rn-knopf rn-knopf--leise' : 'rn-knopf';
}

/**
 * Eingeschlossenen Text aufbereiten.
 *
 * Auf den Vorlagenseiten ist wpautop abgeschaltet (siehe unten), damit
 * die Shortcodes nicht in leeren Absätzen landen. Innerhalb eines
 * Bausteins ist es aber erwünscht: dort steht Fliesstext.
 *
 * @param string|null $inhalt Roher Inhalt.
 */
function absatz( ?string $inhalt ): string {
	$inhalt = trim( (string) $inhalt );

	return '' === $inhalt ? '' : wpautop( do_shortcode( $inhalt ) );
}

/**
 * Einen Knopf bauen — intern benutzt von mehreren Bausteinen.
 *
 * @param string $text Beschriftung.
 * @param string $url  Ziel.
 * @param string $stil 'laut' oder 'leise'.
 */
function knopf_html( string $text, string $url, string $stil = 'laut' ): string {
	if ( '' === trim( $text ) ) {
		return '';
	}

	$extern = str_starts_with( $url, 'http' ) && ! str_contains( $url, (string) wp_parse_url( home_url(), PHP_URL_HOST ) );

	return sprintf(
		'<a class="%s" href="%s"%s>%s</a>',
		esc_attr( knopf_klasse( $stil ) ),
		esc_url( '' === $url ? '#' : $url ),
		$extern ? ' target="_blank" rel="noopener"' : '',
		esc_html( $text )
	);
}

/* ---------- Band und Raster ---------- */

/**
 * [rn_band grund="ruhig"]…[/rn_band]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function band( $attr, ?string $inhalt = null ): string {
	$a = shortcode_atts(
		array(
			'grund'  => 'flaeche',
			'klasse' => '',
			'luft'   => '',
			'id'     => '',
		),
		$attr,
		'rn_band'
	);

	$klassen = band_klasse( $a['grund'] );

	if ( '' !== trim( $a['klasse'] ) ) {
		$klassen .= ' ' . sanitize_html_class( $a['klasse'] );
	}

	return sprintf(
		'<section class="%s"%s><div class="rn-wrap%s">%s</div></section>',
		esc_attr( $klassen ),
		'' !== $a['id'] ? ' id="' . esc_attr( sanitize_title( $a['id'] ) ) . '"' : '',
		'schmal' === $a['luft'] ? ' rn-wrap--schmal' : '',
		do_shortcode( (string) $inhalt )
	);
}
add_shortcode( 'rn_band', __NAMESPACE__ . '\band' );

/**
 * [rn_raster spalten="3"]…[/rn_raster]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function raster( $attr, ?string $inhalt = null ): string {
	$a = shortcode_atts( array( 'spalten' => '3' ), $attr, 'rn_raster' );

	return sprintf(
		'<div class="%s">%s</div>',
		esc_attr( raster_klasse( $a['spalten'] ) ),
		do_shortcode( (string) $inhalt )
	);
}
add_shortcode( 'rn_raster', __NAMESPACE__ . '\raster' );

/**
 * [rn_spalte]…[/rn_spalte] — eine freie Spalte im Raster.
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function spalte( $attr, ?string $inhalt = null ): string {
	return '<div>' . do_shortcode( (string) $inhalt ) . '</div>';
}
add_shortcode( 'rn_spalte', __NAMESPACE__ . '\spalte' );

/* ---------- Kopfteile ---------- */

/**
 * [rn_kopf kicker="…" titel="…" lead="…"] — der Einstieg einer Seite.
 *
 * @param array<string, string>|string $attr Attribute.
 */
function seitenkopf( $attr ): string {
	$a = shortcode_atts(
		array(
			'kicker' => '',
			'titel'  => '',
			'lead'   => '',
			'stufe'  => 'h1',
		),
		$attr,
		'rn_kopf'
	);

	$stufe = in_array( $a['stufe'], array( 'h1', 'h2' ), true ) ? $a['stufe'] : 'h1';
	$html  = '';

	if ( '' !== trim( $a['kicker'] ) ) {
		$html .= '<div class="rn-kicker">' . esc_html( $a['kicker'] ) . '</div>';
	}

	if ( '' !== trim( $a['titel'] ) ) {
		$html .= sprintf( '<%1$s>%2$s</%1$s>', $stufe, wp_kses_post( $a['titel'] ) );
	}

	if ( '' !== trim( $a['lead'] ) ) {
		$html .= '<p class="rn-lead">' . wp_kses_post( $a['lead'] ) . '</p>';
	}

	return $html;
}
add_shortcode( 'rn_kopf', __NAMESPACE__ . '\seitenkopf' );

/**
 * [rn_abschnittskopf titel="…" linktext="…" url="…"]
 *
 * @param array<string, string>|string $attr Attribute.
 */
function abschnittskopf( $attr ): string {
	$a = shortcode_atts(
		array(
			'titel'    => '',
			'linktext' => '',
			'url'      => '',
		),
		$attr,
		'rn_abschnittskopf'
	);

	$link = '';

	if ( '' !== trim( $a['linktext'] ) ) {
		$link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( '' === $a['url'] ? '#' : $a['url'] ),
			esc_html( $a['linktext'] )
		);
	}

	return sprintf(
		'<div class="rn-abschnittskopf"><h2>%s</h2>%s</div>',
		wp_kses_post( $a['titel'] ),
		$link
	);
}
add_shortcode( 'rn_abschnittskopf', __NAMESPACE__ . '\abschnittskopf' );

/* ---------- Bühne ---------- */

/**
 * [rn_buehne kicker="…" titel="…" lead="…" bild="buehne" …]
 *
 * Der Titel darf <br> und <span class="rn-buehne__akzent"> enthalten —
 * im Entwurf steht die dritte Zeile in der helleren Farbe.
 *
 * @param array<string, string>|string $attr Attribute.
 */
function buehne( $attr ): string {
	$a = shortcode_atts(
		array(
			'kicker'    => '',
			'titel'     => '',
			'lead'      => '',
			'bild'      => 'buehne',
			'bildtitel' => '',
			'bildzeile' => '',
			'knopf1'    => '',
			'url1'      => '',
			'knopf2'    => '',
			'url2'      => '',
		),
		$attr,
		'rn_buehne'
	);

	$text = '<div>';

	if ( '' !== trim( $a['kicker'] ) ) {
		$text .= '<div class="rn-kicker">' . esc_html( $a['kicker'] ) . '</div>';
	}

	$text .= '<h1>' . wp_kses_post( $a['titel'] ) . '</h1>';

	if ( '' !== trim( $a['lead'] ) ) {
		$text .= '<p class="rn-lead">' . wp_kses_post( $a['lead'] ) . '</p>';
	}

	$knoepfe = knopf_html( $a['knopf1'], $a['url1'] ) . knopf_html( $a['knopf2'], $a['url2'], 'leise' );

	if ( '' !== $knoepfe ) {
		$text .= '<div class="rn-knopfreihe">' . $knoepfe . '</div>';
	}

	$text .= '</div>';

	$zeile = '';

	if ( '' !== trim( $a['bildtitel'] ) || '' !== trim( $a['bildzeile'] ) ) {
		$zeile = sprintf(
			'<figcaption><span>%s</span><span>%s</span></figcaption>',
			esc_html( $a['bildtitel'] ),
			esc_html( $a['bildzeile'] )
		);
	}

	$bild = sprintf(
		'<figure class="rn-buehne__bild">%s%s</figure>',
		Bilder\bild( $a['bild'], '', 'full' ),
		$zeile
	);

	return '<div class="rn-buehne__raster">' . $text . $bild . '</div>';
}
add_shortcode( 'rn_buehne', __NAMESPACE__ . '\buehne' );

/* ---------- Einzelteile ---------- */

/**
 * [rn_schritt nummer="01" titel="…"]Text[/rn_schritt]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function schritt( $attr, ?string $inhalt = null ): string {
	$a = shortcode_atts( array( 'nummer' => '', 'titel' => '' ), $attr, 'rn_schritt' );

	$nummer = '' !== trim( $a['nummer'] )
		? '<div class="rn-schritt__nummer">' . esc_html( $a['nummer'] ) . '</div>'
		: '';

	return sprintf(
		'<div class="rn-schritt">%s<h3>%s</h3>%s</div>',
		$nummer,
		esc_html( $a['titel'] ),
		absatz( $inhalt )
	);
}
add_shortcode( 'rn_schritt', __NAMESPACE__ . '\schritt' );

/**
 * Das Attribut, mit dem JavaScript eine Kennzahl hochzählt.
 *
 * Hochgezählt wird nur, was mit einer ganzen Zahl anfängt. «2.5–5.5»
 * oder «39 %» bleiben stehen — eine animierte Spanne ergibt keinen Sinn,
 * und ein Prozentwert zählt sich nicht von null hoch an.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar.
 *
 * @param string $wert Angezeigter Wert.
 */
function zaehlwert( string $wert ): string {
	$wert = trim( $wert );

	if ( 1 !== preg_match( '/^(\d+)(\s|$)/', $wert, $treffer ) ) {
		return '';
	}

	return ' data-rn-zahl="' . esc_attr( $treffer[1] ) . '"';
}

/**
 * [rn_zahl wert="250" text="Wohnungen im Bestand"]
 *
 * @param array<string, string>|string $attr Attribute.
 */
function zahl( $attr ): string {
	$a = shortcode_atts( array( 'wert' => '', 'text' => '' ), $attr, 'rn_zahl' );

	return sprintf(
		'<div class="rn-zahl"><div class="rn-zahl__wert"%s>%s</div><div class="rn-zahl__text">%s</div></div>',
		zaehlwert( $a['wert'] ),
		esc_html( $a['wert'] ),
		esc_html( $a['text'] )
	);
}
add_shortcode( 'rn_zahl', __NAMESPACE__ . '\zahl' );

/**
 * [rn_karte kicker="01" titel="…" knopf="…" url="…"]Text[/rn_karte]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function karte( $attr, ?string $inhalt = null ): string {
	$a = shortcode_atts(
		array(
			'kicker' => '',
			'titel'  => '',
			'knopf'  => '',
			'url'    => '',
			'stil'   => 'leise',
		),
		$attr,
		'rn_karte'
	);

	$kicker = '' !== trim( $a['kicker'] )
		? '<div class="rn-karte__kicker">' . esc_html( $a['kicker'] ) . '</div>'
		: '';

	return sprintf(
		'<div class="rn-karte">%s<h3>%s</h3>%s%s</div>',
		$kicker,
		esc_html( $a['titel'] ),
		absatz( $inhalt ),
		knopf_html( $a['knopf'], $a['url'], $a['stil'] )
	);
}
add_shortcode( 'rn_karte', __NAMESPACE__ . '\karte' );

/**
 * [rn_merkmale]…[/rn_merkmale] mit [rn_merkmal begriff="…" wert="…"]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function merkmale( $attr, ?string $inhalt = null ): string {
	return '<div class="rn-merkmale">' . do_shortcode( (string) $inhalt ) . '</div>';
}
add_shortcode( 'rn_merkmale', __NAMESPACE__ . '\merkmale' );

/**
 * [rn_merkmal begriff="Gemeinde" wert="…"]
 *
 * @param array<string, string>|string $attr Attribute.
 */
function merkmal( $attr ): string {
	$a = shortcode_atts( array( 'begriff' => '', 'wert' => '' ), $attr, 'rn_merkmal' );

	return sprintf(
		'<div><span>%s</span><span>%s</span></div>',
		esc_html( $a['begriff'] ),
		wp_kses_post( $a['wert'] )
	);
}
add_shortcode( 'rn_merkmal', __NAMESPACE__ . '\merkmal' );

/**
 * [rn_ablauf]…[/rn_ablauf] mit [rn_etappe zeit="…" jetzt="ja"]Text[/rn_etappe]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function ablauf( $attr, ?string $inhalt = null ): string {
	return '<ol class="rn-ablauf">' . do_shortcode( (string) $inhalt ) . '</ol>';
}
add_shortcode( 'rn_ablauf', __NAMESPACE__ . '\ablauf' );

/**
 * [rn_etappe zeit="12.2025" jetzt="ja"]Text[/rn_etappe]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function etappe( $attr, ?string $inhalt = null ): string {
	$a = shortcode_atts( array( 'zeit' => '', 'jetzt' => '' ), $attr, 'rn_etappe' );

	$jetzt = 'ja' === strtolower( trim( $a['jetzt'] ) );

	return sprintf(
		'<li><span>%s</span><span%s>%s</span></li>',
		esc_html( $a['zeit'] ),
		$jetzt ? ' class="rn-ablauf__jetzt"' : '',
		wp_kses_post( trim( (string) $inhalt ) )
	);
}
add_shortcode( 'rn_etappe', __NAMESPACE__ . '\etappe' );

/**
 * [rn_aufruf titel="…" knopf="…" url="…"]Text[/rn_aufruf]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function aufruf( $attr, ?string $inhalt = null ): string {
	$a = shortcode_atts(
		array(
			'titel' => '',
			'knopf' => '',
			'url'   => '',
		),
		$attr,
		'rn_aufruf'
	);

	return sprintf(
		'<div class="rn-aufruf"><div><h2>%s</h2>%s</div>%s</div>',
		esc_html( $a['titel'] ),
		absatz( $inhalt ),
		knopf_html( $a['knopf'], $a['url'] )
	);
}
add_shortcode( 'rn_aufruf', __NAMESPACE__ . '\aufruf' );

/**
 * [rn_knopf url="…" stil="leise"]Text[/rn_knopf]
 *
 * @param array<string, string>|string $attr   Attribute.
 * @param string|null                  $inhalt Inhalt.
 */
function knopf( $attr, ?string $inhalt = null ): string {
	$a = shortcode_atts( array( 'url' => '', 'stil' => 'laut' ), $attr, 'rn_knopf' );

	return knopf_html( wp_strip_all_tags( (string) $inhalt ), $a['url'], $a['stil'] );
}
add_shortcode( 'rn_knopf', __NAMESPACE__ . '\knopf' );

/**
 * [rn_bild platz="birkenhain" bildzeile="…"]
 *
 * @param array<string, string>|string $attr Attribute.
 */
function bild( $attr ): string {
	$a = shortcode_atts(
		array(
			'platz'     => '',
			'bildzeile' => '',
			'groesse'   => 'large',
		),
		$attr,
		'rn_bild'
	);

	$html = Bilder\bild( $a['platz'], '', $a['groesse'] );

	if ( '' === $html ) {
		return '';
	}

	if ( '' === trim( $a['bildzeile'] ) ) {
		return '<figure>' . $html . '</figure>';
	}

	return sprintf(
		'<figure>%s<figcaption>%s</figcaption></figure>',
		$html,
		esc_html( $a['bildzeile'] )
	);
}
add_shortcode( 'rn_bild', __NAMESPACE__ . '\bild' );

/**
 * [rn_stelle titel="…" detail="…" url="…"]
 *
 * @param array<string, string>|string $attr Attribute.
 */
function stelle( $attr ): string {
	$a = shortcode_atts(
		array(
			'titel'  => '',
			'detail' => '',
			'url'    => '',
		),
		$attr,
		'rn_stelle'
	);

	return sprintf(
		'<div class="rn-stelle"><div><h3>%s</h3><div class="rn-stelle__detail">%s</div></div>%s</div>',
		esc_html( $a['titel'] ),
		esc_html( $a['detail'] ),
		knopf_html( __( 'Ausschreibung', 'realnorth' ), $a['url'], 'leise' )
	);
}
add_shortcode( 'rn_stelle', __NAMESPACE__ . '\stelle' );

/**
 * wpautop auf den Vorlagenseiten abschalten.
 *
 * `the_content` lässt wpautop (Priorität 10) vor do_shortcode (11)
 * laufen. Die Shortcode-Zeilen landen dadurch in leeren Absätzen, und um
 * jeden <section> steht ein <p>. Innerhalb der Bausteine wird wpautop
 * von Hand aufgerufen (siehe absatz()), dort bleibt Fliesstext also
 * normal gesetzt.
 */
function autop_abschalten(): void {
	if ( \RealNorth\Vorlage\ist_aktiv() ) {
		remove_filter( 'the_content', 'wpautop' );
	}
}
add_action( 'wp', __NAMESPACE__ . '\autop_abschalten' );
