# B2B Retailer Ordering and GST Billing Platform

A complete, production-ready B2B e-commerce platform built with Core PHP, MySQL, HTML, CSS, and JavaScript. This platform allows approved retailers to place orders online with automatic GST-compliant billing.

## Features

### 🏪 Public Pages
- **Home Page**: Featured products carousel, company information
- **About**: Company mission, vision, and values
- **Products**: Browse available products (login required to order)
- **Contact**: Contact form and company information
- **Apply for Account**: Retailer application form with validation

### 🛒 Retailer Dashboard
- **Dashboard**: Product catalog with add-to-cart functionality
- **Orders**: View order history and status
- **Bills**: Download GST-compliant invoices
- **Cart**: Manage shopping cart and checkout

### 💳 Payment System
- **Cash on Delivery (COD)**: Simple payment method
- **UPI Payment**: Generate dynamic UPI QR codes
- **Payment Proof Upload**: Upload payment proofs for verification
- **Payment Verification**: Admin verification workflow

### 🧾 GST Billing
- **Automatic Bill Generation**: Create GST-compliant bills
- **Bill Retrieval**: Download and view bills
- **Invoice History**: Complete billing records

### ⚙️ Admin Panel
- **Account Approval**: Review and approve retailer applications
- **Product Management**: Add, edit, delete products
- **Inventory Management**: Track stock levels
- **Payment Verification**: Review and approve payments
- **Bill Generation**: Create and manage invoices
- **User Management**: Manage retailer accounts
- **Analytics Dashboard**: View platform statistics

## System Requirements

- **Server**: Apache 2.4+
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7 or higher
- **Browser**: Modern browser with JavaScript enabled

## Installation Guide

### Step 1: Database Setup

1. Open phpMyAdmin or your MySQL client
2. Import the database schema:
   ```
   Import: database_schema.sql
   ```
3. The following tables will be created:
   - admins
   - admin_sessions
   - retailer_applications
   - users
   - sessions
   - products
   - orders
   - order_items
   - payments
   - bills

### Step 2: Configuration

1. Edit `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'b2b_billing_system');
   ```

2. Update company details:
   ```php
   define('COMPANY_NAME', 'Your Company Name');
   define('COMPANY_GST', 'Your GST Number');
   define('COMPANY_PHONE', 'Your Phone Number');
   define('COMPANY_EMAIL', 'Your Email');
   define('COMPANY_ADDRESS', 'Your Address');
   ```

3. Set the admin setup key:
   ```php
   define('ADMIN_SETUP_KEY', 'Your_Secure_Setup_Key');
   ```

### Step 3: File Permissions

Ensure write permissions for upload directories:
```bash
chmod 755 uploads/
chmod 755 uploads/payment_proofs/
chmod 755 uploads/bills/
```

### Step 4: Create Admin Account

1. Navigate to: `http://localhost/top1/admin/setup.php`
2. Enter the setup key from `config.php`
3. Create your admin account
4. Login at: `http://localhost/top1/admin/login.php`

## User Workflows

### Retailer Registration & Approval Workflow

1. **Application**: Retailer fills out registration form
2. **Submission**: Application stored with "pending" status
3. **Admin Review**: Admin reviews applications
4. **Approval**: Admin approves and creates login credentials
5. **Access**: Retailer receives credentials and can login
6. **Dashboard**: Retailer accesses product catalog

### Order & Payment Workflow

1. **Browse**: Retailer selects products from catalog
2. **Cart**: Items added to shopping cart
3. **Checkout**: Select payment method (COD or UPI)
4. **Payment**: 
   - COD: Order placed immediately
   - UPI: Payment details displayed with QR code
5. **Proof Upload**: For UPI, upload payment proof
6. **Verification**: Admin verifies payment
7. **Confirmation**: Order status updated
8. **Bill**: Admin generates GST bill

### Bill Generation Workflow

1. **Order Completion**: Payment verified
2. **Bill Creation**: Admin generates bill
3. **Storage**: Bill saved in database
4. **Retrieval**: Retailer downloads from Bills section
5. **Archive**: Complete records maintained

## File Structure

```
top1/
├── index.php                          # Home page
├── database_schema.sql                # Database schema
├── includes/
│   ├── config.php                     # Configuration
│   ├── db.php                         # Database class
│   └── functions.php                  # Utility functions
├── assets/
│   ├── css/
│   │   └── style.css                  # Responsive styling
│   ├── js/
│   │   └── main.js                    # JavaScript functions
│   └── images/                        # Product images
├── pages/                             # Public & Retailer pages
│   ├── about.php
│   ├── products.php
│   ├── contact.php
│   ├── apply.php                      # Registration
│   ├── login.php                      # Retailer login
│   ├── dashboard.php                  # Retailer dashboard
│   ├── orders.php
│   ├── bills.php
│   └── logout.php
├── admin/                             # Admin pages
│   ├── setup.php                      # Admin account creation
│   ├── login.php                      # Admin login
│   ├── dashboard.php                  # Admin dashboard
│   ├── applications.php               # Manage applications
│   ├── products.php                   # Product management
│   ├── orders.php
│   ├── payments.php                   # Payment verification
│   ├── bills.php
│   └── logout.php
└── uploads/
    ├── payment_proofs/                # Payment proof images
    └── bills/                         # Generated bills
```

