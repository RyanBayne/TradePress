<?php
/**
 * TradePress Trading212 API Endpoints
 *
 * Defines endpoints and parameters for the Trading212 market data service
 * API Documentation: https://t212public-api-docs.redoc.ly/
 *
 * @package TradePress
 * @subpackage API\Trading212
 * @version 1.1.0
 * @since 2025-04-08
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TradePress Trading212 API Endpoints class
 */
class TradePress_Trading212_Endpoints {

	/**
	 * Get API restrictions and authentication details
	 *
	 * @return array API restrictions information
	 * @version 1.0.0
	 */
	public static function get_api_restrictions() {
		return array(
			'authentication'  => array(
				'description' => 'Trading212 API Authentication',
				'details'     => array(
					'method' => 'Bearer token in Authorization header',
					'header' => 'Authorization: {api_key}',
					'note'   => 'API key is passed directly as the Authorization header value, not as Bearer prefix',
				),
			),
			'environments'    => array(
				'description' => 'Available API environments',
				'details'     => array(
					'live' => 'https://live.trading212.com',
					'demo' => 'https://demo.trading212.com',
				),
			),
			'rate_limits'     => array(
				'description' => 'Per-endpoint rate limits — see each endpoint definition',
				'details'     => array(
					'instruments'   => '1 request per 30 seconds',
					'exchanges'     => '1 request per 30 seconds',
					'portfolio'     => '1 request per 5 seconds',
					'account_cash'  => '1 request per 2 seconds',
					'orders'        => '1 request per 5 seconds',
					'history'       => '6 requests per minute',
				),
			),
			'scopes'          => array(
				'description' => 'Available API permission scopes',
				'details'     => array(
					'account'              => 'Read account information and cash',
					'metadata'             => 'Read instruments and exchanges',
					'portfolio'            => 'Read portfolio positions',
					'orders:read'          => 'Read open orders',
					'orders:execute'       => 'Create and cancel orders',
					'pies:read'            => 'Read pies',
					'pies:write'           => 'Create, update and delete pies',
					'history:orders'       => 'Read historical orders',
					'history:dividends'    => 'Read historical dividends',
					'history:transactions' => 'Read historical transactions',
					'history:exports'      => 'Request and download CSV exports',
				),
			),
		);
	}

