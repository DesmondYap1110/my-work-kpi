<?php

namespace App\Support;

/**
 * Cache-busting URLs for the app's own CSS and JS.
 *
 * There is no build step here (see README), so stylesheets and scripts are
 * plain files served straight from public/. Browsers cache them hard, which
 * means an edit can sit there invisible until someone thinks to hard-reload -
 * and a stale file looks exactly like a bug that was never fixed.
 *
 * Appending the file's modification time gives each version its own URL, so a
 * changed file is fetched and an unchanged one still comes from cache. Vendor
 * libraries are versioned by their own filenames and don't need this.
 */
class Asset
{
    /**
     * Modification times already looked up this request.
     *
     * @var array<string, int|null>
     */
    private static array $versions = [];

    public static function url(string $path): string
    {
        $version = self::version($path);

        return $version === null
            ? asset($path)
            : asset($path).'?v='.$version;
    }

    /**
     * The file's modification time, or null when it isn't on disk - a missing
     * file is the web server's problem to report, not a reason to fail here.
     */
    private static function version(string $path): ?int
    {
        if (! array_key_exists($path, self::$versions)) {
            $full = public_path($path);

            self::$versions[$path] = is_file($full) ? filemtime($full) : null;
        }

        return self::$versions[$path];
    }
}
