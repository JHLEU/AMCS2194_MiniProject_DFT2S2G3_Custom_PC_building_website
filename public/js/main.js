/**
 * Custom PC ERP - Main Frontend JavaScript
 * Vanilla JavaScript (Zero external dependencies)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 6000);
    });

    // 2. Table Real-Time Filter / Search
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        const targetTableSelector = input.getAttribute('data-table-search');
        const targetTable = document.querySelector(targetTableSelector);
        if (!targetTable) return;

        input.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const rows = targetTable.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    // 3. Serial Number Single vs Bulk toggle
    const snModeRadios = document.querySelectorAll('input[name="mode"]');
    const singleSection = document.getElementById('single-sn-section');
    const bulkSection = document.getElementById('bulk-sn-section');

    if (snModeRadios.length && singleSection && bulkSection) {
        snModeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'bulk') {
                    singleSection.style.display = 'none';
                    bulkSection.style.display = 'block';
                } else {
                    singleSection.style.display = 'block';
                    bulkSection.style.display = 'none';
                }
            });
        });
    }

    // 4. Restock Order Dynamic Item Builder
    const restockSupplierSelect = document.getElementById('restock-supplier-select');
    const restockItemsContainer = document.getElementById('restock-items-table-body');
    const addItemBtn = document.getElementById('add-restock-item-btn');
    const grandTotalSpan = document.getElementById('restock-grand-total');

    let currentSupplierProducts = [];

    // Helper to calculate order grand total
    function updateGrandTotal() {
        if (!grandTotalSpan) return;
        let total = 0.0;
        const subtotalInputs = document.querySelectorAll('.item-subtotal-input');
        subtotalInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        grandTotalSpan.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Load supplier products via AJAX
    function loadSupplierProducts(supplierId) {
        if (!supplierId) {
            currentSupplierProducts = [];
            return;
        }

        fetch(`index.php?route=restock/api-supplier-products&supplier_id=${supplierId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && Array.isArray(data.products)) {
                    currentSupplierProducts = data.products;
                } else {
                    currentSupplierProducts = [];
                }
            })
            .catch(err => {
                console.error('Failed to fetch supplier products:', err);
                currentSupplierProducts = [];
            });
    }

    if (restockSupplierSelect) {
        loadSupplierProducts(restockSupplierSelect.value);
        restockSupplierSelect.addEventListener('change', (e) => {
            loadSupplierProducts(e.target.value);
        });
    }

    // Add new order line item row
    if (addItemBtn && restockItemsContainer) {
        addItemBtn.addEventListener('click', () => {
            const rowIndex = restockItemsContainer.children.length;
            const tr = document.createElement('tr');

            // Product options from currentSupplierProducts or fallback
            let productOptionsHtml = '<option value="">-- Choose Hardware Product --</option>';
            if (currentSupplierProducts.length > 0) {
                currentSupplierProducts.forEach(p => {
                    productOptionsHtml += `<option value="${p.product_id}" data-price="${p.agreed_unit_price}">${p.product_name} (${p.product_category}) - Agreed: $${p.agreed_unit_price}</option>`;
                });
            } else {
                const globalProductSelect = document.getElementById('global-products-template');
                if (globalProductSelect) {
                    productOptionsHtml = globalProductSelect.innerHTML;
                }
            }

            tr.innerHTML = `
                <td>
                    <select name="items[${rowIndex}][product_id]" class="form-select product-select" required>
                        ${productOptionsHtml}
                    </select>
                </td>
                <td style="width: 130px;">
                    <input type="number" name="items[${rowIndex}][quantity]" class="form-control item-qty" min="1" value="1" required>
                </td>
                <td style="width: 160px;">
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span>$</span>
                        <input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" class="form-control item-cost" min="0" value="0.00" required>
                    </div>
                </td>
                <td style="width: 160px;">
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <span>$</span>
                        <input type="number" step="0.01" class="form-control item-subtotal-input" value="0.00" readonly style="background:#f1f5f9; font-weight:600;">
                    </div>
                </td>
                <td style="width: 50px; text-align: center;">
                    <button type="button" class="btn btn-outline btn-sm remove-row-btn" style="color:#ef4444;" title="Remove Line">&times;</button>
                </td>
            `;

            restockItemsContainer.appendChild(tr);

            const prodSelect = tr.querySelector('.product-select');
            const qtyInput = tr.querySelector('.item-qty');
            const costInput = tr.querySelector('.item-cost');
            const subtotalInput = tr.querySelector('.item-subtotal-input');
            const removeBtn = tr.querySelector('.remove-row-btn');

            function recalcRow() {
                const q = parseInt(qtyInput.value) || 0;
                const c = parseFloat(costInput.value) || 0;
                subtotalInput.value = (q * c).toFixed(2);
                updateGrandTotal();
            }

            prodSelect.addEventListener('change', () => {
                const selectedOpt = prodSelect.options[prodSelect.selectedIndex];
                const agreed = selectedOpt.getAttribute('data-price');
                if (agreed) {
                    costInput.value = parseFloat(agreed).toFixed(2);
                }
                recalcRow();
            });

            qtyInput.addEventListener('input', recalcRow);
            costInput.addEventListener('input', recalcRow);

            removeBtn.addEventListener('click', () => {
                tr.remove();
                updateGrandTotal();
            });

            recalcRow();
        });
    }

    // 5. Confirm helper for actions
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const message = btn.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
