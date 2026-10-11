<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class Product extends Model
{
    public const BARCODE_PREFIX = 'RR';
    public const BARCODE_STORE_LENGTH = 3;
    public const BARCODE_PRODUCT_LENGTH = 8;

    protected $fillable = [
        'company_id',
        'store_id',
        'category_id',
        'brand_id',
        'unit_id',
        'supplier_id',
        'product_code',
        'barcode',
        'product_name',
        'part_number',
        'manufacturer',
        'rack_location',
        'hsn_code',
        'gst_percent',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'mechanic_price',
        'mrp',
        'opening_stock',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'image',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'store_id' => 'integer',
            'category_id' => 'integer',
            'brand_id' => 'integer',
            'unit_id' => 'integer',
            'supplier_id' => 'integer',
            'gst_percent' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'mechanic_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'opening_stock' => 'decimal:2',
            'current_stock' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
            'maximum_stock' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Product $product): void {
            $product->ensureBarcode();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItems::class);
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForContext(
        Builder $query,
        int $companyId,
        int $storeId
    ): Builder {
        return $query
            ->where('company_id', $companyId)
            ->where('store_id', $storeId);
    }

    public function scopeForCompany(
        Builder $query,
        int $companyId
    ): Builder {
        return $query->where('company_id', $companyId);
    }

    public function scopeForStore(
        Builder $query,
        int $storeId
    ): Builder {
        return $query->where('store_id', $storeId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeWithBarcode(Builder $query): Builder
    {
        return $query
            ->whereNotNull('barcode')
            ->where('barcode', '!=', '');
    }

    public function scopeWithoutBarcode(Builder $query): Builder
    {
        return $query->where(function (Builder $barcodeQuery): void {
            $barcodeQuery
                ->whereNull('barcode')
                ->orWhere('barcode', '');
        });
    }

    public function scopeBarcode(
        Builder $query,
        ?string $barcode
    ): Builder {
        return $query->where('barcode', trim((string) $barcode));
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($search): void {
            $subQuery
                ->where('product_name', 'like', "%{$search}%")
                ->orWhere('product_code', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('part_number', 'like', "%{$search}%")
                ->orWhere('manufacturer', 'like', "%{$search}%")
                ->orWhere('hsn_code', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Barcode Helpers
    |--------------------------------------------------------------------------
    */

    public static function generateBarcodeFor(
        int $storeId,
        int $productId
    ): string {
        if ($storeId < 1 || $productId < 1) {
            throw new RuntimeException(
                'A valid store ID and saved product ID are required.'
            );
        }

        return self::BARCODE_PREFIX
            . str_pad(
                (string) $storeId,
                self::BARCODE_STORE_LENGTH,
                '0',
                STR_PAD_LEFT
            )
            . str_pad(
                (string) $productId,
                self::BARCODE_PRODUCT_LENGTH,
                '0',
                STR_PAD_LEFT
            );
    }

    public function hasBarcode(): bool
    {
        return trim((string) $this->barcode) !== '';
    }

    public function generatedBarcode(): string
    {
        if (!$this->exists || !$this->getKey()) {
            throw new RuntimeException(
                'Save the product before generating its barcode.'
            );
        }

        return self::generateBarcodeFor(
            (int) $this->store_id,
            (int) $this->getKey()
        );
    }

    public function ensureBarcode(): string
    {
        if ($this->hasBarcode()) {
            return (string) $this->barcode;
        }

        $barcode = $this->generatedBarcode();

        $alreadyUsed = self::query()
            ->where('store_id', $this->store_id)
            ->where('barcode', $barcode)
            ->where(
                $this->getKeyName(),
                '!=',
                $this->getKey()
            )
            ->exists();

        if ($alreadyUsed) {
            throw new RuntimeException(
                "Barcode {$barcode} is already assigned in this branch."
            );
        }

        $this->forceFill([
            'barcode' => $barcode,
        ])->saveQuietly();

        return $barcode;
    }

    /*
    |--------------------------------------------------------------------------
    | Label Helpers
    |--------------------------------------------------------------------------
    */

    public function labelMrp(): float
    {
        $mrp = (float) $this->mrp;

        return $mrp > 0 ? $mrp : $this->retailPrice();
    }

    public function labelPrice(): float
    {
        return $this->retailPrice();
    }

    public function barcodeLabelData(?string $branchName = null): array
    {
        if ($branchName === null) {
            $this->loadMissing('store');
            $branchName = $this->store?->store_name;
        }

        return [
            'product_id' => (int) $this->getKey(),
            'product_code' => (string) $this->product_code,
            'product_name' => (string) $this->product_name,
            'part_number' => $this->part_number,
            'barcode' => $this->ensureBarcode(),
            'branch_name' => $branchName,
            'mrp' => $this->labelMrp(),
            'selling_price' => $this->labelPrice(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Price Helpers
    |--------------------------------------------------------------------------
    */

    public function retailPrice(): float
    {
        return (float) $this->selling_price;
    }

    public function wholesalePrice(): float
    {
        $price = (float) $this->wholesale_price;

        return $price > 0 ? $price : $this->retailPrice();
    }

    public function mechanicPrice(): float
    {
        $price = (float) $this->mechanic_price;

        return $price > 0 ? $price : $this->retailPrice();
    }

    public function priceFor(?string $customerType): float
    {
        return match ($this->normalizeCustomerType($customerType)) {
            'wholesale' => $this->wholesalePrice(),
            'mechanic' => $this->mechanicPrice(),
            default => $this->retailPrice(),
        };
    }

    public function discountAmountFor(?string $customerType): float
    {
        $mrp = (float) $this->mrp;

        if ($mrp <= 0) {
            return 0;
        }

        return round(
            max(0, $mrp - $this->priceFor($customerType)),
            2
        );
    }

    public function discountPercentFor(?string $customerType): float
    {
        $mrp = (float) $this->mrp;

        if ($mrp <= 0) {
            return 0;
        }

        return round(
            ($this->discountAmountFor($customerType) / $mrp) * 100,
            2
        );
    }

    private function normalizeCustomerType(?string $customerType): string
    {
        return strtolower(trim((string) $customerType));
    }
}
