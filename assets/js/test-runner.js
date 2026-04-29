jQuery(document).ready(function($) {
    var i18n = (tradePressTests && tradePressTests.i18n) ? tradePressTests.i18n : {};

    function t(key, fallback) {
        return Object.prototype.hasOwnProperty.call(i18n, key) ? i18n[key] : fallback;
    }

    function initUICrawl() {
        var payloadNode = document.getElementById('tradepress-ui-crawl-data');
        if (!payloadNode) {
            return;
        }

        var payloadRaw = payloadNode.getAttribute('data-ui-crawl') || '';
        var payload = {};
        try {
            payload = JSON.parse(payloadRaw);
        } catch (e) {
            payload = {};
        }

        var seedUrls = Array.isArray(payload.seeds) ? payload.seeds : [];
        var config = payload.config && typeof payload.config === 'object' ? payload.config : {
            ajax_url: (typeof ajaxurl !== 'undefined') ? ajaxurl : '',
            nonce: ''
        };

        function decodeHtmlEntities(input) {
            var txt = document.createElement('textarea');
            txt.innerHTML = input;
            return txt.value;
        }

        function toAbsoluteUrl(input) {
            try {
                return new URL(input, window.location.origin).toString();
            } catch (e) {
                return null;
            }
        }

        function normalizeAdminUrl(url) {
            if (!url) {
                return null;
            }

            var absolute = toAbsoluteUrl(url);
            if (!absolute) {
                return null;
            }

            if (absolute.indexOf('/wp-admin/admin.php?page=') === -1) {
                return null;
            }

            return absolute;
        }

        function shouldSkipUrl(url) {
            if (url.indexOf('page=tradepress-tests&tab=ui_crawl') !== -1) {
                return true;
            }
            if (url.indexOf('tab=ui_library') !== -1 || url.indexOf('tab=dark_ui_library') !== -1) {
                return true;
            }
            return false;
        }

        function detectTextSignals(html) {
            var signals = [];
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var nodeList = doc.querySelectorAll('script, style, noscript');

            nodeList.forEach(function(node) {
                node.remove();
            });

            var text = doc.body ? doc.body.textContent : '';
            var checks = [
                { key: 'Fatal error', regex: /Fatal error:.*\.php/i },
                { key: 'Warning', regex: /Warning:.*\.php/i },
                { key: 'Notice', regex: /Notice:.*\.php/i },
                { key: 'Deprecated', regex: /Deprecated:.*\.php/i },
                { key: 'Uncaught Error', regex: /Uncaught\s+\w*Error/i }
            ];

            checks.forEach(function(check) {
                if (check.regex.test(text)) {
                    signals.push(check.key);
                }
            });

            return signals;
        }

        function detectDomSignals(html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var signals = [];

            var errorNoticeCount = doc.querySelectorAll('.notice.notice-error, .wp-die-message').length;
            if (errorNoticeCount > 0) {
                signals.push('Error notice elements: ' + errorNoticeCount);
            }

            var dbError = doc.querySelector('#error-page, .db-error, .wp-die-message');
            if (dbError) {
                signals.push('Critical error container detected');
            }

            return signals;
        }

        async function fetchPage(url) {
            var response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin'
            });

            var html = await response.text();
            return {
                status: response.status,
                ok: response.ok,
                html: html
            };
        }

        async function discoverTabTargets(urls) {
            var discovered = [];
            var seen = new Set();

            for (var i = 0; i < urls.length; i++) {
                var seed = normalizeAdminUrl(urls[i]);
                if (!seed || shouldSkipUrl(seed)) {
                    continue;
                }

                if (!seen.has(seed)) {
                    seen.add(seed);
                    discovered.push(seed);
                }

                try {
                    var seedPage = await fetchPage(seed);
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(seedPage.html, 'text/html');
                    var links = doc.querySelectorAll('a.nav-tab[href]');

                    links.forEach(function(link) {
                        var href = decodeHtmlEntities(link.getAttribute('href') || '');
                        var normalized = normalizeAdminUrl(href);
                        if (normalized && !shouldSkipUrl(normalized) && !seen.has(normalized)) {
                            seen.add(normalized);
                            discovered.push(normalized);
                        }
                    });
                } catch (e) {
                    // Continue with the remaining URLs.
                }
            }

            return discovered;
        }

        function renderSummary(targetNode, summary) {
            var html = '';
            html += '<div class="notice notice-info"><p>';
            html += '<strong>Total URLs:</strong> ' + summary.total + ' | ';
            html += '<strong>Pass:</strong> ' + summary.pass + ' | ';
            html += '<strong>Fail:</strong> ' + summary.fail;
            html += '</p></div>';
            targetNode.innerHTML = html;
        }

        function appendResultRow(targetNode, result) {
            var rowClass = result.failed ? 'test-result failed' : 'test-result passed';
            var html = '<div class="' + rowClass + '">';
            html += '<h4>' + result.url + '</h4>';
            html += '<p><strong>Status:</strong> ' + result.status + '</p>';

            if (result.signals.length > 0) {
                html += '<p><strong>Signals:</strong> ' + result.signals.join(', ') + '</p>';
            } else {
                html += '<p><strong>Signals:</strong> none</p>';
            }

            if (result.errorMessage) {
                html += '<p><strong>Fetch Error:</strong> ' + result.errorMessage + '</p>';
            }

            html += '</div>';
            targetNode.insertAdjacentHTML('beforeend', html);
        }

        async function logFailure(payload) {
            if (!config.ajax_url || !config.nonce) {
                return;
            }

            var form = new FormData();
            form.append('action', 'tradepress_ui_crawl_log');
            form.append('nonce', config.nonce);
            form.append('url', payload.url || '');
            form.append('status', payload.status || '');
            form.append('signals', JSON.stringify(payload.signals || []));
            form.append('error_message', payload.errorMessage || '');

            try {
                await fetch(config.ajax_url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: form
                });
            } catch (e) {
                // Logging failures should not block crawling.
            }
        }

        async function runCrawl() {
            var runBtn = document.getElementById('run-ui-crawl');
            var summaryNode = document.getElementById('ui-crawl-summary');
            var resultsNode = document.getElementById('ui-crawl-results');

            if (!runBtn || !summaryNode || !resultsNode) {
                return;
            }

            runBtn.disabled = true;
            resultsNode.innerHTML = '';
            summaryNode.innerHTML = '<div class="notice notice-info"><p>Discovering tab URLs...</p></div>';

            var targets = await discoverTabTargets(seedUrls);
            var summary = {
                total: targets.length,
                pass: 0,
                fail: 0
            };

            renderSummary(summaryNode, summary);

            for (var i = 0; i < targets.length; i++) {
                var url = targets[i];
                var status = '0';
                var failed = false;
                var signals = [];
                var errorMessage = '';

                try {
                    var result = await fetchPage(url);
                    status = String(result.status);

                    if (!result.ok) {
                        failed = true;
                        signals.push('HTTP status not OK');
                    }

                    signals = signals
                        .concat(detectTextSignals(result.html))
                        .concat(detectDomSignals(result.html));

                    if (signals.length > 0) {
                        failed = true;
                    }
                } catch (e) {
                    failed = true;
                    errorMessage = e && e.message ? e.message : 'Unknown fetch error';
                }

                if (failed) {
                    summary.fail++;
                    await logFailure({
                        url: url,
                        status: status,
                        signals: signals,
                        errorMessage: errorMessage
                    });
                } else {
                    summary.pass++;
                }

                appendResultRow(resultsNode, {
                    url: url,
                    status: status,
                    signals: signals,
                    failed: failed,
                    errorMessage: errorMessage
                });

                renderSummary(summaryNode, summary);
            }

            runBtn.disabled = false;
        }

        $(document).on('click', '#run-ui-crawl', function(event) {
            event.preventDefault();
            runCrawl();
        });

        $(document).on('click', '#clear-ui-crawl', function(event) {
            event.preventDefault();
            var summaryNode = document.getElementById('ui-crawl-summary');
            var resultsNode = document.getElementById('ui-crawl-results');
            if (summaryNode) {
                summaryNode.innerHTML = '';
            }
            if (resultsNode) {
                resultsNode.innerHTML = '';
            }
        });
    }

    // Run specific test
    $('.run-test').click(function() {
        var button = $(this);
        var testId = button.data('test-id');
        var row = button.closest('tr');
        
        button.prop('disabled', true).text(t('running', 'Running...'));
        
        $.ajax({
            url: tradePressTests.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_run_tests',
                test_id: testId,
                nonce: tradePressTests.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayTestResult(row, response.data);
                } else {
                    alert(response.data || t('error', 'Error'));
                }
            },
            error: function() {
                alert(t('error', 'Error'));
            },
            complete: function() {
                button.prop('disabled', false).text(t('run', 'Run'));
            }
        });
    });

    $('#discover-tests').click(function() {
        var button = $(this);
        var resultNode = $('#discover-tests-result');

        button.prop('disabled', true).text(t('discovering', 'Discovering tests...'));
        resultNode.html('');

        $.ajax({
            url: tradePressTests.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_discover_tests',
                nonce: tradePressTests.nonce
            },
            success: function(response) {
                if (!response.success) {
                    resultNode.html('<div class="notice notice-error"><p>' + (response.data || t('error', 'Error')) + '</p></div>');
                    return;
                }

                var summary = response.data || {};
                var errors = Array.isArray(summary.errors) ? summary.errors : [];
                var html = '<div class="notice notice-success"><p>' +
                    t('discoveryComplete', 'Discovery complete. Reloading results...') +
                    ' Registered: ' + (summary.registered || 0) +
                    ', Updated: ' + (summary.updated || 0) +
                    ', Scanned: ' + (summary.files_scanned || 0) +
                    '.</p></div>';

                if (errors.length > 0) {
                    html += '<div class="notice notice-warning"><p>' + t('error', 'Error') + ': ' + errors.join(' | ') + '</p></div>';
                }

                resultNode.html(html);
                window.setTimeout(function() {
                    window.location.reload();
                }, 800);
            },
            error: function() {
                resultNode.html('<div class="notice notice-error"><p>' + t('ajaxError', 'AJAX request failed') + '</p></div>');
            },
            complete: function() {
                button.prop('disabled', false).text(t('discoverTests', 'Discover Tests'));
            }
        });
    });

    // Phase 3 specific handlers
    $('#run-phase3-tests').click(function() {
        var button = $(this);
        button.prop('disabled', true).text(t('running', 'Running...'));
        
        $.ajax({
            url: tradePressTests.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_run_tests',
                test_suite: 'phase3',
                nonce: tradePressTests.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayPhase3Results(response.data);
                } else {
                    $('#test-results').html(
                        '<div class="notice notice-error"><p>' + 
                        t('error', 'Error') + ': ' + response.data + 
                        '</p></div>'
                    );
                }
            },
            error: function() {
                $('#test-results').html(
                    '<div class="notice notice-error"><p>' + 
                    t('ajaxError', 'AJAX request failed') + 
                    '</p></div>'
                );
            },
            complete: function() {
                button.prop('disabled', false).text(t('runPhase3', 'Run Phase 3 Tests'));
            }
        });
    });
    
    $('#clear-test-results').click(function() {
        $('#test-results').html(
            '<p><em>' + t('resultsCleared', 'Test results cleared.') + '</em></p>'
        );
    });
    
    function displayTestResult(row, result) {
        row.find('.test-status').html(getStatusBadge(result.status));
        row.find('.test-last-run').text('just now');
        row.find('.test-success-rate').text(
            result.success_rate ? Math.round(result.success_rate * 10) / 10 + '%' : '-'
        );
    }
    
    function getStatusBadge(status) {
        var statusClasses = {
            active: 'status-active',
            passed: 'status-passed',
            failed: 'status-failed',
            error: 'status-error',
            pending: 'status-pending'
        };
        
        var className = statusClasses[status] || 'status-unknown';
        return '<span class="status-badge ' + className + '">' + status + '</span>';
    }
    
    function displayPhase3Results(results) {
        var html = '<h2>' + t('testResults', 'Test Results') + '</h2>';
        
        // Overall status
        html += '<div class="test-result ' + results.overall_status + '">';
        html += '<h3>' + t('overallStatus', 'Overall Status') + ': ' + results.overall_status.toUpperCase() + '</h3>';
        html += '<p>' + t('executionTime', 'Execution Time') + ': ' + results.execution_time + '</p>';
        html += '<p>' + t('requirementsSatisfied', 'Requirements Satisfied') + ': ' + results.requirements_satisfied.length + '</p>';
        html += '</div>';
        
        // Individual tests
        html += '<h3>' + t('individualResults', 'Individual Test Results') + '</h3>';
        results.tests.forEach(function(test) {
            html += '<div class="test-result ' + test.status + '">';
            html += '<h4>' + test.name + ' (' + test.status.toUpperCase() + ')</h4>';
            html += '<p><strong>' + t('requirement', 'Requirement') + ':</strong> ' + test.requirement + '</p>';
            html += '<p><strong>' + t('executionTime', 'Execution Time') + ':</strong> ' + test.execution_time_ms + 'ms</p>';
            
            if (test.details && test.details.length > 0) {
                html += '<div class="test-details">';
                test.details.forEach(function(detail) {
                    html += detail + '<br>';
                });
                html += '</div>';
            }
            html += '</div>';
        });
        
        // Performance summary
        if (results.performance_summary) {
            html += '<div class="performance-summary">';
            html += '<h3>' + t('performanceSummary', 'Performance Summary') + '</h3>';
            html += '<p><strong>' + t('apiCallsSaved', 'API Calls Saved') + ':</strong> ' + results.performance_summary.api_calls_saved + '</p>';
            html += '<p><strong>' + t('cacheHitRate', 'Cache Hit Rate') + ':</strong> ' + (results.performance_summary.cache_hit_rate * 100) + '%</p>';
            html += '<p><strong>' + t('memoryUsage', 'Memory Usage') + ':</strong> ' + results.performance_summary.memory_usage_mb + 'MB</p>';
            html += '</div>';
        }
        
        // Recommendations
        if (results.recommendations && results.recommendations.length > 0) {
            html += '<div class="recommendations">';
            html += '<h3>' + t('recommendations', 'Recommendations') + '</h3>';
            html += '<ul>';
            results.recommendations.forEach(function(rec) {
                html += '<li>' + rec + '</li>';
            });
            html += '</ul>';
            html += '</div>';
        }
        
        $('#test-results').html(html);
    }

    initUICrawl();
});