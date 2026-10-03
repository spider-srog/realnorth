<?php
/**
 * Die Seiten des Entwurfs in WordPress anlegen.
 *
 * Der Inhalt landet als Seiteninhalt in der Datenbank — Texte sind
 * danach ganz normal im Editor änderbar. Das Layout steckt nicht mit
 * drin, sondern in den Shortcodes aus includes/abschnitte.php; so bleibt
 * der Seiteninhalt lesbar und das Aussehen reviewbar.
 *
 * Der Lauf ist wiederholbar: gefunden wird über den Permalink-Namen.
 * Eine bestehende Seite wird aktualisiert, nicht verdoppelt. Wer eine
 * Seite von Hand geändert hat, verliert diese Änderung beim nächsten
 * Lauf — deshalb fragt die Importseite vorher.
 *
 * @package RealNorth
 */

declare( strict_types=1 );

namespace RealNorth\Seiten;

use RealNorth\Vorlage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alle Seiten mit Titel, Elternseite und Inhalt.
 *
 * Rein, ohne WordPress-Aufruf — damit testbar.
 *
 * @return array<string, array{titel: string, eltern: string, inhalt: string}>
 */
function seiten(): array {
	return array(
		'startseite'      => array(
			'titel'  => 'Start',
			'eltern' => '',
			'inhalt' => startseite(),
		),
		'wohnungen'       => array(
			'titel'  => 'Freie Wohnungen',
			'eltern' => '',
			'inhalt' => wohnungen(),
		),
		'liegenschaften'  => array(
			'titel'  => 'Mietwohnungen',
			'eltern' => '',
			'inhalt' => liegenschaften(),
		),
		'rennweg-14-16'   => array(
			'titel'  => 'Rennweg 14/16',
			'eltern' => 'liegenschaften',
			'inhalt' => rennweg(),
		),
		'entwicklung'     => array(
			'titel'  => 'Entwicklung',
			'eltern' => '',
			'inhalt' => entwicklung(),
		),
		'ueber-uns'       => array(
			'titel'  => 'Über uns',
			'eltern' => '',
			'inhalt' => ueber_uns(),
		),
		'mieterservice'   => array(
			'titel'  => 'Mieterservice',
			'eltern' => '',
			'inhalt' => mieterservice(),
		),
		'kontakt'         => array(
			'titel'  => 'Kontakt',
			'eltern' => '',
			'inhalt' => kontakt(),
		),
	);
}

/**
 * Die Unternavigation der Liegenschaften.
 *
 * @param string $aktuell Permalink-Name der aktuellen Seite.
 */
function portfolio_nav( string $aktuell ): string {
	$punkte = array(
		'liegenschaften' => array( 'Mietwohnungen', '/liegenschaften/' ),
		'rennweg-14-16'  => array( 'Rennweg 14/16', '/liegenschaften/rennweg-14-16/' ),
	);

	$links = '';

	foreach ( $punkte as $name => $punkt ) {
		$links .= sprintf(
			'<a href="%s"%s>%s</a>',
			$punkt[1],
			$name === $aktuell ? ' aria-current="page"' : '',
			$punkt[0]
		);
	}

	return '<div class="rn-portfolio-nav"><span>Liegenschaften</span>'
		. '<nav aria-label="Liegenschaften">' . $links . '</nav></div>';
}

/**
 * Inhalt der Startseite.
 */
