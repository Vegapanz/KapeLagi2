Inventory feature

Overview
- The application now tracks inventory (stock) per product in the `products` table using the `stock` integer column.

Database
- Column: `products.stock` (INT NOT NULL DEFAULT 0)
- The database connection helper ensures the column exists automatically via `config/db.php` when the app boots.
- When orders are placed, stock is decremented per ordered quantity (best-effort) using `api/cart.php`.

Behavior
- Orders: After an order is successfully created, each `order_items` entry will cause the corresponding `products.stock` to be decreased by the ordered quantity (minimum 0, no negative values).
- Admin: `admin/menu-items.php` exposes stock values and provides a modal to update stock manually ("Update Stock"). The admin API supports `update-stock` action for programmatic updates.
- Frontend: Product listing and `menu.php` show stock on product cards and overlay "OUT OF STOCK" when stock == 0.
- Analytics: `admin/analytics.php` now displays inventory KPIs (Total Inventory, Low Stock Items, Out of Stock) and lists low-stock items (stock < 5).

Files changed/added
- Updated: [api/cart.php](api/cart.php) — decrement product stock when orders are created.
- Updated: [admin/analytics.php](admin/analytics.php) — inventory KPIs and low-stock list added.
- Added: [INVENTORY.md](INVENTORY.md) — this file.

Notes & next steps
- Stock decrement is a best-effort update; concurrent orders could cause race conditions. For production, consider using transactions and `SELECT ... FOR UPDATE` or implement stock reservation logic.
- If you want automated alerts (email/SMS) when stock is low, I can add notification hooks.
