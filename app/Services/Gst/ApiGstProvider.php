<?php

namespace App\Services\Gst;

use App\Contracts\GstProviderInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApiGstProvider implements GstProviderInterface
{
    public function lookup(
        string $gstNumber
    ): array {

        $gstNumber =
            strtoupper(
                trim(
                    $gstNumber
                )
            );


        $url =
            config(
                'gst.api.url'
            );


        $apiKey =
            config(
                'gst.api.key'
            );


        $timeout =
            (int) config(
                'gst.api.timeout',
                10
            );


        /*
        |--------------------------------------------------------------------------
        | Provider Configuration
        |--------------------------------------------------------------------------
        */

        if (
            !$url ||
            !$apiKey
        ) {

            throw new RuntimeException(
                'GST API provider is not configured.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Call Provider
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | The exact URL, headers and response mapping will depend on
        | whichever GST API provider we choose.
        |
        */

        $response =
            Http::timeout(
                $timeout
            )

            ->acceptJson()

            ->withHeaders([
                'Authorization' =>
                'Bearer '
                    .
                    $apiKey,
            ])

            ->get(
                $url,
                [
                    'gstin' =>
                    $gstNumber,
                ]
            );


        if (
            !$response->successful()
        ) {

            throw new RuntimeException(
                'GST provider request failed.'
            );
        }


        $providerData =
            $response->json();


        if (
            empty($providerData)
        ) {

            throw new RuntimeException(
                'GST provider returned an empty response.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Provider Response
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Update these mappings once the actual GST provider is selected.
        |
        */

        return [

            'gst_number' =>
            $gstNumber,

            'legal_name' =>
            $providerData['legal_name']
                ?? null,

            'trade_name' =>
            $providerData['trade_name']
                ?? null,

            'pan_number' =>
            $providerData['pan_number']
                ??
                substr(
                    $gstNumber,
                    2,
                    10
                ),

            'status' =>
            $providerData['status']
                ?? null,

            'address' => [

                'address_line' =>
                $providerData['address']
                    ?? null,

                'city' =>
                $providerData['city']
                    ?? null,

                'state' =>
                $providerData['state']
                    ?? null,

                'pincode' =>
                $providerData['pincode']
                    ?? null,

                'country' =>
                'India',
            ],
        ];
    }
}
