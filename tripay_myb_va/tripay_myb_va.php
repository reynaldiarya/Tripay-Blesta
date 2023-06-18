<?php

/**
 * Tripay Gateway
 * The Tripay API documentation can be found at:
 * https://tripay.co.id/developer
 * @package blesta
 * @subpackage blesta.components.gateways.nonmerchant_demo
 * @copyright Copyright (c) 2023, Phillips Data, Inc.
 * @license http://www.blesta.com/license/ The Blesta License Agreement
 * @link http://www.blesta.com/ Blesta
 */
class TripayMybVa extends NonmerchantGateway
{
	/**
	 * @var array An array of meta data for this gateway
	 */
	private $meta;


	/**
	 * Construct a new merchant gateway
	 */
	public function __construct()
	{
		$this->loadConfig(dirname(__FILE__) . DS . 'config.json');

		// Load components required by this gateway
		Loader::loadComponents($this, array("Input"));

		// Load the language required by this gateway
		Language::loadLang("tripay_myb_va", null, dirname(__FILE__) . DS . "language" . DS);
	}

	/**
	 * Sets the currency code to be used for all subsequent payments
	 *
	 * @param string $currency The ISO 4217 currency code to be used for subsequent payments
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}

	/**
	 * Create and return the view content required to modify the settings of this gateway
	 *
	 * @param array $meta An array of meta (settings) data belonging to this gateway
	 * @return string HTML content containing the fields to update the meta data for this gateway
	 */
	public function getSettings(array $meta = null)
	{
		$this->view = $this->makeView("settings", "default", str_replace(ROOTWEBDIR, "", dirname(__FILE__) . DS));

		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));

		$select_options = [
			'1440' => Language::_('TripayMybVa.active_period.1day', true),
			'2880' => Language::_('TripayMybVa.active_period.2day', true),
			'4320' => Language::_('TripayMybVa.active_period.3day', true),
			'10080' => Language::_('TripayMybVa.active_period.7day', true),
			'20160' => Language::_('TripayMybVa.active_period.14day', true),
			'43200' => Language::_('TripayMybVa.active_period.30day', true)
		];
		$this->view->set("meta", $meta);
		$this->view->set("select_options", $select_options);

		return $this->view->fetch();
	}

	/**
	 * Validates the given meta (settings) data to be updated for this gateway
	 *
	 * @param array $meta An array of meta (settings) data to be updated for this gateway
	 * @return array The meta data to be updated in the database for this gateway, or reset into the form on failure
	 */
	public function editSettings(array $meta)
	{
		// Verify meta data is valid
		$rules = [
			'merchant_code' => [
				'valid' => [
					'rule' => 'isEmpty',
					'negate' => true,
					'message' => Language::_('TripayMybVa.!error.merchant_code.valid', true)
				]
			],
			'api_key' => [
				'valid' => [
					'rule' => 'isEmpty',
					'negate' => true,
					'message' => Language::_('TripayMybVa.!error.api_key.valid', true)
				]
			],
			'private_key' => [
				'valid' => [
					'rule' => 'isEmpty',
					'negate' => true,
					'message' => Language::_('TripayMybVa.!error.private_key.valid', true)
				]
			],
			'active_period' => [
				'valid' => [
					'rule' => ['in_array', ['1440', '2880', '4320', '10080', '20160', '43200']],
					'message' => Language::_('TripayMybVa.!error.active_period.valid', true)
				]
			],
		];

		// Set checkbox if not set
		if (!isset($meta['dev_mode'])) {
			$meta['dev_mode'] = 'false';
		}


		$this->Input->setRules($rules);

		// Validate the given meta data to ensure it meets the requirements
		$this->Input->validates($meta);
		// Return the meta data, no changes required regardless of success or failure for this gateway
		return $meta;
	}

	/**
	 * Returns an array of all fields to encrypt when storing in the database
	 *
	 * @return array An array of the field names to encrypt when storing in the database
	 */
	public function encryptableFields()
	{

		#
		# TODO: return an array of all meta field names to store encrypted
		#

		return ['merchant_code', 'api_key', 'private_key'];
	}

	/**
	 * Sets the meta data for this particular gateway
	 *
	 * @param array $meta An array of meta data to set for this gateway
	 */
	public function setMeta(array $meta = null)
	{
		$this->meta = $meta;
	}

	/**
	 * Returns all HTML markup required to render an authorization and capture payment form
	 *
	 * @param array $contact_info An array of contact info including:
	 * 	- id The contact ID
	 * 	- client_id The ID of the client this contact belongs to
	 * 	- user_id The user ID this contact belongs to (if any)
	 * 	- contact_type The type of contact
	 * 	- contact_type_id The ID of the contact type
	 * 	- first_name The first name on the contact
	 * 	- last_name The last name on the contact
	 * 	- title The title of the contact
	 * 	- company The company name of the contact
	 * 	- address1 The address 1 line of the contact
	 * 	- address2 The address 2 line of the contact
	 * 	- city The city of the contact
	 * 	- state An array of state info including:
	 * 		- code The 2 or 3-character state code
	 * 		- name The local name of the country
	 * 	- country An array of country info including:
	 * 		- alpha2 The 2-character country code
	 * 		- alpha3 The 3-cahracter country code
	 * 		- name The english name of the country
	 * 		- alt_name The local name of the country
	 * 	- zip The zip/postal code of the contact
	 * @param float $amount The amount to charge this contact
	 * @param array $invoice_amounts An array of invoices, each containing:
	 * 	- id The ID of the invoice being processed
	 * 	- amount The amount being processed for this invoice (which is included in $amount)
	 * @param array $options An array of options including:
	 * 	- description The Description of the charge
	 * 	- return_url The URL to redirect users to after a successful payment
	 * 	- recur An array of recurring info including:
	 * 		- amount The amount to recur
	 * 		- term The term to recur
	 * 		- period The recurring period (day, week, month, year, onetime) used in conjunction with term in order to determine the next recurring payment
	 * @return string HTML markup required to render an authorization and capture payment form
	 */
	public function buildProcess(array $contact_info, $amount, array $invoice_amounts = null, array $options = null)
	{
		// Load the models required
		Loader::loadModels($this, ['Clients']);
		$client = $this->Clients->get($contact_info['client_id']);

		// Load the helpers required for this view
		Loader::loadHelpers($this, ['Html']);

		// Load library methods
		Loader::load(dirname(__FILE__) . DS . 'lib' . DS . 'autoload.php');
		$merchantCode = $this->meta['merchant_code'];
		$apiKey = $this->meta['api_key'];
		$privateKey = $this->meta['private_key'];
		if ($this->meta['dev_mode'] === 'false') {
			$environment = \Tripay\Constants\Environment::PRODUCTION;
		} else {
			$environment = \Tripay\Constants\Environment::DEVELOPMENT;
		}
		$merchantRef = $this->serializeInvoices($invoice_amounts);
		$channelCode = 'MYBVA';
		$minutes = $this->meta['active_period']; // Waktu kedaluwarsa invoice (dalam menit, default 1440 = 24 jam);
		$customerName = ($contact_info['first_name'] ?? null) . ' ' . ($contact_info['last_name'] ?? null);
		$customerEmail = $client->email;
		$transaction = (new \Tripay\Transaction($environment))
			->apiKey($apiKey)
			->privateKey($privateKey)
			->merchantCode($merchantCode)
			->merchantRef($merchantRef)
			->channelCode($channelCode)
			->expiresAfter($minutes)
			->customerName($customerName)
			->customerEmail($customerEmail)
			->forClosedPayment();
		if (isset($options) && is_array($options)) {
			$productName = $options['description'];
			$price = $amount;
			$quantity = 1;
			$transaction
				->addItem($productName, $price, $quantity)
				->returnUrl($options['return_url']);
		}
		$response = $transaction->process();
		$paymentUrl = $response->data->checkout_url;

		if ($paymentUrl != null) {
			if ($amount <= 5000000 && $amount >= 10000) {
				try {
					$this->log((isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null), serialize($paymentUrl), 'output', true);
					return $this->buildForm($paymentUrl);
				} catch (\Exception $e) {
					echo $e->getMessage();
				}
			} else {
				$this->Input->setErrors($this->getCommonError("general"));
			}
		} else {
			$this->Input->setErrors($this->getCommonError("general"));
		}
	}

	/**
	 * Builds the HTML form.
	 *
	 * @param string $post_to The URL to post to
	 * @param array $fields An array of key/value input fields to set in the form
	 * @return string The HTML form
	 */
	private function buildForm($post_to)
	{
		$this->view = $this->makeView('process', 'default', str_replace(ROOTWEBDIR, '', dirname(__FILE__) . DS));

		// Load the helpers required for this view
		Loader::loadHelpers($this, ['Form', 'Html']);

		$this->view->set('post_to', $post_to);

		return $this->view->fetch();
	}

	/**
	 * Validates the incoming POST/GET response from the gateway to ensure it is
	 * legitimate and can be trusted.
	 *
	 * @param array $get The GET data for this request
	 * @param array $post The POST data for this request
	 * @return array An array of transaction data, sets any errors using Input if the data fails to validate
	 *  - client_id The ID of the client that attempted the payment
	 *  - amount The amount of the payment
	 *  - currency The currency of the payment
	 *  - invoices An array of invoices and the amount the payment should be applied to (if any) including:
	 *  	- id The ID of the invoice to apply to
	 *  	- amount The amount to apply to the invoice
	 * 	- status The status of the transaction (approved, declined, void, pending, reconciled, refunded, returned)
	 * 	- reference_id The reference ID for gateway-only use with this transaction (optional)
	 * 	- transaction_id The ID returned by the gateway to identify this transaction
	 */
	public function validate(array $get, array $post)
	{

		#
		# TODO: Verify the get/post data, then return the transaction
		#
		#
		Loader::load(dirname(__FILE__) . DS . 'lib' . DS . 'autoload.php');

		// ambil data json callback notifikasi
		$json = file_get_contents('php://input');
		// Ambil callback signature
		$callbackSignature = isset($_SERVER['HTTP_X_CALLBACK_SIGNATURE'])
			? $_SERVER['HTTP_X_CALLBACK_SIGNATURE']
			: '';

		// Isi dengan private key anda
		$privateKey = $this->meta['private_key'];

		// Generate signature untuk dicocokkan dengan X-Callback-Signature
		$signature = hash_hmac('sha256', $json, $privateKey);

		// Validasi signature
		if ($callbackSignature !== $signature) {
			exit(json_encode([
				'success' => false,
				'message' => 'Invalid signature',
			]));
		}

		$data = json_decode($json);

		if (JSON_ERROR_NONE !== json_last_error()) {
			exit(json_encode([
				'success' => false,
				'message' => 'Invalid data sent by payment gateway',
			]));
		}

		// Hentikan proses jika callback event-nya bukan payment_status
		if ('payment_status' !== $_SERVER['HTTP_X_CALLBACK_EVENT']) {
			exit(json_encode([
				'success' => false,
				'message' => 'Unrecognized callback event: ' . $_SERVER['HTTP_X_CALLBACK_EVENT'],
			]));
		}

		$order_id = $data->merchant_ref;
		$currency = 'IDR';
		//
		$transaction_id = $data->reference;
		if ($data->fee_customer === 0) {
			$amount = $data->total_amount;
		} else {
			$amount = $data->total_amount - $data->fee_customer;
		}
		$temp = explode('|', $order_id);
		foreach ($temp as $inv) {
			$tempclient = explode('=', $inv, 2);
			if (count($tempclient) != 2) {
				continue;
			}
			$dataclient[] = ['id' => $tempclient[0], 'amount' => $tempclient[1]];
		}

		$record = new Record();
		$client_id = $record->select('client_id')->from('invoices')->where('id', '=', $tempclient[0])->fetch();

		if ($data->is_closed_payment === 1) {

			if ($data->status == 'PAID') {
				// TODO set payment status in merchant's database to 'Settlement'
				$status = 'approved';
				$return_status = true;
			} else if ($data->status == 'UNPAID') {
				// TODO set payment status in merchant's database to 'Pending'
				$status = 'pending';
				$return_status = true;
			} else if ($data->status == 'FAILED') {
				// TODO set payment status in merchant's database to 'Denied'
				$status = 'declined';
				$return_status = true;
			} else if ($data->status == 'EXPIRED') {
				// TODO set payment status in merchant's database to 'expire'
				$status = 'void';
				$return_status = true;
			}
			$response = json_encode(['success' => true]);
			echo $response;
		}


		$this->log((isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null), serialize($data), 'output', $return_status);

		// Return the payment information
		return array(
			'client_id' => $client_id->client_id,
			'amount' => $amount,
			'currency' => $currency,
			'status' => $status,
			'reference_id' => null,
			'transaction_id' => $transaction_id,
			'parent_transaction_id' => null,
			'invoices' => $this->unserializeInvoices($order_id ?? null)
		);
	}

	/**
	 * Returns data regarding a success transaction. This method is invoked when
	 * a client returns from the non-merchant gateway's web site back to Blesta.
	 *
	 * @param array $get The GET data for this request
	 * @param array $post The POST data for this request
	 * @return array An array of transaction data, may set errors using Input if the data appears invalid
	 *  - client_id The ID of the client that attempted the payment
	 *  - amount The amount of the payment
	 *  - currency The currency of the payment
	 *  - invoices An array of invoices and the amount the payment should be applied to (if any) including:
	 *  	- id The ID of the invoice to apply to
	 *  	- amount The amount to apply to the invoice
	 * 	- status The status of the transaction (approved, declined, void, pending, reconciled, refunded, returned)
	 * 	- transaction_id The ID returned by the gateway to identify this transaction
	 */
	public function success(array $get, array $post)
	{

		#
		# TODO: Return transaction data, if possible
		#
		Loader::load(dirname(__FILE__) . DS . 'lib' . DS . 'autoload.php');


		// Isi dengan private key anda
		$apiKey = $this->meta['api_key'];
		$reference = $get['tripay_reference'];
		if ($this->meta['dev_mode'] === 'false') {
			$environment = \Tripay\Constants\Environment::PRODUCTION;
		} else {
			$environment = \Tripay\Constants\Environment::DEVELOPMENT;
		}
		$transaction = (new \Tripay\Transaction($environment))
			->apiKey($apiKey)
			->forClosedPayment();

		$detail = $transaction->detail($reference);
		$order_id = $detail->data->merchant_ref;
		$currency = 'IDR';
		//
		$transaction_id = $detail->data->reference;
		if ($data->fee_customer === 0) {
			$amount = $detail->data->amount;
		} else {
			$amount = $detail->data->amount - $detail->data->fee_customer;
		}

		if ($detail->data->status == 'PAID') {
			// TODO set payment status in merchant's database to 'Settlement'

			$status = 'approved';
			$return_status = true;
		} else if ($detail->data->status == 'UNPAID') {
			// TODO set payment status in merchant's database to 'Pending'
			$status = 'pending';
			$return_status = true;
		} else if ($detail->data->status == 'FAILED') {
			// TODO set payment status in merchant's database to 'Denied'
			$status = 'declined';
			$return_status = true;
		} else if ($detail->data->status == 'EXPIRED') {
			// TODO set payment status in merchant's database to 'expire'
			$status = 'void';
			$return_status = true;
		}

		$this->log((isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null), serialize($get), 'output', $return_status);
		return array(
			'client_id' => $get['client_id'],
			'amount' => $amount,
			'currency' => $currency,
			'status' => $status,
			'reference_id' => null,
			'transaction_id' => $transaction_id,
			'parent_transaction_id' => null,
			'invoices' => $this->unserializeInvoices($order_id ?? null)
		);
	}


	/**
	 * Captures a previously authorized payment
	 *
	 * @param string $reference_id The reference ID for the previously authorized transaction
	 * @param string $transaction_id The transaction ID for the previously authorized transaction
	 * @return array An array of transaction data including:
	 * 	- status The status of the transaction (approved, declined, void, pending, reconciled, refunded, returned)
	 * 	- reference_id The reference ID for gateway-only use with this transaction (optional)
	 * 	- transaction_id The ID returned by the remote gateway to identify this transaction
	 * 	- message The message to be displayed in the interface in addition to the standard message for this transaction status (optional)
	 */
	public function capture($reference_id, $transaction_id, $amount, array $invoice_amounts = null)
	{

		#
		# TODO: Return transaction data, if possible
		#

		$this->Input->setErrors($this->getCommonError("unsupported"));
	}

	/**
	 * Void a payment or authorization
	 *
	 * @param string $reference_id The reference ID for the previously submitted transaction
	 * @param string $transaction_id The transaction ID for the previously submitted transaction
	 * @param string $notes Notes about the void that may be sent to the client by the gateway
	 * @return array An array of transaction data including:
	 * 	- status The status of the transaction (approved, declined, void, pending, reconciled, refunded, returned)
	 * 	- reference_id The reference ID for gateway-only use with this transaction (optional)
	 * 	- transaction_id The ID returned by the remote gateway to identify this transaction
	 * 	- message The message to be displayed in the interface in addition to the standard message for this transaction status (optional)
	 */
	public function void($reference_id, $transaction_id, $notes = null)
	{

		#
		# TODO: Return transaction data, if possible
		#

		$this->Input->setErrors($this->getCommonError("unsupported"));
	}

	/**
	 * Refund a payment
	 *
	 * @param string $reference_id The reference ID for the previously submitted transaction
	 * @param string $transaction_id The transaction ID for the previously submitted transaction
	 * @param float $amount The amount to refund this card
	 * @param string $notes Notes about the refund that may be sent to the client by the gateway
	 * @return array An array of transaction data including:
	 * 	- status The status of the transaction (approved, declined, void, pending, reconciled, refunded, returned)
	 * 	- reference_id The reference ID for gateway-only use with this transaction (optional)
	 * 	- transaction_id The ID returned by the remote gateway to identify this transaction
	 * 	- message The message to be displayed in the interface in addition to the standard message for this transaction status (optional)
	 */
	public function refund($reference_id, $transaction_id, $amount, $notes = null)
	{

		#
		# TODO: Return transaction data, if possible
		#

		$this->Input->setErrors($this->getCommonError("unsupported"));
	}

	/**
	 * Serializes an array of invoice info into a string
	 *
	 * @param array A numerically indexed array invoices info including:
	 *  - id The ID of the invoice
	 *  - amount The amount relating to the invoice
	 * @return string A serialized string of invoice info in the format of key1=value1|key2=value2
	 */
	private function serializeInvoices(array $invoices)
	{
		$str = '';
		foreach ($invoices as $i => $invoice) {
			$str .= ($i > 0 ? '|' : '') . $invoice['id'] . '-' . intval($invoice['amount']);
		}

		return $str;
	}

	/**
	 * Unserializes a string of invoice info into an array
	 *
	 * @param string A serialized string of invoice info in the format of key1=value1|key2=value2
	 * @return array A numerically indexed array invoices info including:
	 *  - id The ID of the invoice
	 *  - amount The amount relating to the invoice
	 */
	private function unserializeInvoices($str)
	{
		$invoices = [];
		$temp = explode('|', $str);
		foreach ($temp as $pair) {
			$pairs = explode('-', $pair, 2);
			if (count($pairs) != 2) {
				continue;
			}
			$invoices[] = ['id' => $pairs[0], 'amount' => $pairs[1]];
		}

		return $invoices;
	}
}
