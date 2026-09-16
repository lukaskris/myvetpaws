<?php

use App\Models\InvoicesModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class InvoicesDiscountTest extends CIUnitTestCase
{
    private const SVC_SUB = 500000.0;
    private const ITM_SUB = 200000.0;

    public function testLegacyRupiahDiscountResolvesAsInvoiceMode(): void
    {
        $row = ['discount' => 10000, 'discount_type' => 'rupiah', 'service_discount' => 0, 'item_discount' => 0];

        $r = InvoicesModel::resolveDiscount($row, self::SVC_SUB, self::ITM_SUB);

        $this->assertSame('invoice', $r['mode']);
        $this->assertSame(10000.0, $r['invoice']);
        $this->assertSame(10000.0, InvoicesModel::discountTotal($row, self::SVC_SUB, self::ITM_SUB));
    }

    public function testInvoicePercentDiscountAppliesToGross(): void
    {
        $row = ['discount' => 10, 'discount_type' => 'percent', 'service_discount' => 0, 'item_discount' => 0];

        // 10% of (500k + 200k) = 70k
        $this->assertSame(70000.0, InvoicesModel::discountTotal($row, self::SVC_SUB, self::ITM_SUB));
    }

    public function testCategoryModeWinsWhenAnyCategoryDiscountSet(): void
    {
        $row = ['discount' => 50000, 'discount_type' => 'rupiah', 'service_discount' => 0, 'item_discount' => 5000, 'item_discount_type' => 'rupiah'];

        $r = InvoicesModel::resolveDiscount($row, self::SVC_SUB, self::ITM_SUB);

        $this->assertSame('category', $r['mode']);
        $this->assertSame(0.0, $r['invoice']);
        $this->assertSame(0.0, $r['service']);
        $this->assertSame(5000.0, $r['item']);
    }

    public function testCategoryPercentDiscountsResolvePerSubtotal(): void
    {
        $row = [
            'discount' => 0, 'discount_type' => 'rupiah',
            'service_discount' => 20, 'service_discount_type' => 'percent',
            'item_discount' => 5, 'item_discount_type' => 'percent',
        ];

        // 20% of 500k + 5% of 200k = 100k + 10k
        $this->assertSame(110000.0, InvoicesModel::discountTotal($row, self::SVC_SUB, self::ITM_SUB));
    }

    public function testMixedCategoryTypesResolveIndependently(): void
    {
        $row = [
            'discount' => 0, 'discount_type' => 'rupiah',
            'service_discount' => 15, 'service_discount_type' => 'percent',
            'item_discount' => 25000, 'item_discount_type' => 'rupiah',
        ];

        // 15% of 500k + fixed 25k
        $this->assertSame(100000.0, InvoicesModel::discountTotal($row, self::SVC_SUB, self::ITM_SUB));
    }

    public function testRupiahDiscountCappedAtCategorySubtotal(): void
    {
        $row = [
            'discount' => 0, 'discount_type' => 'rupiah',
            'service_discount' => 999999, 'service_discount_type' => 'rupiah',
            'item_discount' => 0, 'item_discount_type' => 'rupiah',
        ];

        $r = InvoicesModel::resolveDiscount($row, self::SVC_SUB, self::ITM_SUB);

        $this->assertSame(self::SVC_SUB, $r['service']);
    }

    public function testInvoiceDiscountCappedAtGross(): void
    {
        $row = ['discount' => 999999, 'discount_type' => 'rupiah', 'service_discount' => 0, 'item_discount' => 0];

        $this->assertSame(self::SVC_SUB + self::ITM_SUB, InvoicesModel::discountTotal($row, self::SVC_SUB, self::ITM_SUB));
    }

    public function testMissingKeysDefaultToNoDiscount(): void
    {
        // Rows straight from old selects may lack the new columns entirely
        $this->assertSame(0.0, InvoicesModel::discountTotal(['discount' => 0], self::SVC_SUB, self::ITM_SUB));
        $this->assertSame(0.0, InvoicesModel::discountTotal([], self::SVC_SUB, self::ITM_SUB));
    }

    public function testZeroSubtotalsNeverProduceNegativeDiscount(): void
    {
        $row = [
            'discount' => 0, 'discount_type' => 'rupiah',
            'service_discount' => 10, 'service_discount_type' => 'percent',
            'item_discount' => 0, 'item_discount_type' => 'rupiah',
        ];

        $r = InvoicesModel::resolveDiscount($row, 0.0, 0.0);

        $this->assertSame(0.0, $r['service']);
        $this->assertSame(0.0, $r['item']);
    }

    public function testAllocateDiscountSpreadsProportionallyAndReconciles(): void
    {
        $rows = [
            ['price' => 100000, 'quantity' => 1], // 100k
            ['price' => 50000, 'quantity' => 2],  // 100k
            ['price' => 50000, 'quantity' => 4],  // 200k
        ];

        $out = InvoicesModel::allocateDiscount($rows, 40000.0);

        $this->assertSame(10000.0, $out[0]['discount']);
        $this->assertSame(10000.0, $out[1]['discount']);
        $this->assertSame(20000.0, $out[2]['discount']);
        $this->assertSame(40000.0, array_sum(array_column($out, 'discount')));
    }

    public function testAllocateDiscountZeroPotGivesAllRowsZero(): void
    {
        $rows = [['price' => 100000, 'quantity' => 1]];

        $out = InvoicesModel::allocateDiscount($rows, 0.0);

        $this->assertSame(0.0, $out[0]['discount']);
    }

    public function testAllocateDiscountPotCappedAtSubtotal(): void
    {
        $rows = [['price' => 100000, 'quantity' => 1]];

        $out = InvoicesModel::allocateDiscount($rows, 999999.0);

        $this->assertSame(100000.0, $out[0]['discount']);
    }
}