## Security Features

✅ **Password Hashing**: Using bcrypt algorithm
✅ **SQL Injection Prevention**: Prepared statements
✅ **CSRF Protection**: Token-based validation
✅ **Session Management**: Secure session handling
✅ **File Upload Validation**: MIME type and extension checks
✅ **Input Sanitization**: All user inputs validated
✅ **Single Login**: Only one active session per user
✅ **Role-Based Access**: Separate admin and retailer areas
✅ **Password Requirements**: Minimum 8 characters

## API Functions (includes/functions.php)

### Authentication
- `isLoggedIn()`: Check if user logged in
- `isAdminLoggedIn()`: Check if admin logged in
- `validateSession($userId)`: Validate user session
- `createUserSession($userId)`: Create new user session
- `createAdminSession($adminId)`: Create admin session

### Validation
- `validateEmail($email)`: Email validation
- `validatePhone($phone)`: Phone validation (10-digit Indian)
- `validatePassword($password)`: Password length check
- `validateFileUpload($file)`: File upload validation
- `validateForm($form)`: Form validation

### Database
- `getProducts()`: Get active products
- `getProduct($id)`: Get single product
- `getUserData($userId)`: Get user details
- `getOrders($userId)`: Get user orders
- `getOrder($orderId)`: Get order details

### Utilities
- `formatCurrency($amount)`: Format amount as rupees
- `calculateGST($amount, $rate)`: Calculate GST
- `generateUPIQRCode($amount, $orderId)`: Generate QR code
- `sanitize($data)`: Sanitize input

## Product Management

### Adding Products (Admin)

Products can be added through the admin dashboard with:
- Product name
- Description
- Price (in rupees)
- Stock quantity
- Product image

### Product Visibility

- Products shown to retailers only when logged in
- Out of stock products display status but cannot be ordered
- Product prices shown in Indian Rupees (₹)

## Payment Methods

### Cash on Delivery (COD)
- Simple payment method
- No additional documentation required
- Order confirmation immediately

### UPI Payment
- Dynamic UPI QR code generation
- UPI ID display for manual payment
- Payment proof image upload required
- Accepted formats: JPG, PNG (Max 5MB)
- Admin verification workflow

## GST Compliance

- **GST Number**: Configured in settings
- **Bill Format**: A4 size, standardized layout
- **Tax Calculation**: 18% GST (configurable)
- **Bill Components**:
  - Company details
  - Retailer information
  - Itemized product list
  - Price breakdown
  - GST amount
  - Total payable

## Color Scheme

The platform uses a modern flat design with these primary colors:

- **Primary**: #2563eb (Blue)
- **Secondary**: #1e40af (Dark Blue)
- **Success**: #10b981 (Green)
- **Danger**: #ef4444 (Red)
- **Warning**: #f59e0b (Orange)
- **Info**: #3b82f6 (Light Blue)

## Responsive Design

- **Desktop**: Full-featured navigation bar
- **Tablet**: Adjusted layout and typography
- **Mobile**: Hamburger menu, touch-optimized buttons
- **Breakpoints**: 768px and 480px

## Email Configuration

Currently, the system doesn't send emails. To add email notifications:

1. Add PHPMailer library
2. Update `contact_handler.php` for contact emails
3. Add application approval notifications
4. Add payment verification emails

## Troubleshooting

### Database Connection Error
- Check database credentials in `config.php`
- Ensure MySQL server is running
- Verify database name exists

### File Upload Issues
- Check directory permissions (755)
- Verify file size limits
- Ensure MIME type validation passes

### Session Issues
- Clear browser cookies
- Check PHP session settings
- Verify session directory exists

### CSS/JS Not Loading
- Clear browser cache
- Check file paths in HTML
- Verify asset directories exist

## Future Enhancements

1. **Email Notifications**: Automated email alerts
2. **SMS Alerts**: Payment and order notifications
3. **Advanced Analytics**: Sales reports and charts
4. **Bulk Orders**: CSV import for large orders
5. **Payment Gateway**: Razorpay/PayU integration
6. **Multi-Admin**: Role-based admin access
7. **Audit Logs**: Complete activity tracking
8. **Mobile App**: Native mobile application

## Support & Contact

For support, contact:
- **Email**: <?php echo COMPANY_EMAIL; ?>
- **Phone**: <?php echo COMPANY_PHONE; ?>
- **Address**: <?php echo COMPANY_ADDRESS; ?>

## License

This project is built for B2B retail distribution. All rights reserved.

## Version

**Version**: 1.0.0  
**Last Updated**: December 2025  
**Status**: Production Ready

---

**Developed with focus on:**
- Security and data protection
- User experience and usability
- Scalability and performance
- GST compliance
- Mobile responsiveness

Happy Selling! 🚀
