<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AddCacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class AddCacheHeadersTest extends TestCase
{
    public function test_adds_default_headers(): void
    {
        $middleware = new AddCacheHeaders();
        $request = Request::create('/foo', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('content');
        });

        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=60', $response->headers->get('Cache-Control'));
    }

    public function test_adds_no_cache_headers_on_error(): void
    {
        $middleware = new AddCacheHeaders();
        $request = Request::create('/foo', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('error', 500);
        });

        $header = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $header);
        $this->assertStringContainsString('no-cache', $header);
        $this->assertStringContainsString('must-revalidate', $header);
        $this->assertStringContainsString('max-age=0', $header);
    }

    public function test_adds_public_headers(): void
    {
        $middleware = new AddCacheHeaders();
        $request = Request::create('/foo', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('content');
        }, 'public-medium');

        $header = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $header);
        $this->assertStringContainsString('max-age=3600', $header);
        $this->assertStringContainsString('s-maxage=3600', $header);
    }

    public function test_api_headers_with_etag(): void
    {
        $middleware = new AddCacheHeaders();
        $request = Request::create('/api/foo', 'GET');
        $content = 'api content';

        $response = $middleware->handle($request, function ($req) use ($content) {
            return new Response($content);
        }, 'api');

        $header = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $header);
        $this->assertStringContainsString('max-age=60', $header);
        $this->assertStringContainsString('must-revalidate', $header);
        $this->assertEquals(md5($content), $response->headers->get('ETag'));
    }

    public function test_api_returns_304_when_etag_matches(): void
    {
        $middleware = new AddCacheHeaders();
        $content = 'api content';
        $etag = md5($content);

        $request = Request::create('/api/foo', 'GET');
        $request->headers->set('If-None-Match', $etag);

        $response = $middleware->handle($request, function ($req) use ($content) {
            return new Response($content);
        }, 'api');

        $this->assertEquals(304, $response->getStatusCode());
    }
}
