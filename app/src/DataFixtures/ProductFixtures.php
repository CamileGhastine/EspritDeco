<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('FR_fr');

        for ($i = 0; $i < 20; $i++) { 
            $product = new Product();
            $product->setTitle($faker->words(rand(1,5), true))
                ->setDescription($faker->paragraphs(rand(2,5), true))
                ->setPrice(rand(9, 200) + 0.99)
                ->setCategory($this->getReference('category' . rand(0,4), Category::class))
                ;

            $this->addReference('product' . $i, $product);

            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
