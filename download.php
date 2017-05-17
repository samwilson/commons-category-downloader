<?php

require_once 'vendor/autoload.php';

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\Api\Service\CategoryTraverser;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Title;

$api = MediawikiApi::newFromApiEndpoint('https://commons.wikimedia.org/w/api.php');

$factory = new MediawikiFactory($api);
$cat = $factory->newCategoryTraverser();
$categoryNamespaceId = 14;
$fspsCat = new Title('Category:Fremantle_Society_Photographic_Survey', $categoryNamespaceId);
$titlefile = 'pagenames.txt';
if (file_exists($titlefile)) {
    unlink($titlefile);
}

$cat->addCallback(CategoryTraverser::CALLBACK_PAGE, function(Page $member, Page $rootCat) use ($titlefile, $api) {
    
    $title = $member->getPageIdentifier()->getTitle()->getText();
    file_put_contents($titlefile, $title."\n", FILE_APPEND);
    
    if (substr($title, 0, 5) != 'File:') {
        echo "Storing title: $title\n";
        // We've recorded the page title; nothing more to do now.
        return;
    }
    echo "Downloading $title ... ";

    // If image.
    $pageInfo = $api->getRequest(new SimpleRequest('query', [
        'prop' => 'imageinfo',
        'iiprop' => 'url|sha1',
        'titles' => $title,
    ]));
    
    if (!isset($pageInfo['query']['pages'])) {
        echo "Unable to get $title\n";
        exit();
    }
    $page = array_shift($pageInfo['query']['pages']);

    $url = $page['imageinfo'][0]['url'];
    $sha1 = $page['imageinfo'][0]['sha1'];

    $pageName = substr($title, 5);

    $localFile = __DIR__.'/files/'.$pageName;
    if (file_exists($localFile) && $sha1 == sha1_file($localFile)) {
        echo "already here\n";
        return;
    }
    file_put_contents($localFile, file_get_contents($url));
    echo "OK\n";
});
$cat->descend(new Page(new PageIdentifier($fspsCat)));
