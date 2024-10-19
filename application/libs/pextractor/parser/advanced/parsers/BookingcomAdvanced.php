<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

use function ExternalImporter\prn;
use function ExternalImporter\prnx;

/**
 * BookingcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2024 keywordrush.com
 */
class BookingcomAdvanced extends AdvancedParser
{

    public function parseLinks()
    {
        $path = array(
            ".//a[@data-testid='title-link']/@href",
        );

        $urls = $this->xpathArray($path);
        foreach ($urls as $i => $url)
        {
            $url = strtok($url, '?');
            $url =  preg_replace('/\.[a-z]{2}\.html/', '.html', $url);
            $urls[$i] = $url;
        }

        return $urls;
    }

    public function parseTitle()
    {
        $title = $this->xpathScalar(".//title");
        $parts = explode(' – ', $title);
        $title = reset($parts);

        return $title;
    }

    public function parseDescription()
    {
        $path = array(
            ".//*[@data-testid='property-description']",
        );

        return $this->xpathScalar($path, true);
    }

    public function parsePrice()
    {
        if (!preg_match('/"priceRange" : "(.+?)"/', $this->html, $matches))
            return;

        $price = $matches[1];

        if (preg_match('/[A-Z]{3}.+?([0-9\.\s\'\,]+)/', $price, $matches))
            $price = trim($matches[1]);
        elseif (preg_match('/\d[0-9\.,\s]+/', $price, $matches))
            $price = trim($matches[0]);

        $price = str_replace(' ', '', $price);

        return $price;
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        $images = array();
        $results1 = $this->xpathArray(".//div[contains(@class, 'bh-photo-grid')]//a/@data-thumb-url");
        $results2 = $this->xpathArray(".//div[contains(@class, 'bh-photo-grid-thumbs-wrapper')]//a/img/@src");

        $results = array_merge($results1, $results2);
        foreach ($results as $img)
        {
            $img = preg_replace('~/max\d+/~', '/max1024x768/', $img);
            $images[] = $img;
        }

        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@id='hotelPoliciesInc']//div[not(contains(@class, 'children-policy'))]//p[@class='policy_name']",
                'value' => ".//div[@id='hotelPoliciesInc']/div[starts-with(@class, 'description')]/p[2]",
            ),
        );
    }

    public function parseFeatures()
    {
        $features = parent::parseFeatures();

        if ($location = $this->xpathScalar(".//meta[@name='twitter:title']/@content"))
        {
            $features[] = array(
                'name' => 'Location',
                'value' => $location,
            );
        }

        return $features;
    }

    public function parseReviews()
    {
        $positive_texts = $negative_texts = array();

        if (preg_match_all('/"positiveText":"(.*?)"/', $this->html, $matches))
            $positive_texts = $matches[1];

        if (preg_match_all('/"negativeText":"(.*?)"/', $this->html, $matches))
            $negative_texts = $matches[1];

        if (!$positive_texts && !$negative_texts)
            return array();

        if (preg_match_all('/"averageScore":(\d+)/', $this->html, $matches))
            $ratings = $matches[1];

        if (preg_match_all('/"guestName":"(.+?)"/', $this->html, $matches))
            $authors = $matches[1];

        $results = array();
        for ($i = 0; $i < count($positive_texts); $i++)
        {
            $review = array();

            if ($positive_texts[$i])
                $review['review'] = "<p>[+] " . $positive_texts[$i] . "</p>";
            else
                $review['review'] = '';

            if (isset($negative_texts[$i]) && $negative_texts[$i])
                $review['review'] .= "<p>[-] " . $negative_texts[$i] . "</p>";

            $review['review'] = json_decode('"' . $review['review'] . '"');

            if (isset($ratings[$i]))
            {
                $rating = round(TextHelper::convertRatingScale($ratings[$i], 1, 10, 1, 5), 2);
                $review['rating'] = ExtractorHelper::ratingPrepare($rating);
            }

            if (isset($authors[$i]))
                $review['author'] = $authors[$i];

            $results[] = $review;
        }

        return $results;
    }

    public function parseCurrencyCode()
    {
        $currency = $this->xpathScalar(".//input[@name='selected_currency']/@value");

        if (!$currency)
        {
            if (preg_match("/b_selected_currency: '(\w+)'/ims", $this->html, $matches))
                $currency = $matches[1];
        }

        if (!$currency)
            $currency = 'USD';

        return $currency;
    }

    public function afterParseFix(Product $product)
    {

        foreach ($product->features as $i => $f)
        {
            if (strlen($f['value']) > 90)
                unset($product->features[$i]);
            elseif ($f['name'] == 'Accepted payment methods')
                unset($product->features[$i]);
            elseif ($f['name'] == 'Cancellation prepayment')
                unset($product->features[$i]);
            elseif (strstr($f['name'], 'Cards accepted'))
                unset($product->features[$i]);
        }

        return $product;
    }
}
