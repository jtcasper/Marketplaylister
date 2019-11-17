<?php

require 'mpfuncs.php';

set_time_limit(0);

const SQLITE_DATE_FORM = 'Y-m-d H:i:s';

$pdo = new PDO("sqlite:mktplc.sqlite3");

$genDate = DateTime::createFromFormat(DATE_FORM, '1/1/2017');

$page = 1;

$query = $pdo->query('SELECT date FROM songs order by date desc limit 1');
$resultset = $query->fetch();

$lastEpDT = DateTime::createFromFormat(SQLITE_DATE_FORM, $resultset['date']);
$startDate = new DateTime;
$episodeDatePages = [];
$episodeTrackPages = [];

$episodes = [];
while ($startDate > $lastEpDT) {
    // DOM garbles UTF-8 chars, so loading them to HTML-ENTITIES data fixes this
    $html = mb_convert_encoding(file_get_contents('https://www.marketplace.org/latest-music/marketplace/page/' . $page), 'HTML-ENTITIES', "UTF-8");
    $DOM = new DOMDocument;
    $DOM->loadHTML($html);
    $xpath = new DOMXPath($DOM);
    $episodeData = $xpath->evaluate("//div[contains(@class, 'mp-music-card')]");
    foreach($episodeData as $episode) {
        $children = iterator_to_array($episode->childNodes);
        $episodeHeadCard = array_pop(findChildWithClass($children, 'mp-music-card-episode'));
        $episodeMeta = array_pop(findChildWithClass($episodeHeadCard->childNodes, 'mp-music-card-meta'));
        $episodeDate = array_pop(findChildWithClass($episodeMeta->childNodes, 'mp-music-card-meta_pubdate'))->textContent;
        if (!isset($episodeDate)) { continue; }
        $trackDiv = array_pop(findChildWithClass($children, 'mp-music-card-tracks'));
        $trackItems = findChildWithClass($trackDiv->childNodes, 'flex w-full flex-wrap item');
        $trackIDs = [];
        foreach($trackItems as $trackItem) {
            $divs = findChildWithClass($trackItem->childNodes, 'w-full min-tablet:w-1/2');
            foreach ($divs as $div) {
                $trackIDs[] = array_pop(explode('/', array_pop(findChildWithClass($div->childNodes, 'song-title'))->attributes->getNamedItem('href')->value));
            }
        }
        $episodes[$episodeDate] = $trackIDs;
    }
    $startDate = new DateTime(end(array_keys($episodes)));
    $page++;
}

$stmt = $pdo->prepare("INSERT INTO songs (date, uri) VALUES (:date, :uri)");
$stmt->bindParam(':date', $date);
$stmt->bindParam(':uri', $uri);
foreach(array_reverse($episodes) as $airDate => $trackIDs) {
    $date = (new DateTime($airDate))->format(SQLITE_DATE_FORM);
    foreach ($trackIDs as $trackID) {
        $uri = "spotify:track:{$trackID}";
        $stmt->execute();
    }
}