function startseite(): string {
	return <<<'HTML'
[rn_band grund="flaeche" klasse="rn-buehne"]
[rn_buehne kicker="Bewirtschaftung · Entwicklung · Bestand" titel='Wohnungen<br>bewirtschaften.<br><span class="rn-buehne__akzent">Gebiete entwickeln.</span>' lead="Real North hält, vermietet und betreut den eigenen Wohnungsbestand — und entwickelt daneben neue Areale. Kurze Wege, feste Ansprechpersonen, ein langer Zeithorizont." bild="buehne" bildtitel="Im Birkenhain · Rudolfstetten" bildzeile="Projektvisualisierung" knopf1="Freie Wohnungen" url1="/wohnungen/" knopf2="Areal Birkenhain" url2="/entwicklung/"]
[/rn_band]

[rn_band grund="ruhig" luft="schmal"]
<div class="rn-schritte__titel">Was wir tun</div>
[rn_raster spalten="3"]
[rn_schritt nummer="01" titel="Bewirtschaftung"]Vermietung, Mieterbetreuung, Abrechnung und Instandhaltung — im eigenen Haus statt über Dritte. Wer hier anruft, spricht mit der Person, die das Objekt kennt.[/rn_schritt]
[rn_schritt nummer="02" titel="Entwicklung"]Von der Landreserve zum Quartier: Gestaltungsplan, Etappierung, Bau. Wir arbeiten mit Gemeinden, Planern und der Nachbarschaft — und bleiben nach der Übergabe Eigentümerin.[/rn_schritt]
[rn_schritt nummer="03" titel="Bestand halten"]Wir kaufen, um zu behalten. Erneuerung in Etappen, faire Mieten, planbare Zyklen — statt kurzfristiger Wertsteigerung auf Kosten der Substanz.[/rn_schritt]
[/rn_raster]
[/rn_band]

[rn_band grund="dunkel" luft="schmal"]
[rn_raster spalten="4"]
[rn_zahl wert="250" text="Wohnungen im Bestand"]
[rn_zahl wert="4" text="Standorte"]
[rn_zahl wert="1" text="Areal in Entwicklung"]
[rn_zahl wert="24 h" text="Rückmeldung auf Anliegen"]
[/rn_raster]
[/rn_band]

[rn_band grund="flaeche"]
[rn_abschnittskopf titel="Aktuell frei" linktext="Alle Wohnungen →" url="/wohnungen/"]
[rn_wohnungen darstellung="karten" anzahl="3"]
[/rn_band]

[rn_band grund="weiss"]
[rn_raster spalten="split"]
[rn_spalte]
[rn_bild platz="birkenhain"]
[/rn_spalte]
[rn_spalte]
<div class="rn-kicker">Arealentwicklung</div>
<h2>Im Birkenhain</h2>
<p>Ein neues Quartier am Isleren-Wald in Rudolfstetten: 278 Mietwohnungen in 17 gestaffelten Townhouses statt in grossen Blöcken, dazu Kita und Kindergarten, Bistro, Co-Working und Gym am Quartierplatz. Wettbewerbsprojekt SAOTA, Gestaltungsplan «Im Birkenhain» im Verfahren.</p>
[rn_merkmale]
[rn_merkmal begriff="Gemeinde" wert="Rudolfstetten-Friedlisberg AG"]
[rn_merkmal begriff="Stand" wert="Kantonale Vorprüfung"]
[rn_merkmal begriff="Umfang" wert="278 Mietwohnungen"]
[/rn_merkmale]
<p>[rn_knopf url="https://birkenhain.ch"]birkenhain.ch ↗[/rn_knopf]</p>
[/rn_spalte]
[/rn_raster]
[/rn_band]

[rn_band grund="flaeche"]
[rn_aufruf titel="Liegenschaft oder Land anbieten" knopf="Objekt einreichen" url="/kontakt/"]Wir prüfen Mehrfamilienhäuser und Baulandreserven in der Nordwest- und Zentralschweiz. Diskret, ohne Makler, mit Antwort innerhalb einer Woche.[/rn_aufruf]
[/rn_band]
HTML;
}

/**
 * Inhalt der Seite «Freie Wohnungen».
 */
function wohnungen(): string {
	return <<<'HTML'
[rn_band grund="flaeche"]
[rn_kopf kicker="Vermietung" titel="Freie Wohnungen" lead="Alle Objekte werden direkt von uns vermietet. Bewerbung online, Besichtigung nach Absprache, Entscheid in der Regel innerhalb von fünf Arbeitstagen."]
[/rn_band]

[rn_band grund="weiss"]
[rn_wohnungen darstellung="tabelle" filter="ja"]
[/rn_band]

[rn_band grund="dunkel"]
<h2>So läuft die Bewerbung</h2>
[rn_raster spalten="3"]
[rn_schritt nummer="01" titel="Anfrage"]Formular ausfüllen, Objekt auswählen. Kein Login, keine Registrierung.[/rn_schritt]
[rn_schritt nummer="02" titel="Besichtigung"]Einzeltermin mit der zuständigen Person aus der Bewirtschaftung — keine Massenbesichtigung.[/rn_schritt]
[rn_schritt nummer="03" titel="Entscheid"]Unterlagen einreichen, Antwort innerhalb von fünf Arbeitstagen, Vertrag digital.[/rn_schritt]
[/rn_raster]
[/rn_band]

[rn_band grund="flaeche"]
[rn_raster spalten="split"]
[rn_spalte]
<h2>Suchabo</h2>
<p class="rn-lead">Nichts Passendes gefunden? Wir melden uns, bevor eine Wohnung öffentlich ausgeschrieben wird. Ein Mail, keine Werbung, jederzeit kündbar.</p>
[/rn_spalte]
[rn_spalte]
[rn_formular titel="Suchabo einrichten"]
[/rn_spalte]
[/rn_raster]
[/rn_band]
HTML;
}

