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

/**
 * Class PGOrderManager
 *
 * @since 1.0.0
 */
class PGOrderManager extends Model {

	const PENDING = 1;
	const PROCESSING = 2;

	/**
	 * Create new order with specific orderstate
	 *
	 * @param \Wirecard\PaymentSdk\Response\Response $response
	 * @param ControllerExtensionPaymentGateway $paymentController
	 * @since 1.0.0
	 */
	public function createResponseOrder($response, $paymentController) {
		$this->load->model('checkout/order');
		$orderId = $response->getCustomFields()->get('orderId');
		$order = $this->model_checkout_order->getOrder($orderId);
		/** @var ModelExtensionPaymentGateway $transactionModel */
		$transactionModel = $paymentController->getModel();

		if (self::PENDING == $order['order_status']) {
			$this->model_checkout_order->addOrderHistory(
				$orderId,
				self::PENDING,
				'<pre>' . htmlentities($response->getRawData()) . '</pre>',
				false
			);
			$transactionModel->createTransaction($response, $order, 'awaiting', $paymentController->getType());
		}
	}

	/**
	 * Create new order with specific orderstate
	 *
	 * @param \Wirecard\PaymentSdk\Response\Response $response
	 * @param ControllerExtensionPaymentGateway $paymentController
	 * @since 1.0.0
	 */
	public function createNotifyOrder($response, $paymentController) {
		$orderId = $response->getCustomFields()->get('orderId');
		$this->load->model('checkout/order');
		$this->load->language('extension/payment/wirecard_pg');
		$order = $this->model_checkout_order->getOrder($orderId);
		/** @var ModelExtensionPaymentGateway $transactionModel */
		$transactionModel = $paymentController->getModel();

		//not in use yet but with order state US
		$backendService = new \Wirecard\PaymentSdk\BackendService($paymentController->getConfig());
		//Update an pending order state
		if (self::PENDING == $order['order_status_id']) {
			$this->model_checkout_order->addOrderHistory(
				$orderId,
				//update the order state
				2/*$this->getOrderState($backendService->getOrderState($response->getTransactionType()))*/,
				'<pre>' . htmlentities($response->getRawData()) . '</pre>',
				true
			);
			if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse && $transactionModel->getTransaction($response->getTransactionId())) {
				$transactionModel->updateTransactionState($response, 'success');
			} else {
				$transactionModel->createTransaction($response, $order, 'success', $paymentController->getType());
			}
		}
		//Cancel to implement
		if (self::PROCESSING == $order['order_status_id']) {
			if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
				$this->updateNotifyOrder($response, $transactionModel);
			}
		}
	}

	/**
	 * Update order state and transaction table
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $response
	 * @param ModelExtensionPaymentGateway $transactionModel
	 * @since 1.0.0
	 */
	public function updateNotifyOrder($response, $transactionModel) {
		//just for cancel operation for the moment
		if (\Wirecard\PaymentSdk\Transaction\Transaction::TYPE_VOID_AUTHORIZATION == $response->getTransactionType()) {
			$this->model_checkout_order->addOrderHistory(
				$response->getCustomFields()->get('orderId'),
				7,
				'<pre>' . htmlentities($response->getRawData()) . '</pre>',
				false
			);
			$transactionModel->updateTransactionState($response, 'success');
		}
	}
}
