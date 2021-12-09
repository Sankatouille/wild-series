<?php

namespace App\DataFixtures;

use App\Entity\Season;
use App\DataFixtures\ProgramFixtures;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    const SEASONS = [
        ['saison 1', 1, 2011],
        ['saison 2', 2, 2012 ],
        ['saison 3', 3, 2013],
        ['saison 4', 4, 2014],
        ['saison 5 ', 5, 2015 ]
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::SEASONS as $key => $seasonInfos) {
            $season = new Season();
            $season->setDescription($seasonInfos[0]);
            $season->setNumber($seasonInfos[1]);
            $season->setYear($seasonInfos[2]);

            $season->setProgram($this->getReference("program_6"));
            $this->addReference("season_" . $key, $season);

            $manager->persist($season);
            
            

        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont SeasonFixtures dépend
        return [
          ProgramFixtures::class,
          CategoryFixtures::class,
        ];
    }
}
