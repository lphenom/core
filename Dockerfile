FROM php:8.1.31-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
    && docker-php-ext-install opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer 2.9.5
COPY --from=composer:2.9.5 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["php", "-v"]

