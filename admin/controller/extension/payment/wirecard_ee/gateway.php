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
 * Class ControllerExtensionPaymentAdminGateway
 *
 * Basic payment extension controller
 *
 * @since 1.0.0
 */
abstract class ControllerExtensionPaymentAdminGateway extends Controller{

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $prefix = 'payment_wirecard_ee_';

	/**
	 * Load common headers and template file including config values
	 *
	 * @since 1.0.0
	 */
	public function index() {
		$this->load->language('extension/payment/wirecard_ee');
		$this->load->language('extension/payment/wirecard_ee_' . $this->type );

		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting($this->prefix . $this->type, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		// prefix for payment type
		$data['prefix'] = $this->prefix . $this->type . '_';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['action'] = $this->url->link('extension/payment/wirecard_ee_' . $this->type, 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		$data = array_merge($data, $this->createBreadcrumbs());

		$data = array_merge($data, $this->getConfigText());

		$data = array_merge($data, $this->getRequestData());

		$this->response->setOutput($this->load->view('extension/payment/wirecard_ee', $data));
	}

	/**
	 * Get text for config fields
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function getConfigText() {
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['config_status'] = $this->language->get('config_status');

		return $data;
	}

	/**
	 * Create breadcrumbs
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function createBreadcrumbs() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/wirecard_ee_' . $this->type, 'user_token=' . $this->session->data['user_token'], true)
		);

		return $data;
	}

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function getRequestData() {
		$data = array();

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post[$this->prefix . $this->type . '_status'];
		} else {
			$data['status'] = $this->config->get($this->prefix . $this->type . '_status');
		}

		return $data;
	}

	/**
	 * Validate specific fields
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/wirecard_ee_' . $this->type )) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
