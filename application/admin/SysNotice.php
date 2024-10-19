<?php

namespace ExternalImporter\application\admin;

use ExternalImporter\application\Plugin;

defined('\ABSPATH') || exit;

/**
 * SysNotice class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2024 keywordrush.com
 */
class SysNotice
{
    public function __construct()
    {
        \add_action('admin_notices', array($this, 'displayStatusNotice'));
        \add_action('template_redirect', array($this, 'sendStatusEmail'));
    }

    public function displayStatusNotice()
    {
        $status = \get_option(Plugin::getShortSlug() . '_sys_status');
        if ($status == 'invalid')
        {
            $plugin_name = Plugin::getName();
            $coupon_code = 'WOW30';
            $plugin_url = strtok(Plugin::pluginSiteUrl(), '?');
            $coupon_link =  $plugin_url . '/pricing?ref=' . $coupon_code;
            if (!$deadline = \get_option(Plugin::getShortSlug() . '_sys_deadline', 0))
                $deadline = time();
            $deadline_date = date('F j, Y', $deadline);

            $lic_url = \esc_url_raw(\get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::getSlug() . '-lic'));
            $message = "<div class='notice notice-error'><p>License issue detected for $plugin_name. Temporary access is extended for 7 days. <a href='$lic_url'>Enter a valid license key</a> by $deadline_date to avoid disruption. Use <strong>$coupon_code</strong> for 30% off: <a target=\"_blank\" href='$coupon_link'>Apply Coupon</a>.</p></div>";
            echo $message;
        }
    }

    public function sendStatusEmail()
    {
        $status = \get_option(Plugin::getShortSlug() . '_sys_status');

        $last_email_time = \get_option(Plugin::getShortSlug() . '_sys_last_email', 0);
        if ($status == 'invalid' && !$last_email_time)
        {
            $this->sendWarningEmail();
            \update_option(Plugin::getShortSlug() . '_sys_last_email', time());
        }
    }

    private function sendWarningEmail()
    {
        $plugin_name = Plugin::getName();
        $coupon_code = 'WOW30';
        $plugin_url = strtok(Plugin::pluginSiteUrl(), '?');
        $coupon_link =  $plugin_url . '/pricing?ref=' . $coupon_code;
        $admin_email = \get_option('admin_email');
        $domain = parse_url(\site_url(), PHP_URL_HOST);
        $deadline = \get_option(Plugin::getShortSlug() . '_sys_deadline', 0);
        if ($deadline < time())
            return;

        $deadline_date = date('F j, Y', $deadline);
        $subject =  __('Action Required:', 'external-importer') . ' ' . __('License Issue Detected for', 'external-importer') . ' ' . Plugin::getName();

        $message = "<!DOCTYPE html><html>
<p>Hello,<br></p>
<p>This message is to inform you about a critical issue regarding your license for $plugin_name used on your website, $domain.</p>
<h3>Temporary Access Extension</h3>
<p>To ensure there is no immediate disruption, temporary access to all premium features has been extended for an additional <strong>7 days</strong>. During this period, please resolve the license issue to avoid any interruptions.</p>
<h3>Deadlines</h3>
<p>You have until $deadline_date to rectify the license mismatch. After this date, access to premium features will be revoked, impacting the functionality and benefits enjoyed with $plugin_name.</p>
<h3>Issue Details</h3>
<p>The license issue has been detected for the following domain:</p>
<p>- $domain</p>
<h3>Steps to Resolve</h3>
<ol>
<li><strong>Verify Your License Key</strong>: Ensure you are using the correct license key. The license key can be found in your keywordrush.com dashboard.</li>
<li><strong>Activate Your License</strong>: Enter your license key in the plugin settings. Navigate to $plugin_name > License in the WordPress admin dashboard and enter the correct key.</li>
</ol>
<h3>Exclusive Offer: 30% Discount</h3>
<p>To assist in resolving this issue promptly, a one-time discount of 30% on new license orders is being offered. Use the coupon code <strong>$coupon_code</strong> at checkout. This offer is valid for the next 7 days, expiring on $deadline_date.</p>
<p>Apply the discount directly using the following link:<br>$coupon_link</p>
<h3>Contact Support</h3>
<p>If any issues are encountered or further assistance is needed, please do not hesitate to contact the support team at info@keywordrush.com.</p>
<p>Thank you for your prompt attention to this matter.</p>
</html>";

        \add_filter('wp_mail_content_type', array(__CLASS__, 'setMailContentType'));
        \wp_mail($admin_email, $subject, $message);
        \remove_filter('wp_mail_content_type', array(__CLASS__, 'setMailContentType'));
    }

    public static function setMailContentType()
    {
        return 'text/html';
    }
}
