<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use App\Service\UploaderHelper;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    /**
     * @Route("/list", name="list")
     */
    public function index(Request $request, Security $security)
    {
        if (!$security->getUser()) {
            $this->addFlash('error', 'User should be logged in to access the list of team members!');

            return $this->redirectToRoute('app_login');
        }

        $entityManager = $this->getDoctrine()->getManager();

        /** @var UserRepository $userRepository */
        $userRepository = $entityManager->getRepository(User::class);

        /** @var User[] $users */
        $users = $userRepository->findColleaguesForUser($security->getUser()->getEmail());

        $rawPictures = [];
        foreach ($users as $user) {
            if (!empty($user->getPicture())) {
                $rawPictures[$user->getId()] =
                    "data:image/png;base64," . base64_encode(stream_get_contents($user->getPicture()));
            }
        }

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'rawPictures' => $rawPictures
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(int $id, Request $request, UploaderHelper $uploaderHelper, Security $security)
    {
        if (!$security->getUser()) {
            $this->addFlash('error', 'User should be logged in order to edit a team member!');

            return $this->redirectToRoute('app_login');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->find($id);

        if (empty($user)) {
            throw new \Exception('User not found!');
        }

        $form = $this->createForm(UserEditType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var UploadedFile $uploadFile */
            $uploadedFile = $form['picture']->getData();

            if ($uploadedFile) {
                $filename = $uploaderHelper->uploadImage($uploadedFile);

                $user->setPicture($filename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User updated!');

            return $this->redirectToRoute('list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/add", name="add")
     */
    public function add(Request $request, UploaderHelper $uploaderHelper, Security $security, UserService $userService)
    {
        if (!$security->getUser()) {
            $this->addFlash('error', 'User should be logged in order to add a team member!');

            return $this->redirectToRoute('app_login');
        }

        $user = new User();

        $form = $this->createForm(UserEditType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var UploadedFile $uploadFile */
            $uploadedFile = $form['picture']->getData();

            if ($uploadedFile) {
                $filename = $uploaderHelper->uploadImage($uploadedFile);

                $user->setPicture($filename);
            }

            $user->setRoles(['ROLE_USER']);

            $user->setPassword('default');

            $user->setCreatedBy($security->getUser()->getEmail());

            if (!$userService->checkIfUserExists($user->getEmail())) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('list');
            } else {
                $this->addFlash('error', 'Email already registered in database!');
            }
        }

        return $this->render('user/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(int $id, Request $request, Security $security)
    {
        if (!$security->getUser()) {
            $this->addFlash('error', 'User should be logged in order to delete a team member!');

            return $this->redirectToRoute('app_login');
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw new \Exception('No user found for id ' . $id);
        }

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('list');
    }

    /**
     * @Route("/resetPassword/{id}", name="resetPassword")
     */
    public function resetPassword(int $id, Request $request, UserPasswordEncoderInterface $passwordEncoder, Security $security)
    {
        if (!$security->getUser()) {
            $this->addFlash('error', 'User should be logged in to order to reset password!');

            return $this->redirectToRoute('app_login');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->find($id);

        if (empty($user)) {
            throw new \Exception('User not found!');
        }

        $form = $this->createForm(ResetPasswordType::class, $user);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form['newPassword']->isValid()) {
            if ($user->getEmail() === $form['email']->getData()) {
                $oldEncodedPassword = $passwordEncoder->encodePassword($user, $form['oldPassword']->getData());

                if ($user->getPassword() === $oldEncodedPassword) {
                    $newEncodedPassword = $passwordEncoder->encodePassword($user, $form['newPassword']->getData());
                    $user->setPassword($newEncodedPassword);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Password updated!');

                    return $this->redirectToRoute('app_login');
                }

                $this->addFlash('error', 'Invalid credentials!');
            }
        }

        return $this->render('security/resetPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
