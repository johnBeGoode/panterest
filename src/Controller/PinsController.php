<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods="GET")
     */
    public function index(PinRepository $pinRepository): Response
    {
        $pins = $pinRepository->findby([], ['createdAt' => 'DESC']);

        return $this->render('pins/index.html.twig', compact('pins'));
    }

    /**
     * @Route("/pins/{id<\d+>}", name="app_pins_show", methods="GET")
     */
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }

    /**
     * @Route("/pins/create", name="app_pins_create", methods="GET|POST")
     * @IsGranted("PIN_CREATE")
     */
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {
        $pin = new Pin;

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $pin = $form->getData(); // Pas obligé car on a déjà instancié un pin ligne 42 et si on debug, $form->getData et $pin retourne exactement la même chose.
            $pin->setUser($this->getUser());
            $em->persist($pin);
            $em->flush();

            $this->addFlash('success', 'Pin successfully created');

            return $this->redirectToRoute("app_home");
        }


        return $this->render('pins/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/pins/{id<\d+>}/edit", name="app_pins_edit", methods="GET|PUT")
     * @Security("is_granted('PIN_MANAGE', pin)")
     */
    public function edit(Pin $pin, Request $request, EntityManagerInterface $em): Response     // SYSTEME DE VOTER (plus de flexibilité avec @security que @IsGranted)  
    {
        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas besoin de faire persister le pin car il existe déjà
            $em->flush();

            $this->addFlash('success', 'Pin successfully updated');

            return $this->redirectToRoute("app_pins_show", ['id' => $pin->getId()]);
        }

        return $this->render('pins/edit.html.twig', [
            'pin' => $pin,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/pins/{id<\d+>}/delete", name="app_pins_delete", methods="DELETE")
     * @IsGranted("PIN_MANAGE", subject="pin")
     */
    public function delete(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        if ($pin->getUser() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('pin_deletion ' . $pin->getId(), $request->request->get('csrf_token'))) {
            $em->remove($pin);
            $em->flush();

            $this->addFlash('info', 'Pin successfully deleted');
        }

        return $this->redirectToRoute('app_home');
    }
}
