# Konzept: neue Website realnorth.ch mit WordPress und Elementor

Stand 2026-10-03. Ersetzt die erste Fassung, die ein Block-Theme vorsah.
Der Auftrag lautet jetzt: **mit WordPress und Elementor publizieren.**

Material und Herkunft: `inventar.md`. Ausgangslage der laufenden Seite:
`ist-zustand.md`. Texte: `../content/`.

## Was die Entscheidung für Elementor bedeutet

Elementor ist ein Page-Builder wie das heutige Pagelayer. Die Layouts
liegen damit weiterhin **in der Datenbank**, nicht im Repo. Das ist eine
bewusste Entscheidung zugunsten von Bedienbarkeit — wer Inhalte pflegt,
soll das ohne Entwickler können — und sie prägt die Rollenverteilung:

| Ort | Verantwortlich für | Versioniert? |
|---|---|---|
| **Elementor** | Layout, Abstände, Sektionen, Seitenaufbau | nein, Datenbank |
| **Plugin `realnorth-custom`** (dieses Repo) | Datenmodell, Importer, Suchlogik, eigene Widgets, Textkorrekturen | ja, Git |
| **Theme** | nur Grundgerüst | — |
| **Elementor-Kit** | Farben, Schriften, Grundtypografie | Export als JSON ins Repo |

Daraus folgt die wichtigste Arbeitsregel: **Alles, was Logik ist, gehört
ins Plugin.** Was im Builder liegt, ist nicht reviewbar, nicht testbar und
nicht zurückrollbar. Je weniger dort steckt, desto besser.

## Theme

**Hello Elementor** als Basis, dazu ein Child-Theme für die wenigen
Dinge, die Elementor nicht abdeckt (Schrift-Einbindung, einzelne
Template-Overrides). Hello ist bewusst minimal — kein eigenes Design, das
gegen Elementor arbeitet.

PopularFX fliegt raus. Damit gehen die Customizer-Einstellungen verloren;
das ist beim Neubau gewollt und kein Verlust, weil das Design ohnehin neu
ist.

## Plugins

### Pflicht

| Plugin | Zweck | Anmerkung |
|---|---|---|
| **Elementor** (free) | Builder | Basis |
| **realnorth-custom** (dieses Repo) | CPTs, Importer, Wohnungssuche, eigene Widgets | wir |
| **Hello Elementor** + Child | Theme | |

**Entscheid: ohne Pro.** Damit fallen Theme Builder, Loop Grid und
Dynamic Tags weg. Was Pro geliefert hätte, baut das Plugin:

| Fehlt ohne Pro | Ersatz im Plugin |
|---|---|
| Theme Builder: Kopf-/Fusszeile | Eigene Ausgabe über die Hooks von Hello Elementor |
| Archiv- und Detailseite Wohnungen | Eigene Templates über `template_include` |
| Loop Grid | Eigene Widgets, die Beiträge abfragen und rendern |
| Dynamic Tags | Felder werden in unseren Widgets gelesen, nicht im Builder gebunden |
| Form Builder | WPForms bleibt — liegt mit 78 Einträgen ohnehin schon da |

Die Folge für die Arbeitsteilung: Elementor ist für **Inhaltsseiten**
zuständig, das Plugin für alles Wiederkehrende und alles Dynamische.
Layoutänderungen an Kopfzeile, Fusszeile oder Wohnungsliste sind damit
Entwicklungsaufgaben, keine Klickarbeit. Das ist der bewusst in Kauf
genommene Preis; er lässt sich jederzeit rückgängig machen, indem Pro
nachgekauft und auf Theme-Builder-Templates umgestellt wird.

### Dazu

| Plugin | Zweck |
|---|---|
| **Rank Math SEO** | Meta, Sitemap, `RealEstateListing`-Schema für die Wohnungen |
| **Safe SVG** | Logo und Eisbär als SVG hochladen können |

### Bewusst nicht

* **Kein Immobilien-Plugin** (WP Residence, Estatik). Sie bringen ein
  eigenes Datenmodell und eigene Templates mit, von denen wir einen
  Bruchteil brauchen — und arbeiten dann gegen uns.
* **Kein Filter-Plugin.** Die Wohnungssuche wird ein eigenes
  Elementor-Widget mit REST-Endpoint. Der Entwurf zeigt genau, wie sie
  aussehen und sich verhalten soll; ein generisches Plugin kann das nicht.
* **Kein Cache-Plugin vor der ersten Messung.** Elementor ist schwer genug;
  erst messen, dann gezielt optimieren.

### Raus

PopularFX, PageLayer, Pagelayer Pro, PopularFX Website Templates,
UltraEmbed, Hello Dolly. WPForms bleibt vorerst wegen der 78 Einträge —
wenn Elementor Pro kommt, wandern neue Formulare dorthin und WPForms wird
später abgelöst.

