<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/func.php';

use DiDom\Document;

set_time_limit(0);
ini_set('memory_limit', -1);

$client = new \GuzzleHttp\Client();
$document = new \DiDom\Document();
$url = "https://sanit.by";

echo "Start parsing.....\n";

$file = get_html($url, $client);
$document->loadHtml($file);

$nav_list = get_nav($document);
$products_data = [];

foreach ($nav_list as $item_nav) {
    $url_page = $url . $item_nav->attr('href');

    $file = get_html($url_page, $client);
    $document->loadHtml($file);

    $pages_count = get_pages_count($document);

    for ($i = 1; $i <= $pages_count; $i++) {
        echo "Pages {$i} of {$pages_count}\n";
        $file = get_html("{$url_page}?page={$i}\n", $client);
        $document->loadHtml($file);

        $products_data = array_merge($products_data, get_products($document, $client));
//        file_put_contents('products.json', json_encode($products_data, JSON_UNESCAPED_UNICODE), FILE_APPEND);
        $dom = new DOMDocument('1.0', 'UTF-8');

// Создаем корневой элемент
        $rootElement = $dom->createElement('products');
        $dom->appendChild($rootElement);

// Проходимся по каждому элементу массива
        foreach ($products_data as $product) {
            $productElement = $dom->createElement('product');

            $titleElement = $dom->createElement('title', $product["title"]);
            $productElement->appendChild($titleElement);

            $priceElement = $dom->createElement('price', $product["price"]);
            $productElement->appendChild($priceElement);

            $characteristicsElement = $dom->createElement('characteristics');
            foreach ($product["ch"] as $key => $value) {
                $chElement = $dom->createElement('characteristic', $value);
                $chElement->setAttribute('name', $key);
                $characteristicsElement->appendChild($chElement);
            }
            $productElement->appendChild($characteristicsElement);

            $rootElement->appendChild($productElement);
        }

// Форматируем и сохраняем
        $dom->formatOutput = true;
        $dom->save('products.xml');

        echo "XML created successfully!";
    }
}

$pr_cnt = count($products_data);
file_put_contents('products.json', json_encode($products_data, JSON_UNESCAPED_UNICODE));
echo "\n==============================================\n";
echo "Completed! Items received: {$pr_cnt}";



