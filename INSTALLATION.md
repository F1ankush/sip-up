# Installation Guide - B2B Retailer Ordering and GST Billing Platform

## Prerequisites

Before you begin, ensure you have the following installed:

- **XAMPP** (Apache + MySQL + PHP 7.4+) or similar local server
- **MySQL Database Server** running
- **Web Browser** (Chrome, Firefox, Edge, Safari)
- **Text Editor** (VS Code, Notepad++)
- **Basic knowledge** of PHP and MySQL

## Step-by-Step Installation

### Step 1: Extract Project Files

1. Extract the `top1` folder to `C:\xampp\htdocs\`
2. Verify the structure:
   ```
   C:\xampp\htdocs\top1\
   ├── index.php
   ├── includes/
   ├── pages/
   ├── admin/
   ├── assets/
   ├── uploads/
   ├── database_schema.sql
   └── README.md
   ```

### Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Click "Admin" next to MySQL to open phpMyAdmin
4. Or navigate to: `http://localhost/phpmyadmin/`

### Step 3: Create Database

#### Method 1: Using phpMyAdmin

1. Go to `http://localhost/phpmyadmin/`
2. Click on **"New"** button in left sidebar
3. Database name: `b2b_billing_system`
4. Collation: `utf8mb4_unicode_ci`
5. Click **Create**
6. Select the new database
7. Go to **"Import"** tab
8. Choose `database_schema.sql` from the project folder
9. Click **Go** to import

#### Method 2: Using MySQL Command Line

```bash
mysql -u root -p
CREATE DATABASE b2b_billing_system;
USE b2b_billing_system;
SOURCE C:/xampp/htdocs/top1/database_schema.sql;
```

### Step 4: Configure Database Connection

1. Open `includes/config.php` in your editor
2. Update database credentials:

```php
define('DB_HOST', 'localhost');     // Keep as is
define('DB_USER', 'root');          // Default XAMPP user
define('DB_PASS', '');              // Default is empty
define('DB_NAME', 'b2b_billing_system'); // Database name
```

3. Update company details:

```php
define('COMPANY_NAME', 'Premium Retail Distribution');
define('COMPANY_GST', '27AABCU1234B2Z5');
define('COMPANY_PHONE', '+91 9876543210');
define('COMPANY_EMAIL', 'support@retailerplatform.com');
define('COMPANY_ADDRESS', 'Bangalore, Karnataka, India');
define('SITE_URL', 'http://localhost/top1/');
```

4. Set admin setup key:

```php
define('ADMIN_SETUP_KEY', 'SETUP_KEY_2025_SECURE');
```

**Keep this key safe! You'll need it to create the first admin account.**

### Step 5: Set File Permissions

For Windows XAMPP (usually not needed), but for Linux/Mac:

```bash
chmod 755 uploads/
chmod 755 uploads/payment_proofs/
chmod 755 uploads/bills/
chmod 644 includes/*.php
```

### Step 6: Create Admin Account

1. Navigate to: `http://localhost/top1/admin/setup.php`
2. Fill in the form:
   - **Admin Username**: Enter a username (e.g., `admin`)
   - **Admin Email**: Enter email (e.g., `admin@example.com`)
   - **Password**: Create a strong password (min 8 characters)
   - **Confirm Password**: Repeat the password
   - **Setup Key**: Enter the key from config.php (`SETUP_KEY_2025_SECURE`)
3. Click **"Create Admin Account"**
4. You'll see a success message
5. Click **"Go to Login"**

### Step 7: Admin Login

1. Navigate to: `http://localhost/top1/admin/login.php`
2. Login credentials:
   - **Username**: The username you created
   - **Password**: The password you created
3. Click **Login**
4. You're now in the Admin Dashboard

### Step 8: Add Sample Products (Admin)

1. Click **"Products"** in the navigation menu
2. Click **"+ Add New Product"**
3. Fill in product details:
   - **Product Name**: (e.g., "Widget A")
   - **Description**: (e.g., "High quality widget")
   - **Price**: (e.g., 100)
   - **Stock Quantity**: (e.g., 50)
4. Click **"Add Product"**
5. Repeat for multiple products

### Step 9: Test Retailer Registration

1. Go to: `http://localhost/top1/pages/apply.php`
2. Fill in the registration form:
   - **Name**: Your test name
   - **Email**: test@example.com
   - **Phone**: 9876543210 (10-digit number)
   - **Shop Address**: Your address
   - **Agree to terms**: Check the checkbox
3. Click **"Submit Application"**
4. You'll see a success message

### Step 10: Approve Application (Admin)

1. Login as admin at: `http://localhost/top1/admin/login.php`
2. Click **"Applications"** menu
3. Find the test application
4. Click **"Review"**
5. Click **"Approve Application"**
6. Set a password for the retailer account
7. Click **"Approve"**

### Step 11: Create Retailer Login Credentials (Admin)

1. In Applications list, click on the application
2. Click **"Create Password"** button
3. Enter a temporary password (min 8 characters)
4. Confirm the password
5. Click **"Create Credentials"**
6. The retailer can now login with the email and password

