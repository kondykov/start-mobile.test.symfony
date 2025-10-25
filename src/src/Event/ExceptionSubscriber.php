<?php

namespace App\Event;

use App\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment  $twig,
        private RequestStack $requestStack,  // ← ИСПОЛЬЗУЕМ REQUEST_STACK
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        $isApiRequest = str_contains($request->getPathInfo(), '/api/') ||
            $request->headers->get('Accept') === 'application/json';

        if ($exception instanceof ValidationException) {
            if ($isApiRequest) {
                $response = new JsonResponse([
                    'errors' => $exception->getErrors(),
                ], $exception->getCode());
            } else {
                $session->set('validation_errors', $exception->getErrors());
                $session->set('validation_old_input', $exception->getInputs());

                // Debug: log the errors
                error_log('Validation errors: ' . json_encode($exception->getErrors()));

                $referer = $request->headers->get('referer');
                $response = new RedirectResponse($referer ?? '/');
            }
            $event->setResponse($response);
        }

        if ($exception instanceof NotFoundHttpException) {
            if ($isApiRequest) {
                $response = new JsonResponse([
                    'errors' => $exception->getMessage(),
                ], 404);
            } else {
                $response = new Response(
                    $this->twig->render('common/error.html.twig', [
                        'error' => $exception->getMessage(),
                    ]),
                    404
                );
            }
            $event->setResponse($response);
        }
    }
}
