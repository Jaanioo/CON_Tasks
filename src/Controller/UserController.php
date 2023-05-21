<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    #[Route('/list', name: 'users_index', methods: ['GET'])]
    public function listOfUsers(): Response
    {
        $data = $this->userService->getAllUsers();
//        return new JsonResponse($data);
        return $this->render('list.html.twig', ['data' => $data]);
    }

    #[Route('/user/{id}')]
    public function showUser(int $id): Response
    {
        try {
            $data = $this->userService->getUserById($id);
            return $this->render('user.html.twig', ['data' => $data]);
        } catch (NotFoundHttpException $e) {
            throw $this->createNotFoundException('User not found', $e);
        }
    }

    #[Route('/edit/{id}', methods: ['PUT'])]
    public function updateUser(int $id, Request $request, HttpClientInterface $httpClient): JsonResponse
    {

        try {
            $this->userService->changeUser($id, $httpClient, $request);
        } catch (NotFoundHttpException $e) {
            throw $this->createNotFoundException('User not found', $e);
        }
        return $this->json(
            'Edited a task successfully. ',
            Response::HTTP_OK
        );

    }


    #[Route('/users/search', methods: ['GET'])]
    public function searchUsers(Request $request, HttpClientInterface $httpClient): JsonResponse
    {
        $query = $request->query->get('query');

        $headers = [
            'Authorization' => 'Bearer 7ef181fcc66d75b0834b203c751a25dcb0abd5db667a2e29618ef036e4135c6f'
        ];
        $params = ['name', 'email', 'gender', 'status'];

        foreach ($params as $param) {
            if ($query === 'male' && $param !== 'gender') {
                continue;
            }
            $response = $httpClient->request('GET', 'https://gorest.co.in/public/v2/users', [
                'headers' => $headers,
                'query' => [
                    $param => $query,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            if (!empty($content)) {
                break;
            }
        }

        if ($statusCode === 200) {
            return new JsonResponse($content);
        }

        return new JsonResponse(['error' => 'Failed to search users'], $statusCode);
    }
}