### Step 12: Login as Retailer

1. Go to: `http://localhost/top1/pages/login.php`
2. Login credentials:
   - **Email**: test@example.com (from application)
   - **Password**: Password you set
3. Click **Login**
4. You're now in the Retailer Dashboard

### Step 13: Test Order Flow

1. In the dashboard, select products:
   - Enter quantity
   - Click **"Add to Cart"**
2. Click **"Cart"** menu
3. Review items and total price
4. Click **"Proceed to Checkout"**
5. Select payment method:
   - **COD**: Direct order placed
   - **UPI**: Generate QR code, upload proof
6. Complete checkout

### Step 14: Verify Payment (Admin)

1. Login as admin
2. Click **"Payments"** menu
3. Find pending payment
4. Click **"Verify"**
5. Review payment proof
6. Click **"Approve Payment"** or **"Reject Payment"**

### Step 15: Generate Bill (Admin)

1. Click **"Orders"** menu
2. Find the completed order (payment approved)
3. Click **"Generate Bill"**
4. Review bill details
5. Click **"Generate"**
6. Bill is now available for download

### Step 16: Download Bill (Retailer)

1. Login as retailer
2. Click **"Bills"** menu
3. Find your bill
4. Click **"Download"**
5. Bill PDF is generated and downloaded

## Troubleshooting

### Database Connection Failed

**Problem**: "Database Connection Failed: Connection refused"

**Solution**:
1. Ensure MySQL is running in XAMPP
2. Check database credentials in `config.php`
3. Verify database name matches
4. Restart Apache and MySQL

### Blank Page or 500 Error

**Problem**: White blank page or server error

**Solution**:
1. Check error log: `php_error_log` file
2. Ensure PHP version is 7.4+
3. Check if all required files are present
4. Verify file permissions
5. Clear browser cache

### Upload Directory Errors

**Problem**: "Cannot create directory" or file upload fails

**Solution**:
1. Create directories manually:
   - `uploads/`
   - `uploads/payment_proofs/`
   - `uploads/bills/`
2. Set write permissions (Windows usually allows by default)
3. Check disk space availability

### Session/Login Issues

**Problem**: Logged out immediately after login

**Solution**:
1. Check PHP session settings
2. Verify cookies are enabled in browser
3. Clear browser cookies for localhost
4. Check session save path exists

### CSS/JavaScript Not Loading

**Problem**: Page looks broken, no styling or interactivity

**Solution**:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Check file paths in HTML
3. Verify `assets` folder exists with `css` and `js` subfolders
4. Check browser console for 404 errors (F12)

## Configuration Tips

### Change Site URL (If not localhost)

Update in `config.php`:
```php
define('SITE_URL', 'http://yourdomain.com/top1/');
```

### Change GST Rate

In `includes/functions.php`, find `calculateGST()` function:
```php
function calculateGST($amount, $gstRate = 18) // Change 18 to desired rate
```

### Change Session Timeout

In `config.php`:
```php
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds (3600 = 1 hour)
// Change to desired timeout: 1800 = 30 min, 7200 = 2 hours
```

### Change File Upload Size

In `config.php`:
```php
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
// 1MB = 1048576, 10MB = 10485760
```

## Security Recommendations

1. **Change Admin Setup Key**: Update `ADMIN_SETUP_KEY` in config.php
2. **Change Database Credentials**: Set strong password for MySQL user
3. **Use HTTPS**: Deploy on production with SSL certificate
4. **Disable Setup Page**: Remove setup.php after initial setup
5. **Regular Backups**: Backup database regularly
6. **Update PHP**: Keep PHP version updated
7. **Use Strong Passwords**: Enforce strong password policy
8. **CORS Headers**: Configure if accessing from different domain

## Production Deployment Checklist

- [ ] Database backups configured
- [ ] HTTPS/SSL certificate installed
- [ ] Database password changed
- [ ] Admin setup page disabled/deleted
- [ ] Error logging configured (not displayed)
- [ ] File upload limits set appropriately
- [ ] Regular security updates applied
- [ ] Session security (HttpOnly, Secure flags)
- [ ] Input validation thoroughly tested
- [ ] Payment gateway integration complete
- [ ] Email notifications configured
- [ ] Monitoring and logging enabled

## Support Resources

- **PHP Documentation**: https://www.php.net/manual/
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **XAMPP Help**: https://www.apachefriends.org/
- **MDN Web Docs**: https://developer.mozilla.org/

## Next Steps

1. Customize branding and colors
2. Add your company logo
3. Configure email notifications
4. Integrate with payment gateway (optional)
5. Set up SSL certificate
6. Deploy to production server
7. Create user documentation
8. Train admin and retailer users

## Contact & Support

For issues or questions:
- **Project Folder**: `C:\xampp\htdocs\top1\`
- **Database**: `b2b_billing_system`
- **Admin URL**: `http://localhost/top1/admin/`
- **Main URL**: `http://localhost/top1/`

---

**Installation Status**: Complete ✓

Your B2B Retailer Ordering Platform is now ready to use!

Enjoy! 🚀
