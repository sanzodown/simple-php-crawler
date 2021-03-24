<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Sanzodown\SimplePHPCrawler\Crawler;

final class CrawlerTest extends TestCase
{
    public function testInvalidURI(): void
    {
        self::expectExceptionMessage("URI not valid.");
        new Crawler("httppokemoncom");
    }

    public function testCrawlerGetAllLinks(): void
    {
        $crawler = new Crawler("http://www.google.com");
        $links = $crawler->getAllLinks();

        self::assertTrue(!empty($links));
    }

    public function testCrawlerGetAllLinksNoResults(): void
    {
        $crawler = new Crawler("http://www.sdqdqsdqsdddqdsdqdqsd.com");
        $links = $crawler->getAllLinks();

        self::assertTrue(empty($links));
    }

    public function testCrawlerGetAllImgs(): void
    {
        $crawler = new Crawler("https://www.judgehype.com/");
        $imgs = $crawler->getAllImages();

        self::assertTrue(!empty($imgs));
    }

    public function testFilterCss(): void
    {
        $crawler = new Crawler("https://www.judgehype.com/");
        $selector = $crawler->filter(".header-logo > a");

        self::assertSame("https://judgehype.com", $selector[0]["attributes"]["href"]);
    }
}
