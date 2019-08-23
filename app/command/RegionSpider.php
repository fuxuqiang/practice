<?php
namespace app\command;

class regionSpider
{
    public function handle()
    {
        $this->crawl('http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2018/index.html', [
            '//tr[@class="provincetr"]/td/a',
            '//tr[@class="citytr"]/td[2]/a',
            '//tr[@class="countytr"]',
            '//tr[@class="towntr"]/td[2]/a',
            '//tr[@class="villagetr"]'
        ]);
    }

    private function crawl($url, $expressions)
    {
        if ($content = @file_get_contents($url)) {
            $doc = new DOMDocument;
            $doc->loadHTML(
                str_replace('gb2312', 'utf-8', iconv('GB2312', 'UTF-8//IGNORE', $content))
            );
            $expression = current($expressions);
            if (! $doms = (new DOMXPath($doc))->query($expression)) {
                $expression = next($expressions);
                $doms = (new DOMXPath($doc))->query($expression);
            }
            if (next($expressions)) {
                if ($expression == '//tr[@class="countytr"]') {
                    $firstNodes = $doms->item(0)->childNodes;
                    if ($firstNodes[0]->firstChild instanceof DOMText) {
                        $data[] = [$firstNodes[0]->nodeValue, $firstNodes[1]->nodeValue];
                        $i = 1;
                    } else {
                        $i = 0;
                    }
                    for (; $i < $doms->length; $i++) { 
                        $dom = $doms->item($i)->childNodes[1]->firstChild;
                        $data[] = $this->childCrawl($dom, $url, $expressions);
                    }
                } else {
                    foreach ($doms as $dom) {
                        $data[] = $this->childCrawl($dom, $url, $expressions);
                    }    
                }
            } else {
                foreach ($doms as $dom) {
                    $childNodes = $dom->childNodes;
                    $data[] = [$childNodes[0]->nodeValue, $childNodes[2]->nodeValue];
                }
            }
            mysql('region')->cols('code', 'name')->insert($data);
            return true;
        } else {
            return false;
        }
    }

    private function childCrawl($dom, $url, $expressions)
    {
        $file = $dom->attributes['href']->nodeValue;
        $url = dirname($url).'/'.$file;
        $this->crawl($url, $expressions) || $this->crawl($url, $expressions) || logError($url, false);
        return [substr(basename($file), 0, -5), $dom->nodeValue];
    }
}
