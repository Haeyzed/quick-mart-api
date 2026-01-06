# Database Migration Generation Guide

## Status
✅ Migration plan created (`MIGRATION_PLAN.md`)
✅ Sample migrations created for:
- `general_settings`
- `currencies`
- `brands` (with soft deletes)
- `categories` (with soft deletes and self-reference)

## Next Steps

Given the massive scope (99+ tables), here's the systematic approach:

### Phase 1: Complete Foundation Tables (Priority 1)
1. ✅ `general_settings` - DONE
2. ✅ `currencies` - DONE
3. ✅ `brands` - DONE
4. ✅ `categories` - DONE
5. ⏳ `custom_fields` - Next
6. ⏳ `barcodes` - Next
7. ⏳ `languages` - Next

### Phase 2: Core Business Entities (Priority 2)
8. ⏳ `units` - Measurement units
9. ⏳ `taxes` - Tax definitions
10. ⏳ `warehouses` - Warehouse locations (with soft deletes)
11. ⏳ `billers` - Biller information
12. ⏳ `customer_groups` - Customer group definitions
13. ⏳ `customers` - Customer records (with soft deletes)
14. ⏳ `suppliers` - Supplier records (with soft deletes)
15. ⏳ `accounts` - Financial accounts (with soft deletes)
16. ⏳ `expense_categories` - Expense categories
17. ⏳ `income_categories` - Income categories

### Phase 3: Product Management (Priority 3)
18. ⏳ `variants` - Variant definitions
19. ⏳ `products` - Main product table (with soft deletes)
20. ⏳ `product_variants` - Product variants
21. ⏳ `product_warehouse` - Product stock per warehouse
22. ⏳ `product_batches` - Batch tracking

### Phase 4: Sales & Purchases (Priority 4)
23. ⏳ `sales` - Sales transactions (with soft deletes)
24. ⏳ `product_sales` - Sale line items
25. ⏳ `purchases` - Purchase transactions (with soft deletes)
26. ⏳ `product_purchases` - Purchase line items
27. ⏳ `quotations` - Quotations
28. ⏳ `product_quotation` - Quotation line items
29. ⏳ `returns` - Sale returns
30. ⏳ `product_returns` - Return line items
31. ⏳ `return_purchases` - Purchase returns
32. ⏳ `purchase_product_return` - Purchase return line items

### Phase 5: Payments & Financial (Priority 5)
33. ⏳ `payments` - Payment records
34. ⏳ `payment_with_cheque` - Cheque payments
35. ⏳ `payment_with_credit_card` - Credit card payments
36. ⏳ `payment_with_gift_card` - Gift card payments
37. ⏳ `payment_with_paypal` - PayPal payments
38. ⏳ `money_transfers` - Account transfers
39. ⏳ `cash_registers` - Cash register sessions
40. ⏳ `expenses` - Expense records
41. ⏳ `incomes` - Income records
42. ⏳ `deposits` - Customer deposits
43. ⏳ `gift_cards` - Gift card records
44. ⏳ `gift_card_recharges` - Gift card recharges
45. ⏳ `coupons` - Coupon codes
46. ⏳ `reward_point_settings` - Reward point configuration
47. ⏳ `reward_points` - Reward point transactions

### Remaining Phases
See `MIGRATION_PLAN.md` for complete list.

## Migration Template Pattern

Each migration should follow this pattern:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Columns from SQL dump
            // Use appropriate Laravel types:
            // - string() for VARCHAR
            // - text() for TEXT
            // - longText() for LONGTEXT
            // - integer() for INT
            // - bigInteger() for BIGINT
            // - double() for DOUBLE
            // - decimal() for DECIMAL
            // - boolean() for TINYINT(1)
            // - date() for DATE
            // - timestamp() for TIMESTAMP
            
            // Soft deletes where appropriate
            $table->softDeletes();
            
            // Timestamps
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('foreign_key_column')
                ->references('id')
                ->on('referenced_table')
                ->onDelete('restrict|cascade|set null')
                ->onUpdate('cascade');
            
            // Indexes
            $table->index('column_name');
            $table->index(['column1', 'column2']); // Composite
            
            // Unique constraints
            $table->unique('column_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

## Key Rules

1. **One table per migration** - Each migration creates one complete table
2. **Final state only** - Include all columns from SQL dump, no alter migrations
3. **Soft deletes** - Add `$table->softDeletes()` for business-critical tables
4. **Foreign keys** - Always add with appropriate `onDelete` and `onUpdate` rules
5. **Indexes** - Add indexes for foreign keys and frequently queried columns
6. **Data types** - Match SQL dump types exactly
7. **Defaults** - Include default values from SQL dump
8. **Nullability** - Use `->nullable()` where SQL allows NULL

## Execution Order

Migrations must be created in dependency order:
1. Parent tables first
2. Child tables after
3. Junction/pivot tables last

Use timestamp prefixes to ensure correct order:
- `2025_12_25_174000_` - Foundation
- `2025_12_25_175000_` - Core entities
- `2025_12_25_176000_` - Products
- `2025_12_25_177000_` - Sales/Purchases
- `2025_12_25_178000_` - Payments
- `2025_12_25_179000_` - Inventory
- `2025_12_25_180000_` - HRM
- `2025_12_25_181000_` - Ecommerce
- `2025_12_25_182000_` - Other modules

## Verification

After creating all migrations:
1. Run `php artisan migrate:fresh` to test
2. Verify no foreign key errors
3. Verify all indexes are created
4. Verify soft deletes work correctly
5. Check that schema matches SQL dump

## Notes

- The SQL dump is the source of truth
- All columns from the dump must be included
- Use Laravel 12 migration syntax
- Ensure proper type casting for models
- Add comments where helpful

