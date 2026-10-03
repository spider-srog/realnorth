<?php
/**
 * Inhaltstypen und Felder: Wohnungen, Liegenschaften, Team.
 *
 * Ohne Elementor Pro gibt es keine Dynamic Tags, mit denen der Builder
 * selbst auf Felder zugreifen könnte. Die Felder müssen deshalb sauber
 * registriert sein (REST, Sanitisierung, Admin-Maske), damit unsere
 * eigenen Widgets und Shortcodes damit arbeiten können.
 *
 * Ein einziges Feldverzeichnis (FELDER) treibt alles: Registrierung,
 * Eingabemaske und Speichern. Ein neues Feld ist deshalb eine Zeile, kein
 * neuer Code — und es kann nicht passieren, dass ein Feld zwar in der
 * Maske steht, aber beim Speichern vergessen geht.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Inhaltstyp Wohnung. */
const WOHNUNG = 'rn_wohnung';

/** Inhaltstyp Liegenschaft. */
const LIEGENSCHAFT = 'rn_liegenschaft';

/** Inhaltstyp Teammitglied. */
const TEAM = 'rn_team';

/**
 * Alle Felder je Inhaltstyp.
 *
 * typ: text | zahl | auswahl
 * Die Reihenfolge ist zugleich die Reihenfolge in der Eingabemaske.
 *
 * @return array<string, array<string, array<string, mixed>>>
 */
function felder(): array {
	return array(
		WOHNUNG      => array(
			'rn_ort'       => array( 'label' => 'Ort', 'typ' => 'text' ),
			'rn_zimmer'    => array( 'label' => 'Zimmer', 'typ' => 'text', 'hinweis' => 'z. B. 3.5' ),
			'rn_flaeche'   => array( 'label' => 'Fläche in m²', 'typ' => 'zahl' ),
			'rn_etage'     => array( 'label' => 'Etage', 'typ' => 'text', 'hinweis' => 'z. B. 2. OG, EG, Attika' ),
			'rn_frei'      => array( 'label' => 'Frei ab', 'typ' => 'text', 'hinweis' => 'sofort oder TT.MM.JJJJ' ),
			'rn_miete'     => array( 'label' => 'Miete in CHF', 'typ' => 'zahl', 'hinweis' => 'ganze Franken, brutto' ),
			'rn_status'    => array(
				'label'    => 'Status',
				'typ'      => 'auswahl',
				'optionen' => array( 'Frei', 'Reserviert', 'Vormerkung', 'Vermietet' ),
				'standard' => 'Frei',
				'hinweis'  => 'Vermietete erscheinen nicht in der Liste',
			),
			'rn_quelle_id' => array(
				'label'   => 'Objekt-ID der Quelle',
				'typ'     => 'text',
				'hinweis' => 'Vom Importer gesetzt — von Hand nur ändern, wenn man weiss warum',
			),
		),
		LIEGENSCHAFT => array(
			'rn_adresse'   => array( 'label' => 'Adresse', 'typ' => 'text' ),
			'rn_plz'       => array( 'label' => 'PLZ', 'typ' => 'text' ),
			'rn_ort'       => array( 'label' => 'Ort', 'typ' => 'text' ),
			'rn_einheiten' => array( 'label' => 'Anzahl Einheiten', 'typ' => 'zahl' ),
			'rn_baujahr'   => array( 'label' => 'Baujahr', 'typ' => 'zahl' ),
		),
		TEAM         => array(
			'rn_rolle'       => array( 'label' => 'Rolle', 'typ' => 'text' ),
			'rn_bereich'     => array( 'label' => 'Bereich', 'typ' => 'text' ),
			'rn_reihenfolge' => array( 'label' => 'Reihenfolge', 'typ' => 'zahl', 'hinweis' => 'kleinere Zahl zuerst' ),
		),
	);
}

/**
 * Inhaltstypen registrieren.
 */
