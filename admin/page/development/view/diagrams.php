<?php
/**
 * TradePress Development Diagrams View
 *
 * @package TradePress/Admin/Views
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress_Admin_Development_Diagrams Class
 */
class TradePress_Admin_Development_Diagrams {
    
    /**
     * Output the diagrams view
     */
    public static function output() {
        ?>
        <div class="tradepress-diagrams-container">
            <div class="diagrams-header">
                <h2><?php _e('TradePress System Diagrams', 'tradepress'); ?></h2>
                <p><?php _e('Interactive visual diagrams showing data flow, system architecture, and algorithm processes.', 'tradepress'); ?></p>
            </div>
            
            <div class="diagram-controls">
                <select id="diagram-selector" class="regular-text">
                    <optgroup label="System Architecture">
                        <option value="api-management-flow"><?php _e('API Management Flow', 'tradepress'); ?></option>
                        <option value="api-usage-tracking"><?php _e('API Usage Tracking & Fallback System', 'tradepress'); ?></option>
                        <option value="api-data-standardization"><?php _e('API Data Standardization Flow', 'tradepress'); ?></option>
                        <option value="bugnet-logging"><?php _e('BugNet Logging System', 'tradepress'); ?></option>
                        <option value="data-flow"><?php _e('Data Flow Architecture', 'tradepress'); ?></option>
                        <option value="trading-platform-integration"><?php _e('Trading Platform Integration', 'tradepress'); ?></option>
                        <option value="database-schema"><?php _e('Database Schema Relationships', 'tradepress'); ?></option>
                        <option value="plugin-dependencies"><?php _e('Plugin Module Dependencies', 'tradepress'); ?></option>
                    </optgroup>
                    <optgroup label="Process Flows">
                        <option value="api-testing-workflow"><?php _e('API Testing Workflow', 'tradepress'); ?></option>
                        <option value="api-call-deduplication"><?php _e('API Call Deduplication Process', 'tradepress'); ?></option>
                        <option value="automation-process"><?php _e('Automation System Process', 'tradepress'); ?></option>
                        <option value="scoring-pipeline"><?php _e('Scoring System Pipeline', 'tradepress'); ?></option>
                        <option value="data-freshness"><?php _e('Data Freshness Framework', 'tradepress'); ?></option>
                        <option value="call-register"><?php _e('Recent Call Register System', 'tradepress'); ?></option>
                        <option value="symbol-import"><?php _e('Symbol Import Process', 'tradepress'); ?></option>
                    </optgroup>
                    <optgroup label="Error Handling & Debugging">
                        <option value="error-propagation"><?php _e('Error Propagation Flow', 'tradepress'); ?></option>
                        <option value="troubleshooting-tree"><?php _e('Troubleshooting Decision Tree', 'tradepress'); ?></option>
                    </optgroup>
                    <optgroup label="Security & Safety">
                        <option value="api-safety"><?php _e('API Safety Protocol', 'tradepress'); ?></option>
                        <option value="data-validation"><?php _e('Data Validation Pipeline', 'tradepress'); ?></option>
                    </optgroup>
                    <optgroup label="Performance & Optimization">
                        <option value="cache-strategy"><?php _e('Cache Strategy Map', 'tradepress'); ?></option>
                        <option value="database-optimization"><?php _e('Database Query Optimization', 'tradepress'); ?></option>
                    </optgroup>
                    <optgroup label="User Experience">
                        <option value="admin-navigation"><?php _e('Admin Interface Navigation', 'tradepress'); ?></option>
                        <option value="setup-wizard"><?php _e('Setup Wizard Process', 'tradepress'); ?></option>
                    </optgroup>
                    <optgroup label="Development & Maintenance">
                        <option value="code-architecture"><?php _e('Code Architecture Overview', 'tradepress'); ?></option>
                        <option value="deployment-process"><?php _e('Deployment & Update Process', 'tradepress'); ?></option>
                    </optgroup>
                    <optgroup label="Business Logic">
                        <option value="trading-strategy"><?php _e('Trading Strategy Execution', 'tradepress'); ?></option>
                        <option value="api-integration"><?php _e('API Integration Map', 'tradepress'); ?></option>
                        <option value="trading-flow"><?php _e('Trading Strategy Flow', 'tradepress'); ?></option>
                    </optgroup>
                </select>
                <button id="fullscreen-btn" class="button"><?php _e('Fullscreen', 'tradepress'); ?></button>
                <button id="export-btn" class="button"><?php _e('Export SVG', 'tradepress'); ?></button>
                <button id="ai-analysis-btn" class="button button-primary"><?php _e('AI Analysis', 'tradepress'); ?></button>
            </div>
            
            <div id="ai-analysis-panel" class="ai-analysis-panel" style="display: none;">
                <div class="analysis-header">
                    <h3><?php _e('AI System Analysis', 'tradepress'); ?></h3>
                    <button id="close-analysis" class="button-link"><?php _e('Close', 'tradepress'); ?></button>
                </div>
                <div id="analysis-results" class="analysis-results">
                    <div class="analysis-loading">
                        <span class="spinner is-active"></span>
                        <p><?php _e('Analyzing system architecture and code implementation...', 'tradepress'); ?></p>
                    </div>
                </div>
            </div>
            
            <div id="diagram-container" class="diagram-viewer">
                <div id="mermaid-diagram"></div>
            </div>
            
            <div class="diagram-info">
                <h3 id="diagram-title"><?php _e('API Usage Tracking & Fallback System', 'tradepress'); ?></h3>
                <p id="diagram-description"><?php _e('Intelligent API switching when rate limits are detected, with usage tracking and developer notices.', 'tradepress'); ?></p>
            </div>
        </div>
        
        <style>
        .tradepress-diagrams-container {
            max-width: 100%;
            margin: 20px 0;
        }
        
        .diagrams-header {
            margin-bottom: 20px;
        }
        
        .diagram-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .diagram-viewer {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            background: #fff;
            min-height: 400px;
            overflow: auto;
        }
        
        #mermaid-diagram {
            text-align: center;
        }
        
        .diagram-info {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #0073aa;
        }
        
