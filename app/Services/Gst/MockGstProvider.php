<?php

namespace App\Services\Gst;

use App\Contracts\GstProviderInterface;
use InvalidArgumentException;

class MockGstProvider implements GstProviderInterface
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


        /*
        |--------------------------------------------------------------------------
        | Validate GSTIN
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
        | Extract PAN From GST
        |--------------------------------------------------------------------------
        |
        | GSTIN:
        |
        | 33ABCDE1234F1Z5
        |
        | PAN:
        |
        | ABCDE1234F
        |
        */

        $panNumber =
            substr(
                $gstNumber,
                2,
                10
            );


        /*
        |--------------------------------------------------------------------------
        | Development Data
        |--------------------------------------------------------------------------
        |
        | This is fake development data only.
        |
        */

        return [

            'gst_number' =>
            $gstNumber,

            'legal_name' =>
            'RR TEST CUSTOMER PRIVATE LIMITED',

            'trade_name' =>
            'RR TEST CUSTOMER',

            'pan_number' =>
            $panNumber,

            'status' =>
            'Active',

            'address' => [

                'address_line' =>
                '10 Main Road',

                'city' =>
                'Thiruthuraipoondi',

                'state' =>
                'Tamil Nadu',

                'pincode' =>
                '614713',

                'country' =>
                'India',
            ],
        ];
    }
}
