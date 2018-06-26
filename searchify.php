<?php

    const BASE_URL = 'https://api.spotify.com/v1/';
    // Currently updated manually whenever I get one from the server
    const SPOT_TOKEN = 'BQBU1Qs3ROpkN9CwlQNpZS00khdSU61zuejyKbjS4KiIszK8aiLaTd9TfPiSH0OsmtWStOVL7ym-QYEBWyLX3qlFIN5peit0n6_B-LLtz4C8KSh3Dxj5O3jf4HSWf3fFISC4cLbznfSV3QnpQ4vdnCTehz4vT8V54XDiG2hX275Uw_gDHzKjqFWQo249-rY42rBv7pf555wQ2PSBymuZMcDlIDEeAbGiyRI';
  
    $pdo = new PDO("sqlite:mktplc.sqlite3");
    
    $stmt = $pdo->prepare("SELECT * FROM SONGS WHERE uri IS NULL");
    $upstmt = $pdo->prepare("UPDATE songs SET (uri) = :uri WHERE id = :id");
    $upstmt->bindParam(':uri', $uri);
    $upstmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
      
      #print_r($stmt->fetchAll());
      while ($row = $stmt->fetch()) {
        
        $track_opts = [
            'http' => [
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . SPOT_TOKEN . "\r\n"
            ]
        ];
        
        $track_context = stream_context_create($track_opts);
        
        $track_search_url = BASE_URL . 'search?q=track:' . urlencode($row['track']) 
                            . '+artist:' . urlencode($row['artist']) . '&type=track';

        $trackReq = file_get_contents($track_search_url, false, $track_context);
        if ($trackReq) {
          $trackJSON = json_decode($trackReq, true);
          $trackJSON = $trackJSON['tracks'];
          if ($trackJSON['total'] === 0) {
            continue;
          }

          $uri = $trackJSON['items'][0]['uri'];
          $id = $row['id'];
          $upstmt->execute();

          #rate limit
          sleep(1);

        }
      }
    }