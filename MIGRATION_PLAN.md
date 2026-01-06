# Database Migration Revamp Plan

## Overview
This document outlines the migration strategy for consolidating all database tables from `quick-mart-old` into `quick-mart-api` following Laravel 12 standards.

## Migration Execution Order

### Phase 1: Foundation Tables (No Dependencies)
1. `users` - Already exists, may need updates
2. `general_settings` - Core configuration
3. `currencies` - Currency definitions
4. `languages` - Language support
5. `custom_fields` - Custom field definitions
6. `barcodes` - Barcode templates

### Phase 2: Core Business Entities
7. `brands` - Product brands
8. `categories` - Product categories (self-referencing)
9. `units` - Measurement units
10. `taxes` - Tax definitions
11. `warehouses` - Warehouse locations
12. `billers` - Biller information
13. `customer_groups` - Customer group definitions
14. `customers` - Customer records
15. `suppliers` - Supplier records
16. `accounts` - Financial accounts
17. `expense_categories` - Expense categories
18. `income_categories` - Income categories

### Phase 3: Product Management
19. `products` - Main product table
20. `product_variants` - Product variants
21. `product_warehouse` - Product stock per warehouse
22. `product_batches` - Batch tracking
23. `variants` - Variant definitions

### Phase 4: Sales & Purchases
24. `sales` - Sales transactions
25. `product_sales` - Sale line items
26. `purchases` - Purchase transactions
27. `product_purchases` - Purchase line items
28. `quotations` - Quotations
29. `product_quotation` - Quotation line items
30. `returns` - Sale returns
31. `product_returns` - Return line items
32. `return_purchases` - Purchase returns
33. `purchase_product_return` - Purchase return line items

### Phase 5: Payments & Financial
34. `payments` - Payment records
35. `payment_with_cheque` - Cheque payments
36. `payment_with_credit_card` - Credit card payments
37. `payment_with_gift_card` - Gift card payments
38. `payment_with_paypal` - PayPal payments
39. `money_transfers` - Account transfers
40. `cash_registers` - Cash register sessions
41. `expenses` - Expense records
42. `incomes` - Income records
43. `deposits` - Customer deposits
44. `gift_cards` - Gift card records
45. `gift_card_recharges` - Gift card recharges
46. `coupons` - Coupon codes
47. `reward_point_settings` - Reward point configuration
48. `reward_points` - Reward point transactions

### Phase 6: Inventory Management
49. `adjustments` - Stock adjustments
50. `product_adjustments` - Adjustment line items
51. `transfers` - Stock transfers
52. `product_transfer` - Transfer line items

### Phase 7: HRM Module
53. `departments` - Departments
54. `designations` - Job designations
55. `shifts` - Work shifts
56. `employees` - Employee records
57. `attendances` - Attendance records
58. `leaves` - Leave requests
59. `leave_types` - Leave type definitions
60. `overtimes` - Overtime records
61. `holidays` - Holiday calendar
62. `payrolls` - Payroll records
63. `hrm_settings` - HRM configuration

### Phase 8: Ecommerce Module
64. `blogs` - Blog posts
65. `collections` - Product collections
66. `product_reviews` - Product reviews
67. `menus` - Navigation menus
68. `menu_items` - Menu items
69. `pages` - Static pages
70. `page_widgets` - Page widgets
71. `widgets` - Widget definitions
72. `sliders` - Image sliders
73. `social_links` - Social media links
74. `newsletter` - Newsletter subscriptions
75. `faq_categories` - FAQ categories
76. `faqs` - FAQ entries
77. `links` - External links
78. `ecommerce_settings` - Ecommerce configuration

### Phase 9: Delivery & Shipping
79. `couriers` - Courier services
80. `deliveries` - Delivery records
81. `packing_slips` - Packing slip records
82. `packing_slip_products` - Packing slip line items
83. `challans` - Delivery challans

### Phase 10: Manufacturing Module
84. `productions` - Production orders
85. `product_productions` - Production line items

### Phase 11: Discounts & Promotions
86. `discounts` - Discount rules
87. `discount_plans` - Discount plan definitions
88. `discount_plan_customers` - Plan-customer mapping
89. `discount_plan_discounts` - Plan-discount mapping

### Phase 12: Additional Features
90. `installment_plans` - Installment plan definitions
91. `installments` - Installment payments
92. `invoice_schemas` - Invoice schema templates
93. `invoice_settings` - Invoice configuration
94. `pos_setting` - POS configuration
95. `mail_settings` - Email configuration
96. `printers` - Printer configurations
97. `external_services` - External service integrations
98. `tables` - Restaurant table management
99. `customer_addresses` - Customer shipping addresses
100. `activity_logs` - Activity logging
101. `notifications` - System notifications
102. `mobile_tokens` - Mobile app tokens
103. `dso_alerts` - DSO (Days Sales Outstanding) alerts

## Soft Delete Strategy

### Tables Requiring Soft Deletes:
- `users` - User accounts
- `products` - Products
- `categories` - Categories
- `brands` - Brands
- `customers` - Customers
- `suppliers` - Suppliers
- `sales` - Sales transactions
- `purchases` - Purchase transactions
- `employees` - Employee records
- `warehouses` - Warehouses
- `accounts` - Financial accounts

### Tables NOT Requiring Soft Deletes:
- Transaction line items (product_sales, product_purchases, etc.)
- Logging tables (activity_logs, notifications)
- Configuration tables (settings, schemas)
- Junction/pivot tables

## Foreign Key Strategy

All foreign keys should:
- Use `onDelete('restrict')` for critical relationships (sales, purchases)
- Use `onDelete('cascade')` for dependent records (line items)
- Use `onDelete('set null')` for optional relationships
- Use `onUpdate('cascade')` consistently

## Index Strategy

Indexes should be added for:
- All foreign key columns
- Frequently queried columns (reference_no, code, email, phone)
- Composite indexes for common query patterns
- Unique constraints where applicable

## Notes

- All migrations will be consolidated (one table per migration)
- Each migration represents the final state of the table
- No alter migrations will be created
- All columns from SQL dump will be included
- Proper data types and constraints will be enforced
- Default values will be set where appropriate

