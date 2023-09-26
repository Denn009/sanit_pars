<?php

function get_html($url, \GuzzleHttp\Client $client)
{
    $resp = $client->get($url);
    return $resp->getBody()->getContents();
}

function get_nav(\DiDom\Document $document){
    return $document->find('.cnt-link');
}

function get_pages_count(\DiDom\Document $document)
{
    $pagination = $document->find('.page-link');
    $max_page = [];
    foreach ($pagination as $item){
        if (is_numeric($item->text())){
            $max_page[] = $item->text();
        }
    }
    return max($max_page);
}

function get_products(\DiDom\Document $document, \GuzzleHttp\Client $client)
{
    static $product_cnt = 1;
    $products_data = [];
    $products = $document->find('.pli-title a');
    foreach($products as $prod){
        $url = 'https://sanit.by' . $prod->attr('href');
        echo "product {$product_cnt} {$url}\n";
        if($product_cnt > 3){
            break;
        }
        $products_data[$product_cnt] = get_prod($document, $client, $url);
        $product_cnt++;
    }
    return $products_data;
}

function get_prod(\DiDom\Document $document, \GuzzleHttp\Client $client, $url){
    $file = get_html($url, $client);
    $document->loadHtml($file);
    $product['title'] = $document->first('h1.page-title')->text();
    $product['price'] = $document->first('#product-price')->text();

    if ($document->has('#output-attributes')) {
        $ch_k = $document->find('#output-attributes tr');
        $ch = [];

        foreach($ch_k as $item){
            $el = $item->find('td');

            if (count($el) === 2){
                if(trim($el[0]->text()) && trim($el[1]->text())){
                    $ch[trim($el[0]->text())] = trim($el[1]->text());
                }
            }
        }

        $product['ch'] = $ch;
    }

    return $product;
}