/**
 * Inhalt der Seite «Mietwohnungen».
 */
function liegenschaften(): string {
	$nav = portfolio_nav( 'liegenschaften' );

	$rumpf = <<<'HTML'
[rn_band grund="flaeche"]
[rn_kopf kicker="Portfolio" titel="Mietwohnungen" lead="Vier Standorte, alle im eigenen Eigentum und in eigener Bewirtschaftung. Gekauft wird, was wir dauerhaft halten und selber betreuen können — kein Handel, keine Zwischennutzung."]
[/rn_band]

[rn_band grund="weiss"]
[rn_bild platz="standortkarte" bildzeile="Standorte Mutschellen, Zürich und Winterthur"]
<table class="rn-tabelle">
<thead><tr><th scope="col">Standort</th><th scope="col">Objekte</th><th scope="col">Wohnungen</th><th scope="col">Baujahr / Sanierung</th><th scope="col">Schwerpunkt</th></tr></thead>
<tbody>
<tr><td>Berikon</td><td>Bergstrasse, Tannhofweg</td><td>96</td><td>1978 / 2019–2022</td><td>Familienwohnungen</td></tr>
<tr><td>Rudolfstetten</td><td>Islerenweg</td><td>64</td><td>1994 / laufend</td><td>Bestand + Entwicklung</td></tr>
<tr><td>Zürich</td><td>Seestrasse</td><td>42</td><td>1962 / 2024</td><td>Stadtwohnungen</td></tr>
<tr><td>Winterthur</td><td>Seenerstrasse</td><td>48</td><td>1986 / 2021</td><td>Preiswertes Wohnen</td></tr>
</tbody>
</table>
[/rn_band]

[rn_band grund="ruhig"]
<h2>Wie wir den Bestand pflegen</h2>
[rn_raster spalten="3"]
[rn_karte kicker="Zyklen" titel="Erneuerung in Etappen"]Küche, Bad und Haustechnik folgen einem festen Erneuerungsplan. Mieterinnen und Mieter wissen Jahre im Voraus, was wann kommt.[/rn_karte]
[rn_karte kicker="Energie" titel="Strom vom eigenen Dach"]Geheizt wird bei uns bis auf Weiteres fossil. Photovoltaik bauen wir trotzdem — auf jedem Dach, das sich eignet. Der Verbrauch wird pro Liegenschaft ausgewiesen.[/rn_karte]
[rn_karte kicker="Nachbarschaft" titel="Hauswart vor Ort"]Pro Standort eine feste Bezugsperson für Unterhalt und Umgebung — erreichbar, bekannt, mit Schlüssel.[/rn_karte]
[/rn_raster]
[/rn_band]
HTML;

	return $nav . "\n\n" . $rumpf;
}

/**
 * Inhalt der Seite «Rennweg 14/16».
 *
 * Die Bildarchive der früheren Mieter (Companys, Calida, Breitling)
 * fehlen hier bewusst: dafür müssen erst 26 Fotos in die Mediathek. Der
 * Rest der Seite steht.
 */
