# Тестовое задание
для запуска 
```bash
composer install
php artisan migrate
php artisan storage:link
```

домен http://atlana.test

## Сущности

```
User
  id - идентификатор пользователя в гх, совпадает с локальным
  login - логин пользователя в гх, совпадает с локальным
  name - Имя пользователя в гх
  email - электронная почта
  avatar - ссылка на фото пользователя 
  location - местонахождение пользователя
  bio - биография пользователя 
  popularity - популярность, количество раз открытия пользователя в локальной системе
  followers - количество подписчиков в гх
  following - количество подписок в гх
  repositories_count - количество репозиториев пользователя (публичных)
  created_at - дата регистрации пользователя в гх
  updated_at - последняя дата обновления пользовтаеля в гх
```

```
Repository
  id - идентификатор репозитория в гх
  name - имя репозитория в гх
  user_id - ид автора репозиротория
  forks - количество ответвлений
  stars - количество звездочек
  created_at - создание репозитория
  updated_at - дата последнего обновления
```

## Доступные типы запросов

Все запросы при успешном выполнении присылают статус ответа 200.  В случае какой-то ошибки 
сервер вернет ответ в json формате с описанием ошибки и соответствующим кодом статуса (404, 403, 500)
```json
{"error":"Описание ошибки"}
```

### GET /api/users 
Доступны GET параметры

 - sort - сортировка по полям repositories_count/followers/popularity. По умолчанию repositories_count
 - order - порядок сортировки asc/desc. По умолчанию desc 
 - page - номер текущей страницы


Список всех пользователей в локальной базе которые ранее были найдены в поиске GitHub.
Список полей в ответе ограничен для вывода необходимой информации как на скрине в задаче
```json
{
    "id": 7430407,
    "avatar": "http://atlana.test/avatars/vpineda7.jpeg",
    "name": "Vladimir Pineda",
    "repositories_count": 553
},
....
```

