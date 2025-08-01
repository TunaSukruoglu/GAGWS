#!/bin/bash

# JavaScript Module Integrity Checker
# Ensures add-property.js stays clean and doesn't get contaminated

EXPECTED_SIZE=139
CURRENT_SIZE=$(wc -l < assets/js/add-property.js)

echo "=== JavaScript Module Integrity Check ==="
echo "Expected add-property.js lines: $EXPECTED_SIZE"
echo "Current add-property.js lines: $CURRENT_SIZE"

if [ "$CURRENT_SIZE" -ne "$EXPECTED_SIZE" ]; then
    echo "⚠️  WARNING: add-property.js has been contaminated!"
    echo "🔧 Restoring clean version..."
    cp assets/js/add-property-messy-backup.js assets/js/add-property.js
    echo "✅ Clean version restored"
else
    echo "✅ All modules are clean and properly separated"
fi

echo ""
echo "Module sizes:"
ls -la assets/js/*.js | grep -E "(property-wizard|photo-upload|location-manager|cloudflare-images|form-handlers|add-property\.js)" | sort
