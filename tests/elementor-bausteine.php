<?php
/**
 * Tests der Elementor-Widgets.
 *
 * Elementor liegt hier nicht vor — die Klassen erben trotzdem von
 * \Elementor\Widget_Base. Dieser Test stellt deshalb einen minimalen
 * Ersatz bereit und baut damit jedes Widget einmal auf: Felder anmelden,
 * mit den Vorgabewerten rendern, Ausgabe prüfen.
 *
 * Das fängt genau die Fehler, die man sonst erst im Builder sieht — eine
 * Methode mit falscher Signatur, ein vergessenes Feld, ein Renderer, der
 * mit den Vorgaben nicht zurechtkommt. Ein Widget, das beim Anmelden
 * stirbt, taucht in Elementor wortlos nicht auf.
 *
 * Was dieser Test NICHT prüft: ob die echten Signaturen von Elementor zu
 * unseren passen. Dafür bräuchte es Elementor selbst.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace {

define( 'ABSPATH', __DIR__ . '/' );

// ---------- Ersatz für WordPress ----------

/**
 * Stub.
 *
 * @param string $t Text.
 * @param string $d Textdomain.
 */
function __( string $t, string $d = '' ): string {
	return $t;
}

/**
 * Stub.
 *
 * @param string $t Text.
 * @param string $d Textdomain.
 */
function esc_html__( string $t, string $d = '' ): string {
	return htmlspecialchars( $t, ENT_QUOTES );
}

/**
 * Stub.
 *
 * @param string $t Text.
 * @param string $d Textdomain.
 */
function esc_attr__( string $t, string $d = '' ): string {
	return htmlspecialchars( $t, ENT_QUOTES );
}

/**
 * Stub.
 *
 * @param string $s Einzahl.
 * @param string $p Mehrzahl.
 * @param int    $n Anzahl.
 * @param string $d Textdomain.
 */
function _n( string $s, string $p, int $n, string $d = '' ): string {
	return 1 === $n ? $s : $p;
}

/**
 * Stub.
 *
 * @param mixed $t Wert.
 */
function esc_html( $t ): string {
	return htmlspecialchars( (string) $t, ENT_QUOTES );
}

/**
 * Stub.
 *
 * @param mixed $t Wert.
 */
function esc_attr( $t ): string {
	return htmlspecialchars( (string) $t, ENT_QUOTES );
}

/**
 * Stub.
 *
 * @param mixed $u Adresse.
 */
function esc_url( $u ): string {
	return htmlspecialchars( (string) $u, ENT_QUOTES );
}

/**
 * Stub.
 *
 * @param mixed $t HTML.
 */
function wp_kses_post( $t ): string {
	return (string) $t;
}

/**
 * Stub.
 *
 * @param mixed $t HTML.
 */
function wp_strip_all_tags( $t ): string {
	return wp_strip_tags_einfach( (string) $t );
}

/**
 * Hilfsfunktion zum Stub oben.
 *
 * @param string $t HTML.
 */
function wp_strip_tags_einfach( string $t ): string {
	return strip_tags( $t );
}

/**
 * Stub.
 *
 * @param mixed $c Klassenname.
 */
function sanitize_html_class( $c ): string {
	return (string) preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $c );
}

/**
 * Stub.
 *
 * @param mixed $t Text.
 */
function sanitize_title( $t ): string {
	return strtolower( (string) preg_replace( '/[^A-Za-z0-9_-]/', '-', (string) $t ) );
}

/**
 * Stub.
 *
 * @param string $u Adresse.
 * @param int    $c Bestandteil.
 */
function wp_parse_url( string $u, int $c = -1 ) {
	return parse_url( $u, $c );
}

/**
 * Stub.
 *
 * @param string $p Pfad.
 */
function home_url( string $p = '/' ): string {
	return 'https://realnorth.ch' . $p;
}

/**
 * Stub.
 *
 * @param string $t   Text.
 * @param bool   $br  Zeilenumbrüche.
 */
function wpautop( string $t, bool $br = true ): string {
	return '<p>' . trim( $t ) . '</p>';
}

/**
 * Stub: keine Shortcodes in diesem Test.
 *
 * @param string $t Inhalt.
 */
function do_shortcode( string $t ): string {
	return $t;
}

/**
 * Stub.
 *
 * @param string $t Name.
 */
function shortcode_exists( string $t ): bool {
	return false;
}

/**
 * Stub mit dem Verhalten von WordPress: Vorgaben, überschrieben von den
 * übergebenen Werten, fremde Schlüssel fallen weg.
 *
 * @param array<string, mixed>        $paare Vorgaben.
 * @param array<string, mixed>|string $atts  Übergebene Werte.
 * @param string                      $name  Name.
 * @return array<string, mixed>
 */
