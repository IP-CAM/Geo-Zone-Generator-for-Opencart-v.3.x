<?php
class ControllerExtensionModuleGeoZoneGenerator extends Controller {

	private $error = array();

	public function generateZones() {
		$iso_code_2 = $this->request->get['iso_code_2'];

		$this->load->model('extension/module/geo_zone_generator');

		$countries = $this->model_extension_module_geo_zone_generator->getCountries();

		// Make sure there is no existing zones
		$pre_existing_zones = $this->model_extension_module_geo_zone_generator->checkForExistingZones($iso_code_2);
 
		$error = '';
		if(!$pre_existing_zones) {
			// Add zones
			$result = $this->model_extension_module_geo_zone_generator->addZones($iso_code_2);
			// Format log results for display
			foreach($result as &$line) {
				$line .= '<br>';
			}
		} else {
			$error = 'You have pre-existing zones setup for ' . $countries[$iso_code_2]['name'];
			$data = array(
				'error' => $error,
			);
		}
		
		if(!$error) {
			$data = array(
				'result' => $result,
			);
		}

		echo json_encode($data);
	}

	public function index()
	{
		$this->load->language('extension/module/geo_zone_generator');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		$this->load->model('extension/module/geo_zone_generator');

		$data['countries'] = $this->model_extension_module_geo_zone_generator->getCountries();

		$data['user_token'] = $this->session->data['user_token'];

		$data['entry_email_footer'] = $this->language->get('entry_email_footer');
		$data['entry_email_header'] = $this->language->get('entry_email_header');
		$data['help_options'] = $this->language->get('help_locations_of_other_data');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_geo_zone_generator', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/gdpr', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/geo_zone_generator', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/geo_zone_generator', $data));
	}

	public function install() {

        $this->load->language('extension/extension/module');
        $this->load->model('setting/setting');

		if (!$this->user->hasPermission('access', 'marketplace/extension')) {
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
		} else {
			$this->load->model('user/user_group');

            // Add user permissions
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/geo_zone_generator');
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/geo_zone_generator');

            $starter_settings = array();
			$this->model_setting_setting->editSetting('module_geo_zone_generator', $starter_settings);

			//$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

	}

 	public function uninstall() {
		// TODO
    }


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/geo_zone_generator')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}


}
