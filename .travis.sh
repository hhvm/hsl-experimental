#!/bin/sh
set -ex
hhvm --version

composer install

hh_client

hhvm vendor/bin/hacktest tests/
