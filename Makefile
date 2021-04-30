.PHONY := help clean depend build
.DEFAULT_GOAL := help

COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## Displays this information
help:
	@printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	@printf " make [target]\n\n"
	@printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	@awk '/^[a-zA-Z\-0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

# SOURCE : http://stackoverflow.com/questions/10858261/abort-makefile-if-variable-not-set
# Check that given variables are set and all have non-empty values,
# die with an error otherwise.
#
# Params:
#   1. Variable name(s) to test.
#   2. (optional) Error message to print.
check_defined = \
	$(strip $(foreach 1,$1, \
		$(call __check_defined,$1,$(strip $(value 2)))))
__check_defined = \
	$(if $(value $1),, \
		$(error Undefined $1$(if $2, ($2))))

## Cleanup previously compiled files
clean:
	@rm -rf app/vendor
	@echo "Removed application dependencies"
	@rm -f patchtool.phar patchtool patchtool.phar.gz
	@echo "Removed build artifacts"

## Install dependencies via composer
depend:
	docker run --rm --interactive --tty --volume ${PWD}/app:/app composer install --ignore-platform-reqs

## Build phar file
build:
	@if [ ! -d app/vendor ]; then\
		echo "Missing vendor folder, run 'make depend'";\
		exit 1;\
	fi
	php -f build.php
	chmod +x patchtool.phar
