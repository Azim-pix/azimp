<?php

namespace App\Controller\Api;

use App\Constant\CardProjectStatus;
use App\Controller\Front\SearchPageController;
use App\Repository\CardProjectRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use ZipArchive;

/**
 * @Route("/api/projects", name="api_projects_")
 */

class CardsProjectsController extends SearchPageController
{

    private $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer(array(new ObjectNormalizer()), array(new JsonEncoder()));
    }


    /**
     * Get modal content
     * @Route("/modal-infos", name="modal_infos")
     * @Method({"GET", "POST"})
     */
    public function infos(Request $request, CardProjectRepository $projectsRepo)
    {
        $projectId = $request->request->get("id");

        $html = "";

        if($projectId){
            $project = $projectsRepo->findOneBy(["pixie" => $this->getUser()->getId(), "id" => $projectId]);

            $html = $this->render('front/_modals/project-infos.html.twig', ['project' => $project])->getContent();
        }

        $responseContent = [
            "html" => $html,
            "result" => ($html !== "")?true:false
        ];


        return new JsonResponse($responseContent);
    }


    /**
     * Pixie accept project
     * @Route("/accept", name="accept")
     * @Method({"GET", "POST"})
     */
    public function accept(Request $request, CardProjectRepository $projectsRepo)
    {
        $projectId = $request->request->get("id");

        $success = false;

        if($projectId){
            $project = $projectsRepo->findOneBy(["pixie" => $this->getUser()->getId(), "id" => $projectId, "status" => CardProjectStatus::ASSIGNED]);
            if($project){

                $contract = [
                    "gender" => $project->getPixie()->getGender(),
                    "firstname" => $project->getPixie()->getFirstname(),
                    "lastname" => $project->getPixie()->getLastname(),
                    "birthdate" => $project->getPixie()->getBirthdate(),
                    "status" => $project->getPixie()->getPixie()->getBilling()->getStatus(),
                    "company_name" => $project->getPixie()->getPixie()->getBilling()->getCompanyName(),
                    "company_address" => $project->getPixie()->getPixie()->getBilling()->getAddress()->getInlineAddress(),
                    "company_country" => $project->getPixie()->getPixie()->getBilling()->getAddress()->getCountry(),
                    "company_tva" => $project->getPixie()->getPixie()->getBilling()->getTva(),
                    "date" => date("Y-m-d H:i:s"),
                    "card_project_name" => $project->getName()
                ];

                $project->setContract(json_encode($contract));
                $project->setStatus(CardProjectStatus::PIXIE_ACCEPTED);

                // Save the project
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($project);
                $entityManager->flush();

                $success = true;
            }
        }

        $responseContent = [
            "result" => $success
        ];

        return new JsonResponse($responseContent);
    }


    /**
     * Pixie refused project
     * @Route("/refuse", name="refuse")
     * @Method({"GET", "POST"})
     */
    public function refuse(Request $request, CardProjectRepository $projectsRepo)
    {
        $projectId = $request->request->get("id");

        $success = false;

        if($projectId){
            $project = $projectsRepo->findOneBy(["pixie" => $this->getUser()->getId(), "id" => $projectId, "status" => CardProjectStatus::ASSIGNED]);
            if($project){
                $project->setStatus(CardProjectStatus::PIXIE_REFUSED);

                // Save the project
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($project);
                $entityManager->flush();

                $success = true;
            }
        }

        $responseContent = [
            "result" => $success
        ];

        return new JsonResponse($responseContent);
    }


    /**
     * Pixie refused project
     * @Route("/download-files/{id}", name="download_files")
     * @Method({"GET"})
     */
    public function download_files(Request $request, CardProjectRepository $projectsRepo)
    {
        $projectId = $request->get("id");

        $success = false;

        if($projectId){
            $project = $projectsRepo->findOneBy(["pixie" => $this->getUser()->getId(), "id" => $projectId]);

            if($project){

                $zip = new ZipArchive();
                $fileName = $project->getId().'_documents.zip';
                $zipName = "uploads/projects/".$fileName;
                $zip->open($zipName,  ZipArchive::CREATE);
                foreach($project->getAttachments() as $attachment){
                    $zip->addFromString(basename($attachment->getUrl()),  file_get_contents("../public".$attachment->getUrl()));
                }
                $zip->close();

                $response = new Response(file_get_contents($zipName));
                $response->headers->set('Content-Type', 'application/zip');
                $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
                $response->headers->set('Content-length', filesize($zipName));

                return $response;


            }
        }

        $responseContent = [
            "result" => $success
        ];

        return new JsonResponse($responseContent);
    }

}