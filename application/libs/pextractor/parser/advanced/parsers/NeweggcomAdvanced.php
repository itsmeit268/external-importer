<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

use ExternalImporter\application\libs\pextractor\parser\Product;

defined('\ABSPATH') || exit;

/**
 * NeweggcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2024 keywordrush.com
 */
class NeweggcomAdvanced extends AdvancedParser
{

    public function parseLinks()
    {
        $path = array(
            ".//*[@class='items-view is-grid']//a[@class='item-title']/@href",
            ".//div[@class='item-info']/a/@href",
            ".//a[@class='item-title']/@href",
            ".//a[contains(@href, '/p/') and contains(@href, 'Item=')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='list-tool-pagination']//div[@class='btn-group-cell']//button",
        );

        $pages = $this->xpathArray($path);

        $urls = array();
        foreach ($pages as $page)
        {
            if ($page <= 1)
                continue;

            $url = preg_replace('/\/Page-\d+/', '', $this->getUrl());
            $urls[] = add_query_arg('page', $page, $url);
        }
        return $urls;
    }

    public function parseDescription()
    {
        $path = array(
            ".//div[@class='grpArticle']//div[@class='grpBullet']",
            ".//div[@class='itemDesc']",
            ".//div[@id='overview-content']",
            // ".//div[@id='product-overview']",  //iframe
        );

        return $this->xpathScalar($path, true);
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//ul[@class='price']//span[@class='price-was-data']");
    }

    public function parsePrice()
    {
        $xpath = array(
            ".//div[@class='product-price']//li[@class='price-current']",
            ".//div[@class='price-new-right']//strong",
        );
        return $this->xpathScalar($xpath);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        $xpath = array(
            ".//img[@class='product-view-img-original' and contains(@src, '/ProductImage/')]/@src",
            ".//img[@class='product-view-img-original' and contains(@src, '/productimage/')]/@src",
        );
        return $this->xpathArray($xpath);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@id='Specs']//dt",
                'value' => ".//div[@id='Specs']//dd",
            ),
            array(
                'name' => ".//div[@id='product-details']//div[@class='tab-pane']//th",
                'value' => ".//div[@id='product-details']//div[@class='tab-pane']//td",
            ),
        );
    }

    public function afterParseFix(Product $product)
    {
        if (!$this->parsePrice())
            $product->price = 0;

        return $product;
    }

    public function parseAvailability()
    {
        $xpath = array(
            ".//div[@class='flags-body has-icon-left fa-exclamation-triangle']/span",
            ".//div[@class='product-inventory']/strong",
        );

        $stock = $this->xpathScalar($xpath);
        $stock = trim($stock);

        if ($stock == 'OUT OF STOCK')
            return 'http://schema.org/OutOfStock';
    }

    public function isInStock()
    {
    }
}
