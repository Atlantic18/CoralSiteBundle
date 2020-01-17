<?php

namespace Coral\SiteBundle\Tests\Utility;

use Coral\SiteBundle\Utility\Finder;
use Coral\SiteBundle\Utility\SortorderParser;

class SortorderParserTest extends \PHPUnit\Framework\TestCase
{
    public function testInvalidFileException()
    {
        $this->expectException('InvalidArgumentException');

        $finder = new Finder('invalid_path');
        SortorderParser::parse($finder);
    }

    public function testEmptySortorderException()
    {
        $this->expectException('Coral\SiteBundle\Exception\SortorderParserException');

        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/sortorder/empty');
        SortorderParser::parse($finder);
    }

    public function testEmptyFolderException()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/sortorder/empty_folder');
        $sortorder = SortorderParser::parse($finder);

        $this->assertTrue(is_array($sortorder));
        $this->assertCount(0, $sortorder);
    }

    public function testParseCorrect()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/sortorder/correct');
        $sortorder = SortorderParser::parse($finder);

        $this->assertTrue(is_array($sortorder));
        $this->assertCount(4, $sortorder);
        $this->assertEquals(array('products', 'about-us', 'contact-us', 'buy-now'), $sortorder);
    }

    public function testParseFullFolder()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/sortorder/full_folder');
        $sortorder = SortorderParser::parse($finder);

        $this->assertTrue(is_array($sortorder));
        $this->assertCount(2, $sortorder);
        $this->assertEquals(array('atest.html', 'ztest.md'), $sortorder);
    }
}