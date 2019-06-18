<?php

namespace App\Controller\Api;

use App\Entity\Client;
use App\Entity\ClientMissionProposal;
use App\Entity\UserMission;
use App\Entity\UserPackMedia;
use App\Entity\UserPacks;
use App\Form\B2B\ClientMissionProposalType;
use App\Form\B2B\PackType;
use App\Repository\OptionRepository;
use App\Repository\UserPacksRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/pack/", name="api_pack_")
 */
class PackController extends AbstractController
{
    /**
     * @Route("import-images/{id}",name="import_images")
     */
    public function images($id, UserPacksRepository $packRepo)
    {
        $pack = $packRepo->find($id);

        return $this->render('b2b/shared/pack-images.html.twig',[
            'images' => $pack->getUserPackMedia()
        ]);
    }

    /**
     * @Route("import-image-to-mission", name="import_to_mission")
     */
    public function importToMission(Request $request, Filesystem $filesystem)
    {
        $file = $request->get('filename');
        $filesystem->copy('uploads/packs/'.$this->getUser()->getId().'/'.$file,'uploads/'.UserMission::tempFolder().'/'.basename($file));

        return JsonResponse::create(['success' => true]);
    }

    /**
     * @Route("load-packs/{page}",name="_load_packs")
     */
    public function loadPacks($page)
    {
        $limit = 2;

//        $packs =
    }

    /**
     * @Route("view-pack/{id}",name="view")
     */
    public function viewPack($id, UserPacksRepository $userPacksRepo)
    {
        $pack = $userPacksRepo->find($id);


        if(is_null($pack))
        {
            return JsonResponse::create(['success' => false, 'response' => '<strong>Pack not found </strong>']);
        }

        return $response = $this->render('b2b/pack/_view.html.twig',[
            'pack' => $pack
        ]);
    }

    /**
     * @Route("create",name="create")
     */
    public function create(Request $request, Filesystem $filesystem, OptionRepository $optionRepository)
    {
        $pack = new UserPacks();
        $user = $this->getUser();
        $form = $this->createForm(PackType::class, $pack);

        $form->handleRequest($request);

        $tax = $optionRepository->findBy(['slug' => 'tax']);

        if($form->isSubmitted()) {

            $em = $this->getDoctrine()->getManager();
            $base_price = $request->get('pack')['userBasePrice'];
            $tax = $tax[0]->getValue();
            $margin = $base_price * $tax / 100;

            $total_value = $margin + $base_price;
            $pack->setUser($user);
            $pack->setMarginPercentage($tax);
            $pack->setMarginValue($margin);
            $pack->setTotalPrice($total_value);

            $em->persist($pack);

            $em->flush();

            if ($request->get('attached_files')) {

                $files = explode(',', $request->get('attached_files'));

                foreach ($files as $file) {

                    $file = trim($file);

                    $mediaEntity = new UserPackMedia();
                    $mediaEntity->setName($file);
                    $mediaEntity->setUserPack($pack);

                    $em->persist($mediaEntity);
                    $em->flush();

                    if ($filesystem->exists('uploads/pack/temp/' . $file)) {
                        $filesystem->copy('uploads/pack/temp/' . $file, 'uploads/pack/' . $pack->getId() . '/' . $file);

                    }


                }

            }


            if ($request->get('cm_images')) {

                foreach ($request->get('cm_images') as $key => $item) {
                    $image = explode('/', $item);

                    $mediaEntity = new UserPackMedia();
                    $mediaEntity->setName($image[4]);
                    $mediaEntity->setUserPack($pack);

                    $em->persist($mediaEntity);
                    $em->flush();

                    if ($filesystem->exists('uploads/community_media/' . $user->getId() . '/' . $image[4])) {
                        $filesystem->copy('uploads/community_media/' . $user->getId() . '/' . $image[4], 'uploads/pack/' . $pack->getId() . '/' . $image[4]);

                    }

                }

            }
        }
        return $this->render('b2b/pack/form.html.twig',
            [
                'pack' => $pack,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("details/{id}",name="details")
     */
    public function details($id, UserPacksRepository $packRepo)
    {
        $pack = $packRepo->find($id);
        $images = [];
        if(!is_null($pack))
        {
            foreach($pack->getUserPackMedia() as $media)
            {
                $images[] = [
                    'name' => $media->getName(),
                    'id' => $media->getId()
                ];
            }
            return new JsonResponse(['success' => true, 'data' => ['id' => $pack->getId(),
                'price' => $pack->getUserBasePrice(),
                'images' => $images,
                'skill' => strtoupper($pack->getPackSkill()->getName()),
                'title' => $pack->getTitle(),
                'description' => $pack->getDescription()]]);
        }
    }
}
