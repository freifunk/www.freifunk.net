#SHELL=/bin/bash
CURRENT_DIR=$(basename $(pwd))
VERSION=$(grep 'Version' $(basename $(pwd)).php|sed -e 's/.*:\s//')

all: clean zip

clean:
	@rm *.zip

zip:
	@zip -qr $(value CURRENT_DIR)-$(value VERSION).zip * -x Makefile -x .* 