В случае удачи в ответе будет json объект пегинатора. Пример
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 7430407,
            "avatar": "http://atlana.test/avatars/vpineda7.jpeg",
            "name": "Vladimir Pineda",
            "repositories_count": 553
        },
        ...
    ],
    "first_page_url": "http://atlana.test/api/users/search?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://atlana.test/api/users/search?page=3",
    "links": [
        {
            "url": null,
            "label": "Previous",
            "active": false
        },
        {
            "url": "http://atlana.tes/api/users/search?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": "http://atlana.test/api/users/search?page=2",
            "label": "2",
            "active": false
        },
        {
            "url": "http://atlana.test/api/users/search?page=3",
            "label": "3",
            "active": false
        },
        {
            "url": "http://atlana.test/api/users/search?page=2",
            "label": "Next",
            "active": false
        }
    ],
    "next_page_url": "http://atlana.test/api/users/search?page=2",
    "path": "http://atlana.test/api/users/search",
    "per_page": 3,
    "prev_page_url": null,
    "to": 3,
    "total": 9
}
```

### GET /api/users/search?q=<строка поиска>
Позволяет делать
Доступны GET параметры

 - q - обязательная строка поиска. Формат строки поиска совпадает с поиском гх, можно посмотреть по [ссылке](https://docs.github.com/en/search-github/searching-on-github/searching-users). Для простого поиска достаточно обычной строки для поиска по имени пользователя
 - sort - сортировка по полям repositories_count/followers/popularity. По умолчанию repositories_count
 - order - порядок сортировки asc/desc. По умолчанию desc 
 - page - номер текущей страницы

Пример ответ смотри в запросе GET /api/users 

### GET /api/users/top3 
Список из 3 наиболее популярных пользователей, по количеству просмотров в локальной системе

Пример ответа 
```json
[
    {
        "id": 28755317,
        "login": "tamniakav",
        "name": "Vladimir Gradev",
        "email": null,
        "avatar": "http://atlana.test/avatars/tamniakav.jpeg",
        "popularity": 4,
        "followers": 0,
        "following": 0,
        "location": null,
        "repositories_count": 200,
        "bio": null,
        "created_at": "2017-05-17T11:26:14.000000Z",
        "updated_at": "2022-03-22T11:39:33.000000Z"
    },
    {
        "id": 10488049,
        "login": "vlad-ovsyannikov",
        "name": "Vladimir Ovsyannikov",
        "email": null,
        "avatar": "http://atlana.test/avatars/vlad-ovsyannikov.jpeg",
        "popularity": 2,
        "followers": 5,
        "following": 9,
        "location": "Belarus, Minsk",
        "repositories_count": 197,
        "bio": null,
        "created_at": "2015-01-11T17:25:40.000000Z",
        "updated_at": "2022-03-18T14:25:47.000000Z"
    },
    {
        "id": 7430407,
        "login": "vpineda7",
        "name": "Vladimir Pineda",
        "email": null,
        "avatar": "http://atlana.test/avatars/vpineda7.jpeg",
        "popularity": 1,
        "followers": 8,
        "following": 81,
        "location": null,
        "repositories_count": 553,
        "bio": null,
        "created_at": "2014-04-28T15:25:37.000000Z",
        "updated_at": "2022-03-05T17:34:46.000000Z"
    }
]
```
### GET /api/users/{userId} 
Просмотр профиля пользователя. Ид юзера должно быть число.

В ответе объект пользователя. Пример ответа.
```json
{
    "id": 28755317,
    "login": "tamniakav",
    "name": "Vladimir Gradev",
    "email": null,
    "avatar": "http://atlana.test/avatars/tamniakav.jpeg",
    "popularity": 5,
    "followers": 0,
    "following": 0,
    "location": null,
    "repositories_count": 200,
    "bio": null,
    "created_at": "2017-05-17T11:26:14.000000Z",
    "updated_at": "2022-03-22T13:16:52.000000Z"
}
```

### GET /api/users/{userId}/repositories
Список репозиториев пользователя с возможностью поиска.
Доступны GET параметры

- q - необязательная строка поиска. Если строка поиска не заполнена, то выборка идет по всем репозиториям пользователя. Формат строки поиска совпадает с поиском гх, можно посмотреть по [ссылке](https://docs.github.com/en/search-github/searching-on-github/searching-for-repositories). Для простого поиска достаточно обычной строки для поиска по имени пользователя
- page - номер текущей страницы

Пример ответа
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 533846,
            "name": "WSZ2Web",
            "user_id": 160894,
            "forks": 0,
            "stars": 1,
            "created_at": "2010-02-24T13:19:53.000000Z",
            "updated_at": "2022-03-17T20:28:48.000000Z"
        },
        {
            "id": 647944,
            "name": "RABCDAsm",
            "user_id": 160894,
            "forks": 88,
            "stars": 393,
            "created_at": "2010-05-05T07:23:23.000000Z",
            "updated_at": "2022-03-15T16:13:32.000000Z"
        },
        {
            "id": 1790816,
            "name": "DFeed",
            "user_id": 160894,
            "forks": 101,
            "stars": 336,
            "created_at": "2011-05-23T23:50:15.000000Z",
            "updated_at": "2022-03-14T20:45:06.000000Z"
        }
    ],
    "first_page_url": "http://atlana.test/api/users/160894/repositories?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://atlana.test/api/users/160894/repositories?page=3",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://atlana.test/api/users/160894/repositories?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": "http://atlana.test/api/users/160894/repositories?page=2",
            "label": "2",
            "active": false
        },
        {
            "url": "http://atlana.test/api/users/160894/repositories?page=3",
            "label": "3",
            "active": false
        },
        {
            "url": "http://atlana.test/api/users/160894/repositories?page=2",
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": "http://atlana.test/api/users/16089\/repositories?page=2",
    "path": "http://atlana.test/api/users/160894/repositories",
    "per_page": 3,
    "prev_page_url": null,
    "to": 3,
    "total": 9
}
```
