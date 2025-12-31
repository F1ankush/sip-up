# 🧪 TESTING GUIDE - B2B Platform

## Overview

This guide will help you thoroughly test all features of the B2B retailer platform.

---

## Part 1: Pre-Test Setup (10 minutes)

### Step 1: Verify System
1. Open: `http://localhost/top1/verify.php`
2. Check that all items show ✓ Pass
3. If any items show ✗ Fail, fix them first

### Step 2: Start XAMPP
1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL

### Step 3: Clear Previous Test Data (Optional)
1. Open: `http://localhost/phpmyadmin/`
2. Select database: `b2b_billing_system`
3. Empty tables if you want fresh test data
4. Or keep existing data to test consistency

---

## Part 2: Admin Setup Testing (3 minutes)

### Test: Admin Account Creation

**URL**: `http://localhost/top1/admin/setup.php`

**Steps**:
1. Open the URL
2. Check page loads with form
3. Try submitting without setup key → Should show "Invalid setup key"
4. Enter correct setup key (from config.php ADMIN_SETUP_KEY)
5. Fill in:
   - Username: `testadmin`
   - Email: `admin@test.com`
   - Password: `TestAdmin@123`
   - Confirm: `TestAdmin@123`
6. Click Create Admin Account
7. Should see success message
8. Should redirect to admin login

**Expected Result**: ✅ Admin account created successfully

---

## Part 3: Admin Login Testing (2 minutes)

### Test: Admin Authentication

**URL**: `http://localhost/top1/admin/login.php`

**Steps**:
1. Enter invalid credentials → Should show error
2. Leave fields empty → Should show error
3. Enter correct credentials:
   - Username: `testadmin`
   - Password: `TestAdmin@123`
4. Click Login
5. Should redirect to admin dashboard
6. Should see admin name in navbar

**Expected Result**: ✅ Login successful, dashboard accessible

---

## Part 4: Product Management Testing (15 minutes)

### Test 4.1: Add Product

**URL**: `http://localhost/top1/admin/products.php` → Click "Add New Product"

**Steps**:
1. Form should load with fields for:
   - Product Name
   - Description
   - Price
   - Quantity
   - Image (optional)

2. **Test Validation**:
   - Submit empty form → Should show errors
   - Name < 2 chars → Should show error
   - Price = 0 → Should show error
   - Price = -5 → Should show error
   - Quantity = -1 → Should show error

3. **Add Valid Product**:
   - Name: `Test Product 1`
   - Description: `This is a test product with detailed description`
   - Price: `99.99`
   - Quantity: `50`
   - Image: (optional, or upload test image)
   - Click "Add Product"

4. **Verify**:
   - See success message
   - Redirect to products list
   - Product appears in table
   - Price shows as ₹99.99

**Expected Result**: ✅ Product added successfully

### Test 4.2: Add Second Product

**Repeat Test 4.1** with:
- Name: `Premium Package`
- Price: `249.50`
- Quantity: `25`

**Expected Result**: ✅ Both products in list

### Test 4.3: Edit Product

**Steps**:
1. In products list, click "Edit" on a product
2. Change values:
   - Name: `Updated Product Name`
   - Price: `149.99`
   - Quantity: `100`
3. Click "Update Product"
4. Verify changes in product list

**Expected Result**: ✅ Product updated successfully

### Test 4.4: Delete Product

**Steps**:
1. In products list, click "Delete" on a product
2. Confirm deletion
3. Product should disappear from list
4. Should see success message

**Expected Result**: ✅ Product deleted (soft delete)

---

## Part 5: Retailer Application Testing (10 minutes)

### Test 5.1: Apply for Account

**URL**: `http://localhost/top1/pages/apply.php`

**Steps**:
1. Form should show with fields:
   - Full Name
   - Email
   - Phone
   - Address
   - Terms checkbox

2. **Test Validation**:
   - Submit empty → Show errors
   - Name < 3 chars → Error
   - Invalid email → Error
   - Phone < 10 digits → Error
   - Address < 10 chars → Error
   - Unchecked terms → Error

3. **Apply with Valid Data**:
   - Name: `Test Retailer`
   - Email: `retailer@test.com`
   - Phone: `9876543210`
   - Address: `123 Main Street, City, State, 12345`
   - Check terms
   - Click "Submit Application"

4. **Verify**:
   - See success message
   - Stay on same page
   - Form clears

**Expected Result**: ✅ Application submitted successfully

### Test 5.2: Apply with Duplicate Email

**Steps**:
1. Go back to apply page
2. Use same email as before: `retailer@test.com`
3. Try to submit
4. Should see error: "Email already registered"

**Expected Result**: ✅ Duplicate prevention working

---

## Part 6: Admin Application Review Testing (15 minutes)

### Test 6.1: View Applications

**URL**: `http://localhost/top1/admin/applications.php`

**Steps**:
1. Should see table of applications
2. Should see the application from Test 5.1
3. Status should show "Pending"
4. Click "Review" button

**Expected Result**: ✅ Application details page loads

### Test 6.2: Approve Application

**URL**: `http://localhost/top1/admin/application_detail.php?id=X`

