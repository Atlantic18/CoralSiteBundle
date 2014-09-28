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

use Coral\CoreBundle\Test\WebTestCase;
use Coral\SiteBundle\Service\Redirection;

class RedirectionTest extends WebTestCase
{
    /**
     * @expectedException Coral\SiteBundle\Exception\ConfigurationException
     */
    public function testException()
    {
        new Redirection('invalid_path');
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
    }
}