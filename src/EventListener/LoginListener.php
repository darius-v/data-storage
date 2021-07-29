<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $passwordHasherFactory;
    private $em;

    public function __construct(PasswordHasherFactoryInterface $passwordHasherFactory, EntityManagerInterface $em)
    {
        $this->passwordHasherFactory = $passwordHasherFactory;
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $token = $event->getAuthenticationToken();

        // Migrate the user to the new hashing algorithm if is using the legacy one
        if ($user->hasLegacyPassword()) {
            // Credentials can be retrieved thanks to the false value of
            // the erase_credentials parameter in security.yml
            $plainPassword = $token->getCredentials();
            file_put_contents('darius.txt', 'test'.$plainPassword, FILE_APPEND); // why null?
//            file_put_contents('darius.txt', 'test'.get_class($token), FILE_APPEND);
//            echo get_class($token);

//            $user->setOldPassword(null);
//            $hasher = $this->passwordHasherFactory->getPasswordHasher($user);
//
//            $user->setPassword($hasher->hash($plainPassword));
//
//            $this->em->persist($user);
//            $this->em->flush();
        }

        $token->eraseCredentials();
    }
}