function rennweg(): string {
	$nav = portfolio_nav( 'rennweg-14-16' );

	$rumpf = <<<'HTML'
[rn_band grund="ruhig" klasse="rn-rennweg"]
<div class="rn-rennweg-intro">
<div class="rn-intro-copy">
<div class="rn-property-kicker">Zürich · Rennweg 14/16</div>
<h2>Ein Haus voller<br>Geschichte.<br>Raum für heute.</h2>
<p>Zwischen Rennweg und Lindenhof verbinden sich historische Architektur, vier Residenzen und rund 300 m² Verkaufsfläche über drei Ebenen.</p>
<span class="rn-intro-detail">Wohnen &amp; Retail · Zürcher Altstadt</span>
</div>
[rn_bild platz="rennweg-schnitt" bildzeile="Illustration nach historischem Gebäudeschnitt · Planstand 2016"]
</div>

<div class="rn-residence-media">
<figure class="rn-residence-film">
[rn_bild platz="rennweg-residenz"]
<figcaption><span>Einblicke in die Luxury Residences</span><span>Film · Widder Hotel</span></figcaption>
</figure>
<p class="rn-media-credit">Bild- und Filmmaterial: <a href="https://www.widderhotel.com/de/luxury-residences/" target="_blank" rel="noopener noreferrer">Widder Hotel · Luxury Residences ↗</a></p>
</div>

<div class="rn-property-uses">
<article>
<div class="rn-property-kicker">Wohnen · Obere Geschosse</div>
<h3>Vier Appartements.<br>Vermietet als Residenzen.</h3>
<p>Die vier Appartements in den oberen Geschossen werden über das Widder Hotel als Residenzen vermietet.</p>
<a class="rn-property-link" href="https://www.widderhotel.com/de/luxury-residences/" target="_blank" rel="noopener noreferrer">Residenzen beim Widder Hotel entdecken ↗</a>
</article>
<article>
<div class="rn-property-kicker">Retail · UG, EG und 1. OG</div>
<h3>Rund 300 m²<br>Verkaufsfläche.</h3>
<p>Die Verkaufsfläche erstreckt sich über das Untergeschoss, das Erdgeschoss und das erste Obergeschoss.</p>
<div class="rn-property-tenants"><span>Bisherige Mieter</span><p>Companys · Calida · Breitling</p></div>
</article>
</div>

<section class="rn-history">
<div class="rn-property-kicker">Geschichte &amp; Architektur</div>
<h3>Zwei Häuser. Jahrhunderte Geschichte.</h3>
<p class="rn-history-intro">Zwischen Rennweg und Lindenhof vereint die Liegenschaft zwei ursprünglich eigenständige Häuser. Ihre Geschichte reicht bis ins 14. Jahrhundert zurück – und ist bis heute an der unterschiedlichen Fenstereinteilung der Fassaden ablesbar.</p>
<ol class="rn-history-dates">
<li><strong>14. Jahrhundert</strong><h4>Die Ursprünge</h4><p>Die Häuser am Rennweg 14 und 16 werden erstmals verzeichnet. Der Rennweg, damals auch «Rain» genannt, lag zu jener Zeit am Stadtrand.</p></li>
<li><strong>19. Jahrhundert</strong><h4>Aus zwei wird eins</h4><p>Der «Schwarze Ochse» am Rennweg 14 und der «Rote Stern» am Rennweg 16 werden auch im Inneren zu einer zusammenhängenden Immobilie verbunden.</p></li>
<li><strong>1878</strong><h4>Ein neuer Zugang</h4><p>Mit dem Abtragen des erhöhten Rains verlagert sich der Hauseingang ein Geschoss nach unten. Aus dieser Zeit stammen auch die Bodenplatte des Erdgeschosses und die Unterkellerung.</p></li>
</ol>
<div class="rn-history-details">
<article><h4>Historische Substanz bewahren</h4><p>Der Umbau wurde in enger Zusammenarbeit mit den Behörden und auf Grundlage eines Unterschutzstellungs-Vertrags geplant. Sondierungen legten historische Parkettböden, Täfelungen aus dem 17. Jahrhundert sowie Stuckatur- und Kassettendecken frei, deren Erhalt einen sorgfältigen Rückbau erforderte.</p></article>
<article><h4>Gewölbe, Licht und individuelle Räume</h4><p>Das 2018 vorgestellte Konzept rückte den zweigeschossigen Gewölberaum ins Zentrum der Verkaufsfläche. Ein wieder freigelegter Lichthof und ein zusätzliches Oberlicht sollten Tageslicht in die hinteren Bereiche bringen. Für die Wohngeschosse waren individuelle Grundrisse mit jeweils einer Wohneinheit pro Etage vorgesehen.</p></article>
</div>
<div class="rn-history-source">
<p><strong>Der Umbau im Architekturbericht</strong><br>Projektleitung: Property One · Architektur: Ramseier &amp; Associates Ltd.</p>
<p>Quelle: «Das Ideale Heim», April 2018, «Eine Metamorphose – Am Rennweg, Etappe #1», S. 108–113. Redaktion: Anita Simeon Lutz. Die Angaben zum Umbau beschreiben den damaligen Planungs- und Baustand.</p>
</div>
</section>
[/rn_band]
HTML;

	return $nav . "\n\n" . $rumpf;
}

/**
 * Inhalt der Seite «Entwicklung».
 */
