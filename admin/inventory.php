<?php
$page_title = "Inventory";
include 'includes/header.php';

// Ensure ingredients table exists
ensure_ingredients_table($conn);

// Small helper to load page
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 class="page-title">Ingredients Inventory</h1>
    <button class="btn-add-item" onclick="openAddIngredient()">
        <i class="fas fa-plus"></i> ADD INGREDIENT
    </button>
</div>
<div style="display:flex; gap:12px; margin-bottom:12px; align-items:center;">
    <label style="margin:0;">Filter by product category:</label>
    <select id="productCategoryFilter" style="padding:6px 10px;">
        <option value="">All</option>
        <option value="Coffee">Coffee</option>
        <option value="Non-Coffee">Non-Coffee</option>
        <option value="Fruity">Refreshers</option>
    </select>
    <label style="margin:0 6px 0 18px;">Sort:</label>
    <select id="sortSelect" style="padding:6px 10px;">
        <option value="name_asc">Name ↑</option>
        <option value="name_desc">Name ↓</option>
        <option value="stock_asc">Stock ↑</option>
        <option value="stock_desc">Stock ↓</option>
    </select>
    <label style="margin:0 6px 0 12px;">Per group:</label>
    <select id="perPageSelect" style="padding:6px 10px;">
        <option value="5">5</option>
        <option value="10" selected>10</option>
        <option value="20">20</option>
    </select>
    <label style="margin:0 6px 0 12px;">Search:</label>
    <input id="inventorySearch" type="search" placeholder="Search ingredients..." style="padding:6px 10px; min-width:180px;">
    <button type="button" class="btn btn-sm" id="bulkDeleteBtn" onclick="deleteSelectedIngredients()" disabled style="margin-left:10px; background:#e53935; color:#fff;">Delete selected</button>
    <div style="margin-left:auto; font-size:0.9rem; color:#666;">Grouped by ingredient type</div>
</div>

<div class="section-card">
    <div id="ingredientsContainer" style="overflow:auto;">
        <div id="ingredientsSections">Loading...</div>
    </div>
</div>

<script>
const UNIT_OPTIONS = [
    { value: 'liters', label: 'Liters (L)' },
    { value: 'milliliters', label: 'Milliliters (mL)' },
    { value: 'grams', label: 'Grams (g)' },
    { value: 'milligrams', label: 'Milligrams (mg)' },
    { value: 'kilograms', label: 'Kilograms (kg)' },
    { value: 'pieces', label: 'Pieces (pcs)' },
    { value: 'units', label: 'Units' }
];
</script>

<!-- Add / Edit Modal -->
<div id="ingredientModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color:white; margin: 10% auto; padding: 20px; border-radius:8px; width:90%; max-width:480px;">
        <h3 id="ingredientModalTitle">Add Ingredient</h3>
        <form id="ingredientForm" onsubmit="submitIngredient(event)">
            <input type="hidden" id="ingredientId" name="id" value="0">
            <div class="form-group mb-2">
                <label>Name</label>
                <input id="ingredientName" name="name" class="form-control" required>
            </div>
            <div class="form-group mb-2">
                <label>Unit Scale</label>
                <select id="ingredientUnit" name="unit" class="form-control" required>
                    <option value="">Select unit</option>
                    <option value="liters">Liters (L)</option>
                    <option value="milliliters">Milliliters (mL)</option>
                    <option value="grams">Grams (g)</option>
                    <option value="milligrams">Milligrams (mg)</option>
                    <option value="kilograms">Kilograms (kg)</option>
                    <option value="pieces">Pieces (pcs)</option>
                </select>
            </div>
            <div class="form-group mb-2">
                <label>Stock Amount</label>
                <input id="ingredientStock" name="stock" type="number" step="0.01" class="form-control" value="0">
            </div>
            <div class="form-group mb-2">
                <label>Low stock threshold</label>
                <input id="ingredientThreshold" name="low_stock_threshold" type="number" step="0.01" class="form-control" value="5">
            </div>
            <div style="display:flex; gap:8px; margin-top:12px;">
                <button class="btn" type="submit" style="background-color:#c4a870; color:#fff;">Save</button>
                <button type="button" class="btn" onclick="closeIngredientModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
