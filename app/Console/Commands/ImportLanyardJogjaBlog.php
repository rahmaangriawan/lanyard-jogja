<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Support\AuthorProfile;
use App\Support\HtmlSanitizer;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Import the complete public lanyardjogja.com blog into the local CMS.
 *
 * Discovery combines the public archive, XML sitemaps and a complete
 * WordPress REST response when one is exposed. Every file is validated and
 * staged before the active blog media directory or database is changed.
 */
class ImportLanyardJogjaBlog extends Command
{
    protected $signature = 'blog:import-lanyardjogja
        {--dry-run : Fetch and validate without changing database or active files}
        {--apply : Replace local blog posts and activate downloaded media}
        {--insecure : Disable TLS verification for this local import run}
        {--limit= : Limit articles for a dry-run smoke check}';

    protected $description = 'Import all public lanyardjogja.com blog articles and images locally.';

    private const BASE_URL = 'https://lanyardjogja.com';
    private const IMPORT_DIRECTORY = 'uploads/blog-import';
    private const BACKUP_DIRECTORY = 'blog-import/backups';
    private const MAX_ARCHIVE_PAGES = 100;

    private bool $allowInsecure = false;
    private int $archivePageCount = 0;
    private int $imageSuccessCount = 0;
    private array $categoryNames = [];
    private array $discoveryErrors = [];
    private array $imageManifest = [];
    private array $sourceManifest = [];

