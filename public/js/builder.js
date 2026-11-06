/**
 * PC Builder Interactive Configurator Script
 * Powered by Custom PC Compatibility & Power Estimation Engine
 */

(function ($) {
    'use strict';

    // Global state
    const selectedBuild = {};
    let activeSlotCategory = null;

    $(document).ready(function () {
        // Initialize preselected products embedded in DOM
        $('.slot-card').each(function () {
            const category = $(this).data('category');
            const preselectedId = $(this).data('preselected-id');

            if (preselectedId && window.catalogData && window.catalogData[category]) {
                const product = window.catalogData[category].find(p => p.id === parseInt(preselectedId, 10));
                if (product) {
                    setComponent(category, product, false);
                }
            }
        });

        // Initial compatibility check
        evaluateBuild();

        // Event: Click to open component picker modal
        $(document).on('click', '.open-picker-btn, .change-component-btn', function () {
            const category = $(this).closest('.slot-card').data('category');
            openPickerModal(category);
        });

        // Event: Click to remove a component
        $(document).on('click', '.remove-component-btn', function (e) {
            e.stopPropagation();
            const category = $(this).closest('.slot-card').data('category');
            removeComponent(category);
        });

        // Event: Select component inside modal
        $(document).on('click', '.select-product-btn', function () {
            const productId = parseInt($(this).data('product-id'), 10);
            const category = $(this).data('category');

            if (window.catalogData && window.catalogData[category]) {
                const product = window.catalogData[category].find(p => p.id === productId);
                if (product) {
                    setComponent(category, product, true);
                    closePickerModal();
                }
            }
        });

        // Event: Filter products inside modal search
        $('#modal-search-input').on('input', function () {
            const query = $(this).val().toLowerCase().trim();
            $('.modal-product-card').each(function () {
                const name = $(this).data('name').toLowerCase();
                const brand = ($(this).data('brand') || '').toLowerCase();
                const specs = ($(this).data('specs') || '').toLowerCase();

                if (name.includes(query) || brand.includes(query) || specs.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Event: Close modal
        $('.close-modal-btn, .modal-backdrop').on('click', function () {
            closePickerModal();
        });

        // Event: Clear entire build
        $('#clear-build-btn').on('click', function () {
            if (confirm('Clear all selected components from this build?')) {
                Object.keys(selectedBuild).forEach(cat => {
                    removeComponent(cat, false);
                });
                evaluateBuild();
            }
        });
    });

    function setComponent(category, product, triggerEvaluate) {
        selectedBuild[category] = product;

        const $slot = $(`.slot-card[data-category="${category}"]`);
        $slot.addClass('has-selection');

        $slot.find('.empty-slot-state').hide();
        $slot.find('.selected-slot-state').show();

        $slot.find('.item-title').text(product.name);
        $slot.find('.item-brand').text(product.brand || 'Genuine');
        $slot.find('.item-specs').text(product.specs || '');
        $slot.find('.item-price').text('RM ' + parseFloat(product.selling_price).toFixed(2));

        if (triggerEvaluate !== false) {
            evaluateBuild();
        }
    }

    function removeComponent(category, triggerEvaluate) {
        delete selectedBuild[category];

        const $slot = $(`.slot-card[data-category="${category}"]`);
        $slot.removeClass('has-selection');

        $slot.find('.selected-slot-state').hide();
        $slot.find('.empty-slot-state').show();

        if (triggerEvaluate !== false) {
            evaluateBuild();
        }
    }

    function openPickerModal(category) {
        activeSlotCategory = category;
        $('#modal-category-title').text('Choose ' + category);
        $('#modal-search-input').val('');

        const $list = $('#modal-product-list');
        $list.empty();

        const products = (window.catalogData && window.catalogData[category]) ? window.catalogData[category] : [];

        if (products.length === 0) {
            $list.html('<div style="text-align: center; padding: 40px; color: var(--text-muted);">No products currently available in this category.</div>');
        } else {
            products.forEach(p => {
                const priceFmt = 'RM ' + parseFloat(p.selling_price).toFixed(2);
                const isSelected = selectedBuild[category] && selectedBuild[category].id === p.id;

                const cardHtml = `
                    <div class="modal-product-card ${isSelected ? 'selected' : ''}" 
                         data-name="${escapeHtml(p.name)}" 
                         data-brand="${escapeHtml(p.brand || '')}"
                         data-specs="${escapeHtml(p.specs || '')}">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <span class="badge badge-secondary" style="font-size: 0.7rem;">${escapeHtml(p.brand || 'Component')}</span>
                                ${p.socket_type ? `<span class="badge badge-info" style="font-size: 0.7rem;">${p.socket_type}</span>` : ''}
                                ${p.ram_type ? `<span class="badge badge-warning" style="font-size: 0.7rem;">${p.ram_type}</span>` : ''}
                                ${p.tdp_wattage ? `<span class="badge badge-secondary" style="font-size: 0.7rem;">${p.tdp_wattage}W TDP</span>` : ''}
                            </div>
                            <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px 0;">${escapeHtml(p.name)}</h4>
                            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0; line-height: 1.4;">${escapeHtml(p.specs || '')}</p>
                        </div>
                        <div style="text-align: right; min-width: 140px; margin-left: 16px;">
                            <div style="font-size: 1.15rem; font-weight: 700; color: var(--accent); margin-bottom: 8px;">${priceFmt}</div>
                            <button type="button" class="btn btn-sm ${isSelected ? 'btn-secondary' : 'btn-primary'} select-product-btn" 
                                    data-product-id="${p.id}" data-category="${escapeHtml(category)}">
                                ${isSelected ? '✔ Selected' : '+ Select Part'}
                            </button>
                        </div>
                    </div>
                `;
                $list.append(cardHtml);
            });
        }

        $('#component-picker-modal').fadeIn(150);
    }

    function closePickerModal() {
        $('#component-picker-modal').fadeOut(150);
        activeSlotCategory = null;
    }

    function evaluateBuild() {
        const productIds = Object.values(selectedBuild).map(p => p.id);

        // Update hidden inputs in checkout form
        const $formContainer = $('#checkout-product-inputs');
        $formContainer.empty();
        productIds.forEach(id => {
            $formContainer.append(`<input type="hidden" name="product_ids[]" value="${id}">`);
        });

        // Calculate client-side subtotal immediately
        let clientSubtotal = 0;
        productIds.forEach(id => {
            const comp = Object.values(selectedBuild).find(p => p.id === id);
            if (comp) clientSubtotal += parseFloat(comp.selling_price);
        });
        $('#summary-subtotal').text('RM ' + clientSubtotal.toFixed(2));
        $('#summary-item-count').text(productIds.length + ' / 8 Parts Selected');

        // Toggle checkout button state
        const $checkoutBtn = $('#proceed-checkout-btn');
        if (productIds.length === 0) {
            $checkoutBtn.prop('disabled', true).text('Select Parts to Proceed');
            $('#compatibility-status-box').html(`
                <div style="background: rgba(255,255,255,0.03); border: 1px dashed var(--border-color); border-radius: 8px; padding: 14px; text-align: center; font-size: 0.85rem; color: var(--text-muted);">
                    Select components on the left to activate real-time compatibility checks.
                </div>
            `);
            $('#psu-meter-container').hide();
            return;
        }

        $checkoutBtn.prop('disabled', false).text('🛒 Proceed to Checkout →');

        // AJAX to compatibility engine
        $.ajax({
            url: window.appRoutes.compatibilityEvaluate,
            type: 'POST',
            data: JSON.stringify({ product_ids: productIds }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                renderEvaluationResults(res);
            },
            error: function () {
                console.warn('Compatibility evaluation API unavailable; relying on client estimates.');
            }
        });
    }

    function renderEvaluationResults(res) {
        const $statusBox = $('#compatibility-status-box');
        const $checkoutBtn = $('#proceed-checkout-btn');

        if (res.is_compatible) {
            let warningsHtml = '';
            if (res.warnings && res.warnings.length > 0) {
                warningsHtml = `<ul style="margin: 6px 0 0 0; padding-left: 18px; font-size: 0.8rem; color: #f59e0b;">
                    ${res.warnings.map(w => `<li>${escapeHtml(w)}</li>`).join('')}
                </ul>`;
            }

            $statusBox.html(`
                <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; padding: 14px;">
                    <div style="color: #10b981; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                        <span>✔</span> 100% Hardware Compatible
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 4px;">
                        All sockets, memory types, and power dimensions align.
                    </div>
                    ${warningsHtml}
                </div>
            `);
            $checkoutBtn.prop('disabled', false);
        } else {
            const errorsHtml = (res.errors || []).map(e => `<li>${escapeHtml(e)}</li>`).join('');
            $statusBox.html(`
                <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 8px; padding: 14px;">
                    <div style="color: #ef4444; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                        <span>⚠️</span> Incompatible Hardware Detected
                    </div>
                    <ul style="margin: 8px 0 0 0; padding-left: 18px; font-size: 0.85rem; color: #fca5a5; line-height: 1.4;">
                        ${errorsHtml}
                    </ul>
                </div>
            `);
            $checkoutBtn.prop('disabled', true).text('⚠️ Fix Incompatibilities to Checkout');
        }

        // Render PSU Wattage Headroom Bar
        $('#psu-meter-container').show();
        $('#meter-tdp').text((res.total_tdp || 0) + ' W');
        $('#meter-recommended-psu').text((res.recommended_psu_wattage || 0) + ' W');

        const psuWatts = res.psu_wattage || 0;
        const headroomPct = res.psu_headroom_percent || 0;
        $('#meter-selected-psu').text(psuWatts > 0 ? psuWatts + ' W' : 'Not Selected');

        let barColor = '#10b981'; // Green (Healthy)
        let statusBadge = '<span class="badge badge-success">Healthy Headroom</span>';

        if (res.psu_status === 'insufficient') {
            barColor = '#ef4444'; // Red
            statusBadge = '<span class="badge badge-danger">Underpowered PSU</span>';
        } else if (res.psu_status === 'tight') {
            barColor = '#f59e0b'; // Yellow
            statusBadge = '<span class="badge badge-warning">Tight Headroom</span>';
        } else if (psuWatts === 0) {
            barColor = '#64748b';
            statusBadge = '<span class="badge badge-secondary">Select a PSU</span>';
        }

        const barWidth = Math.min(100, Math.max(5, headroomPct));
        $('#psu-headroom-bar').css({
            'width': barWidth + '%',
            'background-color': barColor
        });
        $('#psu-status-badge').html(statusBadge);
        $('#psu-headroom-pct').text(psuWatts > 0 ? headroomPct + '% Buffer' : '-');
    }

    function escapeHtml(string) {
        return String(string).replace(/[&<>"'`=\/]/g, function (s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                '=': '&#x3D;'
            }[s];
        });
    }

})(jQuery);
