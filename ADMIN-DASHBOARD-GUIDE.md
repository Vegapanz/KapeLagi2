# Admin Dashboard Setup Guide

## Overview
A fully functional admin dashboard has been created for KapeLagi coffee shop management system. The admin dashboard provides comprehensive tools for managing customers, orders, menu items, and viewing analytics.

## Quick Start

### Admin Login Credentials
- **Email:** `admin@kapelagi.com`
- **Password:** `admin123`

⚠️ **IMPORTANT:** Change this password immediately after your first login!

### How to Access the Admin Dashboard
1. Navigate to `https://localhost/KapeLagi/signin.php`
2. Enter the admin email and password
3. You will be automatically redirected to the admin dashboard at `/admin/dashboard.php`

## Admin Dashboard Features

### 1. **Overview Dashboard** (`/admin/dashboard.php`)
- **KPI Cards:** Display key performance indicators
  - Total Revenue with trend
  - Total Orders with trend
  - Total Customers with trend
  - Pending Orders with trend

- **Revenue Chart:** Line chart showing 6-month revenue trends
- **Categories Pie Chart:** Revenue distribution by product category
- **Recent Orders Table:** Last 5 orders with status and actions
- **Top Sellers List:** Best-performing products with sales data

### 2. **Customers Page** (`/admin/customers.php`)
- View all registered customers with statistics
- Customer cards showing:
  - Customer name and email
  - Phone number
  - Total orders placed
  - Total amount spent
  - Loyalty points
  - Active/Inactive status

### 3. **Orders Management** (`/admin/orders.php`)
- View all orders in a professional table format
- Filter orders by status (All, Completed, Processing, Pending, Cancelled)
- Order columns display:
  - Order ID
  - Customer name
  - Items ordered
  - Total amount
  - Order status with color-coded badges
  - Order date and time
  - View details action

- **Order Details Page** (`/admin/order-details.php`)
  - Detailed view of individual orders
  - Customer information
  - Complete list of items in the order
  - Delivery address
  - Order status
  - Order date and time

### 4. **Menu Items Management** (`/admin/menu-items.php`)
- View all menu items with sales analytics
- Menu item cards displaying:
  - Product name and category
  - Average price
  - Total sales quantity
  - Total revenue
  - Monthly growth percentage
  - Edit and Delete buttons
- Add new items button (placeholder for future implementation)

### 5. **Analytics Page** (`/admin/analytics.php`)
- Advanced analytics features (coming soon)

### 6. **Settings Page** (`/admin/settings.php`)
- Admin settings configuration (coming soon)

## Navigation Sidebar
The left sidebar provides quick navigation to all admin pages:
- 📊 Overview - Dashboard statistics
- 👥 Customers - Customer management
- 🛒 Orders - Order management
- ☰ Menu Items - Product management
- 📈 Analytics - Advanced analytics
- ⚙️ Settings - System settings
- Logout button at the bottom

## User Role System

### Role Types
1. **Admin** - Full access to admin dashboard
2. **Customer** - Standard user access to shop interface

### How it Works
- When a user signs in with admin credentials, they are redirected to `/admin/dashboard.php`
- When a customer signs in, they are redirected to the regular customer interface (`index.php`)
- The role is stored in the session and database

## Technical Details

### Database Schema Changes
A new `role` column was added to the `users` table:
```sql
ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer' AFTER email_verified_at;
```

### Session Management
Updated session functions in `/config/session.php`:
- `login_user($user_id, $user_name, $user_email, $role)` - Now includes role parameter
- `get_user_role()` - Returns the user's role
- `is_admin()` - Checks if current user is an admin

### Admin Access Control
All admin pages include `/admin/check_admin.php` which:
- Checks if user is logged in
- Verifies user is an admin
- Redirects non-admins to the customer interface

## File Structure
```
/admin/
├── dashboard.php           # Main admin overview
├── customers.php           # Customer management
├── orders.php              # Order management
├── order-details.php       # Order detail view
├── menu-items.php          # Menu items management
├── analytics.php           # Analytics (coming soon)
├── settings.php            # Settings (coming soon)
├── check_admin.php         # Admin access verification
├── includes/
│   ├── header.php          # Common header with admin check
│   ├── sidebar.php         # Navigation sidebar
│   └── footer.php          # Common footer
├── css/
│   └── admin-styles.css    # Admin dashboard styling
└── js/
    └── admin-script.js     # Admin dashboard scripts
```

## Styling & Theme
The admin dashboard uses:
- **Color Scheme:** Professional beige/brown theme matching KapeLagi branding
- **Primary Gold:** `#c4a870` - Accent color throughout
- **Sidebar:** Dark brown background (`#1a1a1a`)
- **Cards:** Clean white cards with subtle shadows
- **Status Badges:** Color-coded (Completed: Green, Pending: Yellow, Processing: Blue, Cancelled: Red)

## Security Notes
1. Admin pages require admin authentication
2. Non-admins cannot access `/admin/` pages directly
3. Session-based access control is implemented
4. All user inputs are escaped to prevent SQL injection

## Future Enhancements
Placeholder pages have been created for:
- Advanced Analytics Dashboard
- Admin Settings & Configuration
- Additional admin features can be added to existing pages

## Support
For issues or questions, refer to the main project documentation or contact the development team.

## Password Reset
To create a new admin account or reset admin credentials, run:
```
php setup_admin.php
```

Or modify the credentials directly in `/setup_admin.php` and run it again.
