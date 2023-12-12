<?php
$sLangName = 'Deutsch';

$aLang = array(
    'charset' => 'utf-8',

    'KUSSIN_CHATGPT_CONTENT_CREATOR_LEGEND' => 'KUSSIN | ChatGPT Content Creator',
    'KUSSIN_ARTICLE_MAIN_CHATGPT_LONG_DESCRIPTION' => 'Neue Langbeschreibung erstellen',
    'KUSSIN_ARTICLE_MAIN_CHATGPT_SHORT_DESCRIPTION' => 'Neue Kurzbeschreibung erstellen',

    'KUSSIN_CHATGPT_LONG_DESCRIPTION_PROMPT' => 'Erstelle eine Artikel-Langbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. %s Wörtern.',
    'KUSSIN_CHATGPT_SHORT_DESCRIPTION_PROMPT' => 'Erstelle eine Artikel-Kurzbeschreibung für "%s" von "%s". - Und bitte ohne Intro und mit max. %s Wörtern.',

    'KUSSIN_CHATGPT_LONG_DESCRIPTION_INSTRUCTION_PROMPT' => implode(PHP_EOL, array(
        'Aufbau wie folgt:',
        '1. Hauptvorteil in einem kurzen Satz möglichst prägnant und konkret in `<p>` Formatierung.',
        '2. Listenelemente mit Features und dem Vorteil, den das Feature bringt.',
        '3. Pro Feature ein Absatz bestehend aus einer `<h2>` Überschrift (Vorteil des Features + Metapher)',
        'und einem kurzen Text, der das Feature mit alltagsnahem Storytelling untermauert.',
        'Wichtig: Keine `<h1>` Überschrift.',
    )),
    'KUSSIN_CHATGPT_LONG_DESCRIPTION_CONTINUE_PROMPT' => 'Setzen bitte deine vorherige Antwort fort.',
);
