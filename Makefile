# Apigility Documentation Makefile
#
# Configurable variables:
# - PHP - PHP executable to use, if not in path
#
# Available targets:
# - modules - retrieve README.md files for all modules, process, and push into
#   tree
# - all     - currently, synonym for modules target

PHP ?= /usr/local/zend/bin/php

BIN = $(CURDIR)/bin

.PHONY : all modules

all : modules

modules:
	@echo "Fetching module README.md files..."
	- $(PHP) $(BIN)/fetch_module_readme_files.php
	@echo "[DONE] Fetching module README.md files"
	@echo "[DONE] Updating version"
