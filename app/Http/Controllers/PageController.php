<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PageController extends Controller
{
    private const PAGES = ['home', 'pricing', 'how-it-works', 'faq', 'privacy', 'terms', 'contact', 'use-cases', 'about'];

    public function show(string $page = 'home')
    {
        abort_unless(in_array($page, self::PAGES, true), 404);

        return view('pages.'.$page);
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nAllow: /\nDisallow: /image-jobs\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $urls = collect(self::PAGES)->map(fn ($p) => $p === 'home' ? url('/') : url($p));
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$urls->map(fn ($u) => '<url><loc>'.e($u).'</loc></url>')->implode('').'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
