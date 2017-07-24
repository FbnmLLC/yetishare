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
$paymentTracker = urldecode( $_REQUEST['custom'] );
$order          = OrderPeer::loadByPaymentTracker( $paymentTracker );
if ( $order ) {
	$status = 'cancelled';
	if ( isset( $_REQUEST['State'] ) AND $_REQUEST['State'] == 'OK' ) {
		$merchant_id = $ir123pay_webgate_merchant_id;
		$amount      = intval( $order->amount );
		$RefNum      = $_REQUEST['RefNum'];

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, 'https://123pay.ir/api/v1/verify/payment' );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, "merchant_id=$merchant_id&RefNum=$RefNum" );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		$response = curl_exec( $ch );
		curl_close( $ch );

		$result = json_decode( $response );
		if ( $result->status ) {
			$status = 'completed';
			$fault  = 0;
		} else {
			$status = 'failed';
			$fault  = $result->message;
		}
	}

	if ( $status == 'completed' ) {
		$extendedDays  = $order->days;
		$upgradeUserId = $order->upgrade_user_id;
		$orderId       = $order->id;
		$userId        = $order->user_id;
		$user          = $db->getRow( "SELECT * FROM users WHERE id = " . (int) $userId . " LIMIT 1" );
		$to_email      = SITE_CONFIG_DEFAULT_EMAIL_ADDRESS_FROM ? SITE_CONFIG_DEFAULT_EMAIL_ADDRESS_FROM : SITE_CONFIG_REPORT_ABUSE_EMAIL;
		$to_email      = $to_email ? $to_email : null;

		$response_vars = "Transaction_Id => " . $RefNum . "\n";

		$dbInsert                 = new DBObject( "payment_log",
			array(
				"user_id",
				"date_created",
				"amount",
				"currency_code",
				"from_email",
				"to_email",
				"description",
				"request_log",
				"payment_method"
			)
		);
		$dbInsert->user_id        = $userId;
		$dbInsert->date_created   = date( "Y-m-d H:i:s", time() );
		$dbInsert->amount         = $order->amount;
		$dbInsert->currency_code  = SITE_CONFIG_COST_CURRENCY_CODE;
		$dbInsert->from_email     = $user['email'];
		$dbInsert->to_email       = $to_email;
		$dbInsert->description    = $extendedDays . ' days extension';
		$dbInsert->request_log    = $response_vars;
		$dbInsert->payment_method = 'ir123pay';
		$dbInsert->insert();

		if ( $order->order_status == 'completed' ) {
			header( 'Location: ' . urldecode( WEB_ROOT . '/payment_complete.' . SITE_CONFIG_PAGE_EXTENSION ) );
		}

		$dbUpdate               = new DBObject( "premium_order", array( "order_status" ), 'id' );
		$dbUpdate->order_status = 'completed';
		$dbUpdate->id           = $orderId;
		$effectedRows           = $dbUpdate->update();
		if ( $effectedRows === false ) {
			die( 'متاسفانه در حین پرداخت خطایی رخ داده است' );
		}

		$upgrade = UserPeer::upgradeUser( $userId, $order->days );
		if ( $upgrade === false ) {
			die( 'متاسفانه در حین پرداخت خطایی رخ داده است' );
		}

		pluginHelper::includeAppends( 'payment_ipn_paypal.php' );
		header( 'Location: ' . urldecode( WEB_ROOT . '/payment_complete.' . SITE_CONFIG_PAGE_EXTENSION ) );
	}

	if ( $status == 'failed' ) {
		die( 'در حین پرداخت خطای رو به رو رخ داده است : ' . $result->message );
	}

	if ( $status == 'cancelled' ) {
		header( 'Location: ' . urldecode( WEB_ROOT . '/upgrade.' . SITE_CONFIG_PAGE_EXTENSION ) );
	}
} else {
	die( 'متاسفانه در حین پرداخت خطایی رخ داده است' );
}