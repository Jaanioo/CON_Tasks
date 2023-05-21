<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    public function getAllUsers(): array
    {
        $response = $this->httpClient->request('GET', 'https://gorest.co.in/public/v2/users/');

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getUserById(int $id): array
    {
        $response = $this->httpClient->request('GET', 'https://gorest.co.in/public/v2/users/' . $id);

        if ($response->getStatusCode() === 404) {
            throw new NotFoundHttpException('User not found');
        }

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function changeUser(int $id, HttpClientInterface $httpClient, Request $request)
    {
        $data = json_decode($request->getContent());

        $headers = [
            'Authorization' => 'Bearer 7ef181fcc66d75b0834b203c751a25dcb0abd5db667a2e29618ef036e4135c6f',
            'Content-Type' => 'application/json'
        ];

        $requestData = [
            'name' => $data->name,
            'email' => $data->email,
            'gender' => $data->gender,
            'status' => $data->status,
        ];

        $response = $httpClient->request('PUT', 'https://gorest.co.in/public/v2/users/' . $id, [
            'headers' => $headers,
            'json' => $requestData,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            return $response->toArray();
        }

        if ($statusCode === 404) {
            throw new NotFoundHttpException('User not found');
        }

        return new JsonResponse(['error' => 'Failed to update user'], $statusCode);
    }

}