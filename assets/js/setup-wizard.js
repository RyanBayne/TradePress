// TradePress Setup Wizard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // API Test functionality
    const testButton = document.getElementById('test-apis');
    if (testButton) {
        testButton.addEventListener('click', function() {
            const button = this;
            const resultsDiv = document.getElementById('api-test-results');
            
            button.disabled = true;
            button.textContent = 'Testing...';
            resultsDiv.innerHTML = '<div style="color: #666;">Running API tests...</div>';
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=tradepress_test_apis&nonce=' + tradepressSetup.nonce
            })
            .then(response => response.json())
            .then(data => {
                button.disabled = false;
                button.textContent = 'Test APIs';
                resultsDiv.innerHTML = data.html;
            })
            .catch(error => {
                button.disabled = false;
                button.textContent = 'Test APIs';
                resultsDiv.innerHTML = '<div style="color: #d63384;">Error: ' + error.message + '</div>';
            });
        });
    }

    // Developer mode toggle
    const devModeCheckbox = document.getElementById('tradepress_developer_mode');
    if (devModeCheckbox) {
        devModeCheckbox.addEventListener('change', function() {
            const devSection = document.getElementById('tradepress-dev-section');
            if (this.checked) {
                devSection.style.display = 'block';
            } else {
                devSection.style.display = 'none';
            }
        });
    }

    // Symbol selection functionality
    const symbolCards = document.querySelectorAll('.tradepress-symbol-card');
    symbolCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
            }
        });
    });

    // Select/Deselect all symbols
    const selectAllBtn = document.getElementById('select-all-symbols');
    const deselectAllBtn = document.getElementById('deselect-all-symbols');
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.tradepress-symbol-card input[type="checkbox"]').forEach(cb => cb.checked = true);
        });
    }
    
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.tradepress-symbol-card input[type="checkbox"]').forEach(cb => cb.checked = false);
        });
    }
});