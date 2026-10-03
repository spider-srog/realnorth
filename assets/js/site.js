/*
 * realnorth.ch — das Wenige, was JavaScript braucht.
 *
 * Alles hier ist Zugabe: ohne JavaScript steht der fertige Wert bereits
 * im Markup, und die Abschnitte sind sichtbar. Wer die Bewegung
 * abbestellt hat (prefers-reduced-motion), bekommt gar nichts davon.
 */
( function () {
	'use strict';

	var ruhig = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	if ( ruhig || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	/**
	 * Kennzahlen hochzählen, sobald das Band ins Bild kommt.
	 *
	 * Gezählt wird nur der Zahlenteil; Vor- und Nachsatz («24 h», «39 %»)
	 * bleiben stehen, damit aus «2.5–5.5» keine Zahl wird.
	 */
	var beobachter = new IntersectionObserver( function ( eintraege ) {
		eintraege.forEach( function ( eintrag ) {
			if ( ! eintrag.isIntersecting ) {
				return;
			}

			beobachter.unobserve( eintrag.target );
			zaehle( eintrag.target );
		} );
	}, { threshold: 0.4 } );

	function zaehle( element ) {
		var ziel = parseFloat( element.dataset.rnZahl );

		if ( isNaN( ziel ) || ziel <= 0 ) {
			return;
		}

		var text = element.textContent;
		var teil = text.split( String( ziel ) );
		var vor = teil.length > 1 ? teil[ 0 ] : '';
		var nach = teil.length > 1 ? teil[ 1 ] : '';
		var start = null;
		var dauer = 900;

		function schritt( zeit ) {
			if ( null === start ) {
				start = zeit;
			}

			var anteil = Math.min( ( zeit - start ) / dauer, 1 );
			var wert = Math.round( ziel * ( 1 - Math.pow( 1 - anteil, 3 ) ) );

			element.textContent = vor + wert + nach;

			if ( anteil < 1 ) {
				window.requestAnimationFrame( schritt );
			} else {
				element.textContent = text;
			}
		}

		window.requestAnimationFrame( schritt );
	}

	document.querySelectorAll( '[data-rn-zahl]' ).forEach( function ( element ) {
		beobachter.observe( element );
	} );
} )();
