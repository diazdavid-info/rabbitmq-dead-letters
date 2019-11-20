configuration:
	php apps/testing/backend/public/console.php testing:configuration:rabbit_configuration

publish:
	php apps/testing/backend/public/console.php testing:random:message_publish

consume:
	php apps/testing/backend/public/console.php testing:random:message_consume

consume_with_exception:
	php apps/testing/backend/public/console.php testing:random:message_consume 1