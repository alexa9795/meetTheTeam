<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UploaderHelper;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function index(Request $request, UploaderHelper $uploaderHelper, UserService $userService)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadFile */
            $uploadedFile = $form['picture']->getData();

            if ($uploadedFile) {
                $filename = $uploaderHelper->uploadImage($uploadedFile);

                $user->setPicture($filename);
            }

            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

            $user->setCreatedBy($user->getEmail());

            $user->setRoles(['ROLE_USER']);

            if (!$userService->checkIfUserExists($user->getEmail())) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Email already registered in database!');
            }
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
