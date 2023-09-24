<?php
require __DIR__ .'/../../vendor/autoload.php';
require __DIR__ .'/func.php';
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

foreach($nav_list as $item_nav){
    $url_page = $url . $item_nav->attr('href');

    $file = get_html($url_page, $client);
    $document->loadHtml($file);

    $pages_count = get_pages_count($document);

    for($i = 1; $i <= $pages_count; $i++){
        echo "Pages {$i} of {$pages_count}\n";
        $file = get_html("{$url_page}?page={$i}\n", $client);
        $document->loadHtml($file);

        $products_data = array_merge($products_data, get_products($document, $client));
        file_put_contents('products.json', json_encode($products_data, JSON_UNESCAPED_UNICODE), FILE_APPEND);
    }
}

$pr_cnt = count($products_data);
file_put_contents('products.json', json_encode($products_data, JSON_UNESCAPED_UNICODE));
echo "\n==============================================\n";
echo "Completed! Items received: {$pr_cnt}";



