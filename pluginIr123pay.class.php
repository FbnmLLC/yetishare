<?php

class pluginir123pay extends Plugin {

	public $config = null;

	public function __construct() {
		include( DOC_ROOT . '/plugins/ir123pay/_plugin_config.inc.php' );

		$this->config = $pluginConfig;
	}

	public function getPluginDetails() {
		return $this->config;
	}

	public function install() {
		$db = Database::getDatabase();

		$pre_ir123pay_webgate_merchant_id = $db->getValue( 'SELECT config_value FROM site_config WHERE config_key="ir123pay_payments_merchant_id" LIMIT 1' );
		if ( $pre_ir123pay_webgate_merchant_id ) {
			$pluginDetails = $this->getPluginDetails();

			$db = Database::getDatabase();
			$db->query( 'UPDATE plugin SET plugin_settings = :plugin_settings WHERE folder_name = :folder_name',
				array(
					'
					plugin_settings' => '{
						"ir123pay_webgate_merchant_id":"' . $pre_ir123pay_webgate_merchant_id . '"
					}',
					'folder_name'    => $pluginDetails['folder_name']
				)
			);

			$db->query( 'DELETE FROM site_config WHERE config_key="ir123pay_payments_merchant" LIMIT 1' );
		}

		return parent::install();
	}

	public function uninstall() {
		$db = Database::getDatabase();
		$db->query( 'DELETE FROM site_config WHERE config_key="ir123pay_payments_merchant" LIMIT 1' );

		$pluginDetails = $this->getPluginDetails();
		$db->query( 'DELETE FROM plugin WHERE plugin_name="' . $pluginDetails["plugin_name"] . '" LIMIT 1' );

		return parent::uninstall();
	}

}