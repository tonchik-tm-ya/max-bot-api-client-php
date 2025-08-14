# Max Bot API Client library for PHP

[![Actions status](https://github.com/BushlanovDev/max-bot-api-client-php/actions/workflows/ci.yml/badge.svg?style=flat-square)](https://github.com/BushlanovDev/max-bot-api-client-php/actions)
[![Coverage](https://raw.githubusercontent.com/BushlanovDev/max-bot-api-client-php/refs/heads/master/badge-coverage.svg?v=1)](https://github.com/BushlanovDev/max-bot-api-client-php/actions)
[![Packagist Version](https://img.shields.io/packagist/v/bushlanov-dev/max-bot-api-client-php.svg?style=flat-square)](https://packagist.org/packages/bushlanov-dev/max-bot-api-client-php)
[![PHP version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg?style=flat-square)](https://github.com/BushlanovDev/max-bot-api-client-php)
[![Laravel](https://img.shields.io/badge/%20Laravel%20Package-available-success?logo=laravel&style=flat-square)](https://github.com/BushlanovDev/max-bot-api-client-php)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

> [!CAUTION]  
> На мой взгляд `Max Messenger` является ни чем иным как малварью, созданной для слежки за гражданами РФ. Настоятельно
> не рекомендую использовать его на реальных устройствах, с настоящим номером телефона, и для личной переписки.

## Быстрый старт

> Если вы новичок, то можете прочитать [официальную документацию](https://dev.max.ru/), написанную разработчиками Max.

### Получение токена

Откройте диалог с [MasterBot](https://max.ru/MasterBot), следуйте инструкциям и создайте нового бота. После создания
бота MasterBot отправит вам токен.

### Установка библиотеки

```bash
composer require bushlanov-dev/max-bot-api-client-php
```

### Использование

Отправка сообщения с клавиатурой

```php
$api = new \BushlanovDev\MaxMessengerBot\Api('YOUR_BOT_API_TOKEN');

$api->sendMessage(
    userId: 123,     // ID пользователя получателя сообщения
    chatId: 321,     // Или ID чата, в который нужно отправить сообщение
    text: 'Привет!', // Текст сообщения, вы можете использовать HTML или Markdown
    attachments: [
        new InlineKeyboardAttachmentRequest([
            [new CallbackButton('Нажми меня!', 'payload_button1')],
            [new LinkButton('Нажми меня!', 'https://example.com')],
        ]),
    ],
    format: MessageFormat::Markdown, // Формат сообщения (Markdown или HTML)
);
```

Подписка на вэб хуки

```php
$api->subscribe(
    url: 'https://example.com/webhook', // URL на который будут приходить хуки
    secret: 'super_secret',             // Секретная фраза для проверки хуков
    updateTypes: [
        // Типы хуков которые вы хотите получать (либо ничего не указывать, чтобы получать все)
        UpdateType::BotStarted,
        UpdateType::MessageCreated,
    ],
);
```

Обработка хуков

```php
$webhookHandler = $api->createWebhookHandler();

$webhookHandler->addHandler(UpdateType::BotStarted, function (BotStartedUpdate $update, Api $api) {
    $api->sendMessage(
        chatId: $update->chatId,
        text: 'Я запущен!',
    );
});
```

## Реализованные методы

#### Bots

- [x] `GET /me` (`getBotInfo`) - *Получение информации о боте.*
- [x] `PATCH /me` (`editBotInfo`) - *Редактирование информации о боте.*

#### Chats

- [x] `GET /chats` (`getChats`) - *Получение списка всех чатов бота.*
- [x] `GET /chats/{chatLink}` (`getChatByLink`) - *Получение информации о чате по ссылке.*
- [x] `GET /chats/{chatId}` (`getChat`) - *Получение информации о чате по ID.*
- [x] `PATCH /chats/{chatId}` (`editChat`) - *Редактирование информации о чате.*
- [x] `DELETE /chats/{chatId}` (`deleteChat`) - *Удаление чата.*
- [x] `POST /chats/{chatId}/actions` (`sendAction`) - *Отправка действия в чат (например, "печатает...").*
- [x] `GET /chats/{chatId}/pin` (`getPinnedMessage`) - *Получение закрепленного сообщения.*
- [x] `PUT /chats/{chatId}/pin` (`pinMessage`) - *Закрепление сообщения.*
- [x] `DELETE /chats/{chatId}/pin` (`unpinMessage`) - *Открепление сообщения.*
- [x] `GET /chats/{chatId}/members/me` (`getMembership`) - *Получение информации о членстве бота в чате.*
- [x] `DELETE /chats/{chatId}/members/me` (`leaveChat`) - *Выход бота из чата.*
- [x] `GET /chats/{chatId}/members/admins` (`getAdmins`) - *Получение администраторов чата.*
- [x] `POST /chats/{chatId}/members/admins` (`addAdmins`) - *Назначение администраторов чата.*
- [x] `DELETE /chats/{chatId}/members/admins/{userId}` (`deleteAdmins`) - *Снятие прав администратора.*
- [x] `GET /chats/{chatId}/members` (`getMembers`) - *Получение участников чата.*
- [x] `POST /chats/{chatId}/members` (`addMembers`) - *Добавление участников в чат.*
- [x] `DELETE /chats/{chatId}/members` (`deleteMember`) - *Удаление участника из чата.*

#### Subscriptions

- [x] `GET /subscriptions` (`getSubscriptions`) - *Получение списка Webhook-подписок.*
- [x] `POST /subscriptions` (`subscribe`) - *Создание Webhook-подписки.*
- [x] `DELETE /subscriptions` (`unsubscribe`) - *Удаление Webhook-подписки.*
- [x] `GET /updates` (`getUpdates`) - *Получение обновлений через Long-Polling.*

#### Upload

- [x] `POST /uploads` (`getUploadUrl`) - *Получение URL для загрузки файла.*

#### Messages

- [x] `GET /messages` (`getMessages`) - *Получение списка сообщений из чата.*
- [x] `POST /messages` (`sendMessage`) - *Отправка сообщения.*
- [x] `PUT /messages` (`editMessage`) - *Редактирование сообщения.*
- [x] `DELETE /messages` (`deleteMessage`) - *Удаление сообщения.*
- [x] `GET /messages/{messageId}` (`getMessageById`) - *Получение сообщения по ID.*
- [x] `GET /videos/{videoToken}` (`getVideoAttachmentDetails`) - *Получение детальной информации о видео.*
- [x] `POST /answers` (`answerOnCallback`) - *Ответ на нажатие callback-кнопки.*

## Лицензия

Данная библиотека распространяется под лицензией MIT - подробности см. в файле [LICENSE](LICENSE).
