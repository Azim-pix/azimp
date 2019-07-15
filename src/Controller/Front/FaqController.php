<?php

namespace App\Controller\Front;

use App\Repository\FaqsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/front/faq", name="front_faq_")
 */
class FaqController extends AbstractController
{
    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index(FaqsRepository $faqsRepository)
    {
        return $this->render('front/faq/index.html.twig', [
            'faqs0' => $faqsRepository->findBy(['category'=>'CLIENT', 'subcategory'=>0]),
            'faqs1' => $faqsRepository->findBy(['category'=>'CLIENT', 'subcategory'=>1]),
            'faqs2' => $faqsRepository->findBy(['category'=>'CLIENT', 'subcategory'=>2]),
            'faqs3' => $faqsRepository->findBy(['category'=>'CLIENT', 'subcategory'=>3]),
            'faqs4' => $faqsRepository->findBy(['category'=>'CLIENT', 'subcategory'=>4]),
            'faqsCm0' => $faqsRepository->findBy(['category'=>'CM', 'subcategory'=>0]),
            'faqsCm1' => $faqsRepository->findBy(['category'=>'CM', 'subcategory'=>1]),
            'faqsCm2' => $faqsRepository->findBy(['category'=>'CM', 'subcategory'=>2]),
            'faqsCm3' => $faqsRepository->findBy(['category'=>'CM', 'subcategory'=>5]),
            'faqsCm4' => $faqsRepository->findBy(['category'=>'CM', 'subcategory'=>6]),
        ]);
    }
}
