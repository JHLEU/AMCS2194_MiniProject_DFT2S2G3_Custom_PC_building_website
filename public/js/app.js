// jQuery Application Helper (requires public/js/vendor/jquery-3.7.1.min.js loaded first)
$(function () {
    // Send the CSRF token with every jQuery AJAX request (needed for POST routes in web.php)
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Escape untrusted text before inserting it as HTML
    function esc(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function formatMoney(value) {
        return Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // 1. Quick-fill login helper chips
    $('.chip[data-login]').on('click', function () {
        $('#login').val($(this).data('login'));
        $('#password').val($(this).data('password') || 'password123');
    });

    // 2. Auto-dismiss alerts after 5 seconds
    setTimeout(function () {
        $('.alert-dismissible').fadeOut(500, function () {
            $(this).remove();
        });
    }, 5000);

    // 3. Table Real-Time Filter / Search
    $('[data-table-search]').each(function () {
        const $table = $($(this).data('table-search'));
        if (!$table.length) return;

        $(this).on('input', function () {
            const query = $(this).val().toLowerCase().trim();
            $table.find('tbody tr').each(function () {
                $(this).toggle($(this).text().toLowerCase().includes(query));
            });
        });
    });

    // 4. Serial Number Single vs Bulk toggle
    const $singleSection = $('#single-sn-section');
    const $bulkSection = $('#bulk-sn-section');

    if ($singleSection.length && $bulkSection.length) {
        $('input[name="mode"]').on('change', function () {
            const isBulk = $(this).val() === 'bulk';
            $singleSection.toggle(!isBulk);
            $bulkSection.toggle(isBulk);
        });
    }

    // 5. Restock Order Dynamic Item Builder
    const $restockForm = $('#restock-order-form');
    const $restockSupplierSelect = $('#restock-supplier-select');
    const $restockItemsContainer = $('#restock-items-table-body');
    const $grandTotal = $('#restock-grand-total');
    let currentSupplierProducts = [];

    function updateGrandTotal() {
        let total = 0;
        $('.item-subtotal-input').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $grandTotal.text(formatMoney(total));
    }

    function loadSupplierProducts(supplierId) {
        const baseUrl = $restockForm.data('supplier-products-url');
        if (!supplierId || !baseUrl) {
            currentSupplierProducts = [];
            return $.Deferred().resolve().promise();
        }

        return $.getJSON(baseUrl, { supplier_id: supplierId })
            .done(function (data) {
                currentSupplierProducts = (data.success && Array.isArray(data.products)) ? data.products : [];
            })
            .fail(function () {
                currentSupplierProducts = [];
            });
    }

    function appendRestockRow(preselectedProductId) {
        const rowIndex = $restockItemsContainer.children().length;

        let productOptionsHtml = '<option value="">-- Choose Hardware Product --</option>';
        if (currentSupplierProducts.length > 0) {
            $.each(currentSupplierProducts, function (_, p) {
                const selectedAttr = Number(p.product_id) === Number(preselectedProductId) ? 'selected' : '';
                productOptionsHtml += `<option value="${esc(p.product_id)}" data-price="${esc(p.agreed_unit_price)}" ${selectedAttr}>${esc(p.product_name)} (${esc(p.product_category)}) - Agreed: RM ${Number(p.agreed_unit_price).toFixed(2)}</option>`;
            });
        } else if ($('#global-products-template').length) {
            productOptionsHtml = $('#global-products-template').html();
        }

        const $row = $(`
            <tr>
                <td>
                    <select name="items[${rowIndex}][product_id]" class="form-input product-select" required>
                        ${productOptionsHtml}
                    </select>
                </td>
                <td style="width: 130px;">
                    <input type="number" name="items[${rowIndex}][quantity]" class="form-input item-qty" min="1" value="1" required>
                </td>
                <td style="width: 170px;">
                    <input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" class="form-input item-cost" min="0" value="0.00" required>
                </td>
                <td style="width: 170px;">
                    <input type="number" step="0.01" class="form-input item-subtotal-input" value="0.00" readonly style="opacity: 0.85; font-weight: 600;">
                </td>
                <td style="width: 50px; text-align: center;">
                    <button type="button" class="btn btn-outline btn-sm remove-row-btn" style="color: var(--danger);" title="Remove Line">&times;</button>
                </td>
            </tr>
        `);

        $restockItemsContainer.append($row);

        const $prodSelect = $row.find('.product-select');
        const $qty = $row.find('.item-qty');
        const $cost = $row.find('.item-cost');
        const $subtotal = $row.find('.item-subtotal-input');

        function applyAgreedPrice() {
            const agreed = $prodSelect.find('option:selected').data('price');
            if (agreed !== undefined && agreed !== '') {
                $cost.val(parseFloat(agreed).toFixed(2));
            }
        }

        function recalcRow() {
            const q = parseInt($qty.val(), 10) || 0;
            const c = parseFloat($cost.val()) || 0;
            $subtotal.val((q * c).toFixed(2));
            updateGrandTotal();
        }

        $prodSelect.on('change', function () {
            applyAgreedPrice();
            recalcRow();
        });
        $qty.add($cost).on('input', recalcRow);
        $row.find('.remove-row-btn').on('click', function () {
            $row.remove();
            updateGrandTotal();
        });

        if ($prodSelect.val()) {
            applyAgreedPrice();
        }
        recalcRow();
    }

    if ($restockSupplierSelect.length && $restockItemsContainer.length) {
        const preselectedProd = parseInt($restockForm.data('preselected-product') || 0, 10);
        loadSupplierProducts($restockSupplierSelect.val()).always(function () {
            appendRestockRow(preselectedProd);
        });

        $restockSupplierSelect.on('change', function () {
            loadSupplierProducts($(this).val());
        });
    }

    $('#add-restock-item-btn').on('click', function () {
        if ($restockItemsContainer.length) appendRestockRow(0);
    });

    // 6. Default cost auto-fill (Serial Number registration & Supplier mapping forms)
    $('#serial-product-select').on('change', function () {
        const defaultCost = $(this).find('option:selected').data('cost');
        const $costInput = $('#batch-cost-input');
        if (defaultCost !== undefined && !$costInput.val()) {
            $costInput.val(parseFloat(defaultCost).toFixed(2));
        }
    });

    $('#mapping-product-select').on('change', function () {
        const defaultCost = $(this).find('option:selected').data('cost');
        if (defaultCost !== undefined) {
            $('#mapping-price-input').val(parseFloat(defaultCost).toFixed(2));
        }
    });

    // 7. ERP Compatibility & Power Test Bench (live AJAX re-evaluation)
    const $compatForm = $('#compatibilityForm');

    if ($compatForm.length) {
        const evaluateUrl = $compatForm.data('evaluate-url');
        const bottleneckUrl = $compatForm.data('bottleneck-url');
        let pendingRequest = 0;

        function renderStatus(status) {
            const map = {
                compatible: ['var(--success)', '✔ Compatible'],
                warning: ['var(--warning)', '⚠ Compatible (Warnings)'],
                incompatible: ['var(--danger)', '✖ Conflict Detected']
            };
            const [color, label] = map[status] || map.incompatible;
            $('#compatStatusVal').html(
                `<div class="stat-val" style="color: ${color}; font-size: 1.35rem;">${label}</div>`
            );
        }

        function renderPower(power) {
            $('#liveTotalWattage').text(power.estimated_total_wattage + 'W');
            $('#liveWattageBreakdown').text(
                `Components: ${power.component_tdp_wattage}W + ${power.base_overhead_wattage}W Base`
            );
            $('#liveRecPsu').text(power.recommended_psu_wattage + 'W+');

            let psuInfo = 'Selected PSU: ' + (power.selected_psu_wattage ? power.selected_psu_wattage + 'W' : 'None');
            if (power.psu_load_percent !== null) {
                psuInfo += ` (${power.psu_load_percent}% load)`;
            }
            $('#livePsuInfo').text(psuInfo);
        }

        function renderPricing(pricing) {
            $('#liveTotalPrice').text('RM ' + formatMoney(pricing.estimated_total_price));
            $('#livePriceInfo').html(
                `${pricing.component_count} components &bull; ${pricing.all_in_stock ? 'All In-Stock' : 'Stock Deficit'}`
            );
        }

        function renderIssues(errors, warnings) {
            let html = '';

            if (!errors.length && !warnings.length) {
                html += `<div class="alert alert-success" style="margin-bottom: 16px;">
                    <strong>✔ All Hardware Compatibility Checks Passed!</strong><br>
                    CPU socket, Motherboard chipset, RAM generation, and PSU wattage headroom are 100% compatible.
                </div>`;
            }

            if (errors.length) {
                html += `<div style="margin-bottom: 16px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--danger); margin-bottom: 8px; text-transform: uppercase;">
                        ❌ Critical Compatibility Conflicts (${errors.length})
                    </div>`;
                $.each(errors, function (_, e) {
                    html += `<div class="alert alert-danger" style="margin-bottom: 8px;"><strong>[${esc(e.code)}]</strong> ${esc(e.message)}</div>`;
                });
                html += '</div>';
            }

            if (warnings.length) {
                html += `<div style="margin-bottom: 16px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--warning); margin-bottom: 8px; text-transform: uppercase;">
                        ⚠️ Advisory Warnings (${warnings.length})
                    </div>`;
                $.each(warnings, function (_, w) {
                    html += `<div class="alert" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); color: #fcd34d; margin-bottom: 8px; padding: 12px; border-radius: 8px;"><strong>[${esc(w.code)}]</strong> ${esc(w.message)}</div>`;
                });
                html += '</div>';
            }

            $('#compatIssues').html(html);
        }

        function renderComponents(components) {
            const rows = $.map(components, function (part) {
                let compat = '';
                if (part.socket_type) compat += `<span class="badge badge-secondary">${esc(part.socket_type)}</span> `;
                if (part.ram_type) compat += `<span class="badge badge-primary">${esc(part.ram_type)}</span>`;
                if (!compat) compat = '<span style="color: var(--text-muted);">—</span>';

                const isPsu = String(part.category).toUpperCase() === 'PSU';
                const tdp = isPsu
                    ? `<strong style="color: var(--info);">${part.tdp_wattage}W Rated</strong>`
                    : `${part.tdp_wattage}W`;

                const stock = part.in_stock_count > 0
                    ? `<span class="badge badge-success">${part.in_stock_count} In-Stock</span>`
                    : '<span class="badge badge-danger">Out of Stock</span>';

                return `<tr>
                    <td><span class="badge badge-info">${esc(part.category)}</span></td>
                    <td style="font-weight: 600;">${esc(part.name)}</td>
                    <td>${compat}</td>
                    <td>${tdp}</td>
                    <td>${stock}</td>
                    <td>RM ${formatMoney(part.selling_price)}</td>
                </tr>`;
            });
            $('#compatComponentsBody').html(rows.join(''));
        }

        function renderBottleneck(report) {
            const $card = $('#bottleneckCard');
            if (!report) {
                $card.hide();
                return;
            }

            const tierColor = report.tier === 'optimal'
                ? 'var(--success)'
                : (report.tier === 'minor' ? 'var(--warning)' : 'var(--danger)');

            $('#bnResolution').text(report.resolution);
            $('#bnPercent').text(report.bottleneck_percent + '%').css('color', tierColor);
            $('#bnTag').text(report.tag);
            $('#bnUtil').text(report.utilization_percent + '%');
            $('#bnScores').text(`CPU ${report.cpu_score} / GPU ${report.gpu_score}`);
            $('#bnPsu').text(report.recommended_psu_wattage + 'W');
            $('#bnAdvice').text(report.advice);

            const games = $.map(report.games_fps, function (g) {
                return `<div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 600; color: #fff;">${esc(g.name)}</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">${esc(g.tag)}</div>
                    </div>
                    <div style="font-size: 1rem; font-weight: 800; color: var(--success);">
                        ${g.fps} <span style="font-size: 0.7rem; color: var(--text-muted);">FPS</span>
                    </div>
                </div>`;
            });
            $('#bnGames').html(games.join(''));
            $card.show();
        }

        function runEvaluation() {
            const requestId = ++pendingRequest;
            const productIds = [];
            $compatForm.find('.component-selector').each(function () {
                if ($(this).val()) productIds.push(parseInt($(this).val(), 10));
            });

            const cpuId = $('#select_cpu_id').val();
            const gpuId = $('#select_gpu_id').val();
            const resolution = $('#select_resolution').val();

            // Keep the URL in sync so a page refresh / shared link reproduces the same build
            history.replaceState(null, '', '?' + $compatForm.serialize());
            $('#compatLoading').show();

            const evaluateReq = $.ajax({
                url: evaluateUrl,
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ product_ids: productIds })
            });

            const bottleneckReq = (cpuId && gpuId)
                ? $.ajax({
                    url: bottleneckUrl,
                    method: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({ cpu_id: parseInt(cpuId, 10), gpu_id: parseInt(gpuId, 10), resolution: resolution })
                })
                : $.Deferred().resolve(null).promise();

            $.when(evaluateReq, bottleneckReq)
                .done(function (evalRes, bnRes) {
                    if (requestId !== pendingRequest) return; // a newer request superseded this one

                    // $.ajax results arrive as [data, status, xhr]; the resolved Deferred passes data directly
                    const evaluation = evalRes[0];
                    const bottleneck = Array.isArray(bnRes) ? bnRes[0] : bnRes;

                    renderStatus(evaluation.status);
                    renderPower(evaluation.power);
                    renderPricing(evaluation.pricing);
                    renderIssues(evaluation.errors, evaluation.warnings);
                    $('#coolingAdvice').text(evaluation.cooling_advice);
                    renderComponents(evaluation.components);
                    renderBottleneck(bottleneck);
                })
                .fail(function () {
                    if (requestId !== pendingRequest) return;
                    $('#compatIssues').html(
                        '<div class="alert alert-danger" style="margin-bottom: 16px;">Could not reach the compatibility engine. Please try again.</div>'
                    );
                })
                .always(function () {
                    if (requestId === pendingRequest) $('#compatLoading').hide();
                });
        }

        $compatForm.on('change', 'select', runEvaluation);
        $compatForm.on('submit', function (e) {
            e.preventDefault();
            runEvaluation();
        });
    }
});
