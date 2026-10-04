<?php

namespace App\Contracts;

interface AiWorkerServiceInterface
{
    /**
     * Procesa una evidencia usando el microservicio FastAPI.
     *
     * @param string $imagePath Ruta absoluta o relativa de la imagen.
     * @return array
     */
    public function processEvidence(string $imagePath): array;
}
