# Data Freshness Framework - Technical Specification

**Status:** Planning  
**Priority:** Critical Infrastructure  
**Phase:** Foundation (Required before all trading/scoring systems)

## Overview

The Data Freshness Framework serves as a gatekeeper layer between the database and all algorithms, ensuring data quality and freshness before allowing calculations to proceed. This system prevents trading decisions based on stale data while maintaining performance through intelligent caching and queue management.

## Architecture Components

### 1. Core Classes

#### `TradePress_Data_Freshness_Manager`
- **Purpose:** Central coordinator for all data freshness validation
- **Location:** `includes/data-freshness-manager.php`
- **Methods:**
  - `validate_data_freshness($data_type, $symbol, $purpose)`
  - `get_freshness_requirements($purpose)`
  - `trigger_data_update($data_type, $symbol, $priority)`
  - `check_data_quality($data, $requirements)`

#### `TradePress_Data_Requirements_Registry`
- **Purpose:** Stores freshness requirements for different use cases
- **Location:** `includes/class.tradepress-data-requirements-registry.php`
- **Methods:**
  - `register_requirement($purpose, $data_type, $max_age, $update_frequency)`
  - `get_requirements($purpose)`
  - `validate_requirements($data, $purpose)`

#### `TradePress_Data_Update_Queue`
- **Purpose:** Manages prioritized data update requests
- **Location:** `includes/class.tradepress-data-update-queue.php`
- **Methods:**
  - `queue_update($data_type, $symbol, $priority, $callback)`
  - `process_queue()`
  - `get_queue_status()`

### 2. Data Freshness Matrix

```php
// Default requirements registry
$requirements = [
    'cfd_trading' => [
        'price' => ['max_age' => 60, 'update_freq' => 30],      // 1 min max, update every 30s
        'volume' => ['max_age' => 60, 'update_freq' => 30],
        'news' => ['max_age' => 300, 'update_freq' => 180]      // 5 min max, update every 3 min
    ],
    'swing_trading' => [
        'price' => ['max_age' => 900, 'update_freq' => 300],    // 15 min max, update every 5 min
        'volume' => ['max_age' => 900, 'update_freq' => 300],
        'fundamentals' => ['max_age' => 86400, 'update_freq' => 43200] // 24h max, update every 12h
    ],
    'sees_scoring' => [
        'price' => ['max_age' => 3600, 'update_freq' => 1800],  // 1h max, update every 30 min
        'fundamentals' => ['max_age' => 86400, 'update_freq' => 86400], // 24h max, daily update
        'earnings' => ['max_age' => 43200, 'update_freq' => 43200] // 12h max, twice daily
    ],
    'news_analysis' => [
        'news' => ['max_age' => 3600, 'update_freq' => 1800],   // 1h max, update every 30 min
        'sentiment' => ['max_age' => 7200, 'update_freq' => 3600] // 2h max, hourly update
    ],
    'risk_management' => [
        'price' => ['max_age' => 30, 'update_freq' => 15],      // 30s max, update every 15s
        'positions' => ['max_age' => 60, 'update_freq' => 30]   // 1 min max, update every 30s
    ]
];
```

### 3. Validation Workflow

```php
// Standard validation pattern for all algorithms
function execute_algorithm($purpose, $symbols, $callback) {
    $freshness_manager = new TradePress_Data_Freshness_Manager();
    
    // Step 1: Validate data freshness
    $validation_result = $freshness_manager->validate_data_freshness(
        $data_types_needed, 
        $symbols, 
        $purpose
    );
    
    // Step 2: Handle validation results
    switch ($validation_result['status']) {
        case 'fresh':
            return $callback($validation_result['data']);
            
        case 'stale_acceptable':
            $warning = "Using data that is {$validation_result['age']} old";
            return $callback($validation_result['data'], $warning);
            
        case 'stale_critical':
            if ($purpose === 'risk_management') {
                // Override for emergency risk management
                return $callback($validation_result['data'], 'EMERGENCY: Using stale data');
            }
            
            // Queue update and return error
            $freshness_manager->trigger_data_update(
                $data_types_needed, 
                $symbols, 
                $this->get_priority($purpose)
            );
            
            return new WP_Error('stale_data', 'Data too old for safe execution');
            
        case 'missing':
            $freshness_manager->trigger_data_update(
                $data_types_needed, 
                $symbols, 
                'high'
            );
            
            return new WP_Error('missing_data', 'Required data not available');
    }
}
```

## Implementation Details

### 1. Database Schema

#### `tradepress_data_freshness`
```sql
CREATE TABLE tradepress_data_freshness (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_type VARCHAR(50) NOT NULL,
    symbol VARCHAR(20),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_quality ENUM('excellent', 'good', 'acceptable', 'poor') DEFAULT 'good',
    source VARCHAR(50),
    INDEX idx_type_symbol (data_type, symbol),
    INDEX idx_updated (last_updated)
);
```

