<?php

$githubRepository = getenv('GITHUB_REPOSITORY');
if (!is_string($githubRepository) || !preg_match('/^([A-Za-z0-9_.-]+)\/([A-Za-z0-9_.-]+)$/D', $githubRepository, $matches)) {
    throw new RuntimeException('GITHUB_REPOSITORY must be set to owner/repository.');
}

$owner = strtolower($matches[1]);
$repository = strtolower($matches[2]);
$siteName = getenv('TOMOS_SITE_NAME');
if (!is_string($siteName) || trim($siteName) === '') {
    $siteName = $repository;
}

$basePath = $repository === $owner . '.github.io' ? '' : '/' . $repository;
$tomosRoot = getenv('TOMOS_ROOT');
if (!is_string($tomosRoot) || trim($tomosRoot) === '') {
    throw new RuntimeException('TOMOS_ROOT must point to the pinned Tomos checkout.');
}

// TOMOS_SITE_BRANDING_V1
// Site-owned branding images; no extra GitHub App permissions are required.
$tomosBrandingSource = __DIR__ . '/theme-assets';
$tomosBrandingDestination = rtrim($tomosRoot, DIRECTORY_SEPARATOR) . '/theme-assets';
if (is_dir($tomosBrandingSource)) {
    foreach (['ogp', 'favicon'] as $tomosBrandingKind) {
        foreach (['png', 'jpg', 'webp'] as $tomosBrandingExtension) {
            $tomosBrandingName = $tomosBrandingKind . '.' . $tomosBrandingExtension;
            $tomosBrandingFile = $tomosBrandingSource . '/' . $tomosBrandingName;
            if (!is_file($tomosBrandingFile) || is_link($tomosBrandingFile)) continue;
            $tomosBrandingInfo = @getimagesize($tomosBrandingFile);
            if (!is_array($tomosBrandingInfo)) throw new RuntimeException('Invalid site branding image');
            $tomosBrandingMime = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'webp' => 'image/webp'][$tomosBrandingExtension];
            if (($tomosBrandingInfo['mime'] ?? '') !== $tomosBrandingMime) throw new RuntimeException('Invalid site branding image format');
            if (filesize($tomosBrandingFile) > 10485760) {
                throw new RuntimeException('Site branding image exceeds 10 MB');
            }
            if (!is_dir($tomosBrandingDestination)
                && !mkdir($tomosBrandingDestination, 0755, true)
                && !is_dir($tomosBrandingDestination)) {
                throw new RuntimeException('Cannot prepare site branding assets');
            }
            if (!copy($tomosBrandingFile, $tomosBrandingDestination . '/' . $tomosBrandingName)) {
                throw new RuntimeException('Cannot stage site branding image');
            }
        }
    }
}

return [
    'site' => [
        'name' => 'seelsorge',
        'description' => 'devotion and thinking',
        'url' => 'https://' . $owner . '.github.io',
        'base_path' => $basePath,
        'language' => 'ja',
    ],
    'theme' => [
        'name' => 'tomos-note',
    ],
    'paths' => [
        'content_dir' => __DIR__ . '/content',
        'cache_dir' => __DIR__ . '/.tomos-cache',
        'theme_dir' => rtrim($tomosRoot, DIRECTORY_SEPARATOR) . '/themes',
    ],
    'features' => [
        'search' => true,
        'tags' => true,
        'rss' => true,
        'sitemap' => true,
    ],
];
