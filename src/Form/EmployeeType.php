<?php

namespace App\Form;

use App\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'maxlength' => '255'
                ],
                'required' => false
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prenom',
                'attr' => [
                    'maxlength' => '255'
                ],
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'maxlength' => '255'
                ],
                'required' => false
            ])
            ->add('arrival_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date d\'entrée',
                'attr' => [
                    'maxlength' => '255'
                ],
                'required' => false
            ])
            ->add('contract', TextType::class, [
                'label' => 'Statut',
                'attr' => [
                    'maxlength' => '255'
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}
