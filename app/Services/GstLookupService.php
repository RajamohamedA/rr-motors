<?php

namespace App\Services;

use App\Contracts\GstProviderInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class GstLookupService
{
    public function __construct(
        private readonly GstProviderInterface $provider
    ) {}


    /*
    |--------------------------------------------------------------------------
    | GST Lookup
    |--------------------------------------------------------------------------
    */

    public function lookup(
        string $gstNumber
    ): array {

        $gstNumber =
            strtoupper(
                trim(
                    $gstNumber
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/',
                $gstNumber
            )
        ) {

            throw new InvalidArgumentException(
                'Invalid GST number.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cache Key
        |--------------------------------------------------------------------------
        */

        $cacheKey =
            'gst_lookup:'
            .
            $gstNumber;


        /*
        |--------------------------------------------------------------------------
        | Cache Disabled
        |--------------------------------------------------------------------------
        */

        if (
            !config(
                'gst.cache.enabled',
                true
            )
        ) {

            return $this->fetch(
                $gstNumber
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cached Lookup
        |--------------------------------------------------------------------------
        */

        return Cache::remember(
            $cacheKey,
            (int) config(
                'gst.cache.ttl',
                86400
            ),
            fn() =>
            $this->fetch(
                $gstNumber
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Fetch Provider
    |--------------------------------------------------------------------------
    */

    private function fetch(
        string $gstNumber
    ): array {

        Log::info(
            'GST lookup started.',
            [
                'gst_number' =>
                $gstNumber,

                'provider' =>
                config(
                    'gst.provider'
                ),
            ]
        );


        try {

            $data =
                $this->provider
                ->lookup(
                    $gstNumber
                );


            /*
            |--------------------------------------------------------------------------
            | Normalize Required Structure
            |--------------------------------------------------------------------------
            */

            $result = [

                'gst_number' =>
                strtoupper(
                    $data['gst_number']
                        ??
                        $gstNumber
                ),

                'legal_name' =>
                $data['legal_name']
                    ?? null,

                'trade_name' =>
                $data['trade_name']
                    ?? null,

                'pan_number' =>
                strtoupper(
                    $data['pan_number']
                        ??
                        substr(
                            $gstNumber,
                            2,
                            10
                        )
                ),

                'status' =>
                $data['status']
                    ?? null,

                'address' => [

                    'address_line' =>
                    data_get(
                        $data,
                        'address.address_line'
                    ),

                    'city' =>
                    data_get(
                        $data,
                        'address.city'
                    ),

                    'state' =>
                    data_get(
                        $data,
                        'address.state'
                    ),

                    'pincode' =>
                    data_get(
                        $data,
                        'address.pincode'
                    ),

                    'country' =>
                    data_get(
                        $data,
                        'address.country',
                        'India'
                    ),
                ],
            ];


            Log::info(
                'GST lookup completed.',
                [
                    'gst_number' =>
                    $gstNumber,

                    'status' =>
                    $result['status'],
                ]
            );


            return $result;
        } catch (
            \Throwable $exception
        ) {

            Log::warning(
                'GST lookup failed.',
                [
                    'gst_number' =>
                    $gstNumber,

                    'provider' =>
                    config(
                        'gst.provider'
                    ),

                    'message' =>
                    $exception->getMessage(),
                ]
            );


            throw $exception;
        }
    }
}
