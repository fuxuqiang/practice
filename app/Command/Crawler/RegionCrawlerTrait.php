<?php

namespace App\Command\Crawler;

use DOMXPath;
use Exception;

trait RegionCrawlerTrait
{
    private const ROOT_URL = 'https://www.stats.gov.cn/sj/tjbz/tjyqhdmhcxhfdm/2023/index.html',

        COUNTY_EXPRESSION = '//tr[@class="countytr"]',

        FILE = 'crawlerQueue.log';

    private string $filePath;

    private int $count = 0;

    private array $expressions = [
        '//tr[@class="provincetr"]/td/a',
        '//tr[@class="citytr"]/td[2]/a',
        self::COUNTY_EXPRESSION,
        '//tr[@class="towntr"]/td[2]/a',
        '//tr[@class="villagetr"]'
    ];

    private int $rootLen;

    private function init(): void
    {
        $this->filePath = runtimePath(self::FILE);
        $this->rootLen = strlen(dirname(self::ROOT_URL)) + 1;
        if (file_exists($this->filePath)) {
            $this->storage->unserialize(file_get_contents($this->filePath));
            $this->count = \App\Model\Region::count();
        }
        pcntl_signal(SIGINT, function () { exit; });
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
     * 获取子链接
     */
    private function getChildUrl($url, $uri): string
    {
        return dirname($url) . '/' . $uri;
    }

    /**
     * 解析html节点
     * @throws Exception
     */
    private function query(DOMXPath $xpath, $expressions): array
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
     * 去除行政编码尾处的0
     */
    private function trimCode(string $code): string
    {
        return rtrim($code, 0);
    }
}