<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\Status;
use App\Entity\Employee;
use App\Repository\StatusRepository;
use App\Repository\EmployeeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TaskType extends AbstractType
{
    protected $statusRepository;
    protected $requestStack;
    protected $security;

    public function __construct(StatusRepository $statusRepository, RequestStack $requestStack, Security $security)
    {
        $this->statusRepository = $statusRepository;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $projectId = $request->get('id_project');

        $user = $this->security->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la tâche',
                'attr' => [
                    'maxlength' => '255'
                ],
                'required' => false
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => [
                    'maxlength' => '255'
                ],
                'required' => false
            ])
            ->add('deadline', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'attr' => [
                    'maxlength' => '255'
                ],
                'required' => false
            ])
            ->add('employee', EntityType::class, [
                'class' => Employee::class,
                'choice_label' => fn(Employee $employee) => $employee->getFirstName() . ' ' . $employee->getLastName(),
                'query_builder' => function (EmployeeRepository $er) use ($isAdmin, $user, $projectId) {
                    // Récupération de l'utilisateur et vérification du rôle
                    $qb = $er->createQueryBuilder('e');
                    if (!$isAdmin) {
                        // Si l'utilisateur n'est pas admin, ne renvoyer que son propre nom
                        $qb->where('e.id = :userId')
                            ->setParameter('userId', $user);
                    } else {
                        $qb->innerJoin('e.projects', 'p') // Assuming the relation is named "projects"
                            ->where('p.id = :projectId')
                            ->setParameter('projectId', $projectId);
                    }
                    return $qb;
                },
                'placeholder' => '-- associer un employer --',
                'required' => false
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'libelle',
                'query_builder' => function (StatusRepository $sr) use ($projectId) {
                    return $sr->createQueryBuilder('s')
                        ->where('s.project = :project')
                        ->setParameter('project', $projectId);
                },
                'placeholder' => '-- ajouter un status --',
                'required' => false
            ])
            // ->add('project', EntityType::class, [
            //     'class' => Project::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('tag', EntityType::class, [
            //     'class' => Tag::class,
            //     'choice_label' => 'id',
            //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
