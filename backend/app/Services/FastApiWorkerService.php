<?php

namespace App\Services;

use App\Contracts\AiWorkerServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FastApiWorkerService implements AiWorkerServiceInterface
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ai_worker.url', 'http://127.0.0.1:8001');
        $this->timeout = config('services.ai_worker.timeout', 30);
    }

    /**
     * @inheritDoc
     */
    public function processEvidence(string $imagePath): array
    {
        try {
            // Verifica si el archivo existe
            if (!file_exists($imagePath)) {
                return [
                    'success' => false,
                    'error' => 'La imagen no existe localmente.',
                ];
            }

            // Realiza la petición asíncrona (timeout configurado) usando HTTP Client de Laravel
            $response = Http::timeout($this->timeout)
                ->attach(
                    'file', file_get_contents($imagePath), basename($imagePath)
                )
                ->post("{$this->baseUrl}/api/v1/ocr/process-evidence");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI Worker retornó un error HTTP: ' . $response->status(), [
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'El microservicio falló con código ' . $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Excepción al conectar con AI Worker: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Error de conexión con el servicio de IA.',
            ];
        }
    }
}
