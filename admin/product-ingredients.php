<?php
$page_title = "Product Ingredients";
include 'includes/header.php';

// Ensure mapping table exists
ensure_product_ingredients_table($conn);
ensure_ingredients_table($conn);

// Load products and ingredients for selector
$products_res = $conn->query("SELECT id, name FROM products ORDER BY name ASC");
$products = [];
if ($products_res) while ($r = $products_res->fetch_assoc()) $products[] = $r;
$ings_res = $conn->query("SELECT id, name, unit, package_size, package_unit FROM ingredients ORDER BY name ASC");
$ingredients = [];
if ($ings_res) while ($r = $ings_res->fetch_assoc()) $ingredients[] = $r;
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h1 class="page-title">Product Ingredient Mappings</h1>
    <button class="btn-add-item" onclick="openAddMapping()"><i class="fas fa-plus"></i> ADD MAPPING</button>
</div>

<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:14px;">
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <label for="mappingSearch" style="margin:0; color:#7a6a53;">Search</label>
        <input id="mappingSearch" type="search" placeholder="Search product or ingredient..." style="padding:8px 10px; min-width:240px;">
    </div>
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <label for="mappingProductFilter" style="margin:0; color:#7a6a53;">Product</label>
        <select id="mappingProductFilter" style="padding:8px 10px; min-width:220px;">
            <option value="">All products</option>
            <?php foreach($products as $p): ?>
                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="section-card">
    <div style="overflow:auto;">
        <div id="mappingPaginationTop" style="display:flex; justify-content:flex-end; gap:10px; align-items:center; padding:0 0 12px 0;"></div>
        <table id="mappingsTable" class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left; padding:8px;">Product</th>
                    <th style="text-align:left; padding:8px;">Size</th>
                    <th style="text-align:left; padding:8px;">Ingredient</th>
                    <th style="text-align:left; padding:8px;">Stock Scale</th>
                    <th style="text-align:right; padding:8px;">Qty per unit</th>
                    <th style="text-align:left; padding:8px;">Unit</th>
                    <th style="text-align:right; padding:8px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="7" style="text-align:center; color:#999; padding:30px;">Loading...</td></tr>
            </tbody>
        </table>
        <div id="mappingPaginationBottom" style="display:flex; justify-content:flex-end; gap:10px; align-items:center; padding:12px 0 0 0;"></div>
    </div>
</div>

<script>
const UNIT_SCALE_OPTIONS = [
    { value: 'liters', label: 'Liters (L)' },
    { value: 'milliliters', label: 'Milliliters (mL)' },
    { value: 'grams', label: 'Grams (g)' },
    { value: 'milligrams', label: 'Milligrams (mg)' },
    { value: 'kilograms', label: 'Kilograms (kg)' },
    { value: 'pieces', label: 'Pieces (pcs)' },
    { value: 'units', label: 'Units' }
];
</script>

