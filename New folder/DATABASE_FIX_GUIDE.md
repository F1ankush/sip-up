# Database Connection Fix Guide

## Problem Identified
**Error:** "Database connection error. Please try again later."

**Root Cause:** The database credentials in `config.php` were pointing to a non-existent MySQL user `b2b_billing_system`, which doesn't exist on your XAMPP installation.

---

## Solution Applied ✅

### Step 1: Updated Database Credentials
Changed `includes/config.php` to use the default XAMPP MySQL user:

**Before:**
```php
define('DB_USER', 'b2b_billing_system');
define('DB_PASS', 'Karan@1903');
```

**After:**
```php
define('DB_USER', 'root');
define('DB_PASS', '');
```

This uses the default XAMPP MySQL credentials (root user with no password).

---

## Step 2: Create Database and Tables

### Option A: Using the Setup Script (Recommended - Easiest)
1. Open your browser
2. Navigate to: `http://localhost/top1/setup_database.php`
3. The script will:
   - Create the `b2b_billing_system` database
   - Create all required tables with proper structure
   - Display success status

### Option B: Manual Setup via Command Line
```bash
cd c:\xampp
mysql -u root < "c:\xampp\htdocs\top1\database_schema.sql"
```

### Option C: Using phpMyAdmin
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click on "SQL" tab
3. Open `database_schema.sql` from `c:\xampp\htdocs\top1\`
4. Click "Go" to execute

---

## What Gets Created

The setup creates the following tables:

✅ **admins** - Admin user accounts
✅ **admin_sessions** - Admin login sessions
✅ **retailer_applications** - Retailer signup applications
✅ **users** - Approved retailer accounts
✅ **sessions** - User login sessions
✅ **products** - Product catalog
✅ **orders** - Customer orders
✅ **order_items** - Items in each order
✅ **payments** - Payment records
✅ **bills** - GST invoices

---

## Verification

After setup, verify everything works:

### Test 1: Check Database
```bash
mysql -u root -e "SHOW DATABASES LIKE 'b2b_billing_system';"
mysql -u root -e "USE b2b_billing_system; SHOW TABLES;"
```

### Test 2: Visit Application
- **Homepage:** http://localhost/top1/
- **Apply for Account:** http://localhost/top1/pages/apply.php
- **Admin Login:** http://localhost/top1/admin/login.php

---

## Next Steps

### 1. Create Admin Account
Visit: `http://localhost/top1/admin/setup.php`

This will allow you to:
- Set the admin setup key
- Create your first admin account

### 2. Add Products
- Login to admin panel
- Go to "Products Management"
- Add your products with prices and descriptions

### 3. Test the Flow
- Apply for retailer account
- Login as admin to approve
- Login as retailer to browse and order

---

## Troubleshooting

### Still Getting "Database Connection Error"?

**Check 1: Verify MySQL is Running**
```bash
cd c:\xampp
mysql -u root
```
If this fails, start MySQL from XAMPP Control Panel

**Check 2: Verify Database Exists**
```bash
mysql -u root -e "SHOW DATABASES;"
```
Look for `b2b_billing_system` in the list

**Check 3: Verify Tables Exist**
```bash
mysql -u root b2b_billing_system -e "SHOW TABLES;"
```
You should see 10 tables listed

**Check 4: Check Error Log**
Look at `c:\xampp\htdocs\top1\error_log.txt` for detailed error messages

---

## Alternative: Using Different Database Credentials

If you want to use a different MySQL user, you can:

### Create Custom MySQL User
```bash
mysql -u root -e "CREATE USER 'retailer'@'localhost' IDENTIFIED BY 'password123';"
mysql -u root -e "GRANT ALL PRIVILEGES ON b2b_billing_system.* TO 'retailer'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"
```

Then update `config.php`:
```php
define('DB_USER', 'retailer');
define('DB_PASS', 'password123');
```

---

## Configuration Files

### Database Config Location
📄 `c:\xampp\htdocs\top1\includes\config.php`

### Current Settings
- **Host:** localhost
- **User:** root
- **Password:** (empty)
- **Database:** b2b_billing_system

### Database Schema Location
📄 `c:\xampp\htdocs\top1\database_schema.sql`

---

## Security Notes

⚠️ **Development Only:**
The current setup uses `root` with no password - this is fine for local development but should NOT be used in production.

For production:
1. Create a dedicated database user with limited privileges
2. Use a strong password
3. Restrict user to only the required database
4. Store credentials securely (environment variables or .env file)

---

## Support

If you encounter any issues:

1. **Check error_log.txt:** `c:\xampp\htdocs\top1\error_log.txt`
2. **Check phpMyAdmin:** Verify database structure
3. **Restart Services:** Stop and start MySQL from XAMPP Control Panel
4. **Clear Browser Cache:** Hard refresh (Ctrl+Shift+Del)

---

**Status:** ✅ Database credentials updated and setup script created
**Last Updated:** December 29, 2025
