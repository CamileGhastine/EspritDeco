<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('FR_fr');

        for ($i = 0; $i < 20 ; $i++) { 
            $nbrImage = rand(0,3);
            if ($nbrImage === 0) continue;
            
            $bool = true;
            for ($j = 0; $j < $nbrImage; $j++) { 
                $image = new Image;
                $image->setName('images/product/image' . rand(1, 20) . '.webp')
                    ->setAlt($faker->words(rand(3,10), true))
                    ->setIsPrincipal($bool)
                    ->setProduct($this->getReference('product' . $i, Product::class))
                    ;
                $manager->persist($image);
                $bool = false;
            }
        }
        
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
        ];
    }
}
