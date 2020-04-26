<?php

namespace app\command;

use vendor\HttpClient;

class RegionSpider
{
    const ROOT_URL = 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2019/index.html';

    const FILE = __DIR__ . '/../../runtime/spiderQueue.log';

    private $queue, $failedQueue, $count = 0;

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
    }

    /**
     * 执行
     */
    public function handle()
    {
        $expressions = [
            '//tr[@class="provincetr"]/td/a',
            '//tr[@class="citytr"]/td[2]/a',
            '//tr[@class="countytr"]',
            '//tr[@class="towntr"]/td[2]/a',
            '//tr[@class="villagetr"]'
        ];
        if ($this->queue->isEmpty()) {
            $this->crawl(
                self::ROOT_URL,
                $this->domXpath(file_get_contents(self::ROOT_URL)),
                $expressions
            );
        } else {
            for ($i = 0; !$this->queue->isEmpty(); $i++) {
                $i % 100 || $http = new HttpClient(true);
                $http->addHandle($this->queue->dequeue());
                if (!(($i + 1) % 100) || $this->queue->isEmpty()) {
                    $this->childCrawl($http, array_slice($expressions, 1));
                }
            }
        }
    }

    /**
     * 爬取行政区划数据
     */
    private function crawl($url, $xpath, $expressions)
    {
        [$expression, $expressions, $doms] = $this->query($xpath, $expressions);
        if (next($expressions)) {
            $http = new HttpClient(true);
            if ($expression == '//tr[@class="countytr"]') {
                foreach ($doms as $dom) {
                    $firstNodes = $dom->childNodes;
                    if ($firstNodes[0]->firstChild instanceof \DOMText) {
                        $data[] = [rtrim($firstNodes[0]->nodeValue, 0), $firstNodes[1]->nodeValue];
                    } else {
                        $data[] = $this->addHandle($http, $dom->childNodes[1]->firstChild, $url);
                    }
                }
            } else {
                foreach ($doms as $dom) {
                    $data[] = $this->addHandle($http, $dom, $url);
                }
            }
            $this->childCrawl($http, $expressions);
        } else {
            foreach ($doms as $dom) {
                $childNodes = $dom->childNodes;
                $data[] = [$childNodes[0]->nodeValue, $childNodes[2]->nodeValue];
            }
            sleep(1);
        }
        $this->getQuery()->cols('code', 'name')->insert($data);
        echo "\x0d\x1b[2k", '已爬取数据量：', $this->count += count($data);
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
     * 添加curl句柄并获取节点数据
     */
    private function addHandle(HttpClient $http, \DOMNode $dom, $url)
    {
        $file = $dom->attributes['href']->nodeValue;
        $http->addHandle(dirname($url) . '/' . $file);
        return [substr(basename($file), 0, -5), $dom->nodeValue];
    }

    /**
     * 解析请求并继续爬取子页面
     */
    private function childCrawl(HttpClient $http, $expressions)
    {
        $failedUrls = [];
        foreach ($http->multiRequest(2) as $val) {
            $url = curl_getinfo($val['handle'], CURLINFO_EFFECTIVE_URL);
            if (
                200 == curl_getinfo($val['handle'], CURLINFO_HTTP_CODE)
                && ($xpath = $this->domXpath(curl_multi_getcontent($val['handle'])))
                && $xpath->query('//table')->length
            ) {
                $this->crawl($url, $xpath, $expressions);
            } else{
                if (in_array($url, $failedUrls)) {
                    $this->failedQueue->enqueue($url);
                } else {
                    $http->addHandle($url);
                    $failedUrls[] = $url;
                }
                sleep(1);
            }
        }
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
     * 缓存爬取失败的队列数据
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
