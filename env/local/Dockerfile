FROM php:8.2.0-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
	libpq-dev \
    zip \
    unzip \
    libmcrypt-dev \
    libcurl4-openssl-dev \
    && pecl install -n mcrypt \
    && docker-php-ext-enable mcrypt

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*



# Install PHP extensions
RUN docker-php-ext-install \
        bcmath \
        curl \
        exif \
        gd \
        iconv \
        mbstring \
        pdo \
		mysqli \
        pdo_mysql \
        pcntl \
        xml \
        dom \
        zip \
        intl

# Enable mod rewrite
# RUN a2enmod rewrite headers

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Change PHP Memory Limit
RUN cd /usr/local/etc/php/conf.d/ && \
  echo 'memory_limit = 256M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
  echo 'post_max_size = 50M' >> /usr/local/etc/php/conf.d/docker-php-maxsize.ini && \
  echo 'upload_max_filesize = 40M' >> /usr/local/etc/php/conf.d/docker-php-maxsize.ini && \
  echo 'max_execution_time = 120' >> /usr/local/etc/php/conf.d/docker-php-maxexec.ini

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
#    chown -R $user:$user /home/$user && \
    chmod 775 -R /var/www && \
	chmod 777 -R /home/$user && \
	#chmod 777 -R /var/www/storage/logs && \
    chgrp www-data -R /var/www
RUN chown -R www-data:www-data /var/www


# Clean up
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    rm /var/log/lastlog /var/log/faillog

# Set working directory
WORKDIR /var/www

USER $user
