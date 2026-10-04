<?php
/**
 * Die Bausteine als Elementor-Widgets.
 *
 * Jedes Widget beschreibt nur seine Felder und reicht sie an die
 * Funktion weiter, die auch hinter dem Shortcode steht. Dadurch gibt es
 * pro Baustein genau eine Umsetzung — was im Builder herauskommt, ist
 * dasselbe wie im klassischen Seiteninhalt, und die Tests decken beides
 * ab.
 *
 * Die Feldnamen entsprechen genau den Shortcode-Attributen. Das ist kein
 * Zufall: shortcode_atts() arbeitet auf einem Array, also lassen sich
 * die Einstellungen eines Widgets unverändert durchreichen.
 *
 * Geladen wird diese Datei erst beim Anmelden der Widgets — vorher gibt
 * es \Elementor\Widget_Base nicht.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;
use RealNorth\Abschnitte;
use RealNorth\Ansicht;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gemeinsamer Unterbau aller realnorth-Widgets.
 */
abstract class Baustein extends Widget_Base {

	/**
	 * Die Felder dieses Bausteins, im Format der Elementor-Controls.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	abstract protected function felder(): array;

	/**
	 * Die Ausgabe bauen.
	 *
	 * @param array<string, mixed> $werte Einstellungen aus dem Builder.
	 */
	abstract protected function baue( array $werte ): string;

