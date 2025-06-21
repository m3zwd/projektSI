<?php

/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\BlockUserType;
use App\Form\Type\ChangeRoleType;
use App\Form\Type\ChangeUserPasswordType;
use App\Repository\UserRepository;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController.
 */
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UserServiceInterface $userService    User service
     * @param UserRepository       $userRepository User repository
     * @param TranslatorInterface  $translator     Translator
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly UserRepository $userRepository, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        '/user/',
        name: 'user_index',
        methods: 'GET'
    )]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->userService->getPaginatedList($page);

        return $this->render('user/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/user/{id}',
        name: 'user_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function view(User $user): Response
    {
        /*
         * Blokada akcji na swoim własnym koncie.
         */
        if ($user === $this->getUser()) {
            throw new AccessDeniedException('Access denied.');
        }

        $recipes = $this->userService->getRecipesByUser($user);

        return $this->render(
            'user/view.html.twig',
            [
                'user' => $user,
                'recipes' => $recipes,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/user/{id}/delete',
        name: 'user_delete',
        requirements: ['id' => '\d+'],
        methods: 'GET|DELETE'
    )]
    public function delete(Request $request, User $user): Response
    {
        if ($user === $this->getUser()) {
            throw new AccessDeniedException('Access denied.');
        }

        if (!$this->userService->canBeDeleted($user)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.user_has_recipes')
            );

            return $this->redirectToRoute('user_index');
        }

        $form = $this->createForm(FormType::class, $user, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('user_delete', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->delete($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/delete.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Change user's password.
     *
     * @param Request                     $request        HTTP request
     * @param User                        $user           User entity
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     *
     * @return Response HTTP response
     */
    #[Route(
        '/user/{id}/change-password',
        name: 'user_change_password',
        methods: 'GET|POST'
    )]
    public function changePassword(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($user === $this->getUser()) {
            throw new AccessDeniedException('Access denied.');
        }

        $form = $this->createForm(ChangeUserPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $this->userService->save($user);

            $this->addFlash('success', $this->translator->trans('message.password_changed'));

            return $this->redirectToRoute('user_view', ['id' => $user->getId()]);
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Change user's role.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/user/{id}/change-role',
        name: 'user_change_role',
        methods: 'GET|POST'
    )]
    public function changeRole(Request $request, User $user): Response
    {
        if ($user === $this->getUser()) {
            throw new AccessDeniedException('Access denied.');
        }

        $form = $this->createForm(ChangeRoleType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * Pobranie danych wejściowych.
             *
             * $isAdmin - wartość z checkboxa (true jeśli zaznaczony, false jeśli nie)
             * $currentRoles - tablica ról użytkownika z bazy (['ROLE_USER'] lub ['ROLE_USER', 'ROLE_ADMIN'])
             * $hasAdminRole - sprawdzenie, czy użytkownik ma rolę admina
             */
            $isAdmin = $form->get('isAdmin')->getData();
            $currentRoles = $user->getRoles();
            $hasAdminRole = in_array('ROLE_ADMIN', $currentRoles, true);

            /*
             * Odbieranie uprawnień.
             *
             * Jeśli użytkownik ma rolę admina i odznaczę admina:
             * to sprawdzam, ilu adminów jest w systemie.
             * Jeśli użytkownik jest ostatnim adminem, blokuje operację i wyswietla komunikat.
             * Jeśli nie jest, to ustawiam mu tylko ROLE_USER, czyli odbieram admina.
             *
             * Jeśli użytkownik nie ma roli admina i zaznaczę admina:
             * nadaje mu rolę admina.
             */
            if (!$isAdmin && $hasAdminRole) {
                $adminCount = $this->userRepository->countAdmins();

                if ($adminCount <= 1) {
                    $this->addFlash('warning', $this->translator->trans('message.last_admin_error'));

                    return $this->redirectToRoute('user_index');
                }

                $user->setRoles(['ROLE_USER']);
            } elseif ($isAdmin && !$hasAdminRole) {
                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            }

            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.role_changed')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/change_role.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Block user.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/user/{id}/block',
        name: 'user_block',
        methods: 'GET|POST'
    )]
    public function block(Request $request, User $user): Response
    {
        if ($user === $this->getUser()) {
            throw new AccessDeniedException('Access denied.');
        }

        $form = $this->createForm(BlockUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * Pobranie danych wejściowych.
             *
             * $isBlocked - wartość z checkboxa (true jeśli zaznaczony, false jeśli nie)
             * $currentRoles - tablica ról użytkownika z bazy (['ROLE_USER'] lub ['ROLE_USER', 'ROLE_ADMIN'])
             * $hasAdminRole - sprawdzenie, czy użytkownik ma rolę admina.
             */
            $isBlocked = $form->get('isBlocked')->getData();
            $currentRoles = $user->getRoles();
            $hasAdminRole = in_array('ROLE_ADMIN', $currentRoles, true);

            /*
             * Blokowanie konta.
             *
             * Jeśli użytkownik ma rolę admina i go zablokuję:
             * to sprawdzam, ilu adminów jest w systemie.
             * Jeśli użytkownik jest ostatnim adminem, blokuje operację i wyswietla komunikat.
             * Jeśli nie jest lub jest zwyklym uzytkownikiem, to blokuje jego konto.
             */
            if ($hasAdminRole && $isBlocked) {
                $adminCount = $this->userRepository->countAdmins();

                if ($adminCount <= 1) {
                    $this->addFlash('warning', $this->translator->trans('message.last_admin_error'));

                    return $this->redirectToRoute('user_index');
                }
            }

            $user->setIsBlocked($isBlocked);

            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans($isBlocked ? 'message.user_blocked' : 'message.user_unblocked')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/block.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
