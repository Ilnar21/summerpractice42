# Telegram Bot
Этот Telegram-бот обрабатывает изображения, присланные пользователями.
 
Он умеет:

Кадрировать изображение по формату

Преобразовывать изображение в чёрно-белое 

Конвертировать изображение в jpg,png,tiff

Требования для запуска бота:

PHP 7.4+

Установленный ngrok

VPN (обязательно для работы ngrok в РФ)

Telegram-бот (t.me/sumpractice_bot)

Как запустить?

Запусти локальный PHP-сервер:

php -S localhost:8080

Запусти ngrok и пробрось порт 8080:

Убедись, что ngrok.exe лежит в той же папке и включён VPN:

.\ngrok.exe http 8080

Скопируй HTTPS-ссылку из вывода

Установи Webhook в Telegram (замени ссылку своим адресом):

curl "https://api.telegram.org/bot7258168451:AAGHSXW3twsuoSGlrl_EtmkpkPaYYCsC_ro/setWebhook?url=https://91f6e99152c1.ngrok-free.app/bot.php"

Готово!
