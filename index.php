<?php
require './config.php';

libxml_use_internal_errors(true);
$httpClient = new \GuzzleHttp\Client();
$url = "https://books.toscrape.com/";
$response = $httpClient->get($url);
$htmlString = (string) $response->getBody();

$doc = new DOMDocument();
$doc->loadHTML($htmlString);
$xpath = new DOMXPath($doc);

$titles = $xpath->query('//ol[@class="row"]//li//article//h3/a');
$prices = $xpath->query('//ol[@class="row"]//li//article//div[@class="product_price"]//p[@class="price_color"]');
$instocks = $xpath->query('//ol[@class="row"]//li//article//div[@class="product_price"]//p[@class="instock availability"]');
$images  = $xpath->query('//ol[@class="row"]//li//article//div[@class="image_container"]/a/img/@src');

$bookList = [];

foreach ($titles as $key => $title) {
    $bookList[$key]['title'] = replaceSpecialChars($title->textContent);
    $bookList[$key]['price'] = (float) replaceSpecialChars($prices[$key]->textContent) / 100;
    $bookList[$key]['instock'] = replaceSpecialChars($instocks[$key]->textContent);
    $bookList[$key]['image']  = $url . cleanUrl($images[$key]->nodeValue);
    $bookList[$key]['href']  = $url . cleanUrl($title->getAttribute('href'));
}

function replaceSpecialChars($item) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $item);
}

function cleanUrl($url) {
    $url = preg_replace('/\s+/', '', $url);
    $url = str_replace(['\r', '\n'], '', $url);
    $url = stripslashes($url);
    return $url;
}

// print_r($bookList);
echo json_encode($bookList, JSON_PRETTY_PRINT);

libxml_clear_errors();
?>
