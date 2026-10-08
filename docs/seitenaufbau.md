# Wie die neue Seite aufgebaut ist

Umsetzung des Entwurfs «Realnorth — Bewirtschaftung & Entwicklung» in
WordPress. Dieses Dokument erklärt, wo was liegt und wie man die Seite
in Betrieb nimmt.

## Die Arbeitsteilung

| Was | Wo | Warum dort |
|---|---|---|
| Kopf- und Fusszeile | **Elementor Theme Builder** | Dort ohne Deploy änderbar. Fallback im Plugin: `includes/kopf-fuss.php`. |
| Formulare | **Elementor Forms** | Versand, Spamschutz und Datenhaltung sind dort gelöst. |
| Anordnung der Seiten | **Elementor** | Dafür ist ein Builder da. |
| Seitengerüst | `templates/seite-realnorth.php` | Vollständiges Dokument; ruft die Theme-Builder-Bereiche direkt auf, damit sie unabhängig vom aktiven Theme greifen. |
| Gestaltung | `assets/css/site.css` | Ein Stylesheet, klassenbasiert. Gleiche Abstände und Farben, egal ob ein Abschnitt im Builder oder im Seiteninhalt entsteht. |
| Bausteine | `includes/abschnitte.php` | Je Baustein eine Funktion — benutzt vom Shortcode **und** vom Elementor-Widget. |
| Elementor-Widgets | `includes/elementor-bausteine.php` | Dünne Hüllen um dieselben Funktionen. |
| Listen (Wohnungen, Team) | `includes/wohnungen-ansicht.php` | Fachlogik, siehe unten. |
| Texte | **Datenbank** | Im Editor oder im Builder änderbar, ohne Deploy. |
| Bilder | **Mediathek** | Nicht ins Repo — es wird in den Plugin-Ordner der Live-Seite deployt und ist öffentlich. |

Die Trennlinie ist nicht Geschmack, sondern Prüfbarkeit. Was im Builder
steckt, liegt in der Datenbank: nicht reviewbar, nicht per Rollback
zurückholbar, nicht testbar. Also gehört Fachlogik ins Plugin und
Anordnung in den Builder.

Darum bleibt die Wohnungsliste bewusst **kein Loop Grid**: der
Zimmerfilter «4.5+» heisst «4.5 Zimmer und mehr», sortiert wird «sofort
zuerst, dann nach Bezugstermin, bei gleichem Termin nach Ort». Das sind
Regeln mit Testfällen (`tests/wohnungen-logik.php`), keine
Anzeigeeinstellungen. Im Builder liessen sie sich weder abbilden noch
prüfen.

Jeder Baustein hat **eine** Umsetzung. Das Widget `realnorth-zahl` und
der Shortcode `[rn_zahl]` rufen dieselbe Funktion auf, mit denselben
Feldnamen. Es gibt nichts, was auseinanderlaufen könnte, und die Tests
decken beide Wege ab.

## Inbetriebnahme

1. **Deploy**: Push auf `claude/**` -> GitHub Actions -> `main` -> Plesk
   zieht (siehe `docs/automatischer-deploy.md`). Plugin muss aktiv sein.
2. **Bilder hochladen**: wp-admin -> Medien. Das ZIP mit den Bildern aus
   dem Entwurf liegt ausserhalb des Repos; die beiliegende `LIESMICH.txt`
   nennt für jeden Platz die passende Datei.
3. **Bilder zuweisen**: wp-admin -> Design -> Bilder. Pro Platz die
   Anhang-ID eintragen. Ohne Zuweisung erscheint ein beschrifteter
   Platzhalter — ein fehlendes Bild soll auffallen.
4. **Seiten aufbauen**: wp-admin -> Seiten -> «realnorth aufbauen».
   Legt die acht Seiten an, weist ihnen die Vorlage zu und setzt die
   Startseite. Wiederholbar — aber er überschreibt eigene Änderungen am
   Inhalt dieser Seiten. Danach steht die Seite bereits; alles Weitere
   ist Verfeinerung.
5. **Wohnungen füllen**: wp-admin -> Wohnungen -> «Beispieldaten», oder
   von Hand erfassen. Ohne Daten zeigen die Listen den Leerzustand.