**Steps**:
1. See applicant details:
   - Name: `Test Retailer`
   - Email: `retailer@test.com`
   - Phone: `9876543210`
   - Address: (as entered)

2. Click "Approve Application" button
3. Modal dialog appears
4. Add optional remarks: `Approved for testing`
5. Click "Confirm Approval"
6. See success message
7. Status should change to "Approved"

**Expected Result**: ✅ Application approved, user account created

### Test 6.3: Verify User Account Created

**Steps**:
1. Open phpMyAdmin
2. Go to `users` table
3. Should see new user with:
   - Email: `retailer@test.com`
   - Phone: `9876543210`
   - is_active: 1

**Expected Result**: ✅ User account in database

---

## Part 7: Retailer Login Testing (5 minutes)

### Test 7.1: Retailer Login

**URL**: `http://localhost/top1/pages/login.php`

**Steps**:
1. Try login with wrong email → Error
2. Try login with wrong password → Error
3. Login with correct credentials:
   - Email: `retailer@test.com`
   - Password: (temporary password or set in admin)

**Note**: Temporary password was set during approval. You may need to:
- Check database for actual password
- Or set it manually via database
- Or implement password reset

4. Click Login
5. Should redirect to dashboard

**Expected Result**: ✅ Retailer logged in successfully

---

## Part 8: Retailer Dashboard Testing (10 minutes)

### Test 8.1: Dashboard Display

**URL**: `http://localhost/top1/pages/dashboard.php`

**Steps**:
1. Page should show:
   - Welcome message with retailer name
   - Product grid with items added in Part 4
   - Shopping cart feature
   - Recent orders (empty if first time)
   - Statistics cards

2. Verify product display:
   - Images display correctly (if added)
   - Prices show with ₹ symbol
   - Descriptions visible
   - "Add to Cart" buttons present

**Expected Result**: ✅ Dashboard loads with products

### Test 8.2: Add to Cart

**Steps**:
1. Click "Add to Cart" on a product
2. Should see quantity selector popup
3. Select quantity: `5`
4. Click "Add"
5. Should see confirmation message
6. Cart count should update

**Expected Result**: ✅ Item added to cart

### Test 8.3: View Cart

**Steps**:
1. Look for cart summary on page
2. Should show:
   - Items in cart
   - Quantities
   - Unit prices
   - Total price
3. Options to:
   - Update quantity
   - Remove item
   - Checkout

**Expected Result**: ✅ Cart displaying correctly

---

## Part 9: Order Placement Testing (10 minutes)

### Test 9.1: Place Order (COD)

**Steps**:
1. From cart, click "Place Order"
2. Select payment method: "Cash on Delivery"
3. Review order details:
   - Items and quantities
   - Unit prices
   - Subtotal
   - Total
4. Click "Confirm Order"
5. Should see:
   - Success message
   - Order number generated
   - Redirect to orders page

**Expected Result**: ✅ Order created successfully

### Test 9.2: Place Order (UPI)

**Steps**:
1. Add another product to cart
2. Click "Place Order"
3. Select payment method: "UPI"
4. Fill UPI details:
   - UPI ID: `test@upi`
5. Click "Confirm Order"
6. Should see:
   - Success message
   - QR code for payment
   - Order number

**Expected Result**: ✅ UPI order with QR code

### Test 9.3: View Orders

**URL**: `http://localhost/top1/pages/orders.php`

**Steps**:
1. Should see table of orders
2. Orders from tests 9.1 and 9.2 should appear
3. Show:
   - Order number
   - Order date
   - Amount
   - Payment method
   - Status: "pending_payment"
4. Click order to view details

**Expected Result**: ✅ Orders list complete

---

## Part 10: Admin Order Management Testing (5 minutes)

### Test 10.1: View Orders as Admin

**URL**: `http://localhost/top1/admin/orders.php`

**Steps**:
1. Should see all orders from system
2. Should see orders from Part 9
3. Filter by status
4. Click to view order details

**Expected Result**: ✅ Admin can view all orders

---

## Part 11: Payment Verification Testing (10 minutes)

### Test 11.1: Verify Payment

**URL**: `http://localhost/top1/admin/payments.php`

**Steps**:
1. Should see pending payments
2. Should see orders from Part 9
3. Click "Verify" on an order
4. Upload payment proof (or skip)
5. Add remarks: `Payment verified`
6. Click "Approve"
7. Should see success message

**Expected Result**: ✅ Payment verified

---

## Part 12: Bill Generation Testing (10 minutes)

### Test 12.1: View Bills

**URL**: `http://localhost/top1/pages/bills.php` (as retailer)

**Steps**:
1. Should see bills table
2. After payment verification, bills should appear
3. Show:
   - Bill number
   - Order number
   - Amount with GST
   - Bill date
4. Click "Download" or "View"

**Expected Result**: ✅ Bills displaying correctly

---

## Part 13: Session & Security Testing (5 minutes)

### Test 13.1: Session Management

**Steps**:
1. Login as admin
2. Open another browser/private window
3. Login again with same account
4. First session should be invalidated
5. Going back to first window should show "Not logged in"

**Expected Result**: ✅ Single-login enforcement working

