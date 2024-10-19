<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;
use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * OverstockcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2024 keywordrush.com
 */
class OverstockcomAdvanced extends AdvancedParser
{

    private $_pagination = array();

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();

        // reset session cookies
        $httpOptions['cookies'] = array();
        $httpOptions['headers'] = array(
            'Accept' => '', //!!!
            'Accept-Language' => 'en-us,en;q=0.5',
            'Cache-Control' => 'no-cache',
        );
        $httpOptions['user-agent'] = 'ia_archiver';

        return $httpOptions;
    }

    public function parseLinks()
    {

        if (!preg_match('~"taxonomyId": (\d+)~', $this->html, $matches))
            return array();

        $request_url = 'https://api.overstock.com/vsearch/products/v1';
        $response = \wp_remote_post($request_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ),
            'body' => '{"client":{"id":"ostk","version":"1.0.0","deviceType":"DESKTOP"},"user":{"seed":"3622946904276041631","language":"en","country":"US","currency":"USD","zip":"","zipByIp":"","requestId":""},"query":{"productSearchQuery":{"taxonomies":["' . $matches[1] . '"],"attributes":{},"restrictions":{},"ranges":{},"searchParameters":{"fastshipping":null,"oos":null,"page":1,"sort":"bestselling","rating":null}},"origin":{"scheme":"https","hostType":"DomainName","host":"www.overstock.com"}},"requires":["banners","facets","meta","products","redirect","relatedSearches","selectedFacets","seoMetadata","sponsoredProducts","sponsoredShowcase","sortOptions","taxonomyFacets","notating","featuredProduct"],"conversationalSearch":{},"clientProfileOverrides":{"productCount":{"rows":60,"maxSponsoredProducts":0},"sponsoredShowcaseProductCount":{"rows":0,"maxSponsoredProducts":0}},"verboseLogging":false,"url":"' . $this->getUrl() . '"}',
            'method' => 'POST'
        ));

        if (\is_wp_error($response))
            return array();

        if (!$body = \wp_remote_retrieve_body($response))
            return array();

        $result = json_decode($body, true);

        if (!$result || !isset($result['products']))
            return array();

        $urls = array();
        foreach ($result['products'] as $r)
        {
            $urls[] =
                'https://www.overstock.com/products/' . $r['url'];
        }

        return $urls;
    }

    public function parsePagination()
    {
        return $this->_pagination;
    }

    public function parseDescription()
    {
        $paths = array(
            ".//div[@class='disclosure__content rte product-description']",
        );

        return $this->xpathScalar($paths, true);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        $paths = array(
            ".//div[@class='media relative image-blend']/a/@href",
        );

        return $this->xpathArray($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[contains(@class, 'price-comparison')]//span[@data-cy='product-was-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//ul[@class='product-spec']//div[@class='left-col']",
                'value' => ".//ul[@class='product-spec']//div[@class='right-col']",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'USD';
    }
}
