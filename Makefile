########################################
#                                      #
# Wordpress Open edX plugin by eduNEXT #
#                                      #
########################################

.DEFAULT_GOAL := help

help: ## Display this help message
	@echo "Please use \`make <target>' where <target> is one of"
	@perl -nle'print $& if m{^[\.a-zA-Z_-]+:.*?## .*$$}' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m  %-25s\033[0m %s\n", $$1, $$2}'

release-zip:
	rm -rf edunext-openedx-integrator
	rm -f edunext-openedx-integrator.zip
	mkdir edunext-openedx-integrator
	cp LICENSE edunext-openedx-integrator/
	cp readme.txt edunext-openedx-integrator/
	cp *.php edunext-openedx-integrator/
	cp -r assets/ edunext-openedx-integrator/
	cp -r includes/ edunext-openedx-integrator/
	cp -r lang/ edunext-openedx-integrator/
	zip edunext-openedx-integrator.zip -r edunext-openedx-integrator
	rm -fr edunext-openedx-integrator