async function fetchIngredients() {
    const res = await fetch('api.php?action=get-ingredients');
    const data = await res.json();
    const container = document.getElementById('ingredientsSections');
    if (!data.success) {
        container.innerHTML = '<div style="text-align:center; color:#d32f2f; padding:20px;">Failed to load</div>';
        return;
    }
    const items = data.ingredients;
    if (items.length === 0) {
        container.innerHTML = '<div style="text-align:center; color:#999; padding:30px;">No ingredients yet</div>';
        return;
    }

    // group by ingredient category
    const groups = {};
    for (const it of items) {
        const cat = (it.category || 'other').toLowerCase();
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(it);
    }

    // product category filter
    const selectedProductCategory = document.getElementById('productCategoryFilter').value;
    const searchTerm = (document.getElementById('inventorySearch').value || '').trim().toLowerCase();

    // render groups with sorting and pagination
    const sortOpt = document.getElementById('sortSelect').value;
    const perPage = parseInt(document.getElementById('perPageSelect').value || '10', 10);
    const ordered = Object.keys(groups).sort();
    // page indices per group
    window._invPageIdx = window._invPageIdx || {};
    const selectedIds = ensureSelectionState();
    let html = '';
    for (const g of ordered) {
        const displayGroup = g === '' ? 'Other' : (g.charAt(0).toUpperCase() + g.slice(1));
        html += `<h3 style="margin-top:18px;">${escapeHtml(displayGroup)}</h3>`;
        let list = groups[g].slice();
        // apply product-category filter and search term
        list = list.filter(it => {
            if (selectedProductCategory && !(it.product_categories || '').toLowerCase().includes(selectedProductCategory.toLowerCase())) return false;
            if (searchTerm) {
                const hay = (it.name + ' ' + it.unit + ' ' + (it.product_categories||'')).toLowerCase();
                return hay.indexOf(searchTerm) !== -1;
            }
            return true;
        });
        // sort list according to sortOpt
        list.sort((a,b) => {
            switch (sortOpt) {
                case 'name_asc': return a.name.localeCompare(b.name);
                case 'name_desc': return b.name.localeCompare(a.name);
                case 'stock_asc': return parseFloat(a.stock) - parseFloat(b.stock);
                case 'stock_desc': return parseFloat(b.stock) - parseFloat(a.stock);
                default: return a.name.localeCompare(b.name);
            }
        });

        const pageKey = g || 'other';
        if (typeof window._invPageIdx[pageKey] === 'undefined') window._invPageIdx[pageKey] = 0;
        const pageIdx = window._invPageIdx[pageKey] || 0;
        const totalPages = Math.max(1, Math.ceil(list.length / perPage));
        const start = pageIdx * perPage;
        const pageItems = list.slice(start, start + perPage);

    html += `<table class="table" style="width:100%; border-collapse: collapse;"><thead><tr><th style="text-align:center; padding:8px; width:48px;"><input type="checkbox" class="group-select-all" data-group="${escapeJs(pageKey)}" onchange="toggleGroupSelection('${pageKey}', this.checked)"></th><th style="text-align:left; padding:8px;">Ingredient</th><th style="text-align:left; padding:8px;">Unit</th><th style="text-align:right; padding:8px;">Stock</th><th style="text-align:right; padding:8px;">Low Threshold</th><th style="text-align:left; padding:8px;">Used In</th><th style="text-align:right; padding:8px;">Actions</th></tr></thead><tbody>`;

        if (pageItems.length === 0) {
            html += '<tr><td colspan="8" style="text-align:center; color:#999; padding:12px;">No items in this group</td></tr>';
        } else {
            for (const it of pageItems) {
                const usedIn = it.product_categories ? escapeHtml(it.product_categories) : '';
                html += `
                    <tr>
                        <td style="padding:8px; text-align:center;"><input type="checkbox" class="ingredient-select" data-id="${it.id}" data-group="${escapeHtml(pageKey)}" onchange="toggleIngredientSelection(${it.id}, this.checked)"></td>
                        <td style="padding:8px;">${escapeHtml(it.name)}</td>
                        <td style="padding:8px;">${escapeHtml(it.unit)}</td>
                        <td style="padding:8px; text-align:right;">${Number(it.stock)} ${escapeHtml(it.unit)}</td>
                        <td style="padding:8px; text-align:right;">${Number(it.low_stock_threshold)}</td>
                        <td style="padding:8px;">${usedIn}</td>
                        <td style="padding:8px; text-align:right;"><button class="btn btn-sm" onclick="openEditIngredient(${it.id}, '${escapeJs(it.name)}', '${escapeJs(it.unit)}', ${Number(it.stock)}, ${Number(it.low_stock_threshold)})">Edit</button> <button class="btn btn-sm" onclick="openAdjustStock(${it.id}, '${escapeJs(it.name)}', ${Number(it.stock)})">Adjust</button> <button class="btn btn-sm" onclick="deleteIngredient(${it.id})" style="background:#e53935; color:#fff;">Delete</button></td>
                    </tr>
                `;
            }
        }

        html += '</tbody></table>';

        // pagination controls for the group
        if (totalPages > 1) {
            html += '<div style="display:flex; gap:8px; justify-content:flex-end; margin-bottom:12px;">';
            html += `<div style="align-self:center; color:#666;">Page ${pageIdx+1} of ${totalPages}</div>`;
            html += `<button class="btn btn-sm" onclick="invChangePage('${pageKey}', ${Math.max(0, pageIdx-1)})" ${pageIdx===0? 'disabled':''}>Prev</button>`;
            html += `<button class="btn btn-sm" onclick="invChangePage('${pageKey}', ${Math.min(totalPages-1, pageIdx+1)})" ${pageIdx>=totalPages-1? 'disabled':''}>Next</button>`;
            html += '</div>';
        }
    }
    container.innerHTML = html;
    document.querySelectorAll('.ingredient-select').forEach(cb => {
        cb.checked = selectedIds.has(String(cb.dataset.id));
    });
    syncGroupSelectAllState();
    updateBulkDeleteButton();
}

