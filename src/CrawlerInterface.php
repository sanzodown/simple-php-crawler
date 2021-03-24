<?php

namespace Sanzodown\SimplePHPCrawler;

interface CrawlerInterface
{
    public function getDOMDocument(): \DOMDocument;

    /*
     * Take a CSS selector as argument
     * Return a simplified array of the DOMNodeList
    */
    public function filter(string $selector): array;

    public function getAllImages(): array;

    public function getAllLinks(): array;
}
