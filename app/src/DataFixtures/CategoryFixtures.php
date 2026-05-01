<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categoryNames = ['Textile', 'Lumière', 'Mural', 'Décor', 'Pratique'];

        foreach($categoryNames as $i => $name) {
            $category = new Category;
            $category->setName($name);
            $manager->persist($category);

            $this->addReference('category' . $i, $category);
        }

        $manager->flush();
    }
}
