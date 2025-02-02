# Variables
PHP = php
COMPOSER = composer
SYMFONY = symfony
COMPOSER_INSTALL = $(COMPOSER) require
SYMFONY_CONSOLE = $(PHP) bin/console

## —— 🔥 App ——
init: ## Création de la base de donnée, gestion des migrations & fixtures
		$(COMPOSER) install
		$(SYMFONY_CONSOLE) doctrine:database:create
		$(SYMFONY_CONSOLE) doctrine:schema:update --force
		$(SYMFONY_CONSOLE) d:f:l --no-interaction
		$(SYMFONY) serve