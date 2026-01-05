# TradePress

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.en.html)
[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/plugins/)

A comprehensive WordPress plugin for algorithmic stock analysis, trading decision support, and portfolio management. TradePress combines technical indicators, scoring directives, and multi-platform API integrations to help traders identify opportunities and manage risk.

## Features

### Core Capabilities
- **Scoring Directives** - Custom algorithmic scoring system combining multiple technical indicators (RSI, MACD, CCI, ADX, Bollinger Bands, EMA, Volume analysis)
- **Strategy Builder** - Create and manage custom trading strategies with weighted directive combinations
- **Multi-Platform Integration** - Connect to 20+ trading and data platforms (Alpaca, Alpha Vantage, Finnhub, Polygon, IEX Cloud, and more)
- **Real-Time Data** - Live market data with intelligent caching and API optimization
- **Portfolio Management** - Track positions, manage risk, and monitor performance
- **Watchlist Management** - Organize and monitor securities with custom alerts

### Advanced Features
- **Automated Testing Framework** - Built-in test registry and runner for directives and strategies
- **Risk Management** - Position risk calculator, volatility monitoring, and risk factor analysis
- **Educational System** - Interactive learning modules for trading concepts and analysis techniques
- **Advisor Mode** - Step-by-step guidance for strategy development and execution
- **Webhook Integration** - Connect external signals and automation triggers
- **Discord Integration** - Real-time notifications and alerts via Discord webhooks

## Installation

### Requirements
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.2+

### From GitHub
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/tradepress.git
   cd tradepress
   ```

2. Place in WordPress plugins directory:
   ```bash
   cp -r tradepress /path/to/wordpress/wp-content/plugins/
   ```

3. Activate the plugin:
   - Go to WordPress Admin → Plugins
   - Find "TradePress" and click "Activate"

4. Run Setup Wizard:
   - Navigate to TradePress → Setup Wizard
   - Configure API credentials for your preferred data platforms
   - Set up trading platform connections

## Quick Start

### 1. Configure API Credentials
- Go to **TradePress → Settings → Trading Platforms**
- Add API keys for your data providers (Alpha Vantage, Finnhub, etc.)
- Test connections using the diagnostic tools

### 2. Create Your First Strategy
- Navigate to **TradePress → Trading → Create Strategy**
- Select 3-5 scoring directives
- Assign weights to each directive (total = 100%)
- Save and activate your strategy

### 3. Monitor Scores
- Go to **TradePress → SEES Ready**
- Select your active strategy
- View real-time scores for your watchlist symbols
- Identify high-scoring opportunities

## Configuration

### Scoring Directives
TradePress includes 22+ pre-configured directives:

| Directive | Type | Data Source | Use Case |
|-----------|------|-------------|----------|
| ADX | Trend | Technical | Trend strength analysis |
| RSI | Momentum | Technical | Overbought/oversold conditions |
| MACD | Momentum | Technical | Momentum crossovers |
| CCI | Oscillator | Technical | Commodity channel analysis |
| Bollinger Bands | Volatility | Technical | Volatility breakouts |
| Volume | Volume | Technical | Volume surge detection |
| EMA | Trend | Technical | Exponential moving average |

### API Platforms Supported
- **Data Providers**: Alpha Vantage, Finnhub, Polygon, IEX Cloud, Twelve Data, EODHD, MarketStack
- **Brokers**: Alpaca, Interactive Brokers, Fidelity, E*TRADE, Webull, Trading212
- **Social**: Discord, StockTwits
- **Alternative Data**: Intrinio, FMP, TradingView

## Usage Examples

### Create a Momentum Strategy
```php
// Strategy with momentum-focused directives
$strategy = array(
    'name' => 'Momentum Play',
    'directives' => array(
        'rsi' => 30,      // 30% weight
        'macd' => 35,     // 35% weight
        'volume' => 35    // 35% weight
    )
);
```

### Access Scoring Results
```php
// Get scores for a symbol using active strategy
$scores = tradepress_get_symbol_scores('AAPL');
// Returns: array with individual directive scores and composite score
```

### Set Up Webhooks
- Go to **TradePress → Webhooks**
- Create new webhook endpoint
- Configure trigger conditions
- Connect external signals or automation tools

## Development

### Project Structure
```
tradepress/
├── admin/              # Admin interface and settings
├── api/                # API integrations and adapters
├── includes/           # Core functionality
│   ├── scoring-system/ # Directive and strategy logic
│   ├── automation-system/ # Automated trading features
│   └── education-system/  # Learning modules
├── assets/             # CSS, JavaScript, images
├── classes/            # PHP classes
├── functions/          # Helper functions
├── posts/              # Custom post types
├── tests/              # Testing framework
└── shortcodes/         # WordPress shortcodes
```

### Testing Directives
```bash
# Test individual directive
php test-directive.php cci

# Run full test suite
php tests/test-runner.php
```

### Adding Custom Directives
1. Create directive class extending `TradePress_Scoring_Directive_Base`
2. Implement required methods: `calculate()`, `get_score()`
3. Register in `includes/scoring-system/directives-register.php`
4. Test using built-in testing framework

## Contributing

We welcome contributions! Please follow these guidelines:

1. **Fork the repository** and create a feature branch
2. **Follow WordPress coding standards** - Use [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
3. **Write tests** for new features
4. **Document changes** in commit messages
5. **Submit a pull request** with clear description

### Development Setup
```bash
# Clone and install
git clone https://github.com/yourusername/tradepress.git
cd tradepress

# Install dependencies (if using Composer)
composer install

# Run tests
php tests/test-runner.php
```

## Support & Documentation

- **Documentation**: [TradePress Wiki](https://github.com/yourusername/tradepress/wiki)
- **Issues**: [GitHub Issues](https://github.com/yourusername/tradepress/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/tradepress/discussions)

## Roadmap

### Phase 1 (Current)
- ✅ Core scoring directives (5 directives)
- ✅ Strategy builder
- ✅ Multi-platform API integration
- 🔄 Enhanced testing framework

### Phase 2
- Automated trading execution
- Advanced analytics dashboard
- Machine learning directive optimization
- Performance backtesting

### Phase 3
- Mobile app integration
- Advanced risk management tools
- Predictive analytics
- Community strategy marketplace

## License

TradePress is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## Disclaimer

**This plugin is for educational and informational purposes only.** It is not financial advice. Trading and investing involve substantial risk of loss. Past performance does not guarantee future results. Always conduct your own research and consult with a qualified financial advisor before making investment decisions.

## Support the Project

TradePress is developed and maintained by volunteers. If you find this plugin valuable, consider:

- ⭐ **Star the repository** on GitHub
- 🐛 **Report bugs** and suggest features
- 📝 **Contribute code** or documentation
- 💬 **Share feedback** and use cases
- 💰 **Sponsor development** (see below)

## Sponsorship & Funding

### Support Development
Your support helps us maintain and improve TradePress:

- **GitHub Sponsors**: [Sponsor TradePress](https://github.com/sponsors/yourusername)
- **Buy Me a Coffee**: [Support via BMC](https://www.buymeacoffee.com/yourusername)
- **Patreon**: [Become a Patron](https://www.patreon.com/yourusername)

### What Your Support Enables
- Faster bug fixes and feature development
- Expanded API platform support
- Enhanced documentation and tutorials
- Community support and engagement
- Advanced features and tools

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

## Authors

- **Your Name** - Initial development and maintenance

## Acknowledgments

- WordPress community for excellent documentation
- API providers for reliable market data
- Contributors and testers

---

**Questions?** Open an issue or start a discussion on GitHub. We're here to help!
