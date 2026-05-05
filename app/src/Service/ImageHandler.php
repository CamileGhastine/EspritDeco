<?php
namespace App\Service;

use App\Entity\Image;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageHandler
{
    private Session $session;

    public function __construct(
        private SluggerInterface $slugger,
        private RequestStack $requestStack,
        private string $publicPath,
        private string $publicDirectory
        )
    {
        $this->session = $this->requestStack->getSession();
    }

    public function deleteFiles(Product $product)
    {
        foreach ($product->getImages() as $image) {
            $fileName = $image->getName();

            if (file_exists($this->publicDirectory . '/' .$this->publicPath . '/' . $fileName)) {
                unlink($this->publicDirectory . '/' .$this->publicPath . '/'. $fileName);
            }
        }
    }

    public function deleteFile(Image $image)
    {
        $fileName = $image->getName();
        if (file_exists($this->publicDirectory . '/' .$this->publicPath . '/' . $fileName)) {
            unlink($this->publicDirectory . '/' .$this->publicPath . '/' . $fileName);
        }
    }

    public function uploadImage(UploadedFile $imageFile, $product)
    {
        try {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = 'img_' .uniqid() . '-' .$safeFilename . '.' . $imageFile->guessExtension();
            $imageFile->move($this->publicDirectory . '/' .$this->publicPath, $newFilename);

            $image = new Image;
            $image->setName($newFilename)
                ->setAlt($originalFilename)
                ->setIsPrincipal(!$product->hasImages())
                ->setProduct($product)
            ;
            
            return $image;
        } catch (FileException $e) {
            $this->session->getFlashBag()->add('danger', 'Erreur lors de l’upload de l’image.');
            
            return false;
        }
    }
}
