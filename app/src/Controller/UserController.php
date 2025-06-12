<?php

/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
     * @param UserServiceInterface $userService User service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly TranslatorInterface $translator)
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
        // blokada podglądu konta innego admina
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
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
     * Edit action.
     *
     * @param Request                     $request        HTTP request
     * @param User                        $user           User
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     *
     * @return Response HTTP response
     */
    /*
    #[Route(
        '/user/{id}/edit',
        name: 'user_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        // blokada edycji konta innego admina (wlacznie z tym zalogowanym, bo obsluga edycji konta jest w sekcji Account)
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new AccessDeniedException('Access denied.');
        }

        $form = $this->createForm(
            UserEditType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.password_changed')
                );
            }

            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }
    */

    /**
     * Change user's password.
     *
     * @param Request                     $request
     * @param User                        $user
     * @param UserPasswordHasherInterface $passwordHasher
     *
     * @return Response
     */
    #[Route(
        '/user/{id}/change-password',
        name: 'user_change_password',
        methods: 'GET|POST'
    )]
    public function changePassword(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new AccessDeniedException('Access denied.');
        }

        $form = $this->createFormBuilder()
            ->add('plainPassword', PasswordType::class, [
                'label' => 'label.new_password',
                'required' => true,
                'mapped' => false,
            ])
            ->getForm();

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
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     */
    #[Route(
        '/user/{id}/change-role',
        name: 'user_change_role',
        methods: 'GET|POST'
    )]
    public function changeRole(Request $request, User $user): Response
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new AccessDeniedException('Access denied.');
        }

        $form = $this->createFormBuilder($user)
            ->add('roles', ChoiceType::class, [
                'label' => 'label.roles',
                'choices' => [
                    'label.role_user' => 'ROLE_USER',
                    'label.role_admin' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);

            $this->addFlash('success', $this->translator->trans('message.role_changed'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/change_role.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
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
}
