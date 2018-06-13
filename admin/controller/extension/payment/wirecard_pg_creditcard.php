<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

/**
 * Class ControllerExtensionPaymentWirecardPGCreditCard
 *
 * CreditCard payment transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGCreditCard extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'creditcard';

	/**
	 * Credit Card default configuration settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $default = array (
		'title' => 'Wirecard Credit Card',
		'merchant_account_id' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
		'merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'three_d_merchant_account_id' => '508b8896-b37d-4614-845c-26bf8bf2c948',
		'three_d_merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'ssl_max_limit' => 100,
		'three_d_min_limit' => 50,
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => 'qD2wzQ_hrc!8',
		'http_user' => '70000-APITEST-AP',
		'payment_action' => 'pay',
		'descriptor' => '0',
		'additional_info' => '1',
	);

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index() {
		parent::index();
	}

	/**
	 * Get text for config fields
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function getConfigText() {
		$data = parent::getConfigText();

		$data['config_three_d_merchant_account_id'] = $this->language->get('config_three_d_merchant_account_id');
		$data['config_three_d_merchant_account_id_desc'] = $this->language->get('config_three_d_merchant_account_id_desc');
		$data['config_three_d_merchant_secret'] = $this->language->get('config_three_d_merchant_secret');
		$data['config_three_d_merchant_secret_desc'] = $this->language->get('config_three_d_merchant_secret_desc');
		$data['config_ssl_max_limit'] = $this->language->get('config_ssl_max_limit');
		$data['config_three_d_min_limit'] = $this->language->get('config_three_d_min_limit');
		$data['config_limit_desc'] = $this->language->get('config_limit_desc');

		return $data;
	}

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function getRequestData() {
		$data = parent::getRequestData();
		$prefix = $this->prefix . $this->type . '_';

		if (isset($this->request->post[$prefix . 'three_d_merchant_account_id'])) {
			$data['three_d_merchant_account_id'] = $this->request->post[$prefix . 'three_d_merchant_account_id'];
		} else {
			$data['three_d_merchant_account_id'] = strlen($this->config->get($prefix . 'three_d_merchant_account_id')) ? $this->config->get($prefix . 'three_d_merchant_account_id') : $this->default['three_d_merchant_account_id'];
		}

		if (isset($this->request->post[$prefix . 'three_d_merchant_secret'])) {
			$data['three_d_merchant_secret'] = $this->request->post[$prefix . 'three_d_merchant_secret'];
		} else {
			$data['three_d_merchant_secret'] = strlen($this->config->get($prefix . 'three_d_merchant_secret')) ? $this->config->get($prefix . 'three_d_merchant_secret') : $this->default['three_d_merchant_secret'];
		}

		if (isset($this->request->post[$prefix . 'ssl_max_limit'])) {
			$data['ssl_max_limit'] = $this->request->post[$prefix . 'ssl_max_limit'];
		} else {
			$data['ssl_max_limit'] = strlen($this->config->get($prefix . 'ssl_max_limit')) ? $this->config->get($prefix . 'ssl_max_limit') : $this->default['ssl_max_limit'];
		}

		if (isset($this->request->post[$prefix . 'three_d_min_limit'])) {
			$data['three_d_min_limit'] = $this->request->post[$prefix . 'three_d_min_limit'];
		} else {
			$data['three_d_min_limit'] = strlen($this->config->get($prefix . 'three_d_min_limit')) ? $this->config->get($prefix . 'three_d_min_limit') : $this->default['three_d_min_limit'];
		}

		return $data;
	}

	protected function loadConfigBlocks($data) {
		$data = parent::loadConfigBlocks($data);

		$data['three_d_config'] = $this->load->view('extension/payment/wirecard_pg/three_d_config', $data);

		return $data;
	}
}