function invChangePage(groupKey, newIdx) {
    window._invPageIdx = window._invPageIdx || {};
    window._invPageIdx[groupKey] = newIdx;
    fetchIngredients();
}

function ensureSelectionState() {
    window._selectedIngredientIds = window._selectedIngredientIds || new Set();
    return window._selectedIngredientIds;
}

function updateBulkDeleteButton() {
    const selected = ensureSelectionState();
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.disabled = selected.size === 0;
        bulkDeleteBtn.textContent = selected.size > 0 ? `Delete selected (${selected.size})` : 'Delete selected';
    }
}

function toggleIngredientSelection(id, checked) {
    const selected = ensureSelectionState();
    if (checked) {
        selected.add(String(id));
    } else {
        selected.delete(String(id));
    }
    updateBulkDeleteButton();
    syncGroupSelectAllState();
}

function toggleGroupSelection(groupKey, checked) {
    const selected = ensureSelectionState();
    const groupCheckboxes = Array.from(document.querySelectorAll(`.ingredient-select[data-group="${CSS.escape(groupKey)}"]`));
    groupCheckboxes.forEach(cb => {
        cb.checked = checked;
        if (checked) {
            selected.add(String(cb.dataset.id));
        } else {
            selected.delete(String(cb.dataset.id));
        }
    });
    updateBulkDeleteButton();
    syncGroupSelectAllState();
}

function syncGroupSelectAllState() {
    const selected = ensureSelectionState();
    document.querySelectorAll('.group-select-all').forEach(groupCb => {
        const groupKey = groupCb.dataset.group;
        const groupCheckboxes = Array.from(document.querySelectorAll(`.ingredient-select[data-group="${CSS.escape(groupKey)}"]`));
        if (groupCheckboxes.length === 0) {
            groupCb.checked = false;
            groupCb.indeterminate = false;
            return;
        }
        const selectedCount = groupCheckboxes.filter(cb => selected.has(String(cb.dataset.id))).length;
        groupCb.checked = selectedCount === groupCheckboxes.length;
        groupCb.indeterminate = selectedCount > 0 && selectedCount < groupCheckboxes.length;
    });
}

