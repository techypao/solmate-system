<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class NewsArticleMetadataService
{
    public function normalizeUrl(string $url): string
    {
        $trimmedUrl = trim($url);
        $parts = parse_url($trimmedUrl);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return $trimmedUrl;
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';

        if ($path !== '/' && $path !== '') {
            $path = rtrim($path, '/');
        }

        $normalizedUrl = $scheme . '://' . $host;

        if (isset($parts['port'])) {
            $normalizedUrl .= ':' . $parts['port'];
        }

        $normalizedUrl .= $path;

        if (! empty($parts['query'])) {
            $normalizedUrl .= '?' . $parts['query'];
        }

        return $normalizedUrl;
    }

    public function extractSourceName(string $url): string
    {
        $host = (string) parse_url($url, PHP_URL_HOST);
        $host = preg_replace('/^www\./i', '', $host) ?: $host;

        return $host !== '' ? $host : 'Unknown source';
    }

    public function fetch(string $url): array
    {
        $normalizedUrl = $this->normalizeUrl($url);
        $sourceName = $this->extractSourceName($normalizedUrl);
        $fallbackTitle = $sourceName !== 'Unknown source' ? $sourceName : $normalizedUrl;

        $fallback = [
            'title' => $fallbackTitle,
            'description' => null,
            'thumbnail_url' => null,
            'source_name' => $sourceName,
        ];

        try {
            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->withHeaders([
                    'Accept' => 'text/html,application/xhtml+xml',
                    'User-Agent' => 'SolMate News Bot/1.0',
                ])
                ->get($normalizedUrl);

            if (! $response->successful()) {
                return $fallback;
            }

            $body = (string) $response->body();

            if ($body === '') {
                return $fallback;
            }

            $parsed = $this->parseHtml($body, $normalizedUrl);

            return [
                'title' => $parsed['title'] ?: $fallback['title'],
                'description' => $parsed['description'],
                'thumbnail_url' => $parsed['thumbnail_url'],
                'source_name' => $sourceName,
            ];
        } catch (Throwable) {
            return $fallback;
        }
    }

    private function parseHtml(string $html, string $baseUrl): array
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET);
        libxml_clear_errors();

        if (! $loaded) {
            return [
                'title' => $this->extractTitleWithRegex($html),
                'description' => null,
                'thumbnail_url' => null,
            ];
        }

        $meta = [];

        foreach ($dom->getElementsByTagName('meta') as $metaTag) {
            $content = $this->cleanText($metaTag->getAttribute('content'));

            if ($content === null) {
                continue;
            }

            foreach (['property', 'name'] as $attribute) {
                $key = strtolower(trim((string) $metaTag->getAttribute($attribute)));

                if ($key !== '' && ! array_key_exists($key, $meta)) {
                    $meta[$key] = $content;
                }
            }
        }

        $titleNode = $dom->getElementsByTagName('title')->item(0);
        $titleTag = $titleNode ? $this->cleanText($titleNode->textContent) : null;

        $title = $this->firstNonEmpty([
            $meta['og:title'] ?? null,
            $meta['twitter:title'] ?? null,
            $titleTag,
            $this->extractTitleWithRegex($html),
        ]);

        $description = $this->firstNonEmpty([
            $meta['og:description'] ?? null,
            $meta['twitter:description'] ?? null,
            $meta['description'] ?? null,
        ]);

        $thumbnailUrl = $this->firstNonEmpty([
            $meta['og:image'] ?? null,
            $meta['twitter:image'] ?? null,
            $meta['twitter:image:src'] ?? null,
        ]);

        return [
            'title' => $title,
            'description' => $description,
            'thumbnail_url' => $this->resolveUrl($baseUrl, $thumbnailUrl),
        ];
    }

    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            $cleaned = $this->cleanText($value);

            if ($cleaned !== null) {
                return $cleaned;
            }
        }

        return null;
    }

    private function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace('/\s+/u', ' ', trim($decoded));

        return $normalized !== null && $normalized !== '' ? $normalized : null;
    }

    private function extractTitleWithRegex(string $html): ?string
    {
        if (! preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return null;
        }

        return $this->cleanText(strip_tags($matches[1]));
    }

    private function resolveUrl(string $baseUrl, ?string $candidate): ?string
    {
        $candidate = $this->cleanText($candidate);

        if ($candidate === null) {
            return null;
        }

        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $candidate)) {
            return $candidate;
        }

        $baseParts = parse_url($baseUrl);

        if ($baseParts === false || ! isset($baseParts['scheme'], $baseParts['host'])) {
            return $candidate;
        }

        $origin = $baseParts['scheme'] . '://' . $baseParts['host'];

        if (isset($baseParts['port'])) {
            $origin .= ':' . $baseParts['port'];
        }

        if (str_starts_with($candidate, '//')) {
            return $baseParts['scheme'] . ':' . $candidate;
        }

        if (str_starts_with($candidate, '/')) {
            return $origin . $candidate;
        }

        $basePath = $baseParts['path'] ?? '/';
        $baseDirectory = rtrim(str_replace('\\', '/', dirname($basePath)), '/');
        $baseDirectory = $baseDirectory === '.' ? '' : $baseDirectory;

        return $origin . ($baseDirectory !== '' ? '/' . ltrim($baseDirectory, '/') : '') . '/' . ltrim($candidate, '/');
    }
}