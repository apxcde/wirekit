<?php

namespace App\Actions\Commands\Kit;

use Illuminate\Support\Facades\File;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

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

    public static function cleanupStubsDirectory(): void
    {
        $path = resource_path('views/stubs');
        
        if (File::exists($path)) {
            File::deleteDirectory($path);
        }
    }

    public static function cleanupAuthViews(): void
    {
        $magicAuthFiles = [
            'login.blade.php',
            'register.blade.php'
        ];
        
        foreach ($magicAuthFiles as $file) {
            $filePath = resource_path('views/pages/' . $file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        
        $passwordAuthFiles = [
            'login.blade.php',
            'register.blade.php',
            'forgot-password.blade.php',
            'reset-password/[token].blade.php'
        ];
        
        foreach ($passwordAuthFiles as $file) {
            $filePath = resource_path('views/pages/' . $file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        
        $resetPasswordDir = resource_path('views/pages/reset-password');
        if (File::exists($resetPasswordDir) && empty(File::files($resetPasswordDir))) {
            File::deleteDirectory($resetPasswordDir);
        }
    }

    public static function cleanupInstallationFiles(): void
    {
        $installationDir = app_path('Actions/Commands/Kit');

        if (!is_dir($installationDir)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($installationDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }

        rmdir($installationDir);
    }
}
