<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'company_code',
        'owner_name',
        'gst_number',
        'pan_number',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'district',
        'state',
        'country',
        'pincode',
        'logo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function godowns()
    {
        return $this->hasMany(Godown::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function generateCompanyCode(
        string $companyName
    ): string {

    /*
    |--------------------------------------------------------------------------
    | Clean Company Name
    |--------------------------------------------------------------------------
    */

        $words = preg_split(
            '/\s+/',
            trim($companyName)
        );

        $words = array_values(
            array_filter($words)
        );


    /*
    |--------------------------------------------------------------------------
    | Generate Base Code
    |--------------------------------------------------------------------------
    |
    | Multiple words:
    | New Suban Motors -> NSM
    |
    | Single word:
    | Suban -> SUB
    |
    */

        if (count($words) >= 2) {

            $baseCode = '';

            foreach ($words as $word) {

                $cleanWord = preg_replace(
                    '/[^A-Za-z0-9]/',
                    '',
                    $word
                );

                if ($cleanWord !== '') {
                    $baseCode .= strtoupper(
                        substr($cleanWord, 0, 1)
                    );
                }
            }
        } else {

            $cleanName = preg_replace(
                '/[^A-Za-z0-9]/',
                '',
                $companyName
            );

            $baseCode = strtoupper(
                substr($cleanName, 0, 3)
            );
        }


    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    */

        if ($baseCode === '') {
            $baseCode = 'COM';
        }


    /*
    |--------------------------------------------------------------------------
    | Limit Base Length
    |--------------------------------------------------------------------------
    */

        $baseCode = substr(
            $baseCode,
            0,
            6
        );


    /*
    |--------------------------------------------------------------------------
    | Find Unique Code
    |--------------------------------------------------------------------------
    */

        $code = $baseCode;

        $counter = 2;

        while (
            self::withTrashed()
            ->where('company_code', $code)
            ->exists()
        ) {

            $code =
                $baseCode .
                $counter;

            $counter++;
        }


        return $code;
    }
}
