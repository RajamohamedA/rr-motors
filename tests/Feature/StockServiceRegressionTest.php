<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Store;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class StockServiceRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $company = Company::create(['company_name' => 'Stock Test', 'company_code' => 'ST', 'status' => true]);
        $store = Store::create(['company_id' => $company->id, 'store_name' => 'Stock Branch', 'store_code' => 'ST1', 'status' => true]);
        $category = DB::table('categories')->insertGetId(['company_id' => $company->id, 'store_id' => $store->id, 'name' => 'Test category']);
        $unit = DB::table('units')->insertGetId(['company_id' => $company->id, 'store_id' => $store->id, 'unit_name' => 'Pieces', 'short_name' => 'pcs']);
        $product = Product::create(['company_id' => $company->id, 'store_id' => $store->id, 'category_id' => $category, 'unit_id' => $unit, 'product_code' => 'TEST-P1', 'product_name' => 'Test product', 'purchase_price' => 100, 'selling_price' => 200, 'status' => true]);
        $godown = Godown::create(['company_id' => $company->id, 'store_id' => $store->id, 'godown_name' => 'Test godown', 'godown_code' => 'G1', 'status' => true]);
        return [$company, $store, $product, $godown, app(StockService::class)];
    }

    public function test_stock_lifecycle_preserves_balances_and_total(): void
    {
        [$company, $store, $product, $godown, $stock] = $this->fixture();
        $stock->increaseStock($product, 20, 'Purchase', $company->id, $store->id, $godown->id, null, 'P1', 100);
        $stock->transferStock($product, 8, $company->id, $store->id, $godown->id, null);
        $this->assertEquals(8, $stock->getStock($company->id, $store->id, null, $product->id));
        $this->assertEquals(12, $stock->getStock($company->id, $store->id, $godown->id, $product->id));
        $this->assertEquals(20, $product->fresh()->current_stock);
        $stock->decreaseStock($product, 3, 'Sale', $company->id, $store->id, null, null, 'S1');
        $stock->increaseStock($product, 1, 'Sales Return', $company->id, $store->id, null, null, 'SR1', 100);
        $stock->decreaseStock($product, 2, 'Purchase Return', $company->id, $store->id, $godown->id, null, 'PR1');
        $this->assertEquals(6, $stock->getStock($company->id, $store->id, null, $product->id));
        $this->assertEquals(10, $stock->getStock($company->id, $store->id, $godown->id, $product->id));
        $this->assertEquals(16, $product->fresh()->current_stock);
        $this->assertDatabaseCount('stock_ledgers', 6);
    }

    public function test_insufficient_transfer_has_no_partial_effect(): void
    {
        [$company, $store, $product, $godown, $stock] = $this->fixture();
        $stock->increaseStock($product, 2, 'Purchase', $company->id, $store->id, $godown->id, null, null, 100);
        try {
            $stock->transferStock($product, 3, $company->id, $store->id, $godown->id, null);
            $this->fail('Expected insufficient-stock rejection.');
        } catch (RuntimeException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
        $this->assertEquals(2, $product->fresh()->current_stock);
        $this->assertDatabaseCount('stock_ledgers', 1);
        $this->assertEquals(0, $stock->getStock($company->id, $store->id, null, $product->id));
    }

    public function test_foreign_branch_product_is_rejected_before_posting(): void
    {
        [$company, $store, $product, $godown, $stock] = $this->fixture();
        $other = Store::create(['company_id' => $company->id, 'store_name' => 'Other', 'store_code' => 'ST2', 'status' => true]);
        try {
            $stock->increaseStock($product, 1, 'Purchase', $company->id, $other->id, null, null, null);
            $this->fail('Expected branch ownership rejection.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('branch', $e->getMessage());
        }
        $this->assertDatabaseCount('stock_ledgers', 0);
        $this->assertEquals(0, $product->fresh()->current_stock);
    }

    public function test_foreign_godown_is_rejected_before_posting(): void
    {
        [$company, $store, $product, $godown, $stock] = $this->fixture();
        $other = Store::create(['company_id' => $company->id, 'store_name' => 'Other', 'store_code' => 'ST2', 'status' => true]);
        $foreign = Godown::create(['company_id' => $company->id, 'store_id' => $other->id, 'godown_name' => 'Foreign', 'status' => true]);
        try {
            $stock->increaseStock($product, 1, 'Purchase', $company->id, $store->id, $foreign->id, null, null);
            $this->fail('Expected godown ownership rejection.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('godown', $e->getMessage());
        }
        $this->assertDatabaseCount('stock_ledgers', 0);
    }
    private function priceLine(string $type, float $gst = 18, float $quantity = 1, float $discount = 0): array
    {
        [$company, $store, $product] = $this->fixture();
        $method = new \ReflectionMethod(\App\Http\Controllers\SalesController::class, 'buildSaleLines');
        return $method->invoke(app(\App\Http\Controllers\SalesController::class), [
            'product_id' => [$product->id], 'qty' => [$quantity], 'sale_price' => [200],
            'billing_type' => $type, 'price_includes_gst' => true, 'gst' => [$gst],
            'discount_type' => ['percentage'], 'discount_value' => [$discount],
        ], $company->id, $store->id)[0];
    }

    public function test_inclusive_gst_price_is_not_taxed_twice(): void
    {
        $line = $this->priceLine('gst');
        $this->assertEquals(169.49, $line['taxable_amount']);
        $this->assertEquals(30.51, $line['gst_amount']);
        $this->assertEquals(200, $line['line_total']);
    }

    public function test_cash_bill_ignores_gst(): void
    {
        $line = $this->priceLine('non_gst');
        $this->assertEquals(200, $line['taxable_amount']);
        $this->assertEquals(0, $line['gst_amount']);
        $this->assertEquals(200, $line['line_total']);
    }

    public function test_zero_tax_product_uses_existing_gst_fallback(): void
    {
        $line = $this->priceLine('gst', 0);
        $this->assertEquals(18, $line['gst_percent']);
        $this->assertEquals(200, $line['line_total']);
    }

    public function test_multi_quantity_tax_reconciles_to_customer_amount(): void
    {
        $line = $this->priceLine('gst', 18, 3);
        $this->assertEquals(508.47, $line['taxable_amount']);
        $this->assertEquals(91.53, $line['gst_amount']);
        $this->assertEquals(600, $line['line_total']);
    }

    public function test_percentage_discount_keeps_inclusive_total(): void
    {
        $line = $this->priceLine('gst', 18, 1, 10);
        $this->assertEquals(180, $line['line_total']);
        $this->assertEquals(180, round($line['taxable_amount'] + $line['gst_amount'], 2));
    }

}