function shortcode_atts( array $paare, $atts, string $name = '' ): array {
	$atts    = (array) $atts;
	$fertig = array();

	foreach ( $paare as $schluessel => $vorgabe ) {
		$fertig[ $schluessel ] = array_key_exists( $schluessel, $atts ) ? $atts[ $schluessel ] : $vorgabe;
	}

	return $fertig;
}

/**
 * Stub.
 *
 * @param string $tag      Name.
 * @param mixed  $callback Rückruf.
 */
function add_shortcode( string $tag, $callback ): void {
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
 * Stub: keine zugewiesenen Bilder, also überall Platzhalter.
 *
 * @param string $name    Name.
 * @param mixed  $vorgabe Vorgabe.
 */
function get_option( string $name, $vorgabe = false ) {
	return $vorgabe;
}

/**
 * Stub.
 *
 * @param array<string, mixed> $args Argumente.
 * @return array<int, mixed>
 */
function get_posts( array $args = array() ): array {
	return array();
}

/**
 * Stub.
 *
 * @param int    $id Beitrag.
 * @param string $k  Schlüssel.
 * @param bool   $s  Einzelwert.
 */
function get_post_meta( int $id, string $k = '', bool $s = false ) {
	return '';
}

/**
 * Stub.
 *
 * @param array<int, string>|string $k Schlüssel.
 * @param string|false              $q Adresse.
 */
function remove_query_arg( $k, $q = false ): string {
	return 'https://realnorth.ch/wohnungen/';
}

/**
 * Stub.
 *
 * @param mixed ...$a Argumente.
 */
function add_query_arg( ...$a ): string {
	return 'https://realnorth.ch/wohnungen/?gefiltert=1';
}

/**
 * Stub.
 *
 * @param string $f Datei.
 */
function plugin_dir_path( string $f ): string {
	return dirname( $f ) . '/';
}

}

// ---------- Ersatz für Elementor ----------

namespace Elementor {

/**
 * Die Konstanten, die unsere Felder benutzen.
 */
class Controls_Manager {
	const TEXT     = 'text';
	const TEXTAREA = 'textarea';
	const SELECT   = 'select';
	const NUMBER   = 'number';
	const SWITCHER = 'switcher';
	const URL      = 'url';
	const REPEATER = 'repeater';
}

/**
 * Sammelt die Felder einer Wiederholung.
 */
class Repeater {

	/**
	 * Die Felder.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private array $felder = array();

	/**
	 * Ein Feld anmelden.
	 *
	 * @param string               $name Name.
	 * @param array<string, mixed> $feld Beschreibung.
	 */
	public function add_control( string $name, array $feld ): void {
		$this->felder[ $name ] = $feld;
	}

	/**
	 * Alle Felder.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_controls(): array {
		return $this->felder;
	}
}

/**
 * Das Nötigste aus Widget_Base.
 */
abstract class Widget_Base {

	/**
	 * Angemeldete Felder.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	public array $rn_felder = array();

	/**
	 * Offene Abschnitte.
	 *
	 * @var array<int, string>
	 */
	public array $rn_abschnitte = array();

	/**
	 * Der Name des Widgets.
	 */
	abstract public function get_name();

	/**
	 * Einen Abschnitt öffnen.
	 *
	 * @param string               $name Name.
	 * @param array<string, mixed> $args Argumente.
	 */
	protected function start_controls_section( string $name, array $args = array() ): void {
		$this->rn_abschnitte[] = $name;
	}

	/**
	 * Den Abschnitt schliessen.
	 */
	protected function end_controls_section(): void {
		array_pop( $this->rn_abschnitte );
	}

	/**
	 * Ein Feld anmelden.
	 *
	 * @param string               $name Name.
	 * @param array<string, mixed> $feld Beschreibung.
	 */
	protected function add_control( string $name, array $feld ): void {
		if ( array() === $this->rn_abschnitte ) {
			throw new \RuntimeException( 'Feld ' . $name . ' liegt ausserhalb eines Abschnitts.' );
		}

		$this->rn_felder[ $name ] = $feld;
	}

	/**
	 * Die Vorgabewerte aller Felder — hier steht sonst, was im Builder
	 * eingetragen wurde.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_settings_for_display(): array {
		$werte = array();

		foreach ( $this->rn_felder as $name => $feld ) {
			$werte[ $name ] = $feld['default'] ?? '';
		}

		return $werte;
	}

	/**
	 * Von aussen aufrufbar machen, was Elementor sonst selber aufruft.
	 */
	public function rn_aufbauen(): void {
		$this->register_controls();
	}

