<?php

namespace Coral\SiteBundle\Tests\Parser;

use Coral\SiteBundle\Parser\PropertiesParser;

class PropertiesParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidFileException()
    {
        PropertiesParser::parse('invalid_filename');
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PropertiesParserException
     */
    public function testEmptyPropertiesException()
    {
        PropertiesParser::parse(dirname(__FILE__) . '/../Resources/fixtures/.empty_properties');
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PropertiesParserException
     */
    public function testInvalidPropertiesException()
    {
        PropertiesParser::parse(dirname(__FILE__) . '/../Resources/fixtures/.invalid_properties');
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PropertiesParserException
     */
    public function testInvalidPropertiesKeyException()
    {
        PropertiesParser::parse(dirname(__FILE__) . '/../Resources/fixtures/.invalid_properties_key');
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PropertiesParserException
     */
    public function testInvalidPropertiesKeyValueException()
    {
        PropertiesParser::parse(dirname(__FILE__) . '/../Resources/fixtures/.invalid_properties_key_value');
    }

    public function testParse()
    {
        $properties = PropertiesParser::parse(dirname(__FILE__) . '/../Resources/fixtures/.properties');

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