async function deleteSelectedIngredients() {
    const selected = ensureSelectionState();
    const ids = Array.from(selected).map(id => parseInt(id, 10)).filter(id => id > 0);
    if (ids.length === 0) return;
    if (!confirm(`Delete ${ids.length} selected ingredient${ids.length === 1 ? '' : 's'}?`)) return;

    const fd = new FormData();
    ids.forEach(id => fd.append('ids[]', String(id)));
    const res = await fetch('api.php?action=delete-ingredient', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.success) {
        window._selectedIngredientIds = new Set();
        fetchIngredients();
    } else {
        alert(json.error || 'Failed');
    }
}

function openAddIngredient() {
    document.getElementById('ingredientModalTitle').innerText = 'Add Ingredient';
    document.getElementById('ingredientId').value = 0;
    document.getElementById('ingredientName').value = '';
    document.getElementById('ingredientUnit').value = 'pieces';
    document.getElementById('ingredientStock').value = 0;
    document.getElementById('ingredientThreshold').value = 5;
    document.getElementById('ingredientModal').style.display = 'block';
}

function openEditIngredient(id, name, unit, stock, threshold) {
    document.getElementById('ingredientModalTitle').innerText = 'Edit Ingredient';
    document.getElementById('ingredientId').value = id;
    document.getElementById('ingredientName').value = name;
    document.getElementById('ingredientUnit').value = normalizeUnitValue(unit);
    document.getElementById('ingredientStock').value = stock;
    document.getElementById('ingredientThreshold').value = threshold;
    document.getElementById('ingredientModal').style.display = 'block';
}

function normalizeUnitValue(unit) {
    const value = String(unit || '').toLowerCase().trim();
    if (['liter', 'liters', 'l'].includes(value)) return 'liters';
    if (['milliliter', 'milliliters', 'ml'].includes(value)) return 'milliliters';
    if (['gram', 'grams', 'g'].includes(value)) return 'grams';
    if (['milligram', 'milligrams', 'mg'].includes(value)) return 'milligrams';
    if (['kilogram', 'kilograms', 'kg'].includes(value)) return 'kilograms';
    if (['piece', 'pieces', 'pc', 'pcs'].includes(value)) return 'pieces';
    return value || 'units';
}

function closeIngredientModal() {
    document.getElementById('ingredientModal').style.display = 'none';
}

async function submitIngredient(e) {
    e.preventDefault();
    const form = document.getElementById('ingredientForm');
    const id = document.getElementById('ingredientId').value;
    const data = new FormData(form);
    const action = (id && id !== '0') ? 'update-ingredient' : 'add-ingredient';
    const res = await fetch('api.php?action='+action, { method: 'POST', body: data });
    const json = await res.json();
    if (json.success) {
        closeIngredientModal();
        fetchIngredients();
    } else {
        alert(json.error || 'Failed');
    }
}

async function openAdjustStock(id, name, currentStock) {
    const qty = prompt('Enter new stock for "' + name + '"', currentStock);
    if (qty === null) return;
    const fd = new FormData();
    fd.append('id', id);
    fd.append('stock', qty);
    const res = await fetch('api.php?action=update-ingredient-stock', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.success) fetchIngredients(); else alert(json.error || 'Failed');
}

async function deleteIngredient(id) {
    if (!confirm('Delete ingredient?')) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('api.php?action=delete-ingredient', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.success) fetchIngredients(); else alert(json.error || 'Failed');
}

function escapeHtml(s){
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
function escapeJs(s){ return (String(s).replace(/'/g, "\\'").replace(/"/g, '\\"')); }

// initial load
document.getElementById('productCategoryFilter').addEventListener('change', fetchIngredients);
document.getElementById('sortSelect').addEventListener('change', () => { window._invPageIdx = {}; fetchIngredients(); });
document.getElementById('perPageSelect').addEventListener('change', () => { window._invPageIdx = {}; fetchIngredients(); });
document.getElementById('inventorySearch').addEventListener('input', () => { window._invPageIdx = {}; fetchIngredients(); });
fetchIngredients();
</script>

<?php include 'includes/footer.php'; ?>