## Datenmodell

Unverändert gegenüber der ersten Fassung, jetzt mit Elementor-Anbindung:

    wohnung (CPT)         Objekt, Ort, Zimmer, Fläche, Etage, Miete,
                          verfügbar ab, Status (Frei|Reserviert|Vormerkung),
                          Bilder, Dokumente, Relation zu liegenschaft
    liegenschaft (CPT)    Adresse, Baujahr, Einheiten, Hauswart
    bauprojekt (CPT)      Ort, Status, Etappen, Bezugstermin, Visualisierungen
    team (CPT)            Name, Rolle, Bereich, Foto, Reihenfolge

Die Felder werden als Meta registriert und für Elementors **Dynamic Tags**
freigegeben — dann können Templates direkt darauf zugreifen, ohne dass
jemand Werte abtippt. Das Statusfeld und die Preisanzeige folgen dem
Schalter `showPrices` aus dem Entwurf.

`content/wohnungen-beispieldaten.json` und `content/team.json` sind die
Startbefüllung: 9 Wohnungen in Berikon, Rudolfstetten, Zürich und
Winterthur, 7 Personen.

## Eigene Elementor-Widgets

Im Plugin, nicht im Builder:

1. **Wohnungssuche** — Filter nach Ort, Zimmern, Miete und Verfügbarkeit,
   Ergebnisliste ohne Seitenneuladen, Leerzustand mit Suchabo. Genau das,
   was der Entwurf auf der Seite «Freie Wohnungen» zeigt.
2. **Wohnungs-Teaser** — die drei aktuellsten Objekte für die Startseite.
3. **Team-Raster** — aus dem Team-CPT, sortiert nach Bereich.
4. **Projekt-Faktenblock** — Zahlen zu Birkenhain aus einer Datenquelle
   statt abgetippt.

## Importer

Wie gehabt quellen-agnostisch: ein Adapter je Exportformat der
Bewirtschaftungssoftware, dahinter das `wohnung`-CPT. Lauf per WP-Cron,
idempotent über die Objekt-ID der Quelle, verschwundene Objekte werden auf
`vermietet` gesetzt statt gelöscht.

**Comparis bleibt Empfänger, nicht Sender** — die Inserate gehen von der
Bewirtschaftungssoftware dorthin; eine API zum Zurücklesen gibt es nicht.
Die Website zieht deshalb aus derselben Quelle, die Comparis füttert.

## Elementor-Kit

Die Marke gehört einmal zentral hinterlegt, nicht in jede Sektion:

| Token | Wert |
|---|---|
| Primär | `#0e2841` Marineblau |
| Text | `#3c3a38` Graubraun |
| Akzent hell | `#dad8d5` |
| Linien | `#c9c5c0` |
| Fläche hell | `#f6f5f1` |
| Sekundär | `#52616a`, `#153b54` |
| Schrift | Barlow, selbst gehostet (nicht über Google-CDN) |

Das Kit wird nach der Einrichtung als JSON exportiert und hier abgelegt —
damit ist wenigstens die Designgrundlage versioniert.

## Reihenfolge

1. Auf einem Klon arbeiten (Plesk WordPress Toolkit), nicht live.
2. Hello Elementor + Child, Kit mit Farben und Barlow einrichten.
3. Plugin erweitern: CPTs, Felder, Dynamic Tags.
4. Medienbibliothek füllen (Bilder und Teamfotos aus der Übergabe).
5. Plugin: Kopfzeile, Fusszeile, Archiv und Detailseite Wohnungen.
6. Seiten bauen, Texte aus `content/*.md`, Seite für Seite.
7. Wohnungssuche als Widget, zuerst gegen die Beispieldaten.
8. Importer, sobald die Datenquelle feststeht.
9. Redirects der alten URLs, Impressum und Datenschutz, dann umschalten.
10. Erst danach Pagelayer und PopularFX löschen.

## Was dabei riskant ist

* **Elementor ist schwer.** Mehr DOM, mehr CSS, mehr JavaScript als ein
  Block-Theme. Gegenmittel: wenige Sektionen, keine verschachtelten
  Container-Orgien, Bilder in moderner Grösse, am Ende messen.
* **Layouts sind nicht versioniert.** Ein Fehlgriff im Builder lässt sich
  nur über ein Datenbank-Backup zurückholen. Vor grösseren Umbauten ein
  Backup im Plesk Backup Manager anlegen.
* **Mehr Code statt Klickarbeit.** Ohne Pro liegt mehr in unserer Hand:
  Kopfzeile, Fusszeile, Archiv und jede dynamische Liste. Das ist
  versioniert und testbar — aber jede Layoutänderung daran braucht einen
  Entwickler. Wenn das im Alltag stört, ist Pro der Ausweg, nicht ein
  Umbau.
