# B2B Retailer Platform - Complete Setup Guide

## ✅ Overview

This is a complete, production-ready B2B retailer ordering and GST billing platform built with:
- **Backend**: Core PHP 7.4+ with MySQLi
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Security**: Password hashing, prepared statements, CSRF protection

---

## 📋 Prerequisites

Before starting, ensure you have:

1. **XAMPP** installed (Apache + MySQL + PHP)
   - Download from: https://www.apachefriends.org/
   - PHP 7.4 or higher

2. **MySQL Server** running
   - Default: localhost, root user

3. **Project Folder**
   - Location: `C:\xampp\htdocs\top1\`

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Configure Database Credentials

1. Open: `C:\xampp\htdocs\top1\includes\config.php`

2. Update these values:
```php
define('DB_HOST', 'localhost');      // MySQL host
define('DB_USER', 'root');           // MySQL username
define('DB_PASS', '');               // MySQL password (if any)
define('DB_NAME', 'b2b_billing_system');  // Database name
```

3. **Optional**: Update company information:
```php
define('COMPANY_NAME', 'Your Company Name');
define('COMPANY_GST', 'Your_GST_Number');
define('COMPANY_PHONE', 'Your Phone');
define('COMPANY_EMAIL', 'your.email@company.com');
define('COMPANY_ADDRESS', 'Your Address');
```

4. **Change security key**:
```php
define('ADMIN_SETUP_KEY', 'Change_This_To_Something_Secure');
```

### Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Start **Apache** server
3. Start **MySQL** server

### Step 3: Create Database

1. Open MySQL through XAMPP phpMyAdmin:
   - URL: `http://localhost/phpmyadmin/`

2. Create new database:
   - Name: `b2b_billing_system`
   - Charset: utf8mb4

### Step 4: Import Database Schema

1. In phpMyAdmin, select your new database
2. Click **Import** tab
3. Browse and select: `C:\xampp\htdocs\top1\database_schema.sql`
4. Click **Go** to import all tables

### Step 5: Create Admin Account

1. Open browser: `http://localhost/top1/admin/setup.php`

2. Enter:
   - Setup Key: (from config.php `ADMIN_SETUP_KEY`)
   - Admin Username: `admin`
   - Admin Email: `admin@company.com`
   - Admin Password: `strong_password_here`
   - Confirm Password: (repeat above)

3. Click **Create Admin Account**

### Step 6: Test the System

1. **Public Pages**: `http://localhost/top1/`
   - Home, About, Products, Contact, Apply

2. **Retailer Login**: `http://localhost/top1/pages/login.php`
   - (Will work after applying for account and admin approval)

3. **Admin Login**: `http://localhost/top1/admin/login.php`
   - Username: `admin`
   - Password: (from step 5)

---

## 📊 Complete Setup Workflow

### 1. Add Sample Products (As Admin)

1. Login to admin: `http://localhost/top1/admin/login.php`
2. Go to **Products** menu
3. Click **+ Add New Product**
4. Fill in product details:
   - Name: "Product Name"
   - Description: "Product description"
   - Price: "100.00"
   - Stock: "50"
   - Image: (optional)
5. Click **Add Product**

### 2. Test Retailer Application

1. Go to: `http://localhost/top1/pages/apply.php`
2. Fill application form:
   - Full Name: "Test Retailer"
   - Email: "retailer@test.com"
   - Phone: "9876543210"
   - Address: "123 Main St, City, State"
   - Agree to terms: ✓
3. Click **Submit Application**

### 3. Approve Retailer Account (As Admin)

1. Login to admin
2. Go to **Applications** menu
3. Find the application just submitted
4. Click **Review** button
5. Fill approval form and click **Confirm Approval**
6. User account is automatically created

### 4. Test Retailer Dashboard

1. Go to: `http://localhost/top1/pages/login.php`
2. Use retailer's email: `retailer@test.com`
3. Password: (temporary password sent to email, or set in admin)
4. Retailer can now:
   - Browse products
   - Add to cart
   - Place orders
   - View orders history
   - Download bills

---

## 🔑 Default Accounts

| Type | Email/Username | Default Password | Status |
|------|---|---|---|
| Admin | admin | (set during setup) | Must create first |
| Retailer | Via application | Auto-generated | After approval |

---

## 📁 Directory Structure

