#!/usr/bin/env bash

openssl genrsa -out privkey.pem 2048
openssl rsa -in privkey.pem -pubout -out pubkey.pem
echo
echo --------------------------------------------------------
echo Remember not to commit any sensitive data.
echo Files with the following extensions are ingored by git:
echo .key, .pem, .crt, .cert, .pub
echo --------------------------------------------------------
