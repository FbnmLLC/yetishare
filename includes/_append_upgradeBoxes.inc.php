<?php
$pluginConfig = pluginHelper::pluginSpecificConfiguration('ir123pay');
?>
<div style="text-align: center; padding: 3px;">
    <form id="form<?php echo $days; ?>" action="<?php echo PLUGIN_WEB_ROOT; ?>/<?php echo $pluginConfig['data']['folder_name']; ?>/site/send.php" method="post">
        <input type="hidden" name="days" value="<?php echo $days; ?>" />
        <?php
        if (isset($_REQUEST['i']))
        {
            echo '<input type="hidden" name="i" value="' . htmlentities($_REQUEST['i']) . '" />';
        }
        if (isset($_REQUEST['f']))
        {
            echo '<input type="hidden" name="f" value="' . htmlentities($_REQUEST['f']) . '" />';
        }
        ?>
<input type="submit" value="&#1582;&#1585;&#1740;&#1583;" class="buy_premium" title="&#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1575;&#1586; &#1591;&#1585;&#1740;&#1602; &#1705;&#1604;&#1740;&#1607; &#1705;&#1575;&#1585;&#1578; &#1607;&#1575;&#1740; &#1593;&#1590;&#1608; &#1588;&#1578;&#1575;&#1576;" alt="&#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1575;&#1586; &#1591;&#1585;&#1740;&#1602; &#1705;&#1604;&#1740;&#1607; &#1705;&#1575;&#1585;&#1578; &#1607;&#1575;&#1740; &#1593;&#1590;&#1608; &#1588;&#1578;&#1575;&#1576;">

    </form>

</div>