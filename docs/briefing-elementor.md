# Briefing: die Seite im Elementor fertig bauen

Übergabe an die nächste Session. Alles, was hier steht, ist bereits
gebaut und live deploybar — was fehlt, steht unter «Auftrag».

Lies ergänzend `docs/seitenaufbau.md` (Bausteine und Shortcodes im
Detail) und `CLAUDE.md` (Arbeitsregeln des Repos).

## Lage in fünf Zeilen

Die Seite realnorth.ch wird nach dem Entwurf «Realnorth —
Bewirtschaftung & Entwicklung» neu gebaut. Das Gerüst steht: Gestaltung,
Datenmodell, acht Seiten und dreizehn Elementor-Widgets liegen im Plugin
`realnorth-custom` (dieses Repo) und sind auf `main` deployt. Was fehlt,
ist die Arbeit **im Builder**: globale Farben, Kopf- und Fusszeile im
Theme Builder, Formulare. Dafür gibt es den MCP-Server
**`real-north-ag-elementor`**.

## Woran du arbeitest

| | |
|---|---|
| Repo | `spider-srog/realnorth` (früher `spifroca/realnorth`, leitet weiter) |
| Branch | `claude/new-session-bpjsav` |
| Stand | `e6ef612`, Plugin-Version 0.7.0, `main` ist vorgespult |
| Server | Plesk `rlx1.loginserver.ch`, WordPress 7.1, PHP 8.5.9, nginx |
| Plugin-Pfad | `/httpdocs/realnorth/wordpress/wp-content/plugins/realnorth-custom/` |
| Theme | PopularFX (fremd, **nicht anfassen**) |
| Builder | Elementor **Pro** |
| MCP-Server | `real-north-ag-elementor` |

Deploy: Push auf `claude/**` → GitHub Actions (Lint + Tests) → bei Grün
und `AUTO_PROMOTE=true` nach `main` → Plesk zieht. Jeder Push kann live
gehen.

Aus einer Standard-Cloud-Session ist realnorth.ch **nicht** per HTTP
erreichbar (Egress-Proxy). Der MCP-Server geht trotzdem, weil die
Verbindung nicht aus dem Container kommt.

## Auftrag, in dieser Reihenfolge

### 1. Globale Einstellungen

Website-Einstellungen → Globale Farben:

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
| Gold (Fokus) | `#b39b66` |
| Gold hell (Hover auf Dunkel) | `#e3d8bf` |

Globale Schriften: **Barlow**, Überschriften Stärke 500, Fliesstext 400.

> **Barlow nicht als Google Font auswählen.** Die Schrift liegt in vier
> Schnitten selbst gehostet im Plugin (`assets/fonts/`). Wird sie in
> Elementor zusätzlich von Google geholt, lädt sie zweimal — und eine
> Schriftanfrage pro Besucher an einen Dritten ist in der Schweiz
> datenschutzrechtlich heikel. In Elementor unter Website-Einstellungen
> → Benutzerdefinierte Schriften eintragen oder als Systemschrift
> «Barlow» setzen.

### 2. Kopfzeile im Theme Builder

Vorlagen → Theme Builder → Kopfzeile, Bedingung «Gesamte Website».

Aus dem Entwurf: Grund `#102f45`, Höhe 88 px, Inhalt auf 1440 px
begrenzt, seitlich 56 px Luft. Links die Wortmarke (24 px hoch), rechts
das Menü (14 px, Abstand 28 px, Hover `#e3d8bf` mit Unterstrich), ganz
rechts ein **weisser** Knopf «Kontakt» (Text `#102f45`, 11/23 px
Innenabstand).

Menü:

* Wohnungen → `/wohnungen/`
* Liegenschaften (Untermenü) → Mietwohnungen `/liegenschaften/`,
  Rennweg 14/16 `/liegenschaften/rennweg-14-16/`
* Entwicklung → `/entwicklung/`
* Über uns → `/ueber-uns/`
* Mieterservice → `/mieterservice/`

Unter 800 px: Menü umbricht in eine zweite Zeile und scrollt waagrecht,
der Kontakt-Knopf bleibt oben rechts.

### 3. Fusszeile im Theme Builder

Grund `#102f45`, vier Spalten (`1.4fr` + 3×`1fr`), oben 72 px, unten
32 px Luft. Der Eisbär liegt als Wasserzeichen rechts, Deckkraft 0.14.

| Spalte | Inhalt |
|---|---|
| 1 | Wortmarke, dann: Real North AG · Unternehmer-Park 3 · 6340 Baar · +41 41 552 53 63 · verwaltung@realnorth.ch |
| 2 «Mieten» | Freie Wohnungen, Mieterservice, Schaden melden |
| 3 «Unternehmen» | Liegenschaften, Entwicklung, Über uns, Kontakt |
| 4 «Projekte» | birkenhain.ch ↗ |

Unten eine Zeile mit `© <Jahr> Real North AG` links, Impressum und
Datenschutz rechts.

> Vorsicht bei Linkfarben auf dunklem Grund: genau dort lagen zwei
> Fehler, die erst im Browser sichtbar wurden. Telefon und Mail im
> Adressblock müssen weiss sein, nicht in der Akzentfarbe.

### 4. Formulare

Drei Stück, mit Elementor Forms:

