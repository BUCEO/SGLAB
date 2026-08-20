<?php
// app/Controllers/EquipoController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Equipo;
use App\Models\EquipoRepository;
use App\Services\ValidationService;
use App\Core\Response;

class EquipoController
{
    public function __construct(
        private EquipoRepository $equipoRepo,
        private ValidationService $validator
    ) {}
    
    #[Route('/api/equipos', 'POST')]
    public function registrar(array $requestData): Response
    {
        // Validación con el servicio
        $errors = $this->validator->validate($requestData, [
            'num_serie' => 'required|string|max:50',
            'nombre' => 'required|string|max:100',
            'laboratorio' => 'required|string|max:50',
            'estado' => 'sometimes|string|in:Operativo,En Reparación,Dañado,Fuera de Servicio'
        ]);
        
        if (!empty($errors)) {
            return Response::json(['errors' => $errors], 400);
        }
        
        try {
            $equipo = new Equipo(
                null,
                $requestData['num_serie'],
                $requestData['nombre'],
                $requestData['estado'] ?? 'Operativo',
                $requestData['descripcion'] ?? null,
                $requestData['laboratorio'],
                new \DateTimeImmutable(),
                null
            );
            
            $equipoRegistrado = $this->equipoRepo->registrar($equipo);
            
            return Response::json([
                'message' => 'Equipo registrado',
                'data' => $equipoRegistrado
            ], 201);
            
        } catch (\PDOException $e) {
            return Response::json([
                'error' => 'Error al registrar equipo',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/api/equipos/{estado}', 'GET')]
    public function listarPorEstado(string $estado): Response
    {
        $equipos = $this->equipoRepo->listarPorEstado($estado);
        
        return Response::json([
            'count' => count($equipos),
            'data' => $equipos
        ]);
    }
}