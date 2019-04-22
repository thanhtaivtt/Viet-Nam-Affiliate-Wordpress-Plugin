<?php
/**
 * Created by PhpStorm.
 * User: thanhtai
 * Date: 16/04/2019
 * Time: 15:41
 */
defined('ABSPATH') or die('No script kiddies please!');

include_once(plugin_dir_path(__FILE__) . '/config/constant.php');

class TDC
{
    /**
     * contain plugin config option
     *
     * @var array
     */
    private static $option = [];

    /**
     * Contain list domain match for convert to aff Link
     *
     * @var array
     */
    private static $domains = [];

    /**
     *
     * TDC constructor.
     *
     */
    public function __construct()
    {
        static::$option = get_option(TDC_OPTION_NAME);
    }


    /**
     * Check Url Match to redirect
     *
     * @return bool|void
     */
    public static function checkRedirect()
    {

        $goto = '';

        // Check option is null
        if (count(static::$option) == 0) {
            static::$option = get_option(TDC_OPTION_NAME);
        }

        $pathMatch = static::$option['redirectPath'];

        $position = strpos($_SERVER['REQUEST_URI'], '/' . $pathMatch . '/');

        if ($position === false) {
            return true;
        } else {
            $temp = substr($_SERVER['REQUEST_URI'], $position + strlen($pathMatch) + 2);
            $temp = explode('/', $temp);

            $goto = $temp[0];
            $postID = 0;
            if (count($temp) == 2) {
                $goto = $temp[1];
                $postID = $temp[0];
            }

            $url = base64_decode($goto);
            $url = urldecode($url);

            $affLink = str_replace('{{AFF_ID}}', self::$option['affiliateID'], self::$option['deepLinkURL']);

            $affLink = str_replace('{{URL}}', $url, $affLink);

            static::addTracking($url, $postID, @$_SERVER['REMOTE_ADDR']);

            header('location:' . $affLink);
        }
    }

    /**
     * add tracking if enabled
     *
     * @param $link
     * @param $postID
     * @param $ip
     * @return bool|false|int
     */
    private function addTracking($link, $postID, $ip)
    {
        if (self::$option['tracking'] != true) {
            return false;
        }

        global $wpdb;

        return $wpdb->insert($wpdb->prefix . 'tdc_link', [
            'link' => $link,
            'post_id' => $postID,
            'ip' => $ip,
            'date' => date('Ymd'),
        ]);
    }

    /**
     * @param $content
     * @return string|string[]|null
     */
    public static function changeContent($content)
    {
        return self::filter($content);
    }

    private function matchDomain($link)
    {
        if (!filter_var($link, FILTER_VALIDATE_URL) || !in_array(parse_url($link, PHP_URL_HOST), self::$option['domain'])) {
            return false;
        }

        return true;
    }

    /**
     * @param $content
     * @return string|string[]|null
     */
    private function filter($content)
    {
        $pattern = '/<a (.*?)href=[\"\'](.*?)[\"\'](.*?)>(.*?)<\/a>/si';

        $postID = get_post()->ID;

        return preg_replace_callback(
            $pattern,
            function ($matches) use ($postID) {
                $tempURL = @$matches[2];

                if (static::matchDomain($tempURL)) {
                    $tempURL = get_site_url(null, self::$option['redirectPath'] . '/' . $postID . '/' . base64_encode($tempURL));
                }

                return '<a ' . (self::$option['rel'] ? 'rel=nofollow' : '') . ' ' . (self::$option['newtab'] ? 'target="_blank"' : '') . ' ' . @$matches[1] . ' href="' . $tempURL . '" ' . @$matches[3] . '>' . @$matches[4] . '</a>';
            },
            $content
        );
    }
}