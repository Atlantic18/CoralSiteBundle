<?php

/*
 * This file is part of the Coral package.
 *
 * (c) Frantisek Troster <frantisek.troster@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coral\SiteBundle\Tests\Twig;

use Coral\CoreBundle\Test\WebTestCase;

class PathExtensionTest extends WebTestCase
{
    private function createRequestStack($scriptName)
    {
        $stack   = new \Symfony\Component\HttpFoundation\RequestStack;
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/some_path',
            'GET',
            array(),
            array(),
            array(),
            array('SCRIPT_NAME' => $scriptName)
        );
        $stack->push($request);
        $this->getContainer()->set('request_stack', $stack);
    }

    public function testPathWithScriptName()
    {
        $this->createRequestStack('/app_dev.php');
        $extension = $this->getContainer()->get('coral.twig.path_extension');

        $this->assertEquals('/app_dev.php/foo/bar', $extension->path('/foo/bar'));
    }

    public function testPath()
    {
        $this->createRequestStack('');
        $extension = $this->getContainer()->get('coral.twig.path_extension');

        $this->assertEquals('/foo/bar', $extension->path('/foo/bar'));
    }
}