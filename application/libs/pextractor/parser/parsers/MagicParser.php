<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\libs\pextractor\parser\ListingProcessor;

use function ExternalImporter\prnx;

/**
 * MagicParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2024 keywordrush.com
 */
class MagicParser extends AbstractParser
{

    const FORMAT = ParserFormat::MAGIC_PARSER;

    public function parseLinks()
    {
        $links1 = $this->parseLinksMethod1();
        $links2 = $this->parseLinksMethod2();

        if (count($links1) > 100 && $links2 && count($links2) < 50)
            $links = $links2;
        elseif (count($links1) > $links2)
            $links = $links1;
        elseif ($links2)
            $links = $links2;
        else
            $links = $links1;

        if (!$links)
            $links = $this->parseLinksMethod3();

        $links = self::filterLinks($links);
        return $links;
    }

    public function parsePagination()
    {
        return $this->parsePaginationMethod1();
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@class='name']",
            ".//h1[@class='product-name']",
            ".//h1[@class='produt-name']",
            ".//h2[@class='product-name']",
            ".//h1[contains(@class, 'product-title')]",
            ".//div[contains(@class, 'ProductName')]/h1",
            ".//div[contains(@class, 'product-name')]/h1",
            ".//div[contains(@class, 'product-name')]/h2",
            ".//div[@class='name']/*[contains(@class, 'productName')]",
            ".//h1[contains(@class, 'productname')]",
            ".//h1[contains(@class, 'cardTitle')]",
            ".//h2[contains(@class, 'cardTitle')]",
            ".//h1[@class='page-title']",
            ".//h2[@class='page-title']",
            ".//*[@class='product-name']",
            ".//div[@id='product-detail']//h1",
            ".//h1[@id='pagetitle']",
            //--
            ".//h1[contains(@class, 'title') or contains(@class, 'product-name') or contains(@class, 'product_title') or contains(@class, 'page-title') or contains(@class, 'pdp-title') or contains(@class, 'entry-title') or contains(@class, 'product__name') or contains(@class, 'main-title') or contains(@class, 'headline')]/text()",
            ".//h1[@itemprop='name' or @id='title' or @data-testid='heading-product-title' or @data-varianthashname]/text()",
            ".//h1[contains(@class, 'product_title') or contains(@class, 'product-title') or contains(@class, 'page-title') or contains(@class, 'title') or contains(@class, 'entry-title') or contains(@class, 'headline')]/text()",
            ".//div[contains(@class, 'product-name') or contains(@class, 'product_title') or contains(@class, 'page-title') or contains(@class, 'title')]//h1/text()",
            ".//div[contains(@class, 'product-name') or contains(@class, 'product_title') or contains(@class, 'page-title') or contains(@class, 'title')]//span/text()",
            ".//meta[@property='og:title' or @name='title' or @name='twitter:title']/@content",
            ".//span[contains(@class, 'product-name') or contains(@class, 'product-title') or contains(@class, 'product_title')]/text()",
            ".//h2[contains(@class, 'product-name') or contains(@class, 'product_title') or contains(@class, 'title')]/text()",
            ".//div[@class='product-name']/h1/text()",
            ".//div[@class='product-title']/h1/text()",
            ".//div[@id='productTitle']/p/text()",
            ".//h1[@class='product_title entry-title']/text()",
            ".//div[contains(@class, 'product-name') or contains(@class, 'product-title') or contains(@class, 'product_title')]//h1/text()",
            ".//h1[contains(@class, 'product_title') or contains(@class, 'product-title') or contains(@class, 'page-title') or contains(@class, 'title') or contains(@class, 'headline')]/text()",
            ".//h1[@class='product_title entry-title']/text()",
            ".//div[@id='product-header']//h1/text()",
            ".//meta[@property='og:title']/@content",
            ".//span[@itemprop='name']/text()",
            ".//h1",
            ".//title",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//div[contains(@id, 'description') or contains(@id, 'tab-description') or contains(@id, 'product-details') or contains(@id, 'long-description') or contains(@id, 'content') or contains(@id, 'detail') or contains(@id, 'desc') or contains(@id, 'product-info') or contains(@id, 'summary') or contains(@id, 'overview') or contains(@id, 'productDescription') or contains(@id, 'tab1') or contains(@id, 'tab6') or contains(@id, 'tab7') or contains(@id, 'description-content') or contains(@id, 'panel1') or contains(@id, 'collapse-description')]",
            ".//div[contains(@class, 'description') or contains(@class, 'product-description') or contains(@class, 'product-details') or contains(@class, 'content') or contains(@class, 'details') or contains(@class, 'product-info') or contains(@class, 'product-summary') or contains(@class, 'woocommerce-product-details__short-description') or contains(@class, 'rte') or contains(@class, 'item-description') or contains(@class, 'info') or contains(@class, 'content-wrap') or contains(@class, 'section-content') or contains(@class, 'woocommerce-tabs') or contains(@class, 'accordion-content') or contains(@class, 'product-page') or contains(@class, 'box')]",
            ".//div[@itemprop='description']",
            ".//section[contains(@class, 'description') or contains(@class, 'product-description') or contains(@class, 'product-details') or contains(@class, 'content') or contains(@class, 'details') or contains(@class, 'product-info') or contains(@class, 'product-summary') or contains(@class, 'woocommerce-product-details__short-description') or contains(@class, 'rte') or contains(@class, 'item-description') or contains(@class, 'info') or contains(@class, 'content-wrap') or contains(@class, 'section-content') or contains(@class, 'woocommerce-tabs') or contains(@class, 'accordion-content') or contains(@class, 'product-page') or contains(@class, 'box')]",
            ".//ul[contains(@class, 'description') or contains(@class, 'product-description') or contains(@class, 'product-details') or contains(@class, 'content') or contains(@class, 'details') or contains(@class, 'product-info') or contains(@class, 'product-summary') or contains(@class, 'woocommerce-product-details__short-description') or contains(@class, 'rte') or contains(@class, 'item-description') or contains(@class, 'info') or contains(@class, 'content-wrap') or contains(@class, 'section-content') or contains(@class, 'woocommerce-tabs') or contains(@class, 'accordion-content') or contains(@class, 'product-page') or contains(@class, 'box')]",
            ".//p[contains(@class, 'description') or contains(@class, 'product-description') or contains(@class, 'product-details') or contains(@class, 'content') or contains(@class, 'details') or contains(@class, 'product-info') or contains(@class, 'product-summary') or contains(@class, 'woocommerce-product-details__short-description') or contains(@class, 'rte') or contains(@class, 'item-description') or contains(@class, 'info') or contains(@class, 'content-wrap') or contains(@class, 'section-content') or contains(@class, 'woocommerce-tabs') or contains(@class, 'accordion-content') or contains(@class, 'product-page') or contains(@class, 'box')]",
        );

        return $this->xpathScalar($paths, true);
    }

    public function parsePrice()
    {
        $paths = array(

            ".//div[contains(@class, 'price-box')]//span[@class='price']",
            ".//div[contains(@class, 'product_pric')]//span[@class='price']",
            ".//strong[@class='skuBestPrice']",
            ".//div[@class='product-price']",
            ".//*[@data-test='product-price']",
            ".//*[@id='our_price_display']",
            ".//*[@class='ProductPriceValue']",
            ".//*[contains(@class, 'product-intro')]/*[@class='original']",
            ".//*[contains(@class, 'product-details')]//*[@class='price']",
            ".//*[contains(@class, 'product-details')]//*[contains(@class, 'price-item')]",
            ".//*[contains(@class, 'price-box')]//*[contains(@class, 'regular-price')]",
            ".//*[contains(@class, 'product-info')]//*[contains(@class, 'regular-price')]",
            ".//*[contains(@class, 'product-info')]//*[contains(@class, 'price')]",
            ".//*[@class='Brief-minPrice']",
            ".//*[contains(@class, 'woocommerce-Price-amount')]//bdi",
            ".//*[@class='woocommerce-Price-amount amount']",
            ".//*[@class='product-card-price__current']",
            ".//*[contains(@class, 'product-price')]",

            //--
            ".//div[@class='product-price']//span[contains(@id, 'product-price-')]",
            ".//span[@id='mm-saleDscPrc' or @id='priceblock_ourprice' or @id='priceblock_saleprice']",
            ".//h2[contains(@class, 'price') or contains(@id, 'price')]/text()",
            ".//li[contains(@class, 'price') or contains(@id, 'price')]//h2/text()",
            ".//p[contains(@class, 'price') or contains(@id, 'price')]//ins//bdi/text()",
            ".//span[@itemprop='price' or contains(@class, 'price') or contains(@id, 'price')]/@content",
            ".//div[contains(@class, 'price') or contains(@id, 'price') or @data-offer-price-new]",
            ".//bdi[contains(@class, 'price') or contains(@id, 'price')]",
            ".//*[contains(@class, 'price') or contains(@id, 'price') or @itemprop='price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='price-box']//*[@class='old-price']//span[@class='price']",
            ".//span[@class='vi-originalPrice' or @id='mm-saleOrgPrc' or @id='orgPrc']",
            ".//span[contains(@class, 'price-standard') or contains(@class, 'price-old') or contains(@class, 'price-strikethrough')]",
            ".//div[contains(@class, 'old-price') or contains(@class, 'price-old') or contains(@class, 'strike')]",
            ".//div[@class='product-price']//span[@class='price-standard']",
            ".//div[@class='price']//span[@class='price-old']",
            ".//del[contains(@class, 'strike') or contains(@class, 'price')]",
            ".//s[contains(@class, 'strike') or contains(@class, 'price')]",
            ".//*[contains(@class, 'old-price') or contains(@class, 'price') and contains(@class, 'old') or contains(@class, 'strike')]",

        );
        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//*[contains(@class, 'product-image')]//img/@src",
            ".//div[contains(@id, 'gallery')]//img/@src",
            ".//div[contains(@id, 'gallery')]//img/@data-src",
            ".//div[contains(@id, 'productimages')]//img/@src",
            ".//img[@id='image']/@src",
            ".//img[@class='mainimage']/@src",
            ".//img[@id='image-main']/@src",
            ".//*[contains(@class, 'product-image')]//img/@src",
            ".//*[contains(@id, 'slideproduct')]//img/@src",
            ".//*[contains(@class, 'product-main-image')]//img/@src",
            ".//*[contains(@class, 'main_photo')]//img/@src",
            //--
            ".//img[contains(@class, 'pdp__mainImg') or contains(@class, 'js_pdpMainImg')]/@src",
            ".//*[@id='icImg']/@src",
            ".//div[contains(@class, 'ux-image-carousel')]//img/@src",
            ".//meta[contains(@property, 'og:image')]/@content",
            ".//img[contains(@class, 'ProductInfo_Fancybox_IMG')]/@src",
            ".//div[contains(@class, 'gallery-placeholder')]//img/@src",
            ".//span[contains(@id, 'magiczoom')]//a/@href",
            ".//div[contains(@class, 'imgPrinc')]//img/@src",
            ".//div[contains(@class, 'image')]//a[contains(@class, 'fresco')]/@href",
            ".//img[contains(@id, 'imgStock')]/@src",
            ".//*[@class='woocommerce-product-gallery__wrapper']//a/@href",
            ".//img[contains(@class, 'img-fluid img-full')]/@src",
            ".//div[contains(@class, 'image-zoom')]//img/@src",
            ".//img[contains(@class, 'img-mag__asset js-img-mag__asset')]/@src",
            ".//div[contains(@class, 'gallery-placeholder__image')]//img/@src",
            ".//div[contains(@class, 'product-image')]//*[@main-image-url]/@main-image-url",
            ".//a[contains(@class, 'MagicZoom')]/@href",
            ".//img[contains(@class, 'primary-image')]/@src",
            ".//div[contains(@id, 'produto-imagem')]//a/@href"
        );
        return $this->xpathScalar($paths);
    }

    public function parseLinksMethod1()
    {
        $path = array(
            ".//h2[@class='product-name']/a/@href",
            ".//h3[@class='product-name']/a/@href",
            ".//a[@class='product-image']/@href",
            ".//*[@class='product-image']/a/@href",
            ".//a[@class='product-item-link']/@href",
            ".//*[@class='product_name']/a/@href",
            ".//a[@class='product-name']/@href",
            ".//*[@class='product-name']/a/@href",
            ".//*[@class='product-info']//a/@href",
            ".//*[@class='product_name']/a/@href",
            ".//*[@class='products-grid']//a/@href",
            ".//a[contains(@class, 'woocommerce-LoopProduct-link')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseImages()
    {

        $xpath = array(
            ".//div[contains(@class, 'smallgallery')]//a[contains(@id, '')]/@href",
            ".//div[contains(@class, 'pic-vert-msk')]//img/@src",
            ".//div[contains(@id, 'vi_main_img_fs_slider')]//img/@src",
            ".//div[contains(@class, 'vim ux-thumb-image-carousel')]//img/@src",
            ".//img[contains(@data-testid, 'media-gallery-image')]/@src",
            ".//div[contains(@class, 'WraImg')]//img/@src",
            ".//nav[contains(@class, 'imgBox-thumblist')]//a/@href",
            ".//span[contains(@class, 'thumbs')]//a[contains(@class, 'Thumbnail_Productinfo_FancyBox')]/@href",
            ".//div[contains(@id, 'imgproducto')]//img/@src",
            ".//*[contains(@class, 'woocommerce-product-gallery__wrapper')]//img[contains(@data-large_image, '')]/@src",
            ".//div[contains(@class, 'thumbnails')]//a/@href",
            ".//div[contains(@id, 'js-goodsGalleryThumb')]//li/@data-big-img",
            ".//ul[contains(@id, 'lightSlider')]//img/@src",
            ".//div[contains(@class, 'item_slider')]//*/@data-img",
            ".//div[contains(@id, 'foto-scarico')]//img/@src",
            ".//*[contains(@class, 'woocommerce-product-gallery')]//a/@href",
            ".//div[contains(@class, 'thumbWrap')]//img/@src",
            ".//ul[contains(@id, 'imageGallery')]//img/@src",
            ".//div[contains(@id, 'product-thumbnails')]//img/@src",
            ".//*[contains(@class, 'product-gallery-wrapper')]//a/@href"
        );

        return $this->xpathArray($xpath);
    }

    public function parseLinksMethod2()
    {
        $img_links = $this->xpathArray(".//img/ancestor::a/@href");
        $img_links = ListingProcessor::prepareLinks($img_links, $this->base_uri);

        $txt_links = $this->xpathArray(".//a[descendant-or-self::*[string-length(normalize-space(text()))>10 and contains(normalize-space(text()), ' ')]/text()]/@href");
        $txt_links = ListingProcessor::prepareLinks($txt_links, $this->base_uri);

        return array_values(array_intersect($img_links, $txt_links));
    }

    public function parseLinksMethod3()
    {
        $path = array(
            ".//a[contains(@class, 'woocommerce-loop-product__link')]/@href",
            ".//a[contains(@class, 'productLink')]/@href",
            ".//a[contains(@class, 'product-link')]/@href",
            ".//*[starts-with(@class, 'product-')]/a/@href",
            ".//a[starts-with(@class, 'product-')]/@href",
            ".//a[starts-with(@class, 'product ')]/@href",
            ".//a[starts-with(@class, 'product')]/@href",
            ".//a[contains(@class, '-product')]/@href",
            ".//*[@itemprop='name']/a/@href",
            ".//*[contains(@class, 'product-name')]//a/@href",
            ".//*[contains(@class, 'list-product')]//a/@href",
            ".//*[contains(@class, '-product')]/a/@href",
            ".//*[starts-with(@class, 'product')]/a/@href",
            //--
            ".//a[
  contains(@href, '/product') or
  contains(@href, '/item') or
  contains(@href, '/catalog') or
  contains(@href, '/detail') or
  contains(@href, '/shop') or
  contains(@href, '/products')
][
  contains(@class, 'product') or
  contains(@class, 'item') or
  contains(@class, 'product-link') or
  contains(@class, 'product-card') or
  contains(@class, 'product-item') or
  contains(@class, 'grid-item') or
  contains(@class, 'product-thumb')
]",
        );

        $txt_links = $this->xpathArray($path);
        $txt_links = ListingProcessor::prepareLinks($txt_links, $this->base_uri);
        if (!$txt_links || count($txt_links) < 3)
            return array();

        $img_links = $this->xpathArray(".//img/ancestor::a/@href");
        $img_links = ListingProcessor::prepareLinks($img_links, $this->base_uri);

        if ($intersect = array_values(array_intersect($img_links, $txt_links)))
            return $intersect;
        else
            return $txt_links;
    }

    public static function filterLinks(array $links)
    {
        $slash_count = array();
        foreach ($links as $link)
        {
            $count = substr_count($link, '/');
            if (!isset($slash_count[$count]))
                $slash_count[$count] = 0;
            $slash_count[$count]++;
        }
        arsort($slash_count);
        $typical_slash_count = key($slash_count);

        foreach ($links as $i => $link)
        {
            if (substr_count($link, '/') != $typical_slash_count)
                unset($links[$i]);
        }

        return array_values($links);
    }

    public function parsePaginationMethod1()
    {
        $path = array(
            ".//link[@rel='next']/@href",
            ".//ul[contains(@class, 'pagination')]//a/@href",
            ".//ul[contains(@class, 'paging')]//a/@href",
            ".//ul[@class='pages']//a/@href",
            ".//ul[@class='page-numbers']//a/@href",
            ".//ul[@class='pages-items']//a/@href",
            ".//*[@class='paging-list']//a/@href",
            ".//*[contains(@class, 'pagination')]//a/@href",
            ".//*[contains(@id, 'pagination')]//a/@href",
            ".//nav[@class='woocommerce-pagination']//li//a/@href",
            //--
            ".//a[
  contains(@href, 'page') or
  contains(@href, 'pagination') or
  contains(@href, 'p=') or
  contains(@href, 'pg=') or
  contains(@href, 'start=') or
  contains(@href, 'offset=') or
  contains(@href, 'limit=') or
  contains(@href, 'index=')
][
  ancestor::div[contains(@class, 'pagination') or contains(@id, 'pagination')] or
  ancestor::ul[contains(@class, 'pagination') or contains(@class, 'page-numbers') or contains(@class, 'pages-items')] or
  ancestor::ol[contains(@class, 'pagination')] or
  ancestor::nav[contains(@class, 'pagination') or contains(@class, 'paginator')] or
  ancestor::span[contains(@class, 'pagination')]
]
|
//link[@rel='next']/@href
|
//meta[@name='pageID']/@content
"
        );

        return $this->xpathArray($path);
    }

    public function parseCurrencyCode()
    {
        $paths = array(
            ".//meta[@property='product:price:currency']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//nav[contains(@class, 'breadcrumb')]//a |
//ul[contains(@class, 'breadcrumb')]//a |
//ol[contains(@class, 'breadcrumb')]//a |
//div[contains(@class, 'breadcrumb')]//a |
//div[contains(@class, 'breadcrumbs')]//a |
//div[@id='breadcrumbs']//a |
//ul[@class='breadcrumbs']//a |
//ol[@class='breadcrumb']//a",
        );

        return $this->xpathArray($paths);
    }
}
