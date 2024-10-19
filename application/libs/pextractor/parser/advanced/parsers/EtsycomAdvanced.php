<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

use function ExternalImporter\prnx;

/**
 * EtsycomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2024 keywordrush.com
 */
class EtsycomAdvanced extends AdvancedParser
{

    public function parseLinks()
    {
        if ($urls = $this->parseLinksJson())
            return $urls;

        $path = array(
            ".//a[contains(@class, 'listing-link')]/@href",
        );

        $urls = $this->xpathArray($path);
        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
        }

        return $urls;
    }

    public function parseLinksJson()
    {
        $json = $this->xpathScalar(".//script[@type='application/ld+json']");

        if (!$items = json_decode($json, true))
            return array();

        if (!isset($items['itemListElement']))
            return array();

        $urls = array();
        foreach ($items['itemListElement'] as $item)
        {
            if (isset($item['url']))
                $urls[] = $item['url'];
        }

        return $urls;
    }

    public function parsePagination()
    {
        $path = array(
            ".//nav[@class='search-pagination']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//p[@class='wt-text-body-01 wt-break-word']",
        );

        return $this->xpathScalar($paths, true);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[@data-buy-box-region='price']//p[@class='wt-text-title-03 wt-mr-xs-1']/span[2]",
            ".//div[@data-buy-box-region='price']//p[@class='wt-text-title-03 wt-mr-xs-1']",
            ".//div[@class='wt-mb-xs-3']//p[@class='wt-text-title-03 wt-mr-xs-2']",
            ".//*[@class='text-largest strong override-listing-price']",
            ".//p[@class='wt-text-title-03 wt-mr-xs-1']/span",
            ".//div[@data-selector='price-only']//p[@class='wt-text-title-larger wt-mr-xs-1 wt-text-slime ']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//p[contains(@class, 'wt-text-gray')]//span[@class='wt-text-strikethrough']",
            ".//div[@class='wt-mb-xs-3']//p[contains(@class, 'wt-text-strikethrough')]",
            ".//meta[@property='product:price:amount']/@content",

        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        $paths = array(
            ".//div[contains(@class, 'image-carousel-container')]//*/@data-src",
            ".//div[contains(@class, 'image-carousel-container')]//*/@data-src-zoom-image",
            ".//div[contains(@data-selector, 'main-carousel')]//*/@data-zoom-src",
            ".//div[contains(@class, 'image-carousel-container')]//img/@src",
        );

        return $this->xpathArray($paths);
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//div[@id='same-listing-reviews-panel' or @id='reviews']//*[contains(@id, 'review-preview-toggle')]",
                'rating' => ".//div[@id='same-listing-reviews-panel' or @id='reviews']//span[@class='wt-display-inline-block wt-mr-xs-1']//span[last()]/@data-rating",
                'author' => ".//div[@id='same-listing-reviews-panel' or @id='reviews']//a[@class='wt-text-link wt-mr-xs-1']",
                'date' => ".//div[@id='same-listing-reviews-panel'or @id='reviews']//p[@class='wt-text-caption wt-text-gray']/text()",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        if ($this->parsePrice())
        {
            if (preg_match('/"locale_currency_code":"([A-Z]+?)"/', $this->html, $matches))
                return $matches[1];
        }
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name-value' => ".//p[@id='legacy-materials-product-details']",
                'separator' => ":",
            ),
            array(
                'name-value' => ".//div[@id='product_details_content_toggle']//div[@class='wt-ml-xs-1']",
                'separator' => ":",
            ),
        );
    }

    public function afterParseFix(Product $product)
    {
        if (strstr($product->title, 'item is unavailable'))
        {
            $product->availability = 'OutOfStock';
            $product->price = 0;
            $product->oldPrice = 0;
        }

        foreach ($product->reviews as $i => $r)
        {
            if ($r['rating'])
                $product->reviews[$i]['rating']++;
        }

        return $product;
    }
}
