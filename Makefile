
build:
	php bin/yii2-phar phar/build tests/unit/_phar.php

test:
	php ./.test.php run --coverage --html --xml

test-phar: build
	php ./.test.phar.php run --no-exit

test-all: test-phar test

.PHONY: build test test-phar test-all