<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class CoinMarketCapService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('CMC_API_KEY');
        $this->baseUrl = env('CMC_BASE_URL');
    }

    /**
     * Obtiene los precios más recientes de criptomonedas específicas
     */
    public function getLatestQuotes(array $symbols)
    {
        try {
            $response = Http::withHeaders([
                'X-CMC_PRO_API_KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/v1/cryptocurrency/quotes/latest", [
                'symbol' => implode(',', $symbols),
                'convert' => 'USD'
            ]);

            if ($response->successful()) {
                return $response->json()['data'];
            }

            throw new Exception("Error en la API de CoinMarketCap: " . $response->status());
        } catch (Exception $e) {
            logger("Error CMC Service: " . $e->getMessage());
            return null;
        }
    }
}