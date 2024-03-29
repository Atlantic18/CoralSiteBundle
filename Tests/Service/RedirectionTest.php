<?php

/*
 * This file is part of the Coral package.
 *
 * (c) Frantisek Troster <frantisek.troster@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coral\SiteBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Coral\SiteBundle\Service\Redirection;

class RedirectionTest extends KernelTestCase
{
    public function testException()
    {
        $this->expectException('Coral\SiteBundle\Exception\ConfigurationException');

        new Redirection('invalid_path');
    }

    public function testEmptyFile()
    {
        $this->expectException('Coral\SiteBundle\Exception\ConfigurationException');

        new Redirection(dirname(__FILE__) . '/../Resources/fixtures/redirection/empty');
    }

    public function testMissingKey()
    {
        $this->expectException('Coral\SiteBundle\Exception\ConfigurationException');

        new Redirection(dirname(__FILE__) . '/../Resources/fixtures/redirection/missing_key');
    }

    public function testInvalidEntry()
    {
        $this->expectException('Coral\SiteBundle\Exception\ConfigurationException');

        new Redirection(dirname(__FILE__) . '/../Resources/fixtures/redirection/invalid_entry');
    }

    public function testRedirections()
    {
        $redirection = $this->getContainer()->get('coral.redirection');

        $this->assertFalse($redirection->hasRedirect('http://www.example.org'));
        $this->assertEquals(null, $redirection->getRedirect('http://www.example.org'));

        $this->assertTrue($redirection->hasRedirect('/to-redirect'));
        $this->assertEquals(array('/en', 301), $redirection->getRedirect('/to-redirect'));

        $this->assertTrue($redirection->hasRedirect('/old-section/some/document'));
        $this->assertEquals(array('/new-section/some/document', 302), $redirection->getRedirect('/old-section/some/document'));

        $this->assertTrue($redirection->hasRedirect('/wildcard/something-here'));
        $this->assertEquals(array('/en/wildcard/something-here', 301), $redirection->getRedirect('/wildcard/something-here'));
    }
}