#!/bin/bash

# Start Postfix
service postfix start

# Start Dovecot
service dovecot start

# Keep the container running
tail -f /dev/null

