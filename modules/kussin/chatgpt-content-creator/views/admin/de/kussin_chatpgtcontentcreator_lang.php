<?php
$sLangName = 'Deutsch';

$aLang = array(
    'charset' => 'utf-8',

    'KUSSIN_CHATGPT_CONTENT_CREATOR_LEGEND' => 'KUSSIN | ChatGPT Content Creator',
    'KUSSIN_CHATGPT_CONTENT_CREATOR_LONG_DESCRIPTION' => 'Langbeschreibung',
    'KUSSIN_ARTICLE_MAIN_CHATGPT_LONG_DESCRIPTION' => 'Neue Langbeschreibung erstellen',
    'KUSSIN_ARTICLE_MAIN_CHATGPT_OPTIMIZE_LONG_DESCRIPTION' => 'Optimierte Langbeschreibung erstellen',
    'KUSSIN_ARTICLE_MAIN_CHATGPT_SHORT_DESCRIPTION' => 'Neue Kurzbeschreibung erstellen',

    'KUSSIN_CHATGPT_LONG_DESCRIPTION_PROMPT' => 'Erstelle eine Artikel-Langbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. %s Wörtern.',
    'KUSSIN_CHATGPT_SHORT_DESCRIPTION_PROMPT' => 'Erstelle eine Artikel-Kurzbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. %s Wörtern.',
    'KUSSIN_CHATGPT_PRODUCT_SEARCHKEYS_PROMPT' => 'Erstelle eine kommaseparierte CSV-Liste von Synonymen für "%s" vom "%s" ohne Größen-, Volumen, Liter- oder Mengenangaben, ohne Marke/Hersteller oder individuelle Produktmerkmale wie Farbe und ohne Dopplungen.',
    'KUSSIN_CHATGPT_PRODUCT_ATTRIBUTES_PROMPT' => 'Versuche für die folgenden Attribute, für den Artikel "%s" (Hersteller-SKU: %s) von "%s", Werten zu ermitteln und erstelle ein daraus einen JSON; Werte, die "unbekannt" sind als `null` zurückgeben: ',
    'KUSSIN_CHATGPT_CATGEORY_LONG_DESCRIPTION_PROMPT' => 'Erstelle eine Kategorie-Langbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. %s Wörtern.',
    'KUSSIN_CHATGPT_CATGEORY_SHORT_DESCRIPTION_PROMPT' => 'Erstelle eine Kategorie-Kurzbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. 255 Zeichen.',
    'KUSSIN_CHATGPT_MANUFACTURER_LONG_DESCRIPTION_PROMPT' => 'Erstelle eine Marken-Langbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. %s Wörtern.',
    'KUSSIN_CHATGPT_MANUFACTURER_SHORT_DESCRIPTION_PROMPT' => 'Erstelle eine Marken-Kurzbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. 255 Zeichen.',
    'KUSSIN_CHATGPT_VENDOR_SHORT_DESCRIPTION_PROMPT' => 'Erstelle eine Hersteller-Kurzbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. 255 Zeichen.',

    'KUSSIN_CHATGPT_OPTIMIZE_CONTENT_PROMPT' => 'Optimiere den folgenden Inhalte für unserer Website: %s',

    'KUSSIN_CHATGPT_LONG_DESCRIPTION_INSTRUCTION_PROMPT' => implode(PHP_EOL, array(
        'Aufbau wie folgt:',
        '1. Hauptvorteil in einem kurzen Satz möglichst prägnant und konkret in `<p>` Formatierung.',
        '2. Listenelemente mit Features und dem Vorteil, den das Feature bringt.',
        '3. Pro Feature ein Absatz bestehend aus einer `<h2>` Überschrift (Vorteil des Features + Metapher)',
        'und einem kurzen Text, der das Feature mit alltagsnahem Storytelling untermauert.',
        'Wichtig: Keine `<h1>` Überschrift.',
        '4. Verwende keine HTML Entities wie `&uuml;` oder `&auml;` und auch keine einfachen oder doppelten Anführungszeichen.',
    )),
    'KUSSIN_CHATGPT_LONG_DESCRIPTION_CONTINUE_PROMPT' => 'Setzen bitte deine vorherige Antwort fort.',
);
