<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

/**
 * Class ControllerExtensionPaymentWirecardPGMasterpass
 *
 * Masterpass Transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGMasterpass extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'masterpass';

	/**
	 * Create Masterpass transaction
	 *
	 * @since 1.1.0
	 */
	public function confirm() {
		$this->transaction = $this->getTransactionInstance();

		parent::confirm();
	}

	/**
	 * Prepare the Masterpass transaction as required.
	 *
	 * @param $force_data
	 * @since 1.1.0
	 */
	public function prepareTransaction($force_data = false) {
		parent::prepareTransaction($force_data);

		$this->load->model('checkout/order');

		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$additional_helper = new AdditionalInformationHelper($this->registry, $this->prefix . $this->type, $this->config);

		$this->transaction = $additional_helper->addAccountHolder($this->transaction, $order, false);
	}

	/**
	 * Create payment specific config
	 *
	 * @param array $currency
	 * @return \Wirecard\PaymentSdk\Config\Config
	 * @since 1.1.0
	 */
	public function getConfig($currency = null) {
		$merchant_account_id = $this->getShopConfigVal('merchant_account_id');
		$merchant_secret = $this->getShopConfigVal('merchant_secret');

		$config = parent::getConfig($currency);
		$payment_config = new PaymentMethodConfig(MasterpassTransaction::NAME, $merchant_account_id, $merchant_secret);
		$config->add($payment_config);

		return $config;
	}

	/**
	 * Payment specific model getter
	 *
	 * @return Model
	 * @since 1.1.0
	 */
	public function getModel() {
		$this->load->model('extension/payment/wirecard_pg_' . $this->type);

		return $this->model_extension_payment_wirecard_pg_masterpass;
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return MasterpassTransaction
	 * @since 1.1.0
	 */
	public function getTransactionInstance() {
		return new MasterpassTransaction();
	}

	/**
	 * Create Masterpass transaction
	 *
	 * @param array $parent_transaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @param string $operation
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.1.0
	 */
	public function createTransaction($parent_transaction, $amount) {
		$this->transaction = $this->getTransactionInstance();

		return parent::createTransaction($parent_transaction, $amount);
	}
}