	/**
	 * Get all available endpoints
	 *
	 * @return array Array of available endpoints with their configurations
	 * @version 1.1.0
	 */
	public static function get_endpoints() {
		return array(
			// Instruments Metadata
			'exchanges'            => array(
				'endpoint'         => '/api/v0/equity/metadata/exchanges',
				'method'           => 'GET',
				'description'      => 'Fetch all exchanges and their corresponding working schedules that your account has access to',
				'parameters'       => array(),
				'rate_limit'       => '1 request per 30 seconds',
				'scopes'           => array( 'metadata' ),
				'example_response' => array(
					array(
						'id'               => 1,
						'name'             => 'NYSE',
						'workingSchedules' => array(
							// Working schedule data
						),
					),
				),
			),
			'instruments'          => array(
				'endpoint'         => '/api/v0/equity/metadata/instruments',
				'method'           => 'GET',
				'description'      => 'Fetch all instruments that your account has access to',
				'parameters'       => array(),
				'rate_limit'       => '1 request per 30 seconds',
				'scopes'           => array( 'metadata' ),
				'example_response' => array(
					array(
						'addedOn'           => '2025-03-24T14:15:22Z',
						'currencyCode'      => 'USD',
						'isin'              => 'US0378331005',
						'maxOpenQuantity'   => 100,
						'minTradeQuantity'  => 0.001,
						'name'              => 'Apple Inc.',
						'shortName'         => 'Apple',
						'ticker'            => 'AAPL_US_EQ',
						'type'              => 'STOCK',
						'workingScheduleId' => 1,
					),
				),
			),

			'instrument_by_ticker' => array(
				'endpoint'         => '/api/v0/equity/metadata/instruments/{ticker}',
				'method'           => 'GET',
				'description'      => 'Fetch a single instrument by ticker',
				'parameters'       => array(
					'ticker' => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Instrument ticker',
						'example'     => 'AAPL_US_EQ',
					),
				),
				'rate_limit'       => '1 request per 1 second',
				'scopes'           => array( 'metadata' ),
				'example_response' => array(
					'addedOn'           => '2025-03-24T14:15:22Z',
					'currencyCode'      => 'USD',
					'isin'              => 'US0378331005',
					'maxOpenQuantity'   => 100,
					'minTradeQuantity'  => 0.001,
					'name'              => 'Apple Inc.',
					'shortName'         => 'Apple',
					'ticker'            => 'AAPL_US_EQ',
					'type'              => 'STOCK',
					'workingScheduleId' => 1,
				),
			),

			// Account Data
			'account_cash'         => array(
				'endpoint'         => '/api/v0/equity/account/cash',
				'method'           => 'GET',
				'description'      => 'Get account cash information',
				'parameters'       => array(),
				'rate_limit'       => '1 request per 2 seconds',
				'scopes'           => array( 'account' ),
				'example_response' => array(
					'blocked'  => 0,
					'free'     => 1000.50,
					'invested' => 5000.75,
					'pieCash'  => 250,
					'ppl'      => 125.30,
					'result'   => 250.25,
					'total'    => 6250.80,
				),
			),
			'account_info'         => array(
				'endpoint'         => '/api/v0/equity/account/info',
				'method'           => 'GET',
				'description'      => 'Get account information',
				'parameters'       => array(),
				'rate_limit'       => '1 request per 30 seconds',
				'scopes'           => array( 'account' ),
				'example_response' => array(
					'currencyCode' => 'USD',
					'id'           => 12345,
				),
			),

			// Portfolio
			'portfolio'            => array(
				'endpoint'         => '/api/v0/equity/portfolio',
				'method'           => 'GET',
				'description'      => 'Get all positions in the portfolio',
				'parameters'       => array(),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'portfolio' ),
				'example_response' => array(
					array(
						'averagePrice'    => 150.25,
						'currentPrice'    => 175.50,
						'frontend'        => 'API',
						'fxPpl'           => 0,
						'initialFillDate' => '2025-02-15T09:30:22Z',
						'maxBuy'          => 100,
						'maxSell'         => 10,
						'pieQuantity'     => 0,
						'ppl'             => 252.50,
						'quantity'        => 10,
						'ticker'          => 'AAPL_US_EQ',
					),
				),
			),
			'portfolio_by_ticker'  => array(
				'endpoint'         => '/api/v0/equity/portfolio/{ticker}',
				'method'           => 'GET',
				'description'      => 'Get position details for a specific ticker',
				'parameters'       => array(
					'ticker' => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Ticker symbol',
						'example'     => 'AAPL_US_EQ',
					),
				),
				'rate_limit'       => '1 request per 1 second',
				'scopes'           => array( 'portfolio' ),
				'example_response' => array(
					'averagePrice'    => 150.25,
					'currentPrice'    => 175.50,
					'frontend'        => 'API',
					'fxPpl'           => 0,
					'initialFillDate' => '2025-02-15T09:30:22Z',
					'maxBuy'          => 100,
					'maxSell'         => 10,
					'pieQuantity'     => 0,
					'ppl'             => 252.50,
					'quantity'        => 10,
					'ticker'          => 'AAPL_US_EQ',
				),
			),

			// Orders
			'orders'               => array(
				'endpoint'         => '/api/v0/equity/orders',
				'method'           => 'GET',
				'description'      => 'Get all open orders',
				'parameters'       => array(),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'orders:read' ),
				'example_response' => array(
					array(
						'creationTime'   => '2025-04-07T10:15:22Z',
						'filledQuantity' => 0,
						'filledValue'    => 0,
						'id'             => 987654,
						'limitPrice'     => 180.00,
						'quantity'       => 5,
						'status'         => 'PENDING',
						'stopPrice'      => 0,
						'strategy'       => 'QUANTITY',
						'ticker'         => 'AAPL_US_EQ',
						'type'           => 'LIMIT',
						'value'          => 900.00,
					),
				),
			),
			'order_by_id'          => array(
				'endpoint'         => '/api/v0/equity/orders/{id}',
				'method'           => 'GET',
				'description'      => 'Get order details by ID',
				'parameters'       => array(
					'id' => array(
						'required'    => true,
						'type'        => 'integer',
						'description' => 'Order ID',
						'example'     => 987654,
					),
				),
				'rate_limit'       => '1 request per 1 second',
				'scopes'           => array( 'orders:read' ),
				'example_response' => array(
					'creationTime'   => '2025-04-07T10:15:22Z',
					'filledQuantity' => 0,
					'filledValue'    => 0,
					'id'             => 987654,
					'limitPrice'     => 180.00,
					'quantity'       => 5,
					'status'         => 'PENDING',
					'stopPrice'      => 0,
					'strategy'       => 'QUANTITY',
					'ticker'         => 'AAPL_US_EQ',
					'type'           => 'LIMIT',
					'value'          => 900.00,
				),
			),
			'order_market'         => array(
				'endpoint'         => '/api/v0/equity/orders/market',
				'method'           => 'POST',
				'description'      => 'Create a market order',
				'parameters'       => array(
					'quantity' => array(
						'required'    => true,
						'type'        => 'number',
						'description' => 'Quantity to buy or sell',
						'example'     => 5,
					),
					'ticker'   => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Instrument ticker',
						'example'     => 'AAPL_US_EQ',
					),
					'side'     => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Order side',
						'enum'        => array( 'BUY', 'SELL' ),
						'example'     => 'BUY',
					),
				),
				'rate_limit'       => '1 request per 1 second',
				'scopes'           => array( 'orders:execute' ),
				'example_response' => array(
					'creationTime'   => '2025-04-08T10:15:22Z',
					'filledQuantity' => 0,
					'filledValue'    => 0,
					'id'             => 987655,
					'limitPrice'     => 0,
					'quantity'       => 5,
					'status'         => 'PENDING',
					'stopPrice'      => 0,
					'strategy'       => 'QUANTITY',
					'ticker'         => 'AAPL_US_EQ',
					'type'           => 'MARKET',
					'value'          => 0,
				),
			),
			'order_limit'          => array(
				'endpoint'         => '/api/v0/equity/orders/limit',
				'method'           => 'POST',
				'description'      => 'Create a limit order',
				'parameters'       => array(
					'limitPrice'   => array(
						'required'    => true,
						'type'        => 'number',
						'description' => 'Limit price',
						'example'     => 180.00,
					),
					'quantity'     => array(
						'required'    => true,
						'type'        => 'number',
						'description' => 'Quantity to buy or sell',
						'example'     => 5,
					),
					'side'         => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Order side',
						'enum'        => array( 'BUY', 'SELL' ),
						'example'     => 'BUY',
					),
					'ticker'       => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Instrument ticker',
						'example'     => 'AAPL_US_EQ',
					),
					'timeValidity' => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Order expiration type',
						'enum'        => array( 'DAY', 'GOOD_TILL_CANCEL' ),
						'example'     => 'DAY',
					),
				),
				'rate_limit'       => '1 request per 2 seconds',
				'scopes'           => array( 'orders:execute' ),
				'example_response' => array(
					'creationTime'   => '2025-04-08T10:15:22Z',
					'filledQuantity' => 0,
					'filledValue'    => 0,
					'id'             => 987656,
					'limitPrice'     => 180.00,
					'quantity'       => 5,
					'status'         => 'PENDING',
					'stopPrice'      => 0,
					'strategy'       => 'QUANTITY',
					'ticker'         => 'AAPL_US_EQ',
					'type'           => 'LIMIT',
					'value'          => 900.00,
				),
			),
			'order_stop'           => array(
				'endpoint'         => '/api/v0/equity/orders/stop',
				'method'           => 'POST',
				'description'      => 'Create a stop order',
				'parameters'       => array(
					'quantity'     => array(
						'required'    => true,
						'type'        => 'number',
						'description' => 'Quantity to buy or sell',
						'example'     => 5,
					),
					'side'         => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Order side',
						'enum'        => array( 'BUY', 'SELL' ),
						'example'     => 'BUY',
					),
					'stopPrice'    => array(
						'required'    => true,
						'type'        => 'number',
						'description' => 'Stop price',
						'example'     => 170.00,
					),
					'ticker'       => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Instrument ticker',
						'example'     => 'AAPL_US_EQ',
					),
					'timeValidity' => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Order expiration type',
						'enum'        => array( 'DAY', 'GOOD_TILL_CANCEL' ),
						'example'     => 'DAY',
					),
				),
				'rate_limit'       => '1 request per 2 seconds',
				'scopes'           => array( 'orders:execute' ),
				'example_response' => array(
					'creationTime'   => '2025-04-08T10:15:22Z',
					'filledQuantity' => 0,
					'filledValue'    => 0,
					'id'             => 987657,
					'limitPrice'     => 0,
					'quantity'       => 5,
					'status'         => 'PENDING',
					'stopPrice'      => 170.00,
					'strategy'       => 'QUANTITY',
					'ticker'         => 'AAPL_US_EQ',
					'type'           => 'STOP',
					'value'          => 0,
				),
			),
			'order_stop_limit'     => array(
				'endpoint'         => '/api/v0/equity/orders/stop_limit',
				'method'           => 'POST',
				'description'      => 'Create a stop-limit order',
				'parameters'       => array(
					'limitPrice'   => array(
						'required'    => true,
						'type'        => 'number',
						'description' => 'Limit price',
						'example'     => 180.00,
					),
					'quantity'     => array(
						'required'    => true,
						'type'        => 'number',
						'description' => 'Quantity to buy or sell',
						'example'     => 5,
					),
					'side'         => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Order side',
						'enum'        => array( 'BUY', 'SELL' ),
						'example'     => 'BUY',
					),
					'stopPrice'    => array(
						'required'    => true,
						'type'        => 'number',
						'description' => 'Stop price',
						'example'     => 175.00,
					),
					'ticker'       => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Instrument ticker',
						'example'     => 'AAPL_US_EQ',
					),
					'timeValidity' => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Order expiration type',
						'enum'        => array( 'DAY', 'GOOD_TILL_CANCEL' ),
						'example'     => 'DAY',
					),
				),
				'rate_limit'       => '1 request per 2 seconds',
				'scopes'           => array( 'orders:execute' ),
				'example_response' => array(
					'creationTime'   => '2025-04-08T10:15:22Z',
					'filledQuantity' => 0,
					'filledValue'    => 0,
					'id'             => 987658,
					'limitPrice'     => 180.00,
					'quantity'       => 5,
					'status'         => 'PENDING',
					'stopPrice'      => 175.00,
					'strategy'       => 'QUANTITY',
					'ticker'         => 'AAPL_US_EQ',
					'type'           => 'STOP_LIMIT',
					'value'          => 900.00,
				),
			),
			'cancel_order'         => array(
				'endpoint'         => '/api/v0/equity/orders/{id}',
				'method'           => 'DELETE',
				'description'      => 'Cancel an open order',
				'parameters'       => array(
					'id' => array(
						'required'    => true,
						'type'        => 'integer',
						'description' => 'Order ID',
						'example'     => 987654,
					),
				),
				'rate_limit'       => '1 request per 1 second',
				'scopes'           => array( 'orders:execute' ),
				'example_response' => array(),
			),

			// Pies
			'pies'                 => array(
				'endpoint'         => '/api/v0/equity/pies',
				'method'           => 'GET',
				'description'      => 'Fetches all pies for the account',
				'parameters'       => array(),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'pies:read' ),
				'example_response' => array(
					array(
						'cash'            => 50.25,
						'dividendDetails' => array(),
						'id'              => 12345,
						'progress'        => 0.75,
						'result'          => array(),
						'status'          => 'AHEAD',
					),
				),
			),
			'pie_by_id'            => array(
				'endpoint'         => '/api/v0/equity/pies/{id}',
				'method'           => 'GET',
				'description'      => 'Get pie details by ID',
				'parameters'       => array(
					'id' => array(
						'required'    => true,
						'type'        => 'integer',
						'description' => 'Pie ID',
						'example'     => 12345,
					),
				),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'pies:read' ),
				'example_response' => array(
					'instruments' => array(
						array(
							'ticker'     => 'AAPL_US_EQ',
							'percentage' => 0.5,
						),
						array(
							'ticker'     => 'MSFT_US_EQ',
							'percentage' => 0.5,
						),
					),
					'settings'    => array(
						'creationDate'       => '2025-01-15T14:15:22Z',
						'dividendCashAction' => 'REINVEST',
						'endDate'            => '2025-12-31T23:59:59Z',
						'goal'               => 10000,
						'icon'               => 'technology',
						'id'                 => 12345,
						'initialInvestment'  => 1000,
						'instrumentShares'   => array(
							'AAPL_US_EQ' => 0.5,
							'MSFT_US_EQ' => 0.5,
						),
						'name'               => 'Tech Giants',
						'publicUrl'          => 'https://trading212.com/pies/tech-giants-12345',
					),
				),
			),
			'create_pie'           => array(
				'endpoint'         => '/api/v0/equity/pies',
				'method'           => 'POST',
				'description'      => 'Creates a pie for the account',
				'parameters'       => array(
					'dividendCashAction' => array(
						'required'    => false,
						'type'        => 'string',
						'description' => 'Dividend cash action',
						'enum'        => array( 'REINVEST', 'TO_ACCOUNT_CASH' ),
						'example'     => 'REINVEST',
					),
					'endDate'            => array(
						'required'    => false,
						'type'        => 'string',
						'format'      => 'date-time',
						'description' => 'End date for the pie',
						'example'     => '2025-12-31T23:59:59Z',
					),
					'goal'               => array(
						'required'    => false,
						'type'        => 'number',
						'description' => 'Total desired value of the pie',
						'example'     => 10000,
					),
					'icon'               => array(
						'required'    => false,
						'type'        => 'string',
						'description' => 'Icon identifier',
						'example'     => 'technology',
					),
					'instrumentShares'   => array(
						'required'    => true,
						'type'        => 'object',
						'description' => 'Instrument shares allocation',
						'example'     => array(
							'AAPL_US_EQ' => 0.5,
							'MSFT_US_EQ' => 0.5,
						),
					),
					'name'               => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Pie name',
						'example'     => 'Tech Giants',
					),
				),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'pies:write' ),
				'example_response' => array(
					'instruments' => array(
						array(
							'ticker'     => 'AAPL_US_EQ',
							'percentage' => 0.5,
						),
						array(
							'ticker'     => 'MSFT_US_EQ',
							'percentage' => 0.5,
						),
					),
					'settings'    => array(
						'creationDate'       => '2025-04-08T10:15:22Z',
						'dividendCashAction' => 'REINVEST',
						'endDate'            => '2025-12-31T23:59:59Z',
						'goal'               => 10000,
						'icon'               => 'technology',
						'id'                 => 12346,
						'initialInvestment'  => 0,
						'instrumentShares'   => array(
							'AAPL_US_EQ' => 0.5,
							'MSFT_US_EQ' => 0.5,
						),
						'name'               => 'Tech Giants',
						'publicUrl'          => 'https://trading212.com/pies/tech-giants-12346',
					),
				),
			),
			'delete_pie'           => array(
				'endpoint'         => '/api/v0/equity/pies/{id}',
				'method'           => 'DELETE',
				'description'      => 'Deletes a pie',
				'parameters'       => array(
					'id' => array(
						'required'    => true,
						'type'        => 'integer',
						'description' => 'Pie ID',
						'example'     => 12345,
					),
				),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'pies:write' ),
				'example_response' => array(),
			),

			'update_pie'           => array(
				'endpoint'         => '/api/v0/equity/pies/{id}',
				'method'           => 'PUT',
				'description'      => 'Update an existing pie',
				'parameters'       => array(
					'id'                 => array(
						'required'    => true,
						'type'        => 'integer',
						'description' => 'Pie ID',
						'example'     => 12345,
					),
					'dividendCashAction' => array(
						'required'    => false,
						'type'        => 'string',
						'description' => 'Dividend cash action',
						'enum'        => array( 'REINVEST', 'TO_ACCOUNT_CASH' ),
						'example'     => 'REINVEST',
					),
					'endDate'            => array(
						'required'    => false,
						'type'        => 'string',
						'format'      => 'date-time',
						'description' => 'End date for the pie',
						'example'     => '2025-12-31T23:59:59Z',
					),
					'goal'               => array(
						'required'    => false,
						'type'        => 'number',
						'description' => 'Total desired value of the pie',
						'example'     => 10000,
					),
					'icon'               => array(
						'required'    => false,
						'type'        => 'string',
						'description' => 'Icon identifier',
						'example'     => 'technology',
					),
					'instrumentShares'   => array(
						'required'    => true,
						'type'        => 'object',
						'description' => 'Instrument shares allocation',
						'example'     => array(
							'AAPL_US_EQ' => 0.5,
							'MSFT_US_EQ' => 0.5,
						),
					),
					'name'               => array(
						'required'    => true,
						'type'        => 'string',
						'description' => 'Pie name',
						'example'     => 'Tech Giants',
					),
				),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'pies:write' ),
				'example_response' => array(),
			),
			'rebalance_pie'        => array(
				'endpoint'         => '/api/v0/equity/pies/{id}',
				'method'           => 'POST',
				'description'      => 'Manually trigger a pie rebalance',
				'parameters'       => array(
					'id' => array(
						'required'    => true,
						'type'        => 'integer',
						'description' => 'Pie ID',
						'example'     => 12345,
					),
				),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'pies:write' ),
				'example_response' => array(),
			),

			// Historical Data
			'history_orders'       => array(
				'endpoint'         => '/api/v0/equity/history/orders',
				'method'           => 'GET',
				'description'      => 'Get historical orders',
				'parameters'       => array(
					'cursor' => array(
						'required'    => false,
						'type'        => 'integer',
						'description' => 'Pagination cursor',
						'example'     => 0,
					),
					'ticker' => array(
						'required'    => false,
						'type'        => 'string',
						'description' => 'Filter by ticker',
						'example'     => 'AAPL_US_EQ',
					),
					'limit'  => array(
						'required'    => false,
						'type'        => 'integer',
						'description' => 'Number of results to return (max 50)',
						'default'     => 20,
						'example'     => 20,
					),
				),
				'rate_limit'       => '6 requests per minute',
				'scopes'           => array( 'history:orders' ),
				'example_response' => array(
					'items'        => array(
						// Order history items
					),
					'nextPagePath' => '/api/v0/equity/history/orders?cursor=123456',
				),
			),
			'history_dividends'    => array(
				'endpoint'         => '/api/v0/equity/history/dividends',
				'method'           => 'GET',
				'description'      => 'Get historical dividends',
				'parameters'       => array(
					'cursor' => array(
						'required'    => false,
						'type'        => 'integer',
						'description' => 'Pagination cursor',
						'example'     => 0,
					),
					'ticker' => array(
						'required'    => false,
						'type'        => 'string',
						'description' => 'Filter by ticker',
						'example'     => 'AAPL_US_EQ',
					),
					'limit'  => array(
						'required'    => false,
						'type'        => 'integer',
						'description' => 'Number of results to return (max 50)',
						'default'     => 20,
						'example'     => 20,
					),
				),
				'rate_limit'       => '6 requests per minute',
				'scopes'           => array( 'history:dividends' ),
				'example_response' => array(
					'items'        => array(
						// Dividend history items
					),
					'nextPagePath' => '/api/v0/history/dividends?cursor=123456',
				),
			),
			'history_transactions' => array(
				'endpoint'         => '/api/v0/equity/history/transactions',
				'method'           => 'GET',
				'description'      => 'Get historical transactions',
				'parameters'       => array(
					'cursor' => array(
						'required'    => false,
						'type'        => 'string',
						'description' => 'Pagination cursor',
						'example'     => '123456',
					),
					'time'   => array(
						'required'    => false,
						'type'        => 'string',
						'format'      => 'date-time',
						'description' => 'Retrieve transactions starting from the specified time',
						'example'     => '2025-01-01T00:00:00Z',
					),
					'limit'  => array(
						'required'    => false,
						'type'        => 'integer',
						'description' => 'Number of results to return (max 50)',
						'default'     => 20,
						'example'     => 20,
					),
				),
				'rate_limit'       => '6 requests per minute',
				'scopes'           => array( 'history:transactions' ),
				'example_response' => array(
					'items'        => array(
						// Transaction history items
					),
					'nextPagePath' => '/api/v0/history/transactions?cursor=789012',
				),
			),
			'request_report'       => array(
				'endpoint'         => '/api/v0/history/exports',
				'method'           => 'POST',
				'description'      => 'Request a CSV export of orders, dividends and transactions history',
				'parameters'       => array(
					'dataIncluded' => array(
						'required'    => true,
						'type'        => 'object',
						'description' => 'Which data types to include in the export',
						'example'     => array(
							'includeDividends'    => true,
							'includeInterest'     => true,
							'includeOrders'       => true,
							'includeTransactions' => true,
						),
					),
					'timeFrom'     => array(
						'required'    => true,
						'type'        => 'string',
						'format'      => 'date-time',
						'description' => 'Start of the export period (ISO 8601)',
						'example'     => '2025-01-01T00:00:00Z',
					),
					'timeTo'       => array(
						'required'    => true,
						'type'        => 'string',
						'format'      => 'date-time',
						'description' => 'End of the export period (ISO 8601)',
						'example'     => '2025-04-30T23:59:59Z',
					),
				),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'history:exports' ),
				'example_response' => array(
					'reportId' => 12345,
				),
			),
			'list_exports'         => array(
				'endpoint'         => '/api/v0/history/exports',
				'method'           => 'GET',
				'description'      => 'List previously requested CSV exports',
				'parameters'       => array(),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'history:exports' ),
				'example_response' => array(
					array(
						'reportId'   => 12345,
						'status'     => 'Finished',
						'downloadLink' => 'https://...',
					),
				),
			),
			'download_export'      => array(
				'endpoint'         => '/api/v0/history/exports/{reportId}',
				'method'           => 'GET',
				'description'      => 'Download a specific CSV export by report ID',
				'parameters'       => array(
					'reportId' => array(
						'required'    => true,
						'type'        => 'integer',
						'description' => 'Report ID returned by request_report',
						'example'     => 12345,
					),
				),
				'rate_limit'       => '1 request per 5 seconds',
				'scopes'           => array( 'history:exports' ),
				'example_response' => null,
			),
		);
	}

	/**
	 * Get endpoint configuration by name
	 *
	 * @param string $endpoint_name The name of the endpoint
	 * @return array|false Endpoint configuration or false if not found
	 * @version 1.1.0
	 */
	public static function get_endpoint( $endpoint_name ) {
		$endpoints = self::get_endpoints();
		return isset( $endpoints[ $endpoint_name ] ) ? $endpoints[ $endpoint_name ] : false;
	}

	/**
	 * Get endpoint URL
	 *
	 * @param string $endpoint_name The name of the endpoint
	 * @param array  $params Parameters to include in the URL
	 * @param string $base_url Base API URL
	 * @return string Complete endpoint URL
	 * @version 1.1.0
	 */
	public static function get_endpoint_url( $endpoint_name, $params = array(), $base_url = '' ) {
		$endpoint = self::get_endpoint( $endpoint_name );

		if ( ! $endpoint ) {
			return '';
		}

		if ( empty( $base_url ) ) {
			// Use live or demo base URL depending on account mode setting.
			$demo_mode = get_option( 'tradepress_trading212_demo_mode', 'yes' );
			$base_url  = ( $demo_mode === 'yes' ) ? 'https://demo.trading212.com' : 'https://live.trading212.com';
		}

		$url = $base_url . $endpoint['endpoint'];

		// Replace URL parameters (e.g., {id}, {ticker})
		if ( ! empty( $params ) ) {
			foreach ( $params as $key => $value ) {
				$url = str_replace( '{' . $key . '}', urlencode( $value ), $url );
			}
		}

		// For GET requests, add query parameters
		if ( $endpoint['method'] === 'GET' && ! empty( $params ) ) {
			$query_params = array();

			// Extract URL parameters that are already used in path
			$path_params = array();
			preg_match_all( '/\{([^}]+)\}/', $endpoint['endpoint'], $matches );
			if ( ! empty( $matches[1] ) ) {
				$path_params = $matches[1];
			}

			// Add remaining parameters as query string
			foreach ( $params as $key => $value ) {
				if ( ! in_array( $key, $path_params ) ) {
					$query_params[ $key ] = $value;
				}
			}

			if ( ! empty( $query_params ) ) {
				$url .= '?' . http_build_query( $query_params );
			}
		}

		return $url;
	}
}
