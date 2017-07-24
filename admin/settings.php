<?php
define( 'ADMIN_SELECTED_PAGE', 'plugins' );
define( 'ADMIN_SELECTED_SUB_PAGE', 'plugin_manage' );

include_once( '../../../core/includes/master.inc.php' );
include_once( DOC_ROOT . '/' . ADMIN_FOLDER_NAME . '/_local_auth.inc.php' );

$pluginId = (int) $_REQUEST['id'];
$plugin   = $db->getRow( "SELECT * FROM plugin WHERE id = " . (int) $pluginId . " LIMIT 1" );
if ( ! $plugin ) {
	adminFunctions::redirect( ADMIN_WEB_ROOT . '/plugin_manage.php?error=' . urlencode( 'There was a problem loading the plugin details.' ) );
}
define( 'ADMIN_PAGE_TITLE', $plugin['plugin_name'] . ' Plugin Settings' );

$plugin_enabled               = (int) $plugin['plugin_enabled'];
$ir123pay_webgate_merchant_id = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';

if ( strlen( $plugin['plugin_settings'] ) ) {
	$plugin_settings = json_decode( $plugin['plugin_settings'], true );
	if ( $plugin_settings ) {
		$ir123pay_webgate_merchant_id = $plugin_settings['ir123pay_webgate_merchant_id'];
	}
}

if ( isset( $_REQUEST['submitted'] ) ) {
	$plugin_enabled               = (int) $_REQUEST['plugin_enabled'];
	$plugin_enabled               = $plugin_enabled != 1 ? 0 : 1;
	$ir123pay_webgate_merchant_id = trim( strtolower( $_REQUEST['ir123pay_webgate_merchant_id'] ) );

	if ( _CONFIG_DEMO_MODE == true ) {
		adminFunctions::setError( adminFunctions::t( "no_changes_in_demo_mode" ) );
	} elseif ( strlen( $ir123pay_webgate_merchant_id ) == 0 ) {
		adminFunctions::setError( adminFunctions::t( "please_enter_your_ir123pay_webgate_merchant_id", "Please enter your 123pay merchant id" ) );
	}

	if ( adminFunctions::isErrors() == false ) {
		$settingsArr                                 = array();
		$settingsArr['ir123pay_webgate_merchant_id'] = $ir123pay_webgate_merchant_id;
		$settings                                    = json_encode( $settingsArr );

		$dbUpdate                  = new DBObject( "plugin", array( "plugin_enabled", "plugin_settings" ), 'id' );
		$dbUpdate->plugin_enabled  = $plugin_enabled;
		$dbUpdate->plugin_settings = $settings;
		$dbUpdate->id              = $pluginId;
		$dbUpdate->update();

		adminFunctions::redirect( ADMIN_WEB_ROOT . '/plugin_manage.php?se=1' );
	}
}

include_once( ADMIN_ROOT . '/_header.inc.php' );
?>

    <script>
        $(function () {
            $("#userForm").validationEngine();
        });
    </script>

    <div class="row clearfix">
        <div class="col_12">
            <div class="sectionLargeIcon" style="background: url(../assets/img/icons/128px.png) no-repeat;"></div>
            <div class="widget clearfix">
                <h2>سامانه پرداخت یک دو س پی</h2>
                <div class="widget_inside">
					<?php echo adminFunctions::compileNotifications(); ?>
                    <form method="POST" action="settings.php" name="pluginForm" id="pluginForm" autocomplete="off">
                        <div class="clearfix col_12">
                            <div class="col_4">
                                <h3>فعال سازی سامانه پرداخت یک دو سه پی</h3>
                                <p>در صورتی که میخواهید کاربر بتواند از طریق سامانه پرداخت یک دو سه پی قادر به
                                    پرداخت باشد باید درگاه را فعال نمایید .</p>
                            </div>
                            <div class="col_8 last">
                                <div class="form">
                                    <div class="clearfix alt-highlight">
                                        <label>فعال سازی :</label>
                                        <div class="input">
                                            <select name="plugin_enabled" id="plugin_enabled"
                                                    class="medium validate[required]">
												<?php
												$enabledOptions = array( 0 => 'خیر', 1 => 'بله' );
												foreach ( $enabledOptions AS $k => $enabledOption ) {
													echo '<option value="' . $k . '"';
													if ( $plugin_enabled == $k ) {
														echo ' SELECTED';
													}
													echo '>' . $enabledOption . '</option>';
												}
												?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix col_12">
                            <div class="col_4">
                                <h3>تنظیمات سامانه پرداخت یک دو سه پی</h3>
                                <p>جهت انجام عملیات پرداخت صحیح تنظیمات را به درستی انجام دهید .</p>
                                <p><a target="_blank" href="https://123pay.ir">سامانه پرداخت یک دو سه پی</a></p>
                            </div>
                            <div class="col_8 last">
                                <div class="form">
                                    <div class="clearfix alt-highlight">
                                        <label>API key</label>
                                        <div class="input"><input id="ir123pay_webgate_merchant_id"
                                                                  name="ir123pay_webgate_merchant_id"
                                                                  type="text" class="large validate[required]"
                                                                  value="<?php echo adminFunctions::makeSafe( $ir123pay_webgate_merchant_id ); ?>"/>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="clearfix col_12">
                            <div class="col_4 adminResponsiveHide">&nbsp;</div>
                            <div class="col_8 last">
                                <div class="clearfix">
                                    <div class="input no-label">
                                        <input type="submit" value="Submit" class="button blue">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input name="submitted" type="hidden" value="1"/>
                        <input name="id" type="hidden" value="<?php echo $pluginId; ?>"/>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
include_once( ADMIN_ROOT . '/_footer.inc.php' );
?>