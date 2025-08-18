<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Support\Facades\File;

class KitHelpers
{
    public static function copyDirectory(string $source, string $destination): void
    {
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $files = File::allFiles($source);
        
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $targetPath = $destination . '/' . $relativePath;
            
            $targetDir = dirname($targetPath);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            
            File::copy($file->getPathname(), $targetPath);
        }
    }

    public static function installMagicLinkAuth(): void
    {
        self::copyDirectory(
            resource_path('views/stubs/magic-auth'),
            resource_path('views/pages')
        );
    }

    public static function installPasswordAuth(): void
    {
        self::copyDirectory(
            resource_path('views/stubs/password-auth'),
            resource_path('views/pages')
        );
    }
}
