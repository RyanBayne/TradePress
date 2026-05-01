jQuery(function ($) {
    if (typeof tradepress_sees_diagnostics === 'undefined') {
        console.error('SEES diagnostics config missing: tradepress_sees_diagnostics');
        return;
    }

    const cardsContainer = $('#tp-sees-symbol-cards');
    const stepsContainer = $('#tp-sees-trace-steps');
    const traceHeader = $('#tp-sees-trace-header');
    const processContainer = $('#tp-sees-trace-process');
    const branchDetailsContainer = $('#tp-sees-branch-details');
    const strategyStackContainer = $('#tp-sees-strategy-stack');

    const traceModeSelect = $('#tp-sees-trace-mode');
    const strategySelect = $('#tp-sees-strategy-select');
    const symbolSelect = $('#tp-sees-selected-symbol');
    const maxSymbolsSelect = $('#tp-sees-max-symbols');
    const refreshIntervalSelect = $('#tp-sees-refresh-interval');

    const refreshNowBtn = $('#tp-sees-refresh-now');
    const startAutoBtn = $('#tp-sees-start-auto');
    const stopAutoBtn = $('#tp-sees-stop-auto');
	const copyJsonBtn = $('#tp-sees-copy-json');
	const copyStatus = $('#tp-sees-copy-status');
	const autoStatus = $('#tp-sees-auto-status');

    let symbols = [];
    let selectedSymbol = '';
    let selectedTraceMode = String(traceModeSelect.val() || 'scoring');
    let selectedStrategyId = '';
    let autoTimer = null;
    let lastTracePayload = null;

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function toNumber(value) {
        const parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function setAutoButtons(isRunning) {
        startAutoBtn.prop('disabled', isRunning);
        stopAutoBtn.prop('disabled', !isRunning);

        if (autoStatus.length) {
            autoStatus.text(
                isRunning
                    ? 'Auto refresh is running every ' + (parseInt(refreshIntervalSelect.val(), 10) / 1000) + 's.'
                    : 'Auto refresh is stopped.'
            );
        }
    }

    function setCopyStatus(message) {
        if (copyStatus.length) {
            copyStatus.text(message || '');
        }
    }

    function setTraceAvailable(hasTrace) {
        copyJsonBtn.prop('disabled', !hasTrace);

        if (!hasTrace) {
            setCopyStatus('');
        }
    }

    function renderCards(data) {
        const maxCards = parseInt(maxSymbolsSelect.val(), 10);
        const rows = data.slice(0, maxCards);

        cardsContainer.empty();

        if (!rows.length) {
            cardsContainer.append('<p>' + tradepress_sees_diagnostics.noDataMessage + '</p>');
            return;
        }

        rows.forEach(function (row) {
            const activeClass = row.symbol === selectedSymbol ? ' is-active' : '';
            const score = toNumber(row.score);
            const change = toNumber(row.change_percent);
            const changeClass = change > 0 ? 'is-up' : (change < 0 ? 'is-down' : 'is-flat');
            const changePrefix = change > 0 ? '+' : '';

            const card = $(
                '<button type="button" class="tp-sees-card' + activeClass + '" data-symbol="' + row.symbol + '">' +
                '<span class="tp-sees-card-symbol">' + escapeHtml(row.symbol) + '</span>' +
                '<span class="tp-sees-card-name">' + escapeHtml(row.name || 'N/A') + '</span>' +
                '<span class="tp-sees-card-score">Score: ' + score.toFixed(2) + '</span>' +
                '<span class="tp-sees-card-change ' + changeClass + '">' + changePrefix + change.toFixed(2) + '%</span>' +
                '</button>'
            );

            cardsContainer.append(card);
        });
    }

    function syncSymbolDropdown(data) {
        const previous = selectedSymbol;

        symbolSelect.empty();
        data.forEach(function (row) {
            symbolSelect.append('<option value="' + escapeHtml(row.symbol) + '">' + escapeHtml(row.symbol) + ' - ' + escapeHtml(row.name || 'N/A') + '</option>');
        });

        if (previous && data.find(function (row) { return row.symbol === previous; })) {
            selectedSymbol = previous;
        } else {
            selectedSymbol = data.length ? data[0].symbol : '';
        }

        symbolSelect.val(selectedSymbol);
    }

    function renderStrategyOptions(options) {
        strategySelect.empty();

        if (!options.length) {
            strategySelect.append('<option value="">No strategy available for selected mode</option>');
            selectedStrategyId = '';
            strategySelect.val('');
            return;
        }

        options.forEach(function (strategy) {
            const componentCount = Number.isFinite(parseInt(strategy.component_count, 10))
                ? parseInt(strategy.component_count, 10)
                : 0;
            const strategyType = strategy.type ? String(strategy.type) : 'strategy';
            const optionLabel = strategy.name + ' - ' + strategyType + ', ' + componentCount + ' components';

            strategySelect.append(
                '<option value="' + strategy.id + '">' +
                escapeHtml(optionLabel) +
                '</option>'
            );
        });

        const currentStillValid = options.find(function (row) {
            return String(row.id) === String(selectedStrategyId);
        });

        if (!currentStillValid) {
            selectedStrategyId = String(options[0].id);
        }

        strategySelect.val(selectedStrategyId);
    }

    function renderProcessTrace(processSteps) {
        processContainer.empty();

        if (!Array.isArray(processSteps) || !processSteps.length) {
            processContainer.append('<p>No process checkpoints available for this trace.</p>');
            return;
        }

        processSteps.forEach(function (processStep, index) {
            const passClass = processStep.passed ? 'is-pass' : 'is-fail';
            const row = $(
                '<div class="tp-sees-process-step ' + passClass + '">' +
                '<span class="tp-sees-process-index">P' + (index + 1) + '</span>' +
                '<span class="tp-sees-process-label">' + escapeHtml(processStep.label) +
                ' <span class="tp-sees-process-code">' + escapeHtml(processStep.code_path || '') + '</span></span>' +
                '<span class="tp-sees-process-status">' + (processStep.passed ? 'Pass' : 'Fail') + '</span>' +
                '</div>'
            );

            processContainer.append(row);
        });
    }

    function renderDecisionBranchDetails(details) {
        branchDetailsContainer.empty();

        if (!Array.isArray(details) || !details.length) {
            branchDetailsContainer.append('<p>No branch details available for this trace.</p>');
            return;
        }

        const rows = details.map(function (detail) {
            const status = String(detail.status || 'info').toLowerCase();
            const cssStatus = ['passed', 'failed', 'warning'].includes(status) ? status : 'info';
            const statusLabel = cssStatus.charAt(0).toUpperCase() + cssStatus.slice(1);

            return (
                '<div class="tp-sees-branch-item is-' + escapeHtml(cssStatus) + '">' +
                '<span class="tp-sees-branch-status">' + escapeHtml(statusLabel) + '</span>' +
                '<strong>' + escapeHtml(detail.gate || 'gate') + '</strong>: ' +
                escapeHtml(detail.reason || '') +
                ' <span class="tp-sees-process-code">' + escapeHtml(detail.code_path || '') + '</span>' +
                '</div>'
            );
        }).join('');

        branchDetailsContainer.html(
            '<h4 class="tp-sees-branch-title">Decision Branch Details</h4>' + rows
        );
    }

    function renderStrategyStack(trace) {
        strategyStackContainer.empty();

        const steps = Array.isArray(trace.steps) ? trace.steps : [];
        if (!steps.length) {
            strategyStackContainer.append('<p>No strategy components are available for stack rendering.</p>');
            return;
        }

        const listItems = steps.map(function (step) {
            const weightPct = (toNumber(step.weight) * 100).toFixed(2);
            return '<li>' + escapeHtml(step.label) + ' (' + escapeHtml(step.component_type || 'component') + ') - weight ' + weightPct + '%</li>';
        }).join('');

        strategyStackContainer.html(
            '<h4 class="tp-sees-stack-title">Strategy Stack</h4>' +
            '<div class="tp-sees-stack-summary">Components: ' + steps.length +
            ' | Passed: ' + (parseInt(trace.passed_count, 10) || 0) +
            ' | Current Score: ' + toNumber(trace.score).toFixed(2) +
            ' | Minimum: ' + toNumber(trace.minimum_threshold).toFixed(2) +
            '</div>' +
            '<ol class="tp-sees-stack-list">' + listItems + '</ol>'
        );
    }

    function renderTrace(trace) {
        const strategyName = trace.strategy_name ? String(trace.strategy_name) : 'None selected';
        const strategyType = trace.strategy_type ? String(trace.strategy_type) : 'n/a';
        const modeLabel = trace.trace_mode === 'trading' ? 'Trading Strategy' : 'Scoring Strategy';
        const decisionState = trace.decision_state === 'continued' ? 'continued' : 'stopped';
        const componentCount = parseInt(trace.component_count, 10) || 0;
        const passedCount = parseInt(trace.passed_count, 10) || 0;
        const warningCount = parseInt(trace.component_warning_count, 10) || 0;
        const maxPossibleScore = toNumber(trace.max_possible_score);
        const scoreValue = toNumber(trace.score);
        const scorePercentOfMax = toNumber(trace.score_percent_of_max);
        const distanceToThreshold = trace.threshold_distance !== undefined
            ? toNumber(trace.threshold_distance)
            : toNumber(trace.distance_to_threshold);
        const distanceLabel = distanceToThreshold >= 0 ? '+' + distanceToThreshold.toFixed(2) : distanceToThreshold.toFixed(2);

        traceHeader.html(
            '<div class="tp-sees-trace-summary is-' + escapeHtml(decisionState) + '">' +
            '<strong>' + escapeHtml(trace.symbol) + '</strong> - ' + escapeHtml(trace.name) + '<br>' +
            'Trace mode: ' + escapeHtml(modeLabel) + ' | Strategy: ' + escapeHtml(strategyName) + ' (' + escapeHtml(strategyType) + ') | Status: ' + escapeHtml(trace.strategy_status || 'n/a') + '<br>' +
            'Strategy ID: ' + escapeHtml(trace.strategy_id || '0') + ' | Source: ' + escapeHtml(trace.strategy_storage || 'n/a') + ' | Components: ' + componentCount + ' | Passed: ' + passedCount + ' | Warnings: ' + warningCount + '<br>' +
            'Industry: ' + escapeHtml(trace.industry) + ' | Score: ' + scoreValue.toFixed(2) + ' / ' + maxPossibleScore.toFixed(2) + ' (' + scorePercentOfMax.toFixed(2) + '%) | Minimum: ' + toNumber(trace.minimum_threshold).toFixed(2) + ' | Distance to threshold: ' + distanceLabel + ' | Decision: <span class="tp-sees-decision">' + escapeHtml(trace.decision) + '</span><br>' +
            'Next function: <code>' + escapeHtml(trace.next_function || 'n/a') + '</code><br>' +
            'Generated: ' + escapeHtml(trace.generatedAt) +
            '</div>'
        );

        renderDecisionBranchDetails(trace.decision_branch_details || []);
        renderProcessTrace(trace.process || []);
        renderStrategyStack(trace);
        lastTracePayload = trace;
        setTraceAvailable(true);
        setCopyStatus('');

        stepsContainer.empty();

        (trace.steps || []).forEach(function (step, index) {
            const hasWarning = Boolean(step.warning);
            const passClass = hasWarning ? 'is-warning' : (step.passed ? 'is-pass' : 'is-fail');
            const statusText = hasWarning ? 'Warning' : (step.passed ? 'Pass' : 'Fail');
            const scoreValue = toNumber(step.score);
            const weightValue = toNumber(step.weight);
            const weightedValue = toNumber(step.weighted_score);
            const formulaText = String(step.formula_text || (weightedValue.toFixed(2) + ' = ' + scoreValue.toFixed(2) + ' x ' + weightValue.toFixed(4)));
            const barWidth = Math.max(0, Math.min(100, scoreValue));
            const warningText = step.warning ? '<div class="tp-sees-step-warning">Warning: ' + escapeHtml(step.warning) + '</div>' : '';
            const row = $(
                '<article class="tp-sees-step ' + passClass + '">' +
                '<header>' +
                '<span class="tp-sees-step-index">Step ' + (index + 1) + '</span>' +
                '<span class="tp-sees-step-label">' + escapeHtml(step.label) + '</span>' +
                '<span class="tp-sees-step-status">' + statusText + '</span>' +
                '</header>' +
                '<div class="tp-sees-step-values">' +
                '<span>Input: ' + escapeHtml(step.input_value) + '</span>' +
                '<span>Score: ' + scoreValue.toFixed(2) + '</span>' +
                '<span>Weight: ' + (weightValue * 100).toFixed(0) + '%</span>' +
                '<span>Weighted: ' + weightedValue.toFixed(2) + '</span>' +
                '<span>Threshold: ' + toNumber(step.threshold).toFixed(2) + '</span>' +
                '<span>Type: ' + escapeHtml(step.component_type || 'component') + '</span>' +
                '</div>' +
                '<div class="tp-sees-step-source">Source: ' + escapeHtml(step.component_source || 'n/a') + '</div>' +
                '<div class="tp-sees-step-code">Code path: ' + escapeHtml(step.code_path || 'n/a') + '</div>' +
                '<div class="tp-sees-step-formula">Math: ' + escapeHtml(formulaText) + '</div>' +
                warningText +
                '<div class="tp-sees-step-next">Next action: ' + escapeHtml(step.next_action || 'Continue pipeline') + '</div>' +
                '<div class="tp-sees-step-bar"><span style="width:' + barWidth + '%"></span></div>' +
                '</article>'
            );

            stepsContainer.append(row);
        });
    }

    function fetchTrace(symbol) {
        if (!symbol) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'tradepress_fetch_sees_diagnostic_trace',
                _ajax_nonce: tradepress_sees_diagnostics.traceNonce,
                symbol: symbol,
                trace_mode: selectedTraceMode,
                strategy_id: selectedStrategyId,
            },
        }).done(function (response) {
            if (response && response.success && response.data) {
                renderTrace(response.data);
            } else {
                lastTracePayload = null;
                setTraceAvailable(false);
                traceHeader.empty();
                processContainer.empty();
                branchDetailsContainer.empty();
                strategyStackContainer.empty();
                stepsContainer.html('<p>' + tradepress_sees_diagnostics.loadErrorMessage + '</p>');
            }
        }).fail(function () {
            lastTracePayload = null;
            setTraceAvailable(false);
            traceHeader.empty();
            processContainer.empty();
            branchDetailsContainer.empty();
            strategyStackContainer.empty();
            stepsContainer.html('<p>' + tradepress_sees_diagnostics.loadErrorMessage + '</p>');
        });
    }

    function copyTracePayload() {
        if (!lastTracePayload) {
            setCopyStatus('No trace payload available.');
            return;
        }

        const payload = JSON.stringify(lastTracePayload, null, 2);

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(payload).then(function () {
                setCopyStatus('Trace JSON copied.');
            }).catch(function () {
                setCopyStatus('Copy failed.');
            });
            return;
        }

        const helper = $('<textarea readonly></textarea>').val(payload).css({ position: 'absolute', left: '-9999px' });
        $('body').append(helper);
        helper[0].select();
        document.execCommand('copy');
        helper.remove();
        setCopyStatus('Trace JSON copied.');
    }

    function fetchStrategyOptions(callback) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'tradepress_fetch_sees_strategy_options',
                _ajax_nonce: tradepress_sees_diagnostics.traceNonce,
                trace_mode: selectedTraceMode,
            },
        }).done(function (response) {
            if (response && response.success && Array.isArray(response.data)) {
                renderStrategyOptions(response.data);
            } else {
                renderStrategyOptions([]);
            }

            if (typeof callback === 'function') {
                callback();
            }
        }).fail(function () {
            renderStrategyOptions([]);
            if (typeof callback === 'function') {
                callback();
            }
        });
    }

    function fetchCardsAndSync() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'tradepress_fetch_sees_demo_data',
                _ajax_nonce: tradepress_sees_diagnostics.listNonce,
                trace_mode: selectedTraceMode,
                strategy_id: selectedStrategyId,
            },
        }).done(function (response) {
            if (!(response && response.success && response.data)) {
                cardsContainer.html('<p>' + tradepress_sees_diagnostics.loadErrorMessage + '</p>');
                return;
            }

            symbols = response.data.slice().sort(function (a, b) {
                return parseFloat(b.score) - parseFloat(a.score);
            });

            syncSymbolDropdown(symbols);
            renderCards(symbols);
            fetchTrace(selectedSymbol);
        }).fail(function () {
            cardsContainer.html('<p>' + tradepress_sees_diagnostics.loadErrorMessage + '</p>');
        });
    }

    function startAuto() {
		let interval = parseInt(refreshIntervalSelect.val(), 10);
		if (Number.isNaN(interval) || interval < 1000) {
			interval = 10000;
		}

        stopAuto();
		fetchCardsAndSync();
        autoTimer = setInterval(function () {
            fetchCardsAndSync();
        }, interval);

        setAutoButtons(true);
    }

    function stopAuto() {
        if (autoTimer) {
            clearInterval(autoTimer);
            autoTimer = null;
        }

        setAutoButtons(false);
    }

    cardsContainer.on('click', '.tp-sees-card', function () {
        selectedSymbol = String($(this).data('symbol'));
        symbolSelect.val(selectedSymbol);
        renderCards(symbols);
        fetchTrace(selectedSymbol);
    });

    symbolSelect.on('change', function () {
        selectedSymbol = String($(this).val() || '');
        renderCards(symbols);
        fetchTrace(selectedSymbol);
    });

    traceModeSelect.on('change', function () {
        selectedTraceMode = String($(this).val() || 'scoring');
        fetchStrategyOptions(function () {
            fetchTrace(selectedSymbol);
        });
    });

    strategySelect.on('change', function () {
        selectedStrategyId = String($(this).val() || '');
        fetchTrace(selectedSymbol);
    });

    refreshNowBtn.on('click', function () {
        fetchCardsAndSync();
    });

    copyJsonBtn.on('click', function () {
        copyTracePayload();
    });

    startAutoBtn.on('click', startAuto);
    stopAutoBtn.on('click', stopAuto);

    maxSymbolsSelect.on('change', function () {
        renderCards(symbols);
    });

    refreshIntervalSelect.on('change', function () {
        if (autoTimer) {
            startAuto();
        }
    });

    setAutoButtons(false);
    setTraceAvailable(false);
    fetchStrategyOptions(function () {
        fetchCardsAndSync();
    });
});
