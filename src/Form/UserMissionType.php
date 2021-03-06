<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\User;
use App\Entity\UserMission;
use KMS\FroalaEditorBundle\Form\Type\FroalaEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UserMissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', FroalaEditorType::class, array(
                'label' => 'label.content',
                'attr' => [
                    'rowClass' => 'type-froala'
                ],
                'toolbarStickyOffset' =>  70,
                'fontFamily' => ['Montserrat'],
            ))
            ->add('title',TextType::class,array('label'=>'Titre mission'))
            ->add('bannerImage',HiddenType::class,array('data_class'=> null, 'label' => 'Image bannière'))
            ->add('briefFiles',TextType::class,array('label'=>'Documents de travail'))
            ->add('missionBasePrice',TextType::class,array('label'=>'Prix mission CM'))
           // ->add('createdAt')
           // ->add('updatedAt')
            ->add('status',TextType::class,array('label'=>'Statut'))
//            ->add('dueDate', DateType::class, [
//                'placeholder' => [
//                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
//                ]
//            ])
            //->add('conditionsAgreed')
            ->add('axaInsurance')
            ->add('generalConditionsBrief')
            ->add('missionAgreedClient', ChoiceType::class, [
                'choices'  => [
                    'Yes' => 1,
                    'No' => 0,
                ],
            ])
            ->add('cancelReason',TextType::class,array('label'=>'Raison annulation'))
            ->add('cancelledBy',TextType::class,array('label'=>'Annulé par'))
            ->add('user',EntityType::class, array(
                'class' => User::class,
                'choice_label' => 'firstname',
                'label'=>'CM'
            ))
            ->add('client',EntityType::class, array(
                'class' => Client::class,
                'choice_label' => 'firstName',
            ))
            ->add('referencePack')
            //->add('userMissionPayment')
            ->add('region')
           // ->add('log')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserMission::class,
        ]);
    }
}
