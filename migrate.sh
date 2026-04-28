#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

echo "=========================================="
echo "KapeLagi Migration Setup"
echo "=========================================="
echo

echo "Installing Composer dependencies..."
if [[ -f composer.json ]]; then
    composer install
else
    echo "composer.json not found."
    exit 1
fi

echo
echo "Running database setup..."
php config/setup_db.php

echo
echo "Migration setup complete."
echo "If you moved this project to another device, update config/db.php with the new database credentials before running the site."