function entwicklung(): string {
	return <<<'HTML'
[rn_band grund="flaeche"]
[rn_kopf kicker="Arealentwicklung" titel="Neue Gebiete entwickeln" lead="Wir entwickeln Areale, die wir anschliessend selber halten. Das ändert die Reihenfolge der Fragen: erst wie es sich bewohnen lässt, dann wie es sich rechnet."]
[/rn_band]

[rn_band grund="ruhig"]
[rn_raster spalten="4"]
[rn_schritt nummer="01" titel="Areal und Recht"]Landerwerb, Machbarkeit, Abstimmung mit Gemeinde und Kanton.[/rn_schritt]
[rn_schritt nummer="02" titel="Gestaltungsplan"]Studienauftrag, Mitwirkung, öffentliche Auflage, Genehmigung.[/rn_schritt]
[rn_schritt nummer="03" titel="Bau in Etappen"]Baubewilligung, Realisierung Etappe für Etappe, Vermietungsstart.[/rn_schritt]
[rn_schritt nummer="04" titel="Betrieb"]Übergabe in die eigene Bewirtschaftung. Wir bleiben Eigentümerin.[/rn_schritt]
[/rn_raster]
[/rn_band]

[rn_band grund="weiss"]
<div class="rn-kicker">Projekt 01 · Rudolfstetten-Friedlisberg AG · Kantonale Vorprüfung</div>
[rn_abschnittskopf titel="Im Birkenhain"]
[rn_bild platz="birkenhain" bildzeile="Richtprojekt Lötscher Architektur nach Wettbewerbsprojekt SAOTA · Visualisierung Real North AG, unverbindlich"]
[/rn_band]

[rn_band grund="dunkel" luft="schmal"]
[rn_raster spalten="5"]
[rn_zahl wert="278" text="Mietwohnungen"]
[rn_zahl wert="2.5–5.5" text="Zimmer"]
[rn_zahl wert="17" text="gestaffelte Häuser"]
[rn_zahl wert="39 %" text="unbebaut"]
[rn_zahl wert="10 Min." text="zur S17"]
[/rn_raster]
[/rn_band]

[rn_band grund="flaeche"]
[rn_raster spalten="split"]
[rn_spalte]
<p>Zwischen dem Siedlungsrand von Rudolfstetten und dem Isleren-Wald entsteht ein eigenes Quartier: 17 gestaffelte Townhouses von drei bis acht Geschossen, gruppiert um einen Quartierplatz mit dem namengebenden Birkenhain. Oberirdisch bleibt das Areal autofrei, 39 Prozent der Fläche unbebaut.</p>
<p>Am Platz liegen Bistro und Co-Working, Gym, Naturpool sowie Kita und Kindergarten mit 290 m² und eigenem Aussenraum. Gründächer, Photovoltaik und Retention gehören zum Konzept; Dachgärten und Fusswege ziehen den Waldbezug durch das ganze Areal.</p>
<p>Rudolfstetten-Friedlisberg liegt auf dem Mutschellen auf 550 m zwischen Reusstal und Limmattal. Zu den Haltestellen Berikon-Widen und Hofacker sind es zehn Gehminuten, nach Zürich HB 31 Minuten. Pläne, Termine und die Interessentenliste führen wir auf der Projektwebsite.</p>
<p>[rn_knopf url="https://birkenhain.ch"]birkenhain.ch ↗[/rn_knopf]</p>
[/rn_spalte]
[rn_spalte]
[rn_merkmale]
[rn_merkmal begriff="Gemeinde" wert="Rudolfstetten-Friedlisberg AG"]
[rn_merkmal begriff="Instrument" wert="Gestaltungsplan «Im Birkenhain»"]
[rn_merkmal begriff="Wohnungen" wert="278 Miete, 2.5–5.5 Zimmer"]
[rn_merkmal begriff="Nutzung" wert="Wohnen, Kita &amp; Kindergarten, Bistro, Co-Working, Gym"]
[rn_merkmal begriff="Wettbewerb" wert="SAOTA, 2020–22"]
[rn_merkmal begriff="Richtprojekt" wert="Lötscher Architektur, 2025"]
[rn_merkmal begriff="Erschliessung" wert="oberirdisch autofrei"]
[rn_merkmal begriff="Vermietungsstart" wert="nach Rechtskraft, etappiert"]
[/rn_merkmale]
[/rn_spalte]
[/rn_raster]
[/rn_band]

[rn_band grund="weiss"]
[rn_raster spalten="split"]
[rn_spalte]
<div class="rn-kicker">Verfahren</div>
<h2>Wo der Gestaltungsplan steht</h2>
<p class="rn-lead">Das Quartier entsteht auf Grundlage des Gestaltungsplans «Im Birkenhain». Alle Unterlagen sind öffentlich — Fragen aus der Nachbarschaft beantworten wir jederzeit.</p>
[/rn_spalte]
[rn_spalte]
[rn_ablauf]
[rn_etappe zeit="2016–17"]Studienauftrag der Gemeinde, sechs Planungsteams[/rn_etappe]
[rn_etappe zeit="2020–22"]Architekturwettbewerb, Siegerprojekt SAOTA[/rn_etappe]
[rn_etappe zeit="08.2025"]Richtprojekt, überarbeitet durch Lötscher Architektur[/rn_etappe]
[rn_etappe zeit="12.2025" jetzt="ja"]Kantonale Vorprüfung — aktueller Stand[/rn_etappe]
[rn_etappe zeit="danach"]Öffentliche Auflage, Beschluss Gemeinderat, Genehmigung Kanton[/rn_etappe]
[rn_etappe zeit="später"]Baugesuch, etappierter Baustart[/rn_etappe]
[/rn_ablauf]
[/rn_spalte]
[/rn_raster]
[/rn_band]

[rn_band grund="ruhig"]
<h2>Bilder aus dem Richtprojekt</h2>
[rn_raster spalten="3"]
<div class="rn-galerie">[rn_bild platz="birkenhain-gasse" bildzeile="Ankunft am Quartier"]</div>
<div class="rn-galerie">[rn_bild platz="birkenhain-pool" bildzeile="Naturpool am Waldrand"]</div>
<div class="rn-galerie">[rn_bild platz="birkenhain-dach" bildzeile="Dachgarten über dem Hain"]</div>
[/rn_raster]
[/rn_band]

[rn_band grund="dunkel"]
[rn_raster spalten="split"]
[rn_spalte]
<h2>Land oder Liegenschaft anbieten</h2>
<p class="rn-lead">Wir prüfen jedes Dossier selber und antworten innerhalb einer Woche — auch wenn es nicht passt. Kein Weiterverkauf, keine Vermittlung an Dritte.</p>
[/rn_spalte]
[rn_spalte]
[rn_merkmale]
[rn_merkmal begriff="01" wert="Mehrfamilienhäuser ab 6 Wohnungen"]
[rn_merkmal begriff="02" wert="Bauland und Landreserven mit Entwicklungspotenzial"]
[rn_merkmal begriff="03" wert="Region Zentral- und Nordwestschweiz, Grossraum Zürich"]
[/rn_merkmale]
<p>[rn_knopf url="/kontakt/"]Dossier einreichen[/rn_knopf]</p>
[/rn_spalte]
[/rn_raster]
[/rn_band]
HTML;
}

