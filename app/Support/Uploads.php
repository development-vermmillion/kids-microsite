<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Stores admin uploads directly in public/uploads/<folder> so they work
 * on any host without `php artisan storage:link`.
 */
class Uploads
{
    /** Returns the path relative to public/, e.g. "uploads/badges/abc123.png". */
    public static function store(UploadedFile $file, string $folder): string
    {
        $dir = public_path('uploads/'.$folder);
        File::ensureDirectoryExists($dir);

        $name = Str::random(32).'.'.strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $file->move($dir, $name);

        return 'uploads/'.$folder.'/'.$name;
    }

    /** Deletes a previously uploaded file. URLs and bundled images are left alone. */
    public static function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/')) {
            File::delete(public_path($path));
        }
    }

    /** Turns a stored value (full URL, uploads/... path, or bundled image name) into a URL. */
    public static function url(?string $value, string $bundledFolder = 'frontend/images'): ?string
    {
        if (! $value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return str_starts_with($value, 'uploads/')
            ? asset($value)
            : asset($bundledFolder.'/'.$value);
    }
}
