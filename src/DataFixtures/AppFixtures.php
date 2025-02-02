<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Tag;
use App\Entity\Slot;
use App\Entity\Task;
use App\Entity\Status;
use App\Entity\Project;
use App\Entity\Employee;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    protected $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Tableau contenant tous les projets.
        $projects = [];

        // Création des projets.
        for ($index = 0; $index < 4; $index++) {
            $project = new Project();
            $project->setName($faker->realText(mt_rand(20, 50)))
                ->setStartDate($faker->dateTimeBetween('-6 months', 'now'))
                ->setDeadline($faker->dateTimeBetween('now', '+6 months'))
                ->setArchive($faker->boolean((25)));

            $projects[] = $project;

            $manager->persist($project);
        }

        // Tableau des différents types de contrat possible (exemple).
        $typesContract = ['CDI', 'CDD', 'Freelance'];

        // Tableau contenant tous les employés.
        $employees = [];
        $employeesPerProject = [];

        // Création compte admin
        $admin = new Employee();
        $hashAdmin = $this->encoder->hashPassword($admin, 'password');
        $admin->setEmail('user@gmail.com')
            ->setFirstName($faker->firstName())
            ->setLastName($faker->lastName())
            ->setContract('CDI')
            ->setArrivalDate($faker->dateTimeBetween('-9 months', 'now'))
            ->setActive(true)
            ->setPassword($hashAdmin)
            ->setRoles(["ROLE_ADMIN"]);

        $manager->persist($admin);

        // Création des employés.
        for ($index = 0; $index < 15; $index++) {
            $employee = new Employee();
            $hash = $this->encoder->hashPassword($employee, 'password');
            $employee->setEmail($faker->email())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setContract($faker->randomElement($typesContract))
                ->setArrivalDate($faker->dateTimeBetween('-9 months', 'now'))
                ->setActive($faker->boolean((75)))
                ->setPassword($hash);

            $selectedProjects = $faker->randomElements($projects, mt_rand(1, 2));

            foreach ($selectedProjects as $project) {
                $employee->addProject($project);
                $employeesPerProject[$project->getId()][] = $employee;
            }
            $employees[] = $employee;


            $manager->persist($employee);
        }

        // Tableau des différents libellé possible (exemple).
        $libelleStatus = ['To Do', 'Doing', 'Done'];

        // Tableau contenant tous les statuts.
        $arrayStatus = [];

        // Création des statuts.
        foreach ($projects as $project) {
            for ($index = 0; $index < 3; $index++) {
                $status = new Status();
                $status->setLibelle($libelleStatus[$index])
                    ->setProject($project);

                $arrayStatus[] = $status;

                $manager->persist($status);
            }
        }

        // Tableau des différents libellé possibles (exemple).
        $libelleTag = ['UX', 'Frontend', 'Backend'];

        // Création des tags associé au projet.
        foreach ($projects as $project) {
            for ($index = 0; $index < random_int(1, 2); $index++) {
                $tag = new Tag();
                $tag->setLibelle($faker->randomElement($libelleTag))
                    ->setProject($project);

                $manager->persist($tag);
            }
        }

        // Tableau contenant toutes les taches.
        $arrayTask = [];

        // Création des tâches.
        foreach ($projects as $project) {
            for ($index = 0; $index < mt_rand(2, 6); $index++) {
                $task = new Task();
                $task->setTitle($faker->realText(mt_rand(15, 20)))
                    ->setDescription($faker->realText(mt_rand(40, 60)))
                    ->setDeadline($faker->dateTimeBetween('now', '+2 months'))
                ;

                foreach ($employeesPerProject as $key => $value) {
                    if ($key === $project->getId()) {
                        $task->setEmployee($faker->randomElement($value));
                    }
                }
                $task->setStatus($faker->randomElement($arrayStatus))
                    ->setProject($project);

                $arrayTask[] = $task;

                $manager->persist($task);
            }
        }

        // Création des créneaux.
        foreach ($arrayTask as $task) {
            for ($index = 0; $index < random_int(0, 3); $index++) {

                $selectedEmployee = $faker->randomElement($employees);

                $slot = new Slot();
                $slot->setTask($task)
                    ->setEmployee($selectedEmployee)
                    ->setStart($faker->dateTimeBetween('-7 days', 'now'))
                    ->setEnd($faker->dateTimeBetween('-5 days', 'now'));

                $manager->persist($slot);
            }
        }


        $manager->flush();
    }
}