/**
 * Inhalt der Seite «Über uns».
 */
function ueber_uns(): string {
	return <<<'HTML'
[rn_band grund="flaeche"]
[rn_kopf kicker="Über uns" titel="Eine Eigentümerin, die selber bewirtschaftet." lead="Real North ist ein kleines Team mit eigenem Bestand. Wir kaufen, entwickeln, vermieten und unterhalten selber — deshalb gibt es bei uns keine Weiterleitung an eine externe Verwaltung."]
[/rn_band]

[rn_band grund="ruhig"]
[rn_raster spalten="3"]
[rn_schritt titel="Langfristig"]Kein Objekt wird gekauft, um es weiterzugeben. Wir rechnen in Jahrzehnten, nicht in Quartalen.[/rn_schritt]
[rn_schritt titel="Direkt"]Eine Nummer, eine zuständige Person pro Liegenschaft. Anliegen werden beantwortet, auch die unbequemen.[/rn_schritt]
[rn_schritt titel="Ortsverbunden"]Wir entwickeln dort, wo wir bereits Häuser haben — und sprechen mit der Gemeinde, bevor die Pläne fertig sind.[/rn_schritt]
[/rn_raster]
[/rn_band]

[rn_band grund="weiss"]
[rn_abschnittskopf titel="Team"]
[rn_team spalten="4"]
[/rn_band]

[rn_band grund="flaeche"]
[rn_raster spalten="split"]
[rn_spalte]
<h2>Offene Stellen</h2>
<p class="rn-lead">Spontanbewerbungen sind willkommen — an die Adresse im Fussbereich.</p>
[/rn_spalte]
[rn_spalte]
[rn_stelle titel="Sachbearbeitung Bewirtschaftung 80–100 %" detail="Hauptsitz · per sofort oder nach Vereinbarung" url="/kontakt/"]
[rn_stelle titel="Hauswart Region Mutschellen 60 %" detail="Berikon / Rudolfstetten · per Anfang Jahr" url="/kontakt/"]
[/rn_spalte]
[/rn_raster]
[/rn_band]
HTML;
}

/**
 * Inhalt der Seite «Mieterservice».
 */
