<?php

namespace App\Command\Crawler;

use App\Model\Region;
use DOMXPath;
use Generator;
use SplObjectStorage;
use stdClass;
use Throwable;

class RegionCrawler
{
    use RegionCrawlerTrait;

    /**
     * 初始化队列和计数
     */
    public function __construct(protected readonly SplObjectStorage $storage = new SplObjectStorage) {
        $this->init();
    }

    /**
     * 执行
     * @throws Throwable
     */
    public function handle(string $url = self::ROOT_URL): void
    {
        if ($this->storage->count() > 0) {
            foreach ($this->storage as $item) {
                $this->request($item);
            }
        } else {
            $url = (object)$url;
            $this->storage->attach($url);
            $this->request($url);
        }
    }

    /**
     * 获取网页数据并爬取
     * @throws Throwable
     */
    protected function request(stdClass $url): void
    {
        try {
            $content = file_get_contents($url->scalar);
        } catch (Throwable) {
            return;
        }
        $this->crawl($url, $this->domXpath($content));
        pcntl_signal_dispatch();
    }

    /**
     * 爬取行政区划数据
     * @throws Throwable
     */
    protected function crawl(stdClass $url, DOMXPath $xpath): void
    {
        [$expression, $expressions, $domes] = $this->query($xpath, $this->expressions);

        if (next($expressions)) {
            if ($expression == self::COUNTY_EXPRESSION) {
                $dataAndGenerators = $this->getData($domes, function ($dom) use ($url) {
                    $childNodes = $dom->childNodes;
                    if ($childNodes[0]->firstChild instanceof \DOMText) {
                        return Region::newInstance($this->trimCode($childNodes[0]->nodeValue), $childNodes[1]->nodeValue);
                    } else {
                        return $this->getDataAndContinue($childNodes[1]->firstChild, $url);
                    }
                });
            } else {
                $dataAndGenerators = $this->getData($domes, function ($dom) use ($url) {
                    if ($dom->attributes['href']) {
                        return $this->getDataAndContinue($dom, $url);
                    } else {
                        return Region::newInstance(
                            $this->trimCode($dom->parentNode->previousSibling->childNodes[0]->nodeValue),
                            $dom->nodeValue
                        );
                    }
                });
            }
        } else {
            $dataAndGenerators = $this->getData($domes, function ($dom) {
                $childNodes = $dom->childNodes;
                return Region::newInstance($childNodes[0]->nodeValue, $childNodes[2]->nodeValue);
            });
        }

        Region::batchSave($dataAndGenerators->data);
        $this->storage->detach($url);
        echo "\x0d\x1b[2k", '数据量：', $this->count += count($dataAndGenerators->data);
        foreach ($dataAndGenerators->generators as $generator) {
            $generator->next();
        }
    }

    private function getData(\DOMNodeList $domes, callable $func): DataAndGenerators
    {
        $fibers = [];
        foreach ($domes as $dom) {
            $result = $func($dom);
            if ($result instanceof Generator) {
                $data[] = $result->current();
                $fibers[] = $result;
            } else {
                $data[] = $result;
            }
        }
        return new DataAndGenerators($data, $fibers);
    }

    /**
     * 获取节点数据并添加url至队列
     * @throws Throwable
     */
    protected function getDataAndContinue(\DOMNode $node, stdClass $url): Generator
    {
        $uri = $node->attributes['href']->nodeValue;
        $childUrl = (object)$this->getChildUrl($url->scalar, $uri);
        $this->storage->attach($childUrl);
        yield Region::newInstance(substr(basename($uri), 0, -5), $node->nodeValue);
        $this->request($childUrl);
    }

    /**
     * 处理队列数据并提示是否完成
     */
    public function __destruct()
    {
        if ($this->storage->count()) {
            file_put_contents($this->filePath, $this->storage->serialize());
        } elseif (file_exists($this->filePath)) {
            unlink($this->filePath);
            echo PHP_EOL, '爬取完成';
        }
        echo PHP_EOL;
    }
}