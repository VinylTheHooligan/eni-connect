<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
    )
    {}

    public function handle(Request $request, AccessDeniedException $exception): ?Response
    {
        /** @var Session $session */
        $session = $request->getSession();

        $attribute = $exception->getAttributes()[0] ?? null;

        $message = "Vous n'avez pas la permission de consulter cette page.";

        $messages = [
            'EDIT'    => "Vous ne pouvez pas modifier cette sortie.",
            'PUBLISH' => "Vous ne pouvez pas publier cette sortie.",
            'CANCEL'  => "Vous ne pouvez pas annuler cette sortie.",
            'DELETE'  => "Vous ne pouvez pas supprimer cette sortie.",
            'CREATE'  => "Vous ne pouvez pas créer une sortie.",
        ];

        $message = $messages[$attribute] ?? "Vous n'avez pas la permission d'effectuer cette action.";

        $session->getFlashBag()->add('error', $message);

        return new RedirectResponse($this->router->generate('outing_list'));
    }
}   