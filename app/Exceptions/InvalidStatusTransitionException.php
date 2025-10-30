<?php

namespace App\Exceptions;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    /**
     * Current status of the entity.
     *
     * @var string
     */
    public string $currentStatus;

    /**
     * Target status that was attempted.
     *
     * @var string
     */
    public string $targetStatus;

    /**
     * Entity type (e.g., 'SalesOrder', 'PurchaseOrder').
     *
     * @var string|null
     */
    public ?string $entityType;

    /**
     * Create a new exception instance.
     *
     * @param string $currentStatus
     * @param string $targetStatus
     * @param string|null $entityType
     * @param string|null $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $currentStatus,
        string $targetStatus,
        ?string $entityType = null,
        ?string $message = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->currentStatus = $currentStatus;
        $this->targetStatus = $targetStatus;
        $this->entityType = $entityType;

        // Build message if not provided
        if ($message === null) {
            $entityPrefix = $entityType ? "{$entityType}: " : '';
            $message = "{$entityPrefix}Cannot transition from status '{$currentStatus}' to '{$targetStatus}'";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the current status.
     *
     * @return string
     */
    public function getCurrentStatus(): string
    {
        return $this->currentStatus;
    }

    /**
     * Get the target status.
     *
     * @return string
     */
    public function getTargetStatus(): string
    {
        return $this->targetStatus;
    }

    /**
     * Get the entity type.
     *
     * @return string|null
     */
    public function getEntityType(): ?string
    {
        return $this->entityType;
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
                'current_status' => $this->currentStatus,
                'target_status' => $this->targetStatus,
                'entity_type' => $this->entityType,
            ], 422);
        }

        return response()->view('errors.invalid-status-transition', [
            'message' => $this->getMessage(),
            'currentStatus' => $this->currentStatus,
            'targetStatus' => $this->targetStatus,
            'entityType' => $this->entityType,
        ], 422);
    }
}
