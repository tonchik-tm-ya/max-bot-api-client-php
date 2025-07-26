# Max Bot API Client library for PHP

[![Actions status](https://github.com/BushlanovDev/max-bot-api-client-php/actions/workflows/ci.yml/badge.svg?style=flat-square)](https://github.com/BushlanovDev/max-bot-api-client-php/actions)
[![Coverage](https://raw.githubusercontent.com/BushlanovDev/max-bot-api-client-php/refs/heads/master/badge-coverage.svg?v=1)](https://github.com/BushlanovDev/max-bot-api-client-php/actions)
[![PHP version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg?style=flat-square)](https://github.com/BushlanovDev/max-bot-api-client-php)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

> [!CAUTION]  
> На мой взгляд `Max Messenger` является ни чем иным как малварью, созданной для слежки за гражданами РФ. Настоятельно
> не рекомендую использовать его на реальных устройствах, с настоящим номером телефона, и для личной переписки.

> [!IMPORTANT]  
> Библиотека в стадии активной разработки.

## Быстрый старт

> Если вы новичок, то можете прочитать [официальную документацию](https://dev.max.ru/), написанную разработчиками Max

### Получение токена

Откройте диалог с [MasterBot](https://max.ru/MasterBot), следуйте инструкциям и создайте нового бота. После создания
бота MasterBot отправит вам токен.

## Реализованные методы

#### Bots

- [x] `GET /me` (`getBotInfo`) — *Получение информации о боте.*
- [ ] `PATCH /me` (`editBotInfo`) — *Редактирование информации о боте.*

#### Chats

- [ ] `GET /chats` (`getChats`) — *Получение списка всех чатов бота.*
- [ ] `GET /chats/{chatLink}` (`getChatByLink`) — *Получение информации о чате по ссылке.*
- [x] `GET /chats/{chatId}` (`getChat`) — *Получение информации о чате по ID.*
- [ ] `PATCH /chats/{chatId}` (`editChat`) — *Редактирование информации о чате.*
- [ ] `DELETE /chats/{chatId}` (`deleteChat`) — *Удаление чата.*
- [ ] `POST /chats/{chatId}/actions` (`sendAction`) — *Отправка действия в чат (например, "печатает...").*
- [ ] `GET /chats/{chatId}/pin` (`getPinnedMessage`) — *Получение закрепленного сообщения.*
- [ ] `PUT /chats/{chatId}/pin` (`pinMessage`) — *Закрепление сообщения.*
- [ ] `DELETE /chats/{chatId}/pin` (`unpinMessage`) — *Открепление сообщения.*
- [ ] `GET /chats/{chatId}/members/me` (`getMembership`) — *Получение информации о членстве бота в чате.*
- [ ] `DELETE /chats/{chatId}/members/me` (`leaveChat`) — *Выход бота из чата.*
- [ ] `GET /chats/{chatId}/members/admins` (`getAdmins`) — *Получение администраторов чата.*
- [ ] `POST /chats/{chatId}/members/admins` (`postAdmins`) — *Назначение администраторов чата.*
- [ ] `DELETE /chats/{chatId}/members/admins/{userId}` (`deleteAdmins`) — *Снятие прав администратора.*
- [ ] `GET /chats/{chatId}/members` (`getMembers`) — *Получение участников чата.*
- [ ] `POST /chats/{chatId}/members` (`addMembers`) — *Добавление участников в чат.*
- [ ] `DELETE /chats/{chatId}/members` (`removeMember`) — *Удаление участника из чата.*

#### Subscriptions

- [x] `GET /subscriptions` (`getSubscriptions`) — *Получение списка Webhook-подписок.*
- [x] `POST /subscriptions` (`subscribe`) — *Создание Webhook-подписки.*
- [x] `DELETE /subscriptions` (`unsubscribe`) — *Удаление Webhook-подписки.*
- [x] `GET /updates` (`getUpdates`) — *Получение обновлений через Long-Polling.*

#### Upload

- [x] `POST /uploads` (`getUploadUrl`) — *Получение URL для загрузки файла.*

#### Messages

- [ ] `GET /messages` (`getMessages`) — *Получение списка сообщений из чата.*
- [x] `POST /messages` (`sendMessage`) — *Отправка сообщения.*
- [ ] `PUT /messages` (`editMessage`) — *Редактирование сообщения.*
- [ ] `DELETE /messages` (`deleteMessage`) — *Удаление сообщения.*
- [ ] `GET /messages/{messageId}` (`getMessageById`) — *Получение сообщения по ID.*
- [ ] `GET /videos/{videoToken}` (`getVideoAttachmentDetails`) — *Получение детальной информации о видео.*
- [ ] `POST /answers` (`answerOnCallback`) — *Ответ на нажатие callback-кнопки.*
