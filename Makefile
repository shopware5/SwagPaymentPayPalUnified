.DEFAULT_GOAL := help

filter := "default"

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help

init: composer-install install-hooks install-plugin install-test-environment ## install the plugin SwagPaymentPayPalUnified with pre commit hook, requirements and the test environment

composer-install: ## Install composer requirements
	@echo "Install composer requirements"
	composer install

install-hooks: ## Install pre commit hooks
	@echo "Install pre commit hooks"
	.githooks/install_hooks.sh

install-plugin: .refresh-plugin-list ## Install and activate the SwagPaymentPayPalUnified plugin
	@echo "Install the plugin"
	./../../../bin/console sw:plugin:install SwagPaymentPayPalUnified --activate -c

install-test-environment: ## Installs the plugin test environment
	@echo "Install the test environment"
	./psh local:init

run-tests: ## Execute the php unit tests... (You can use the filter parameter "make run-tests filter=yourFilterPhrase")
ifeq ($(filter), "default")
	SHOPWARE_ENV=swagpaymentpaypalunifiedtest /home/dennis/www/shopware/vendor/phpunit/phpunit/phpunit --verbose
else
	SHOPWARE_ENV=swagpaymentpaypalunifiedtest /home/dennis/www/shopware/vendor/phpunit/phpunit/phpunit --verbose --filter $(filter)
endif

fix-cs: ## Run the code style fixer
	./../../../vendor/bin/php-cs-fixer fix

phpstan: ## Run PHPstan
	./../../../vendor/bin/phpstan analyse .

phpstan-generate-baseline: ## Run PHPstan
	./../../../vendor/bin/phpstan analyse . --generate-baseline

.refresh-plugin-list:
	@echo "Refresh the plugin list"
	./../../../bin/console sw:plugin:refresh
