<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    /**
     * Array of items with insufficient stock.
     *
     * @var array
     */
    public array $unavailableItems;

    /**
     * Create a new exception instance.
     *
     * @param array $unavailableItems
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        array $unavailableItems,
        string $message = 'Insufficient stock for requested items',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->unavailableItems = $unavailableItems;
        
        // Build detailed message
        if (!empty($unavailableItems)) {
            $details = [];
            foreach ($unavailableItems as $item) {
                $productName = $item['product_name'] ?? $item['product_sku'] ?? "Product ID {$item['product_id']}";
                $details[] = "{$productName}: Required {$item['required']}, Available {$item['available']}";
            }
            $message .= ': ' . implode('; ', $details);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the unavailable items.
     *
     * @return array
     */
    public function getUnavailableItems(): array
    {
        return $this->unavailableItems;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'unavailable_items' => $this->unavailableItems,
            ], 422);
        }

        return response()->view('errors.insufficient-stock', [
            'message' => $this->getMessage(),
            'unavailableItems' => $this->unavailableItems,
        ], 422);
    }
}
