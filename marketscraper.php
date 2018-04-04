<?php

    require 'mpfuncs.php';
    
    const SQLITE_DATE_FORM = 'Y-m-d H:i:s';
    
    $pdo = new PDO("sqlite:mktplc.sqlite3");
        
    $genDate = DateTime::createFromFormat(DATE_FORM, '1/1/2017');
    
    $page = 1;
    
    $query = $pdo->query('SELECT date FROM songs order by date desc limit 1');
    $resultset = $query->fetch();

    $lastEpDT = new DateTime::createFromFormat(SQLITE_DATE_FORM, $resultset['date'];
    $startDate = new DateTime;
    $episodeDatePages = [];
    $episodeTrackPages = [];
    
    while ($startDate > $lastEpDT) {
      // DOM garbles UTF-8 chars, so loading them to HTML-ENTITIES data fixes this
      $html = mb_convert_encoding(file_get_contents('https://www.marketplace.org/latest-music?page=' . $page), 'HTML-ENTITIES', "UTF-8");
      $DOM = new DOMDocument;
      $DOM->loadHTML($html);
      $headers = $DOM->getElementsByTagName('h2');
      $divs = $DOM->getElementsByTagName('div');
      
      $episodeDatePages[] = parseEpisodeDate($headers, $lastEpDT);
      $episodeTrackPages[] = parseEpisodePage($divs);
      $startDate = end($episodeDatePages[$page - 1]);
      $page++;
    }
    
    //print_r($episodeDatePages);

    
    //Unroll episodeDatePages
    $episodeDates = [];
    foreach ($episodeDatePages as $episodeDatePage) {
      foreach ($episodeDatePage as $episodeDate) {
        $episodeDates[] = $episodeDate;
      }
    }
    
    
    // Unroll episodeTrackPages
    $episodeTrackLists = [];
    foreach ($episodeTrackPages as $epTrackPage) {
      foreach ($epTrackPage as $epTrackList) {
        $episodeTrackLists[] = $epTrackList;
      }
    }
    
    $episodes = array_slice(
                  array_map(
                    null, $episodeDates, $episodeTrackLists), 0, min(
                      count($episodeDates), count($episodeTrackLists)
                    )
                );
    print_r($episodes);
    
    $stmt = $pdo->prepare("INSERT INTO songs (track, artist, date) VALUES (:track, :artist, :date)");
    $stmt->bindParam(':track', $trackName);
    $stmt->bindParam(':artist', $artist);
    $stmt->bindParam(':date', $date);
    foreach(array_reverse($episodes) as $episode) {
      $date = $episode[0]->format(SQLITE_DATE_FORM);
      foreach ($episode[1] as $track) {
        $trackName = $track['title'];
        $artist = $track['artist'];
        $stmt->execute();
      }
    }
    
    