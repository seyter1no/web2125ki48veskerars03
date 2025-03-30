# Використовуємо офіційний PHP образ з Apache
FROM php:8.1-apache

# Копіюємо ваш код у папку /var/www/html всередині контейнера
COPY . /var/www/html/

# Встановлюємо необхідні розширення PHP, якщо це потрібно
RUN docker-php-ext-install mysqli

# Відкриваємо порт 80 для доступу до сайту
EXPOSE 80