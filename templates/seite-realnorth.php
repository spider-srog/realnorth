<?php
/**
 * Template Name: realnorth (Vollbreite)
 *
 * Vollständiges Dokument: Kopf und Fuss kommen aus dem Plugin, das Theme
 * ist auf diesen Seiten nicht beteiligt. Angemeldet wird die Vorlage in
 * includes/vorlage.php.
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
<body <?php body_class( 'rn-body' ); ?>>
<?php wp_body_open(); ?>
<div class="rn-site">
	<?php RealNorth\KopfFuss\kopfzeile(); ?>

	<main id="rn-inhalt">
		<?php
		while ( have_posts() ) {
			the_post();
			the_content();
		}
		?>
	</main>

	<?php RealNorth\KopfFuss\fusszeile(); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