#### `tradepress_data_requirements`
```sql
CREATE TABLE tradepress_data_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purpose VARCHAR(50) NOT NULL,
    data_type VARCHAR(50) NOT NULL,
    max_age_seconds INT NOT NULL,
    update_frequency_seconds INT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    UNIQUE KEY unique_purpose_type (purpose, data_type)
);
```

#### `tradepress_data_update_queue`
```sql
CREATE TABLE tradepress_data_update_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_type VARCHAR(50) NOT NULL,
    symbol VARCHAR(20),
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    callback_class VARCHAR(100),
    callback_method VARCHAR(100),
    INDEX idx_priority_status (priority, status),
    INDEX idx_created (created_at)
);
```

### 2. Integration Points

#### All Scoring Directives
```php
class TradePress_Scoring_Directive_Base {
    protected function validate_data_before_scoring($symbol) {
        $freshness_manager = TradePress_Data_Freshness_Manager::get_instance();
        
        return $freshness_manager->validate_data_freshness(
            $this->get_required_data_types(),
            $symbol,
            'sees_scoring'
        );
    }
}
```

#### All Trading Strategies
```php
class TradePress_Trading_Strategy_Base {
    protected function validate_data_before_execution($symbols) {
        $freshness_manager = TradePress_Data_Freshness_Manager::get_instance();
        
        return $freshness_manager->validate_data_freshness(
            $this->get_required_data_types(),
            $symbols,
            $this->get_trading_style() // 'cfd_trading', 'swing_trading', etc.
        );
    }
}
```

### 3. Queue Processing

#### Cron Integration
```php
// Register cron jobs for different priority levels
add_action('tradepress_process_critical_queue', 'process_critical_data_updates'); // Every 15 seconds
add_action('tradepress_process_high_queue', 'process_high_priority_updates');     // Every minute
add_action('tradepress_process_medium_queue', 'process_medium_priority_updates'); // Every 5 minutes
add_action('tradepress_process_low_queue', 'process_low_priority_updates');       // Every 30 minutes
```

### 4. User Interface Elements

#### Data Status Dashboard
- **Location:** Data > Data Status (new tab)
- **Features:**
  - Real-time data freshness indicators
  - Queue status and processing times
  - Data quality metrics
  - Manual refresh triggers

#### Algorithm Warnings
- **Stale Data Warnings:** Yellow banner with data age
- **Critical Data Errors:** Red banner with update queue status
- **Override Options:** For experienced users in non-critical scenarios

## Error Handling & Fallbacks

### 1. Graceful Degradation
```php
$fallback_strategies = [
    'use_cached_with_warning' => 'Continue with stale data + warning',
    'use_alternative_source' => 'Try backup data provider',
    'queue_and_notify' => 'Queue update and notify user',
    'abort_with_explanation' => 'Stop execution with clear reason'
];
```

### 2. Emergency Overrides
- **Risk Management:** Always executes regardless of data age
- **Stop Losses:** Critical for position protection
- **Manual Override:** Admin can bypass for testing/emergency

### 3. Logging & Monitoring
- **Data Age Violations:** Log when algorithms use stale data
- **Queue Performance:** Monitor update times and failures
- **User Impact:** Track how often users encounter data issues

## Performance Considerations

### 1. Caching Strategy
- **In-Memory Cache:** Recent validation results (5-minute TTL)
- **Database Optimization:** Indexed queries for fast lookups
- **Batch Processing:** Group similar update requests

### 2. API Rate Limiting
- **Intelligent Queuing:** Respect API limits while prioritizing critical updates
- **Batch Requests:** Combine multiple symbol requests where possible
- **Fallback Timing:** Spread non-critical updates across time

### 3. Resource Management
- **Queue Size Limits:** Prevent memory issues with large queues
- **Processing Timeouts:** Avoid hanging on failed API calls
- **Cleanup Jobs:** Remove old queue entries and logs

## Testing Strategy

### 1. Unit Tests
- Data freshness validation logic
- Queue management functionality
- Fallback behavior verification

### 2. Integration Tests
- End-to-end algorithm execution with stale data
- Queue processing under load
- API failure scenarios

### 3. Performance Tests
- Large symbol set validation
- High-frequency update scenarios
- Memory usage under sustained load

## Deployment Phases

### Phase 1: Core Framework
- [ ] Base classes and database schema
- [ ] Basic validation logic
- [ ] Simple queue system

### Phase 2: Integration
- [ ] Integrate with existing scoring directives
- [ ] Add to trading strategy base classes
- [ ] User interface components

### Phase 3: Advanced Features
- [ ] Intelligent caching
- [ ] Performance optimization
- [ ] Advanced fallback strategies

### Phase 4: Monitoring & Analytics
- [ ] Data quality metrics
- [ ] Performance dashboards
- [ ] Predictive update scheduling

This framework ensures that TradePress maintains the highest data quality standards while providing flexibility for different use cases and graceful handling of real-world scenarios.
