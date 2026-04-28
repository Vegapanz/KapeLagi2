# Admin Dashboard - Functional Buttons Guide

## Overview
All buttons in the admin dashboard are now fully functional. This guide explains each button, what it does, and how to use it.

---

## 🎯 Dashboard Overview Page (`/admin/dashboard.php`)

### Revenue Chart Period Filter Buttons
**Location:** Revenue Overview card header

#### Month Button
- **Function:** Displays revenue data for the last 6 months
- **How to Use:** Click the "Month" button
- **Visual Feedback:** Active button changes to gold (#c4a870), inactive to light beige
- **Data Displayed:** Monthly revenue trends with dates in "MMM" format

#### Year Button
- **Function:** Displays revenue data for the last 12 months
- **How to Use:** Click the "Year" button
- **Visual Feedback:** Active button changes to gold, shows year range
- **Data Displayed:** 12-month revenue trends with dates in "MMM YYYY" format

**Technical Details:**
- Buttons use `?period=month` and `?period=year` URL parameters
- No page reload needed (link-based navigation)
- Charts automatically update based on selected period
- Data aggregates from orders table

---

## 👥 Customers Page (`/admin/customers.php`)

Currently displays customer information in card view with:
- Customer name and email
- Total orders placed
- Total amount spent
- Loyalty points earned
- Active/Inactive status

*Note: Individual customer action buttons (edit/delete) can be added in future updates*

---

## 📦 Orders Management Page (`/admin/orders.php`)

### Status Filter Dropdown
**Location:** Above orders table

**Functionality:**
- Filter orders by status: All, Completed, Processing, Pending, Cancelled
- Dropdown shows count of orders in each category
- **Example:**
  - "All Status (15)" - Shows 15 total orders
  - "Completed (8)" - Shows 8 completed orders
  - "Pending (3)" - Shows 3 pending orders

**How to Use:**
1. Click the dropdown menu
2. Select desired status
3. Table automatically filters to show only matching orders

**Technical Details:**
- Uses `?status=` URL parameter
- Query rebuilds dynamically based on selection
- Live count updates from database

### Order Status Update Dropdown
**Location:** In Status column of orders table

**Functionality:**
- Change order status directly from table view
- Available statuses: Pending, Processing, Completed, Cancelled
- Immediate API update without page reload

**How to Use:**
1. Locate the order in the table
2. Click the Status dropdown (currently showing order's status)
3. Select new status from options
4. Status updates immediately with visual highlight
5. Table row flashes yellow to confirm change

**Visual Feedback:**
- Selected row background flashes yellow (#ffffcc)
- Flash duration: 1 second
- Provides visual confirmation of update

**Technical Details:**
- Sends data to `/admin/api.php?action=update-order-status`
- POST request with order_id and status
- Response returns success/error JSON
- Error handling with automatic reload if failed

### View Details Button
**Location:** Actions column (eye icon)

**Functionality:**
- Navigate to detailed view of specific order
- Shows complete order information

**How to Use:**
1. Click the eye icon in the Actions column
2. Redirected to `/admin/order-details.php?id=[ORDER_ID]`
3. View full order details and change status if needed

---

## 📋 Order Details Page (`/admin/order-details.php`)

### Order Status Dropdown
**Location:** Right sidebar, Order Summary section

**Functionality:**
- Update order status from detailed view
- Options: Pending, Processing, Completed, Cancelled
- Immediate API update

**How to Use:**
1. Open any order via View Details button
2. In the Order Summary section, find the Status dropdown
3. Select new status
4. Dropdown background flashes green (#c8e6c9) to confirm

**Visual Feedback:**
- Background color changes to green for 1 second
- Indicates successful status update
- Error reloads page to show current state

**Technical Details:**
- Same API endpoint as table dropdown
- More detailed feedback in detail page context
- Order information remains visible while status changes

---

## ☕ Menu Items Management Page (`/admin/menu-items.php`)

### ADD ITEM Button
**Location:** Top right corner, above menu items grid

**Functionality:**
- Opens modal form to add new menu item
- Form validates all required fields

**How to Use:**
1. Click the "+ ADD ITEM" button
2. Add Item modal opens
3. Fill in all required fields:
   - Item Name (required)
   - Category (required) - Dropdown with options
   - Description (optional)
   - Price 16oz (required)
   - Price 22oz (required)
4. Click "Add Item" button to submit
5. Success message appears and page reloads
6. New item appears in grid

**Modal Features:**
- Clean, professional design
- Close button (X) in top right
- Cancel button to close without saving
- Click outside modal to close
- Press Escape key to close

**Categories Available:**
- Espresso Drinks
- Cold Brew
- Pastries
- Specialty
- Fruity
- Non-Coffee

**Technical Details:**
- Sends data to `/admin/api.php?action=add-menu-item`
- POST request with form data
- Database validation prevents duplicates
- Success triggers page reload to show new item

### Edit Button (Pencil Icon)
**Location:** On each menu item card, bottom action buttons

**Functionality:**
- Opens modal pre-filled with current item data
- Allows modification of all product details

**How to Use:**
1. Click the pencil/edit icon on any menu item card
2. Edit Item modal opens with current data populated
3. Modify any fields as needed
4. Click "Update Item" to save changes
5. Success message appears and page reloads
6. Card updates with new information

**Pre-filled Data:**
- Item ID (hidden)
- Current item name
- Current category
- Current description
- Current 16oz price
- Current 22oz price

**Technical Details:**
- Sends data to `/admin/api.php?action=edit-menu-item`
- Requires valid item ID
- Database updates only changed fields
- Page reload reflects all updates

### Delete Button (Trash Icon)
**Location:** On each menu item card, bottom action buttons

**Functionality:**
- Deletes menu item with confirmation
- Safety confirmation prevents accidental deletion

**How to Use:**
1. Click the trash/delete icon on any menu item card
2. Confirmation dialog appears asking: "Are you sure you want to delete '[Item Name]'?"
3. Click OK to confirm deletion or Cancel to abort
4. On confirmation, item is permanently deleted
5. Success message appears and page reloads
6. Item card disappears from grid

**Safety Features:**
- JavaScript confirmation dialog
- Shows item name in confirmation message
- Option to cancel before deletion
- Can only delete one item at a time

**Technical Details:**
- Sends data to `/admin/api.php?action=delete-menu-item`
- POST request with product ID
- Deletes from products table
- Also removes related order_items references via CASCADE
- Page reload shows updated inventory

---

## 🔐 Security & Validation

### Admin Access Control
- All actions require admin authentication
- Non-admins redirected to customer interface
- Failed authentication triggers redirect

### Data Validation
- **Menu Item Name:** Required, non-empty string
- **Category:** Required, from predefined list
- **Prices:** Required, must be positive numbers
- **Order ID:** Required, must be valid integer
- **Order Status:** Required, must be from valid list

### API Error Handling
- Invalid requests return HTTP error codes
- Error messages returned as JSON
- Frontend displays user-friendly error messages
- Failed operations trigger page reload for safety

---

## 💡 User Experience Features

### Visual Feedback
1. **Button State Changes:**
   - Active buttons highlight in gold (#c4a870)
   - Inactive buttons show light beige (#f0ebe4)

2. **Update Confirmation:**
   - Table rows flash yellow on status update
   - Order details show green background flash
   - Success messages briefly display

3. **Modal Animations:**
   - Smooth fade-in effect on modal open
   - Slide-down animation for modal content
   - Smooth animations on close

4. **Keyboard Support:**
   - Press Escape to close any modal
   - Tab navigation through form fields
   - Enter to submit forms

### Responsive Design
- All buttons work on desktop, tablet, and mobile
- Touch-friendly button sizes
- Dropdown menus adapt to screen size
- Modals scale appropriately

---

## 🛠️ Technical Stack

### Frontend Technologies
- JavaScript (Vanilla, no jQuery)
- Fetch API for AJAX requests
- Bootstrap 5 forms and utilities
- CSS animations and transitions

### Backend API
**File:** `/admin/api.php`

**Endpoints:**
- `api.php?action=add-menu-item` (POST)
- `api.php?action=edit-menu-item` (POST)
- `api.php?action=delete-menu-item` (POST)
- `api.php?action=update-order-status` (POST)

**Response Format:** JSON
```json
{
    "success": true,
    "message": "Operation completed",
    "id": 123
}
```

### Database Operations
- All operations use prepared statements
- Prevents SQL injection
- Uses transactions where applicable
- Cascading deletes for referential integrity

---

## 📱 Button Summary Table

| Button | Page | Function | Status |
|--------|------|----------|--------|
| Month | Dashboard | View 6-month revenue | ✅ Working |
| Year | Dashboard | View 12-month revenue | ✅ Working |
| Status Dropdown | Orders | Change order status | ✅ Working |
| View Details | Orders | Open order details | ✅ Working |
| Status Dropdown | Order Details | Change status from detail | ✅ Working |
| + ADD ITEM | Menu Items | Open add item form | ✅ Working |
| Edit (Pencil) | Menu Items | Open edit form | ✅ Working |
| Delete (Trash) | Menu Items | Delete item | ✅ Working |

---

## ⚠️ Important Notes

1. **Data Loss:** Deleted items cannot be recovered. Confirmation dialogs are your safety net.

2. **Order Status:** Always verify order details before changing status, especially to "Cancelled".

3. **Menu Item Prices:** Prices must be greater than 0. The system accepts decimal values up to 2 places.

4. **Page Reload:** Some operations trigger automatic page reload to ensure data consistency.

5. **Real-time Updates:** All updates are immediately reflected in the database.

---

## 🔄 Future Enhancements

Potential buttons and features for future versions:
- Bulk actions (select multiple items to delete)
- Search/filter by menu item name
- Customer action buttons (view details, edit, deactivate)
- Order export/print functionality
- Batch order status updates
- Schedule promotional items
- Inventory management buttons

---

## 📞 Support & Troubleshooting

### Button Not Working?
1. Check browser console (F12 → Console tab)
2. Verify admin authentication
3. Try page refresh
4. Check API endpoint accessibility

### Modal Won't Open?
1. Check for JavaScript errors in console
2. Verify modal ID matches button onclick
3. Clear browser cache
4. Try different browser

### Changes Not Saving?
1. Check internet connection
2. Verify admin role in database
3. Look for error messages in console
4. Try operation again

---

**Last Updated:** April 2026
**Version:** 1.0
**Status:** ✅ All Buttons Functional
