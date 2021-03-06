<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

/**
 * Class ControllerExtensionPaymentWirecardPGRatepayInvoice
 *
 * Guaranteed Invoice Transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGRatepayInvoice extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'ratepayinvoice';

	/**
	 * Basic index method
	 *
	 * @param array $data
	 * @return array
	 * @since 1.1.0
	 */
	public function index($data = null) {
		$this->load->language('extension/payment/wirecard_pg_ratepayinvoice');
		$data['birthdate_input'] = $this->language->get('birthdate_input');
		$data['birthdate_error'] = $this->language->get('ratepayinvoice_fields_error');

		$data['ratepayinvoice'] = $this->load->view('extension/payment/wirecard_pg_ratepayinvoice', $data);
		return parent::index($data);
	}

	/**
	 * Create Ratepay-Invoice transaction
	 *
	 * @since 1.1.0
	 */
	public function confirm() {
		$this->transaction = new RatepayInvoiceTransaction();
		$this->prepareTransaction();

		parent::confirm();
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
		$payment_config = new PaymentMethodConfig(RatepayInvoiceTransaction::NAME, $merchant_account_id, $merchant_secret);
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

		return $this->model_extension_payment_wirecard_pg_ratepayinvoice;
	}

	/**
	 * Create Ratepay-Invoice transaction
	 *
	 * @param array $parent_transaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.1.0
	 */
	public function createTransaction($parent_transaction, $amount) {
		$this->transaction = new RatepayInvoiceTransaction();

		return parent::createTransaction($parent_transaction, $amount);
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return RatepayInvoiceTransaction
	 * @since 1.1.0
	 */
	public function getTransactionInstance() {
		return new RatepayInvoiceTransaction();
	}
}

