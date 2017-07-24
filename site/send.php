<?php
header( 'Content-Type: text/html; charset=utf-8' );
require_once( '../../../core/includes/master.inc.php' );
$pluginConfig                 = pluginHelper::pluginSpecificConfiguration( 'ir123pay' );
$pluginSettings               = $pluginConfig['data']['plugin_settings'];
$ir123pay_webgate_merchant_id = '';
if ( strlen( $pluginSettings ) ) {
	$pluginSettingsArr            = json_decode( $pluginSettings, true );
	$ir123pay_webgate_merchant_id = $pluginSettingsArr['ir123pay_webgate_merchant_id'];
}
if ( ! isset( $_REQUEST['days'] ) ) {
	coreFunctions::redirect( WEB_ROOT . '/index.html' );
}

if ( ! isset( $_REQUEST['i'] ) ) {
	$Auth->requireUser( WEB_ROOT . '/login.' . SITE_CONFIG_PAGE_EXTENSION );
	$userId    = $Auth->id;
	$username  = $Auth->username;
	$userEmail = $Auth->email;
} else {
	$user = UserPeer::loadUserByIdentifier( $_REQUEST['i'] );
	if ( ! $user ) {
		die( 'چنین کاربری وجود ندارد' );
	}

	$userId    = $user->id;
	$username  = $user->username;
	$userEmail = $user->email;
}
$days   = (int) ( trim( $_REQUEST['days'] ) );
$fileId = null;
if ( isset( $_REQUEST['f'] ) ) {
	$file = file::loadByShortUrl( $_REQUEST['f'] );
	if ( $file ) {
		$fileId = $file->id;
	}
}

$orderHash  = MD5( time() . $userId );
$amount     = intval( constant( 'SITE_CONFIG_COST_FOR_' . $days . '_DAYS_PREMIUM' ) ) * 10;
$order      = OrderPeer::create( $userId, $orderHash, $days, $amount, $fileId );
$return_url = urldecode( PLUGIN_WEB_ROOT . '/' . $pluginConfig['data']['folder_name'] . '/site/return.php' ) . '?custom=' . urlencode( $orderHash ) . '&mc_gross=' . $amount;
if ( $order ) {
	$merchant_id  = $ir123pay_webgate_merchant_id;
	$callback_url = $return_url;

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, 'https://123pay.ir/api/v1/create/payment' );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, "merchant_id=$merchant_id&amount=$amount&callback_url=$callback_url" );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$response = curl_exec( $ch );
	curl_close( $ch );

	$result = json_decode( $response );
	if ( $result->status ) {
		Header( 'Location: ' . $result->payment_url );
	} else {
		die( $result->message );
	}
}