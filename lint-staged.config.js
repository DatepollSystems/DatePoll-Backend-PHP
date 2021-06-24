module.exports = {
    '**/*.php': ['php ./vendor/bin/php-cs-fixer fix --using-cache=no --config .php_cs.dist'],
};

