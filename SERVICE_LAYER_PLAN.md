# Service Layer & Business Logic Refactoring Plan

## Overview
This document outlines the comprehensive plan for refactoring business logic from `quick-mart-old` into `quick-mart-api` following Laravel 12 standards, clean architecture, and strong typing.

## Phase 1: Foundation ✅ (Completed)

### 1. ResponseServiceProvider ✅
- **Location**: `app/Providers/ResponseServiceProvider.php`
- **Purpose**: Centralized API response formatting
- **Methods**:
  - `success()` - Success responses
  - `error()` - Error responses
  - `validationError()` - Validation error responses
  - `notFound()` - 404 responses
  - `unauthorized()` - 401 responses
  - `forbidden()` - 403 responses

### 2. BaseService ✅
- **Location**: `app/Services/BaseService.php`
- **Purpose**: Base class for all services
- **Features**:
  - Database transaction handling
  - Logging utilities
  - Response helper methods

### 3. Form Request Classes ✅ (Started)
- **Product Requests**:
  - `StoreProductRequest` ✅
  - `UpdateProductRequest` ✅

## Phase 2: Core Services (In Progress)

### Service Architecture

Each service will follow this structure:
```php
class DomainService extends BaseService
{
    // Create operations
    public function create(array $data): Model
    
    // Update operations
    public function update(Model $model, array $data): Model
    
    // Delete operations
    public function delete(Model $model): bool
    
    // Query operations
    public function find(int $id): ?Model
    public function getAll(array $filters = []): Collection
    
    // Business logic methods
    protected function handleSpecificLogic(...): void
}
```

### Priority Services (To Be Created)

#### 1. ProductService ✅ (Started)
- **Status**: Core structure created, needs completion
- **Methods Needed**:
  - ✅ `createProduct()` - Create product with all logic
  - ✅ `updateProduct()` - Update product
  - ✅ `deleteProduct()` - Delete product
  - ⏳ `getProducts()` - Get products with filters
  - ⏳ `getProductHistory()` - Get product history
  - ⏳ `getProductStock()` - Get product stock levels
  - ⏳ `handleProductImages()` - Image processing
  - ⏳ `handleProductVariants()` - Variant management
  - ⏳ `handleInitialStock()` - Initial stock handling
  - ⏳ `createAutoPurchase()` - Auto purchase creation

#### 2. SaleService (Next)
- **Complexity**: Very High
- **Key Methods**:
  - `createSale()` - Create sale with payment processing
  - `updateSale()` - Update sale
  - `deleteSale()` - Delete sale
  - `processPayment()` - Handle payment processing
  - `applyDiscount()` - Apply discounts/coupons
  - `calculateTax()` - Calculate taxes
  - `updateInventory()` - Update inventory after sale
  - `generateInvoice()` - Generate invoice
  - `sendNotifications()` - Send email/SMS notifications

#### 3. PurchaseService (Next)
- **Complexity**: Very High
- **Key Methods**:
  - `createPurchase()` - Create purchase order
  - `updatePurchase()` - Update purchase
  - `deletePurchase()` - Delete purchase
  - `receivePurchase()` - Receive purchased items
  - `processPayment()` - Handle payment processing
  - `updateInventory()` - Update inventory after purchase
  - `handleReturns()` - Handle purchase returns

#### 4. CustomerService
- **Complexity**: Medium
- **Key Methods**:
  - `createCustomer()` - Create customer
  - `updateCustomer()` - Update customer
  - `deleteCustomer()` - Delete customer
  - `addDeposit()` - Add customer deposit
  - `updateDeposit()` - Update deposit
  - `addRewardPoints()` - Add reward points
  - `redeemRewardPoints()` - Redeem points
  - `getCustomerBalance()` - Get customer balance

#### 5. SupplierService
- **Complexity**: Medium
- **Key Methods**:
  - `createSupplier()` - Create supplier
  - `updateSupplier()` - Update supplier
  - `deleteSupplier()` - Delete supplier
  - `getSupplierProducts()` - Get supplier products
  - `updateSupplierPricing()` - Update pricing

#### 6. InventoryService
- **Complexity**: High
- **Key Methods**:
  - `getStockLevels()` - Get stock levels
  - `updateStock()` - Update stock
  - `transferStock()` - Transfer between warehouses
  - `adjustStock()` - Stock adjustments
  - `getLowStockProducts()` - Get low stock alerts

#### 7. PaymentService
- **Complexity**: High
- **Key Methods**:
  - `processPayment()` - Process payment
  - `processChequePayment()` - Handle cheque
  - `processCreditCardPayment()` - Handle credit card
  - `processGiftCardPayment()` - Handle gift card
  - `processPayPalPayment()` - Handle PayPal
  - `refundPayment()` - Refund payment

#### 8. AccountService
- **Complexity**: Medium
- **Key Methods**:
  - `createAccount()` - Create account
  - `updateAccount()` - Update account
  - `deleteAccount()` - Delete account
  - `transferMoney()` - Transfer between accounts
  - `getAccountBalance()` - Get balance
  - `getAccountTransactions()` - Get transactions

