<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Service;

use GdImage;

class ResizeImageService
{
    public function resizeBase64Image(
        string $originImage,
        int $newWidth,
        bool $returnOrigin = false
    ): ?string {
        $image = $originImage;
        $data = base64_decode($image);
        $image = $this->getGdImage($data);

        if (!$image instanceof GdImage) {
            return null;
        }

        $width = $this->getImagesx($image);
        $height = $this->getImagesy($image);

        if ($width <= $newWidth) {
            return $returnOrigin ? $originImage : null;
        }

        $percent = $newWidth / $width;
        $newWidth = (int) ($width * $percent);
        $newHeight = (int) ($height * $percent);

        $thumb = $this->getImageCreateTrueColory($newWidth, $newHeight);
        if ($thumb === false) {
            return null;
        }

        imagecopyresized(
            dst_image: $thumb,
            src_image: $image,
            dst_x: 0,
            dst_y: 0,
            src_x: 0,
            src_y: 0,
            dst_width: $newWidth,
            dst_height: $newHeight,
            src_width: $width,
            src_height: $height
        );

        $contents = $this->getContent($thumb);
        if ($contents === false) {
            return null;
        }

        return base64_encode($contents);
    }

    public function getGdImage(string $data): GdImage|false
    {
        return imagecreatefromstring($data);
    }

    protected function getImagesx(GdImage $image): int
    {
        return imagesx($image);
    }

    protected function getImagesy(GdImage $image): int
    {
        return imagesy($image);
    }

    protected function getImageCreateTrueColory(int $width, int $height): GdImage|false
    {
        return imagecreatetruecolor(max(1, $width), max(1, $height));
    }

    protected function getContent(GdImage $image): string|false
    {
        ob_start();
        imagejpeg($image);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}
