<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * @Route("/logout", name="logout", methods={"POST"})
     */
    public function logout()
    {
    }

    /**
     * POST - to not give error when running from curl
     * @Route("/after-logout", name="after_logout", methods={"GET", "POST"})
     */
    public function afterLogout(): JsonResponse
    {
        return $this->json([]);
    }
}
