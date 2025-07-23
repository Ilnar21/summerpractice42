<?php
require_once __DIR__ . '/../config.php';

function getStatePath($user_id) {
    return TEMP_DIR . "state_$user_id.json";
}

function saveState($user_id, $state) {
    file_put_contents(getStatePath($user_id), json_encode($state));
}

function loadState($user_id) {
    $path = getStatePath($user_id);
    return file_exists($path) ? json_decode(file_get_contents($path), true) : null;
}

function clearUserFiles($user_id) {
    @unlink(TEMP_DIR . "$user_id.jpg");
    @unlink(TEMP_DIR . "$user_id.png");
    @unlink(TEMP_DIR . "$user_id.jpeg");
    @unlink(getStatePath($user_id));
}
