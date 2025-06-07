SHELL := /bin/bash

.DEFAULT_GOAL := help

UID := $(shell id -u)
USERNAME := $(shell id -u -n)
GID := $(shell id -g)
GROUPNAME := $(shell id -g -n)

CONTAINER := lottery-box

.PHONY: build
build: ## Build docker image for develop environment
	docker build -t lottery-box:8.4 ./docker/php \
		--build-arg UID=${UID} \
		--build-arg GID=${GID} \
		--build-arg USERNAME=${USERNAME} \
		--build-arg GROUPNAME=${GROUPNAME}

.PHONY: up
up: ## Start the container
	docker compose up -d

.PHONY: down
down: ## Delete the container
	docker compose down

.PHONY: php
php: ## Enter php container
	docker exec -it ${CONTAINER} bash

.PHONY: composer-install
composer-install: ## Install composer packages
	docker compose run --rm php composer install

.PHONY: phpstan
phpstan: ## Run PHPStan
	docker exec ${CONTAINER} composer phpstan

.PHONY: phpstan-clear-cache
phpstan-clear-cache: ## Clear PHPStan cache
	docker exec ${CONTAINER} composer phpstan-clear-cache

.PHONY: arkitect
arkitect: ## Run arkitect
	docker exec ${CONTAINER} composer arkitect

.PHONY: ecs
ecs: ## Run ecs
	docker exec ${CONTAINER} composer ecs

.PHONY: ecs-fix
ecs-fix: ## Run ecs fix
	docker exec ${CONTAINER} composer ecs-fix

.PHONY: tests
tests: ## Run all tests
	docker exec ${CONTAINER} composer tests

.PHONY: test-unit
test-unit: ## Run PHPUnit
	docker exec ${CONTAINER} composer test-unit

.PHONY: test-feature
test-feature: ## Run PHPUnit
	docker exec ${CONTAINER} composer test-feature

.PHONY: coverage
coverage: ## Export coverage
	docker exec ${CONTAINER} composer coverage

.PHONY: infection
infection: ## Run infection
	docker exec ${CONTAINER} composer infection

.PHONY: help
help: ## Display a list of targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
