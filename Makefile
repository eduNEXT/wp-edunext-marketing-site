########################################
#                                      #
# Wordpress Open edX plugin by eduNEXT #
#                                      #
########################################

.DEFAULT_GOAL := help

help: ## Display this help message
	@echo "Please use \`make <target>' where <target> is one of"
	@perl -nle'print $& if m{^[\.a-zA-Z_-]+:.*?## .*$$}' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m  %-25s\033[0m %s\n", $$1, $$2}'

build: tidy verify-sem-version ## Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
	python scripts/plugin.py -b

change-to-pro: ## Change development environment to pro version.
	python scripts/plugin.py -c pro

change-to-lite: ## Change development environment to lite version.
	python scripts/plugin.py -c lite

dev-version: ## Get current development environment version
	python scripts/plugin.py -d

tidy: ## Remove build directories
	python scripts/plugin.py -t

clean-env: ## Clean development environment, delete symlinks that change in every version
	python scripts/plugin.py -e

verify-sem-version: ## Verify the semantic version across versioned files
	python scripts/plugin.py -s

release: tidy verify-sem-version build ## Release a new version of the plugin to the WordPress repository.
	./scripts/release.sh