	/**
	 * Von aussen aufrufbar machen, was Elementor sonst selber aufruft.
	 */
	public function rn_ausgeben(): string {
		ob_start();
		$this->render();

		return (string) ob_get_clean();
	}
}

}

namespace {

	require dirname( __DIR__ ) . '/includes/wohnungen-logik.php';
	require dirname( __DIR__ ) . '/includes/post-types.php';
	require dirname( __DIR__ ) . '/includes/bilder.php';
	require dirname( __DIR__ ) . '/includes/abschnitte.php';
	require dirname( __DIR__ ) . '/includes/wohnungen-ansicht.php';
	require dirname( __DIR__ ) . '/includes/elementor.php';
	require dirname( __DIR__ ) . '/includes/elementor-bausteine.php';

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

	echo "--- Jedes Widget lässt sich bauen und rendern ---\n";

	$namen = array();

	foreach ( RealNorth\Elementor\bausteine() as $klasse ) {
		$voll = 'RealNorth\\Elementor\\' . $klasse;

		if ( ! class_exists( $voll ) ) {
			pruefe( "Klasse {$klasse} existiert", false, true );
			continue;
		}

		$widget = new $voll();
		$widget->rn_aufbauen();
		$ausgabe = $widget->rn_ausgeben();

		$namen[] = $widget->get_name();

		if ( array() === $widget->rn_felder ) {
			pruefe( "{$klasse} hat Felder", false, true );
		}

		if ( array() !== $widget->rn_abschnitte ) {
			pruefe( "{$klasse} schliesst seinen Abschnitt", false, true );
		}

		if ( ! str_starts_with( $widget->get_name(), 'realnorth-' ) ) {
			pruefe( "{$klasse} heisst realnorth-…", $widget->get_name(), 'realnorth-…' );
		}

		if ( '' === trim( $widget->get_title() ) ) {
			pruefe( "{$klasse} hat einen Titel", false, true );
		}

		if ( ! str_starts_with( $widget->get_icon(), 'eicon-' ) ) {
			pruefe( "{$klasse} hat ein Elementor-Symbol", $widget->get_icon(), 'eicon-…' );
		}

		if ( 1 === preg_match( '/\[\/?rn_/', $ausgabe ) ) {
			pruefe( "{$klasse}: kein unaufgelöster Shortcode", false, true );
		}

		printf( "ok    %-16s %-24s %4d Zeichen, %d Felder\n", $klasse, $widget->get_name(), strlen( $ausgabe ), count( $widget->rn_felder ) );
		++$anzahl;
	}

	echo "\n--- Namen und Gruppe ---\n";
	pruefe( 'Namen sind eindeutig', count( array_unique( $namen ) ), count( $namen ) );
	pruefe( 'alle dreizehn gebaut', count( $namen ), 13 );

	$eines = new RealNorth\Elementor\Zahl();
	pruefe( 'Widgets liegen in der eigenen Gruppe', $eines->get_categories(), array( 'realnorth' ) );
	pruefe( 'Stylesheet ist als Abhängigkeit genannt', $eines->get_style_depends(), array( 'realnorth-site' ) );

	echo "\n--- Was die Widgets tatsächlich ausgeben ---\n";
	$zahl = new RealNorth\Elementor\Zahl();
	$zahl->rn_aufbauen();
	pruefe( 'Kennzahl bekommt das Zählattribut', str_contains( $zahl->rn_ausgeben(), 'data-rn-zahl="250"' ), true );

	$buehne = new RealNorth\Elementor\Buehne();
	$buehne->rn_aufbauen();
	$html = $buehne->rn_ausgeben();
	pruefe( 'Bühne bringt die Akzentzeile mit', str_contains( $html, 'rn-buehne__akzent' ), true );
	pruefe( 'Bühne zeigt den Platzhalter, solange kein Bild zugewiesen ist', str_contains( $html, 'rn-bildplatz' ), true );

	$bild = new RealNorth\Elementor\Bild();
	$bild->rn_aufbauen();
	pruefe( 'Bildplatz ohne Auswahl gibt nichts aus', $bild->rn_ausgeben(), '' );

	echo "\n";
	echo 0 === $fehler
		? "elementor-bausteine: alle {$anzahl} Prüfungen ok.\n"
		: "elementor-bausteine: {$fehler} von {$anzahl} Prüfungen fehlgeschlagen.\n";

	exit( 0 === $fehler ? 0 : 1 );
}
