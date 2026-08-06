@php
  use App\Models\Post;

  $posts = \App\Support\HomePageCache::remember('home_posts', function () {
    return Post::with('category')
      ->where('published', true)
      ->orderBy('createdAt', 'desc')
      ->take(4)
      ->get();
  });

  $asset = fn ($source) => \App\Support\AssetUrl::relative($source) ?: '/uploads/hero-lanyard-jogja.webp';
  $formatDate = function ($value): string {
    try {
      return \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
    } catch (\Throwable) {
      return '';
    }
  };
@endphp

@if ($posts->isNotEmpty())
<section class="home-blog" id="blog" aria-labelledby="home-blog-title">
  <div class="home-shell">
    <header class="home-blog__header">
      <div class="home-section-heading home-section-heading--left">
        <p>Artikel Terbaru</p>
        <h2 id="home-blog-title">Panduan Seputar Lanyard</h2>
      </div>
      <a href="{{ route('blog.index') }}">
        Lihat Semua Artikel
        <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
      </a>
    </header>

    <div class="home-blog__grid {{ $posts->count() === 2 ? 'home-blog__grid--two' : '' }}">
      @foreach ($posts as $post)
        @php
          $image = $asset($post->featuredImage ?? null);
          $excerpt = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($post->content ?? ''))));
          $category = trim((string) ($post->category->name ?? 'Artikel')) ?: 'Artikel';
        @endphp
        <article class="home-blog__card {{ $loop->first ? 'home-blog__card--featured' : 'home-blog__card--compact' }}">
          <a href="/blog/{{ $post->slug }}">
            <img
              src="{{ $image }}"
              @if ($srcset = \App\Support\ResponsiveImage::srcset($image))
                srcset="{{ $srcset }}"
                sizes="(min-width: 1024px) 360px, (min-width: 640px) 45vw, 92vw"
              @endif
              alt="{{ $post->title }}"
              width="640"
              height="400"
              loading="lazy"
              decoding="async"
            >
            <div class="home-blog__body">
              <span>{{ $category }}</span>
              <h3>{{ $post->title }}</h3>
              @if ($excerpt !== '')
                <p>{{ \Illuminate\Support\Str::limit($excerpt, 105) }}</p>
              @endif
              <small>{{ $formatDate($post->createdAt ?? $post->created_at ?? null) }}</small>
            </div>
          </a>
        </article>
      @endforeach
    </div>
  </div>
</section>
@endif
