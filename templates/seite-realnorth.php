<?php
/**
 * Template Name: realnorth (Vollbreite)
 *
 * Vollständiges Dokument. Kopf und Fuss kommen aus dem Theme Builder von
 * Elementor Pro, sobald dort eine Vorlage zugewiesen ist; solange nicht,
 * springt die Fassung aus dem Plugin ein. Das Theme ist auf diesen
 * Seiten in keinem Fall beteiligt.
 *
 * Warum überhaupt eine eigene Vorlage, wo Elementor Pro doch einen Theme
 * Builder hat: dessen Ausgabe hängt an `elementor_theme_do_location()`,
 * und ob das greift, entscheidet das aktive Theme. PopularFX ist fremd
 * und meldet die Unterstützung nicht an. Hier wird sie direkt
 * aufgerufen — damit funktioniert der Theme Builder unabhängig davon,
 * welches Theme gerade aktiv ist.
 *
 * Angemeldet wird die Vorlage in includes/vorlage.php.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( array( 'rn-body', 'rn-site' ) ); ?>>
<?php
wp_body_open();

if ( ! RealNorth\KopfFuss\elementor_bereich( 'header' ) ) {
	RealNorth\KopfFuss\kopfzeile();
}
?>

	<main id="rn-inhalt">
		<?php
		while ( have_posts() ) {
			the_post();
			the_content();
		}
		?>
	</main>

<?php
if ( ! RealNorth\KopfFuss\elementor_bereich( 'footer' ) ) {
	RealNorth\KopfFuss\fusszeile();
}

wp_footer();
?>
</body>
</html>
