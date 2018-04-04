<?php

    const BASE_URL = 'https://api.spotify.com/v1/';
    // Currently updated manually whenever I get one from the server
    const SPOT_TOKEN = 'BQCQTtwO2kiMcV_VDgfSmTXQzGlO47rUuPyc4oCHpRunPQx2ZhhVYOtksVZPMbSgoCy3cGiRMHMygon5-SleqfsP0lvRMQW3gm1Q_a8TRv5MfCGQdNwdUcUu_NBpcSjWDNUadWeg3ps-WTDWxjUWm_FOlfxMy7a2AdI_RHWZ0Lx56WHf8gYA4-YVUm_HxpqDlReqEkWE9DHppQ';
  
    $pdo = new PDO("sqlite:mktplc.sqlite3");
    
    $stmt = $pdo->prepare("SELECT * FROM SONGS WHERE uri IS NULL");
    $upstmt = $pdo->prepare("UPDATE songs SET (uri) = :uri WHERE id = :id");
    $upstmt->bindParam(':uri', $uri);
    $upstmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
      
      print_r($stmt->fetchAll());
      exit(0);
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