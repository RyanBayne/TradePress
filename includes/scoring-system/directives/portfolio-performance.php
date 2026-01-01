<?php
/**
 * Portfolio Performance Directive
 * 
 * Analyzes portfolio momentum and performance patterns using Alpaca account data.
 * This directive is unique to Alpaca as it requires real portfolio/account information.
 *
 * @package TradePress
 * @subpackage Scoring\Directives
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Portfolio_Performance_Directive extends TradePress_Base_Directive {
    
    public function __construct() {
        parent::__construct();
        $this->directive_id = 'portfolio_performance';
        $this->name = 'Portfolio Performance';
        $this->description = 'Analyzes portfolio momentum and performance patterns';
    }
    
    /**
     * Calculate portfolio performance score
     */
    public function calculate_score($symbol, $data = array()) {
        // Get portfolio history from Alpaca
        $portfolio_data = $this->get_portfolio_history();
        
        if (!$portfolio_data) {
            return array(
                'score' => 0,
                'signals' => array(),
                'debug' => array('error' => 'No portfolio data available')
            );
        }
        
        $score = 0;
        $signals = array();
        
        // Calculate portfolio momentum (last 5 days)
        $momentum = $this->calculate_portfolio_momentum($portfolio_data);
        
        if ($momentum > 0.02) { // 2% positive momentum
            $score += 40;
            $signals[] = 'Strong portfolio momentum (+' . round($momentum * 100, 2) . '%)';
        } elseif ($momentum > 0) {
            $score += 20;
            $signals[] = 'Positive portfolio momentum (+' . round($momentum * 100, 2) . '%)';
        } elseif ($momentum < -0.02) {
            $score -= 20;
            $signals[] = 'Negative portfolio momentum (' . round($momentum * 100, 2) . '%)';
        }
        
        // Check if symbol is in user's watchlist (higher conviction)
        if ($this->is_in_watchlist($symbol)) {
            $score += 30;
            $signals[] = 'Symbol in active watchlist (high conviction)';
        }
        
        // Portfolio diversification factor
        $diversification = $this->get_portfolio_diversification();
        if ($diversification < 0.3) { // Low diversification = higher risk tolerance
            $score += 20;
            $signals[] = 'Low portfolio diversification suggests risk tolerance';
        }
        
        return array(
            'score' => max(0, min(100, $score)),
            'signals' => $signals,
            'debug' => array(
                'momentum' => $momentum,
                'diversification' => $diversification,
                'in_watchlist' => $this->is_in_watchlist($symbol)
            )
        );
    }
    
    /**
     * Get portfolio history from Alpaca
     */
    private function get_portfolio_history() {
        // Use Alpaca API to get portfolio history
        $alpaca_api = new TradePress_Alpaca_API();
        
        $params = array(
            'period' => '1W',
            'timeframe' => '1D'
        );
        
        return $alpaca_api->call_endpoint('portfolio_history', $params);
    }
    
    /**
     * Calculate portfolio momentum over last 5 days
     */
    private function calculate_portfolio_momentum($portfolio_data) {
        if (!isset($portfolio_data['equity']) || count($portfolio_data['equity']) < 2) {
            return 0;
        }
        
        $equity_values = $portfolio_data['equity'];
        $latest = end($equity_values);
        $previous = $equity_values[count($equity_values) - 2];
        
        if ($previous == 0) return 0;
        
        return ($latest - $previous) / $previous;
    }
    
    /**
     * Check if symbol is in user's watchlists
     */
    private function is_in_watchlist($symbol) {
        $alpaca_api = new TradePress_Alpaca_API();
        $watchlists = $alpaca_api->call_endpoint('watchlists');
        
        if (!$watchlists) return false;
        
        foreach ($watchlists as $watchlist) {
            if (isset($watchlist['assets'])) {
                foreach ($watchlist['assets'] as $asset) {
                    if ($asset['symbol'] === $symbol) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Calculate portfolio diversification (simplified)
     */
    private function get_portfolio_diversification() {
        // Simplified: return 0.5 as placeholder
        // In real implementation, would analyze position sizes
        return 0.5;
    }
    
    /**
     * Get required API endpoints
     */
    public function get_api_requirements() {
        return array(
            'alpaca' => array(
                'portfolio_history',
                'watchlists'
            )
        );
    }
}