6. **Team füllen**: wp-admin -> Team. Beitragsbild je Person setzen.
7. **Markenfarben in Elementor hinterlegen**: Hamburger-Menü ->
   Website-Einstellungen -> Globale Farben. Dann greift jedes
   Elementor-Widget auf dieselbe Palette zu:

   | Rolle | Hex |
   |---|---|
   | Marineblau (Kopf, dunkle Bänder) | `#102f45` |
   | Akzent (Knöpfe, Verweise) | `#153b54` |
   | Akzent hell (Hover) | `#22526d` |
   | Text | `#203440` |
   | Text leise | `#52616a` |
   | Grund | `#f6f5f1` |
   | Grund ruhig | `#eaece5` |
   | Linie | `#d9ddd8` |
   | Gold (Fokus, Hover auf Dunkel) | `#b39b66` / `#e3d8bf` |

   Globale Schriften: Barlow, Überschriften 500. Die Schrift liegt
   selbst gehostet im Plugin — in Elementor **nicht** Google Fonts
   auswählen, sonst lädt sie ein zweites Mal von Google.
8. **Kopf und Fuss im Theme Builder**: Vorlagen -> Theme Builder ->
   Kopfzeile bzw. Fusszeile anlegen und auf «Gesamte Website» setzen.
   Ab diesem Moment zeigt die Seite die Elementor-Fassung; die aus dem
   Plugin tritt still zurück. Vorlage wieder entfernen = Plugin-Fassung
   ist zurück.
9. **Formulare**: in Elementor bauen. Auf einer mit Elementor
   bearbeiteten Seite direkt das Formular-Widget ziehen. Im klassischen
   Seiteninhalt stattdessen das Formular als Vorlage speichern
   (Vorlagen -> Gespeicherte Vorlagen) und deren ID eintragen:
   `[rn_formular vorlage="123"]`.

## Zurück, wenn etwas schiefgeht

Drei Stufen, von klein nach gross:

* **Kopf oder Fuss**: Theme-Builder-Vorlage löschen oder ihre Bedingung
  entfernen. Die Fassung aus dem Plugin übernimmt sofort wieder.
* **Eine Seite**: im Editor die Vorlage von «realnorth (Vollbreite)»
  zurück auf «Standard» stellen. Die Seite sieht wieder aus wie vorher.
* **Die Gestaltung**: Plugin deaktivieren. Dann fehlen auch CPTs,
  Shortcodes und Widgets — Elementor zeigt für die realnorth-Widgets
  einen Hinweis, der klassische Seiteninhalt die Shortcode-Namen als
  Text.
* **Der Stand**: Rollback über Plesk, siehe `docs/plesk-deploy.md`.

## Die Bausteine

Alle Shortcodes beginnen mit `rn_`. Umschliessende Shortcodes dürfen
verschachtelt werden.

### Gerüst

| Shortcode | Attribute | Zweck |
|---|---|---|
| `[rn_band]…[/rn_band]` | `grund` (`flaeche`, `weiss`, `ruhig`, `dunkel`), `klasse`, `luft="schmal"`, `id` | Ein Abschnitt über die volle Breite, innen auf 1440 px begrenzt. |
| `[rn_raster]…[/rn_raster]` | `spalten` (`2`–`5`, `split`) | Spalten. `split` ist die asymmetrische Zweiteilung des Entwurfs. |
| `[rn_spalte]…[/rn_spalte]` | — | Eine freie Spalte im Raster. |

### Kopfteile

| Shortcode | Attribute |
|---|---|
| `[rn_kopf]` | `kicker`, `titel`, `lead`, `stufe` (`h1`/`h2`) |
| `[rn_abschnittskopf]` | `titel`, `linktext`, `url` |
| `[rn_buehne]` | `kicker`, `titel`, `lead`, `bild`, `bildtitel`, `bildzeile`, `knopf1`/`url1`, `knopf2`/`url2` |

Im `titel` sind `<br>` und `<span class="rn-buehne__akzent">` erlaubt —
damit steht die dritte Zeile wie im Entwurf in der helleren Farbe. Weil
der Wert dann doppelte Anführungszeichen enthält, muss das Attribut in
einfache gesetzt werden: `titel='… <span class="…">…</span>'`.

### Inhalte

| Shortcode | Attribute |
|---|---|
| `[rn_schritt]Text[/rn_schritt]` | `nummer`, `titel` |
| `[rn_zahl]` | `wert`, `text` |
| `[rn_karte]Text[/rn_karte]` | `kicker`, `titel`, `knopf`, `url`, `stil` |
| `[rn_merkmale]` + `[rn_merkmal]` | `begriff`, `wert` |
| `[rn_ablauf]` + `[rn_etappe]Text[/rn_etappe]` | `zeit`, `jetzt="ja"` |
| `[rn_aufruf]Text[/rn_aufruf]` | `titel`, `knopf`, `url` |
| `[rn_knopf]Text[/rn_knopf]` | `url`, `stil` (`laut`/`leise`) |
| `[rn_bild]` | `platz`, `bildzeile`, `groesse` |
| `[rn_stelle]` | `titel`, `detail`, `url` |
| `[rn_formular]` | `vorlage` (Elementor-Vorlage), `id` (WPForms), `titel` |