function register_post_types(): void {
	register_post_type(
		WOHNUNG,
		array(
			'labels'       => array(
				'name'          => __( 'Wohnungen', 'realnorth' ),
				'singular_name' => __( 'Wohnung', 'realnorth' ),
				'add_new_item'  => __( 'Wohnung hinzufügen', 'realnorth' ),
				'edit_item'     => __( 'Wohnung bearbeiten', 'realnorth' ),
				'search_items'  => __( 'Wohnungen durchsuchen', 'realnorth' ),
				'not_found'     => __( 'Keine Wohnungen gefunden', 'realnorth' ),
			),
			'public'       => true,
			// Kein eigenes Archiv: die Liste steht auf der Seite
			// /wohnungen/ und wird dort von [rn_wohnungen] gebaut. Ein
			// Archiv unter demselben Namen würde die Seite verdecken.
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'wohnung', 'with_front' => false ),
			'menu_icon'    => 'dashicons-admin-home',
			'menu_position' => 20,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		LIEGENSCHAFT,
		array(
			'labels'       => array(
				'name'          => __( 'Liegenschaften', 'realnorth' ),
				'singular_name' => __( 'Liegenschaft', 'realnorth' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'liegenschaft', 'with_front' => false ),
			'menu_icon'    => 'dashicons-building',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true,
		)
	);

	register_post_type(
		TEAM,
		array(
			'labels'              => array(
				'name'          => __( 'Team', 'realnorth' ),
				'singular_name' => __( 'Teammitglied', 'realnorth' ),
			),
			// Kein öffentlicher Einzelaufruf: ein Teammitglied hat keine
			// eigene Seite, es erscheint nur im Raster auf «Über uns».
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-groups',
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'show_in_rest'        => true,
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\register_post_types' );

/**
 * Meta-Felder registrieren.
 *
 * `single` und ein expliziter `sanitize_callback` sind Pflicht — ohne sie
 * landet ungeprüfter Input in der Datenbank. `auth_callback` hält das
 * Schreiben über REST bei denen, die den Beitrag ohnehin bearbeiten dürfen.
 */
function register_meta_fields(): void {
	foreach ( felder() as $post_type => $felder ) {
		foreach ( $felder as $key => $feld ) {
			$typ = (string) ( $feld['typ'] ?? 'text' );

			register_post_meta(
				$post_type,
				$key,
				array(
					'single'            => true,
					'type'              => 'zahl' === $typ ? 'integer' : 'string',
					'show_in_rest'      => true,
					'sanitize_callback' => 'zahl' === $typ
						? static fn( $wert ): int => (int) $wert
						: 'sanitize_text_field',
					'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
}
add_action( 'init', __NAMESPACE__ . '\register_meta_fields' );

/**
 * Eingabemaske je Inhaltstyp anmelden.
 */
function add_meta_boxes(): void {
	foreach ( array_keys( felder() ) as $post_type ) {
		add_meta_box(
			'realnorth-felder',
			__( 'Angaben', 'realnorth' ),
			__NAMESPACE__ . '\render_meta_box',
			$post_type,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\add_meta_boxes' );

/**
 * Eingabemaske ausgeben.
 *
 * @param \WP_Post $post Beitrag.
 */
function render_meta_box( \WP_Post $post ): void {
	$felder = felder()[ $post->post_type ] ?? array();

	wp_nonce_field( 'realnorth_felder', 'realnorth_felder_nonce' );

	echo '<table class="form-table"><tbody>';

	foreach ( $felder as $key => $feld ) {
		$wert  = (string) get_post_meta( $post->ID, $key, true );
		$label = (string) ( $feld['label'] ?? $key );

		if ( '' === $wert && isset( $feld['standard'] ) ) {
			$wert = (string) $feld['standard'];
		}

		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td>',
			esc_attr( $key ),
			esc_html( $label )
		);

		if ( 'auswahl' === ( $feld['typ'] ?? '' ) ) {
			printf( '<select name="%1$s" id="%1$s">', esc_attr( $key ) );
			foreach ( (array) ( $feld['optionen'] ?? array() ) as $option ) {
				printf(
					'<option value="%1$s"%2$s>%1$s</option>',
					esc_attr( (string) $option ),
					selected( $wert, (string) $option, false )
				);
			}
			echo '</select>';
		} else {
			printf(
				'<input type="%1$s" name="%2$s" id="%2$s" value="%3$s" class="regular-text">',
				'zahl' === ( $feld['typ'] ?? '' ) ? 'number' : 'text',
				esc_attr( $key ),
				esc_attr( $wert )
			);
		}

		if ( ! empty( $feld['hinweis'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( (string) $feld['hinweis'] ) );
		}

		echo '</td></tr>';
	}

	echo '</tbody></table>';
}

/**
 * Eingaben speichern.
 *
 * Reihenfolge der Prüfungen ist Absicht: erst Autosave und Revisionen
 * aussortieren, dann die Nonce, dann die Berechtigung. Fehlt eine davon,
 * schreibt ein fremdes Formular in unsere Felder.
 *
 * @param int $post_id Beitrag.
 */
function save_meta_box( int $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post_type = (string) get_post_type( $post_id );
	$felder    = felder()[ $post_type ] ?? array();

	if ( array() === $felder ) {
		return;
	}

	$nonce = isset( $_POST['realnorth_felder_nonce'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['realnorth_felder_nonce'] ) )
		: '';

	if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'realnorth_felder' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( $felder as $key => $feld ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$roh = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- direkt darunter sanitisiert.

		if ( 'zahl' === ( $feld['typ'] ?? '' ) ) {
			update_post_meta( $post_id, $key, (int) $roh );
			continue;
		}

		$wert = sanitize_text_field( (string) $roh );

		// Bei einer Auswahl zählt nur, was auch zur Auswahl stand.
		if ( 'auswahl' === ( $feld['typ'] ?? '' )
			&& ! in_array( $wert, (array) ( $feld['optionen'] ?? array() ), true ) ) {
			continue;
		}

		update_post_meta( $post_id, $key, $wert );
	}
}
add_action( 'save_post', __NAMESPACE__ . '\save_meta_box' );

/**
 * Spalten in der Wohnungs-Übersicht: ohne sie sieht man im Backend nur
 * Titel und Datum und muss jedes Objekt öffnen, um Status oder Miete zu
 * sehen.
 *
 * @param array<string, string> $spalten Bestehende Spalten.
 * @return array<string, string>
 */
function wohnung_columns( array $spalten ): array {
	$neu = array();

	foreach ( $spalten as $key => $label ) {
		$neu[ $key ] = $label;

		if ( 'title' === $key ) {
			$neu['rn_ort']    = __( 'Ort', 'realnorth' );
			$neu['rn_zimmer'] = __( 'Zimmer', 'realnorth' );
			$neu['rn_miete']  = __( 'Miete', 'realnorth' );
			$neu['rn_frei']   = __( 'Frei ab', 'realnorth' );
			$neu['rn_status'] = __( 'Status', 'realnorth' );
		}
	}

	return $neu;
}
add_filter( 'manage_' . WOHNUNG . '_posts_columns', __NAMESPACE__ . '\wohnung_columns' );

/**
 * Inhalt der eigenen Spalten.
 *
 * @param string $spalte  Spaltenschlüssel.
 * @param int    $post_id Beitrag.
 */
function wohnung_column_content( string $spalte, int $post_id ): void {
	if ( ! str_starts_with( $spalte, 'rn_' ) ) {
		return;
	}

	$wert = (string) get_post_meta( $post_id, $spalte, true );

	if ( 'rn_miete' === $spalte && '' !== $wert ) {
		echo esc_html( \RealNorth\Wohnungen\format_miete( (int) $wert ) );
		return;
	}

	echo esc_html( '' !== $wert ? $wert : '—' );
}
add_action( 'manage_' . WOHNUNG . '_posts_custom_column', __NAMESPACE__ . '\wohnung_column_content', 10, 2 );