```
top1/
├── includes/
│   ├── config.php          ← Edit database credentials here
│   ├── db.php              ← Database class
│   └── functions.php       ← Helper functions
├── assets/
│   ├── css/style.css       ← All styling
│   └── js/main.js          ← All JavaScript
├── pages/
│   ├── login.php           ← Retailer login
│   ├── dashboard.php       ← Retailer dashboard
│   ├── orders.php          ← Order history
│   ├── bills.php           ← Bill management
│   ├── apply.php           ← Account application
│   ├── products.php        ← Product catalog
│   ├── about.php           ← About page
│   ├── contact.php         ← Contact page
│   └── logout.php          ← Logout
├── admin/
│   ├── setup.php           ← Create first admin (one-time)
│   ├── login.php           ← Admin login
│   ├── dashboard.php       ← Admin dashboard
│   ├── applications.php    ← Manage applications
│   ├── application_detail.php ← Approve/reject apps
│   ├── products.php        ← Manage products
│   ├── add_product.php     ← Add new product
│   ├── edit_product.php    ← Edit product
│   ├── delete_product.php  ← Delete product
│   ├── orders.php          ← Manage orders
│   ├── payments.php        ← Verify payments
│   ├── bills.php           ← View bills
│   └── logout.php          ← Logout
├── uploads/
│   ├── products/           ← Product images
│   ├── payment_proofs/     ← Payment evidence
│   └── bills/              ← Generated invoices
├── database_schema.sql     ← Database structure
├── index.php               ← Home page
└── config.php              ← Main configuration

```

---

## 🔐 Security Features Implemented

✅ **Password Security**
- Bcrypt hashing with password_hash()
- Secure comparison with password_verify()

✅ **SQL Injection Prevention**
- MySQLi prepared statements
- Parameter binding with bind_param()

✅ **CSRF Protection**
- Token generation and validation
- Session-based tokens

✅ **Session Management**
- Secure session hashing
- Single-login enforcement
- Automatic session invalidation

✅ **Input Validation**
- Email validation
- Phone number validation (10-digit Indian format)
- File upload validation (MIME type, size, extension)

✅ **Data Encryption**
- Password hashing for all users
- Secure transmission (use HTTPS in production)

---

## 🛠️ Key Configuration Values

### In `config.php`

| Setting | Default | Purpose |
|---------|---------|---------|
| SESSION_TIMEOUT | 3600 | Session duration (seconds) |
| MAX_FILE_SIZE | 5242880 | Max file upload (5MB) |
| ADMIN_SETUP_KEY | Custom | Secure admin creation |
| ITEMS_PER_PAGE | 10 | Pagination limit |

---

## ⚠️ Important Notes

### Before Going Live

1. **Change all default passwords**
   - Admin password
   - Database password

2. **Update company information**
   - Company name
   - GST number
   - Contact details

3. **Enable HTTPS**
   - Use SSL certificate
   - Redirect HTTP to HTTPS

4. **Set proper file permissions**
   - uploads/ folder: 755
   - files in uploads/: 644

5. **Create regular backups**
   - Database backups
   - File backups

6. **Review security settings**
   - SESSION_TIMEOUT
   - ALLOWED_FILE_TYPES
   - ALLOWED_EXTENSIONS

---

## 🐛 Troubleshooting

### Database Connection Error
```
Error: "Could not connect to database"
```
**Solution:**
- Check DB_HOST, DB_USER, DB_PASS in config.php
- Ensure MySQL is running
- Verify database name exists

### File Upload Fails
```
Error: "Failed to upload image file"
```
**Solution:**
- Check uploads/ folder permissions (755)
- Verify file size < 5MB
- Check allowed file types (JPG, PNG)

### Session/Login Issues
```
Error: "You are not logged in"
```
**Solution:**
- Clear browser cookies
- Check SESSION_TIMEOUT setting
- Verify database sessions table

### White Page
```
Blank page with no content
```
**Solution:**
- Check PHP error logs
- Enable display_errors (if not production)
- Verify all includes are correct

---

## 📧 Email Configuration

Currently, the system is configured for COD and UPI payments. To add email functionality:

1. Update config.php with email settings
2. Use PHPMailer or similar for sending emails
3. Send credentials to retailers after approval

---

## 📱 Mobile Responsiveness

The system is fully responsive with breakpoints at:
- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: 480px - 767px
- **Small Mobile**: < 480px

All pages work perfectly on all devices.

---

## 🚀 Going Live

### Checklist for Production

- [ ] Update all configuration values
- [ ] Change admin credentials
- [ ] Enable HTTPS/SSL
- [ ] Set proper file permissions
- [ ] Configure email system
- [ ] Test all features
- [ ] Create database backup
- [ ] Set up monitoring
- [ ] Enable error logging
- [ ] Document your changes

### Recommended Hosts

- AWS
- DigitalOcean
- Bluehost
- SiteGround
- Any host with PHP 7.4+ and MySQL 5.7+

---

## 📞 Support

For issues or questions:
1. Check the INSTALLATION.md file
2. Review comments in code files
3. Check error logs in error_log.txt
4. Verify database structure in database_schema.sql

---

## 📄 License

This project is provided as-is for your use.

---

## ✨ Next Steps

1. ✅ Complete setup (5 minutes)
2. ✅ Add sample products (2 minutes)
3. ✅ Test retailer workflow (5 minutes)
4. ✅ Configure for your business (ongoing)
5. ✅ Deploy to production (varies)

**Total Setup Time: ~15-30 minutes**

---

**Status**: ✅ Ready to Use  
**Version**: 1.0.0  
**Created**: December 2025

