FROM php:7.0.11-cli

# gd
RUN apt-get update && apt-get install --no-install-recommends -y \
	libpng-dev \
	libfreetype6-dev \
	libjpeg-dev \
	libxpm-dev \
	libxml2-dev \
	libxslt-dev \
	libssh-dev \
	libwebp-dev # php >=7.0 (use libvpx for php <7.0)

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/lib/x86_64-linux-gnu/ --with-jpeg-dir=/usr/lib/x86_64-linux-gnu/  --with-xpm-dir=/usr/lib/x86_64-linux-gnu/ --with-webp-dir=/usr/lib/x86_64-linux-gnu/ # php >=7.0 (use libvpx for php <7.0)

RUN docker-php-ext-install gd

# zip gmp bcmath pdo_mysql zip gmp mysqli

RUN apt-get install --no-install-recommends -y \
	libzip-dev \
	libgmp-dev

RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/

RUN docker-php-ext-configure gmp

RUN docker-php-ext-install -j$(nproc) bcmath \
	&& docker-php-ext-install -j$(nproc) pdo_mysql \
	&& docker-php-ext-install -j$(nproc) zip \
	&& docker-php-ext-install -j$(nproc) gmp \
	&& docker-php-ext-install mysqli

# install secp256k1 && php secp256k1 ext

RUN apt-get install -y autoconf \
	git  \
	automake \
	g++ \
	make \
	libtool

RUN git clone https://github.com/bitcoin-core/secp256k1.git \
    && ( \
           cd secp256k1 \
           # ECDH api is destroyed, check the commit of the latest secp256k1 before the merge of 95e99f196fd08a8b2c236ab99d7e7fec8f6dc78f
           && git checkout 452d8e4d2a2f9f1b5be6b02e18f1ba102e5ca0b4 -f \
           && ./autogen.sh \
           && ./configure --enable-experimental --enable-module-ecdh --enable-module-recovery \
           && make \
           && make install \
       ) \
    && rm -rf secp256k1

RUN wget https://github.com/Bit-Wasp/secp256k1-php/archive/v0.1.3.tar.gz \
    && mv secp256k1-php-0.1.3.tar.gz secp256k1-php.tar.gz

RUN mkdir -p secp256k1-php \
    && tar -xf secp256k1-php.tar.gz -C secp256k1-php --strip-components=1 \
    && rm secp256k1-php.tar.gz \
    && ( \
        cd secp256k1-php/secp256k1 \
        && phpize \
        && ./configure --enable-secp256k1 \
        && make -j$(nproc) \
        && make install \
    ) \
    && rm -r secp256k1-php \
    && docker-php-ext-enable secp256k1

# install php keccak ext
RUN git clone https://github.com/EricYChu/php-keccak-hash.git

RUN \
    ( \
		cd php-keccak-hash \
		&& phpize \
		&& ./configure --enable-keccak \
		&& make -j$(nproc) \
		&& make install \
	) \
	&& rm -rf php-keccak-hash \
	&& docker-php-ext-enable keccak


# COPY the configuration
# COPY php.ini $PHP_INI_DIR/php.ini

# clean
RUN apt-get purge --auto-remove -y g++ git \
	&& apt-get clean \
    && rm -rf /var/lib/apt/lists/*

