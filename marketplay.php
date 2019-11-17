<?php
require 'secrets.php';
$scopes = 'playlist-modify-private playlist-modify-public';
$url = 'https://accounts.spotify.com/en/authorize?response_type=code&client_id=93a6f9c0375c45d4b348157691aa24e8&scope=' . urlencode($scopes) . '&redirect_uri=' . urlencode(REDIRECT_URI) . '&state=' . $_GET['state'];
header('Location: ' .$url);
exit();

