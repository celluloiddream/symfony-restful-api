<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
    #[Route('/', name: 'app_api')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to Symfony RESTful API',
            'version' => '0.0.4'
        ]);
    }

    #[Route('/health', name: 'app_api_health', methods: ['GET'])]
    public function health(EntityManagerInterface $em): JsonResponse
    {
        try {
            // 1. Intenta obtener la conexión y ejecutar una consulta simple
            $connection = $em->getConnection();
            $connection->executeQuery('SELECT 1');

            // 2. Si todo va bien, devuelve un estado saludable
            $data = [
                'status' => 'ok',
                'database' => 'healthy',
                'timestamp' => new \DateTime(),
            ];

            return $this->json($data);

        } catch (\Exception $e) {
            // 3. Si hay un error (ej. la BD no responde), devuelve un error de servidor
            $data = [
                'status' => 'error',
                'database' => 'unhealthy',
                'error' => 'Could not connect to the database.',
                'timestamp' => new \DateTime(),
            ];

            return $this->json($data, 503); // 503 Service Unavailable
        }
    }
}
