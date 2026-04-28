# Admin Dashboard - Button Testing Guide

## Pre-Test Checklist
- [ ] Admin user logged in (admin@kapelagi.com / admin123)
- [ ] Browser developer console open (F12)
- [ ] Database has sample orders and products
- [ ] JavaScript enabled in browser

---

## 🧪 Test Cases

### Test 1: Dashboard - Month/Year Filter
**Steps:**
1. Navigate to `/admin/dashboard.php`
2. Click "Month" button
3. Verify: Revenue chart shows 6 months of data
4. Click "Year" button
5. Verify: Revenue chart shows 12 months of data
6. Check URL changes to `?period=month` or `?period=year`

**Expected Results:**
- ✅ Buttons highlight in gold when active
- ✅ Chart data updates
- ✅ Chart labels reflect period (MMM vs MMM YYYY)
- ✅ URL parameter changes

**Pass/Fail:** ___

---

### Test 2: Orders - Status Filter Dropdown
**Steps:**
1. Navigate to `/admin/orders.php`
2. Open status filter dropdown
3. Select "Completed"
4. Verify: Only completed orders display
5. Select "Pending"
6. Verify: Only pending orders display
7. Select "All Status"
8. Verify: All orders display

**Expected Results:**
- ✅ Table filters correctly
- ✅ Row count matches expected status
- ✅ Dropdown shows correct status selected
- ✅ URL changes to reflect selection

**Pass/Fail:** ___

---

### Test 3: Orders - Update Status Inline
**Steps:**
1. Navigate to `/admin/orders.php`
2. Locate an order with "Pending" status
3. Click status dropdown in table row
4. Select "Processing"
5. Verify: Status updates immediately
6. Observe: Row flashes yellow
7. Refresh page
8. Verify: Status change persisted in database

**Expected Results:**
- ✅ Dropdown selection changes status
- ✅ No page reload occurs
- ✅ Row highlights with yellow flash
- ✅ Status persists after refresh
- ✅ Console shows no errors

**Pass/Fail:** ___

---

### Test 4: Orders - View Details Button
**Steps:**
1. Navigate to `/admin/orders.php`
2. Click eye icon for any order
3. Verify: Redirected to order details page
4. Check: URL is `order-details.php?id=[ORDER_ID]`
5. Verify: Order information displays correctly
6. Check: Items, total, and status show

**Expected Results:**
- ✅ Correct page loads
- ✅ Correct order data displays
- ✅ URL contains order ID
- ✅ Status dropdown present and functional

**Pass/Fail:** ___

---

### Test 5: Order Details - Update Status
**Steps:**
1. Open any order details page
2. Locate Status dropdown in right sidebar
3. Change status from current to another option
4. Verify: Dropdown background flashes green
5. Refresh page
6. Verify: New status saved

**Expected Results:**
- ✅ Status updates immediately
- ✅ Green flash confirmation appears
- ✅ Status persists after refresh
- ✅ No console errors

**Pass/Fail:** ___

---

### Test 6: Menu Items - Add Item Modal
**Steps:**
1. Navigate to `/admin/menu-items.php`
2. Click "+ ADD ITEM" button
3. Verify: Add Item modal appears
4. Try: Click outside modal
5. Verify: Modal closes
6. Click "+ ADD ITEM" again
7. Try: Click X button
8. Verify: Modal closes
9. Click "+ ADD ITEM" again
10. Try: Press Escape key
11. Verify: Modal closes

**Expected Results:**
- ✅ Modal opens with fade animation
- ✅ Click outside closes modal
- ✅ X button closes modal
- ✅ Escape key closes modal
- ✅ Form is empty and ready for input

**Pass/Fail:** ___

---

### Test 7: Menu Items - Add Item Form Submission
**Steps:**
1. Open Add Item modal
2. Fill in all fields:
   - Name: "Test Cappuccino"
   - Category: "Espresso Drinks"
   - Description: "Test description"
   - Price 16oz: "150"
   - Price 22oz: "180"
3. Click "Add Item" button
4. Verify: Success message appears
5. Verify: Page reloads
6. Verify: New item appears in grid
7. Check console for errors

