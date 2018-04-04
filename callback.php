<?php
    declare(strict_types = 1);
    
    require 'secrets.php';
    require 'mpfuncs.php';
    
    const BASE_URL = 'https://api.spotify.com/v1/';
    const AUTH_URL = 'https://accounts.spotify.com/';
    const DATE_FILE = 'prev_date.txt';
    const DATE_FORM = 'm/d/Y';
    const MONTHS = [
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
    ];
        
    $code = $_GET['code'];
    
    // Get month and year user is requesting
    $state = explode(':', $_GET['state']);
    
    
    if (!$code) {
        exit(1);
    }
        
    #print_r($today);    
    
    #Handle Spotify Token Authorization
    
    $token_data = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => REDIRECT_URI
    ];
    $token_data = http_build_query($token_data);
    
    $token_opts = [
        'http' => [
            'method' => 'POST',
            /*'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                     . "Content-Length: " . strlen($token_data) . "\r\n"
                     . "Authorization: Basic " . base64_encode('868e2cba00de4819900dd8a647a7ba7d:' . CLIENT_SECRET) . "\r\n",*/
            'header' => "Authorization: Basic " . base64_encode('868e2cba00de4819900dd8a647a7ba7d:' . CLIENT_SECRET) . " \r\n",
            'content' => $token_data
        ]
    ];
        
    $token_context = stream_context_create($token_opts);
    
    $spot_req = file_get_contents(AUTH_URL . 'api/token', false, $token_context);
    
    echo $spot_req;
    $spot_json = json_decode($spot_req, true);

    $spot_token = $spot_json['access_token'];
    
    
    $me_opts = [
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $spot_token . "\r\n"
        ]
    ];
    
    $me_context = stream_context_create($me_opts);
    
    $me_resp = file_get_contents(BASE_URL . 'me', false, $me_context);
    $me_json = json_decode($me_resp, true);
    $me_id = $me_json['id'];
    
    # Check if this month's playlist exists
    
    $playlistName = MONTHS[$state[0]] . ' ' . $state[1] . ' Marketplace Tracks';
    
    $checkPlaylistOpts = [
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $spot_token . "\r\n"
        ]
    ];
    
    $checkPlaylistContext = stream_context_create($checkPlaylistOpts);
    
    $checkPlaylistReq = file_get_contents(BASE_URL  . 'me/playlists', false, $checkPlaylistContext);
    
    $checkPlaylistJson = json_decode($checkPlaylistReq, true);
            
    foreach ($checkPlaylistJson['items'] as $playlist) {
        #TODO should check if $user owns playlist
        if (!strcmp($playlistName, $playlist['name'])) {
            $playlistID = $playlist['id'];
        }
    }
    
    #echo 'playlistID' . $playlistID;
    
    # Create new playlist if one does not exist
    # DEVELOPMENT TEMP ALWAYS CREATE NEW PLAYLIST
    #if (!$playlistID) {
    if (true) {

        $playlist_data = [
            'name' => $playlistName,
            'description' => 'A playlist of Marketplace tracks by Marketplaylister.',
        ];
        
        $playlist_opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Authorization: Bearer ' . $spot_token . "\r\n"
                        . 'Content-Type application/json \r\n',
                'content' => json_encode($playlist_data)
            ]
        ];

        $playlist_context = stream_context_create($playlist_opts);
        $playlist_req = file_get_contents(BASE_URL . 'users/' . $me_id . '/playlists', false, $playlist_context);
        $playlist_json = json_decode($playlist_req, true);
        $playlistID = $playlist_json['id'];
        
        #echo '<br />' . $playlistID;
        
    }
    
    $uris = [];
    
    $pdo = new PDO("sqlite:mktplc.sqlite3");
    $stmt = $pdo->prepare("SELECT uri FROM songs s WHERE (SELECT strftime('%m', s.date) == :month and strftime('%Y', s.date) == :year;");
    $stmt->bindParam(':month', $state[0]);
    $stmt->bindParam(':year', $state[1]);
    if ($stmt->execute()) {
      while ($row = $stmt->fetch()) {
        $uris[] = $row['uri'];
      }
    }
        
    $update_data = [
        'uris' => $uris,
    ];
    
    echo '<br /> update_data <br />';
    #print_r($update_data);
    
    $update_opts = [
        'http' => [
            'method' => 'POST',
            'header' => 'Authorization: Bearer ' . $spot_token . "\r\n"
                     . 'Content-Type application/json \r\n',
            'content' => json_encode($update_data)
        ]
    ];
    
    $update_context = stream_context_create($update_opts);
    $update_url = BASE_URL . 'users/' . $me_id . '/playlists/' . $playlistID . '/tracks';
    echo '<br />' . $update_url;
    echo '<br />';
    echo '<br />' . count($uris);
    echo '<br />';
    print_r(json_encode($update_data));
    $update_req = file_get_contents(BASE_URL . 'users/' . $me_id . '/playlists/' . $playlistID . '/tracks', false, $update_context);
    print_r($update_req);
    
    file_put_contents(DATE_FILE, $recentEpDT->format(DATE_FORM));
    