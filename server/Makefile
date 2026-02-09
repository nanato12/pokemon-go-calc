.PHONY: generate-client lint fix

generate-client:
	docker run --rm -v $(PWD)/..:/local openapitools/openapi-generator-cli:latest generate \
		-i /local/docs/openapi.yaml \
		-g php \
		-o /local/server/generated/iv-extractor-client \
		--additional-properties=invokerPackage=IvExtractorClient

lint:
	./vendor/bin/phpstan analyse --memory-limit=512M
	./vendor/bin/php-cs-fixer fix --dry-run --diff

fix:
	./vendor/bin/php-cs-fixer fix
