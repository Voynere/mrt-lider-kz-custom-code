# Чеклист настройки в WordPress Admin

## Новый стандартный филиал

- [ ] Рубрика Category со slug = `{city_slug}`
- [ ] Пост «Контакты»: рубрики `{city_slug}` + `contacty`, заполнить ACF
- [ ] Страница главной: slug `{city_slug}`, шаблон **home**
- [ ] Дочерние страницы под филиал (или city-prefix URL): услуги, контакты, врачи, FAQ
- [ ] Импорт прайса: лист Excel = имя/slug филиала
- [ ] Метрики (опционально): Настройки → Метрики городов

## Филиал almaty_aubakirova (МРТ животным)

- [ ] Рубрика `almaty_aubakirova`
- [ ] Contact-пост с адресом: ул. Аубакирова, 17/1, с. Отеген батыра
- [ ] Страница: slug `almaty_aubakirova`, шаблон **home-animals**
- [ ] Прайс: лист `almaty_aubakirova` в Excel
- [ ] Telegram chat IDs в ACF для заявок с формы
- [ ] Проверить URL: `/almaty_aubakirova/`
- [ ] Проверить выбор в модальном окне городов

## После деплоя кода

1. Push в `main` → GitHub Actions rsync
2. Очистить кэш (если есть плагин кэширования на сервере)
3. Проверить форму записи (модалка + WhatsApp)
4. Проверить мобильную вёрстку animals-лендинга

## ACF-поля контактов (основные)

| Группа | Поля |
|--------|------|
| contacts_addresses | contacts_address_1..5 |
| contacts_phones | contacts_phone_1..3 |
| contacts_emails | contacts_email_1..3 |
| contacts_opening_hours | contacts_opening_hours_1, contacts_opening_days |
| contacts_map | HTML embed |
| contacts_whatsapp | номер |
| telegram_chats | telegram_chat_1, telegram_chat_2 |