    public function handle(): int
    {
        $this->allowInsecure = (bool) $this->option('insecure');
        $this->imageManifest = [];
        $this->sourceManifest = [];
        $this->categoryNames = [];
        $this->discoveryErrors = [];
        $this->imageSuccessCount = 0;
        $this->archivePageCount = 0;
        $apply = (bool) $this->option('apply');
        $dryRun = (bool) $this->option('dry-run') || ! $apply;
        $limit = $this->parseLimit();

        if ($apply && (bool) $this->option('dry-run')) {
            $this->error('--apply dan --dry-run tidak boleh dipakai bersamaan.');

            return self::FAILURE;
        }

        if ($limit !== null && $apply) {
            $this->error('--limit hanya boleh dipakai bersama --dry-run.');

            return self::FAILURE;
        }

        if ($apply && ! AuthorProfile::primaryAuthor()) {
            $this->error('Import dibatalkan karena belum ada akun ADMIN sebagai penulis blog.');

            return self::FAILURE;
        }

        $runId = now()->format('Ymd-His').'-'.Str::lower(Str::random(8));
        $stagingDirectory = self::IMPORT_DIRECTORY.'-staging-'.$runId;
        $prepared = [];

        try {
            $inventory = $this->collectInventory();
            if ($limit !== null) {
                $inventory = array_slice($inventory, 0, $limit);
            }
            if ($inventory === []) {
                $detail = $this->discoveryErrors !== []
                    ? ' Detail discovery: '.implode(' | ', $this->discoveryErrors)
                    : '';
                throw new RuntimeException('Tidak ada artikel valid yang ditemukan di archive, sitemap, atau API.'.$detail);
            }

            $knownSlugs = array_fill_keys(array_column($inventory, 'slug'), true);
            foreach ($inventory as $source) {
                $prepared[] = $this->prepareArticle($source, $knownSlugs, $stagingDirectory, $apply);
                $this->line('Valid: '.$source['title']);
            }
            $this->validatePreparedArticles($prepared);

            if ($dryRun) {
                $this->renderSummary($prepared, false, null);
                $this->info('Dry run selesai. Database dan filesystem aktif tidak diubah.');

                return self::SUCCESS;
            }

            $backupDirectory = self::BACKUP_DIRECTORY.'/'.$runId;
            $this->createBackup($backupDirectory, $prepared);
            $this->activateStaging($stagingDirectory, $backupDirectory);

            try {
                $author = AuthorProfile::primaryAuthor();
                if (! $author) {
                    throw new RuntimeException('Akun ADMIN utama tidak tersedia saat persist.');
                }
                $this->persistReplacement($prepared, $author->id);
            } catch (Throwable $exception) {
                $this->restoreActiveDirectory($backupDirectory);
                Storage::disk('public')->deleteDirectory($stagingDirectory);
                throw $exception;
            }

            Storage::disk('public')->deleteDirectory($stagingDirectory);
            $this->clearPublicCaches();
            $this->renderSummary($prepared, true, $backupDirectory);
            $this->info('Import Lanyard Jogja selesai. Backup disimpan di storage/app/private/'.$backupDirectory.'.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Storage::disk('public')->deleteDirectory($stagingDirectory);
            $this->error('Import dibatalkan; data lama dipertahankan bila proses belum commit. '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    private function parseLimit(): ?int
    {
        $value = $this->option('limit');
        if ($value === null || $value === false || $value === '') {
            return null;
        }

        $limit = (int) $value;

        return $limit > 0 ? $limit : null;
    }

    /** @return list<array<string, mixed>> */
    private function collectInventory(): array
    {
        $sources = [];
        $api = $this->collectApiInventory();
        if ($api !== []) {
            $sources = array_merge($sources, $api);
        }

        try {
            $sources = array_merge($sources, $this->collectSitemapInventory());
        } catch (Throwable $exception) {
            $this->discoveryErrors[] = 'Sitemap: '.$exception->getMessage();
            $this->warn('Sitemap tidak dapat dibaca: '.$exception->getMessage());
        }

        try {
            $sources = array_merge($sources, $this->collectArchiveInventory());
        } catch (Throwable $exception) {
            $this->discoveryErrors[] = 'Archive: '.$exception->getMessage();
            $this->warn('Archive tidak dapat dibaca: '.$exception->getMessage());
        }

        $unique = [];
        foreach ($sources as $source) {
            $slug = Str::slug((string) ($source['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $source['slug'] = $slug;
            $source['url'] = $this->canonicalArticleUrl((string) ($source['url'] ?? ''), $slug);
            if (! isset($unique[$slug])) {
                $unique[$slug] = $source + [
                    'title' => '',
                    'featuredUrl' => null,
                    'categoryName' => '',
                    'categorySlug' => '',
                    'createdAt' => null,
                    'updatedAt' => null,
                    'tags' => '',
                    'content' => '',
                    'excerpt' => '',
                ];
                continue;
            }

            foreach (['title', 'featuredUrl', 'categoryName', 'categorySlug', 'createdAt', 'updatedAt', 'tags', 'content', 'excerpt'] as $field) {
                if (($unique[$slug][$field] ?? null) === null || ($unique[$slug][$field] ?? '') === '') {
                    $unique[$slug][$field] = $source[$field] ?? $unique[$slug][$field];
                }
            }
        }

        $inventory = array_values($unique);
        usort($inventory, function (array $left, array $right): int {
            return strcmp((string) ($right['createdAt'] ?? ''), (string) ($left['createdAt'] ?? ''));
        });

        if ($inventory !== []) {
            $this->sourceManifest = array_map(fn (array $item): array => [
                'slug' => $item['slug'],
                'url' => $item['url'],
                'title' => $item['title'],
            ], $inventory);
        }

        return $inventory;
    }

    /** @return list<array<string, mixed>> */
    private function collectApiInventory(): array
    {
        $endpoint = self::BASE_URL.'/wp-json/wp/v2/posts';
        try {
            $first = $this->get($endpoint, [
                'per_page' => 100,
                'page' => 1,
                'orderby' => 'date',
                'order' => 'desc',
                '_fields' => 'date,modified,slug,link,title,content,excerpt,jetpack_featured_media_url',
            ]);
            $payload = $first->json();
            if (! is_array($payload) || ! array_is_list($payload) || $first->header('X-WP-Total') === null) {
                return [];
            }

            $total = (int) $first->header('X-WP-Total');
            $pages = max(1, (int) ($first->header('X-WP-TotalPages') ?: 1));
            $all = $payload;
            for ($page = 2; $page <= $pages; $page++) {
                $response = $this->get($endpoint, [
                    'per_page' => 100,
                    'page' => $page,
                    'orderby' => 'date',
                    'order' => 'desc',
                    '_fields' => 'date,modified,slug,link,title,content,excerpt,jetpack_featured_media_url',
                ]);
                $next = $response->json();
                if (! is_array($next) || ! array_is_list($next)) {
                    return [];
                }
                $all = array_merge($all, $next);
            }
            if ($total < 1 || count($all) !== $total) {
                $this->warn('WordPress API tidak lengkap; discovery dilanjutkan melalui archive dan sitemap.');

                return [];
            }

            return array_values(array_map(function (array $post): array {
                $slug = Str::slug((string) ($post['slug'] ?? ''));

                return [
                    'slug' => $slug,
                    'url' => (string) ($post['link'] ?? ''),
                    'title' => $this->decodeText((string) data_get($post, 'title.rendered', '')),
                    'content' => (string) data_get($post, 'content.rendered', ''),
                    'excerpt' => $this->decodeText((string) data_get($post, 'excerpt.rendered', '')),
                    'featuredUrl' => $this->absoluteUrl((string) ($post['jetpack_featured_media_url'] ?? '')),
                    'createdAt' => $this->parseDate((string) ($post['date'] ?? '')),
                    'updatedAt' => $this->parseDate((string) ($post['modified'] ?? '')),
                    'categoryName' => '',
                    'categorySlug' => '',
                    'tags' => '',
                ];
            }, $all));
        } catch (Throwable $exception) {
            $this->discoveryErrors[] = 'API: '.$exception->getMessage();
            $this->warn('WordPress API gagal; memakai HTML archive/sitemap. '.$exception->getMessage());

            return [];
        }
    }

    /** @return list<array<string, mixed>> */
    private function collectSitemapInventory(): array
    {
        $candidates = ['/sitemap.xml', '/sitemap_index.xml', '/post-sitemap.xml', '/sitemap-posts.xml'];
        $urls = [];
        foreach ($candidates as $path) {
            try {
                $xml = $this->get(self::BASE_URL.$path)->body();
                foreach ($this->xmlLocs($xml) as $loc) {
                    if (str_ends_with(strtolower($loc), '.xml')) {
                        try {
                            $xml = $this->get($loc)->body();
                            $urls = array_merge($urls, $this->xmlLocs($xml));
                        } catch (Throwable $exception) {
                            $this->discoveryErrors[] = $loc.': '.$exception->getMessage();
                            continue;
                        }
                    } else {
                        $urls[] = $loc;
                    }
                }
            } catch (Throwable $exception) {
                $this->discoveryErrors[] = $path.': '.$exception->getMessage();
                continue;
            }
        }

        $result = [];
        foreach (array_unique($urls) as $url) {
            $slug = $this->blogSlugFromUrl($url);
            if ($slug !== null) {
                $result[] = ['slug' => $slug, 'url' => $url];
            }
        }

        return $result;
    }

    /** @return list<array<string, mixed>> */
    private function collectArchiveInventory(): array
    {
        $result = [];
        $seen = [];
        $this->archivePageCount = 0;

        for ($page = 1; $page <= self::MAX_ARCHIVE_PAGES; $page++) {
            $url = self::BASE_URL.'/blog'.($page > 1 ? '?page='.$page : '');
            try {
                $html = $this->get($url)->body();
            } catch (Throwable $exception) {
                if ($page === 1) {
                    throw $exception;
                }
                break;
            }

            $document = $this->document($html);
            $xpath = new DOMXPath($document);
            $foundOnPage = 0;
            foreach ($xpath->query('//main//a[@href] | //article//a[@href]') ?: [] as $anchor) {
                if (! $anchor instanceof DOMElement) {
                    continue;
                }
                $href = $this->absoluteUrl($anchor->getAttribute('href'));
                $slug = $href ? $this->blogSlugFromUrl($href) : null;
                if ($slug === null || isset($seen[$slug])) {
                    continue;
                }
                $seen[$slug] = true;
                $titleNode = $this->firstElement($xpath, './/h1|.//h2|.//h3|.//h4', $anchor->parentNode ?: $anchor);
                $result[] = [
                    'slug' => $slug,
                    'url' => $href,
                    'title' => $this->decodeText($titleNode?->textContent ?? $anchor->textContent),
                ];
                $foundOnPage++;
            }
            $this->archivePageCount++;

            $hasNext = (bool) ($xpath->query('//a[@rel="next"] | //a[contains(@href, "page='.($page + 1).'")]')->length ?? 0);
            if ($foundOnPage === 0 && ! $hasNext) {
                break;
            }
        }

        return $result;
    }

    /** @return list<string> */
    private function xmlLocs(string $xml): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (! $loaded) {
            return [];
        }
        $xpath = new DOMXPath($document);

        return array_values(array_filter(array_map(
            fn (DOMNode $node): string => trim((string) $node->textContent),
            iterator_to_array($xpath->query('//*[local-name()="loc"]') ?: []),
        )));
    }

    /** @param array<string, mixed> $source @param array<string, bool> $knownSlugs */
    private function prepareArticle(array $source, array $knownSlugs, string $stagingDirectory, bool $writeFiles): array
    {
        $pageHtml = $this->get((string) $source['url'])->body();
        $pageDocument = $this->document($pageHtml);
        $pageXPath = new DOMXPath($pageDocument);
        $content = trim((string) ($source['content'] ?? ''));
        if ($content === '') {
            $root = $this->articleRoot($pageXPath, $pageDocument);
            $content = $root ? $this->innerHtml($root) : '';
        }
        if ($content === '') {
            throw new RuntimeException('Area isi artikel tidak ditemukan: '.$source['slug']);
        }

        $content = $this->cleanArticleHtml($content);
        $content = $this->rewriteArticleLinks($content, $knownSlugs);
        $downloads = [];
        foreach ($this->extractImageUrls($content) as $index => $remoteUrl) {
            $downloads[$remoteUrl] = $this->downloadImage(
                $remoteUrl,
                $source['slug'].'-inline-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                $stagingDirectory,
                $writeFiles,
            );
        }

        $featuredUrl = $this->absoluteUrl((string) ($source['featuredUrl'] ?? ''))
            ?? $this->metaValue($pageXPath, 'og:image')
            ?? $this->firstImageUrl($pageXPath);
        $featuredImage = null;
        if ($featuredUrl !== null) {
            $downloads[$featuredUrl] ??= $this->downloadImage(
                $featuredUrl,
                $source['slug'].'-featured',
                $stagingDirectory,
                $writeFiles,
            );
            $featuredImage = $downloads[$featuredUrl]['url'];
        }

        foreach ($downloads as $remoteUrl => $download) {
            $content = str_replace(
                [$remoteUrl, htmlspecialchars($remoteUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8')],
                $download['url'],
                $content,
            );
        }
        $content = HtmlSanitizer::clean($this->normalizeImageAttributes($content));
        if (trim(strip_tags($content)) === '' || $this->containsRemoteImage($content)) {
            throw new RuntimeException('Konten hasil import tidak valid: '.$source['slug']);
        }

        $title = $this->decodeText((string) ($source['title'] ?? ''))
            ?: $this->decodeText($this->firstElement($pageXPath, '//main//h1|//article//h1|//h1', $pageDocument)?->textContent ?? '');
        if ($title === '') {
            $title = Str::headline(str_replace('-', ' ', (string) $source['slug']));
        }
        $categoryName = $this->decodeText((string) ($source['categoryName'] ?? ''))
            ?: $this->decodeText($this->firstElement($pageXPath, '//a[contains(@href, "/category/") or contains(@href, "/kategori/")]', $pageDocument)?->textContent ?? '')
            ?: 'Artikel';
        $categorySlug = Str::slug((string) ($source['categorySlug'] ?? '')) ?: Str::slug($categoryName);
        $this->categoryNames[$categorySlug] = $categoryName;
        $description = $this->decodeText((string) ($source['excerpt'] ?? ''))
            ?: $this->decodeText((string) ($this->metaValue($pageXPath, 'description') ?? ''));
        $description = Str::limit($description !== '' ? $description : strip_tags($content), 155, '');

        return [
            'title' => $title,
            'slug' => $source['slug'],
            'content' => $content,
            'featuredImage' => $featuredImage,
            'createdAt' => $source['createdAt'] instanceof Carbon ? $source['createdAt'] : ($this->parseDate((string) ($this->metaValue($pageXPath, 'article:published_time') ?? '')) ?? now()),
            'updatedAt' => $source['updatedAt'] instanceof Carbon ? $source['updatedAt'] : ($this->parseDate((string) ($this->metaValue($pageXPath, 'article:modified_time') ?? '')) ?? now()),
            'metaTitle' => $this->decodeText((string) ($this->metaValue($pageXPath, 'og:title') ?? '')) ?: $title,
            'metaDescription' => $description,
            'tags' => (string) ($source['tags'] ?? ''),
            'categoryName' => $categoryName,
            'categorySlug' => $categorySlug,
            'media' => array_values($downloads),
        ];
    }

    private function articleRoot(DOMXPath $xpath, DOMNode $context): ?DOMElement
    {
        foreach ([
            '//main//div[contains(concat(" ", normalize-space(@class), " "), " prose ")]',
            '//main//*[contains(concat(" ", normalize-space(@class), " "), " entry-content ")]',
            '//main//*[contains(concat(" ", normalize-space(@class), " "), " post-content ")]',
            '//main//article',
            '//article',
        ] as $query) {
            $element = $this->firstElement($xpath, $query, $context);
            if ($element && trim(strip_tags($this->innerHtml($element))) !== '') {
                return $element;
            }
        }

        return null;
    }

    private function cleanArticleHtml(string $html): string
    {
        $document = $this->document($html);
        $xpath = new DOMXPath($document);
        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }
        foreach (['script', 'style', 'form', 'iframe', 'noscript', 'svg', 'nav', 'aside', 'header', 'footer'] as $tag) {
            $this->removeNodes($xpath->query('.//'.$tag, $body));
        }
        foreach (iterator_to_array($xpath->query('.//*', $body) ?: []) as $element) {
            if (! $element instanceof DOMElement) {
                continue;
            }
            $marker = strtolower($element->getAttribute('class').' '.$element->getAttribute('id'));
            if (preg_match('/(?:^|[\s_-])(toc|table-of-contents|breadcrumb|sidebar|related|share|author|comment|pagination|newsletter|cta)(?:[\s_-]|$)/', $marker)) {
                $element->parentNode?->removeChild($element);
            }
        }

        return $this->innerHtml($body);
    }

    /** @return list<string> */
    private function extractImageUrls(string &$html): array
    {
        $document = $this->document($html);
        $xpath = new DOMXPath($document);
        $body = $document->getElementsByTagName('body')->item(0);
        $urls = [];
        if (! $body) {
            return [];
        }
        foreach ($xpath->query('.//img', $body) ?: [] as $image) {
            if (! $image instanceof DOMElement) {
                continue;
            }
            $source = $image->getAttribute('data-src')
                ?: $image->getAttribute('data-lazy-src')
                ?: $image->getAttribute('data-original')
                ?: $image->getAttribute('src')
                ?: $this->firstSrcsetCandidate($image->getAttribute('srcset'));
            $absolute = $this->absoluteUrl($source);
            if ($absolute === null) {
                throw new RuntimeException('Gambar artikel tidak memiliki URL HTTP.');
            }
            $image->setAttribute('src', $absolute);
            $urls[] = $absolute;
        }
        $html = $this->innerHtml($body);

        return array_values(array_unique($urls));
    }

    /** @param array<string, bool> $knownSlugs */
    private function rewriteArticleLinks(string $html, array $knownSlugs): string
    {
        $document = $this->document($html);
        $xpath = new DOMXPath($document);
        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body) {
            return $html;
        }
        foreach ($xpath->query('.//a[@href]', $body) ?: [] as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }
            $href = html_entity_decode($anchor->getAttribute('href'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $host = strtolower((string) parse_url($href, PHP_URL_HOST));
            if ($host !== '' && ! in_array($host, ['lanyardjogja.com', 'www.lanyardjogja.com'], true)) {
                continue;
            }
            $path = trim((string) parse_url($href, PHP_URL_PATH), '/');
            $segments = array_values(array_filter(explode('/', $path)));
            $slug = Str::slug((string) end($segments));
            $fragment = parse_url($href, PHP_URL_FRAGMENT);
            if (isset($knownSlugs[$slug])) {
                $anchor->setAttribute('href', '/blog/'.$slug.($fragment ? '#'.$fragment : ''));
            } elseif (str_starts_with($path, 'category/') || str_starts_with($path, 'kategori/')) {
                $anchor->setAttribute('href', '/kategori/'.$slug);
            } elseif ($path === 'blog' || $path === '') {
                $anchor->setAttribute('href', '/blog');
            }
        }

        return $this->innerHtml($body);
    }

    private function normalizeImageAttributes(string $html): string
    {
        $document = $this->document($html);
        $xpath = new DOMXPath($document);
        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body) {
            return $html;
        }
        foreach ($xpath->query('.//img', $body) ?: [] as $image) {
            if (! $image instanceof DOMElement) {
                continue;
            }
            foreach (['srcset', 'sizes', 'data-src', 'data-lazy-src', 'data-original'] as $attribute) {
                $image->removeAttribute($attribute);
            }
            $image->setAttribute('loading', 'lazy');
            $image->setAttribute('decoding', 'async');
        }

        return $this->innerHtml($body);
    }

    /** @return array{filename:string,filepath:string,mimetype:string,size:int,url:string} */
    private function downloadImage(string $url, string $basename, string $stagingDirectory, bool $writeFile): array
    {
        $response = $this->get($url);
        $body = $response->body();
        $imageInfo = @getimagesizefromstring($body);
        $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
        if (! is_array($imageInfo) || $body === '') {
            throw new RuntimeException('File gambar tidak valid: '.$url);
        }
        if (! str_starts_with($contentType, 'image/')) {
            $contentType = strtolower((string) ($imageInfo['mime'] ?? 'image/jpeg'));
        }
        if (! str_starts_with($contentType, 'image/')) {
            throw new RuntimeException('MIME gambar tidak valid: '.$url);
        }
        $extension = $this->extensionFromContentType($contentType) ?? $this->extensionFromUrl($url) ?? 'jpg';
        $filename = Str::slug($basename).'-'.substr(sha1($url), 0, 10).'.'.$extension;
        $relative = self::IMPORT_DIRECTORY.'/'.$filename;
        if ($writeFile && ! Storage::disk('public')->put($stagingDirectory.'/'.$filename, $body)) {
            throw new RuntimeException('Tidak dapat menulis gambar staging: '.$filename);
        }
        $this->imageSuccessCount++;
        $this->imageManifest[] = [
            'sourceUrl' => $url,
            'localPath' => '/storage/'.$relative,
            'sha256' => hash('sha256', $body),
            'mimetype' => $contentType,
            'size' => strlen($body),
            'status' => 'validated',
        ];

        return [
            'filename' => $filename,
            'filepath' => 'storage/'.$relative,
            'mimetype' => $contentType,
            'size' => strlen($body),
            'url' => '/storage/'.$relative,
        ];
    }

    /** @param list<array<string, mixed>> $articles */
    private function validatePreparedArticles(array $articles): void
    {
        foreach ($articles as $article) {
            if (trim(strip_tags((string) $article['content'])) === '') {
                throw new RuntimeException('Konten hasil sanitasi kosong: '.$article['slug']);
            }
            foreach ($article['media'] as $media) {
                if (! str_starts_with((string) $media['url'], '/storage/'.self::IMPORT_DIRECTORY.'/')) {
                    throw new RuntimeException('URL media lokal tidak valid: '.$article['slug']);
                }
            }
        }
    }

    private function createBackup(string $backupDirectory, array $prepared): void
    {
        $local = Storage::disk('local');
        $payload = [
            'createdAt' => now()->toIso8601String(),
            'posts' => Post::query()->get()->map(fn (Post $row) => $row->getAttributes())->all(),
            'comments' => Comment::query()->get()->map(fn (Comment $row) => $row->getAttributes())->all(),
            'media' => Media::query()->get()->map(fn (Media $row) => $row->getAttributes())->all(),
            'blogCategories' => Category::query()->where('type', 'BLOG')->get()->map(fn (Category $row) => $row->getAttributes())->all(),
            'sourceManifest' => $this->sourceManifest,
        ];
        $local->put($backupDirectory.'/database.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $local->put($backupDirectory.'/manifest.json', json_encode([
            'runId' => basename($backupDirectory),
            'source' => self::BASE_URL.'/blog',
            'articles' => $this->sourceManifest,
            'images' => $this->imageManifest,
            'preparedSlugs' => array_values(array_map(fn (array $article): string => (string) $article['slug'], $prepared)),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $public = Storage::disk('public');
        foreach ($public->allFiles(self::IMPORT_DIRECTORY) as $file) {
            $relative = ltrim(substr($file, strlen(self::IMPORT_DIRECTORY)), '/');
            $local->put($backupDirectory.'/media/'.$relative, $public->get($file));
        }
    }

    private function activateStaging(string $stagingDirectory, string $backupDirectory): void
    {
        $public = Storage::disk('public');
        try {
            $public->deleteDirectory(self::IMPORT_DIRECTORY);
            foreach ($public->allFiles($stagingDirectory) as $file) {
                $relative = ltrim(substr($file, strlen($stagingDirectory)), '/');
                if (! $public->put(self::IMPORT_DIRECTORY.'/'.$relative, $public->get($file))) {
                    throw new RuntimeException('Gagal menyalin file staging ke folder aktif.');
                }
            }
            $public->deleteDirectory($stagingDirectory);
        } catch (Throwable $exception) {
            $this->restoreActiveDirectory($backupDirectory);
            throw $exception;
        }
    }

    private function restoreActiveDirectory(string $backupDirectory): void
    {
        $local = Storage::disk('local');
        $public = Storage::disk('public');
        $public->deleteDirectory(self::IMPORT_DIRECTORY);
        $prefix = $backupDirectory.'/media';
        foreach ($local->allFiles($prefix) as $file) {
            $relative = ltrim(substr($file, strlen($prefix)), '/');
            $public->put(self::IMPORT_DIRECTORY.'/'.$relative, $local->get($file));
        }
    }

    /** @param list<array<string, mixed>> $articles */
    private function persistReplacement(array $articles, int $authorId): void
    {
        DB::transaction(function () use ($articles, $authorId): void {
            $categories = [];
            foreach ($this->categoryNames as $slug => $name) {
                $categories[$slug] = Category::updateOrCreate(
                    ['slug' => $slug, 'type' => 'BLOG'],
                    ['name' => $name, 'description' => 'Artikel Lanyard Jogja'],
                );
            }

            Comment::query()->whereIn('postId', Post::query()->select('id'))->delete();
            Post::query()->delete();
            Media::query()
                ->where('filepath', 'like', 'storage/'.self::IMPORT_DIRECTORY.'/%')
                ->orWhere('url', 'like', '/storage/'.self::IMPORT_DIRECTORY.'/%')
                ->delete();

            foreach ($articles as $article) {
                $category = $categories[$article['categorySlug']]
                    ?? Category::updateOrCreate(
                        ['slug' => $article['categorySlug'], 'type' => 'BLOG'],
                        ['name' => $article['categoryName'], 'description' => 'Artikel Lanyard Jogja'],
                    );
                Post::query()->create([
                    'title' => $article['title'],
                    'slug' => $article['slug'],
                    'content' => $article['content'],
                    'published' => true,
                    'featuredImage' => $article['featuredImage'],
                    'categoryId' => $category->id,
                    'authorId' => $authorId,
                    'tags' => $article['tags'],
                    'createdAt' => $article['createdAt'],
                    'updatedAt' => $article['updatedAt'],
                    'metaTitle' => $article['metaTitle'],
                    'metaDescription' => $article['metaDescription'],
                ]);
                foreach ($article['media'] as $media) {
                    Media::query()->updateOrCreate(['filepath' => $media['filepath']], $media);
                }
            }

            Category::query()
                ->where('type', 'BLOG')
                ->whereNotIn('slug', array_keys($this->categoryNames))
                ->whereDoesntHave('posts')
                ->whereDoesntHave('products')
                ->delete();
        });
    }

    private function clearPublicCaches(): void
    {
        $store = Cache::store(config('cache.public_store', 'file'));
        foreach (['home_posts', 'home_response_html', 'public_sitemap_posts'] as $key) {
            $store->forget($key);
        }
    }

    private function renderSummary(array $articles, bool $changed, ?string $backupDirectory): void
    {
        $this->table(['Metric', 'Value'], [
            ['Halaman archive', $this->archivePageCount],
            ['Artikel valid', count($articles)],
            ['Kategori BLOG', count($this->categoryNames)],
            ['Gambar tervalidasi', $this->imageSuccessCount],
            ['Data diubah', $changed ? 'Ya' : 'Tidak'],
            ['Backup', $backupDirectory ?? 'Tidak dibuat pada dry-run'],
        ]);
    }

    private function get(string $url, array $query = []): Response
    {
        $urls = [$url];
        if (str_starts_with($url, self::BASE_URL)) {
            $urls[] = 'https://www.lanyardjogja.com'.substr($url, strlen(self::BASE_URL));
        }
        $lastError = null;
        foreach (array_values(array_unique($urls)) as $candidate) {
            try {
                $request = Http::timeout(30)
                    ->retry(2, 350)
                    ->withHeaders([
                        'Accept' => 'application/json,text/html,image/avif,image/webp,image/*,*/*;q=0.8',
                        'User-Agent' => 'LanyardJogjaBlogImporter/1.0',
                    ]);
                if ($this->allowInsecure) {
                    $request = $request->withoutVerifying();
                }
                $response = $request->get($candidate, $query);
                if (! $response->successful()) {
                    throw new RuntimeException("HTTP {$response->status()} saat mengambil {$candidate}");
                }

                return $response;
            } catch (Throwable $exception) {
                $lastError = $exception;
            }
        }

        throw $lastError ?? new RuntimeException('Tidak dapat mengambil '.$url);
    }

    private function document(string $html): DOMDocument
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><!doctype html><html><body>'.$html.'</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $document;
    }

    private function innerHtml(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument?->saveHTML($child) ?? '';
        }

        return trim($html);
    }

    private function firstElement(DOMXPath $xpath, string $query, DOMNode $context): ?DOMElement
    {
        $node = $xpath->query($query, $context)?->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private function removeNodes(mixed $nodes): void
    {
        if (! $nodes) {
            return;
        }
        foreach (iterator_to_array($nodes) as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    private function metaValue(DOMXPath $xpath, string $name): ?string
    {
        $node = $xpath->query('//meta[@name="'.htmlspecialchars($name, ENT_QUOTES).'"] | //meta[@property="'.htmlspecialchars($name, ENT_QUOTES).'"]')?->item(0);

        return $node instanceof DOMElement ? trim((string) $node->getAttribute('content')) : null;
    }

    private function firstImageUrl(DOMXPath $xpath): ?string
    {
        $node = $xpath->query('//main//img[@src or @data-src] | //article//img[@src or @data-src]')?->item(0);
        if (! $node instanceof DOMElement) {
            return null;
        }

        return $this->absoluteUrl($node->getAttribute('data-src') ?: $node->getAttribute('src'));
    }

    private function firstSrcsetCandidate(string $srcset): string
    {
        $candidate = trim((string) (preg_split('/\s*,\s*/', $srcset)[0] ?? ''));

        return trim((string) (preg_split('/\s+/', $candidate, 2)[0] ?? ''));
    }

    private function containsRemoteImage(string $html): bool
    {
        return preg_match('/<img\b[^>]+src=["\'][^"\']*https?:\/\//i', $html) === 1;
    }

    private function absoluteUrl(string $url): ?string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($url === '' || str_starts_with($url, 'data:')) {
            return null;
        }
        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        return self::BASE_URL.'/'.ltrim($url, '/');
    }

    private function canonicalArticleUrl(string $url, string $slug): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        if ($path !== '' && $path !== 'blog') {
            return self::BASE_URL.'/'.$path;
        }

        return self::BASE_URL.'/'.$slug;
    }

    private function blogSlugFromUrl(string $url): ?string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $segments = array_values(array_filter(explode('/', $path)));
        if ($segments === []) {
            return null;
        }
        if (($segments[0] ?? '') === 'blog' && count($segments) >= 2) {
            $segments = [$segments[1]];
        } elseif (count($segments) !== 1) {
            return null;
        }
        $reserved = [
            'area', 'author', 'blog', 'cara-pemesanan', 'category', 'dashboard',
            'faq', 'garansi', 'images', 'kawruh', 'kategori', 'layanan', 'login', 'media',
            'pengiriman', 'produk', 'promo', 'register', 'robots.txt', 'sitemap',
            'storage', 'syarat-ketentuan', 'tag', 'uploads', 'page',
        ];
        if (in_array(strtolower((string) $segments[0]), $reserved, true)) {
            return null;
        }

        $slug = Str::slug((string) $segments[0]);

        return $slug !== '' ? $slug : null;
    }

    private function parseDate(string $value): ?Carbon
    {
        try {
            return $value !== '' ? Carbon::parse($value) : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function decodeText(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }

    private function extensionFromContentType(string $contentType): ?string
    {
        return match ($contentType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
            default => null,
        };
    }

    private function extensionFromUrl(string $url): ?string
    {
        $extension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true)
            ? ($extension === 'jpeg' ? 'jpg' : $extension)
            : null;
    }
}
