<?php
require_once __DIR__ . '/../config.php';

function sendMessage($chat_id, $text, $buttons = null) {
    $data = ['chat_id' => $chat_id, 'text' => $text];
    if ($buttons) {
        $keyboard = [];
        foreach ($buttons as $row) {
            $keyboard[] = array_map(fn($b) => ['text' => strtoupper($b), 'callback_data' => $b], $row);
        }
        $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
    }
    file_get_contents(API_URL . "/sendMessage?" . http_build_query($data));
}

function sendDocument($chat_id, $file_path) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => API_URL . "/sendDocument",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            "chat_id" => $chat_id,
            "document" => new CURLFile(realpath($file_path))
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    curl_exec($curl);
    curl_close($curl);
}