`[rn_zahl]` zählt beim Scrollen hoch, wenn der Wert mit einer ganzen
Zahl beginnt. «2.5–5.5» bleibt stehen — eine animierte Spanne ergibt
keinen Sinn.

### Daten

| Shortcode | Attribute |
|---|---|
| `[rn_wohnungen]` | `darstellung` (`karten`/`tabelle`), `anzahl`, `filter` (`ja`/`nein`) |
| `[rn_wohnungszahl]` | — |
| `[rn_team]` | `spalten` |

Der Filter arbeitet über die Adresszeile (`?ort=…&zimmer=…`) und kommt
ohne JavaScript aus. Jede Auswahl hat damit eine eigene Adresse, die
sich verlinken und als Lesezeichen speichern lässt.

## Was geprüft wird

`tests/seitenaufbau.php` prüft nicht nur die Funktionen, sondern den
Seiteninhalt selbst: dass jeder benutzte Shortcode registriert ist und
dass die Klammern aufgehen. Ein Tippfehler im Inhalt sieht live nicht
nach Fehler aus, sondern nach einer Zeile Text in eckigen Klammern —
genau das fällt so vor dem Deploy auf.

Zusätzlich wurden alle acht Seiten vor dem Commit einmal durch den
echten Shortcode-Parser von WordPress gerendert und im Browser
angesehen. Das lief ausserhalb des Repos, weil es WordPress-Code
herunterlädt; es ist kein Teil von `bin/test.sh`.

## Im Builder

Alle Bausteine stehen in Elementor unter der Gruppe **realnorth**:

Bühne · Seitenkopf · Abschnittskopf · Schritt · Kennzahl · Karte ·
Merkmalsliste · Zeitablauf · Aufruf · Bildplatz · Offene Stelle ·
Wohnungen · Team

Für die Bänder braucht es kein Widget — das macht der Container von
Elementor. Damit er aussieht wie im Entwurf, trägt man im Feld
**CSS-Klassen** eine dieser Klassen ein:

| Klasse | Wirkung |
|---|---|
| `rn-band--ruhig` | Grund `#eaece5` |
| `rn-band--dunkel` | Grund `#102f45`, helle Schrift, Knöpfe drehen auf Weiss |
| `rn-band--weiss` | Grund Weiss |
| `rn-luft` | 88 px Abstand oben und unten |
| `rn-luft-klein` | 52 px Abstand oben und unten |
| `rn-karussell` | Macht aus den Bildern im Container eine Galerie: Klick auf ein Bild öffnet es gross, mit Vor und Zurück über alle Bilder desselben Containers |

Der Builder hat kein Karussell-Widget, deshalb steckt die Mechanik im
Plugin (`assets/js/site.js`). Im Builder genügt die Klasse am Container;
zwei optionale Attribute beschriften den Kopf des Karussells:

| Attribut | Zweck |
|---|---|
| `data-rn-titel` | Titel über dem Bild, z. B. «Companys» |
| `data-rn-kicker` | Zeile darüber, z. B. «Rennweg 14/16 · Nutzungsgeschichte» |

Die Bildlegende kommt aus dem **Alternativtext** des Bildes — eine
Quelle für beides. Ohne JavaScript bleibt das Raster ein Raster; es ist
nichts versteckt.

Für die Spalten eines Rasters nimmt man ebenfalls Elementor-Container.
`rn-raster--drei` und Geschwister gibt es weiterhin, sie sind aber nur
für den klassischen Seiteninhalt gedacht.

## Was noch fehlt

* **Standortkarte und Kontaktkarte** — beide Plätze sind vorbereitet,
  die Bilder gibt es noch nicht.
* **Bilderkarussells Rennweg** (Companys, Calida, Breitling, 26 Fotos).
  Die Fotos liegen im ZIP, der Baustein dafür ist noch nicht gebaut.
* **Formulare** — in Elementor bauen und einsetzen. WPForms kann
  danach weg; der Shortcode unterstützt es übergangsweise weiter.
* **Impressum und Datenschutz** — die Fusszeile verlinkt bereits auf
  `/impressum/` und `/datenschutz/`; die Seiten gibt es noch nicht.
* **Notfallnummern** auf dem Mieterservice stehen als Platzhalter
  (`+41 00 000 00 00`) im Entwurf und damit auch hier.
