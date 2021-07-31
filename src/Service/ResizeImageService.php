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

namespace App\Service;

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
        $image = imagecreatefromstring($data);

        if (!is_object($image) && (!is_resource($image) || !$image instanceof GdImage)) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $newWidth) {
            return $returnOrigin ? $originImage : null;
        }

        $percent = $newWidth / $width;
        $newWidth = (int) ($width * $percent);
        $newHeight = (int) ($height * $percent);

        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresized($thumb, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagejpeg($thumb);
        $contents = ob_get_contents();
        ob_end_clean();

        return base64_encode($contents);
    }
}
