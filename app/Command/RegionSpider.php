<?php

namespace App\Command;

use Exception;
use SplQueue;
use App\Model\Region;
use Fuxuqiang\Framework\Http\HttpClient;
use Throwable;
use DOMXPath;

class RegionSpider
{
    const ROOT_URL = 'http://www.stats.gov.cn/sj/tjbz/tjyqhdmhcxhfdm/2023/index.html',

        COUNTY_EXPRESSION = '//tr[@class="countytr"]';

    private readonly string $file;
    
    private int $count = 0, $interval = 2;

    private array $expressions = [
            '//tr[@class="provincetr"]/td/a',
            '//tr[@class="citytr"]/td[2]/a',
            self::COUNTY_EXPRESSION,
            '//tr[@class="towntr"]/td[2]/a',
            '//tr[@class="villagetr"]'
        ];

    private int $rootLen;

    /**
     * 初始化队列和计数
     */
    public function __construct(
        private readonly SplQueue   $queue = new SplQueue,
        private readonly SplQueue   $failedQueue = new SplQueue,
        private readonly HttpClient $http = new HttpClient,
    ) {
        $this->file = runtimePath('spiderQueue.log');
        $this->rootLen = strlen(dirname(self::ROOT_URL)) + 1;
        if (file_exists($this->file)) {
            $this->queue->unserialize(file_get_contents($this->file));
            $this->count = Region::count();
        }
        pcntl_signal(SIGINT, function () { exit; });
    }

    /**
     * 执行
     * @throws Throwable
     */
    public function handle($url = self::ROOT_URL): void
    {
        if ($this->queue->isEmpty()) {
            $this->addUrl($url);
            $this->multiRequest();
        } else {
            for ($i = 1; !$this->queue->isEmpty(); $i++) {
                $this->addUrl($this->getChildUrl($url, $this->queue->dequeue()));
                if (!($i % 9) || $this->queue->isEmpty()) {
                    $this->multiRequest();
                }
            }
        }
    }

    /**
     * 爬取行政区划数据
     * @throws Throwable
     */
    private function crawl($url, $xpath): void
    {
        [$expression, $expressions, $domes] = $this->query($xpath, $this->expressions);
        if (next($expressions)) {
            if ($expression == self::COUNTY_EXPRESSION) {
                foreach ($domes as $dom) {
                    $childNodes = $dom->childNodes;
                    if ($childNodes[0]->firstChild instanceof \DOMText) {
                        $data[] = Region::newInstance($this->trimCode($childNodes[0]->nodeValue), $childNodes[1]->nodeValue);
                    } else {
                        $data[] = $this->getDataAndAddHandle($childNodes[1]->firstChild, $url);
                    }
                }
            } else {
                foreach ($domes as $dom) {
                    if ($dom->attributes['href']) {
                        $data[] = $this->getDataAndAddHandle($dom, $url);
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
        } catch (Throwable $th) {
            $this->failedQueue->enqueue($this->getUri($url));
            throw $th;
        }
        echo "\x0d\x1b[2k", '数据量：', $this->count += count($data);
    }

    /**
     * 并发请求并解析curl句柄
     * @throws Throwable
     */
    private function multiRequest(): void
    {
        foreach ($this->http->multiRequest($this->interval) as $val) {
            $url = curl_getinfo($val->handle, CURLINFO_EFFECTIVE_URL);
            if (
                200 == curl_getinfo($val->handle, CURLINFO_HTTP_CODE)
                && ($xpath = $this->domXpath($val->getContent()))
                && $xpath->query('//table')->length
            ) {
                $this->crawl($url, $xpath);
            } else{
                $this->failedQueue->enqueue($this->getUri($url));
                sleep($this->interval * 5);
            }
            pcntl_signal_dispatch();
        }
    }  

    /**
     * 获取加载了内容的DOMXPath
     */
    private function domXpath($content): DOMXPath
    {
        $doc = new \DOMDocument;
        @$doc->loadHTML($content);
        return new DOMXPath($doc);
    }

    /**
     * 获取节点数据并添加url至队列
     */
    private function getDataAndAddHandle(\DOMNode $node, $url): Region
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
        $this->http->addHandle($url, [], [CURLOPT_ENCODING => 'gzip']);
    }

    /**
     * 解析html节点
     * @throws Exception
     */
    private function query(\DOMXPath $xpath, $expressions): array
    {
        $expression = current($expressions);
        $domes = $xpath->query($expression);
        if ($domes->length) {
            return [$expression, $expressions, $domes];
        } elseif (next($expressions)) {
            return $this->query($xpath, $expressions);
        } else {
            throw new Exception('html解析失败');
        }
    }

    /**
     * 获取链接的URI
     */
    private function getUri($url): string
    {
        return substr($url, $this->rootLen);
    }

    /**
     * 去除行政编码尾处的0
     */
    private function trimCode(string $code): string
    {
        return rtrim($code, 0);
    }

    /**
     * 获取子链接
     */
    public function getChildUrl($url, $uri): string
    {
        return dirname($url) . '/' . $uri;
    }

    /**
     * 处理队列数据并提示是否完成
     */
    public function __destruct()
    {
        foreach ($this->http->getHandles() as $ch) {
            $this->failedQueue->enqueue($this->getUri(curl_getinfo($ch->handle, CURLINFO_EFFECTIVE_URL)));
        }
        foreach ($this->queue as $url) {
            $this->failedQueue->enqueue($url);
        }
        if ($this->failedQueue->count()) {
            file_put_contents($this->file, $this->failedQueue->serialize());
        } elseif (file_exists($this->file)) {
            unlink($this->file);
            echo PHP_EOL, '爬取完成';
        }
        echo PHP_EOL;
    }
}
