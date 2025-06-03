#!/bin/bash

# Set variables
DB_NAME="taravel_docker"
DB_USER="root"
DB_PASS="melodic"
BACKUP_DIR="/var/backups/mysql"
DATE=$(date +\%F_\%H-\%M-\%S)

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Dump the database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/${DB_NAME}_$DATE.sql

# Optional: Delete backups older than 7 days
find $BACKUP_DIR -type f -name "*.sql" -mtime +7 -delete