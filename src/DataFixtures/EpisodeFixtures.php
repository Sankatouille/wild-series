<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use App\DataFixtures\SeasonFixtures;
use App\DataFixtures\ProgramFixtures;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    const EPISODES = [
        ['Episode 1', 1, "nazejaiofhnapokfefjifpaioek"],
        ['Episode 2', 2, "nazejaiofhnapokfefjifpaioek"],
        ['Episode 3', 3, "nazejaiofhnapokfefjifpaioek"],
        ['Episode 4', 4, "nazejaiofhnapokfefjifpaioek"],
        ['Episode 5 ', 5, "nazejaiofhnapokfefjifpaioek"],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::EPISODES as $key => $episodeInfos) {
            $episode = new Episode();
            $episode->setTitle($episodeInfos[0]);
            $episode->setNumber($episodeInfos[1]);
            $episode->setSynopsis($episodeInfos[2]);

            $manager->persist($episode);
            
            $episode->setSeason($this->getReference("season_4"));

        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont SeasonFixtures dépend
        return [
          ProgramFixtures::class,
          CategoryFixtures::class,
          SeasonFixtures::class,
          
        ];
    }
}
