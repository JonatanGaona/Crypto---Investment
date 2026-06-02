<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CoinMarketCapService;
use App\Models\Cryptocurrency;
use App\Models\PriceHistory;
use Carbon\Carbon;

class CryptoController extends Controller
{
    protected $cmcService;

    public function __construct(CoinMarketCapService $cmcService)
    {
        $this->cmcService = $cmcService;
    }

    public function index()
    {
        return view('index');
    }

    public function getUpdates()
    {
        $symbolsToTrack = ['BTC', 'ETH', 'BNB', 'SOL'];

        $apiData = $this->cmcService->getLatestQuotes($symbolsToTrack);

        if (!$apiData) {
            return response()->json(['error' => 'No se pudieron obtener datos de la API'], 500);
        }

        $formattedData = [];

        foreach ($symbolsToTrack as $symbol) {
            if (isset($apiData[$symbol])) {
                $cryptoInfo = $apiData[$symbol];


                $crypto = Cryptocurrency::firstOrCreate(
                    ['cmc_id' => $cryptoInfo['id']],
                    [
                        'name' => $cryptoInfo['name'],
                        'symbol' => $cryptoInfo['symbol'],
                        'slug' => $cryptoInfo['slug']
                    ]
                );

                $history = PriceHistory::create([
                    'cryptocurrency_id' => $crypto->id,
                    'price' => $cryptoInfo['quote']['USD']['price'],
                    'percent_change_24h' => $cryptoInfo['quote']['USD']['percent_change_24h'],
                    'volume_24h' => $cryptoInfo['quote']['USD']['volume_24h'],
                    'recorded_at' => Carbon::now()
                ]);

                $formattedData[] = [
                    'id' => $crypto->id,
                    'name' => $crypto->name,
                    'symbol' => $crypto->symbol,
                    'price' => round($history->price, 2),
                    'change' => round($history->percent_change_24h, 2),
                    'volume' => round($history->volume_24h, 2),
                ];
            }
        }

        return response()->json($formattedData);
    }

    public function getHistory(Request $request)
    {
        $cryptoId = $request->query('crypto_id');
        $from = $request->query('from');
        $to = $request->query('to');

        $history = PriceHistory::where('cryptocurrency_id', $cryptoId)
            ->whereBetween('recorded_at', [
                Carbon::parse($from)->startOfDay(), 
                Carbon::parse($to)->endOfDay()
            ])
            ->orderBy('recorded_at', 'asc')
            ->get();

        $labels = $history->map(function($item) {
            return \Carbon\Carbon::parse($item->recorded_at)->timezone('America/Bogota')->format('d/m H:i');
        });

        $prices = $history->map(function($item) {
            return (float) $item->price;
        });

        return response()->json([
            'labels' => $labels,
            'prices' => $prices
        ]);
    }
}