<?php

namespace App\Controller;

use Twig\Environment;
use App\Entity\Status;
use App\Form\ProjectType;
use App\Repository\TaskRepository;
use App\Repository\StatusRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{
    protected $twig;
    protected $projectRepository;
    protected $taskRepository;
    protected $statusRepository;
    protected $em;

    public function __construct(Environment $twig, ProjectRepository $projectRepository, TaskRepository $taskRepository, EntityManagerInterface $em, StatusRepository $statusRepository)
    {
        $this->twig = $twig;
        $this->projectRepository = $projectRepository;
        $this->taskRepository = $taskRepository;
        $this->statusRepository = $statusRepository;
        $this->em = $em;
    }

    #[Route('/projets', name: 'app_projects')]
    public function showProjects()
    {
        $roles = $this->getUser()->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            $projects = $this->projectRepository->findByArchiveStatus(false);
        } else {
            $projects = $this->projectRepository->getProjectsByEmployeeEmail($this->getUser()->getUserIdentifier());
        }

        return $this->render('project/projects.html.twig', [
            'projects' => $projects
        ]);
    }

    #[Route('/projet/{id}', name: 'app_one_project', priority: -1)]
    public function showProject($id)
    {
        $project = $this->projectRepository->find($id);

        $tasks = $this->taskRepository->findByProject($project);

        $taskPerStatus = [
            "To Do" => [],
            "Doing" => [],
            "Done" => [],
        ];

        foreach ($tasks as $task) {
            $taskPerStatus[$task->getStatus()->getLibelle()][] = $task;
        }

        $listEmployee = [];

        foreach ($project->getEmployee() as $employee) {
            $listEmployee[] = $employee;
        }

        return $this->render('project/one-project.html.twig', [
            'project' => $project,
            'taskPerStatus' => $taskPerStatus,
            'listEmployee' => $listEmployee
        ]);
    }

    #[Route('/projet/{id}/edit', name: 'app_edit_project')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les autorisations requises pour accèder à cette page.')]
    public function editProject($id, Request $request)
    {
        $project = $this->projectRepository->find($id);

        if (!$project) {
            throw $this->createNotFoundException("Le projet demandé n'existe pas.");
        }

        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('app_one_project', ['id' => $project->getId()]);
        }

        return $this->render('project/edit-project.html.twig', [
            'project' => $project,
            'formView' => $form->createView()
        ]);
    }

    #[Route('/projet/add', name: 'app_add_project')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les autorisations requises pour accèder à cette page.')]
    public function addProject(Request $request)
    {
        $form = $this->createForm(ProjectType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $project = $form->getData();
            $project->setStartDate(new \DateTime());
            $this->em->persist($project);

            $libelleStatus = ['To Do', 'Doing', 'Done'];

            foreach ($libelleStatus as $libelle) {
                $status = new Status();
                $status->setLibelle($libelle)
                    ->setProject($project);

                $this->em->persist($status);
            }

            $this->em->flush();
            return $this->redirectToRoute('app_one_project', ['id' => $project->getId()]);
        }

        return $this->render('project/add-project.html.twig', [
            'formView' => $form->createView()
        ]);
    }

    #[Route('/project/{id}/archiving', name: 'app_archiving_project')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les autorisations requises pour accèder à cette page.')]
    public function archivingProject($id)
    {
        $project = $this->projectRepository->find($id);

        $project->setArchive(true);

        $this->em->flush();

        return $this->redirectToRoute('app_projects');
    }
}
