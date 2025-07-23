<?php
require_once 'config.php';
require_once 'functions/message.php';
require_once 'functions/state.php';
require_once 'functions/image.php';

$update = json_decode(file_get_contents("php://input"), true);
file_put_contents(LOG_FILE, print_r($update, true));

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $user_id = $update['message']['from']['id'];
} elseif (isset($update['callback_query'])) {
    $chat_id = $update['callback_query']['message']['chat']['id'];
    $user_id = $update['callback_query']['from']['id'];
} else {
    exit;
}

$image_path = glob(TEMP_DIR . "$user_id.*")[0] ?? null;

if (isset($update['message']['text']) && $update['message']['text'] === '/start') {
    sendMessage($chat_id, "Привет! Отправь мне фотографию");
    exit;
}

// Фото
if (isset($update['message']['photo'])) {
    $photo = end($update['message']['photo']);
    $file_info = json_decode(file_get_contents(API_URL . "/getFile?file_id={$photo['file_id']}"), true);
    $url = "https://api.telegram.org/file/bot" . BOT_TOKEN . "/" . $file_info['result']['file_path'];
    $image_path = TEMP_DIR . "$user_id.jpg";
    file_put_contents($image_path, file_get_contents($url));

    saveState($user_id, ['resize' => null, 'grayscale' => null, 'format' => null]);
    sendMessage($chat_id, "Выбери размер изображения:", [['1:1', '4:3'], ['16:9', 'Оригинал']]);
    exit;
}

// Документ
elseif (isset($update['message']['document'])) {
    $doc = $update['message']['document'];
    $file_name = $doc['file_name'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif', 'tiff'];

    if (!in_array($ext, $allowed)) {
        sendMessage($chat_id, "Данный формат не поддерживается");
        exit;
    }

    $file_info = json_decode(file_get_contents(API_URL . "/getFile?file_id={$doc['file_id']}"), true);
    $url = "https://api.telegram.org/file/bot" . BOT_TOKEN . "/" . $file_info['result']['file_path'];
    $image_path = TEMP_DIR . "$user_id.$ext";
    file_put_contents($image_path, file_get_contents($url));

    saveState($user_id, ['resize' => null, 'grayscale' => null, 'format' => null]);
    sendMessage($chat_id, "Выбери размер изображения:", [['1:1', '4:3'], ['16:9', 'Оригинал']]);
    exit;
}

if (isset($update['callback_query'])) {
    $data = $update['callback_query']['data'];
    $state = loadState($user_id);

    if (!$state || !$image_path) {
        sendMessage($chat_id, "Сессия устарела. Отправь изображение заново.");
        exit;
    }

    if ($state['resize'] === null) {
        $state['resize'] = $data;
        if ($data !== 'Оригинал' && $data !== 'original') {
            cropToAspect($image_path, $data);
        }
        saveState($user_id, $state);
        sendMessage($chat_id, "Преобразовать в черно-белое?", [['Да', 'Нет']]);
        exit;
    }

    if ($state['grayscale'] === null) {
        $state['grayscale'] = $data === 'Да';
        if ($state['grayscale']) {
            applyGrayscale($image_path);
        }
        saveState($user_id, $state);
        sendMessage($chat_id, "Выбери формат:", [['jpg', 'png']]);
        exit;
    }

    if ($state['format'] === null && in_array($data, ['jpg', 'png'])) {
        $state['format'] = $data;
        $output = TEMP_DIR . "$user_id.$data";

        $ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $img = imagecreatefromjpeg($image_path);
                break;
            case 'png':
                $img = imagecreatefrompng($image_path);
                break;
            case 'webp':
                $img = imagecreatefromwebp($image_path);
                break;
            case 'bmp':
                $img = imagecreatefrombmp($image_path);
                break;
            case 'gif':
                $img = imagecreatefromgif($image_path);
                break;
            case 'tiff':
                sendMessage($chat_id, "TIFF пока не поддерживается.");
                exit;
            default:
                sendMessage($chat_id, "Неподдерживаемый формат файла.");
                exit;
        }

        if ($data === 'jpg') {
            imagejpeg($img, $output);
        } else {
            imagepng($img, $output);
        }

        imagedestroy($img);
        sendMessage($chat_id, "Вот результат:");
        sendDocument($chat_id, $output);
        clearUserFiles($user_id);
    }
}
