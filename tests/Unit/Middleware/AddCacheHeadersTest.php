<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AddCacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class AddCacheHeadersTest extends TestCase
{
    public function test_adds_cache_headers_for_all_types(): void
    {
        $types = [
            'api' => ['private', 'max-age=60', 'must-revalidate', 'ETag'],
            'static' => ['public', 'max-age=31536000', 'immutable'],
            'public-short' => ['public', 'max-age=300', 's-maxage=300'],
            'public-medium' => ['public', 'max-age=3600', 's-maxage=3600'],
            'private' => ['private', 'max-age=300', 'must-revalidate'],
            'no-cache' => ['no-store', 'no-cache', 'must-revalidate', 'max-age=0'],
            'default' => ['private', 'max-age=60'],
        ];

        foreach ($types as $type => $expectedStrings) {
            $middleware = new AddCacheHeaders();
            $request = Request::create('/test-cache-' . $type, 'GET');

            $response = $middleware->handle($request, function ($req) {
                return new Response('test content');
            }, $type);

            $this->assertTrue($response->headers->has('Cache-Control'), "Missing Cache-Control for type: $type");
            $cacheControl = $response->headers->get('Cache-Control');
            foreach ($expectedStrings as $expected) {
                if ($expected === 'ETag') {
                    $this->assertTrue($response->headers->has('ETag'), "Missing ETag for type: $type");
                } else {
                    $this->assertStringContainsString($expected, $cacheControl, "Type $type missing $expected in Cache-Control");
                }
            }
        }
    }

    public function test_handles_request_without_cache_type(): void
    {
        $middleware = new AddCacheHeaders();
        $request = Request::create('/any-route', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('test content');
        });

        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_passes_request_through_middleware(): void
    {
        $middleware = new AddCacheHeaders();
        $request = Request::create('/test', 'GET');
        $expectedContent = 'middleware test';

        $response = $middleware->handle($request, function ($req) use ($expectedContent) {
            return new Response($expectedContent);
        });

        $this->assertEquals($expectedContent, $response->getContent());
    }
}
