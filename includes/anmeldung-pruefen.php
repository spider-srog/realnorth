<?php
/**
 * Messpunkt: kommt die Anmeldung per Anwendungspasswort bei WordPress an?
 *
 * Vorübergehend. Der MCP-Server `real-north-ag-elementor` wird mit
 * `rest_forbidden` abgewiesen, auch mit einem Benutzernamen, den es nicht
 * gibt — WordPress hat also gar keinen Anmeldeversuch gesehen. Ob der
 * `Authorization`-Header unterwegs (nginx → Apache → PHP) verloren geht
 * oder WordPress die Prüfung überspringt, lässt sich von aussen nicht
 * unterscheiden. Dieser Endpunkt sagt es:
 *
 *     GET /wp-json/realnorth/v1/anmeldung
 *
 * Ausgegeben werden nur Ja/Nein-Werte und Fehlercodes, nie Benutzername
 * oder Passwort. Sobald der MCP-Server verbunden ist, gehört die Datei
 * wieder heraus.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Anmeldung;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wo der Header in `$_SERVER` angekommen ist.
 *
 * WordPress liest Basic-Auth aus `PHP_AUTH_USER` und füllt das selbst aus
 * `HTTP_AUTHORIZATION` bzw. `REDIRECT_HTTP_AUTHORIZATION` nach. Fehlt alles
 * drei, hat der Webserver den Header verschluckt.
 *
 * @param array<string, mixed> $server Inhalt von `$_SERVER`.
 * @return array{header_angekommen: bool, quelle: ?string, schema: ?string}
 */
function header_befund( array $server ): array {
	foreach ( array( 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION' ) as $schluessel ) {
		$wert = $server[ $schluessel ] ?? '';
		if ( is_string( $wert ) && '' !== trim( $wert ) ) {
			return array(
				'header_angekommen' => true,
				'quelle'            => $schluessel,
				'schema'            => 0 === stripos( ltrim( $wert ), 'basic ' ) ? 'basic' : 'anderes',
			);
		}
	}

	if ( isset( $server['PHP_AUTH_USER'] ) && '' !== $server['PHP_AUTH_USER'] ) {
		return array(
			'header_angekommen' => true,
			'quelle'            => 'PHP_AUTH_USER',
			'schema'            => 'basic',
		);
	}

	return array(
		'header_angekommen' => false,
		'quelle'            => null,
		'schema'            => null,
	);
}

/**
 * Was der Befund bedeutet, in einem Satz.
 *
 * @param bool    $header     Header bei PHP angekommen.
 * @param bool    $verfuegbar Anwendungspasswörter eingeschaltet.
 * @param bool    $angemeldet Ein Benutzer ist angemeldet.
 * @param ?string $fehler     Fehlercode der Passwortprüfung, falls einer vorliegt.
 */
function deutung( bool $header, bool $verfuegbar, bool $angemeldet, ?string $fehler ): string {
	if ( ! $header ) {
		return 'Der Authorization-Header kommt nicht bei PHP an. Webserver-Konfiguration prüfen.';
	}
	if ( ! $verfuegbar ) {
		return 'Anwendungspasswörter sind abgeschaltet (Sicherheits-Plugin oder Filter).';
	}
	if ( $angemeldet ) {
		return 'Anmeldung funktioniert.';
	}
	if ( null !== $fehler ) {
		return 'Header kommt an, die Prüfung lehnt ab: ' . $fehler . '.';
	}
	return 'Header kommt an, aber WordPress hat die Passwortprüfung übersprungen.';
}

/**
 * Antwort des Endpunkts.
 *
 * @return array<string, mixed>
 */
function antwort(): array {
	global $wp_rest_application_password_status;

	$befund     = header_befund( $_SERVER );
	$verfuegbar = function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available();
	$angemeldet = get_current_user_id() > 0;
	$fehler     = $wp_rest_application_password_status instanceof \WP_Error
		? (string) $wp_rest_application_password_status->get_error_code()
		: null;

	return $befund + array(
		'anwendungspasswoerter_verfuegbar' => $verfuegbar,
		'angemeldet'                       => $angemeldet,
		'administrator'                    => $angemeldet && current_user_can( 'manage_options' ),
		'pruefung_fehler'                  => $fehler,
		'deutung'                          => deutung( $befund['header_angekommen'], $verfuegbar, $angemeldet, $fehler ),
	);
}

add_action(
	'rest_api_init',
	static function (): void {
		register_rest_route(
			'realnorth/v1',
			'/anmeldung',
			array(
				'methods'             => 'GET',
				'callback'            => __NAMESPACE__ . '\antwort',
				'permission_callback' => '__return_true',
			)
		);
	}
);
