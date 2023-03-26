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
    const ROOT_URL = 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2021/index.html',

        COUNTY_EXPRESSION = '//tr[@class="countytr"]';

    private readonly string $file;
    
    private $count;

    private array $expressions = [
            '//tr[@class="provincetr"]/td/a',
            '//tr[@class="citytr"]/td[2]/a',
            self::COUNTY_EXPRESSION,
            '//tr[@class="towntr"]/td[2]/a',
            '//tr[@class="villagetr"]'
        ];

    private int $rootlen;

    /**
     * 初始化队列和计数
     */
    public function __construct(
        private readonly SplQueue   $queue = new SplQueue,
        private readonly SplQueue   $failedQueue = new SplQueue,
        private readonly HttpClient $http = new HttpClient,
    ) {
        $this->file = runtimePath('spiderQueue.log');
        $this->rootlen = strlen(dirname(self::ROOT_URL)) + 1;
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
                if (!($i % 99) || $this->queue->isEmpty()) {
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
        [$expression, $expressions, $doms] = $this->query($xpath, $this->expressions);
        if (next($expressions)) {
            if ($expression == self::COUNTY_EXPRESSION) {
                foreach ($doms as $dom) {
                    $firstNodes = $dom->childNodes;
                    if ($firstNodes[0]->firstChild instanceof \DOMText) {
                        $data[] = [rtrim($firstNodes[0]->nodeValue, 0), $firstNodes[1]->nodeValue];
                    } else {
                        $data[] = $this->getDataAndAddHandle($dom->childNodes[1]->firstChild, $url);
                    }
                }
            } else {
                foreach ($doms as $dom) {
                    $data[] = $this->getDataAndAddHandle($dom, $url);
                }
            }
        } else {
            foreach ($doms as $dom) {
                $childNodes = $dom->childNodes;
                $data[] = [$childNodes[0]->nodeValue, $childNodes[2]->nodeValue];
            }
        }
        try {
            $this->insert($data);
        } catch (Throwable $th) {
            $this->failedQueue->enqueue($this->getUri($url));
            throw $th;
        }
    }

    /**
     * 并发请求并解析curl句柄
     * @throws Throwable
     */
    private function multiRequest(): void
    {
        foreach ($this->http->multiRequest(30, 2) as $val) {
            $url = curl_getinfo($val->handle, CURLINFO_EFFECTIVE_URL);
            if (
                200 == curl_getinfo($val->handle, CURLINFO_HTTP_CODE)
                && ($xpath = $this->domXpath($val->getContent()))
                && $xpath->query('//table')->length
            ) {
                $this->crawl($url, $xpath);
            } else{
                $this->failedQueue->enqueue($this->getUri($url));
                sleep(5);
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
    private function getDataAndAddHandle(\DOMNode $dom, $url): array
    {
        $uri = $dom->attributes['href']->nodeValue;
        $this->addUrl($this->getChildUrl($url, $uri));
        return [substr(basename($uri), 0, -5), $dom->nodeValue];
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
        $doms = $xpath->query($expression);
        if ($doms->length) {
            return [$expression, $expressions, $doms];
        } else {
            if (next($expressions)) {
                return $this->query($xpath, $expressions);
            } else {
                throw new Exception('html解析失败');
            }
        }
    }

    /**
     * 写入数据
     */
    private function insert($data): void
    {
        Region::fields([Region::CODE, Region::NAME])->insert($data);
        echo "\x0d\x1b[2k", '数据量：', $this->count += count($data);
    }

    /**
     * 获取链接的URI
     */
    private function getUri($url): string
    {
        return substr($url, $this->rootlen);
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
            $this->failedQueue->enqueue(
                $this->getUri(curl_getinfo($ch->handle, CURLINFO_EFFECTIVE_URL))
            );
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
