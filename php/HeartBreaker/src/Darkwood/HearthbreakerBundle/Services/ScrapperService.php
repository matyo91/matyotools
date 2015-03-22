<?php

namespace Darkwood\HearthbreakerBundle\Services;

use Doctrine\Common\Cache\Cache;
use Goutte\Client;
use GuzzleHttp\Subscriber\Cache\CacheStorage;
use GuzzleHttp\Subscriber\Cache\CacheSubscriber;

class ScrapperService
{
    private $host = 'http://www.hearthstone-decks.com';

    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $url
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function request($url)
    {
        static $client = null;

        if(!$client) {
            $client = new Client();

            $guzzle = $client->getClient();
            $guzzle->setDefaultOption('debug', true);
            CacheSubscriber::attach($guzzle, array(
                'storage' => new CacheStorage($this->cache),
                'validate' => false,
                'can_cache' => function () {
                    return true;
                }
            ));

            $guzzle->getEmitter()->on(
                'complete',
                function (\GuzzleHttp\Event\CompleteEvent $event) {
                    $response = $event->getResponse();
                    $response->setHeader('Cache-Control', 'max-age=3600');
                },
                'first'
            );
        }

        return $client->request('GET', $url);
    }

    public function getCardList()
    {
        $crawler = $this->request($this->host . '/carte');
    }
}
