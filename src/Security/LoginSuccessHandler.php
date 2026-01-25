<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $router;
    private $roleHierarchy;

    public function __construct(RouterInterface $router, RoleHierarchyInterface $roleHierarchy)
    {
        $this->router = $router;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?RedirectResponse
    {
        $user = $token->getUser();
        $roles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        // Check if user has ROLE_ARTISAN or ROLE_ADMIN
        if (in_array('ROLE_ARTISAN', $roles) || in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->router->generate('admin_event_index'));
        }

        // Default redirect for other roles (CLIENT)
        return new RedirectResponse($this->router->generate('app_home'));
    }
}
