<?php

namespace app\command;

use vendor\HttpClient;

class RegionSpider
{
    const ROOT_URL = 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2019/index.html',

        FILE = __DIR__ . '/../../runtime/spiderQueue.log';

    private $queue, $failedQueue, $count,

        $expressions = [
            '//tr[@class="provincetr"]/td/a',
            '//tr[@class="citytr"]/td[2]/a',
            '//tr[@class="countytr"]',
            '//tr[@class="towntr"]/td[2]/a',
            '//tr[@class="villagetr"]'
        ];

    /**
     * @var HttpClient
     */
    private $http;

    /**
     * 初始化队列和计数
     */
    public function __construct()
    {
        $this->queue = new \SplQueue;
        $this->failedQueue = new \SplQueue;
        if (file_exists(self::FILE)) {
            $this->queue->unserialize(file_get_contents(self::FILE));
            $this->count = $this->getQuery()->count();
        }
        pcntl_signal(SIGINT, function () {
            foreach ($this->http->getChs() as $ch) {
                $this->failedQueue->enqueue(curl_getinfo($ch['handle'], CURLINFO_EFFECTIVE_URL));
            }
            foreach ($this->queue as $url) {
                $this->failedQueue->enqueue($url);
            }
            exit;
        });
    }

    /**
     * 执行
     */
    public function handle()
    {
        if ($this->queue->isEmpty()) {
            $this->crawl(self::ROOT_URL, $this->domXpath(file_get_contents(self::ROOT_URL)), true);
        } else {
            for ($i = 0; !$this->queue->isEmpty(); $i++) {
                $i % 100 || $http = new HttpClient(true);
                $http->addHandle($this->queue->dequeue());
                if (!(($i + 1) % 100) || $this->queue->isEmpty()) {
                    $this->multiRequest($http, true);
                }
            }
        }
    }

    /**
     * 爬取行政区划数据
     */
    private function crawl($url, $xpath, $isRoot)
    {
        [$expression, $expressions, $doms] = $this->query($xpath, $this->expressions);
        if (next($expressions)) {
            $http = new HttpClient(true);
            if ($expression == '//tr[@class="countytr"]') {
                foreach ($doms as $dom) {
                    $firstNodes = $dom->childNodes;
                    if ($firstNodes[0]->firstChild instanceof \DOMText) {
                        $data[] = [rtrim($firstNodes[0]->nodeValue, 0), $firstNodes[1]->nodeValue];
                    } else {
                        $data[] = $this->getDataAndAddHandle($http, $dom->childNodes[1]->firstChild, $url);
                    }
                }
            } else {
                foreach ($doms as $dom) {
                    $data[] = $this->getDataAndAddHandle($http, $dom, $url);
                }
            }
            $this->insert($data);
            sleep(1);
            $this->multiRequest($http, $isRoot);
        } else {
            foreach ($doms as $dom) {
                $childNodes = $dom->childNodes;
                $data[] = [$childNodes[0]->nodeValue, $childNodes[2]->nodeValue];
            }
            $this->insert($data);
            sleep(1);
        }
    }

    /**
     * 并发请求并解析curl句柄
     */
    private function multiRequest(HttpClient $http, $isRoot)
    {
        $gen = $http->multiRequest(3);
        if ($isRoot) {
            $this->http = $http;
            foreach ($gen as $val) {
                declare (ticks = 1) {
                    $this->getCurlInfoAndContinueCrawl($val['handle']);
                }
            }
        } else {
            foreach ($gen as $val) {
                $this->getCurlInfoAndContinueCrawl($val['handle']);
            }
        }
    }

    /**
     * 解析响应页面并继续爬取子页面
     */
    private function getCurlInfoAndContinueCrawl($ch)
    {
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        if (
            200 == curl_getinfo($ch, CURLINFO_HTTP_CODE)
            && ($xpath = $this->domXpath(curl_multi_getcontent($ch)))
            && $xpath->query('//table')->length
        ) {
            $this->crawl($url, $xpath, false);
        } else{
            $this->failedQueue->enqueue($url);
            sleep(1);
        }
    }

    /**
     * 获取加载了内容的DOMXPath
     */
    private function domXpath($content)
    {
        $doc = new \DOMDocument;
        @$doc->loadHTML(str_replace('gb2312', 'utf-8', iconv('GB2312', 'UTF-8//IGNORE', $content)));
        return new \DOMXPath($doc);
    }

    /**
     * 获取节点数据并添加url至队列
     */
    private function getDataAndAddHandle(HttpClient $http, \DOMNode $dom, $url)
    {
        $file = $dom->attributes['href']->nodeValue;
        $http->addHandle(dirname($url) . '/' . $file);
        return [substr(basename($file), 0, -5), $dom->nodeValue];
    }

    /**
     * 获取节点数据
     */
    private function getDomData(\DOMNode $dom)
    {
        return [substr(basename($dom->attributes['href']->nodeValue), 0, -5), $dom->nodeValue];
    }

    /**
     * 解析html节点
     */
    private function query(\DOMXPath $xpath, $expressions)
    {
        $expression = current($expressions);
        $doms = $xpath->query($expression);
        if ($doms->length) {
            return [$expression, $expressions, $doms];
        } else {
            if (next($expressions)) {
                return $this->query($xpath, $expressions);
            } else {
                throw new \Exception('html解析失败');
            }
        }
    }

    /**
     * 获取数据库查询实例
     * @return \vendor\Mysql
     */
    private function getQuery()
    {
        return \src\Mysql::table('region');
    }

    /**
     * 写入数据
     */
    private function insert($data)
    {
        $this->getQuery()->cols('code', 'name')->insert($data);
        echo "\x0d\x1b[2k", '已爬取数据量：', $this->count += count($data);
    }

    /**
     * 处理队列数据并提示是否完成
     */
    public function __destruct()
    {
        if ($this->failedQueue->count()) {
            file_put_contents(self::FILE, $this->failedQueue->serialize());
        } elseif (file_exists(self::FILE)) {
            unlink(self::FILE);
            echo PHP_EOL, '爬取完成';
        }
        echo PHP_EOL;
    }
}
