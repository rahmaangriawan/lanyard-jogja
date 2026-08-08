<?php

declare(strict_types=1);

function resizeWebp(string $source, string $target, int $targetWidth, int $quality = 78): void
{
    [$width, $height] = getimagesize($source) ?: throw new RuntimeException("Cannot read {$source}");
    $targetHeight = (int) round($height * ($targetWidth / $width));
    $input = imagecreatefromwebp($source);
    $output = imagecreatetruecolor($targetWidth, $targetHeight);

    imagealphablending($output, false);
    imagesavealpha($output, true);
    imagecopyresampled($output, $input, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    if (! imagewebp($output, $target, $quality)) {
        throw new RuntimeException("Cannot write {$target}");
    }

    imagedestroy($input);
    imagedestroy($output);
}

$public = dirname(__DIR__).DIRECTORY_SEPARATOR.'public';

$variants = [
    ['/uploads/hero-lanyard-slider-01.webp', [320, 640], 76],
    ['/uploads/hero-lanyard-slider-02.webp', [320, 640], 76],
    ['/uploads/hero-lanyard-jogja.webp', [480, 768, 1024], 76],
    ['/uploads/aset-produk.webp', [320, 640], 76],
    ['/uploads/home-categories/lanyard-id-card-custom.webp', [320, 480, 640], 76],
    ['/uploads/home-categories/cetak-lanyard-warna.webp', [320, 480, 640], 76],
    ['/uploads/home-categories/lanyard-event.webp', [320, 480, 640], 76],
    ['/uploads/home-advantages/produksi-kilat.webp', [96, 128, 192], 74],
    ['/uploads/home-advantages/cetak-full-color.webp', [96, 128, 192], 74],
    ['/uploads/home-advantages/gratis-ongkir.webp', [96, 128, 192], 74],
    ['/uploads/home-advantages/garansi-kualitas.webp', [96, 128, 192], 74],
];

foreach ($variants as [$relativeSource, $widths, $quality]) {
    $source = $public.$relativeSource;
    if (! is_file($source)) {
        continue;
    }

    $extension = pathinfo($source, PATHINFO_EXTENSION);
    $base = substr($source, 0, -strlen($extension) - 1);
    foreach ($widths as $width) {
        resizeWebp($source, "{$base}-{$width}w.webp", $width, $quality);
    }
}

foreach (range(1, 5) as $index) {
    foreach ([192, 384] as $width) {
        $source = $public."/images/trust/trust-{$index}.webp";
        if (! is_file($source)) {
            continue;
        }

        resizeWebp(
            $source,
            $public."/images/trust/trust-{$index}-{$width}w.webp",
            $width,
            74,
        );
    }
}