**Expected Results:**
- ✅ Modal closes after submission
- ✅ Success message appears briefly
- ✅ Page reloads automatically
- ✅ New item visible in grid
- ✅ Item has correct data
- ✅ No console errors

**Pass/Fail:** ___

---

### Test 8: Menu Items - Form Validation
**Steps:**
1. Open Add Item modal
2. Leave Name field empty
3. Try: Click "Add Item" button
4. Verify: HTML5 validation prevents submission
5. Fill Name: "Test Item"
6. Leave Category empty
7. Try: Click "Add Item" button
8. Verify: Validation prevents submission
9. Fill Category, leave prices empty
10. Try: Click "Add Item" button
11. Verify: Validation prevents submission

**Expected Results:**
- ✅ Required fields show validation messages
- ✅ Form doesn't submit with empty required fields
- ✅ Browser shows native validation alerts

**Pass/Fail:** ___

---

### Test 9: Menu Items - Edit Item
**Steps:**
1. Click edit (pencil) icon on any menu item
2. Verify: Edit modal opens with data populated
3. Verify: Current values appear in fields
4. Change one value (e.g., name to "Updated Name")
5. Click "Update Item" button
6. Verify: Success message appears
7. Verify: Page reloads
8. Verify: Updated value shows in card

**Expected Results:**
- ✅ Modal opens with correct item data
- ✅ All fields pre-populated
- ✅ Update submits successfully
- ✅ Page reloads
- ✅ Updated value visible
- ✅ Other values unchanged

**Pass/Fail:** ___

---

### Test 10: Menu Items - Delete Item
**Steps:**
1. Note a menu item name
2. Click delete (trash) icon
3. Verify: Confirmation dialog appears with item name
4. Click "Cancel"
5. Verify: Item still exists in grid
6. Click delete icon again
7. Verify: Confirmation dialog appears
8. Click "OK"
9. Verify: Success message appears
10. Verify: Page reloads
11. Verify: Item no longer in grid

**Expected Results:**
- ✅ Confirmation dialog shows before deletion
- ✅ Item name appears in dialog
- ✅ Cancel preserves item
- ✅ OK deletes item
- ✅ Success message displays
- ✅ Page reloads
- ✅ Item removed from grid

**Pass/Fail:** ___

---

### Test 11: Security - Non-Admin Access
**Steps:**
1. Logout from admin account
2. Create/use customer account
3. Try to access `/admin/dashboard.php`
4. Verify: Redirected to signin page
5. Try to call `/admin/api.php?action=add-menu-item` directly
6. Verify: Get 403 Forbidden error

**Expected Results:**
- ✅ Non-admins cannot access admin pages
- ✅ Non-admins cannot call admin API
- ✅ Proper redirects and error codes

**Pass/Fail:** ___

---

### Test 12: Error Handling
**Steps:**
1. Open browser console
2. Try invalid operations:
   - Edit non-existent product ID
   - Update order with invalid status
   - Delete already-deleted item
3. Observe error messages
4. Verify: Errors handled gracefully

**Expected Results:**
- ✅ API returns error JSON
- ✅ User sees friendly error message
- ✅ Page remains responsive
- ✅ Appropriate HTTP status codes returned

**Pass/Fail:** ___

---

## Summary Report

**Date:** ___________
**Tester:** ___________
**Total Tests:** 12
**Passed:** ___ / 12
**Failed:** ___ / 12

### Failed Tests Details:
(List any tests that failed and why)
1. 
2. 
3. 

### Notes:
(Additional observations or issues)

---

## Browser Compatibility Testing

Test each button functionality in these browsers:

| Browser | Version | Status |
|---------|---------|--------|
| Chrome | Latest | ___ |
| Firefox | Latest | ___ |
| Edge | Latest | ___ |
| Safari | Latest | ___ |
| Mobile Chrome | Latest | ___ |

---

## Performance Testing

### Load Time
- Dashboard with charts: ___ ms
- Orders page with 100+ items: ___ ms
- Menu items page with 50+ items: ___ ms

### Action Response Time
- Status update: ___ ms
- Add menu item: ___ ms
- Delete menu item: ___ ms

---

## Sign-off

All button functionality tested and verified.

**Tester Name:** ___________________
**Date:** ___________________
**Signature:** ___________________

