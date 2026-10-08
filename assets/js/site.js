/*
 * realnorth.ch — das Wenige, was JavaScript braucht.
 *
 * Zwei Dinge, unabhängig voneinander:
 *   1. Kennzahlen hochzählen. Reine Zugabe — ohne JavaScript steht der
 *      fertige Wert bereits im Markup.
 *   2. Das Bilderkarussell. Das ist Funktion, keine Zierde: ohne
 *      JavaScript bleibt das Raster ein Raster, und jedes Bild ist über
 *      seinen eigenen Link erreichbar.
 *
 * Deshalb schaltet `prefers-reduced-motion` nur das Zählen ab, nicht das
 * Karussell. Wer keine Bewegung will, soll trotzdem die Bilder sehen.
 */
( function () {
	'use strict';

	var ruhig = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ---------- 1. Kennzahlen ---------- */

	/**
	 * Hochzählen, sobald das Band ins Bild kommt.
	 *
	 * Gezählt wird nur der Zahlenteil; Vor- und Nachsatz («24 h», «39 %»)
	 * bleiben stehen, damit aus «2.5–5.5» keine Zahl wird.
	 */
	function zaehlenAnmelden() {
		if ( ruhig || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var beobachter = new IntersectionObserver( function ( eintraege ) {
			eintraege.forEach( function ( eintrag ) {
				if ( ! eintrag.isIntersecting ) {
					return;
				}

				beobachter.unobserve( eintrag.target );
				zaehle( eintrag.target );
			} );
		}, { threshold: 0.4 } );

		document.querySelectorAll( '[data-rn-zahl]' ).forEach( function ( element ) {
			beobachter.observe( element );
		} );
	}

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

	/* ---------- 2. Karussell ---------- */

	/**
	 * Jeder Container mit der Klasse `rn-karussell` wird zu einer Galerie:
	 * ein Klick auf ein Bild öffnet es gross, mit Vor und Zurück über alle
	 * Bilder desselben Containers.
	 *
	 * Der Builder kennt kein Karussell-Widget. Statt eines nachgebauten
	 * Sliders im Seiteninhalt steht die Mechanik deshalb hier — einmal,
	 * reviewbar, und im Builder reicht die Klasse im Feld «CSS-Klassen».
	 *
	 * Die Beschriftung kommt aus dem Alternativtext des Bildes. Damit gibt
	 * es nur eine Quelle: ein gepflegter Alternativtext ist zugleich die
	 * Bildlegende.
	 */
	var dialog = null;
	var bilder = [];
	var stelle = 0;
	var oeffner = null;

	/**
	 * Aus einem srcset die grösste Fassung ziehen.
	 *
	 * Im Raster genügt eine kleine Datei, gross aufgezogen will man die
	 * grosse. Ohne srcset bleibt es bei dem, was dasteht.
	 */
	function grossteQuelle( bild ) {
		var satz = bild.getAttribute( 'srcset' );

		if ( ! satz ) {
			return bild.currentSrc || bild.src;
		}

		var beste = { breite: 0, url: bild.currentSrc || bild.src };

		satz.split( ',' ).forEach( function ( teil ) {
			var stueck = teil.trim().split( /\s+/ );
			var breite = parseInt( stueck[ 1 ], 10 );

			if ( stueck[ 0 ] && ! isNaN( breite ) && breite > beste.breite ) {
				beste = { breite: breite, url: stueck[ 0 ] };
			}
		} );

		return beste.url;
	}

	function dialogBauen() {
		if ( dialog ) {
			return dialog;
		}

		dialog = document.createElement( 'dialog' );
		dialog.className = 'rn-carousel';
		dialog.innerHTML =
			'<div class="rn-carousel-head">' +
				'<div><span class="rn-carousel-kicker"></span><h2 class="rn-carousel-titel"></h2></div>' +
				'<button type="button" class="rn-carousel-zu" aria-label="Schliessen">&times;</button>' +
			'</div>' +
			'<div class="rn-carousel-stage">' +
				'<button type="button" class="rn-carousel-zurueck" aria-label="Vorheriges Bild">&lsaquo;</button>' +
				'<img class="rn-carousel-photo" alt="">' +
				'<button type="button" class="rn-carousel-vor" aria-label="Nächstes Bild">&rsaquo;</button>' +
			'</div>' +
			'<div class="rn-carousel-footer">' +
				'<p class="rn-carousel-caption"></p>' +
				'<span class="rn-carousel-count"></span>' +
			'</div>' +
			'<div class="rn-carousel-dots"></div>';

		document.body.appendChild( dialog );

		dialog.querySelector( '.rn-carousel-zu' ).addEventListener( 'click', function () {
			dialog.close();
		} );
		dialog.querySelector( '.rn-carousel-zurueck' ).addEventListener( 'click', function () {
			blaettern( -1 );
		} );
		dialog.querySelector( '.rn-carousel-vor' ).addEventListener( 'click', function () {
			blaettern( 1 );
		} );

		dialog.addEventListener( 'keydown', function ( e ) {
			if ( 'ArrowLeft' === e.key ) {
				blaettern( -1 );
			} else if ( 'ArrowRight' === e.key ) {
				blaettern( 1 );
			}
		} );

		// Klick auf den Hintergrund schliesst. Das <dialog> selbst füllt
		// den ganzen Bildschirm, also zählt nur ein Treffer ausserhalb
		// seines Kastens.
		dialog.addEventListener( 'click', function ( e ) {
			var kasten = dialog.getBoundingClientRect();
			var drin = e.clientX >= kasten.left && e.clientX <= kasten.right
				&& e.clientY >= kasten.top && e.clientY <= kasten.bottom;

			if ( ! drin ) {
				dialog.close();
			}
		} );

		dialog.addEventListener( 'close', function () {
			if ( oeffner ) {
				oeffner.focus();
				oeffner = null;
			}
		} );

		var startX = null;

		dialog.addEventListener( 'touchstart', function ( e ) {
			startX = e.changedTouches[ 0 ].clientX;
		}, { passive: true } );

		dialog.addEventListener( 'touchend', function ( e ) {
			if ( null === startX ) {
				return;
			}

			var weg = e.changedTouches[ 0 ].clientX - startX;
			startX = null;

			if ( Math.abs( weg ) > 40 ) {
				blaettern( weg > 0 ? -1 : 1 );
			}
		}, { passive: true } );

		return dialog;
	}

	function zeigen() {
		var quelle = bilder[ stelle ];
		var foto = dialog.querySelector( '.rn-carousel-photo' );
		var text = quelle.getAttribute( 'alt' ) || '';

		foto.src = grossteQuelle( quelle );
		foto.alt = text;
		dialog.querySelector( '.rn-carousel-caption' ).textContent = text;
		dialog.querySelector( '.rn-carousel-count' ).textContent =
			( stelle + 1 ) + ' / ' + bilder.length;

		dialog.querySelectorAll( '.rn-carousel-dot' ).forEach( function ( punkt, i ) {
			punkt.setAttribute( 'aria-current', i === stelle ? 'true' : 'false' );
		} );
	}

	function blaettern( schritt ) {
		stelle = ( stelle + schritt + bilder.length ) % bilder.length;
		zeigen();
	}

	function punkteBauen() {
		var leiste = dialog.querySelector( '.rn-carousel-dots' );
		leiste.innerHTML = '';

		if ( bilder.length < 2 ) {
			return;
		}

		bilder.forEach( function ( _, i ) {
			var punkt = document.createElement( 'button' );
			punkt.type = 'button';
			punkt.className = 'rn-carousel-dot';
			punkt.setAttribute( 'aria-label', 'Bild ' + ( i + 1 ) );
			punkt.addEventListener( 'click', function () {
				stelle = i;
				zeigen();
			} );
			leiste.appendChild( punkt );
		} );
	}

	/**
	 * Den Titel für den Kopf des Karussells bestimmen.
	 *
	 * Bevorzugt `data-rn-titel`. Der Builder lässt eigene Attribute aber
	 * nicht zu, deshalb der Rückfall: die letzte Überschrift, die im
	 * Dokument vor dem Container steht. In einem Reiter ist das dessen
	 * eigene Überschrift, und damit stimmt es von selbst. Findet sich
	 * keine, bleibt der Kopf leer — Legende und Zähler tragen die
	 * Information ohnehin.
	 */
	function titelFinden( container ) {
		if ( container.getAttribute( 'data-rn-titel' ) ) {
			return container.getAttribute( 'data-rn-titel' );
		}

		var ueberschriften = document.querySelectorAll( 'h1, h2, h3, h4' );
		var gefunden = '';

		ueberschriften.forEach( function ( kopf ) {
			var davor = kopf.compareDocumentPosition( container )
				& Node.DOCUMENT_POSITION_FOLLOWING;

			if ( davor && kopf.offsetParent !== null ) {
				gefunden = kopf.textContent.trim();
			}
		} );

		return gefunden;
	}

	function oeffnen( container, bild ) {
		dialogBauen();

		bilder = Array.prototype.slice.call( container.querySelectorAll( 'img' ) );
		stelle = Math.max( 0, bilder.indexOf( bild ) );
		oeffner = bild;

		var titel = titelFinden( container );
		var kicker = container.getAttribute( 'data-rn-kicker' ) || '';

		dialog.querySelector( '.rn-carousel-titel' ).textContent = titel;
		dialog.querySelector( '.rn-carousel-kicker' ).textContent = kicker;

		punkteBauen();
		zeigen();

		if ( dialog.showModal ) {
			dialog.showModal();
		}
	}

	function karussellAnmelden() {
		if ( ! document.querySelector( '.rn-karussell' ) ) {
			return;
		}

		// Ein Zuhörer am Dokument statt einer pro Bild: so wirkt es auch
		// auf Bilder, die erst später im DOM landen — etwa in einem
		// Reiter, den Elementor beim Wechsel neu aufbaut.
		document.addEventListener( 'click', function ( e ) {
			var bild = e.target.closest( '.rn-karussell img' );

			if ( ! bild ) {
				return;
			}

			var container = bild.closest( '.rn-karussell' );

			if ( ! container ) {
				return;
			}

			e.preventDefault();
			oeffnen( container, bild );
		} );

		// Tastaturbedienung: die Bilder sind keine Knöpfe, bekommen aber
		// Fokus und reagieren auf Enter und Leertaste.
		document.querySelectorAll( '.rn-karussell img' ).forEach( function ( bild ) {
			bild.setAttribute( 'tabindex', '0' );
			bild.setAttribute( 'role', 'button' );

			bild.addEventListener( 'keydown', function ( e ) {
				if ( 'Enter' === e.key || ' ' === e.key ) {
					e.preventDefault();
					oeffnen( bild.closest( '.rn-karussell' ), bild );
				}
			} );
		} );
	}

	function los() {
		zaehlenAnmelden();
		karussellAnmelden();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', los );
	} else {
		los();
	}
} )();