	/**
	 * Alle Widgets liegen in der eigenen Gruppe.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( GRUPPE );
	}

	/**
	 * Suchbegriffe im Widget-Bereich.
	 *
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( 'realnorth', 'real north' );
	}

	/**
	 * Unser Stylesheet hängt an jeder Seite mit der Vorlage. Damit die
	 * Vorschau im Builder gleich aussieht, wird es hier zusätzlich als
	 * Abhängigkeit genannt.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'realnorth-site' );
	}

	/**
	 * Felder in einem Abschnitt anmelden.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'rn_inhalt',
			array( 'label' => __( 'Inhalt', 'realnorth' ) )
		);

		foreach ( $this->felder() as $name => $feld ) {
			$this->add_control( $name, $feld );
		}

		$this->end_controls_section();
	}

	/**
	 * Ausgeben.
	 *
	 * Kein zusätzliches Escaping: baue() liefert fertiges HTML, das in
	 * den Renderern Feld für Feld escaped wurde. Ein esc_html() hier
	 * würde das Markup sichtbar machen.
	 */
	protected function render(): void {
		echo $this->baue( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- in den Renderern escaped.
	}
}

/**
 * Die Bühne der Startseite.
 */
class Buehne extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-buehne';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Bühne', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-image-rollover';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'kicker'    => array(
				'label'   => __( 'Kicker', 'realnorth' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Bewirtschaftung · Entwicklung · Bestand',
			),
			'titel'     => array(
				'label'       => __( 'Titel', 'realnorth' ),
				'type'        => Controls_Manager::TEXTAREA,
				'description' => __( '&lt;br&gt; ist erlaubt. Für die hellere Zeile: &lt;span class="rn-buehne__akzent"&gt;…&lt;/span&gt;', 'realnorth' ),
				'default'     => 'Wohnungen<br>bewirtschaften.<br><span class="rn-buehne__akzent">Gebiete entwickeln.</span>',
			),
			'lead'      => array(
				'label' => __( 'Lauftext', 'realnorth' ),
				'type'  => Controls_Manager::TEXTAREA,
			),
			'bild'      => array(
				'label'   => __( 'Bildplatz', 'realnorth' ),
				'type'    => Controls_Manager::SELECT,
				'options' => bildplaetze(),
				'default' => 'buehne',
			),
			'bildtitel' => array(
				'label' => __( 'Bildunterschrift links', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'bildzeile' => array(
				'label' => __( 'Bildunterschrift rechts', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'knopf1'    => array(
				'label'     => __( 'Knopf 1', 'realnorth' ),
				'type'      => Controls_Manager::TEXT,
				'separator' => 'before',
			),
			'url1'      => array(
				'label' => __( 'Ziel 1', 'realnorth' ),
				'type'  => Controls_Manager::URL,
			),
			'knopf2'    => array(
				'label' => __( 'Knopf 2 (leise)', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'url2'      => array(
				'label' => __( 'Ziel 2', 'realnorth' ),
				'type'  => Controls_Manager::URL,
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		$werte['url1'] = url_wert( $werte['url1'] ?? '' );
		$werte['url2'] = url_wert( $werte['url2'] ?? '' );

		return Abschnitte\buehne( $werte );
	}
}

/**
 * Kicker, Titel und Lauftext als Einstieg einer Seite.
 */
class Seitenkopf extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-kopf';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Seitenkopf', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-site-title';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'kicker' => array(
				'label' => __( 'Kicker', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'titel'  => array(
				'label' => __( 'Titel', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'lead'   => array(
				'label' => __( 'Lauftext', 'realnorth' ),
				'type'  => Controls_Manager::TEXTAREA,
			),
			'stufe'  => array(
				'label'   => __( 'Überschriftstufe', 'realnorth' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array( 'h1' => 'H1', 'h2' => 'H2' ),
				'default' => 'h1',
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		return Abschnitte\seitenkopf( $werte );
	}
}

/**
 * Überschrift eines Abschnitts, rechts optional ein Verweis.
 */
class Abschnittskopf extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-abschnittskopf';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Abschnittskopf', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-heading';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'titel'    => array(
				'label' => __( 'Titel', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'linktext' => array(
				'label' => __( 'Verweis', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'url'      => array(
				'label' => __( 'Ziel', 'realnorth' ),
				'type'  => Controls_Manager::URL,
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		$werte['url'] = url_wert( $werte['url'] ?? '' );

		return Abschnitte\abschnittskopf( $werte );
	}
}

/**
 * Eine nummerierte Spalte.
 */
class Schritt extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-schritt';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Schritt', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-number-field';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'nummer' => array(
				'label' => __( 'Nummer', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'titel'  => array(
				'label' => __( 'Titel', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'text'   => array(
				'label' => __( 'Text', 'realnorth' ),
				'type'  => Controls_Manager::TEXTAREA,
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		return Abschnitte\schritt( $werte, (string) ( $werte['text'] ?? '' ) );
	}
}

/**
 * Eine Kennzahl im dunklen Band.
 */
class Zahl extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-zahl';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Kennzahl', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-counter';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'wert' => array(
				'label'   => __( 'Wert', 'realnorth' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '250',
			),
			'text' => array(
				'label'       => __( 'Beschriftung', 'realnorth' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Beginnt der Wert mit einer ganzen Zahl, zählt sie beim Scrollen hoch.', 'realnorth' ),
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		return Abschnitte\zahl( $werte );
	}
}

/**
 * Eine Karte mit Kicker, Titel, Text und Knopf.
 */
class Karte extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-karte';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Karte', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-info-box';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'kicker' => array(
				'label' => __( 'Kicker', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'titel'  => array(
				'label' => __( 'Titel', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'text'   => array(
				'label' => __( 'Text', 'realnorth' ),
				'type'  => Controls_Manager::TEXTAREA,
			),
			'knopf'  => array(
				'label' => __( 'Knopf', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'url'    => array(
				'label' => __( 'Ziel', 'realnorth' ),
				'type'  => Controls_Manager::URL,
			),
			'stil'   => array(
				'label'   => __( 'Knopfstil', 'realnorth' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'leise' => __( 'leise', 'realnorth' ),
					'laut'  => __( 'laut', 'realnorth' ),
				),
				'default' => 'leise',
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		$werte['url'] = url_wert( $werte['url'] ?? '' );

		return Abschnitte\karte( $werte, (string) ( $werte['text'] ?? '' ) );
	}
}

/**
 * Begriff und Wert, zeilenweise.
 */
class Merkmale extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-merkmale';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Merkmalsliste', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-bullet-list';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		$zeile = new Repeater();

		$zeile->add_control(
			'begriff',
			array(
				'label' => __( 'Begriff', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$zeile->add_control(
			'wert',
			array(
				'label' => __( 'Wert', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		return array(
			'eintraege' => array(
				'label'       => __( 'Zeilen', 'realnorth' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $zeile->get_controls(),
				'title_field' => '{{{ begriff }}}',
				'default'     => array(
					array( 'begriff' => 'Gemeinde', 'wert' => 'Rudolfstetten-Friedlisberg AG' ),
				),
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		$innen = '';

		foreach ( (array) ( $werte['eintraege'] ?? array() ) as $zeile ) {
			$innen .= Abschnitte\merkmal( (array) $zeile );
		}

		return Abschnitte\merkmale( array(), $innen );
	}
}

/**
 * Zeitpunkt und Ereignis, zeilenweise.
 */
class Ablauf extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-ablauf';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Zeitablauf', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-time-line';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		$zeile = new Repeater();

		$zeile->add_control(
			'zeit',
			array(
				'label' => __( 'Zeitpunkt', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$zeile->add_control(
			'text',
			array(
				'label' => __( 'Ereignis', 'realnorth' ),
				'type'  => Controls_Manager::TEXTAREA,
			)
		);

		$zeile->add_control(
			'jetzt',
			array(
				'label'        => __( 'Aktueller Stand', 'realnorth' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'ja',
			)
		);

		return array(
			'eintraege' => array(
				'label'       => __( 'Etappen', 'realnorth' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $zeile->get_controls(),
				'title_field' => '{{{ zeit }}}',
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		$innen = '';

		foreach ( (array) ( $werte['eintraege'] ?? array() ) as $zeile ) {
			$zeile  = (array) $zeile;
			$innen .= Abschnitte\etappe( $zeile, (string) ( $zeile['text'] ?? '' ) );
		}

		return Abschnitte\ablauf( array(), $innen );
	}
}

/**
 * Der Kasten mit Aufforderung und Knopf.
 */
class Aufruf extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-aufruf';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Aufruf', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-call-to-action';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'titel' => array(
				'label' => __( 'Titel', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'text'  => array(
				'label' => __( 'Text', 'realnorth' ),
				'type'  => Controls_Manager::TEXTAREA,
			),
			'knopf' => array(
				'label' => __( 'Knopf', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'url'   => array(
				'label' => __( 'Ziel', 'realnorth' ),
				'type'  => Controls_Manager::URL,
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		$werte['url'] = url_wert( $werte['url'] ?? '' );

		return Abschnitte\aufruf( $werte, (string) ( $werte['text'] ?? '' ) );
	}
}

/**
 * Ein Bild aus einem benannten Platz.
 */
class Bild extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-bild';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Bildplatz', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-image';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'platz'     => array(
				'label'       => __( 'Bildplatz', 'realnorth' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => bildplaetze(),
				'description' => __( 'Zugewiesen wird unter Design → Bilder. Für ein einzelnes Bild ohne festen Platz das normale Bild-Widget von Elementor nehmen.', 'realnorth' ),
			),
			'bildzeile' => array(
				'label' => __( 'Bildunterschrift', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'groesse'   => array(
				'label'   => __( 'Grösse', 'realnorth' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'medium_large' => __( 'mittel', 'realnorth' ),
					'large'        => __( 'gross', 'realnorth' ),
					'full'         => __( 'Original', 'realnorth' ),
				),
				'default' => 'large',
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		return Abschnitte\bild( $werte );
	}
}

/**
 * Eine offene Stelle.
 */
class Stelle extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-stelle';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Offene Stelle', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-person';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'titel'  => array(
				'label' => __( 'Stelle', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'detail' => array(
				'label' => __( 'Ort und Antritt', 'realnorth' ),
				'type'  => Controls_Manager::TEXT,
			),
			'url'    => array(
				'label' => __( 'Ziel', 'realnorth' ),
				'type'  => Controls_Manager::URL,
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		$werte['url'] = url_wert( $werte['url'] ?? '' );

		return Abschnitte\stelle( $werte );
	}
}

/**
 * Die Wohnungsliste.
 *
 * Bewusst kein Loop Grid: der Zimmerfilter «4.5+» und die Sortierung
 * «sofort zuerst, dann nach Bezugstermin» sind Fachlogik. Sie liegt in
 * includes/wohnungen-logik.php und ist dort getestet — im Builder liesse
 * sie sich weder abbilden noch prüfen.
 */
class Wohnungen extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-wohnungen';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Wohnungen', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-posts-grid';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'darstellung' => array(
				'label'   => __( 'Darstellung', 'realnorth' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'karten'  => __( 'Karten', 'realnorth' ),
					'tabelle' => __( 'Tabelle', 'realnorth' ),
				),
				'default' => 'karten',
			),
			'anzahl'      => array(
				'label'       => __( 'Höchstens', 'realnorth' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'default'     => 0,
				'description' => __( '0 zeigt alle.', 'realnorth' ),
			),
			'filter'      => array(
				'label'        => __( 'Filterleiste', 'realnorth' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'ja',
				'default'      => '',
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		$werte['anzahl'] = (string) ( $werte['anzahl'] ?? '0' );
		$werte['filter'] = 'ja' === ( $werte['filter'] ?? '' ) ? 'ja' : 'nein';

		return Ansicht\wohnungen( $werte );
	}
}

/**
 * Das Team als Raster.
 */
class Team extends Baustein {

	/** @return string */
	public function get_name(): string {
		return 'realnorth-team';
	}

	/** @return string */
	public function get_title(): string {
		return __( 'Team', 'realnorth' );
	}

	/** @return string */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
	}

	/** @return array<string, array<string, mixed>> */
	protected function felder(): array {
		return array(
			'spalten' => array(
				'label'   => __( 'Spalten', 'realnorth' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5' ),
				'default' => '4',
			),
		);
	}

	/**
	 * @param array<string, mixed> $werte Einstellungen.
	 */
	protected function baue( array $werte ): string {
		return Ansicht\team( $werte );
	}
}
