<?php
    require 'secrets.php';
    $scopes = 'playlist-modify-private playlist-modify-public';
    $url = 'https://accounts.spotify.com/en/authorize?response_type=code&client_id=868e2cba00de4819900dd8a647a7ba7d&scope=' . urlencode($scopes) . '&redirect_uri=' . urlencode(REDIRECT_URI) ;
    header('Location: ' .$url);
    exit();
