- [Быстрый старт](#Быстрый-старт)
    - [Получение токена](#Получение-токена)
    - [Установка библиотеки](#Установка-библиотеки)
    - [Установка библиотеки в Laravel](#Установка-библиотеки-в-Laravel)
    - [Инициализация бота](#Инициализация-бота)
- [Информация о боте](#Информация-о-боте)
    - `GET /me` (`getBotInfo`) - [*Получение информации о боте.*](#Получение-информации-о-боте)
    - `PATCH /me` (`editBotInfo`) - [*Редактирование информации о боте.*](#Редактирование-информации-о-боте)
- [Чаты](#Чаты)
    - `GET /chats` (`getChats`) - [*Получение списка всех чатов бота.*](#Получение-списка-всех-чатов-бота)
    - `GET /chats/{chatLink}` (`getChatByLink`) - *Получение информации о чате по ссылке.*
    - `GET /chats/{chatId}` (`getChat`) - *Получение информации о чате по ID.*
    - `PATCH /chats/{chatId}` (`editChat`) - *Редактирование информации о чате.*
    - `DELETE /chats/{chatId}` (`deleteChat`) - *Удаление чата.*
    - `POST /chats/{chatId}/actions` (`sendAction`) - *Отправка действия в чат (например, "печатает...").*
    - `GET /chats/{chatId}/pin` (`getPinnedMessage`) - *Получение закрепленного сообщения.*
    - `PUT /chats/{chatId}/pin` (`pinMessage`) - *Закрепление сообщения.*
    - `DELETE /chats/{chatId}/pin` (`unpinMessage`) - *Открепление сообщения.*
    - `GET /chats/{chatId}/members/me` (`getMembership`) - *Получение информации о членстве бота в чате.*
    - `DELETE /chats/{chatId}/members/me` (`leaveChat`) - *Выход бота из чата.*
    - `GET /chats/{chatId}/members/admins` (`getAdmins`) - *Получение администраторов чата.*
    - `POST /chats/{chatId}/members/admins` (`addAdmins`) - *Назначение администраторов чата.*
    - `DELETE /chats/{chatId}/members/admins/{userId}` (`deleteAdmins`) - *Снятие прав администратора.*
    - `GET /chats/{chatId}/members` (`getMembers`) - *Получение участников чата.*
    - `POST /chats/{chatId}/members` (`addMembers`) - *Добавление участников в чат.*
    - `DELETE /chats/{chatId}/members` (`deleteMember`) - *Удаление участника из чата.*
- Получение обновлений
    - `GET /subscriptions` (`getSubscriptions`) - *Получение списка Webhook-подписок.*
    - `POST /subscriptions` (`subscribe`) - *Создание Webhook-подписки.*
    - `DELETE /subscriptions` (`unsubscribe`) - *Удаление Webhook-подписки.*
    - `GET /updates` (`getUpdates`) - *Получение обновлений через Long-Polling.*
- Загрузка файлов
    - `POST /uploads` (`getUploadUrl`) - *Получение URL для загрузки файла.*
- Сообщения
    - `GET /messages` (`getMessages`) - *Получение списка сообщений из чата.*
    - `POST /messages` (`sendMessage`) - *Отправка сообщения.*
    - `PUT /messages` (`editMessage`) - *Редактирование сообщения.*
    - `DELETE /messages` (`deleteMessage`) - *Удаление сообщения.*
    - `GET /messages/{messageId}` (`getMessageById`) - *Получение сообщения по ID.*
    - `GET /videos/{videoToken}` (`getVideoAttachmentDetails`) - *Получение детальной информации о видео.*
    - `POST /answers` (`answerOnCallback`) - *Ответ на нажатие callback-кнопки.*

## Быстрый старт

> Если вы новичок, то можете прочитать [официальную документацию](https://dev.max.ru/), написанную разработчиками Max.

### Получение токена

Откройте диалог с [MasterBot](https://max.ru/MasterBot), следуйте инструкциям и создайте нового бота. После создания
бота MasterBot отправит вам токен.

### Установка библиотеки

```bash
composer require bushlanov-dev/max-bot-api-client-php
```

### Установка библиотеки в Laravel

Пользователи Laravel могут зарегистрировать сервис провайдер и фасад в `config/app.php`:

```php
'providers' => [
    // ...
    BushlanovDev\MaxMessengerBot\Laravel\MaxBotServiceProvider::class,
],
// ...
'aliases' => [
    // ...
    'MaxBot' => BushlanovDev\MaxMessengerBot\Laravel\MaxBotFacade::class,
],
```

При не необходимости опубликовать конфиг выполните следующую команду

```bash
php artisan vendor:publish --provider="BushlanovDev\MaxMessengerBot\Laravel\MaxBotServiceProvider"
```

Для работы вам потребуется внести следующие настройки в `.env`

```env
MAXBOT_ACCESS_TOKEN=your_bot_access_token_here
MAXBOT_WEBHOOK_SECRET=your_webhook_secret_here
MAXBOT_LOGGING_ENABLED=true
```

### Инициализация бота

Единственной обязательной настройкой является токен вашего бота.  
⚠️ Никогда, и ни при каких обстоятельствах не храните токен в коде. ⚠️  
Используйте переменные окружения!

```php
require __DIR__.'/vendor/autoload.php';

use BushlanovDev\MaxMessengerBot\Api;

$api = new Api('YOUR_BOT_API_TOKEN');
```

Так же вы можете создать экземпляр бота гибко настроив все зависимости под свои нужды.

```php
$api = new Api(
    client: new Client(...),
    modelFactory: new ModelFactory(),
    logger: new YourPsrLogger(),
);
```

## Информация о боте

### Получение информации о боте

```php
$botInfo = $api->getBotInfo();
```

### Редактирование информации о боте

Обратите внимание что данный метод отправляется PATCH запросом. Это значит, что будут обновлены только переданные
поля.  
В следующем примере мы изменяем только название бота и отчистим его описание. Остальные поля останутся неизменными.

```php
$botInfo = $api->editBotInfo(
    new BotPatch(
        name: 'Супер бот',
        description: null,
    )
);
```

## Чаты

### Получение списка всех чатов бота

```php
$api->getChats(
    count: 10, // Количество запрашиваемых чатов
    marker: 2, // Указатель на следующую страницу данных. Для первой страницы передайте null
);
```