<!-- Modal -->
<div id="mappingModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background:#fff; margin:6% auto; padding:24px; width:92%; max-width:760px; border-radius:8px;">
        <h3 id="mappingTitle">Add Mapping</h3>
        <form id="mappingForm" onsubmit="submitMapping(event)">
            <input type="hidden" id="mapId" name="id" value="0">
            <div class="form-group mb-2">
                <label>Product</label>
                <select id="mapProduct" name="product_id" class="form-control" required>
                    <option value="">Select product</option>
                    <?php foreach($products as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-2">
                <label style="display:block; margin-bottom:8px;">Size</label>
                <input type="hidden" id="recipeSize" value="16oz">
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="button" class="btn size-option-btn active" data-size="16oz" onclick="setRecipeSize('16oz')" style="background:#f1e4cc; border:1px solid #d8c39a; color:#3d3123;">16oz</button>
                    <button type="button" class="btn size-option-btn" data-size="22oz" onclick="setRecipeSize('22oz')" style="background:#fff; border:1px solid #d8c39a; color:#3d3123;">22oz</button>
                </div>
            </div>
            <div class="form-group mb-2">
                <label>Ingredients (max 7)</label>
                <div id="ingredientsRows" style="display:flex; flex-direction:column; gap:10px;">
                    <!-- ingredient rows appended here -->
                </div>
                <div style="margin-top:10px;">
                    <button type="button" class="btn" onclick="addIngredientRow()" id="addIngredientBtn">Add ingredient</button>
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-top:12px;">
                <button class="btn" type="submit" style="background:#c4a870; color:#fff;">Save</button>
                <button type="button" class="btn" onclick="closeMapping()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
async function loadMappings(){
    const res = await fetch('api.php?action=get-product-ingredients');
    const json = await res.json();
    const tbody = document.querySelector('#mappingsTable tbody');
    if (!json.success){ tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#d32f2f; padding:20px;">Failed</td></tr>'; return; }
    const maps = json.mappings;
    if (maps.length===0){ tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#999; padding:30px;">No mappings</td></tr>'; return; }

    const searchTerm = (document.getElementById('mappingSearch')?.value || '').trim().toLowerCase();
    const selectedProductId = (document.getElementById('mappingProductFilter')?.value || '').trim();
    const perPage = Math.max(1, parseInt(window._mappingPerPage || '5', 10));

    const byProduct = {};
    for (const map of maps) {
        if (selectedProductId && String(map.product_id) !== String(selectedProductId)) {
            continue;
        }
        if (searchTerm) {
            const haystack = [map.product_name, map.ingredient_name, map.size, map.stock_unit, map.quantity_per_unit, map.unit]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();
            if (!haystack.includes(searchTerm)) {
                continue;
            }
        }
        const key = `${map.product_id}`;
        if (!byProduct[key]) {
            byProduct[key] = {
                product_id: map.product_id,
                product_name: map.product_name,
                items: []
            };
        }
        byProduct[key].items.push(map);
    }

    const groups = Object.values(byProduct);
    if (groups.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#999; padding:30px;">No mappings found</td></tr>';
        renderMappingPagination('mappingPaginationTop', 1, 1, 0, perPage);
        renderMappingPagination('mappingPaginationBottom', 1, 1, 0, perPage);
        return;
    }
    const totalPages = Math.max(1, Math.ceil(groups.length / perPage));
    const currentPage = Math.min(Math.max(1, parseInt(window._mappingPage || '1', 10)), totalPages);
    window._mappingPage = currentPage;

    const pageStart = (currentPage - 1) * perPage;
    const pageGroups = groups.slice(pageStart, pageStart + perPage);

    tbody.innerHTML = '';
    pageGroups.forEach(group => {
        const groupedBySize = {};
        group.items.forEach(item => {
            const sizeKey = item.size || '16oz';
            if (!groupedBySize[sizeKey]) groupedBySize[sizeKey] = [];
            groupedBySize[sizeKey].push(item);
        });

        const headerRow = `
            <tr style="background:#f7f1e6; border-top:1px solid #e6d9c3;">
                <td colspan="7" style="padding:12px 8px;">
                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;">
                        <div>
                            <div style="font-weight:700; font-size:1rem; color:#3d3123;">1 ${escapeHtml(group.product_name)} equals to these ingredients</div>
                            <div style="font-size:0.9rem; color:#7a6a53; margin-top:4px;">Grouped by size: ${Object.keys(groupedBySize).map(size => escapeHtml(size)).join(', ')}</div>
                        </div>
                    </div>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', headerRow);

        Object.entries(groupedBySize).forEach(([size, sizeItems]) => {
            const sizeHeader = `
                <tr style="background:#fff8ef;">
                    <td colspan="7" style="padding:8px 12px; font-weight:700; color:#7a5f33;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <span>${escapeHtml(size)}</span>
                            <button class="btn btn-sm" onclick="openEditMappingForProduct(${group.product_id}, '${escapeJs(size)}')">Edit recipe</button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', sizeHeader);

            sizeItems.forEach(m => {
            const row = `
                <tr>
                    <td style="padding:8px;">${escapeHtml(m.product_name)}</td>
                    <td style="padding:8px;">${escapeHtml(m.size || '16oz')}</td>
                    <td style="padding:8px;">${escapeHtml(m.ingredient_name)}</td>
                    <td style="padding:8px;">${escapeHtml(m.stock_unit || 'unit')}</td>
                    <td style="padding:8px; text-align:right;">${Number(m.quantity_per_unit)}</td>
                    <td style="padding:8px;">${escapeHtml(m.unit)}</td>
                    <td style="padding:8px; text-align:right;"><button class="btn btn-sm" onclick="deleteMapping(${m.id})" style="background:#e53935;color:#fff;">Delete</button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
            });
        });
    });

    renderMappingPagination('mappingPaginationTop', currentPage, totalPages, groups.length, perPage);
    renderMappingPagination('mappingPaginationBottom', currentPage, totalPages, groups.length, perPage);
}

function renderMappingPagination(targetId, currentPage, totalPages, totalGroups, perPage) {
    const target = document.getElementById(targetId);
    if (!target) return;

    if (totalGroups === 0) {
        target.innerHTML = '';
        return;
    }

    target.innerHTML = `
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <span style="color:#7a6a53; font-size:0.9rem;">Page ${currentPage} of ${totalPages}</span>
            <select id="mappingPerPageSelect" style="padding:6px 10px;">
                <option value="5" ${perPage === 5 ? 'selected' : ''}>5</option>
                <option value="10" ${perPage === 10 ? 'selected' : ''}>10</option>
                <option value="20" ${perPage === 20 ? 'selected' : ''}>20</option>
            </select>
            <button class="btn btn-sm" onclick="changeMappingPage(${Math.max(1, currentPage - 1)})" ${currentPage <= 1 ? 'disabled' : ''}>Prev</button>
            <button class="btn btn-sm" onclick="changeMappingPage(${Math.min(totalPages, currentPage + 1)})" ${currentPage >= totalPages ? 'disabled' : ''}>Next</button>
        </div>
    `;

    const perPageSelect = target.querySelector('#mappingPerPageSelect');
    if (perPageSelect && !perPageSelect.dataset.bound) {
        perPageSelect.dataset.bound = '1';
        perPageSelect.addEventListener('change', function() {
            window._mappingPerPage = parseInt(this.value || '5', 10);
            window._mappingPage = 1;
            loadMappings();
        });
    }
}

function changeMappingPage(page) {
    window._mappingPage = Math.max(1, page);
    loadMappings();
}

function setupMappingFilters() {
    const searchInput = document.getElementById('mappingSearch');
    const productFilter = document.getElementById('mappingProductFilter');

    if (searchInput && !searchInput.dataset.bound) {
        searchInput.dataset.bound = '1';
        searchInput.addEventListener('input', function() {
            window._mappingPage = 1;
            loadMappings();
        });
    }

    if (productFilter && !productFilter.dataset.bound) {
        productFilter.dataset.bound = '1';
        productFilter.addEventListener('change', function() {
            window._mappingPage = 1;
            loadMappings();
        });
    }
}
function openAddMapping(){
    document.getElementById('mappingTitle').innerText = 'Add Mapping';
    document.getElementById('mapId').value = 0;
    document.getElementById('mapProduct').value = '';
    setRecipeSize('16oz');
    const container = document.getElementById('ingredientsRows');
    container.innerHTML = '';
    addIngredientRow();
    document.getElementById('mappingModal').style.display = 'block';
}

async function openEditMappingForProduct(productId, size = '16oz'){
    document.getElementById('mappingTitle').innerText = 'Edit Recipe';
    document.getElementById('mapId').value = 0; // not used
    document.getElementById('mapProduct').value = productId;
    setRecipeSize(size || '16oz');
    const container = document.getElementById('ingredientsRows');
    container.innerHTML = '';
    await loadRecipeRows(productId, size || '16oz');
    document.getElementById('mappingModal').style.display = 'block';
}
function normalizeUnitValue(unit) {
    const value = String(unit || '').toLowerCase().trim();
    if (['liter', 'liters', 'l'].includes(value)) return 'liters';
    if (['milliliter', 'milliliters', 'ml'].includes(value)) return 'milliliters';
    if (['gram', 'grams', 'g'].includes(value)) return 'grams';
    if (['milligram', 'milligrams', 'mg'].includes(value)) return 'milligrams';
    if (['kilogram', 'kilograms', 'kg'].includes(value)) return 'kilograms';
    if (['piece', 'pieces', 'pc', 'pcs', 'unit', 'units'].includes(value)) return 'pieces';
    return value || 'units';
}
function closeMapping(){ document.getElementById('mappingModal').style.display='none'; }

function setRecipeSize(size) {
    const recipeSizeInput = document.getElementById('recipeSize');
    if (recipeSizeInput) {
        recipeSizeInput.value = size || '16oz';
    }
    document.querySelectorAll('.size-option-btn').forEach(btn => {
        const isActive = btn.dataset.size === (size || '16oz');
        btn.classList.toggle('active', isActive);
        btn.style.background = isActive ? '#f1e4cc' : '#fff';
    });

    const productSelect = document.getElementById('mapProduct');
    const currentProductId = productSelect ? productSelect.value : '';
    if (currentProductId) {
        loadRecipeRows(currentProductId, size || '16oz');
    }
}
async function submitMapping(e){
    e.preventDefault();
    const id = document.getElementById('mapId').value;
    const recipeSize = document.getElementById('recipeSize').value || '16oz';
    // collect ingredient rows
    const rows = Array.from(document.querySelectorAll('.ingredient-row'));
    const ingredients = [];
    for (const r of rows) {
        const ingId = r.querySelector('.ing-select').value;
        if (!ingId) continue;
        const qty = parseFloat(r.querySelector('.ing-qty').value || '0');
        const stockUnit = r.querySelector('.ing-stock-unit').value || 'unit';
        const unit = r.querySelector('.ing-unit').value || 'pieces';
        if (qty <= 0) { alert('Quantity must be > 0 for each ingredient'); return; }
        ingredients.push({ ingredient_id: ingId, quantity_per_unit: qty, stock_unit: stockUnit, unit: unit });
    }
    if (ingredients.length === 0) { alert('Please add at least one ingredient'); return; }
    if (ingredients.length > 7) { alert('Maximum 7 ingredients allowed'); return; }

    const fd = new FormData();
    fd.append('product_id', document.getElementById('mapProduct').value);
    fd.append('size', recipeSize);
    fd.append('payload', JSON.stringify(ingredients));
    const res = await fetch('api.php?action=save-product-ingredients', { method:'POST', body: fd });
    const json = await res.json();
    if (json.success){ closeMapping(); window._mappingPage = 1; loadMappings(); } else alert(json.error || 'Failed');
}
async function deleteMapping(id){ if(!confirm('Delete mapping?')) return; const fd=new FormData(); fd.append('id', id); const res=await fetch('api.php?action=delete-product-ingredient',{method:'POST', body:fd}); const j=await res.json(); if (j.success) { const currentPage = window._mappingPage || 1; window._mappingPage = currentPage; loadMappings(); } else alert(j.error||'Failed'); }
function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
function escapeJs(s){ return (String(s).replace(/'/g, "\\'").replace(/"/g, '\\"')); }

window._mappingPage = 1;
window._mappingPerPage = 5;
setupMappingFilters();
loadMappings();
</script>

<script>
// ingredients data for row population
const AVAILABLE_INGREDIENTS = [
    <?php foreach($ingredients as $ing): ?>
    { id: '<?php echo $ing['id']; ?>', name: '<?php echo addslashes($ing['name']); ?>', unit: '<?php echo addslashes($ing['unit']); ?>', package_size: '<?php echo addslashes((string)($ing['package_size'] ?? '')); ?>', package_unit: '<?php echo addslashes((string)($ing['package_unit'] ?? '')); ?>' },
    <?php endforeach; ?>
];

function getRecipeUnitForIngredient(ingredient) {
    const stockUnit = normalizeUnitValue(ingredient && ingredient.unit ? ingredient.unit : 'pieces');
    const packageSize = parseFloat((ingredient && ingredient.package_size) ? ingredient.package_size : '0');
    const packageUnit = normalizeUnitValue(ingredient && ingredient.package_unit ? ingredient.package_unit : '');

    if (stockUnit === 'pieces' && packageSize > 0 && packageUnit !== 'pieces') {
        return packageUnit;
    }

    return stockUnit;
}

function createIngredientRow(data){
    const div = document.createElement('div');
    div.className = 'ingredient-row';
    div.style.display = 'flex';
    div.style.gap = '10px';
    div.style.flexWrap = 'wrap';
    div.style.alignItems = 'center';

    const sel = document.createElement('select');
    sel.className = 'form-control ing-select';
    sel.style.flex = '1 1 260px';
    sel.style.minWidth = '260px';
    sel.innerHTML = '<option value="">Select ingredient</option>' + AVAILABLE_INGREDIENTS.map(i=>{
        const packageNote = (i.package_size && i.package_unit) ? ` - ${i.package_size} ${i.package_unit}` : '';
        return `<option value="${i.id}" data-unit="${escapeHtml(i.unit)}" data-package-size="${escapeHtml(i.package_size || '')}" data-package-unit="${escapeHtml(i.package_unit || '')}">${escapeHtml(i.name)} (${escapeHtml(i.unit)}${packageNote})</option>`;
    }).join('');
    if (data && data.ingredient_id) sel.value = data.ingredient_id;
    sel.addEventListener('change', function(){ const opt = sel.options[sel.selectedIndex]; const ingredient = opt ? { unit: opt.dataset.unit || 'pieces', package_size: opt.dataset.packageSize || '', package_unit: opt.dataset.packageUnit || '' } : null; const unit = ingredient ? getRecipeUnitForIngredient(ingredient) : 'pieces'; rowUnit.value = normalizeUnitValue(unit); });

    const qty = document.createElement('input'); qty.type = 'number'; qty.step='0.0001'; qty.className='form-control ing-qty'; qty.style.width='120px'; qty.style.minWidth='120px'; qty.placeholder='Qty'; qty.value = data ? data.quantity_per_unit : '';

    const stockUnit = document.createElement('select'); stockUnit.className='form-control ing-stock-unit'; stockUnit.style.width='130px'; stockUnit.style.minWidth='130px'; stockUnit.innerHTML = `
        <option value="unit">Unit</option>
        <option value="carton">Carton</option>
        <option value="bottle">Bottle</option>
        <option value="box">Box</option>
        <option value="pack">Pack</option>
        <option value="scoop">Scoop</option>
        <option value="serving">Serving</option>
    `;
    if (data && data.stock_unit) stockUnit.value = data.stock_unit;

    const rowUnit = document.createElement('select'); rowUnit.className='form-control ing-unit'; rowUnit.style.width='150px'; rowUnit.style.minWidth='150px'; rowUnit.innerHTML = `
        <option value="liters">Liters (L)</option>
        <option value="milliliters">Milliliters (mL)</option>
        <option value="grams">Grams (g)</option>
        <option value="milligrams">Milligrams (mg)</option>
        <option value="kilograms">Kilograms (kg)</option>
        <option value="pieces">Pieces (pcs)</option>
        <option value="units">Units</option>
    `;
    const ingredient = data && data.ingredient_id ? AVAILABLE_INGREDIENTS.find(item => String(item.id) === String(data.ingredient_id)) : null;
    const mappedUnit = normalizeUnitValue(data && data.unit ? data.unit : '');
    if (ingredient && ingredient.package_size && ingredient.package_unit && (mappedUnit === 'pieces' || mappedUnit === 'units')) {
        rowUnit.value = normalizeUnitValue(getRecipeUnitForIngredient(ingredient));
    } else if (data && data.unit) {
        rowUnit.value = mappedUnit;
    } else if (ingredient) {
        rowUnit.value = normalizeUnitValue(getRecipeUnitForIngredient(ingredient));
    }

    const del = document.createElement('button'); del.type='button'; del.className='btn'; del.style.background='#e53935'; del.style.color='#fff'; del.style.minWidth='88px'; del.innerText='Remove'; del.addEventListener('click', function(){ div.remove(); updateAddBtnState(); });

    div.appendChild(sel);
    div.appendChild(qty);
    div.appendChild(stockUnit);
    div.appendChild(rowUnit);
    div.appendChild(del);
    return div;
}

async function loadRecipeRows(productId, size) {
    const res = await fetch('api.php?action=get-product-ingredients');
    const json = await res.json();
    if (!json.success) { alert('Failed to load mappings'); return; }
    const maps = json.mappings.filter(m => Number(m.product_id) === Number(productId) && String(m.size || '16oz') === String(size || '16oz'));
    const container = document.getElementById('ingredientsRows');
    container.innerHTML = '';
    if (maps.length === 0) {
        addIngredientRow();
    } else {
        for (const m of maps) {
            addIngredientRow({ ingredient_id: m.ingredient_id, quantity_per_unit: m.quantity_per_unit, stock_unit: m.stock_unit, unit: m.unit });
        }
    }
    updateAddBtnState();
}

function addIngredientRow(data){
    const container = document.getElementById('ingredientsRows');
    const count = container.querySelectorAll('.ingredient-row').length;
    if (count >= 7) { alert('Maximum 7 ingredients allowed'); return; }
    container.appendChild(createIngredientRow(data));
    updateAddBtnState();
}

function bindRecipeSelectors(){
    const productSelect = document.getElementById('mapProduct');

    if (productSelect && !productSelect.dataset.bound) {
        productSelect.dataset.bound = '1';
        productSelect.addEventListener('change', async function() {
            if (!this.value) {
                document.getElementById('ingredientsRows').innerHTML = '';
                addIngredientRow();
                return;
            }
            await loadRecipeRows(this.value, document.getElementById('recipeSize') ? document.getElementById('recipeSize').value : '16oz');
        });
    }
}

function updateAddBtnState(){
    const container = document.getElementById('ingredientsRows');
    const count = container.querySelectorAll('.ingredient-row').length;
    document.getElementById('addIngredientBtn').disabled = (count >= 7);
}

// Prepopulate a single empty row for convenience
document.addEventListener('DOMContentLoaded', function(){ addIngredientRow(); updateAddBtnState(); bindRecipeSelectors(); });
</script>

<?php include 'includes/footer.php'; ?>
