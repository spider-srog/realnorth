# Inventar: woher das Material für die neue Website kommt

Stand 2026-10-03. Das Material für realnorth.ch liegt an fünf Orten. Dieses
Dokument sagt, was wo liegt, in welcher Qualität, und wofür es beim
Elementor-Aufbau gebraucht wird.

## Die fünf Quellen

### 1. Design-Entwurf «Realnorth Website Redesign»

Ein 28-MB-Bundle (React + Design-Components-Runtime, alle Assets
eingebettet), erstellt am 2026-09-24. **Die mit Abstand wichtigste Quelle** —
es enthält den kompletten Seitenaufbau mit fertigen Texten.

| | |
|---|---|
| Seiten | 9: Startseite, Freie Wohnungen, Liegenschaften (Tabs), Mietbestand, Rennweg, Entwicklung, Über uns, Service, Kontakt |
| Palette | `#0e2841` Marineblau, `#3c3a38` Graubraun, `#dad8d5`/`#c9c5c0` Hellgrau, `#52616a`, `#153b54`, `#f6f5f1` |
| Typografie | Barlow, als woff/woff2 eingebettet (4 Schnitte) |
| Schalter im Entwurf | `heroMark` (Bär/Foto/Ohne), `showPrices`, `tenantLogin` |
| Bilder | 7: Wortmarke weiss, Eisbär weiss (2 Varianten), 4 Birkenhain-Visualisierungen |
| Daten | 9 Wohnungen, 7 Teammitglieder mit Foto |

Daraus extrahiert und in dieses Repo übernommen:

* `content/*.md` — die Texte, eine Datei pro Seite, in der Reihenfolge des Entwurfs
* `content/wohnungen-beispieldaten.json` — das Datenmodell der Wohnungen
* `content/team.json` — Team mit Rolle, Bereich und Foto-Referenz

### 2. Repo `spider-srog/birkenhain`

Die fertige, live laufende Projektwebsite birkenhain.ch: Astro 5,
zweisprachig DE/EN, Auto-Deploy nach Plesk, Inhalte als JSON mit
Zod-Schemas, PHP-Endpoint mit Double-Opt-In für die Anmeldung.

**Nicht anfassen** — das Projekt läuft. Für realnorth.ch ist es Materiallager
und Vorbild:

| Was | Wo | Wofür bei realnorth.ch |
|---|---|---|
| 14 Visualisierungen, 1.5–6 MB | `src/assets/` | Seite «Entwicklung», Teaser «Im Birkenhain» |
| 3 PDFs (Planungsbericht, Situationsplan, Sondernutzungsvorschriften) | `public/dokumente/` | Downloads auf der Entwicklungsseite |
| Projektzahlen (300 Wohnungen, 17 Baubereiche, Koordinaten) | `src/data/projekt.json` | Faktenblock Birkenhain |
| Anmelde-Endpoint mit Double-Opt-In | `public/api/` | Vorbild für das Suchabo |
| Plesk-Deploy-Dokumentation | `docs/PLESK-*.md` | Vergleich mit unserem Deploy-Weg |

Achtung: Der Entwurf nennt **278** Mietwohnungen, `projekt.json` nennt
**300**. Eine der beiden Zahlen ist veraltet — vor der Publikation klären.

### 3. Dieses Repo (`spider-srog/realnorth`)

Das Plugin `realnorth-custom`, die Deploy-Kette und die Dokumentation.
Siehe `README.md`. Enthält ausserdem unter `design/` meine früheren
Richtungsentwürfe — **überholt**, der Entwurf aus Quelle 1 ist weiter und
markenkonform.

### 4. Die laufende Seite realnorth.ch

WordPress 7.1 mit PopularFX und Pagelayer. Inhalte liegen in der Datenbank.
Details: `ist-zustand.md`. Für den Neubau relevant sind daraus nur:
bestehende URLs (Redirects), die WPForms-Formulare mit 78 Einträgen und die
Medienbibliothek.

### 5. Markenvorgaben

Wortmarke «realnorth», Eisbär als angeschnittener Zusatz, Marineblau /
Graubraun / Hellgrau, Barlow. Der Entwurf aus Quelle 1 hält sich daran —
seine Hex-Werte sind die verbindliche Umsetzung.

## Zuordnung Seite → Material

| Seite | Texte | Bilder | Daten |
|---|---|---|---|
| Startseite | `content/startseite.md` | Eisbär, Birkenhain-Aerial | Wohnungszähler |
| Freie Wohnungen | `content/wohnungen.md` | — | `wohnungen-beispieldaten.json` |
| Liegenschaften | `content/liegenschaften.md` | — | Tab-Umschaltung, kein eigener Inhalt |
| Mietbestand | `content/mietbestand.md` | Bestandsfotos **fehlen** | — |
| Rennweg | `content/rennweg.md` | Objektfotos **fehlen** | — |
| Entwicklung | `content/entwicklung.md` | Birkenhain-Visualisierungen, PDFs | `projekt.json` aus Quelle 2 |
| Über uns | `content/ueber-uns.md` | 7 Teamfotos | `team.json` |
| Service | `content/service.md` | — | — |
| Kontakt | `content/kontakt.md` | — | Adresse, Telefon |

## Was bewusst nicht in diesem Repo liegt

**Die Bilddateien.** Dieses Repo wird in den Plugin-Ordner der Live-Seite
deployt; jedes Byte darin landet auf dem Server, bei jedem Deploy. Bilder
gehören in die WordPress-Medienbibliothek, nicht in ein Plugin. Im Repo
stehen deshalb nur die Referenzen; die Dateien selbst wurden separat
übergeben und werden einmal in die Medienbibliothek geladen.

## Was fehlt

1. **Fotos des Mietbestands** und des Objekts Rennweg 14/16 — im Entwurf
   sind dort Platzhalter.
2. **Bewirtschaftungssoftware** und ihr Exportformat — bestimmt, ob die
   Wohnungsliste importiert oder von Hand gepflegt wird.
3. **Entscheid 278 oder 300 Wohnungen** im Birkenhain.
4. **Juristisches**: Impressum und Datenschutz für realnorth.ch.
5. **Mieterlogin** — im Entwurf als Schalter angelegt, Umfang ungeklärt.