        .diagram-viewer.fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 999999;
            background: white;
            border: none;
            border-radius: 0;
        }
        
        .ai-analysis-panel {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .analysis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            background: #fff;
        }
        
        .analysis-header h3 {
            margin: 0;
            color: #0073aa;
        }
        
        .analysis-results {
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .analysis-loading {
            text-align: center;
            padding: 40px 20px;
        }
        
        .analysis-section {
            margin-bottom: 25px;
            padding: 15px;
            background: #fff;
            border-radius: 4px;
            border-left: 4px solid #0073aa;
        }
        
        .analysis-section h4 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .improvement-item {
            margin-bottom: 15px;
            padding: 10px;
            background: #f0f8ff;
            border-radius: 3px;
        }
        
        .improvement-priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .priority-high {
            background: #ffebee;
            color: #c62828;
        }
        
        .priority-medium {
            background: #fff3e0;
            color: #ef6c00;
        }
        
        .priority-low {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .code-suggestion {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 10px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Wait for Mermaid to be available
            if (typeof mermaid === 'undefined') {
                console.error('Mermaid.js not loaded');
                return;
            }
            
            // Initialize Mermaid with proper DOM context
            mermaid.initialize({ 
                startOnLoad: false,
                theme: 'default',
                flowchart: {
                    useMaxWidth: true,
                    htmlLabels: true
                },
                securityLevel: 'loose'
            });
            
            // Diagram definitions
            const diagrams = {
                // System Architecture Diagrams
                'api-usage-tracking': {
                    title: '<?php _e('API Usage Tracking & Fallback System', 'tradepress'); ?>',
                    description: '<?php _e('Intelligent API switching when rate limits are detected, with usage tracking and developer notices.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[API Call Request] --> B[TradePress_Base_API]
                            B --> C[Track Usage]
                            C --> D[Make API Call]
                            D --> E{Success?}
                            E -->|Yes| F[Track Successful Call]
                            E -->|No| G{Rate Limited?}
                            
                            G -->|Yes| H[Mark Rate Limited]
                            G -->|No| I[Track Failed Call]
                            
                            H --> J[Usage Tracker]
                            J --> K[Get Best API for Data Type]
                            K --> L{Fallback Available?}
                            L -->|Yes| M[Switch to Fallback API]
                            L -->|No| N[Return Error]
                            
                            M --> O[Developer Notice: API Switch]
                            O --> P[Retry with Fallback]
                            P --> Q[Return Data]
                            
                            F --> Q
                            I --> Q
                            
                            subgraph Tracking["Usage Tracking"]
                                R[Daily Call Counts]
                                S[Success/Failure Rates]
                                T[Endpoint Usage Stats]
                                U[Rate Limit Detection]
                            end
                            
                            subgraph Fallback["Fallback Priority"]
                                V[Quote: AV → Finnhub → Alpaca]
                                W[Technical: AV → Finnhub]
                                X[News: Finnhub → AV]
                                Y[Fundamentals: AV → Finnhub]
                            end
                            
                            C -.-> Tracking
                            K -.-> Fallback
                            
                            style A fill:#e3f2fd
                            style H fill:#ffebee
                            style M fill:#fff3e0
                            style O fill:#e8f5e8
                            style Tracking fill:#f3e5f5
                            style Fallback fill:#fff8e1
                    `
                },
                'api-management-flow': {
                    title: '<?php _e('API Management Flow with Dual Testing', 'tradepress'); ?>',
                    description: '<?php _e('Shows the dual testing system (Call Test vs Query Test) with cache checking and rate limit protection.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[User Clicks Test] --> B{Test Type?}
                            B -->|Call Test| C[Direct API Call]
                            B -->|Query Test| D[Check Cache First]
                            
                            D --> E{Cache Found?}
                            E -->|Yes & Fresh| F[Return Cached Data]
                            E -->|No/Stale| G[Make API Call]
                            
                            C --> H[Log to calls.log]
                            G --> H
                            H --> I[Store in Cache]
                            I --> J[Log to Platform Log]
                            J --> K[Return Results]
                            F --> K
                            
                            subgraph Safety["Safety Checks"]
                                L[Live Mode Check]
                                M[Read-Only Endpoints]
                                N[Rate Limit Validation]
                            end
                            
                            C -.-> Safety
                            G -.-> Safety
                            
                            style B fill:#fff3e0
                            style D fill:#e8f5e8
                            style F fill:#e8f5e8
                            style Safety fill:#ffebee
                    `
                },
                'bugnet-logging': {
                    title: '<?php _e('BugNet Logging System Architecture', 'tradepress'); ?>',
                    description: '<?php _e('Two-tier logging with hook system for modular log file registration and developer mode controls.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Event Occurs] --> B[BugNet Logger]
                            B --> C{Developer Mode?}
                            C -->|Yes| D[Detailed Logging]
                            C -->|No| E[Simple Logging]
                            
                            D --> F[calls.log - Summary]
                            D --> G[Platform Logs - Details]
                            E --> F
                            
                            subgraph Hooks["Hook System"]
                                H[tradepress_bugnet_log_files]
                                I[Register Additional Logs]
                                J[alpaca.log, discord.log, etc.]
                            end
                            
                            G -.-> Hooks
                            
                            subgraph Logs["Log Files"]
                                K[calls.log - Activity Summary]
                                L[alpaca.log - Alpaca Details]
                                M[discord.log - Discord Details]
                                N[Custom Platform Logs]
                            end
                            
                            F --> K
                            G --> L
                            G --> M
                            I --> N
                            
                            style B fill:#e3f2fd
                            style Hooks fill:#fff8e1
                            style Logs fill:#f3e5f5
                    `
                },
                'data-flow': {
                    title: '<?php _e('Data Flow Architecture', 'tradepress'); ?>',
                    description: '<?php _e('Complete data flow from API to frontend with caching and database storage.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart LR
                            subgraph APIs["External APIs"]
                                A[Alpaca]
                                B[Alpha Vantage]
                                C[IEX Cloud]
                            end
                            
                            subgraph Cache["Cache Layer"]
                                D[WordPress Transients]
                                E[Object Cache]
                                F[File Cache]
                            end
                            
                            subgraph Database["Database"]
                                G[Price Data Table]
                                H[Symbol Meta]
                                I[Options Table]
                            end
                            
                            subgraph Frontend["User Interface"]
                                J[Admin Pages]
                                K[API Testing]
                                L[Data Display]
                            end
                            
                            APIs --> Cache
                            Cache --> Database
                            Database --> Frontend
                            Cache --> Frontend
                            
                            style APIs fill:#e8f5e8
                            style Cache fill:#fff3e0
                            style Database fill:#e3f2fd
                            style Frontend fill:#f3e5f5
                    `
                },
                'trading-platform-integration': {
                    title: '<?php _e('Trading Platform Integration Map', 'tradepress'); ?>',
                    description: '<?php _e('All supported APIs with connection types, authentication, and data endpoints.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            subgraph Trading["Trading Platforms"]
                                A[Alpaca - REST/WebSocket]
                                B[IBKR - TWS API]
                                C[Trading212 - REST]
                                D[Tradier - REST]
                            end
                            
                            subgraph Data["Data Providers"]
                                E[Alpha Vantage - REST]
                                F[IEX Cloud - REST]
                                G[Polygon - REST/WebSocket]
                                H[Finnhub - REST/WebSocket]
                            end
                            
                            subgraph Auth["Authentication"]
                                I[API Keys]
                                J[OAuth 2.0]
                                K[Bearer Tokens]
                            end
                            
                            subgraph Endpoints["Data Types"]
                                L[Market Data]
                                M[Account Info]
                                N[Order Management]
                                O[Historical Data]
                            end
                            
                            Trading --> Auth
                            Data --> Auth
                            Auth --> Endpoints
                            
                            style Trading fill:#e8f5e8
                            style Data fill:#fff3e0
                            style Auth fill:#ffebee
                            style Endpoints fill:#e3f2fd
                    `
                },
                'database-schema': {
                    title: '<?php _e('Database Schema Relationships', 'tradepress'); ?>',
                    description: '<?php _e('Core tables and their relationships for symbols, price data, and metadata.', 'tradepress'); ?>',
                    mermaid: `
                        erDiagram
                            SYMBOLS {
                                int ID PK
                                string symbol
                                string name
                                string exchange
                                datetime created
                            }
                            
                            SYMBOL_META {
                                int meta_id PK
                                int symbol_id FK
                                string meta_key
                                text meta_value
                            }
                            
                            PRICE_DATA {
                                int id PK
                                int symbol_id FK
                                decimal open
                                decimal high
                                decimal low
                                decimal close
                                bigint volume
                                datetime timestamp
                            }
                            
                            WATCHLISTS {
                                int id PK
                                string name
                                int user_id
                                datetime created
                            }
                            
                            WATCHLIST_SYMBOLS {
                                int id PK
                                int watchlist_id FK
                                int symbol_id FK
                                int sort_order
                            }
                            
                            SYMBOLS ||--o{ SYMBOL_META : has
                            SYMBOLS ||--o{ PRICE_DATA : has
                            SYMBOLS ||--o{ WATCHLIST_SYMBOLS : in
                            WATCHLISTS ||--o{ WATCHLIST_SYMBOLS : contains
                    `
                },
                'plugin-dependencies': {
                    title: '<?php _e('Plugin Module Dependencies', 'tradepress'); ?>',
                    description: '<?php _e('Core systems and their interdependencies showing module relationships.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            subgraph Core["Core Systems"]
                                A[BugNet Logging]
                                B[API Factory]
                                C[Database Functions]
                                D[Cache Manager]
                            end
                            
                            subgraph APIs["API Modules"]
                                E[Alpaca API]
                                F[Alpha Vantage API]
                                G[IEX Cloud API]
                            end
                            
                            subgraph Features["Feature Modules"]
                                H[Scoring System]
                                I[Automation System]
                                J[Symbol Management]
                                K[Watchlists]
                            end
                            
                            subgraph Admin["Admin Interface"]
                                L[Trading Platforms]
                                M[Data Management]
                                N[Settings]
                            end
                            
                            Core --> APIs
                            Core --> Features
                            APIs --> Features
                            Features --> Admin
                            Core --> Admin
                            
                            style Core fill:#e3f2fd
                            style APIs fill:#e8f5e8
                            style Features fill:#fff3e0
                            style Admin fill:#f3e5f5
                    `
                },
                
                // Process Flow Diagrams
                'api-call-deduplication': {
                    title: '<?php _e('API Call Deduplication Process', 'tradepress'); ?>',
                    description: '<?php _e('Shows how to prevent duplicate API calls between directive handler and directive classes.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Directive Test Request] --> B{Handler or Directive?}
                            B -->|Handler Approach| C[❌ WRONG: Handler fetches data]
                            B -->|Directive Approach| D[✅ CORRECT: Directive fetches data]
                            
                            C --> E[Handler makes API calls]
                            E --> F[Passes data to directive]
                            F --> G[Directive ALSO makes API calls]
                            G --> H[❌ DUPLICATE CALLS]
                            
                            D --> I[Handler passes symbol info only]
                            I --> J[Directive checks cache first]
                            J --> K{Cache Hit?}
                            K -->|Yes| L[✅ Use cached data]
                            K -->|No| M[Make API calls]
                            M --> N[Cache results]
                            N --> O[✅ SINGLE CALL SET]
                            
                            subgraph Problem["❌ Problem Pattern"]
                                P["Handler: ADX + +DI + -DI calls"]
                                Q["Directive: ADX + +DI + -DI calls"]
                                R["Result: 6 API calls"]
                                P --> Q --> R
                            end
                            
                            subgraph Solution["✅ Solution Pattern"]
                                S["Handler: Symbol info only"]
                                T["Directive: Check cache"]
                                U["Directive: 3 API calls if needed"]
                                V["Result: 3 API calls maximum"]
                                S --> T --> U --> V
                            end
                            
                            H -.-> Problem
                            O -.-> Solution
                            
                            style A fill:#e3f2fd
                            style C fill:#ffebee
                            style D fill:#e8f5e8
                            style H fill:#ffcdd2
                            style O fill:#c8e6c9
                            style Problem fill:#ffebee
                            style Solution fill:#e8f5e8
                    `
                },
                'api-testing-workflow': {
                    title: '<?php _e('API Testing Workflow', 'tradepress'); ?>',
                    description: '<?php _e('Decision tree for cache checking, API calls, and error handling in testing.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Start Test] --> B{Live Mode?}
                            B -->|Yes| C[Use Read-Only Endpoints]
                            B -->|No| D[All Endpoints Available]
                            
                            C --> E{Query Test?}
                            D --> E
                            E -->|Yes| F[Check Multiple Cache Keys]
                            E -->|No| G[Direct API Call]
                            
                            F --> H{Cache Hit?}
                            H -->|Yes| I[Return Cached Data]
                            H -->|No| J[Make API Call]
                            
                            G --> K[Log Call Test]
                            J --> L[Log Query Test]
                            
                            K --> M[Store in Cache]
                            L --> M
                            M --> N[Update Database]
                            N --> O[Display Results]
                            I --> O
                            
                            subgraph Error["Error Handling"]
                                P[API Error]
                                Q[Network Error]
                                R[Auth Error]
                            end
                            
                            G -.-> Error
                            J -.-> Error
                            Error --> S[Log Error]
                            S --> T[Show User Message]
                            
                            style B fill:#fff3e0
                            style F fill:#e8f5e8
                            style Error fill:#ffebee
                    `
                },
                'automation-process': {
                    title: '<?php _e('Automation System Process', 'tradepress'); ?>',
                    description: '<?php _e('Cron job scheduling, safety mechanisms, and background processing workflow.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[WordPress Cron] --> B[Automation Handler]
                            B --> C{Safety Checks}
                            C -->|Pass| D[Load Strategies]
                            C -->|Fail| E[Log Safety Violation]
                            
                            D --> F[Execute Directives]
                            F --> G[Calculate Scores]
                            G --> H{Threshold Met?}
                            H -->|Yes| I[Generate Signals]
                            H -->|No| J[Continue Monitoring]
                            
                            I --> K[Risk Management]
                            K -->|Approved| L[Execute Trades]
                            K -->|Rejected| M[Log Risk Rejection]
                            
                            L --> N[Update Portfolio]
                            N --> O[Log Results]
                            
                            subgraph Safety["Safety Mechanisms"]
                                P[Rate Limiting]
                                Q[Market Hours Check]
                                R[Account Balance]
                                S[Max Daily Trades]
                            end
                            
                            C -.-> Safety
                            K -.-> Safety
                            
                            style Safety fill:#ffebee
                            style C fill:#fff3e0
                            style K fill:#fff3e0
                    `
                },
                'scoring-pipeline': {
                    title: '<?php _e('Scoring System Pipeline', 'tradepress'); ?>',
                    description: '<?php _e('Data input to scoring output with directive execution and strategy validation.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Market Data Input] --> B[Load Active Directives]
                            B --> C[Execute Directives]
                            C --> D[Technical Analysis]
                            C --> E[Fundamental Analysis]
                            C --> F[Sentiment Analysis]
                            
                            D --> G[Calculate Technical Score]
                            E --> H[Calculate Fundamental Score]
                            F --> I[Calculate Sentiment Score]
                            
                            G --> J[Weighted Scoring]
                            H --> J
                            I --> J
                            
                            J --> K[Final Score]
                            K --> L{Score Validation}
                            L -->|Valid| M[Store Score]
                            L -->|Invalid| N[Log Error]
                            
                            M --> O[Generate Signals]
                            O --> P[Strategy Execution]
                            
                            style A fill:#e3f2fd
                            style J fill:#fff3e0
                            style K fill:#e8f5e8
                            style P fill:#f3e5f5
                    `
                },
                
                // Error Handling & Debugging Diagrams
                'error-propagation': {
                    title: '<?php _e('Error Propagation Flow', 'tradepress'); ?>',
                    description: '<?php _e('How errors flow from WordPress database through BugNet logging to admin notices.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[WordPress Database Error] --> B[WordPress Error Handler]
                            B --> C[BugNet Logger]
                            C --> D{Developer Mode?}
                            D -->|Yes| E[Detailed Error Log]
                            D -->|No| F[Simple Error Log]
                            
                            E --> G[calls.log + Platform Log]
                            F --> H[calls.log Only]
                            
                            G --> I[Admin Notice System]
                            H --> I
                            I --> J[Display to User]
                            
                            subgraph ErrorTypes["Error Types"]
                                K[Database Connection]
                                L[API Timeout]
                                M[Authentication]
                                N[Rate Limit]
                            end
                            
                            A -.-> ErrorTypes
                            
                            subgraph UserFeedback["User Feedback"]
                                O[Error Notice]
                                P[Troubleshooting Tips]
                                Q[Contact Support]
                            end
                            
                            J --> UserFeedback
                            
                            style A fill:#ffebee
                            style C fill:#fff3e0
                            style ErrorTypes fill:#fce4ec
                            style UserFeedback fill:#e8f5e8
                    `
                },
                'troubleshooting-tree': {
                    title: '<?php _e('Troubleshooting Decision Tree', 'tradepress'); ?>',
                    description: '<?php _e('Step-by-step diagnostic and resolution paths for common issues.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Issue Reported] --> B{API Connection?}
                            B -->|Yes| C[Check API Keys]
                            B -->|No| D{Database Error?}
                            
                            C --> E{Keys Valid?}
                            E -->|Yes| F[Check Rate Limits]
                            E -->|No| G[Update API Keys]
                            
                            F --> H{Rate Limited?}
                            H -->|Yes| I[Wait/Upgrade Plan]
                            H -->|No| J[Check Endpoint Status]
                            
                            D -->|Yes| K[Check Database Connection]
                            D -->|No| L{Cache Issue?}
                            
                            K --> M{Connection OK?}
                            M -->|Yes| N[Check Table Structure]
                            M -->|No| O[Fix Database Config]
                            
                            L -->|Yes| P[Clear Cache]
                            L -->|No| Q[Check Plugin Conflicts]
                            
                            style A fill:#e3f2fd
                            style B fill:#fff3e0
                            style D fill:#fff3e0
                            style L fill:#fff3e0
                    `
                },
                
                // Security & Safety Diagrams
                'api-safety': {
                    title: '<?php _e('API Safety Protocol', 'tradepress'); ?>',
                    description: '<?php _e('Live vs Paper mode validation with read-only endpoint restrictions and rate limiting.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[API Call Request] --> B{Trading Mode?}
                            B -->|Live| C[Live Mode Checks]
                            B -->|Paper| D[Paper Mode - All Endpoints]
                            
                            C --> E{Endpoint Type?}
                            E -->|Read-Only| F[Allow Call]
                            E -->|Trading| G[Block Call]
                            
                            F --> H[Rate Limit Check]
                            D --> H
                            
                            H --> I{Within Limits?}
                            I -->|Yes| J[Execute Call]
                            I -->|No| K[Queue/Delay Call]
                            
                            G --> L[Log Security Block]
                            L --> M[Show Safety Message]
                            
                            J --> N[Log Successful Call]
                            K --> O[Log Rate Limit Hit]
                            
                            subgraph ReadOnly["Read-Only Endpoints"]
                                P[Market Data]
                                Q[Account Info]
                                R[Historical Data]
                            end
                            
                            subgraph Trading["Trading Endpoints"]
                                S[Place Order]
                                T[Cancel Order]
                                U[Modify Position]
                            end
                            
                            F -.-> ReadOnly
                            G -.-> Trading
                            
                            style C fill:#ffebee
                            style G fill:#ffcdd2
                            style ReadOnly fill:#e8f5e8
                            style Trading fill:#ffebee
                    `
                },
                'data-validation': {
                    title: '<?php _e('Data Validation Pipeline', 'tradepress'); ?>',
                    description: '<?php _e('Input sanitization, validation, and storage with security checks at each layer.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[User Input] --> B[Input Sanitization]
                            B --> C[Data Type Validation]
                            C --> D[Business Logic Validation]
                            D --> E[Security Checks]
                            E --> F{Validation Passed?}
                            F -->|Yes| G[Store Data]
                            F -->|No| H[Log Validation Error]
                            
                            G --> I[Database Storage]
                            I --> J[Success Response]
                            
                            H --> K[User Error Message]
                            
                            subgraph Sanitization["Sanitization"]
                                L[Strip HTML]
                                M[Escape SQL]
                                N[Validate URLs]
                            end
                            
                            subgraph Validation["Validation Rules"]
                                O[Required Fields]
                                P[Data Formats]
                                Q[Value Ranges]
                            end
                            
                            subgraph Security["Security Checks"]
                                R[CSRF Protection]
                                S[User Permissions]
                                T[Rate Limiting]
                            end
                            
                            B -.-> Sanitization
                            C -.-> Validation
                            E -.-> Security
                            
                            style B fill:#fff3e0
                            style E fill:#ffebee
                            style F fill:#e8f5e8
                    `
                },
                
                // Performance & Optimization Diagrams
                'cache-strategy': {
                    title: '<?php _e('Cache Strategy Map', 'tradepress'); ?>',
                    description: '<?php _e('Multi-level caching with transients, database, and file cache plus invalidation triggers.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Data Request] --> B[Level 1: Object Cache]
                            B --> C{Cache Hit?}
                            C -->|Yes| D[Return Cached Data]
                            C -->|No| E[Level 2: Transients]
                            
                            E --> F{Transient Hit?}
                            F -->|Yes| G[Store in Object Cache]
                            F -->|No| H[Level 3: Database]
                            
                            H --> I{Database Hit?}
                            I -->|Yes| J[Store in Transients]
                            I -->|No| K[Level 4: API Call]
                            
                            K --> L[Store in Database]
                            L --> M[Store in Transients]
                            M --> N[Store in Object Cache]
                            N --> O[Return Fresh Data]
                            
                            G --> D
                            J --> G
                            
                            subgraph Invalidation["Cache Invalidation"]
                                P[Time-based Expiry]
                                Q[Manual Clear]
                                R[Data Update Triggers]
                            end
                            
                            subgraph Performance["Performance Metrics"]
                                S[Hit Rate]
                                T[Response Time]
                                U[Memory Usage]
                            end
                            
                            D -.-> Performance
                            O -.-> Performance
                            
                            style B fill:#e3f2fd
                            style E fill:#fff3e0
                            style H fill:#e8f5e8
                            style K fill:#ffebee
                    `
                },
                'database-optimization': {
                    title: '<?php _e('Database Query Optimization', 'tradepress'); ?>',
                    description: '<?php _e('Query patterns, indexing strategy, and performance bottleneck identification.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Query Request] --> B[Query Analysis]
                            B --> C{Optimized?}
                            C -->|Yes| D[Execute Query]
                            C -->|No| E[Query Optimization]
                            
                            E --> F[Add Indexes]
                            E --> G[Rewrite Query]
                            E --> H[Use Prepared Statements]
                            
                            F --> D
                            G --> D
                            H --> D
                            
                            D --> I[Performance Monitoring]
                            I --> J{Slow Query?}
                            J -->|Yes| K[Log Performance Issue]
                            J -->|No| L[Return Results]
                            
                            K --> M[Optimization Recommendations]
                            
                            subgraph Indexes["Index Strategy"]
                                N[Primary Keys]
                                O[Foreign Keys]
                                P[Composite Indexes]
                                Q[Unique Constraints]
                            end
                            
                            subgraph Bottlenecks["Common Bottlenecks"]
                                R[Missing Indexes]
                                S[N+1 Queries]
                                T[Large Result Sets]
                                U[Complex Joins]
                            end
                            
                            F -.-> Indexes
                            K -.-> Bottlenecks
                            
                            style B fill:#e3f2fd
                            style E fill:#fff3e0
                            style I fill:#e8f5e8
                            style K fill:#ffebee
                    `
                },
                
                // User Experience Diagrams
                'admin-navigation': {
                    title: '<?php _e('Admin Interface Navigation Map', 'tradepress'); ?>',
                    description: '<?php _e('Page hierarchy, user flow, and tab relationships in the admin interface.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[TradePress Menu] --> B[Trading Platforms]
                            A --> C[Data Management]
                            A --> D[Automation]
                            A --> E[Settings]
                            A --> F[Development]
                            
                            B --> G[API Management]
                            B --> H[API Efficiency]
                            B --> I[Endpoints]
                            
                            C --> J[Symbols]
                            C --> K[Data Sources]
                            C --> L[Import/Export]
                            
                            D --> M[Strategies]
                            D --> N[Scheduling]
                            D --> O[Monitoring]
                            
                            E --> P[General]
                            E --> Q[BugNet]
                            E --> R[Notifications]
                            
                            F --> S[Diagrams]
                            F --> T[Testing]
                            F --> U[Debug Tools]
                            
                            style A fill:#e3f2fd
                            style B fill:#e8f5e8
                            style C fill:#fff3e0
                            style D fill:#f3e5f5
                            style E fill:#fce4ec
                            style F fill:#e0f2f1
                    `
                },
                'setup-wizard': {
                    title: '<?php _e('Setup Wizard Process Flow', 'tradepress'); ?>',
                    description: '<?php _e('Initial configuration steps with validation checkpoints and error recovery.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Start Setup] --> B[Welcome Screen]
                            B --> C[API Configuration]
                            C --> D[Test API Connection]
                            D --> E{Connection OK?}
                            E -->|Yes| F[Database Setup]
                            E -->|No| G[Show Error + Retry]
                            
                            G --> C
                            
                            F --> H[Create Tables]
                            H --> I[Import Sample Data]
                            I --> J[Configure Settings]
                            J --> K[Setup Complete]
                            
                            subgraph Validation["Validation Points"]
                                L[API Keys Valid]
                                M[Database Writable]
                                N[Required Extensions]
                            end
                            
                            subgraph Recovery["Error Recovery"]
                                O[Retry Connection]
                                P[Manual Configuration]
                                Q[Skip Step]
                            end
                            
                            D -.-> Validation
                            G -.-> Recovery
                            
                            style A fill:#e3f2fd
                            style E fill:#fff3e0
                            style K fill:#e8f5e8
                            style Recovery fill:#ffebee
                    `
                },
                
                // Development & Maintenance Diagrams
                'code-architecture': {
                    title: '<?php _e('Code Architecture Overview', 'tradepress'); ?>',
                    description: '<?php _e('MVC pattern implementation with class inheritance and plugin hook system.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            subgraph MVC["MVC Pattern"]
                                A[Models - Data Layer]
                                B[Views - Presentation]
                                C[Controllers - Logic]
                            end
                            
                            subgraph Classes["Class Hierarchy"]
                                D[Base_API]
                                E[Alpaca_API extends Base_API]
                                F[Alpha_Vantage_API extends Base_API]
                            end
                            
                            subgraph Hooks["WordPress Hooks"]
                                G[Actions]
                                H[Filters]
                                I[Custom Hooks]
                            end
                            
                            subgraph Autoloader["Autoloading"]
                                J[PSR-4 Autoloader]
                                K[Class Mapping]
                                L[Namespace Structure]
                            end
                            
                            A --> D
                            B --> G
                            C --> H
                            D --> E
                            D --> F
                            
                            J --> Classes
                            
                            style MVC fill:#e3f2fd
                            style Classes fill:#e8f5e8
                            style Hooks fill:#fff3e0
                            style Autoloader fill:#f3e5f5
                    `
                },
                'deployment-process': {
                    title: '<?php _e('Deployment & Update Process', 'tradepress'); ?>',
                    description: '<?php _e('Version control workflow with database migrations and rollback procedures.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Code Changes] --> B[Version Control]
                            B --> C[Testing Environment]
                            C --> D{Tests Pass?}
                            D -->|Yes| E[Staging Deployment]
                            D -->|No| F[Fix Issues]
                            
                            F --> B
                            
                            E --> G[Database Migrations]
                            G --> H[Production Deployment]
                            H --> I[Health Checks]
                            I --> J{Deployment OK?}
                            J -->|Yes| K[Update Complete]
                            J -->|No| L[Rollback Process]
                            
                            L --> M[Restore Database]
                            L --> N[Restore Code]
                            M --> O[Verify Rollback]
                            N --> O
                            
                            subgraph Migrations["Database Migrations"]
                                P[Schema Changes]
                                Q[Data Transformations]
                                R[Index Updates]
                            end
                            
                            subgraph Monitoring["Post-Deployment"]
                                S[Error Monitoring]
                                T[Performance Metrics]
                                U[User Feedback]
                            end
                            
                            G -.-> Migrations
                            K -.-> Monitoring
                            
                            style D fill:#fff3e0
                            style J fill:#fff3e0
                            style L fill:#ffebee
                            style K fill:#e8f5e8
                    `
                },
                
                // Business Logic Diagrams
                'trading-strategy': {
                    title: '<?php _e('Trading Strategy Execution', 'tradepress'); ?>',
                    description: '<?php _e('Signal generation to execution with validation, risk management, and portfolio updates.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Market Data] --> B[Signal Generation]
                            B --> C[Strategy Validation]
                            C --> D{Valid Signal?}
                            D -->|Yes| E[Risk Assessment]
                            D -->|No| F[Log Invalid Signal]
                            
                            E --> G{Risk Acceptable?}
                            G -->|Yes| H[Position Sizing]
                            G -->|No| I[Log Risk Rejection]
                            
                            H --> J[Order Preparation]
                            J --> K[Execute Trade]
                            K --> L[Monitor Position]
                            L --> M[Update Portfolio]
                            
                            subgraph RiskMgmt["Risk Management"]
                                N[Position Size Limits]
                                O[Stop Loss Levels]
                                P[Portfolio Correlation]
                                Q[Volatility Checks]
                            end
                            
                            subgraph Monitoring["Position Monitoring"]
                                R[P&L Tracking]
                                S[Exit Conditions]
                                T[Rebalancing]
                            end
                            
                            E -.-> RiskMgmt
                            L -.-> Monitoring
                            
                            style B fill:#e3f2fd
                            style E fill:#fff3e0
                            style K fill:#ffebee
                            style M fill:#e8f5e8
                    `
                },
                
                'data-freshness': {
                    title: '<?php _e('Data Freshness Framework with Call Register', 'tradepress'); ?>',
                    description: '<?php _e('Shows how the Recent Call Register prevents duplicate API calls and integrates with Data Freshness validation.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Feature Request] --> B[Generate Serial]
                            B --> C{Check Call Register}
                            C -->|Found & Fresh| D[Return Cached Data]
                            C -->|Not Found/Stale| E[Data Freshness Check]
                            E -->|Fresh in DB| F[Use Database Data]
                            E -->|Stale/Missing| G[Make API Call]
                            G --> H[Store in Call Register]
                            H --> I[Update Database]
                            I --> J[Return Fresh Data]
                            F --> J
                            D --> J
                            
                            subgraph CR["Call Register"]
                                K["tradepress_call_register_YYYYMMDDHH"]
                                L["Serial: md5(platform+method+params)"]
                                M["Hourly Rotation"]
                            end
                            
                            B -.-> CR
                            C -.-> CR
                            H -.-> CR
                            
                            style A fill:#e1f5fe
                            style C fill:#fff3e0
                            style D fill:#e8f5e8
                            style G fill:#fce4ec
                            style J fill:#e8f5e8
                            style CR fill:#f3e5f5
                    `
                },
                'call-register': {
                    title: '<?php _e('Recent Call Register System', 'tradepress'); ?>',
                    description: '<?php _e('Detailed view of how the Call Register prevents duplicate API calls across multiple features.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            subgraph Features["Plugin Features"]
                                A[Test Directive]
                                B[Data Freshness Manager]
                                C[Scoring Algorithm]
                                D[Symbol Import]
                            end
                            
                            subgraph CallRegister["Recent Call Register"]
                                E[Generate Serial]
                                F[Check Recent Call]
                                G[Register Call]
                                H[Cache Result]
                            end
                            
                            subgraph Storage["Transient Storage"]
                                I["Current Hour: 2024121614"]
                                J["Previous Hour: 2024121613"]
                                K["Serial → Result Mapping"]
                            end
                            
                            A --> E
                            B --> E
                            C --> E
                            D --> E
                            
                            E --> F
                            F -->|Found| L[Return Cached]
                            F -->|Not Found| M[Make API Call]
                            M --> G
                            G --> H
                            H --> N[Return Fresh Data]
                            
                            F -.-> Storage
                            G -.-> Storage
                            H -.-> Storage
                            
                            style Features fill:#e3f2fd
                            style CallRegister fill:#fff8e1
                            style Storage fill:#f3e5f5
                            style L fill:#e8f5e8
                            style N fill:#e8f5e8
                    `
                },
                'symbol-import': {
                    title: '<?php _e('Symbol Import Process', 'tradepress'); ?>',
                    description: '<?php _e('Visualizes the batch symbol import process and database interactions.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[User Clicks Import] --> B[Create Database Records]
                            B --> C[Load TradePress_Symbol Objects]
                            C --> D[Queue API Updates]
                            D --> E[Background Process]
                            E --> F[API Call for Each Symbol]
                            F --> G[Update Symbol Meta]
                            G --> H[Update Status: Success/Failed]
                            H --> I[Display Results]
                            
                            style A fill:#e3f2fd
                            style B fill:#fff8e1
                            style E fill:#f3e5f5
                            style I fill:#e8f5e8
                    `
                },
                'api-integration': {
                    title: '<?php _e('API Integration Map', 'tradepress'); ?>',
                    description: '<?php _e('Shows all API providers and their data relationships.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart LR
                            subgraph APIs
                                A[Alpaca API]
                                B[Alpha Vantage]
                                C[IEX Cloud]
                            end
                            
                            subgraph Data Types
                                D[Price Data]
                                E[Earnings Calendar]
                                F[News & Sentiment]
                                G[Trading Orders]
                            end
                            
                            subgraph Database
                                H[tradepress_symbols]
                                I[tradepress_symbol_meta]
                                J[tradepress_earnings_calendar]
                                K[tradepress_news]
                            end
                            
                            A --> D
                            A --> G
                            B --> D
                            B --> E
                            C --> F
                            
                            D --> H
                            D --> I
                            E --> J
                            F --> K
                            G --> H
                            
                            style A fill:#e8f5e8
                            style B fill:#fff3e0
                            style C fill:#e1f5fe
                    `
                },
                'trading-flow': {
                    title: '<?php _e('Trading Strategy Flow', 'tradepress'); ?>',
                    description: '<?php _e('Shows the complete flow from signal generation to order execution.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            A[Market Data] --> B[Scoring Directives]
                            B --> C[Calculate Scores]
                            C --> D{Score Threshold Met?}
                            D -->|Yes| E[Generate Signal]
                            D -->|No| F[Continue Monitoring]
                            E --> G[Risk Management Check]
                            G -->|Pass| H[Place Order]
                            G -->|Fail| I[Log Risk Rejection]
                            H --> J[Monitor Position]
                            J --> K[Update Portfolio]
                            F --> A
                            I --> A
                            
                            style A fill:#e3f2fd
                            style C fill:#fff8e1
                            style E fill:#e8f5e8
                            style H fill:#ffebee
                            style K fill:#e8f5e8
                    `
                },
                'api-data-standardization': {
                    title: '<?php _e('API Data Standardization Flow', 'tradepress'); ?>',
                    description: '<?php _e('Eliminates code duplication by standardizing data from different API providers for 100+ directives.', 'tradepress'); ?>',
                    mermaid: `
                        flowchart TD
                            subgraph Directives["100+ Future Directives"]
                                A[D17 RSI]
                                B[D4 CCI]
                                C[D10 MACD]
                                D[D1 ADX]
                                E[... 96 more]
                            end
                            
                            subgraph Adapter["API Data Adapter"]
                                F[standardize_rsi_data]
                                G[standardize_quote_data]
                                H[standardize_macd_data]
                                I[standardize_cci_data]
                            end
                            
                            subgraph Factory["API Factory"]
                                J[Provider Selection]
                                K[Rate Limit Tracking]
                                L[Automatic Fallback]
                            end
                            
                            subgraph Providers["API Providers"]
                                M[Alpha Vantage]
                                N[Finnhub]
                                O[Future APIs]
                            end
                            
                            subgraph Storage["Standardized Storage"]
                                P[Database Tables]
                                Q[Cache System]
                                R[Universal Format]
                            end
                            
                            Directives --> Adapter
                            Adapter --> Factory
                            Factory --> Providers
                            Adapter --> Storage
                            
                            M -.->|Format A| Adapter
                            N -.->|Format B| Adapter
                            O -.->|Format C| Adapter
                            
                            style Directives fill:#e3f2fd
                            style Adapter fill:#fff3e0
                            style Factory fill:#e8f5e8
                            style Providers fill:#f3e5f5
                            style Storage fill:#e0f2f1
                    `
                }
            };
            
            // Function to render diagram
            function renderDiagram(diagramKey) {
                const diagram = diagrams[diagramKey];
                if (!diagram) return;
                
                $('#diagram-title').text(diagram.title);
                $('#diagram-description').text(diagram.description);
                
                const element = document.getElementById('mermaid-diagram');
                element.innerHTML = '';
                
                try {
                    // Use async render method
                    mermaid.render('diagram-' + Date.now(), diagram.mermaid).then(function(result) {
                        element.innerHTML = result.svg;
                    }).catch(function(error) {
                        console.error('Mermaid render error:', error);
                        element.innerHTML = '<p>Error rendering diagram: ' + error.message + '</p>';
                    });
                } catch (error) {
                    console.error('Mermaid error:', error);
                    element.innerHTML = '<p>Error initializing diagram</p>';
                }
            }
            
            // Event handlers
            $('#diagram-selector').on('change', function() {
                renderDiagram($(this).val());
            });
            
            $('#fullscreen-btn').on('click', function() {
                $('.diagram-viewer').toggleClass('fullscreen');
                $(this).text($('.diagram-viewer').hasClass('fullscreen') ? '<?php _e('Exit Fullscreen', 'tradepress'); ?>' : '<?php _e('Fullscreen', 'tradepress'); ?>');
            });
            
            $('#export-btn').on('click', function() {
                const svg = $('#mermaid-diagram svg')[0];
                if (svg) {
                    const serializer = new XMLSerializer();
                    const svgString = serializer.serializeToString(svg);
                    const blob = new Blob([svgString], {type: 'image/svg+xml'});
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'tradepress-diagram.svg';
                    a.click();
                    URL.revokeObjectURL(url);
                }
            });
            
            // ESC key to exit fullscreen
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('.diagram-viewer').hasClass('fullscreen')) {
                    $('.diagram-viewer').removeClass('fullscreen');
                    $('#fullscreen-btn').text('<?php _e('Fullscreen', 'tradepress'); ?>');
                }
            });
            
            // AI Analysis functionality
            $('#ai-analysis-btn').on('click', function() {
                $('#ai-analysis-panel').show();
                $('#analysis-results').html(`
                    <div class="analysis-loading">
                        <span class="spinner is-active"></span>
                        <p><?php _e('Analyzing system architecture and code implementation...', 'tradepress'); ?></p>
                    </div>
                `);
                
                // Perform AI analysis
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tradepress_ai_diagram_analysis',
                        nonce: '<?php echo wp_create_nonce('tradepress_ai_analysis'); ?>',
                        current_diagram: $('#diagram-selector').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            displayAnalysisResults(response.data);
                        } else {
                            $('#analysis-results').html('<p class="error">' + response.data + '</p>');
                        }
                    },
                    error: function() {
                        $('#analysis-results').html('<p class="error"><?php _e('Analysis failed. Please try again.', 'tradepress'); ?></p>');
                    }
                });
            });
            
            $('#close-analysis').on('click', function() {
                $('#ai-analysis-panel').hide();
            });
            
            function displayAnalysisResults(data) {
                let html = '';
                
                if (data.immediate_issues && data.immediate_issues.length > 0) {
                    html += '<div class="analysis-section">';
                    html += '<h4><?php _e('Immediate Issues Found', 'tradepress'); ?></h4>';
                    data.immediate_issues.forEach(function(issue) {
                        html += '<div class="improvement-item">';
                        html += '<span class="improvement-priority priority-' + issue.priority + '">' + issue.priority + '</span>';
                        html += '<strong>' + issue.title + '</strong>';
                        html += '<p>' + issue.description + '</p>';
                        if (issue.code_suggestion) {
                            html += '<div class="code-suggestion">' + issue.code_suggestion + '</div>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                if (data.optimizations && data.optimizations.length > 0) {
                    html += '<div class="analysis-section">';
                    html += '<h4><?php _e('Optimization Opportunities', 'tradepress'); ?></h4>';
                    data.optimizations.forEach(function(opt) {
                        html += '<div class="improvement-item">';
                        html += '<span class="improvement-priority priority-' + opt.priority + '">' + opt.priority + '</span>';
                        html += '<strong>' + opt.title + '</strong>';
                        html += '<p>' + opt.description + '</p>';
                        if (opt.expected_benefit) {
                            html += '<p><em>Expected Benefit: ' + opt.expected_benefit + '</em></p>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                if (data.architecture_suggestions && data.architecture_suggestions.length > 0) {
                    html += '<div class="analysis-section">';
                    html += '<h4><?php _e('Architecture Improvements', 'tradepress'); ?></h4>';
                    data.architecture_suggestions.forEach(function(suggestion) {
                        html += '<div class="improvement-item">';
                        html += '<span class="improvement-priority priority-' + suggestion.priority + '">' + suggestion.priority + '</span>';
                        html += '<strong>' + suggestion.title + '</strong>';
                        html += '<p>' + suggestion.description + '</p>';
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                if (data.summary) {
                    html += '<div class="analysis-section">';
                    html += '<h4><?php _e('Analysis Summary', 'tradepress'); ?></h4>';
                    html += '<p>' + data.summary + '</p>';
                    html += '</div>';
                }
                
                $('#analysis-results').html(html);
            }
            
            // Initial render
            renderDiagram('api-data-standardization');
        });
        </script>
        <?php
    }
    

}