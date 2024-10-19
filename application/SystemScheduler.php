<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\LicConfig;
use ExternalImporter\application\components\Scheduler;

/**
 * SystemScheduler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2024 keywordrush.com
 */
class SystemScheduler extends Scheduler
{
    const CRON_TAG = 'exi_system_cron';

    public static function getCronTag()
    {
        return self::CRON_TAG;
    }

    public static function run()
    {
        self::checkStatus();
    }

    public static function checkStatus()
    {
        if (!$key = LicConfig::getInstance()->option('license_key'))
            return;

        $response = \wp_remote_post('https://www.keywordrush.com/api/v1', array('body' => array('cmd' => 'status', 'd' => parse_url(\site_url(), PHP_URL_HOST), 'p' => Plugin::product_id, 'v' => Plugin::version(), 'key' => $key)));
        if (\is_wp_error($response))
            return;

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code != 200)
            return;

        $response_body = \wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($data['status'] == 'invalid')
        {
            \update_option(Plugin::getShortSlug() . '_sys_status', 'invalid');
            if (!\get_option(Plugin::getShortSlug() . '_sys_deadline', 0))
                \update_option(Plugin::getShortSlug() . '_sys_deadline', time() + 7 * 86400);
        }
        else
            \update_option(Plugin::getShortSlug() . '_sys_status', 'valid');
    }
}