function mieterservice(): string {
	return <<<'HTML'
[rn_band grund="flaeche"]
[rn_kopf kicker="Für Mieterinnen und Mieter" titel="Mieterservice" lead="Das Wichtigste in vier Wegen. Alles andere geht direkt an die zuständige Person Ihrer Liegenschaft — ohne Ticketnummer."]
[/rn_band]

[rn_band grund="weiss"]
[rn_raster spalten="2"]
[rn_karte kicker="01" titel="Schaden oder Störung melden" knopf="Meldung erfassen" url="/kontakt/" stil="laut"]Tropfender Hahn, defekte Storen, Heizung kalt: Formular mit Foto, Bearbeitung innerhalb von 24 Stunden an Werktagen.[/rn_karte]
[rn_karte kicker="02" titel="Kündigung und Nachmieter" knopf="Unterlagen und Fristen" url="/kontakt/"]Kündigungsfristen, Vorlage für die Kündigung, Anmeldung von Nachmietern und Ablauf der Wohnungsabgabe.[/rn_karte]
[rn_karte kicker="03" titel="Nebenkosten und Abrechnung" knopf="Erklärt" url="/kontakt/"]Wie sich Ihre Akontozahlungen zusammensetzen, wann die Abrechnung kommt und wie Sie sie lesen.[/rn_karte]
[rn_karte kicker="04" titel="Umbauen, Bohren, Haustiere" knopf="Hausordnung" url="/kontakt/"]Was ohne Rückfrage erlaubt ist, was eine kurze Zustimmung braucht — auf einer Seite zusammengefasst.[/rn_karte]
[/rn_raster]
[/rn_band]

[rn_band grund="dunkel"]
[rn_raster spalten="split"]
[rn_spalte]
<div class="rn-kicker">Notfall</div>
<h2>Wasser, Heizung, Strom — ausserhalb der Bürozeiten</h2>
[/rn_spalte]
[rn_spalte]
[rn_merkmale]
[rn_merkmal begriff="Sanitär / Heizung" wert="+41 00 000 00 00"]
[rn_merkmal begriff="Elektro" wert="+41 00 000 00 00"]
[rn_merkmal begriff="Lift" wert="+41 00 000 00 00"]
[/rn_merkmale]
[/rn_spalte]
[/rn_raster]
[/rn_band]

[rn_band grund="flaeche"]
[rn_raster spalten="split"]
[rn_spalte]
<h2>Anliegen erfassen</h2>
<p class="rn-lead">Geht direkt an die Person, die Ihre Liegenschaft betreut. Werktags eine Antwort innerhalb eines Arbeitstages.</p>
[/rn_spalte]
[rn_spalte]
[rn_formular titel="Anliegen erfassen"]
[/rn_spalte]
[/rn_raster]
[/rn_band]
HTML;
}

/**
 * Inhalt der Seite «Kontakt».
 */
function kontakt(): string {
	return <<<'HTML'
[rn_band grund="flaeche"]
[rn_kopf kicker="Kontakt" titel="Reden wir" lead="Mietinteresse, Anliegen als Mieterin, ein Objekt zum Verkauf oder eine Anfrage aus einer Gemeinde — alles kommt hier an."]
[/rn_band]

[rn_band grund="weiss"]
[rn_raster spalten="split"]
[rn_spalte]
<h3>Hauptsitz</h3>
<p>Real North AG<br>Unternehmer-Park 3<br>6340 Baar</p>
<h3>Direkt</h3>
<p><a href="tel:+41415525363">+41 41 552 53 63</a><br><a href="mailto:verwaltung@realnorth.ch">verwaltung@realnorth.ch</a></p>
<h3>Bürozeiten</h3>
<p>Mo – Do 08.00 – 12.00 / 13.30 – 17.00<br>Fr 08.00 – 12.00</p>
[/rn_spalte]
[rn_spalte]
[rn_formular titel="Nachricht senden"]
[/rn_spalte]
[/rn_raster]
[/rn_band]

[rn_band grund="ruhig"]
[rn_bild platz="kontaktkarte"]
[/rn_band]
HTML;
}

/**
 * Eine Seite anlegen oder aktualisieren.
 *
 * @param string $name   Permalink-Name.
 * @param array{titel: string, eltern: string, inhalt: string} $daten Seitendaten.
 * @param int    $eltern_id ID der Elternseite, 0 = keine.
 * @return array{id: int, ergebnis: string}
 */
