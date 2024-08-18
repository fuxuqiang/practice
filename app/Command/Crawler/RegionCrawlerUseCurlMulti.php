<?php

namespace App\Command\Crawler;

use App\Model\Region;
use Fuxuqiang\Framework\Http\HttpClient;
use SplDoublyLinkedList;
use Throwable;

class RegionCrawlerUseCurlMulti
{
    use RegionCrawlerTrait;

    private int $interval = 2;
    
    public function __construct(
        private readonly SplDoublyLinkedList $storage = new SplDoublyLinkedList,
        private readonly SplDoublyLinkedList $failedList = new SplDoublyLinkedList,
        private readonly HttpClient          $httpClient = new HttpClient,
    ) {
        $this->init();
    }

    /**
     * 执行
     * @throws Throwable
     */
    public function handle($url = self::ROOT_URL): void
    {
        if ($this->storage->isEmpty()) {
            $this->crawl($url, $this->domXpath(file_get_contents($url)));
            $this->multiRequest();
        } else {
            for ($i = 1; !$this->storage->isEmpty(); $i++) {
                $this->addUrl($this->storage->pop());
                if (!($i % 9) || $this->storage->isEmpty()) {
                    $this->multiRequest();
                }
            }
        }
    }

    /**
     * 爬取行政区划数据
     * @throws Throwable
     */
    protected function crawl(string $url, \DOMXPath $xpath): void
    {
        [$expression, $expressions, $domes] = $this->query($xpath, $this->expressions);

        if (next($expressions)) {
            if ($expression == self::COUNTY_EXPRESSION) {
                foreach ($domes as $dom) {
                    $childNodes = $dom->childNodes;
                    if ($childNodes[0]->firstChild instanceof \DOMText) {
                        $data[] = Region::newInstance($this->trimCode($childNodes[0]->nodeValue), $childNodes[1]->nodeValue);
                    } else {
                        $data[] = $this->getDataAndContinue($childNodes[1]->firstChild, $url);
                    }
                }
            } else {
                foreach ($domes as $dom) {
                    if ($dom->attributes['href']) {
                        $data[] = $this->getDataAndContinue($dom, $url);
                    } else {
                        $data[] = Region::newInstance(
                            $this->trimCode($dom->parentNode->previousSibling->childNodes[0]->nodeValue),
                            $dom->nodeValue
                        );
                    }
                }
            }
        } else {
            foreach ($domes as $dom) {
                $childNodes = $dom->childNodes;
                $data[] = Region::newInstance($childNodes[0]->nodeValue, $childNodes[2]->nodeValue);
            }
        }

        try {
            Region::batchSave($data);
        } catch (Throwable) {
            $this->failedList->push($url);
            return;
        }

        echo "\x0d\x1b[2k", '数据量：', $this->count += count($data);
    }

    /**
     * 并发请求并解析curl句柄
     * @throws Throwable
     */
    private function multiRequest(): void
    {
        foreach ($this->httpClient->multiRequest($this->interval) as $val) {
            $url = curl_getinfo($val->handle, CURLINFO_EFFECTIVE_URL);
            if (
                200 == curl_getinfo($val->handle, CURLINFO_HTTP_CODE)
                && ($xpath = $this->domXpath($val->getContent()))
                && $xpath->query('//table')->length
            ) {
                $this->crawl($url, $xpath);
            } else{
                $this->failedList->push($url);
                sleep($this->interval * 5);
            }
            pcntl_signal_dispatch();
        }
    }



    /**
     * 获取节点数据并添加url至队列
     */
    protected function getDataAndContinue(\DOMNode $node, $url): Region
    {
        $uri = $node->attributes['href']->nodeValue;
        $this->addUrl($this->getChildUrl($url, $uri));
        return Region::newInstance(substr(basename($uri), 0, -5), $node->nodeValue);
    }

    /**
     * 添加url至HttpClient
     */
    private function addUrl($url): void
    {
        $this->httpClient->addHandle($url, [], [CURLOPT_ENCODING => 'gzip']);
    }

    /**
     * 处理队列数据并提示是否完成
     */
    public function __destruct()
    {
        foreach ($this->httpClient->getHandles() as $ch) {
            $this->failedList->push(curl_getinfo($ch->handle, CURLINFO_EFFECTIVE_URL));
        }
        foreach ($this->storage as $url) {
            $this->failedList->push($url);
        }
        if ($this->failedList->count()) {
            file_put_contents($this->filePath, $this->failedList->serialize());
        } elseif (file_exists($this->filePath)) {
            unlink($this->filePath);
            echo PHP_EOL, '爬取完成';
        }
        echo PHP_EOL;
    }
}