| Wo | Felder |
|---|---|
| Kontakt | Anliegen (Auswahl), Name, E-Mail, Telefon (optional), Nachricht |
| Mieterservice | Name, Liegenschaft/Wohnung, E-Mail oder Telefon, Anliegen |
| Wohnungen (Suchabo) | Name, E-Mail, gewünschter Standort, Zimmer ab |

Auf einer mit Elementor bearbeiteten Seite das Formular-Widget direkt
ziehen. Bleibt die Seite klassisch, das Formular als Vorlage speichern
und die ID eintragen: `[rn_formular vorlage="123"]`.

Empfänger: `verwaltung@realnorth.ch`.

## Was schon da ist — nicht neu bauen

**Dreizehn Widgets** in der Builder-Gruppe «realnorth»: Bühne,
Seitenkopf, Abschnittskopf, Schritt, Kennzahl, Karte, Merkmalsliste,
Zeitablauf, Aufruf, Bildplatz, Offene Stelle, Wohnungen, Team.

**Bänder** brauchen kein Widget. Elementor-Container nehmen und im Feld
CSS-Klassen eintragen:

| Klasse | Wirkung |
|---|---|
| `rn-band--ruhig` | Grund `#eaece5` |
| `rn-band--dunkel` | Grund `#102f45`, helle Schrift, Knöpfe drehen auf Weiss |
| `rn-band--weiss` | Grund Weiss |
| `rn-luft` | 88 px oben und unten |
| `rn-luft-klein` | 52 px oben und unten |

**Acht Seiten** liegen fertig als Seiteninhalt vor und lassen sich über
wp-admin → Seiten → «realnorth aufbauen» anlegen: Start, Freie
Wohnungen, Mietwohnungen, Rennweg 14/16, Entwicklung, Über uns,
Mieterservice, Kontakt.

**Bildplätze**: unter Design → Bilder bekommt jeder Platz eine
Anhang-ID. Ohne Zuweisung erscheint ein beschrifteter Platzhalter. Die
Bilder aus dem Entwurf (52 Dateien) hat der Auftraggeber als ZIP; sie
gehören in die Mediathek, **nicht** ins Repo.

**Seitenvorlage** «realnorth (Vollbreite)»: rendert das ganze Dokument
und ruft `elementor_theme_do_location()` direkt auf. Deshalb greift der
Theme Builder, obwohl PopularFX das nicht anmeldet. Solange im Theme
Builder nichts hinterlegt ist, zeigt das Plugin seine eigene Kopf- und
Fusszeile — sobald du eine Vorlage zuweist, tritt sie still zurück.

## Grenzen

* **Theme-Dateien nicht anfassen.** PopularFX ist fremd, ein Update
  überschreibt alles.
* **Kein Loop Grid für die Wohnungen.** «4.5+» heisst «4.5 Zimmer und
  mehr», sortiert wird «sofort zuerst, dann nach Bezugstermin». Das sind
  Regeln mit Testfällen in `tests/wohnungen-logik.php`, keine
  Anzeigeeinstellungen. Das Widget `realnorth-wohnungen` bleibt.
* **Fachlogik gehört ins Plugin, Anordnung in den Builder.** Was im
  Builder steckt, liegt in der Datenbank: nicht reviewbar, nicht per
  Rollback zurückholbar, nicht testbar.
* **Kein Build auf dem Server.** Kein Shell-Zugriff. Fertiges CSS/JS
  wird eingecheckt.
* Vor jedem Push `./bin/php-lint.sh` und `./bin/test.sh`. Beide müssen
  grün sein (aktuell 143 Prüfungen).

## Prüfen, dass es stimmt

1. Startseite aufrufen: Kopf und Fuss kommen aus dem Theme Builder, die
   Plugin-Fassung erscheint **nicht** zusätzlich.
2. Eine Seite mit dunklem Band ansehen (Entwicklung, Mieterservice):
   Knöpfe weiss auf dunkel, Text lesbar.
3. `/wohnungen/`: Filterleiste arbeitet über die Adresszeile
   (`?ort=…&zimmer=…`), jede Auswahl hat eine eigene URL.
4. Auf 800 px und 520 px Breite nachsehen — die Haltepunkte aus dem
   Entwurf sind im Stylesheet gesetzt.
5. Kennzahlen zählen beim Scrollen hoch, «2.5–5.5» bleibt stehen.

## Rollback

| Stufe | Vorgehen |
|---|---|
| Kopf oder Fuss | Theme-Builder-Vorlage löschen → Plugin-Fassung ist sofort zurück |
| Eine Seite | Im Editor Vorlage auf «Standard» stellen |
| Die ganze Gestaltung | wp-admin → Plugins → realnorth Custom deaktivieren |
| Der Codestand | Plesk-Rollback, siehe `docs/plesk-deploy.md` |

## Offene Punkte für den Auftraggeber

* Standortkarte und Kontaktkarte — beide Bildplätze sind vorbereitet,
  die Bilder gibt es noch nicht.
* Bilderkarussells am Rennweg (Companys, Calida, Breitling, 26 Fotos im
  ZIP) — Baustein noch nicht gebaut.
* Impressum und Datenschutz — die Fusszeile verlinkt bereits darauf.
* Notfallnummern auf dem Mieterservice stehen als Platzhalter
  (`+41 00 000 00 00`) im Entwurf und damit auch auf der Seite.
* Bewirtschaftungssoftware und Exportformat für den Wohnungs-Importer.
* Repo ist öffentlich; für ein Kundenprojekt eher privat + Deploy-Key.
