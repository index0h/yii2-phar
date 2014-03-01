
phar:
	/usr/bin/env php bin/yii2-phar phar/build tests/unit/_phar.php

test:
	/usr/bin/env php ./.test.php run --coverage --html --xml

test-phar: phar
	/usr/bin/env php ./.test.phar.php run --no-exit

test-all: test-phar test

.PHONY: phar test test-phar test-all