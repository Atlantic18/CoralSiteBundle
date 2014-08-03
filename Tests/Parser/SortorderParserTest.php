<?php

namespace Coral\SiteBundle\Tests\Parser;

use Coral\SiteBundle\Parser\SortorderParser;

class SortorderParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidFileException()
    {
        SortorderParser::parse('invalid_filename');
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\SortorderParserException
     */
    public function testEmptyPropertiesException()
    {
        SortorderParser::parse(dirname(__FILE__) . '/../Resources/fixtures/.empty_sortorder');
    }

    public function testParse()
    {
        $sortorder = SortorderParser::parse(dirname(__FILE__) . '/../Resources/fixtures/.sortorder');

        $this->assertTrue(is_array($sortorder));
        $this->assertCount(4, $sortorder);
        $this->assertEquals(array('products', 'about-us', 'contact-us', 'buy-now'), $sortorder);
    }
}