<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GeneratePwaIcons extends Command
{
    protected $signature = 'pwa:generate-icons {--source= : Source image path}';
    protected $description = 'Génère les icônes PWA à partir du logo';

    protected array $sizes = [
        72, 96, 128, 144, 152, 167, 180, 192, 384, 512
    ];

    public function handle(): int
    {
        $sourcePath = $this->option('source') ?: public_path('images/logo.png');
        $outputDir = public_path('images/icons');

        if (!File::exists($sourcePath)) {
            $this->error("Fichier source non trouvé: {$sourcePath}");
            return 1;
        }

        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $this->info('Génération des icônes PWA...');

        // Vérifier si GD est disponible
        if (!extension_loaded('gd')) {
            $this->warn('Extension GD non disponible. Création d\'icônes de couleur unie.');
            return $this->generateColorIcons($outputDir);
        }

        // Charger l'image source
        $sourceInfo = getimagesize($sourcePath);
        if (!$sourceInfo) {
            $this->error('Impossible de lire l\'image source');
            return 1;
        }

        $sourceImage = match ($sourceInfo[2]) {
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (!$sourceImage) {
            $this->error('Format d\'image non supporté');
            return 1;
        }

        // Activer la transparence
        imagealphablending($sourceImage, true);
        imagesavealpha($sourceImage, true);

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $bar = $this->output->createProgressBar(count($this->sizes));
        $bar->start();

        foreach ($this->sizes as $size) {
            $destImage = imagecreatetruecolor($size, $size);
            
            // Gérer la transparence
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
            imagefill($destImage, 0, 0, $transparent);

            // Redimensionner en conservant les proportions
            $ratio = min($size / $sourceWidth, $size / $sourceHeight);
            $newWidth = (int)($sourceWidth * $ratio);
            $newHeight = (int)($sourceHeight * $ratio);
            $x = (int)(($size - $newWidth) / 2);
            $y = (int)(($size - $newHeight) / 2);

            imagecopyresampled(
                $destImage,
                $sourceImage,
                $x, $y, 0, 0,
                $newWidth, $newHeight,
                $sourceWidth, $sourceHeight
            );

            $outputPath = "{$outputDir}/icon-{$size}x{$size}.png";
            imagepng($destImage, $outputPath, 9);
            imagedestroy($destImage);

            $bar->advance();
        }

        imagedestroy($sourceImage);

        $bar->finish();
        $this->newLine(2);
        $this->info('Icônes générées avec succès dans: ' . $outputDir);

        return 0;
    }

    protected function generateColorIcons(string $outputDir): int
    {
        $this->info('Génération d\'icônes minimalistes...');

        $bar = $this->output->createProgressBar(count($this->sizes));
        $bar->start();

        foreach ($this->sizes as $size) {
            $image = imagecreatetruecolor($size, $size);
            
            // Fond bleu (#3b82f6)
            $bgColor = imagecolorallocate($image, 59, 130, 246);
            imagefill($image, 0, 0, $bgColor);
            
            // Cercle blanc au centre
            $white = imagecolorallocate($image, 255, 255, 255);
            $center = $size / 2;
            $radius = $size * 0.35;
            imagefilledellipse($image, (int)$center, (int)$center, (int)($radius * 2), (int)($radius * 2), $white);
            
            // Lettre "B" au centre
            $textColor = imagecolorallocate($image, 59, 130, 246);
            $fontSize = $size * 0.4;
            
            // Utiliser la police par défaut
            $font = 5; // Plus grande police intégrée
            $text = 'B';
            
            $textX = (int)(($size - imagefontwidth($font) * strlen($text)) / 2);
            $textY = (int)(($size - imagefontheight($font)) / 2);
            imagestring($image, $font, $textX, $textY, $text, $textColor);

            $outputPath = "{$outputDir}/icon-{$size}x{$size}.png";
            imagepng($image, $outputPath);
            imagedestroy($image);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Icônes générées avec succès!');

        return 0;
    }
}
