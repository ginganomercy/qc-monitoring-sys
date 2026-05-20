<?php

namespace App\Services;

/**
 * Base Service Class
 *
 * Provides common functionality for all services.
 * Extend this class for specific service implementations.
 */
abstract class BaseService
{
    /**
     * Get the repository/model for this service.
     */
    abstract protected function getModel(): string;

    /**
     * Log service activity.
     */
    protected function log(string $action, array $context = []): void
    {
        $modelName = $this->getModel();
        activity()
            ->performedOn(new $modelName())
            ->withProperties($context)
            ->log($action);
    }

    /**
     * Execute with database transaction.
     */
    protected function withTransaction(callable $callback, int $attempts = 3): mixed
    {
        return \DB::transaction($callback, $attempts);
    }
}
