<?php

namespace App\Command;

class RegionSpider
{
    const ROOT_URL = 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2021/index.html',

        FILE = __DIR__ . '/../../runtime/spiderQueue.log',

        COUNTY_EXPRESSION = '//tr[@class="countytr"]';

    private $queue, $failedQueue, $count, $http,

        $expressions = [
            '//tr[@class="provincetr"]/td/a',
            '//tr[@class="citytr"]/td[2]/a',
            self::COUNTY_EXPRESSION,
            '//tr[@class="towntr"]/td[2]/a',
            '//tr[@class="villagetr"]'
        ];

    /**
     * 初始化队列和计数
     */
    public function __construct()
    {
        $this->queue = new \SplQueue;
        $this->failedQueue = new \SplQueue;
        $this->http = new \Fuxuqiang\Framework\Http\HttpClient(true);

        if (file_exists(self::FILE)) {
            $this->queue->unserialize(file_get_contents(self::FILE));
            $this->count = $this->getQuery()->count();
        }

        pcntl_signal(SIGINT, function () {
            foreach ($this->http->getChs() as $ch) {
                $this->failedQueue->enqueue($this->getUri(curl_getinfo($ch->handle, CURLINFO_EFFECTIVE_URL)));
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
    public function handle($url = self::ROOT_URL)
    {
        if ($this->queue->isEmpty()) {
            $this->crawl($url, $this->domXpath(file_get_contents($url)));
        } else {
            for ($i = 1; !$this->queue->isEmpty(); $i++) {
                $this->http->addHandle($this->getChildUrl($url, $this->queue->dequeue()));
                if (!($i % 100) || $this->queue->isEmpty()) {
                    $this->multiRequest();
                }
            }
        }
    }

    /**
     * 爬取行政区划数据
     */
    private function crawl($url, $xpath)
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
            $this->insert($data);
            $this->multiRequest();
        } else {
            foreach ($doms as $dom) {
                $childNodes = $dom->childNodes;
                $data[] = [$childNodes[0]->nodeValue, $childNodes[2]->nodeValue];
            }
            $this->insert($data);
        }
    }

    /**
     * 并发请求并解析curl句柄
     */
    private function multiRequest()
    {
        foreach ($this->http->multiRequest() as $val) {
            $this->getCurlInfoAndContinueCrawl($val->handle);
            pcntl_signal_dispatch();
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
            echo "\x0d\x1b[2k爬取：$url\n";
            $this->crawl($url, $xpath);
        } else{
            $this->failedQueue->enqueue($this->getUri($url));
        }
    }

    /**
     * 获取加载了内容的DOMXPath
     */
    private function domXpath($content)
    {
        $doc = new \DOMDocument;
        $doc->loadHTML($content);
        return new \DOMXPath($doc);
    }

    /**
     * 获取节点数据并添加url至队列
     */
    private function getDataAndAddHandle(\DOMNode $dom, $url)
    {
        $uri = $dom->attributes['href']->nodeValue;
        $this->http->addHandle($this->getChildUrl($url, $uri));
        return [substr(basename($uri), 0, -5), $dom->nodeValue];
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
     * @return \Fuxuqiang\Framework\Mysql
     */
    private function getQuery()
    {
        return \Src\Mysql::table('region');
    }

    /**
     * 写入数据
     */
    private function insert($data)
    {
        $this->getQuery()->cols('code', 'name')->insert($data);
        echo "\x0d\x1b[2k", '数据量：', $this->count += count($data);
    }

    /**
     * 获取链接的URI
     */
    private function getUri($url)
    {
        return ltrim($url, dirname(self::ROOT_URL) . '/');
    }

    /**
     * 获取子链接
     */
    public function getChildUrl($url, $uri)
    {
        return dirname($url) . '/' . $uri;
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
