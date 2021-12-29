<?php

namespace App\DataFixtures;

use App\Entity\Program;
use App\Service\Slugify;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\ActorFixtures;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    private $slugify;

    public const USERS = [ 'user_contributor'];

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }


    public function load(ObjectManager $manager): void
    {
        $title = "Walking Dead";
        $program = new Program();
        $program->setTitle($title);
        $program->setSlug( $this->slugify->generate($title));
        $program->setSummary('Des zombies envahissent la terre');
        $program->setOwner($this->getReference("Contributor"));
        $program->setCategory($this->getReference('category_0'));
        //ici les acteurs sont insérés via une boucle pour être DRY mais ce n'est pas obligatoire
        for ($i = 0; $i < count(ActorFixtures::ACTORS); $i++) {
            $program->addActor($this->getReference('actor_' . $i));
        }
        $this->addReference("program_6", $program);
        $manager->persist($program);
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            ActorFixtures::class,
            CategoryFixtures::class,
            UserFixtures::class,
        ];
    }
}
