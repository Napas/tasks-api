<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TasksFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $task1 = new Task();
        $task1
            ->setName('Name 1')
            ->setDeadlineAt(Carbon::tomorrow());

        $manager->persist($task1);

        $task2 = new Task();
        $task2
            ->setName('Name 2')
            ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque suscipit et.')
            ->setDeadlineAt(Carbon::now()->addMonth());

        $manager->persist($task2);

        $manager->flush();
    }
}
