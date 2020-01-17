<?php

namespace Coral\SiteBundle\Tests\Utility;

use Coral\SiteBundle\Utility\Finder;
use Coral\SiteBundle\Utility\PropertiesParser;
use PHPUnit\Framework\TestCase;

class PropertiesParserTest extends TestCase
{
    public function testInvalidFileException()
    {
        $this->expectException('InvalidArgumentException');

        $finder = new Finder('invalid_filename');
        PropertiesParser::parse($finder);
    }

    public function testEmptyPropertiesException()
    {
        $this->expectException('Coral\SiteBundle\Exception\PropertiesParserException');

        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/empty');
        PropertiesParser::parse($finder);
    }

    public function testInvalidPropertiesException()
    {
        $this->expectException('Coral\SiteBundle\Exception\PropertiesParserException');

        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/invalid');
        PropertiesParser::parse($finder);
    }

    public function testInvalidPropertiesKeyException()
    {
        $this->expectException('Coral\SiteBundle\Exception\PropertiesParserException');

        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/invalid_key');
        PropertiesParser::parse($finder);
    }

    public function testInvalidPropertiesKeyValueException()
    {
        $this->expectException('Coral\SiteBundle\Exception\PropertiesParserException');

        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/invalid_value');
        PropertiesParser::parse($finder);
    }

    public function testParse()
    {
        $finder = new Finder(dirname(__FILE__) . '/../Resources/fixtures/properties/correct');
        $properties = PropertiesParser::parse($finder);

        $this->assertTrue(is_array($properties));
        $this->assertCount(2, $properties);
        $this->assertArrayHasKey('name', $properties);
        $this->assertArrayHasKey('properties', $properties);
        $this->assertEquals('Homepage', $properties['name']);
        $this->assertCount(3, $properties['properties']);
        $this->assertEquals('default.html.twig', $properties['properties']['tree_template']);
        $this->assertEquals('acme, project, default', $properties['properties']['keywords']);
        $this->assertEquals('ACME: see for yourself', $properties['properties']['description']);
    }
}