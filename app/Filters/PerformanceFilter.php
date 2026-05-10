<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PerformanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $uri = (string) $request->getUri();

        if (str_ends_with($uri, '.css') || str_ends_with($uri, '.js')) {
            $response->setHeader('Cache-Control', 'public, max-age=31536000, immutable');
            $response->setHeader('Vary', 'Accept-Encoding');
        }

        if (str_ends_with($uri, '.woff2')) {
            $response->setHeader('Cache-Control', 'public, max-age=31536000, immutable');
        }

        if (preg_match('#/media/photo#', $uri)) {
            $response->setHeader('Cache-Control', 'private, max-age=3600');
        }

        if (str_ends_with($uri, '.svg') || str_ends_with($uri, '.png') || str_ends_with($uri, '.jpg')) {
            $response->setHeader('Cache-Control', 'public, max-age=86400');
        }
    }
}
