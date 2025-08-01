#!/bin/bash
# 🚀 SIMPLE PERFORMANCE TEST

echo "🚀 DASHBOARD PERFORMANCE TEST"
echo "============================="
echo ""

echo "📊 File Sizes:"
echo "Minimal Dashboard: $(ls -lh dashboard-minimal.php | awk '{print $5}')"
echo "Full Dashboard: $(ls -lh dashboard-admin.php | awk '{print $5}')"
echo ""

echo "⚡ PHP Execution Speed:"
echo -n "Minimal Dashboard: "
time php -r "include 'dashboard-minimal.php';" 2>&1 | grep real | awk '{print $2}'

echo -n "Full Dashboard: "
time php -r "include 'dashboard-admin.php';" 2>&1 | grep real | awk '{print $2}'

echo ""
echo "🎯 Results logged to performance.log"

# Create access links
echo ""
echo "🔗 Test URLs:"
echo "Minimal: https://gokhanaydinli.com/dashboard/dashboard-minimal.php"
echo "Full: https://gokhanaydinli.com/dashboard/dashboard-admin.php"
