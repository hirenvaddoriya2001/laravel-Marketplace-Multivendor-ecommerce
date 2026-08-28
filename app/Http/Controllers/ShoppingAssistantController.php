<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShoppingAssistantController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required','string','min:2','max:500',],
        ]);

        if (! config('services.gemini.key')) {
            return response()->json([
                'message' =>
                    'The shopping assistant is not configured.',
            ], 503);
        }

        $message = trim($validated['message']);

        $keywords = collect(
            preg_split('/\s+/', Str::lower($message))
        )
            ->map(fn ($word) =>
                preg_replace('/[^a-z0-9-]/', '', $word)
            )
            ->filter(fn ($word) =>
                strlen($word) >= 3
            )
            ->unique()
            ->take(8);

        $productsQuery = Product::query()
            ->where('visibility', true)
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere(
                        'name',
                        'like',
                        "%{$keyword}%"
                    )->orWhere(
                        'summary',
                        'like',
                        "%{$keyword}%"
                    );
                }
            });

        $products = $keywords->isEmpty()
            ? collect()
            : $productsQuery->limit(8)->get();

        // Provide fallback products when keyword search has no result.
        if ($products->isEmpty()) {
            $products = Product::query()
                ->where('visibility', true)
                ->latest()
                ->limit(8)
                ->get();
        }

        $catalog = $products
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'summary' => Str::limit(
                        strip_tags($product->summary),
                        180
                    ),
                    'price' => (float) $product->price,
                    'stock_status' =>
                        $product->stock_status,
                ];
            })
            ->values()
            ->all();

        $prompt = <<<PROMPT
You are a concise shopping assistant for LARAVECOM.

Rules:
- Only recommend products contained in the supplied catalog.
- Never invent a product, price, discount, feature, or stock status.
- Recommend no more than 3 products.
- Briefly explain why each product fits.
- If none of the supplied products match, clearly say so.
- Do not claim that an order was placed.
- Keep the response under 140 words.
- Use plain text, not Markdown tables.

Customer request:
{$message}

Available product catalog:
PROMPT;

        $prompt .= "\n".json_encode(
            $catalog,
            JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
        );

        try {
            $model = config(
                'services.gemini.model',
                'gemini-3.7-flash'
            );

            $response = Http::timeout(25)
                ->retry(2, 300)
                ->withHeaders([
                    'x-goog-api-key' =>
                        config('services.gemini.key'),
                ])
                ->post(
                    "https://generativelanguage.googleapis.com/"
                    ."v1beta/models/{$model}:generateContent",
                    [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.3,
                            'maxOutputTokens' => 300,
                        ],
                    ]
                );

            if ($response->failed()) {
                Log::warning('Gemini request failed', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return response()->json([
                    'message' =>
                        'The assistant is temporarily unavailable.',
                ], 502);
            }

            // $reply = data_get(
            //     $response->json(),
            //     'candidates.0.content.parts.0.text'
            // );

            // if (! is_string($reply) || trim($reply) === '') {
            //     return response()->json([
            //         'message' =>
            //             'The assistant could not create a response.',
            //     ], 502);
            // }
            $responseData = $response->json();

            $parts = data_get(
                $responseData,
                'candidates.0.content.parts',
                []
            );

            $reply = collect($parts)
                ->filter(function ($part) {
                    return is_array($part)
                        && isset($part['text'])
                        && is_string($part['text'])
                        && ! ($part['thought'] ?? false);
                })
                ->pluck('text')
                ->filter()
                ->implode("\n");

            if (trim($reply) === '') {
                Log::warning('Gemini returned no answer text', [
                    'response' => $responseData,
                ]);

                return response()->json([
                    'message' =>
                        'The assistant could not create a response.',
                ], 502);
            }

            return response()->json([
                'reply' => trim($reply),

                // Return real database products separately.
                'products' => $products
                    ->take(3)
                    ->map(fn (Product $product) => [
                        'name' => $product->name,
                        'price' => number_format(
                            (float) $product->price,
                            2
                        ),
                        'url' => route(
                            'products.show',
                            $product
                        ),
                        'in_stock' =>
                            $product->isInStock(),
                    ])
                    ->values(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Shopping assistant error', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' =>
                    'The assistant is temporarily unavailable.',
            ], 500);
        }
    }
}