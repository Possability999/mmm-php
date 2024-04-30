# Use Debian as the base image
FROM debian:buster-slim

# Set debconf frontend to noninteractive
ENV DEBIAN_FRONTEND noninteractive

# Install Postfix, Dovecot, and MariaDB client along with necessary tools
RUN apt-get update && apt-get install -y \
    postfix \
    dovecot-core \
    dovecot-imapd \
    dovecot-lmtpd \
    dovecot-mysql \
    rsyslog \
    && rm -rf /var/lib/apt/lists/*

# Configure Postfix
COPY postfix/main.cf /etc/postfix/main.cf
COPY postfix/master.cf /etc/postfix/master.cf

# Preseed Postfix configuration options
RUN echo "postfix postfix/main_mailer_type select Internet Site" | debconf-set-selections \
    && echo "postfix postfix/mailname string your.hostname.com" | debconf-set-selections

# Configure Dovecot
COPY dovecot/*.conf /etc/dovecot/
COPY dovecot/conf.d/* /etc/dovecot/conf.d/
ADD letsencrypt.tar.gz /etc/
RUN groupadd -g 5000 vmail
RUN useradd -g vmail -u 5000 vmail -d /var/vmail -m -s /bin/false

# Setup entrypoint and command
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose SMTP, IMAP ports
EXPOSE 25 143 587 993

ENTRYPOINT ["/entrypoint.sh"]
CMD ["start-services"]

