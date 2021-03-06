<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\GuzzleHttp;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\StorageAdapterInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class MiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheMiddleware()
    {
        $response = new Response(204);
        $mocks = array_fill(0, 2, $response);
        $mock = new MockHandler($mocks);
        $handler = HandlerStack::create($mock);

        $adapter = $this->getMock(StorageAdapterInterface::class);
        $adapter
            ->expects($this->at(0))
            ->method('fetch')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn(false)
        ;
        $adapter
            ->expects($this->at(1))
            ->method('save')
            ->with(
                $this->isInstanceOf(RequestInterface::class),
                $this->equalTo($response)
            )
        ;
        $adapter
            ->expects($this->at(2))
            ->method('fetch')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn($response)
        ;

        $handler->push(Middleware::cache($adapter));

        $client = new Client(['handler' => $handler]);

        $client->get('http://foo.bar');

        $client->get('http://foo.bar');
    }
}
