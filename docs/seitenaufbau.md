# Wie die neue Seite aufgebaut ist

Umsetzung des Entwurfs «Realnorth — Bewirtschaftung & Entwicklung» in
WordPress. Dieses Dokument erklärt, wo was liegt und wie man die Seite
in Betrieb nimmt.

## Die Arbeitsteilung

| Was | Wo | Warum dort |
|---|---|---|
| Kopf- und Fusszeile | `includes/kopf-fuss.php` | Ohne Elementor Pro gibt es keinen Theme Builder. Im Plugin sind sie in Git und überleben Theme-Updates. |
| Seitengerüst | `templates/seite-realnorth.php` | Vollständiges Dokument; das Theme ist auf diesen Seiten nicht beteiligt. |
| Gestaltung | `assets/css/site.css` | Ein Stylesheet, klassenbasiert, alles unter `.rn-site`. |
| Bausteine | `includes/abschnitte.php` | Die wiederkehrenden Teile des Entwurfs als Shortcodes. |
| Listen (Wohnungen, Team) | `includes/wohnungen-ansicht.php` | Ohne Pro gibt es kein Loop Grid. |
| Texte | **Datenbank**, im Seiteninhalt | Dort gehören sie hin: im Editor änderbar, ohne Deploy. |
| Bilder | **Mediathek** | Nicht ins Repo — es wird in den Plugin-Ordner der Live-Seite deployt und ist öffentlich. |

Der Seiteninhalt besteht aus Shortcodes mit Text dazwischen. Das Layout
steckt nicht mit in der Datenbank: Wer eine Überschrift ändern will,
macht das im Editor; wer den Abstand über einem Band ändern will, ändert
eine Zeile CSS in Git. Beides bleibt dort, wo man es nachvollziehen kann.

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
   Inhalt dieser Seiten.
5. **Wohnungen füllen**: wp-admin -> Wohnungen -> «Beispieldaten», oder
   von Hand erfassen. Ohne Daten zeigen die Listen den Leerzustand.
6. **Team füllen**: wp-admin -> Team. Beitragsbild je Person setzen.
7. **Formulare**: in WPForms anlegen, dann im Seiteninhalt
   `[rn_formular id="…"]` eintragen. Ohne ID steht ein Hinweis da statt
   eines toten Formulars.
8. **Menü**: kommt aus `includes/kopf-fuss.php`, nicht aus wp-admin.
   Anpassbar über den Filter `realnorth_navigation`.

## Zurück, wenn etwas schiefgeht

Drei Stufen, von klein nach gross:

* **Eine Seite**: im Editor die Vorlage von «realnorth (Vollbreite)»
  zurück auf «Standard» stellen. Die Seite sieht wieder aus wie vorher.
* **Die Gestaltung**: Plugin deaktivieren. Dann fehlen auch CPTs und
  Shortcodes — der Seiteninhalt zeigt dann die Shortcode-Namen als Text.
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
| `[rn_formular]` | `id` (WPForms), `titel` |

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

## Was noch fehlt

* **Standortkarte und Kontaktkarte** — beide Plätze sind vorbereitet,
  die Bilder gibt es noch nicht.
* **Bilderkarussells Rennweg** (Companys, Calida, Breitling, 26 Fotos).
  Die Fotos liegen im ZIP, der Baustein dafür ist noch nicht gebaut.
* **Formulare** — WPForms-IDs eintragen.
* **Impressum und Datenschutz** — die Fusszeile verlinkt bereits auf
  `/impressum/` und `/datenschutz/`; die Seiten gibt es noch nicht.
* **Notfallnummern** auf dem Mieterservice stehen als Platzhalter
  (`+41 00 000 00 00`) im Entwurf und damit auch hier.