### Test 13.2: Unauthorized Access

**Steps**:
1. Logout
2. Try to access protected page:
   - `http://localhost/top1/pages/dashboard.php`
   - `http://localhost/top1/admin/dashboard.php`
3. Should redirect to login page

**Expected Result**: ✅ Authorization working

### Test 13.3: CSRF Protection

**Steps**:
1. Login and go to apply page
2. Check page source
3. Should see hidden CSRF token
4. Try to submit form via API without token
5. Should fail

**Expected Result**: ✅ CSRF protection working

---

## Part 14: Data Validation Testing (10 minutes)

### Test 14.1: Email Validation

**URLs**: Apply, Login, Product forms

**Steps**:
1. Try invalid emails:
   - `test@` → Error
   - `test.com` → Error
   - `@example.com` → Error
2. Valid emails:
   - `test@example.com` → Success

**Expected Result**: ✅ Email validation working

### Test 14.2: Phone Validation

**URL**: Apply page

**Steps**:
1. Try invalid phones:
   - `123` → Error
   - `12345a6789` → Error
   - `+919876543210` → Error (should strip to 10 digits)
2. Valid phones:
   - `9876543210` → Success

**Expected Result**: ✅ Phone validation working

### Test 14.3: File Upload Validation

**URL**: `http://localhost/top1/admin/add_product.php`

**Steps**:
1. Try uploading:
   - .txt file → Error
   - .exe file → Error
   - 10MB+ image → Error
2. Upload valid image:
   - JPG or PNG
   - < 5MB
   - Should succeed

**Expected Result**: ✅ File validation working

---

## Part 15: Responsive Design Testing (10 minutes)

### Test on Desktop (1920x1080)
1. Open `http://localhost/top1/index.php`
2. Check layout looks good
3. Navigation is horizontal
4. All elements properly spaced

### Test on Tablet (768x1024)
1. Use browser dev tools to simulate tablet
2. Check responsive layout
3. Navigation might show hamburger
4. Content should adapt

### Test on Mobile (375x667)
1. Simulate mobile in dev tools
2. Check hamburger menu works
3. Navigation slides out
4. Forms are readable
5. Buttons are clickable
6. No horizontal scroll

**Expected Result**: ✅ Design responsive at all breakpoints

---

## Part 16: Browser Compatibility Testing (5 minutes)

Test on multiple browsers:
- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (if available)
- ✅ Edge (latest)

Check:
- All pages load
- Forms work
- Styling consistent
- No console errors

**Expected Result**: ✅ Works on modern browsers

---

## Part 17: Performance Testing (5 minutes)

### Test Page Load Times
1. Open Dev Tools (F12)
2. Go to Network tab
3. Reload home page
4. Check:
   - Page load: Should be < 2 seconds
   - No 404 errors
   - All resources load

### Test Database Queries
1. Add items:
   - 5 products
   - 3 orders
   - 2 payments
2. Load dashboard
3. Should load without lag

**Expected Result**: ✅ Good performance

---

## Test Results Summary

Create a checklist:

```
Part 1: Pre-Test Setup ..................... [ ]
Part 2: Admin Setup ........................ [ ]
Part 3: Admin Login ........................ [ ]
Part 4: Product Management ................ [ ]
Part 5: Retailer Application .............. [ ]
Part 6: Admin Review ...................... [ ]
Part 7: Retailer Login .................... [ ]
Part 8: Retailer Dashboard ................ [ ]
Part 9: Order Placement ................... [ ]
Part 10: Admin Orders ..................... [ ]
Part 11: Payment Verification ............. [ ]
Part 12: Bill Generation .................. [ ]
Part 13: Security ......................... [ ]
Part 14: Data Validation .................. [ ]
Part 15: Responsive Design ................ [ ]
Part 16: Browser Compatibility ............ [ ]
Part 17: Performance ...................... [ ]
```

---

## Known Test Accounts

After testing, you should have:

| Role | Email/Username | Password | Status |
|------|---|---|---|
| Admin | testadmin | TestAdmin@123 | Created in test |
| Retailer | retailer@test.com | (auto-generated) | Approved in test |

---

## Troubleshooting During Tests

### Page shows blank
- Check error_log.txt
- Verify config.php settings
- Check database connection

### Login fails
- Verify credentials are correct
- Check database for user record
- Verify password hashing

### Product won't add
- Check file upload permissions
- Verify price is positive
- Check form validation messages

### Cart not working
- Check JavaScript console for errors
- Verify session is active
- Check cart data in localStorage

### Orders not showing
- Verify user is logged in
- Check database orders table
- Verify user_id matches

---

## After Testing

1. **Review all test results**
2. **Fix any issues found**
3. **Document any changes**
4. **Clean up test data** (optional)
5. **Create database backup**
6. **Ready for production**

---

## Testing Time Estimate

| Part | Time |
|------|------|
| Setup | 10 min |
| Admin Features | 25 min |
| Retailer Features | 35 min |
| Security & Performance | 15 min |
| **Total** | **~85 minutes** |

---

**Happy Testing!** 🎉

For any issues, check the SETUP_GUIDE.md or contact support.

