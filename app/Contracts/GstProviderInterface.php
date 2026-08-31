<?php

namespace App\Contracts;

interface GstProviderInterface
{
    /**
     * Lookup GST information and return normalized data.
     */
    public function lookup(
        string $gstNumber
    ): array;
}
