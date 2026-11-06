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
                                ${p.socket_type ? `<span class="badge badge-info" style="font-size: 0.7rem;">Socket: ${escapeHtml(p.socket_type)}</span>` : ''}
                                ${p.ram_type ? `<span class="badge badge-warning" style="font-size: 0.7rem;">${escapeHtml(p.ram_type)}</span>` : ''}
                                ${p.performance_score > 0 ? `<span class="badge badge-success" style="font-size: 0.7rem;">Rating: ${p.performance_score}/100</span>` : ''}
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
        const clientSubtotal = Object.values(selectedBuild).reduce(
            (sum, comp) => sum + (parseFloat(comp.selling_price) || 0),
            0
        );
        $('#summary-subtotal').text('RM ' + clientSubtotal.toFixed(2));
        $('#summary-item-count').text(productIds.length + ' / 8 Parts Selected');

        // Update 4-parameter performance bars immediately from client selection
        updatePerformanceBars();

        // Toggle checkout button state
        const $checkoutBtn = $('#proceed-checkout-btn');
        if (productIds.length === 0) {
            $checkoutBtn.prop('disabled', true).text('Select Parts to Proceed');
            $('#compatibility-status-box').html(`
                <div style="background: rgba(255,255,255,0.03); border: 1px dashed var(--border-color); border-radius: 8px; padding: 14px; text-align: center; font-size: 0.85rem; color: var(--text-muted);">
                    Select CPU, Motherboard, and RAM to check socket &amp; memory compatibility.
                </div>
            `);
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
        const isCompatible = res.compatible !== undefined ? res.compatible : res.is_compatible;

        if (isCompatible) {
            let warningsHtml = '';
            if (res.warnings && res.warnings.length > 0) {
                warningsHtml = `<ul style="margin: 6px 0 0 0; padding-left: 18px; font-size: 0.8rem; color: #f59e0b;">
                    ${res.warnings.map(w => `<li>${escapeHtml(w.message || w)}</li>`).join('')}
                </ul>`;
            }

            $statusBox.html(`
                <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; padding: 14px;">
                    <div style="color: #10b981; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                        <span>✔</span> Hardware Compatible
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 4px;">
                        CPU &amp; Motherboard socket type and RAM compatibility checks passed.
                    </div>
                    ${warningsHtml}
                </div>
            `);
            $checkoutBtn.prop('disabled', false).text('🛒 Proceed to Checkout →');
        } else {
            const errorsHtml = (res.errors || []).map(e => `<li>${escapeHtml(e.message || e)}</li>`).join('');
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

        if (res.performance) {
            updatePerformanceBars(res.performance);
        }
    }

    /**
     * Map a 0-100 performance score into 4 tiers:
     * Poor (Red), Average (Yellow), Good (Green), Excellent (Cyan)
     */
    function getPerformanceTierInfo(score) {
        const s = parseInt(score, 10) || 0;
        if (s <= 0) {
            return { label: 'None', color: '#64748b', bg: 'rgba(255,255,255,0.06)' };
        }
        if (s >= 85) {
            return { label: 'Excellent', color: '#06b6d4', bg: 'rgba(6, 182, 212, 0.16)' }; // Cyan
        }
        if (s >= 70) {
            return { label: 'Good', color: '#10b981', bg: 'rgba(16, 185, 129, 0.16)' }; // Green
        }
        if (s >= 50) {
            return { label: 'Average', color: '#f59e0b', bg: 'rgba(245, 158, 11, 0.16)' }; // Yellow
        }
        return { label: 'Poor', color: '#ef4444', bg: 'rgba(239, 68, 68, 0.16)' }; // Red
    }

    function renderSingleBar(suffix, score) {
        const s = Math.min(100, Math.max(0, parseInt(score, 10) || 0));
        const tier = getPerformanceTierInfo(s);

        $(`#summary-score-${suffix}`).text(s + '/100').css('color', s > 0 ? tier.color : 'var(--text-muted)');
        $(`#summary-tier-${suffix}`)
            .text(tier.label)
            .css({
                'color': s > 0 ? tier.color : 'var(--text-muted)',
                'background': tier.bg,
                'font-weight': '700'
            });
        $(`#summary-bar-${suffix}`).css({
            'width': s + '%',
            'background-color': tier.color
        });
    }

    function updatePerformanceBars(serverPerf) {
        const cpuScore = serverPerf
            ? (serverPerf.cpu_processing_power || 0)
            : (selectedBuild['CPU'] ? (parseInt(selectedBuild['CPU'].performance_score, 10) || 0) : 0);
        const gpuScore = serverPerf
            ? (serverPerf.gpu_performance || 0)
            : (selectedBuild['GPU'] ? (parseInt(selectedBuild['GPU'].performance_score, 10) || 0) : 0);
        const ramScore = serverPerf
            ? (serverPerf.memory || 0)
            : (selectedBuild['RAM'] ? (parseInt(selectedBuild['RAM'].performance_score, 10) || 0) : 0);
        const storageScore = serverPerf
            ? (serverPerf.storage || 0)
            : (selectedBuild['Storage'] ? (parseInt(selectedBuild['Storage'].performance_score, 10) || 0) : 0);

        renderSingleBar('cpu', cpuScore);
        renderSingleBar('gpu', gpuScore);
        renderSingleBar('ram', ramScore);
        renderSingleBar('storage', storageScore);

        const active = [cpuScore, gpuScore, ramScore, storageScore].filter(v => v > 0);
        const avg = active.length > 0
            ? Math.round(active.reduce((a, b) => a + b, 0) / active.length)
            : 0;
        const avgTier = getPerformanceTierInfo(avg);

        $('#summary-overall-badge')
            .text(avg > 0 ? `${avg} / 100 (${avgTier.label})` : '0 / 100')
            .css('color', avg > 0 ? avgTier.color : 'var(--text-muted)');
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
