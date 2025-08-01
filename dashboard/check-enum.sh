#!/bin/bash

# Mevcut ENUM değerlerini kontrol et
echo "=== Checking ENUM values for usage_status ==="

# Önce hangi MySQL kullanıcıları var kontrol et
echo "Available MySQL commands:"
which mysql 2>/dev/null || echo "mysql not found"
which mariadb 2>/dev/null || echo "mariadb not found"

# Database bilgilerini al
if [ -f "../db.php" ]; then
    echo "Database config found. Extracting info..."
    grep -E "(host|username|password|database)" ../db.php | head -4
fi

echo "Manual check needed for ENUM values"
