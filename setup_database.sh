#!/bin/bash
# Database Setup Script for XAMPP
# Run this script to quickly set up your database

echo "========================================"
echo "B2B Retailer Platform - Database Setup"
echo "========================================"
echo ""

# Check if MySQL is running
echo "Checking MySQL connection..."
mysql -u root -e "SELECT 1" > /dev/null 2>&1

if [ $? -ne 0 ]; then
    echo "❌ ERROR: MySQL is not running!"
    echo "Please start MySQL from XAMPP Control Panel and try again."
    exit 1
fi

echo "✓ MySQL is running"
echo ""

# Check if database already exists
echo "Checking if database exists..."
mysql -u root -e "USE b2b_billing_system" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "ℹ Database already exists"
    echo ""
    read -p "Do you want to drop and recreate it? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Dropping existing database..."
        mysql -u root -e "DROP DATABASE b2b_billing_system;"
        echo "✓ Database dropped"
    else
        echo "Keeping existing database"
        exit 0
    fi
fi

# Create database and tables
echo "Creating database and tables..."
mysql -u root < "c:\xampp\htdocs\top1\database_schema.sql"

if [ $? -eq 0 ]; then
    echo "✓ Database setup completed successfully!"
    echo ""
    echo "Next steps:"
    echo "1. Open browser and go to: http://localhost/top1/admin/setup.php"
    echo "2. Create your admin account"
    echo "3. Login and start adding products"
    echo ""
else
    echo "❌ ERROR: Database setup failed!"
    exit 1
fi
