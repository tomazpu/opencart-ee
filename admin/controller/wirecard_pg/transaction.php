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

require_once __DIR__ . '/panel.php';

/**
 * Class ControllerWirecardPGTransaction
 *
 * Basic payment extension controller
 *
 * @since 1.0.0
 */
class ControllerWirecardPGTransaction extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg';
	const PANEL = 'wirecard_pg/panel';
	const TRANSACTION = 'wirecard_pg/transaction';

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index() {
		$this->load->language(self::ROUTE);
		$panel = new ControllerWirecardPGPanel($this->registry);

		$data['title'] = $this->language->get('heading_transaction_details');

		$this->document->setTitle($data['title']);

		$data['breadcrumbs'] = $panel->getBreadcrumbs();

		$data = array_merge($data, $panel->getCommons());

		$data['text_transaction'] = $this->language->get('text_transaction');
		$data['text_response_data'] = $this->language->get('text_response_data');
		$data['text_backend_operations'] = $this->language->get('text_backend_operations');
		$data['route_href'] = $this->url->link(self::TRANSACTION . '/');

		if (isset($this->request->get['id'])) {
			$data['transaction'] = $this->getTransactionDetails($this->request->get['id']);
		} else {
			$data['error'] = $this->language->get('error_no_transaction');
		}

		$this->response->setOutput($this->load->view('extension/wirecard_pg/details', $data));
	}

	/**
	 * Get transaction detail data via id
	 *
	 * @param $id
	 * @return bool|array
	 * @since 1.0.0
	 */
	public function getTransactionDetails($id) {
		$this->load->model(self::ROUTE);
		$transaction = $this->model_extension_payment_wirecard_pg->getTransaction($id);
		$operations = $this->getBackendOperations($transaction);
		$data = false;

		if ($transaction) {
			$data = array(
				'transaction_id' => $transaction['transaction_id'],
				'response' => json_decode($transaction['response'], true),
				'operations' => $operations
			);
		}

		return $data;
	}

	/**
	 * Retrieve backend operations for specific transaction
	 *
	 * @param array $parentTransaction
	 * @return array|bool
	 * @since 1.0.0
	 */
	private function getBackendOperations($parentTransaction) {
		$files = glob(
			DIR_CATALOG . 'controller/extension/payment/wirecard_pg_*.php',
			GLOB_BRACE
		);

		/** @var ControllerExtensionPaymentGateway $controller */
		foreach ($files as $file) {
			if (is_file($file) && strpos($file, $parentTransaction['payment_method'])) {
				//load catalog controller
				require_once($file);
				$controller = new ControllerExtensionPaymentWirecardPGPayPal($this->registry);
				/** @var \Wirecard\PaymentSdk\Transaction\Transaction $transaction */
				$transaction = $controller->getTransactionInstance();
				$transaction->setParentTransactionId($parentTransaction['transaction_id']);

				$backendService = new \Wirecard\PaymentSdk\BackendService($controller->getConfig());
				$backOperations = $backendService->retrieveBackendOperations($transaction, true);

				if ($backOperations) {
					$operations = array();
					foreach ($backOperations as $item => $value) {
						$key = key($value);
						$op = array(
							'action' => $this->url->link(self::TRANSACTION . '/' . $key,
								'user_token=' . $this->session->data['user_token'] . '&id=' . $parentTransaction['transaction_id'],
								true),
							'text' => $value[$key]
						);
						array_push($operations, $op);
					}

					return $operations;
				}
			}
		}

		return false;
	}

	/**
	 * Handle cancel transactions
	 *
	 * @since 1.0.0
	 */
	public function cancel() {
		$this->load->language(self::ROUTE);
		$panel = new ControllerWirecardPGPanel($this->registry);

		$data['title'] = $this->language->get('heading_transaction_details');

		$this->document->setTitle($data['title']);

		$data['breadcrumbs'] = $panel->getBreadcrumbs();

		$data = array_merge($data, $panel->getCommons());

		if (isset($this->request->get['id'])) {
			$data['transaction'] = $this->getTransactionDetails($this->request->get['id']);
		} else {
			$data['error'] = $this->language->get('error_no_transaction');
		}

		$this->response->setOutput($this->load->view('extension/wirecard_pg/details', $data));
	}
}
