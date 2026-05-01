<?php

namespace App\Service;

use App\Entity\Product;

class ImageHandler
{
    public function deleteFiles(Product $product)
    {
        foreach ($product->getImages() as $image) {
            $filePath = $image->getPath();

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}