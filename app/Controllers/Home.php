<?php

namespace App\Controllers;

use CodeIgniter\HTTP\IncomingRequest;

class Home extends BaseController
{
    public function index(): string
    {
        return view('landing/index');
    }

    public function sitemap()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>' . site_url('/') . '</loc>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
</urlset>';

        return $this->response
            ->setContentType('application/xml')
            ->setBody($xml);
    }

    public function robots(): string
    {
        $content = "User-agent: *\n" .
            "Allow: /\n" .
            "Disallow: /api/*\n" .
            "Disallow: /export/*\n" .
            "Disallow: /cron/*\n" .
            "Disallow: /writable/*\n" .
            "Disallow: /app/*\n" .
            "\n" .
            "Sitemap: " . site_url('sitemap.xml') . "\n";

        return $this->response
            ->setContentType('text/plain')
            ->setBody($content);
    }

    public function ogDefault()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
          <rect width="1200" height="630" fill="#0a84ff"/>
          <text x="600" y="300" font-family="system-ui" font-size="120" font-weight="bold" fill="white" text-anchor="middle">MANG-CV</text>
          <text x="600" y="380" font-family="system-ui" font-size="36" fill="rgba(255,255,255,0.8)" text-anchor="middle">Buat CV Profesional Gratis</text>
        </svg>';

        return $this->response
            ->setContentType('image/svg+xml')
            ->setBody($svg);
    }

    public function test(): string
    {
        return view('test/index');
    }
}
