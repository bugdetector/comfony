<?php

namespace App\Controller;

use App\Theme\BaseTheme\AuthLayoutController;
use App\Theme\ThemeHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AuthLayoutController
{
    public function __construct(
        public ThemeHelper $theme,
        protected TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($theme, $translator);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage', status: Response::HTTP_SEE_OTHER);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'title' => $this->translator->trans('Login'),
            'last_username' => $lastUsername, 'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(Security $security): Response
    {
        $response = $security->logout();
        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }
}
