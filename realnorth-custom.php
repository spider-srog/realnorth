<?php
/**
 * Plugin Name:       realnorth Custom
 * Plugin URI:        https://github.com/spider-srog/realnorth
 * Description:       Projektspezifische Anpassungen für realnorth.ch — eigenes CSS und eigene Hooks, versioniert in Git und per Plesk deployt. Bewusst getrennt vom PopularFX-Theme, damit Theme-Updates nichts überschreiben.
 * Version:           0.7.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Spiderfrog AG
 * License:           GPL-2.0-or-later
 * Text Domain:       realnorth
 */

declare( strict_types=1 );

namespace RealNorth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VERSION  = '0.7.0';
const CSS_FILE = 'assets/css/site.css';
const JS_FILE  = 'assets/js/site.js';

require_once plugin_dir_path( __FILE__ ) . 'includes/content-cleanup.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/wohnungen-logik.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/post-types.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/seed.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/bilder.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/vorlage.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/kopf-fuss.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/abschnitte.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/wohnungen-ansicht.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/seiten.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/elementor.php';

/**
 * Eigenes Stylesheet im Frontend laden.
 *
 * Priorität 20, damit es nach den Theme- und Pagelayer-Styles kommt und
 * ohne !important überschreiben kann.
 *
 * Als Versionsstring dient die Änderungszeit der Datei: auf dem Server
 * läuft kein Build, also ist filemtime() das einzige verlässliche
 * Cache-Busting. Fällt es aus, greift die Plugin-Version.
 */
function enqueue_site_styles(): void {
	$path = plugin_dir_path( __FILE__ ) . CSS_FILE;

	if ( ! is_readable( $path ) ) {
		return;
	}

	$mtime = filemtime( $path );

	wp_enqueue_style(
		'realnorth-site',
		plugins_url( CSS_FILE, __FILE__ ),
		array(),
		false !== $mtime ? (string) $mtime : VERSION
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_site_styles', 20 );

/**
 * Das kleine Skript laden — nur auf Seiten mit unserer Vorlage.
 *
 * Es zählt die Kennzahlen hoch, sonst nichts. Auf allen anderen Seiten
 * hätte es nichts zu tun, also wird es dort auch nicht geladen.
 */
function enqueue_site_script(): void {
	if ( ! Vorlage\ist_aktiv() ) {
		return;
	}

	$path = plugin_dir_path( __FILE__ ) . JS_FILE;

	if ( ! is_readable( $path ) ) {
		return;
	}

	$mtime = filemtime( $path );

	wp_enqueue_script(
		'realnorth-site',
		plugins_url( JS_FILE, __FILE__ ),
		array(),
		false !== $mtime ? (string) $mtime : VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_site_script', 20 );

/**
 * Beim Aktivieren die Permalinks neu schreiben.
 *
 * Ohne das liefert `/wohnungen` einen 404, weil WordPress die Regeln für
 * neue Inhaltstypen nur beim Flush kennt. Die Inhaltstypen müssen vorher
 * registriert sein — deshalb der Aufruf von Hand statt sich auf `init`
 * zu verlassen, das bei der Aktivierung schon durch ist.
 */
function on_activate(): void {
	PostTypes\register_post_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\on_activate' );

/**
 * Beim Deaktivieren aufräumen: die Regeln der Inhaltstypen verschwinden,
 * die Beiträge bleiben. Wer das Plugin als Notausschalter benutzt, soll
 * danach keine toten URLs im Cache haben.
 */
function on_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\on_deactivate' );
