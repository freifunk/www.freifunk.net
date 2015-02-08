SHELL=/bin/bash
DIRS=$(ls -d */ | cut -f1 -d'/')

plugins:
	for dir in $(value DIRS); do make -C $$dir; done
