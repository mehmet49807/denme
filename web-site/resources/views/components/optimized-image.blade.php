@props([
    'name',
    'alt' => '',
    'width',
    'height',
    'loading' => 'lazy',
    'fetchpriority' => null,
    'sizes' => null,
    'class' => '',
    'widths' => null,
])

@php
    $version = 'opt-v6';
    $imagesDir = base_path('images');
    $widthVariants = $widths ?? match ($name) {
        'landing-community' => [384, 480, 640, 960],
        'landing-step-profile', 'landing-step-discover', 'landing-step-meet' => [320, 400, 800],
        'testimonial-ayse', 'testimonial-mehmet', 'testimonial-elif' => [56, 112],
        default => null,
    };

    $defaultSizes = match ($name) {
        'landing-community' => '(max-width: 768px) min(100vw - 2rem, 425px), 640px',
        'landing-step-profile', 'landing-step-discover', 'landing-step-meet' => '(max-width: 768px) min(100vw - 2.5rem, 320px), 400px',
        'testimonial-ayse', 'testimonial-mehmet', 'testimonial-elif' => '56px',
        default => null,
    };

    $resolvedSizes = $sizes ?? $defaultSizes;

    $buildUrl = static function (string $baseName, string $ext) use ($version): string {
        return asset("images/{$baseName}.{$ext}?v={$version}");
    };

    $webpSrcset = [];
    $jpgSrcset = [];
    $smallestVariant = null;

    if (is_array($widthVariants) && count($widthVariants) > 0) {
        $sorted = $widthVariants;
        sort($sorted);
        $maxW = max($sorted);
        $smallestVariant = min($sorted);

        foreach ($sorted as $w) {
            $suffix = ($w === $maxW) ? '' : "-{$w}";
            $base = $name.$suffix;
            $webpPath = $imagesDir.'/'.$base.'.webp';
            $jpgPath = $imagesDir.'/'.$base.'.jpg';

            if (is_file($webpPath)) {
                $webpSrcset[] = $buildUrl($base, 'webp')." {$w}w";
            }
            if (is_file($jpgPath)) {
                $jpgSrcset[] = $buildUrl($base, 'jpg')." {$w}w";
            }
        }
    }

    $resolveFallback = static function (string $baseName) use ($imagesDir, $buildUrl): string {
        if (is_file($imagesDir.'/'.$baseName.'.webp')) {
            return $buildUrl($baseName, 'webp');
        }

        return $buildUrl($baseName, 'jpg');
    };

    if ($smallestVariant !== null) {
        $maxW = max($widthVariants);
        $suffix = ($smallestVariant === $maxW) ? '' : "-{$smallestVariant}";
        $fallbackSrc = $resolveFallback($name.$suffix);
    } else {
        $fallbackSrc = $resolveFallback($name);
    }
@endphp

<picture {{ $attributes->class(['optimized-picture']) }}>
    @if($webpSrcset !== [])
        <source srcset="{{ implode(', ', $webpSrcset) }}" type="image/webp" @if($resolvedSizes) sizes="{{ $resolvedSizes }}" @endif>
    @else
        <source srcset="{{ $resolveFallback($name) }}" type="image/webp" @if($resolvedSizes) sizes="{{ $resolvedSizes }}" @endif>
    @endif
    <img
        src="{{ $fallbackSrc }}"
        alt="{{ $alt }}"
        width="{{ $width }}"
        height="{{ $height }}"
        loading="{{ $loading }}"
        decoding="async"
        @if($resolvedSizes) sizes="{{ $resolvedSizes }}" @endif
        @if($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        @class([$class])
    >
</picture>