function speichere( string $name, array $daten, int $eltern_id = 0 ): array {
	$vorhanden = get_page_by_path( '' !== $daten['eltern'] ? $daten['eltern'] . '/' . $name : $name, OBJECT, 'page' );

	$felder = array(
		'post_type'    => 'page',
		'post_name'    => $name,
		'post_title'   => $daten['titel'],
		'post_content' => $daten['inhalt'],
		'post_status'  => 'publish',
		'post_parent'  => $eltern_id,
	);

	if ( $vorhanden instanceof \WP_Post ) {
		$felder['ID'] = $vorhanden->ID;
		$id           = wp_update_post( $felder, true );
		$ergebnis     = 'aktualisiert';
	} else {
		$id       = wp_insert_post( $felder, true );
		$ergebnis = 'neu';
	}

	if ( is_wp_error( $id ) ) {
		return array( 'id' => 0, 'ergebnis' => 'fehler' );
	}

	update_post_meta( (int) $id, '_wp_page_template', Vorlage\DATEI );

	return array( 'id' => (int) $id, 'ergebnis' => $ergebnis );
}

/**
 * Alle Seiten anlegen und die Startseite setzen.
 *
 * @return array<string, int> Zählwerte je Ergebnis.
 */
function importiere(): array {
	$zaehler = array( 'neu' => 0, 'aktualisiert' => 0, 'fehler' => 0 );
	$ids     = array();

	// Erst die Seiten ohne Elternteil, damit die Kindseite ihre ID kennt.
	foreach ( array( false, true ) as $kinder ) {
		foreach ( seiten() as $name => $daten ) {
			if ( ( '' !== $daten['eltern'] ) !== $kinder ) {
				continue;
			}

			$eltern_id = '' !== $daten['eltern'] ? ( $ids[ $daten['eltern'] ] ?? 0 ) : 0;
			$ergebnis  = speichere( $name, $daten, $eltern_id );

			$ids[ $name ] = $ergebnis['id'];
			++$zaehler[ $ergebnis['ergebnis'] ];
		}
	}

	if ( ( $ids['startseite'] ?? 0 ) > 0 ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['startseite'] );
	}

	flush_rewrite_rules();

	return $zaehler;
}

/**
 * Menüpunkt unter «Seiten».
 */
function add_admin_page(): void {
	add_submenu_page(
		'edit.php?post_type=page',
		__( 'realnorth: Seiten aufbauen', 'realnorth' ),
		__( 'realnorth aufbauen', 'realnorth' ),
		'manage_options',
		'realnorth-seiten',
		__NAMESPACE__ . '\render_admin_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\add_admin_page' );

/**
 * Die Importseite.
 */
function render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'realnorth' ) );
	}

	$meldung = '';

	if ( isset( $_POST['realnorth_seiten'] )
		&& check_admin_referer( 'realnorth_seiten', 'realnorth_seiten_nonce' ) ) {
		$z       = importiere();
		$meldung = sprintf(
			/* translators: 1: neu, 2: aktualisiert, 3: Fehler */
			__( '%1$d neu angelegt, %2$d aktualisiert, %3$d fehlgeschlagen.', 'realnorth' ),
			$z['neu'],
			$z['aktualisiert'],
			$z['fehler']
		);
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'realnorth: Seiten aufbauen', 'realnorth' ) . '</h1>';

	if ( '' !== $meldung ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $meldung ) . '</p></div>';
	}

	echo '<p>' . esc_html__(
		'Legt die Seiten des Entwurfs an, weist ihnen die Vorlage «realnorth (Vollbreite)» zu und setzt die Startseite. Der Lauf ist wiederholbar.',
		'realnorth'
	) . '</p>';

	echo '<div class="notice notice-warning inline"><p>' . esc_html__(
		'Achtung: Eigene Änderungen am Inhalt dieser Seiten werden dabei überschrieben. Betroffen sind nur die unten aufgeführten Seiten.',
		'realnorth'
	) . '</p></div>';

	echo '<ul style="list-style:disc;padding-left:20px">';

	foreach ( seiten() as $name => $daten ) {
		$pfad = '' !== $daten['eltern'] ? $daten['eltern'] . '/' . $name : $name;
		printf(
			'<li>%s <code>/%s/</code></li>',
			esc_html( $daten['titel'] ),
			esc_html( $pfad )
		);
	}

	echo '</ul>';

	echo '<form method="post">';
	wp_nonce_field( 'realnorth_seiten', 'realnorth_seiten_nonce' );
	submit_button( __( 'Seiten jetzt aufbauen', 'realnorth' ), 'primary', 'realnorth_seiten' );
	echo '</form></div>';
}
