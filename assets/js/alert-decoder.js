/**
 * TradePress - Discord Stock VIP Alert Decoder JavaScript
 *
 * @package  TradePress/admin/page/SocialPlatforms/view/Discord/StockVIP
 * @since    1.0.0
 * @created  April 29, 2023
 */

jQuery(document).ready(function($) {
    console.log('Alert decoder script loaded');
    
    // Force accordion visibility after a short delay
    setTimeout(function() {
        $('.alert-decoder-accordion-header').css({
            'visibility': 'visible',
            'display': 'flex',
            'background-color': '#f5f5f5',
            'padding': '12px 15px',
            'cursor': 'pointer',
            'border': '1px solid #ddd',
            'border-radius': '4px 4px 0 0'
        });
        
        // Apply emoji item styles again to ensure they're visible
        $('.emoji-item').css({
            'display': 'flex',
            'align-items': 'center',
            'padding': '8px 10px',
            'border': '1px solid #e5e5e5',
            'border-radius': '3px',
            'background-color': '#f9f9f9',
            'box-shadow': '0 1px 1px rgba(0, 0, 0, 0.02)',
            'margin-bottom': '5px'
        });
        
        $('.emoji').css({
            'font-size': '20px',
            'margin-right': '10px'
        });
    }, 100);
    
    // Accordion functionality
    $('.alert-decoder-accordion-header').on('click', function() {
        $(this).toggleClass('active');
        $(this).find('.accordion-icon').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        $(this).next('.alert-decoder-accordion-content').slideToggle(300);
    });
    
    // Ensure accordion is visible and expanded initially
    setTimeout(function() {
        $('.alert-decoder-accordion-header').addClass('active');
        $('.alert-decoder-accordion-header').find('.accordion-icon').addClass('dashicons-arrow-up-alt2').removeClass('dashicons-arrow-down-alt2');
        $('.alert-decoder-accordion-content').slideDown(300);
    }, 500);
    
    // Handle form submission
    $('#alert-decoder-form').on('submit', function(e) {
        e.preventDefault();
        
        const alertMessage = $('#alert-message').val();
        
        if (!alertMessage) {
            alert('Please paste a Stock VIP message to decode.');
            return;
        }
        
        // Process and analyze the message
        const results = analyzeStockVIPMessage(alertMessage);
        
        // Display results
        displayResults(results);
        
        // Show results section
        $('#decoder-results').show();
    });
    
    // Clear form and results
    $('#clear-decoder').on('click', function() {
        $('#alert-message').val('');
        $('#decoder-results').hide();
    });
    
    // Function to analyze the message
    function analyzeStockVIPMessage(message) {
        // Initialize results object
        const results = {
            ticker: '',
            price: '',
            actionType: '',
            timeframe: '',
            entry: '',
            target: '',
            stop: '',
            support: '',
            resistance: '',
            alertType: '',
            floatSize: '',
            catalysts: '',
            setup: '',
            bias: '',
            urgency: '',
            confidence: '',
            risk: '',
            summary: ''
        };
        
        // Extract ticker - look for common ticker patterns
        const tickerRegex = /(?:Ticker|TICKER):\s*([A-Z]+)|📌\s*Ticker:\s*([A-Z]+)|💡\s*Ticker:\s*([A-Z]+)|✅\s*Ticker:\s*([A-Z]+)|[^\w]([A-Z]{1,5})(?:\s*–|\s*-|\s*:|$)/m;
        const tickerMatch = message.match(tickerRegex);
        
        if (tickerMatch) {
            // Find the first non-undefined group from the regex match
            results.ticker = tickerMatch.slice(1).find(match => match !== undefined);
        }
        
        // Extract current price
        const priceRegex = /(?:Current Price|CURRENT PRICE|current price|Price|price):\s*\$?(\d+\.\d+|\d+)/i;
        const priceMatch = message.match(priceRegex);
        
        if (priceMatch) {
            results.price = '$' + priceMatch[1];
        }
        
        // Extract entry zone
        const entryRegex = /(?:Entry Range|ENTRY RANGE|entry range|Entry Zone|entry zone):\s*\$?(\d+\.?\d*\s*-\s*\$?\d+\.?\d*|\$?\d+\.?\d*\s*-\s*\d+\.?\d*|\$?\d+\.?\d*)/i;
        const entryMatch = message.match(entryRegex);
        
        if (entryMatch) {
            results.entry = entryMatch[1];
            if (!results.entry.includes('$')) {
                results.entry = '$' + results.entry;
            }
        }
        
        // Extract target price
        const targetRegex = /(?:Target|TARGET|target price|Target Price|price target):\s*\$?(\d+\.?\d*\s*[\+\-]?|\$?\d+\.?\d*\s*-\s*\$?\d+\.?\d*)/i;
        const targetMatch = message.match(targetRegex);
        
        if (targetMatch) {
            results.target = targetMatch[1];
            if (!results.target.includes('$') && !results.target.includes('-')) {
                results.target = '$' + results.target;
            }
        }
        
        // Other extraction functions remain the same...
        // Determine timeframe
        if (message.match(/intraday|day-trade|day trade|same day|today's|todays/i)) {
            results.timeframe = 'Intraday';
        } else if (message.match(/swing trade|swing-trade|multi-day|multi day|this week/i)) {
            results.timeframe = 'Swing Trade (Multi-day)';
        } else if (message.match(/long-term|long term|investment|hold/i)) {
            results.timeframe = 'Long-term';
        } else {
            results.timeframe = 'Short-term (Unspecified)';
        }
        
        // Determine action type
        if (message.match(/buy|long|enter|add|accumulate/i)) {
            results.actionType = 'Buy/Long';
        } else if (message.match(/sell|exit|close|take profit|profit-taking/i)) {
            results.actionType = 'Sell/Exit';
        } else if (message.match(/short|put|bearish/i)) {
            results.actionType = 'Short/Bearish';
        } else if (message.match(/watch|monitor|alert|keep.*on radar/i)) {
            results.actionType = 'Watch/Monitor';
        } else {
            results.actionType = 'Unspecified';
        }
        
        // Extract support and resistance
        const resistanceRegex = /(?:resistance|RESISTANCE)(?:\s*level|\s*zone)?:?\s*\$?(\d+\.?\d*)/i;
        const resistanceMatch = message.match(resistanceRegex);
        
        if (resistanceMatch) {
            results.resistance = '$' + resistanceMatch[1];
        } else {
            // Try to find resistance mentioned in non-standard ways
            const altResistanceRegex = /(?:needs to break|break above|breakout above|break through)\s*\$?(\d+\.?\d*)/i;
            const altResistanceMatch = message.match(altResistanceRegex);
            
            if (altResistanceMatch) {
                results.resistance = '$' + altResistanceMatch[1];
            }
        }
        
        const supportRegex = /(?:support|SUPPORT)(?:\s*level|\s*zone)?:?\s*\$?(\d+\.?\d*)/i;
        const supportMatch = message.match(supportRegex);
        
        if (supportMatch) {
            results.support = '$' + supportMatch[1];
        }
        
        // Extract float size
        const floatRegex = /(?:float|FLOAT)(?:\s*size)?:?\s*(\d+\.?\d*\s*[MBK])/i;
        const floatMatch = message.match(floatRegex);
        
        if (floatMatch) {
            results.floatSize = floatMatch[1];
        } else if (message.match(/(?:low|small|tiny|micro|nano)[\-\s]float/i)) {
            results.floatSize = 'Low Float';
        } else if (message.match(/(?:high|large|big)[\-\s]float/i)) {
            results.floatSize = 'High Float';
        }
        
        // Determine alert type
        if (message.match(/🚨|ALERT|Alert|alert/)) {
            results.alertType = 'Alert/Warning';
        } else if (message.match(/UPDATE|Update|update/)) {
            results.alertType = 'Update';
        } else if (message.match(/WATCH|Watch|watch/)) {
            results.alertType = 'Watchlist';
        } else if (message.match(/breakout|BREAKOUT|Breakout/)) {
            results.alertType = 'Breakout';
        } else if (message.match(/teaser|TEASER|Teaser/)) {
            results.alertType = 'Teaser';
        } else {
            results.alertType = 'Information';
        }
        
        // Extract setup type
        const setupRegex = /(?:setup|SETUP|Setup)(?:\s*type)?:?\s*([^,\.]+)/i;
        const setupMatch = message.match(setupRegex);
        
        if (setupMatch) {
            results.setup = setupMatch[1].trim();
        } else if (message.match(/breakout/i)) {
            results.setup = 'Breakout Setup';
        } else if (message.match(/reversal/i)) {
            results.setup = 'Reversal Setup';
        } else if (message.match(/swing/i)) {
            results.setup = 'Swing Trade Setup';
        } else if (message.match(/momentum/i)) {
            results.setup = 'Momentum Setup';
        } else if (message.match(/dip buy/i)) {
            results.setup = 'Dip Buying Setup';
        }
        
        // Extract catalysts
        if (message.match(/catalyst|news|PR|press release|announcement/i)) {
            // Find the sentence containing catalyst information
            const sentences = message.split(/[.!?]+/);
            const catalystSentence = sentences.find(s => s.match(/catalyst|news|PR|press release|announcement/i));
            
            if (catalystSentence) {
                results.catalysts = catalystSentence.trim();
                // Truncate if too long
                if (results.catalysts.length > 100) {
                    results.catalysts = results.catalysts.substring(0, 100) + '...';
                }
            }
        }
        
        // Determine sentiment
        const bullishKeywords = /bullish|positive|upside|gains|profit|higher|growth|uptrend|comeback|breakout|surge|rocket|moon/i;
        const bearishKeywords = /bearish|negative|downside|loss|lower|downtrend|fall|decline|drop|bearish/i;
        
        const bullishCount = (message.match(bullishKeywords) || []).length;
        const bearishCount = (message.match(bearishKeywords) || []).length;
        
        if (bullishCount > bearishCount) {
            results.bias = 'Bullish';
        } else if (bearishCount > bullishCount) {
            results.bias = 'Bearish';
        } else {
            results.bias = 'Neutral';
        }
        
        // Determine urgency
        if (message.match(/urgent|immediately|asap|right now|don't miss|alert|🚨/i)) {
            results.urgency = 'High';
        } else if (message.match(/soon|today|watch closely|keep an eye/i)) {
            results.urgency = 'Medium';
        } else {
            results.urgency = 'Low';
        }
        
        // Determine confidence level
        if (message.match(/confident|strong|sure|certain|definitely|absolutely|no doubt/i)) {
            results.confidence = 'High';
        } else if (message.match(/likely|probable|should|expect|anticipate/i)) {
            results.confidence = 'Medium';
        } else if (message.match(/possibly|may|might|could|attempt|try/i)) {
            results.confidence = 'Low';
        } else {
            results.confidence = 'Unspecified';
        }
        
        // Determine risk level
        if (message.match(/high risk|risky|speculative|gamble|lottery|volatile/i)) {
            results.risk = 'High Risk';
        } else if (message.match(/moderate risk|calculated risk|balanced/i)) {
            results.risk = 'Moderate Risk';
        } else if (message.match(/low risk|safe|conservative|defensive/i)) {
            results.risk = 'Low Risk';
        } else {
            // Calculate risk based on price and setup
            if (results.price && parseFloat(results.price.replace('$', '')) < 5) {
                results.risk = 'Higher Risk (Low-priced stock)';
            } else if (results.floatSize === 'Low Float') {
                results.risk = 'Higher Risk (Low Float stock)';
            } else {
                results.risk = 'Moderate Risk (Default assessment)';
            }
        }
        
        // Extract stop loss
        const stopRegex = /(?:stop loss|STOP LOSS|Stop Loss|SL):\s*\$?(\d+\.?\d*)/i;
        const stopMatch = message.match(stopRegex);
        
        if (stopMatch) {
            results.stop = '$' + stopMatch[1];
        }
        
        // Generate a summary
        let summary = '';
        
        if (results.ticker) {
            summary += `This appears to be a ${results.bias.toLowerCase()} ${results.alertType.toLowerCase()} for ${results.ticker}`;
            
            if (results.price) {
                summary += ` at ${results.price}`;
            }
            
            summary += `. `;
            
            if (results.actionType !== 'Unspecified') {
                summary += `The suggested action is to ${results.actionType.toLowerCase()} `;
                
                if (results.timeframe) {
                    summary += `with a ${results.timeframe.toLowerCase()} timeframe. `;
                }
            }
            
            if (results.entry) {
                summary += `The recommended entry zone is ${results.entry}. `;
            }
            
            if (results.target && results.stop) {
                summary += `Target price is ${results.target} with a stop loss at ${results.stop}. `;
            } else if (results.target) {
                summary += `Target price is ${results.target}. `;
            }
            
            if (results.setup) {
                summary += `This is identified as a ${results.setup} with ${results.risk.toLowerCase()} profile. `;
            }
            
            if (results.catalysts) {
                summary += `Potential catalyst: ${results.catalysts} `;
            }
        } else {
            summary = 'Could not identify key trading information in the provided message. Please check if the message contains standard trading alert formatting.';
        }
        
        results.summary = summary;
        
        return results;
    }
    
    // Function to display results
    function displayResults(results) {
        $('#result-ticker').text(results.ticker || 'N/A');
        $('#result-price').text(results.price || 'N/A');
        $('#result-action-type').text(results.actionType || 'N/A');
        $('#result-timeframe').text(results.timeframe || 'N/A');
        
        $('#result-entry').text(results.entry || 'N/A');
        $('#result-target').text(results.target || 'N/A');
        $('#result-stop').text(results.stop || 'N/A');
        $('#result-support').text(results.support || 'N/A');
        $('#result-resistance').text(results.resistance || 'N/A');
        
        $('#result-alert-type').text(results.alertType || 'N/A');
        $('#result-float-size').text(results.floatSize || 'N/A');
        $('#result-catalysts').text(results.catalysts || 'N/A');
        $('#result-setup').text(results.setup || 'N/A');
        
        $('#result-bias').text(results.bias || 'N/A');
        $('#result-urgency').text(results.urgency || 'N/A');
        $('#result-confidence').text(results.confidence || 'N/A');
        $('#result-risk').text(results.risk || 'N/A');
        
        $('#result-summary').text(results.summary);
    }
});
