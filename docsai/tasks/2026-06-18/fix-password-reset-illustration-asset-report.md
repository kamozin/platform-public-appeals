# Отчет: исправлена битая иллюстрация восстановления пароля

## Что сделано

- Заменена поврежденная frontend-копия `password-reset-illustration.png`.
- Источником использован валидный файл `static/assets/password-reset-illustration.png`.
- Разметка страницы `/password-reset` и backend API не менялись.

## Затронутые файлы

- `frontend/public/assets/password-reset-illustration.png`
- `docsai/tasks/2026-06-18/fix-password-reset-illustration-asset.md`
- `docsai/tasks/2026-06-18/fix-password-reset-illustration-asset-report.md`

## Проверки

- Frontend PNG успешно декодируется как изображение.
- SHA256 frontend-копии совпадает с SHA256 исходника из `static/assets`.
- `static/` не изменялся.

## Postman

Postman collection не обновлялась: API endpoints и контракты не менялись.
