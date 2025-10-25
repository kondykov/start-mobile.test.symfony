# Symfony Book Management Application

Это приложение на базе Symfony для управления авторами и книгами. Оно включает административную панель для CRUD операций и RESTful API для работы с книгами.

## Требования

- Docker и Docker Compose

## Установка и запуск

1. Клонируйте репозиторий:
   ```bash
   git clone https://github.com/kondykov/start-mobile.test.symfony.git
   cd start-mobile.test.symfony
   ```

2. Запустите контейнеры:
   ```bash
   docker-compose up -d
   ```

3. Установите зависимости (если не установлены в контейнере):
   ```bash
   docker-compose exec app composer install
   ```

4. Выполните миграции базы данных:
   ```bash
   docker-compose exec app php bin/console doctrine:migrations:migrate
   ```

5. Приложение будет доступно по адресу: http://localhost:8080

## Структура проекта

- `src/Entity/` - Сущности Doctrine (Author, Book)
- `src/Controller/` - Контроллеры для веб-интерфейса
- `src/Controller/Api/V1/` - API контроллеры
- `src/Service/` - Бизнес-логика
- `src/Repository/` - Репозитории для работы с БД
- `templates/` - Twig шаблоны
- `migrations/` - Миграции базы данных

## Административная панель

### Авторы
- `/authors` - Список авторов с количеством книг
- `/authors/new` - Создание нового автора
- `/authors/{id}` - Просмотр автора и его книг
- `/authors/{id}/edit` - Редактирование автора
- `/authors/{id}` (DELETE) - Удаление автора

### Книги
- `/books` - Список всех книг с именами авторов
- `/authors/{authorId}/books` - Добавление книги к автору
- `/authors/{authorId}/books/{bookId}` - Обновление/удаление книги

## RESTful API

Все API запросы должны содержать заголовок `X-API-User-Name: admin`. В противном случае возвращается ошибка 403.

### Endpoints

#### Получить список книг
```
GET /api/v1/books
```
**Ответ:**
```json
{
  "books": [
    {
      "id": 1,
      "title": "Book Title",
      "author": "Author Name",
      "created_at": "2023-01-01 12:00:00",
      "updated_at": "2023-01-01 12:00:00"
    }
  ],
  "pagination": {
    "total": 10,
    "page": 1,
    "pageSize": 20
  }
}
```

#### Получить книгу по ID
```
GET /api/v1/books/{id}
```
**Ответ:**
```json
{
  "data": {
    "id": 1,
    "title": "Book Title",
    "author": "Author Name",
    "created_at": "2023-01-01 12:00:00",
    "updated_at": "2023-01-01 12:00:00"
  }
}
```

#### Обновить книгу
```
PUT /api/v1/books/{id}
Content-Type: application/x-www-form-urlencoded

title=New Title&author=1
```
**Ответ:**
```json
{
  "data": {
    "id": 1,
    "title": "New Title",
    "author": "Author Name",
    "created_at": "2023-01-01 12:00:00",
    "updated_at": "2023-01-02 12:00:00"
  }
}
```

#### Удалить книгу
```
DELETE /api/v1/books/{id}
```
**Ответ:** 204 No Content

## Тестирование API

Примеры команд для тестирования:

```bash
# Получить список книг
docker-compose exec app curl -H "X-API-User-Name: admin" http://nginx/api/v1/books

# Получить книгу по ID
docker-compose exec app curl -H "X-API-User-Name: admin" http://nginx/api/v1/books/1

# Обновить книгу
docker-compose exec app curl -H "X-API-User-Name: admin" -X PUT -d "title=Updated Title&author=1" http://nginx/api/v1/books/1

# Удалить книгу
docker-compose exec app curl -H "X-API-User-Name: admin" -X DELETE http://nginx/api/v1/books/1

# Попытка доступа без заголовка (должна вернуть 403)
docker-compose exec app curl http://nginx/api/v1/books
```

## База данных

Приложение использует PostgreSQL. Доступ к базе:
- Хост: localhost:8090
- База: start-mobile
- Пользователь: root
- Пароль: root




