<?php

namespace App\Form\B2B;

use App\Entity\Client;
use App\Entity\Region;
use App\Entity\UserMission;
use App\Entity\UserPacks;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class MissionType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $regions = [];
        $clients = [];
        if($options['type'] == 'create' )
        {
            $readonly = false;
        }
        else
        {
            $readonly = true;
        }
        foreach($options['region'] as $region)
        {
            $regions[] = $region->getId();
        }

        foreach($options['proposals'] as $client)
        {
            if(!is_null($client->getClient()))
            {
                $clients[] = $client->getClient()->getId();
            }
        }
        $builder
            ->add('title',TextType::class,['label' => false])
            ->add('description', TextareaType::class,[
                'attr' => ['maxlength' => 1200, 'readonly' => $readonly],
                'label' => false
            ])
            ->add('client',EntityType::class,[
                'class' => Client::class,
                'multiple' => false,
                'expanded' => true,
                'query_builder' => function(EntityRepository $er) use($clients)
                {
                    return $er->createQueryBuilder('c')
                        ->where('c.id IN (:user)')->setParameter('user',$clients);
                }
            ])
            ->add('conditionsAgreed',HiddenType::class,[
                'attr' => ['value' => 1]
            ])
            ->add('referencePack', EntityType::class,[
                'class' => UserPacks::class,
                'multiple' => false,
//                'choices' => $options['user']->getUserPacks(),
                'expanded' => true,
                'attr' => ['class' => 'select-user-pack'],
                'query_builder' => function(EntityRepository $er) use($options)
                {
                    return $er->createQueryBuilder('p')->where('p.user = :user AND (p.deleted IS NULL or p.deleted = 0)')->setParameter('user',$options['user']);
                }
            ])
            ->add('bannerImage', HiddenType::class)
            ->add('briefFiles', HiddenType::class)
            ->add('documents',CollectionType::class,
                [
                    'required' => true,
                    'entry_type' => MissionDocumentType::class,
                    'entry_options' => ['label' => false],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'error_bubbling' => false,
                    'by_reference' => false
                ])
            ->add('missionType',ChoiceType::class,[
                'label' => false,
                'choices' => ['Mission One Shot' => 'one-shot','Mission Recurrente' => 'recurring']
            ])
            ->add('missionBasePrice', TextType::class,[
                'label' => false
            ])
            
//            ->add('dueDate', DateType::class)
//            ->add('conditionsAgreed', ChoiceType::class,[
//                'choices' => ['Yes' => 1, 'No' => 0],
//                'multiple' => false,
//                'expanded' => false
//            ])
            ->add('axaInsurance',ChoiceType::class,[
                'choices' => ['Add Axa insurance' => TRUE],
                'multiple' => false,
                'expanded' => true
            ])
//            ->add('generalConditionsBrief')
//            ->add('missionAgreedClient', ChoiceType::class,[
//                'choices' => ['Client Agreed' => TRUE],
//                'multiple' => false,
//                'expanded' => true
//            ])
            ->add('region', EntityType::class,[
                'class' => Region::class,
                'multiple' => false,
                'expanded' => false,
                'choice_label' => 'name',
                'query_builder' => function(EntityRepository $er) use($regions)
                {
                    return $er->createQueryBuilder('r')->where('r.id IN (:regions)')->setParameter('regions',$regions);
                }
            ])
//            ->add('client')
            ->add('userMissionPayment',UserMissionPaymentType::class)
            ->add('missionMedia',CollectionType::class,[
                'required' => true,
                'entry_type' => MissionMediaType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'by_reference' => false
            ])

            ->add('missionRegions', EntityType::class, array(
                'label' => 'regions',
                'class' => Region::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
                'query_builder' => function(EntityRepository $er) use($regions)
                {
                    return $er->createQueryBuilder('p')->where('p.id IN (:regions)')->setParameter('regions',$regions);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserMission::class,
            'region' => null,
            'user' => null,
            'proposals' => null,
            'type' => null
        ]);
    }
}