#### 9. WarehouseService
- **Complexity**: Medium
- **Key Methods**:
  - `createWarehouse()` - Create warehouse
  - `updateWarehouse()` - Update warehouse
  - `deleteWarehouse()` - Delete warehouse
  - `getWarehouseStock()` - Get warehouse stock
  - `getWarehouseProducts()` - Get products in warehouse

#### 10. HRM Services
- **EmployeeService**
- **PayrollService**
- **AttendanceService**
- **LeaveService**

## Phase 3: Form Request Classes

### Required Form Requests

#### Product Requests ✅ (2/2)
- ✅ `StoreProductRequest`
- ✅ `UpdateProductRequest`

#### Sale Requests (0/3)
- ⏳ `StoreSaleRequest`
- ⏳ `UpdateSaleRequest`
- ⏳ `ProcessPaymentRequest`

#### Purchase Requests (0/3)
- ⏳ `StorePurchaseRequest`
- ⏳ `UpdatePurchaseRequest`
- ⏳ `ReceivePurchaseRequest`

#### Customer Requests (0/3)
- ⏳ `StoreCustomerRequest`
- ⏳ `UpdateCustomerRequest`
- ⏳ `AddDepositRequest`

#### Supplier Requests (0/2)
- ⏳ `StoreSupplierRequest`
- ⏳ `UpdateSupplierRequest`

#### Inventory Requests (0/4)
- ⏳ `StoreTransferRequest`
- ⏳ `StoreAdjustmentRequest`
- ⏳ `StoreStockCountRequest`
- ⏳ `UpdateStockRequest`

#### Payment Requests (0/5)
- ⏳ `ProcessPaymentRequest`
- ⏳ `ProcessChequePaymentRequest`
- ⏳ `ProcessCreditCardPaymentRequest`
- ⏳ `ProcessGiftCardPaymentRequest`
- ⏳ `RefundPaymentRequest`

#### Account Requests (0/3)
- ⏳ `StoreAccountRequest`
- ⏳ `UpdateAccountRequest`
- ⏳ `TransferMoneyRequest`

#### Warehouse Requests (0/2)
- ⏳ `StoreWarehouseRequest`
- ⏳ `UpdateWarehouseRequest`

## Phase 4: Service Implementation Guidelines

### 1. Strong Typing Requirements

All services must:
- Use `declare(strict_types=1)`
- Type hint all parameters
- Type hint all return types
- Use typed arrays where possible
- Use DTOs for complex data structures

### 2. Error Handling

```php
try {
    return $this->transaction(function () {
        // Business logic
    });
} catch (\Exception $e) {
    $this->logError('Error message', ['context' => $e]);
    throw new ServiceException('User-friendly message', 0, $e);
}
```

### 3. Transaction Management

All write operations must use transactions:
```php
return $this->transaction(function () {
    // All database operations
});
```

### 4. Logging

- Log all errors with context
- Log important business events
- Use appropriate log levels

### 5. Validation

- All validation in Form Request classes
- Services should trust validated data
- Additional business rule validation in services

### 6. Response Formatting

All services return data, not responses:
```php
// Service returns data
return $product;

// Controller formats response
return ResponseServiceProvider::success($product, 'Product created');
```

## Phase 5: Verification Checklist

### For Each Service:
- [ ] All business logic extracted from controller
- [ ] All methods have type hints
- [ ] All methods have PHPDoc
- [ ] All database operations in transactions
- [ ] Error handling implemented
- [ ] Logging implemented
- [ ] Unit tests created
- [ ] Integration tests created

### For Each Form Request:
- [ ] All validation rules match old implementation
- [ ] Custom messages provided
- [ ] Authorization logic implemented
- [ ] Type hints on validated data

### Overall:
- [ ] No business logic in controllers
- [ ] All responses use ResponseServiceProvider
- [ ] All code follows Laravel 12 standards
- [ ] All code is strongly typed
- [ ] Documentation complete

## Next Steps

1. **Complete ProductService** - Finish all methods
2. **Create SaleService** - Most complex service
3. **Create PurchaseService** - Second most complex
4. **Create remaining core services** - Customer, Supplier, etc.
5. **Create all Form Request classes**
6. **Create specialized services** - Payment, Inventory, etc.
7. **Create HRM services**
8. **Create module services** - Ecommerce, Manufacturing, etc.

## Estimated Timeline

- **Phase 1 (Foundation)**: ✅ Complete
- **Phase 2 (Core Services)**: 2-3 weeks
- **Phase 3 (Form Requests)**: 1 week
- **Phase 4 (Specialized Services)**: 1-2 weeks
- **Phase 5 (Verification)**: 1 week

**Total Estimated Time**: 5-7 weeks

---

**Status**: Phase 1 Complete, Phase 2 In Progress
**Last Updated**: 2024

