<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Unit;

use Illuminate\Database\Seeder;


class DefaultUnitSeeder extends Seeder
{
    /*
    |--------------------------------------------------------------------------
    | Default Units
    |--------------------------------------------------------------------------
    */

    private array $units = [

        [
            'unit_name' =>
            'Numbers',

            'short_name' =>
            'Nos',

            'description' =>
            'Individual countable items.',
        ],

        [
            'unit_name' =>
            'Pieces',

            'short_name' =>
            'Pcs',

            'description' =>
            'Individual pieces.',
        ],

        [
            'unit_name' =>
            'Set',

            'short_name' =>
            'Set',

            'description' =>
            'A complete set of items.',
        ],

        [
            'unit_name' =>
            'Pair',

            'short_name' =>
            'Pair',

            'description' =>
            'Two matching items.',
        ],

        [
            'unit_name' =>
            'Box',

            'short_name' =>
            'Box',

            'description' =>
            'Items packed in a box.',
        ],

        [
            'unit_name' =>
            'Pack',

            'short_name' =>
            'Pack',

            'description' =>
            'Items sold as a pack.',
        ],

        [
            'unit_name' =>
            'Packet',

            'short_name' =>
            'Pkt',

            'description' =>
            'Items packed in a packet.',
        ],

        [
            'unit_name' =>
            'Bundle',

            'short_name' =>
            'Bdl',

            'description' =>
            'Items grouped as a bundle.',
        ],

        [
            'unit_name' =>
            'Dozen',

            'short_name' =>
            'Dzn',

            'description' =>
            'Twelve items.',
        ],

        [
            'unit_name' =>
            'Kilogram',

            'short_name' =>
            'Kg',

            'description' =>
            'Weight in kilograms.',
        ],

        [
            'unit_name' =>
            'Gram',

            'short_name' =>
            'Gm',

            'description' =>
            'Weight in grams.',
        ],

        [
            'unit_name' =>
            'Litre',

            'short_name' =>
            'Ltr',

            'description' =>
            'Liquid volume in litres.',
        ],

        [
            'unit_name' =>
            'Millilitre',

            'short_name' =>
            'Ml',

            'description' =>
            'Liquid volume in millilitres.',
        ],

        [
            'unit_name' =>
            'Metre',

            'short_name' =>
            'Mtr',

            'description' =>
            'Length in metres.',
        ],

        [
            'unit_name' =>
            'Centimetre',

            'short_name' =>
            'Cm',

            'description' =>
            'Length in centimetres.',
        ],

        [
            'unit_name' =>
            'Millimetre',

            'short_name' =>
            'Mm',

            'description' =>
            'Length in millimetres.',
        ],

        [
            'unit_name' =>
            'Feet',

            'short_name' =>
            'Ft',

            'description' =>
            'Length in feet.',
        ],

        [
            'unit_name' =>
            'Inch',

            'short_name' =>
            'In',

            'description' =>
            'Length in inches.',
        ],

        [
            'unit_name' =>
            'Square Feet',

            'short_name' =>
            'SqFt',

            'description' =>
            'Area in square feet.',
        ],

        [
            'unit_name' =>
            'Square Metre',

            'short_name' =>
            'SqM',

            'description' =>
            'Area in square metres.',
        ],

        [
            'unit_name' =>
            'Roll',

            'short_name' =>
            'Roll',

            'description' =>
            'Material supplied in rolls.',
        ],

        [
            'unit_name' =>
            'Coil',

            'short_name' =>
            'Coil',

            'description' =>
            'Wire or material supplied in coils.',
        ],

        [
            'unit_name' =>
            'Sheet',

            'short_name' =>
            'Sheet',

            'description' =>
            'Material supplied as sheets.',
        ],

        [
            'unit_name' =>
            'Bag',

            'short_name' =>
            'Bag',

            'description' =>
            'Material supplied in bags.',
        ],

        [
            'unit_name' =>
            'Bottle',

            'short_name' =>
            'Btl',

            'description' =>
            'Liquid or material supplied in bottles.',
        ],

        [
            'unit_name' =>
            'Can',

            'short_name' =>
            'Can',

            'description' =>
            'Material supplied in cans.',
        ],

        [
            'unit_name' =>
            'Drum',

            'short_name' =>
            'Drum',

            'description' =>
            'Material supplied in drums.',
        ],

        [
            'unit_name' =>
            'Tube',

            'short_name' =>
            'Tube',

            'description' =>
            'Material supplied in tubes.',
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | Run
    |--------------------------------------------------------------------------
    */

    public function run(): void
    {
        $stores =
            Store::query()
            ->where(
                'status',
                true
            )
            ->get([
                'id',
                'company_id',
            ]);


        foreach (
            $stores as $store
        ) {

            foreach (
                $this->units as $unit
            ) {

                Unit::query()
                    ->updateOrCreate(
                        [
                            'store_id' =>
                            $store->id,

                            'short_name' =>
                            $unit['short_name'],
                        ],
                        [
                            'company_id' =>
                            $store->company_id,

                            'unit_name' =>
                            $unit['unit_name'],

                            'description' =>
                            $unit['description'],

                            'is_system_unit' =>
                            true,

                            'status' =>
                            true,
                        ]
                    );
            }
        }
    }
}
