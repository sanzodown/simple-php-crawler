<?php

namespace Sanzodown\SimplePHPCrawler;

use Symfony\Component\CssSelector\CssSelectorConverter;

class Crawler
{
    private const CHARSET = "UTF-8";

    private $uri;
    private $requireAuth = false;
    private $dom;
    private $username;
    private $password;

    public function __construct(string $startingURI)
    {
        $this->uri = $this->validateURI($startingURI);
        $this->dom = $this->getDOMDocument();
    }

    public function getDOMDocument(): \DOMDocument
    {
        $handle = curl_init($this->uri);

        if ($this->requireAuth) {
            curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($handle, CURLOPT_USERPWD, "$this->username:$this->password");
        }

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        $body = curl_exec($handle);
        curl_close($handle);

        return $this->parseHtml($body);
    }

    public function getAllLinks(): array
    {
        $links = [];

        foreach($this->dom->getElementsByTagName('a') as $link) {
            $links[] = ['url' => $link->getAttribute('href'), 'text' => $link->nodeValue];
        }

        return $links;
    }

    public function getAllImages(): array
    {
        $images = [];

        foreach($this->dom->getElementsByTagName('img') as $img) {
            $images[] = ['url' => $img->getAttribute('src')];
        }

        return $images;
    }

    private function convertToHTMLEntities(string $html): string
    {
        return mb_convert_encoding($html, 'HTML-ENTITIES', Crawler::CHARSET);
    }

    private function parseHtml(string $html): \DOMDocument
    {
        $htmlContent = $this->convertToHTMLEntities($html);

        $dom = new \DOMDocument('1.0', Crawler::CHARSET);
        @$dom->loadHTML($htmlContent);

        return $dom;
    }

    public function filter(string $selector): array
    {
        $converter = $this->createCssSelectorConverter();
        $xpath = new \DOMXPath($this->dom);

        return $this->DOMNodeListToArray($xpath->query($converter->toXPath($selector)));
    }

    private function DOMNodeListToArray(\DOMNodeList $domNodeList): array
    {
        $array = [];

        foreach($domNodeList as $node){
            $array[] = $this->XMLToArray($node);
        }

        return $array;
    }

    private function XMLToArray($root): array
    {
        $result = [];

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['value']
                        : $result;
                }
            }
            $groups = [];
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = $this->XMLToArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = [$result[$child->nodeName]];
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = $this->XMLToArray($child);
                }
            }
        }

        return $result;
    }

    private function createCssSelectorConverter(): CssSelectorConverter
    {
        if (!class_exists(CssSelectorConverter::class)) {
            throw new \LogicException('To filter with a CSS selector, install the CssSelector component ("composer require symfony/css-selector"). Or use filterXpath instead.');
        }

        return new CssSelectorConverter(true);
    }

    private function validateURI(string $uri): string
    {
        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new \Exception('URI not valid.');
        }

        return $uri;
    }

    public function setLogin(string $username, string $password): void
    {
        $this->requireAuth = true;
        $this->username = $username;
        $this->password = $password;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }
}