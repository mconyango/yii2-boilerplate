<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-06-28 7:56 PM
 */

namespace common\barcode;


class BarcodeGeneratorPNG extends \Picqer\Barcode\BarcodeGeneratorPNG
{
    /**
     * @param string $code
     * @param string $type
     * @param int $widthFactor
     * @param int $totalHeight
     * @param array $color
     * @return string
     */
    public function getBarcode($code, $type, $widthFactor = 2, $totalHeight = 30, $color = [0, 0, 0])
    {
        $barcodeData = $this->getBarcodeData($code, $type);

        // calculate image size
        $width = ($barcodeData['maxWidth'] * $widthFactor);
        $height = $totalHeight;

        if (function_exists('imagecreate')) {
            // GD library
            $imagick = false;
            //$png = imagecreate($width, $height);
            //my customization
            $png = imagecreate($width, $height + 20);
            $colorBackground = imagecolorallocate($png, 255, 255, 255);
            imagecolortransparent($png, $colorBackground);
            $colorForeground = imagecolorallocate($png, $color[0], $color[1], $color[2]);

            //my customization
            $font = 5;
            $font_width = imagefontwidth($font);
            $text_width = $font_width * strlen($code);
            // Position to align in center
            $position_center = ceil(($width - $text_width) / 2);
            $black = imagecolorallocate($png, 0, 0, 0);
            imagestring($png, $font, $position_center, $height, $code, $black);
        } elseif (extension_loaded('imagick')) {
            $imagick = true;
            $colorForeground = new \imagickpixel('rgb(' . $color[0] . ',' . $color[1] . ',' . $color[2] . ')');
            $png = new \Imagick();
            $png->newImage($width, $height, 'none', 'png');
            $imageMagickObject = new \imagickdraw();
            $imageMagickObject->setFillColor($colorForeground);
        } else {
            return false;
        }

        // print bars
        $positionHorizontal = 0;
        foreach ($barcodeData['bars'] as $bar) {
            $bw = round(($bar['width'] * $widthFactor), 3);
            $bh = round(($bar['height'] * $totalHeight / $barcodeData['maxHeight']), 3);
            if ($bar['drawBar']) {
                $y = round(($bar['positionVertical'] * $totalHeight / $barcodeData['maxHeight']), 3);
                // draw a vertical bar
                if ($imagick && isset($imageMagickObject)) {
                    $imageMagickObject->rectangle($positionHorizontal, $y, ($positionHorizontal + $bw), ($y + $bh));
                } else {
                    imagefilledrectangle($png, $positionHorizontal, $y, ($positionHorizontal + $bw) - 1, ($y + $bh),
                        $colorForeground);
                }
            }
            $positionHorizontal += $bw;
        }
        ob_start();
        if ($imagick && isset($imageMagickObject)) {
            $png->drawImage($imageMagickObject);
            echo $png;
        } else {
            imagepng($png);
            imagedestroy($png);
        }
        $image = ob_get_clean();

        return $image;
    }


}