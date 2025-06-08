<?php

/**
 * Account controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\AccountType;
use App\Form\Type\ChangePasswordType;
use App\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class AccountController.
 */
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UserServiceInterface $userService User service
     */
    public function __construct(private readonly UserServiceInterface $userService)
    {
    }

    /**
     * Index action.
     *
     * @return Response
     */
    #[Route(
        '/account',
        name: 'account_index',
        methods: 'GET'
    )]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $recipes = $this->userService->getRecipesByUser($user);
        return $this->render('account/index.html.twig', [
            'user' => $user,
            'recipes' => $recipes,
        ]);
    }

    /**
     * Edit action.
     *
     * @param Request              $request     HTTP request
     * @param UserServiceInterface $userService User service interface
     *
     * @return Response
     */
    #[Route(
        '/account/edit',
        name: 'account_edit',
        methods: 'GET|POST'
    )]
    public function edit(Request $request, UserServiceInterface $userService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(AccountType::class, $user, ['method' => 'POST']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->save($user);
            $this->addFlash('success', 'message.edited_successfully');

            return $this->redirectToRoute('account_index');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Change password action.
     *
     * @param Request                     $request        Request
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param EntityManagerInterface      $entityManager  Entity manager
     *
     * @return Response HTTP response
     */
    #[Route(
        'account/change-password',
        name: 'account_change_password'
    )]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            $entityManager->flush();

            $this->addFlash('success', 'message.passwordChanged');

            return $this->redirectToRoute('account_index');
        }

        return $this->render('account/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
