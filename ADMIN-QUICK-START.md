# 🏪 KapeLagi Admin Dashboard - Quick Reference

## 🚀 Getting Started

### Login Credentials
```
Email: admin@kapelagi.com
Password: admin123
```

### Access the Dashboard
1. Go to: `http://localhost/KapeLagi/signin.php`
2. Enter admin credentials
3. You'll be automatically redirected to the admin dashboard

---

## 📊 Dashboard Pages Overview

### 1️⃣ **Dashboard** (Overview)
**Path:** `/admin/dashboard.php`
- Real-time KPIs (Revenue, Orders, Customers, Pending)
- 6-month revenue trend chart
- Product category revenue pie chart
- Recent 5 orders table
- Top 5 selling products

### 2️⃣ **Customers**
**Path:** `/admin/customers.php`
- All customers displayed as cards
- Shows: Orders count, Total spent, Loyalty points
- Customer status (Active/Inactive)
- Contact information visible

### 3️⃣ **Orders**
**Path:** `/admin/orders.php`
- Table view of all orders
- **Filter by Status:** All, Completed, Processing, Pending, Cancelled
- Quick view of order details
- Status counts for each category

### 4️⃣ **Order Details**
**Path:** `/admin/order-details.php?id=[ORDER_ID]`
- Complete order information
- Itemized product list with prices
- Delivery address
- Customer contact details
- Order status and date

### 5️⃣ **Menu Items**
**Path:** `/admin/menu-items.php`
- All menu items in grid view
- Statistics: Price, Sales count, Revenue
- Monthly growth percentage
- Edit/Delete buttons for each item

### 6️⃣ **Analytics** (Coming Soon)
**Path:** `/admin/analytics.php`
- Advanced analytics features

### 7️⃣ **Settings** (Coming Soon)
**Path:** `/admin/settings.php`
- System configuration

---

## 🎨 Dashboard Features

### Color-Coded Status Badges
- 🟢 **Completed** - Green
- 🟡 **Pending** - Yellow
- 🔵 **Processing** - Blue
- 🔴 **Cancelled** - Red
- 🟢 **Active** - Green (Customer status)

### Navigation Sidebar
- **Location:** Fixed on left side (250px width)
- **Features:** Active page highlight, logout button
- **Responsive:** Collapses on mobile

### Data Visualization
- **Charts:** Chart.js integration for graphs
- **Revenue Chart:** Monthly trends with fill
- **Category Chart:** Doughnut chart showing distribution

---

## 📱 Key Statistics Displayed

### KPI Cards
1. **Total Revenue** - Sum of all completed orders
2. **Total Orders** - Count of all orders
3. **Total Customers** - Count of customer users
4. **Pending Orders** - Orders not yet completed

### Customer Stats
- Total orders placed
- Total amount spent (₱)
- Loyalty points earned

### Product Stats
- Total sales units
- Revenue generated (₱)
- Monthly growth %

---

## 🔐 Security & Access Control

### Role-Based Access
- **Admin:** Full access to `/admin/` pages
- **Customer:** Access only to `/` pages
- **Non-logged in:** Redirect to signin.php

### Admin Verification
Every admin page includes security check:
- Validates user is logged in
- Confirms user is admin role
- Redirects unauthorized users

---

## 🗄️ Database Integration

### Tables Used
- `users` - User accounts with role field
- `orders` - Order information
- `order_items` - Items within orders
- `products` - Menu items

### Role Column
New column added to users table:
```
role VARCHAR(20) DEFAULT 'customer'
```

---

## 🛠️ Technical Stack

### Frontend
- HTML5, CSS3
- Bootstrap 5
- Font Awesome Icons
- Chart.js for charts

### Backend
- PHP 7.4+
- MySQL/MySQLi
- Session-based authentication

### Styling
- Custom admin-styles.css
- Gold/Brown color scheme
- Responsive design

---

## ⚡ Quick Actions

### View Recent Orders
Click "View All →" on Dashboard to see full orders list

### Filter Orders by Status
Use the dropdown on Orders page to filter

### View Order Details
Click the eye icon next to any order

### Customer Information
Hover over customer cards for full details

---

## 📝 Important Notes

1. **Change Password:** Change admin password after first login
2. **Data Backup:** Regular database backups recommended
3. **Session Timeout:** Sessions expire after browser close
4. **Mobile Responsive:** Dashboard works on tablets and phones

---

## 🔗 File Locations

```
/admin/
├── dashboard.php          # Main dashboard
├── customers.php          # Customer list
├── orders.php             # Orders list
├── order-details.php      # Order details
├── menu-items.php         # Menu management
├── check_admin.php        # Admin verification
├── includes/              # Components
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
├── css/
│   └── admin-styles.css   # Styling
└── js/
    └── admin-script.js    # JavaScript
```

---

## 📞 Support Information

For implementation details, see: `ADMIN-DASHBOARD-GUIDE.md`

For general info, see: `README.md`

---

**Last Updated:** April 2026
**Version:** 1.0
**Status:** ✅ Active
