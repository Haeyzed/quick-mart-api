<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Database\Seeders\Tenant\BarcodeSeeder;
use Database\Seeders\Tenant\ExternalServicesSeeder;
use Database\Seeders\Tenant\InvoiceSettingsSeeder;
use Database\Seeders\Tenant\LanguagesTableSeeder;
use Database\Seeders\Tenant\TranslationsTableSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\PermissionService;
use App\Models\User;

/**
 * Tenant Database Seeder
 *
 * Main tenant seeder that seeds all core application data including:
 * - General settings
 * - Users and roles
 * - Permissions and role-permission mappings
 * - Accounts, billers, brands, categories
 * - Products, purchases, suppliers
 * - Taxes, units, warehouses
 * - And other core business data
 *
 * This seeder supports tenant-specific data through the static $tenantData property.
 *
 * @package Database\Seeders\Tenant
 */
class TenantDatabaseSeeder extends Seeder
{
    /**
     * Tenant-specific data passed from tenant creation process.
     * Contains site settings, user info, package info, and permissions.
     *
     * @var array<string, mixed>
     */
    public static array $tenantData = [];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // Call other seeders first
        $this->call(BarcodeSeeder::class);
        $this->call(ExternalServicesSeeder::class);
        // $this->call(LanguagesTableSeeder::class);
        // $this->call(TranslationsTableSeeder::class);
        $this->call(InvoiceSettingsSeeder::class);

        // Seed core application data
        $this->seedGeneralSettings();
        $this->seedRoles();
        $this->seedPermissions();
        $this->seedRolePermissions();
        $this->seedAccounts();
        $this->seedBillers();
        $this->seedWarehouses();
        $this->seedUsers();
        $this->seedBrands();
        $this->seedCategories();
        $this->seedUnits();
        $this->seedTaxes();
        // $this->seedCurrencies();
        $this->seedCustomerGroups();
        $this->seedCustomers();
        $this->seedSuppliers();
        $this->seedPosSettings();
        $this->seedProducts();
        $this->seedPurchases();
        $this->seedProductPurchases();
        $this->seedProductWarehouse();
        $this->seedMailSettings();
    }

    /**
     * Seed general settings.
     *
     * @return void
     */
    private function seedGeneralSettings(): void
    {
        if (DB::table('general_settings')->count() > 0) {
            return;
        }

        $tenantData = self::$tenantData;

        DB::table('general_settings')->insert([
            [
                'id' => 1,
                'site_title' => $tenantData['site_title'] ?? 'Quick Mart',
                'site_logo' => $tenantData['site_logo'] ?? '20250102042651.png',
                'is_rtl' => 0,
                'currency' => '1',
                'package_id' => $tenantData['package_id'] ?? 0,
                'subscription_type' => $tenantData['subscription_type'] ?? 'monthly',
                'staff_access' => 'own',
                'without_stock' => 'no',
                'date_format' => 'd/m/Y',
                'developed_by' => $tenantData['developed_by'] ?? 'Softmax Technologies',
                'invoice_format' => 'standard',
                'decimal' => 2,
                'state' => 1,
                'theme' => 'default.css',
                'modules' => $tenantData['modules'] ?? null,
                'currency_position' => 'prefix',
                'expiry_date' => $tenantData['expiry_date'] ?? '1970-01-01',
                'expiry_type' => 'days',
                'expiry_value' => '0',
                'is_zatca' => null,
                'company_name' => null,
                'vat_registration_number' => null,
                'is_packing_slip' => 0,
                'storage_provider' => $tenantData['storage_provider'] ??'public',
                'google_client_id' => $tenantData['google_client_id'] ?? null,
                'google_client_secret' => $tenantData['google_client_secret'] ?? null,
                'google_redirect_url' => $tenantData['google_redirect_url'] ?? null,
                'google_login_enabled' => $tenantData['google_login_enabled'] ?? true,
                'facebook_client_id' => $tenantData['facebook_client_id'] ?? null,
                'facebook_client_secret' => $tenantData['facebook_client_secret'] ?? null,
                'facebook_redirect_url' => $tenantData['facebook_redirect_url'] ?? null,
                'facebook_login_enabled' => $tenantData['facebook_login_enabled'] ?? true,
                'github_client_id' => $tenantData['github_client_id'] ?? null,
                'github_client_secret' => $tenantData['github_client_secret'] ?? null,
                'github_redirect_url' => $tenantData['github_redirect_url'] ?? null,
                'github_login_enabled' => $tenantData['github_login_enabled'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed users.
     *
     * This method creates the initial admin user and assigns roles and permissions
     * using the PermissionService to ensure proper deduplication and role resolution.
     *
     * @return void
     */
    private function seedUsers(): void
    {
        if (DB::table('users')->count() > 0) {
            return;
        }

        $tenantData = self::$tenantData;
        $adminRoleId = DB::table('roles')->where('name', 'Admin')->where('guard_name', 'web')->value('id');
        if ($adminRoleId === null) {
            return;
        }

        $now = now();
        DB::table('users')->insert([
            [
                'name' => $tenantData['name'] ?? 'admin',
                'username' => $tenantData['username'] ?? 'admin',
                'email' => $tenantData['email'] ?? 'admin@gmail.com',
                'password' => $tenantData['password'] ?? '$2y$10$DWAHTfjcvwCpOCXaJg11MOhsqns03uvlwiSUOQwkHL2YYrtrXPcL6',
                'remember_token' => '6mN44MyRiQZfCi0QvFFIYAU9LXIUz9CdNIlrRS5Lg8wBoJmxVu8auzTP42ZW',
                'phone' => $tenantData['phone'] ?? '12112',
                'company_name' => $tenantData['company_name'] ?? 'Softmax Technologies',
                'role_id' => $adminRoleId,
                'biller_id' => null,
                'warehouse_id' => null,
                'is_active' => 1,
                'is_deleted' => 0,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $firstUserId = (int) DB::table('users')->orderBy('id')->value('id');
        $user = User::find($firstUserId);
        if ($user) {
            $permissionService = app(PermissionService::class);

            // Get roles to assign (default: Admin role by name)
            $rolesToAssign = ['Admin'];
            if (isset($tenantData['user_roles']) && is_array($tenantData['user_roles'])) {
                $rolesToAssign = $tenantData['user_roles'];
            }

            // Get all available permissions to assign to admin user
            $allPermissions = $permissionService->getAllPermissions()->pluck('name')->toArray();
            
            // Merge with any tenant-specific direct permissions
            $directPermissions = $tenantData['user_permissions'] ?? [];
            if (!empty($directPermissions)) {
                $allPermissions = array_merge($allPermissions, $directPermissions);
            }

            // Assign roles and all permissions with automatic deduplication
            $permissionService->assignRolesAndPermissions($user, $rolesToAssign, $allPermissions);
        }
    }

    /**
     * Seed roles.
     *
     * @return void
     */
    private function seedRoles(): void
    {
        if (DB::table('roles')->count() > 0) {
            return;
        }

        $now = now();
        DB::table('roles')->insert([
            [
                'name' => 'Admin',
                'description' => 'admin can access all data...',
                'is_active' => true,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Owner',
                'description' => 'Staff of shop',
                'is_active' => true,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'staff',
                'description' => 'staff has specific access...',
                'is_active' => true,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Customer',
                'description' => null,
                'is_active' => true,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Seed permissions.
     *
     * This method seeds all application permissions, checking for existing
     * permissions to avoid duplicates.
     *
     * @return void
     */
    private function seedPermissions(): void
    {
        $existingPermissions = DB::table('permissions')
            ->select('name', 'guard_name')
            ->get();

        $existingMap = [];
        foreach ($existingPermissions as $item) {
            $existingMap["{$item->name}|{$item->guard_name}"] = true;
        }

        $permissionData = $this->getPermissionData();
        $now = now();

        $insertData = [];
        foreach ($permissionData as $permission) {
            $lookupKey = "{$permission['name']}|{$permission['guard_name']}";
            if (!isset($existingMap[$lookupKey])) {
                $insertData[] = [
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($insertData)) {
            DB::table('permissions')->insert($insertData);
        }
    }

    /**
     * Seed role-permission mappings.
     *
     * Uses permission and role names (no hardcoded IDs). Resolves names to IDs
     * from the database after roles and permissions are seeded.
     *
     * @return void
     */
    private function seedRolePermissions(): void
    {
        $tenantData = self::$tenantData;
        $guard = 'web';

        $permissionIds = DB::table('permissions')
            ->where('guard_name', $guard)
            ->pluck('id', 'name')
            ->all();

        $roleIds = DB::table('roles')
            ->where('guard_name', $guard)
            ->pluck('id', 'name')
            ->all();

        $existingRolePermissions = DB::table('role_has_permissions')
            ->select('permission_id', 'role_id')
            ->get();

        $existingMap = [];
        foreach ($existingRolePermissions as $item) {
            $existingMap["{$item->permission_id}|{$item->role_id}"] = true;
        }

        $isSaaS = config('database.connections.saleprosaas_landlord') !== null;

        $nameBasedMappings = [];
        if (!$isSaaS) {
            foreach ($this->getPermissionData() as $p) {
                $nameBasedMappings[] = ['permission' => $p['name'], 'role' => 'Admin'];
            }
        } else {
            $nameBasedMappings = $this->getBasicPermissionsRole();
        }

        $packagePermissionsRole = $tenantData['package_permissions_role'] ?? [];
        $allMappings = array_merge($nameBasedMappings, $packagePermissionsRole);

        $insertData = [];
        foreach ($allMappings as $row) {
            $permissionId = null;
            $roleId = null;

            if (isset($row['permission_id'], $row['role_id'])) {
                $permissionId = (int) $row['permission_id'];
                $roleId = (int) $row['role_id'];
            } elseif (isset($row['permission'], $row['role'])) {
                $permissionId = $permissionIds[$row['permission']] ?? null;
                $roleId = $roleIds[$row['role']] ?? null;
            }

            if ($permissionId === null || $roleId === null) {
                continue;
            }

            $lookupKey = "{$permissionId}|{$roleId}";
            if (!isset($existingMap[$lookupKey])) {
                $insertData[] = [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ];
                $existingMap[$lookupKey] = true;
            }
        }

        if (!empty($insertData)) {
            DB::table('role_has_permissions')->insert($insertData);
        }
    }

    /**
     * Seed accounts.
     *
     * @return void
     */
    private function seedAccounts(): void
    {
        if (DB::table('accounts')->count() > 0) {
            return;
        }

        DB::table('accounts')->insert([
            [
                'id' => 1,
                'account_no' => '019912229',
                'name' => 'Sales Account',
                'initial_balance' => 0.0,
                'total_balance' => 0.0,
                'note' => 'This is the default account.',
                'is_default' => 1,
                'is_active' => 1,
                'code' => null,
                'type' => 'Bank Account',
                'parent_account_id' => null,
                'is_payment' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed billers.
     *
     * @return void
     */
    private function seedBillers(): void
    {
        if (DB::table('billers')->count() > 0) {
            return;
        }

        DB::table('billers')->insert([
            [
                'id' => 1,
                'name' => 'Test Biller',
                'image' => null,
                'company_name' => 'Test Company',
                'vat_number' => null,
                'email' => 'test@gmail.com',
                'phone_number' => '12312',
                'address' => 'Test address',
                'city' => 'Test City',
                'state' => null,
                'postal_code' => null,
                'country' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed brands.
     *
     * @return void
     */
    private function seedBrands(): void
    {
        if (DB::table('brands')->count() > 0) {
            return;
        }

        $brands = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'image' => '20240114102326.png',
                'image_url' => $this->getRandomImageUrl(),
                'short_description' => 'Apple is a technology company that designs and sells smartphones, tablets, and computers.',
                'is_active' => true,
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'image' => '20240114102343.png',
                'image_url' => $this->getRandomImageUrl(),
                'short_description' => 'Samsung is a technology company that designs and sells smartphones, tablets, and computers.',
                'is_active' => true,
            ],
            [
                'name' => 'Huawei',
                'slug' => 'huawei',
                'image' => '20240114102512.png',
                'image_url' => $this->getRandomImageUrl(),
                'short_description' => 'Huawei is a technology company that designs and sells smartphones, tablets, and computers.',
                'is_active' => true,
            ],
            [
                'name' => 'Xiaomi',
                'slug' => 'xiaomi',
                'image' => '20240114103640.png',
                'image_url' => $this->getRandomImageUrl(),
                'short_description' => 'Xiaomi is a technology company that designs and sells smartphones, tablets, and computers.',
                'is_active' => true,
            ],
            [
                'name' => 'Whirlpool',
                'slug' => 'whirlpool',
                'image' => '20240114103701.png',
                'image_url' => $this->getRandomImageUrl(),
                'short_description' => 'Whirlpool is a technology company that designs and sells smartphones, tablets, and computers.',
                'is_active' => true,
            ],
            [
                'name' => 'Nestle',
                'slug' => 'nestle',
                'image' => '20240114103717.png',
                'image_url' => $this->getRandomImageUrl(),
                'short_description' => 'Nestle is a technology company that designs and sells smartphones, tablets, and computers.',
                'is_active' => true,
            ],
            [
                'name' => 'Kraft',
                'slug' => 'kraft',
                'image' => '20240114103851.png',
                'image_url' => $this->getRandomImageUrl(),
                'short_description' => 'Kraft is a technology company that designs and sells smartphones, tablets, and computers.',
                'is_active' => true,
            ],
            [
                'name' => "Kellogg's",
                'slug' => 'kelloggs',
                'image' => '20240114103906.png',
                'image_url' => $this->getRandomImageUrl(),
                'short_description' => "Kellogg's is a multinational food manufacturing company.",
                'is_active' => true,
            ]
        ];

        $now = now();
        $rows = array_map(
            fn ($brand) => array_merge($brand, ['created_at' => $now, 'updated_at' => $now]),
            $brands
        );
        DB::table('brands')->insert($rows);
    }

    /**
     * Seed categories.
     *
     * @return void
     */
    private function seedCategories(): void
    {
        if (DB::table('categories')->count() > 0) {
            return;
        }

        $categories = [
            ['id' => 1, 'name' => 'Smartphone & Gadgets', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
            ['id' => 2, 'name' => 'Phone Accessories', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 1, 'is_active' => 1],
            ['id' => 3, 'name' => 'iPhone', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 1, 'is_active' => 1],
            ['id' => 4, 'name' => 'Samsung', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 1, 'is_active' => 1],
            ['id' => 5, 'name' => 'Phone Cases', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 1, 'is_active' => 1],
            ['id' => 6, 'name' => 'Laptops & Computers', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
            ['id' => 7, 'name' => 'Keyboards', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 6, 'is_active' => 1],
            ['id' => 8, 'name' => 'Laptop Bags', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 6, 'is_active' => 1],
            ['id' => 9, 'name' => 'Mouses', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 6, 'is_active' => 1],
            ['id' => 10, 'name' => 'Webcams', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 6, 'is_active' => 1],
            ['id' => 11, 'name' => 'Monitors', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 6, 'is_active' => 1],
            ['id' => 12, 'name' => 'Smartwatches', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
            ['id' => 13, 'name' => 'Sport Watches', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 12, 'is_active' => 1],
            ['id' => 14, 'name' => 'Kids Watches', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 12, 'is_active' => 1],
            ['id' => 15, 'name' => 'Women Watches', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 12, 'is_active' => 1],
            ['id' => 16, 'name' => 'Men Watches', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 12, 'is_active' => 1],
            ['id' => 23, 'name' => 'TVs, Audio & Video', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
            ['id' => 24, 'name' => 'Television Accessories', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 23, 'is_active' => 1],
            ['id' => 25, 'name' => 'HD, DVD Players', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 23, 'is_active' => 1],
            ['id' => 26, 'name' => 'TV-DVD Combos', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 23, 'is_active' => 1],
            ['id' => 27, 'name' => 'Projectors', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 23, 'is_active' => 1],
            ['id' => 28, 'name' => 'Projection Screen', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => 23, 'is_active' => 1],
            ['id' => 29, 'name' => 'Fruits & Vegetables', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
            ['id' => 30, 'name' => 'Dairy & Egg', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
            ['id' => 31, 'name' => 'Meat & Fish', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
            ['id' => 33, 'name' => 'Candy & Chocolates', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
            ['id' => 39, 'name' => 'Clothing', 'image' => null, 'image_url' => $this->getRandomImageUrl(), 'parent_id' => null, 'is_active' => 1],
        ];

        $now = now();
        $rows = array_map(
            fn ($category) => array_merge($category, ['created_at' => $now, 'updated_at' => $now]),
            $categories
        );
        DB::table('categories')->insert($rows);
    }

    /**
     * Seed currencies.
     *
     * @return void
     */
    private function seedCurrencies(): void
    {
        if (DB::table('currencies')->count() > 0) {
            return;
        }

        DB::table('currencies')->insert([
            [
                'id' => 1,
                'name' => 'US Dollar',
                'code' => 'USD',
                'exchange_rate' => 1.0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed customer groups.
     *
     * @return void
     */
    private function seedCustomerGroups(): void
    {
        if (DB::table('customer_groups')->count() > 0) {
            return;
        }

        DB::table('customer_groups')->insert([
            [
                'id' => 1,
                'name' => 'General',
                'percentage' => '0',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed customers.
     *
     * @return void
     */
    private function seedCustomers(): void
    {
        if (DB::table('customers')->count() > 0) {
            return;
        }

        DB::table('customers')->insert([
            [
                'id' => 1,
                'customer_group_id' => 1,
                'user_id' => null,
                'name' => 'John Doe',
                'company_name' => 'Test Company',
                'email' => 'john@gmail.com',
                'phone_number' => '231312',
                'tax_no' => null,
                'address' => 'Test address',
                'city' => 'Test City',
                'state' => null,
                'postal_code' => null,
                'country' => null,
                'points' => null,
                'is_active' => 1,
                'deposit' => null,
                'expense' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed POS settings.
     *
     * @return void
     */
    private function seedPosSettings(): void
    {
        if (DB::table('pos_setting')->count() > 0) {
            return;
        }

        DB::table('pos_setting')->insert([
            [
                'id' => 1,
                'customer_id' => 1,
                'warehouse_id' => 1,
                'biller_id' => 1,
                'product_number' => 2,
                'keybord_active' => 1,
                'is_table' => 0,
                'send_sms' => 0,
                'stripe_public_key' => null,
                'stripe_secret_key' => null,
                'paypal_live_api_username' => null,
                'paypal_live_api_password' => null,
                'paypal_live_api_secret' => null,
                'payment_options' => 'cash,card,cheque,gift_card,deposit,paypal',
                'invoice_option' => 'thermal',
                'thermal_invoice_size' => '80',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed products.
     *
     * Note: The products array is extremely large (over 2000 lines).
     * The full product data array from lines 1805-4068 of the original
     * TenantDatabaseSeeder.php file should be copied here.
     *
     * @return void
     */
    private function seedProducts(): void
    {
        if (DB::table('products')->count() > 0) {
            return;
        }

        $products = $this->getProductData();

        if (empty($products)) {
            return;
        }

        $now = now();
        $imageUrl = json_encode([$this->getRandomImageUrl(), $this->getRandomImageUrl(), $this->getRandomImageUrl()]);
        $fileUrl = $this->getRandomFileUrl();

        $rows = array_map(
            fn ($product) => array_merge($product, [
                'image_url' => $imageUrl,
                'file_url' => $fileUrl,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            $products
        );
        DB::table('products')->insert($rows);
    }

    /**
     * Return a placeholder image URL for seeded data.
     *
     * Uses picsum.photos as a placeholder service. For production, consider
     * using local assets or null to avoid external dependencies.
     *
     * @return string Placeholder image URL
     */
    private function getRandomImageUrl(): string
    {
        return 'https://picsum.photos/200/300';
    }

    /**
     * Return a placeholder file URL for seeded product data.
     *
     * @return string Placeholder PDF URL
     */
    private function getRandomFileUrl(): string
    {
        return 'https://www.rd.usda.gov/sites/default/files/pdf-sample_0.pdf';
    }

    /**
     * Get product data array.
     *
     * This method should return the full product data array.
     *
     * @return array<int, array<string, mixed>> Product data array
     */
    private function getProductData(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Zenbook 14 OLED (UX3402)｜Laptops For Home – ASUS',
                'code' => '59028109',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 1099.99,
                'price' => 1299.99,
                'wholesale_price' => NULL,
                'qty' => 624.7,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => 1,
                'promotion_price' => '1050.99',
                'starting_date' => '2024-01-08',
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'name' => '2021 Apple 12.9-inch iPad Pro Wi-Fi 512GB',
                'code' => '20358923',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 3,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 1000.0,
                'price' => 1249.0,
                'wholesale_price' => NULL,
                'qty' => -152.5,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => 1,
                'promotion_price' => '1200.00',
                'starting_date' => '2024-01-08',
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 0,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => 0,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Apple iPhone 11 (4GB-64GB) Black',
                'code' => '49251814',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 300.0,
                'price' => 350.0,
                'wholesale_price' => NULL,
                'qty' => -47.7,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => 1,
                'promotion_price' => '330',
                'starting_date' => '2024-01-08',
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 4,
                'name' => 'Samsung Galaxy Chromebook Go, 14″ HD LED, Intel Celeron N4500',
                'code' => '28090345',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 900.0,
                'price' => 1050.0,
                'wholesale_price' => NULL,
                'qty' => -18.78,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 5,
                'name' => 'SAMSUNG Galaxy Book Pro 15.6 Laptop – Intel Core i5',
                'code' => '67015642',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 950.99,
                'price' => 1150.99,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 6,
                'name' => 'Microsoft – Surface Laptop 4 13.5” Touch-Screen – AMD Ryzen 5',
                'code' => '24005329',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 3,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 999.99,
                'price' => 1111.99,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 7,
                'name' => 'Acer Chromebook 315, 15.6 HD – Intel Celeron N4000',
                'code' => '30798200',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 4,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 899.99,
                'price' => 999.99,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 8,
                'name' => 'HP Victus 16-e00244AX GTX 1650 Gaming Laptop 16.1” FHD 144Hz',
                'code' => '81526930',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 4,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 1199.0,
                'price' => 1300.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 9,
                'name' => 'Epson Inkjet WorkForce Pro WF-3820DWF',
                'code' => '20142029',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 399.0,
                'price' => 559.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 10,
                'name' => 'iPhone 14 Pro 256GB Gold',
                'code' => '29733132',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 990.0,
                'price' => 1250.0,
                'wholesale_price' => NULL,
                'qty' => 84.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 14,
                'name' => 'Sony Bravia 55X90J 4K Ultra HD 55″ 140 Screen Google Smart LED TV',
                'code' => '16530612',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 3,
                'category_id' => 23,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 350.0,
                'price' => 499.0,
                'wholesale_price' => NULL,
                'qty' => -1.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 15,
                'name' => 'Samsung 43AU7000 4K Ultra HD 43″ 109 Screen Smart LED TV',
                'code' => '73189124',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 23,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 499.0,
                'price' => 547.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 16,
                'name' => 'Apple TV HD 32GB (2nd Generation)',
                'code' => '71493353',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 23,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 79.0,
                'price' => 109.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 17,
                'name' => 'Apple Watch SE GPS + Cellular 40mm Space Gray',
                'code' => '92178104',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 12,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 349.0,
                'price' => 499.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 0,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => 0,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 18,
                'name' => 'Xbox One Wireless Controller Black Color',
                'code' => '93060790',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 459.0,
                'price' => 599.0,
                'wholesale_price' => NULL,
                'qty' => -5.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 19,
                'name' => 'Apple iPhone XS Max-64GB -white',
                'code' => '22061536',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 899.0,
                'price' => 1059.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 20,
                'name' => 'Apple Watch Series 8 GPS 45mm Midnight Aluminum Case',
                'code' => '31429623',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 12,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 399.0,
                'price' => 499.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 21,
                'name' => 'Huawei Watch GT 2 Sport Stainless Steel 46mm',
                'code' => '02456392',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 3,
                'category_id' => 12,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 369.0,
                'price' => 599.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => 1,
                'promotion_price' => '499',
                'starting_date' => '2024-01-15',
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 22,
                'name' => 'Samsung Galaxy Active 2 R835U Smartwatch 40mm',
                'code' => '10203743',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 12,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 275.0,
                'price' => 399.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 23,
                'name' => 'Canon EOS R10 RF-S 18-45 IS STM',
                'code' => '13929367',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 439.0,
                'price' => 577.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 24,
                'name' => 'Sony A7 III Mirrorless Camera Body Only',
                'code' => '99421096',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 299.0,
                'price' => 379.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 25,
                'name' => 'WOLFANG GA420 Action Camera 4K 60FPS 24MP',
                'code' => '99218280',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 4,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 130.0,
                'price' => 157.99,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 26,
                'name' => 'Fresh Organic Navel Orange',
                'code' => '33887520',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 29,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 2.99,
                'price' => 3.99,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 27,
                'name' => 'Banana (pack of 12)',
                'code' => '27583341',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 29,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 0.89,
                'price' => 1.29,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 28,
                'name' => 'Water Melon ~ 3KG',
                'code' => '19186147',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 29,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 2.39,
                'price' => 3.3,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 0,
            ],
            [
                'id' => 29,
                'name' => 'Gala Original Apple - 1KG',
                'code' => '80912386',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 29,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 2.39,
                'price' => 3.19,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 31,
                'name' => "Men's Premium Egyptian Cotton T-shirt",
                'code' => '30282941',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 39,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 50.5,
                'price' => 70.99,
                'wholesale_price' => NULL,
                'qty' => -13.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => 1,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 0,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => 0,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["Size","Color"]',
                'variant_value' => '["S,M,L,XL,XXL","red,green,blue"]',
                'is_active' => 1,
            ],
            [
                'id' => 34,
                'name' => 'Bon Sprayer',
                'code' => '09138264',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 2,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 115.0,
                'price' => 130.0,
                'wholesale_price' => NULL,
                'qty' => 338.5,
                'alert_quantity' => 5.0,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["Color"]',
                'variant_value' => '["Red,Yellow,Green,Bule"]',
                'is_active' => 1,
            ],
            [
                'id' => 35,
                'name' => 'Toffee',
                'code' => '76722958',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 10.0,
                'price' => 20.0,
                'wholesale_price' => NULL,
                'qty' => 48.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 36,
                'name' => 'AMD RYZEN 5 5600G',
                'code' => '1001',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 2,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 2500.0,
                'price' => 3500.0,
                'wholesale_price' => NULL,
                'qty' => 6.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 37,
                'name' => 'KINGSTON 8GB RAM',
                'code' => '1002',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 2,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 1000.0,
                'price' => 1450.0,
                'wholesale_price' => NULL,
                'qty' => 8.0,
                'alert_quantity' => 5.0,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 38,
                'name' => 'MI BUILD PACKAGE',
                'code' => '1004',
                'type' => 'combo',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 0.0,
                'price' => 4950.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => '36,37',
                'variant_list' => ',',
                'qty_list' => '1,1',
                'price_list' => '3500,1450',
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 39,
                'name' => 'Irene Jack',
                'code' => '3456',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 1000.0,
                'price' => 899.0,
                'wholesale_price' => NULL,
                'qty' => 84.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 41,
                'name' => 'off white Tshirt',
                'code' => '75308742',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 39,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 4.8,
                'price' => 8.0,
                'wholesale_price' => NULL,
                'qty' => -1.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 42,
                'name' => 'test',
                'code' => '125',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 12.0,
                'price' => 124.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => -1.0,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 43,
                'name' => 'samsung laptop',
                'code' => '65317202',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 50000.0,
                'price' => 55000.0,
                'wholesale_price' => NULL,
                'qty' => -5.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => 1,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 44,
                'name' => 'samsung laptop 15',
                'code' => '67600232',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 55000.0,
                'price' => 60000.0,
                'wholesale_price' => NULL,
                'qty' => 3.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => 1,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 45,
                'name' => 'TAKA',
                'code' => '81639204',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 3000.0,
                'price' => 3500.0,
                'wholesale_price' => NULL,
                'qty' => 2.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 1,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => 1,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 47,
                'name' => 'Apple 14',
                'code' => 'apple14',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 3,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 80000.0,
                'price' => 85000.0,
                'wholesale_price' => NULL,
                'qty' => 19.0,
                'alert_quantity' => 5.0,
                'daily_sale_objective' => 10.0,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 2,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 1,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 48,
                'name' => 'Laptop11',
                'code' => '1111111',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 30000.0,
                'price' => 32500.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => 2.0,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 49,
                'name' => 'Shirt',
                'code' => '112233',
                'type' => 'service',
                'barcode_symbology' => 'C39',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 0.0,
                'price' => 10.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 50,
                'name' => '14 pro max',
                'code' => '34692007',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 15000.0,
                'price' => 16000.0,
                'wholesale_price' => NULL,
                'qty' => 4.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 1,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => 1,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["RAM | ROM","Color"]',
                'variant_value' => '["128GB,256GB,512GB","SpaceBlack,Silver,Gold,DeepPurple"]',
                'is_active' => 1,
            ],
            [
                'id' => 51,
                'name' => 'Iphone 15 Pro Max',
                'code' => '63028277',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 0.0,
                'price' => 0.0,
                'wholesale_price' => NULL,
                'qty' => 145.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => 1,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["Condition","RAM | ROM","Color"]',
                'variant_value' => '["Brand New,Pre-Owned","256GB,512GB","BlackTitanium,WhiteTitanium,BlueTitanium,NaturalTitanium"]',
                'is_active' => 1,
            ],
            [
                'id' => 52,
                'name' => 'Product Test',
                'code' => 'KK',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 44.0,
                'price' => 23.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => '2024-05-18',
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => 1,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["Quantity","Size","Price","Color"]',
                'variant_value' => '["3KG,2KG,5KG","Large,Medium,Small","120,500,70","RED,GReen,Blue"]',
                'is_active' => 1,
            ],
            [
                'id' => 58,
                'name' => 'PRUEBA',
                'code' => '000',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 3,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 7.0,
                'price' => 7.0,
                'wholesale_price' => 7.0,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 59,
                'name' => 'Prueba Easy',
                'code' => '190',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 3,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 777.0,
                'price' => 777.0,
                'wholesale_price' => 777.0,
                'qty' => 0.0,
                'alert_quantity' => 120.0,
                'daily_sale_objective' => 65.0,
                'promotion' => 1,
                'promotion_price' => '150',
                'starting_date' => '2024-06-11',
                'last_date' => '2024-06-20',
                'tax_id' => 1,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 1,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => 1,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["BLANCO","NEGRO"]',
                'variant_value' => '["199","299"]',
                'is_active' => 1,
            ],
            [
                'id' => 60,
                'name' => 'Producto Prueba',
                'code' => '777',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 4,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 200.0,
                'price' => 200.0,
                'wholesale_price' => 200.0,
                'qty' => 0.0,
                'alert_quantity' => 10.0,
                'daily_sale_objective' => 10.0,
                'promotion' => 1,
                'promotion_price' => '175',
                'starting_date' => NULL,
                'last_date' => '2024-06-25',
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["NEGRO","NEGRO","NEGRO","NEGRO"]',
                'variant_value' => '["255","255","255","255"]',
                'is_active' => 1,
            ],
            [
                'id' => 61,
                'name' => 'IPHONE 14 PRO MAX',
                'code' => '01234',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 3,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 1500.0,
                'price' => 1500.0,
                'wholesale_price' => 1499.0,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => 15.0,
                'promotion' => 1,
                'promotion_price' => '1299',
                'starting_date' => '2024-06-11',
                'last_date' => '2024-06-25',
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => 1,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["Color Blanco","Color Rosa","RAM","Almacenamiento","Color Blanco","Color Rosa","RAM","Almacenamiento","Color Blanco","Color Rosa","RAM","Almacenamiento"]',
                'variant_value' => '["1600","1699","8,16,32","32,64,128","1600","1699","8,16,32","32,64,128","1600","1699","8,16,32","32,64,128"]',
                'is_active' => 1,
            ],
            [
                'id' => 62,
                'name' => 'T-Shirt',
                'code' => '003',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => NULL,
                'category_id' => 4,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 8000.0,
                'price' => 9500.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => 3.0,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => '2024-06-21',
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 1,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 63,
                'name' => 'Laptop',
                'code' => '83058761',
                'type' => 'standard',
                'barcode_symbology' => 'C39',
                'brand_id' => 2,
                'category_id' => 6,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 1000.0,
                'price' => 2000.0,
                'wholesale_price' => 500.0,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 64,
                'name' => 'Glass',
                'code' => '37580174',
                'type' => 'standard',
                'barcode_symbology' => 'UPCA',
                'brand_id' => NULL,
                'category_id' => 4,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 70.0,
                'price' => 100.0,
                'wholesale_price' => 60.0,
                'qty' => 0.0,
                'alert_quantity' => 5.0,
                'daily_sale_objective' => 3.0,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 1,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 65,
                'name' => 'test prod',
                'code' => '862837',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 3,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 100.0,
                'price' => 150.0,
                'wholesale_price' => 120.0,
                'qty' => 27.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 1,
                'is_variant' => 1,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => '["Size","Colour"]',
                'variant_value' => '["S,M,L","R,g,b"]',
                'is_active' => 1,
            ],
            [
                'id' => 66,
                'name' => 'Earphone True Wireless G70',
                'code' => '2312021280054',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 3,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 58.0,
                'price' => 17.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 67,
                'name' => 'T shirt',
                'code' => '07116185',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 1,
                'category_id' => 3,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 500.0,
                'price' => 1000.0,
                'wholesale_price' => 700.0,
                'qty' => 0.0,
                'alert_quantity' => 100.0,
                'daily_sale_objective' => 100.0,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => '2024-10-12',
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => 1,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ],
            [
                'id' => 68,
                'name' => 'kannadasan yogaraja',
                'code' => '38259140',
                'type' => 'standard',
                'barcode_symbology' => 'C128',
                'brand_id' => 2,
                'category_id' => 2,
                'unit_id' => 1,
                'purchase_unit_id' => 1,
                'sale_unit_id' => 1,
                'cost' => 200.0,
                'price' => 10.0,
                'wholesale_price' => NULL,
                'qty' => 0.0,
                'alert_quantity' => NULL,
                'daily_sale_objective' => NULL,
                'promotion' => NULL,
                'promotion_price' => NULL,
                'starting_date' => NULL,
                'last_date' => NULL,
                'tax_id' => NULL,
                'tax_method' => 1,
                'image' => NULL,
                'file' => NULL,
                'is_embeded' => NULL,
                'is_variant' => NULL,
                'is_batch' => NULL,
                'is_diff_price' => NULL,
                'is_imei' => NULL,
                'featured' => NULL,
                'product_list' => NULL,
                'variant_list' => NULL,
                'qty_list' => NULL,
                'price_list' => NULL,
                'product_details' => NULL,
                'variant_option' => NULL,
                'variant_value' => NULL,
                'is_active' => 1,
            ]
        ];
    }

    /**
     * Seed product purchases.
     *
     * @return void
     */
    private function seedProductPurchases(): void
    {
        if (DB::table('product_purchases')->count() > 0) {
            return;
        }

        DB::table('product_purchases')->insert([
            [
                'id' => 1,
                'purchase_id' => 1,
                'product_id' => 1,
                'product_batch_id' => null,
                'variant_id' => null,
                'imei_number' => null,
                'qty' => 10.0,
                'recieved' => 10.0,
                'return_qty' => 0.0,
                'purchase_unit_id' => 1,
                'net_unit_cost' => 10.0,
                'discount' => 0.0,
                'tax_rate' => 10.0,
                'tax' => 10.0,
                'total' => 110.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed product warehouse.
     *
     * @return void
     */
    private function seedProductWarehouse(): void
    {
        if (DB::table('product_warehouse')->count() > 0) {
            return;
        }

        DB::table('product_warehouse')->insert([
            [
                'id' => 1,
                'product_id' => '1',
                'product_batch_id' => null,
                'variant_id' => null,
                'imei_number' => null,
                'warehouse_id' => 1,
                'qty' => 10.0,
                'price' => 20.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed purchases.
     *
     * @return void
     */
    private function seedPurchases(): void
    {
        if (DB::table('purchases')->count() > 0) {
            return;
        }

        DB::table('purchases')->insert([
            [
                'id' => 1,
                'reference_no' => 'pr-20230528-125929',
                'user_id' => 1,
                'warehouse_id' => 1,
                'supplier_id' => null,
                'currency_id' => 1,
                'exchange_rate' => 1.0,
                'item' => 1,
                'total_qty' => 10,
                'total_discount' => 0.0,
                'total_tax' => 10.0,
                'total_cost' => 110.0,
                'order_tax_rate' => 0.0,
                'order_tax' => 0.0,
                'order_discount' => 0.0,
                'shipping_cost' => 0.0,
                'grand_total' => 110.0,
                'paid_amount' => 0.0,
                'status' => 1,
                'payment_status' => 1,
                'document' => null,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed suppliers.
     *
     * @return void
     */
    private function seedSuppliers(): void
    {
        if (DB::table('suppliers')->count() > 0) {
            return;
        }

        DB::table('suppliers')->insert([
            [
                'id' => 1,
                'name' => 'John Doe',
                'image' => null,
                'company_name' => 'Test Company',
                'vat_number' => null,
                'email' => 'john@gmail.com',
                'phone_number' => '231312',
                'address' => 'Test address',
                'city' => 'Test City',
                'state' => null,
                'postal_code' => null,
                'country' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed taxes.
     *
     * @return void
     */
    private function seedTaxes(): void
    {
        if (DB::table('taxes')->count() > 0) {
            return;
        }

        DB::table('taxes')->insert([
            [
                'id' => 1,
                'name' => 'VAT 10%',
                'rate' => 10.0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed units.
     *
     * @return void
     */
    private function seedUnits(): void
    {
        if (DB::table('units')->count() > 0) {
            return;
        }

        DB::table('units')->insert([
            [
                'id' => 1,
                'code' => 'Pc',
                'name' => 'piece',
                'base_unit' => null,
                'operator' => '*',
                'operation_value' => 1.0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed warehouses.
     *
     * @return void
     */
    private function seedWarehouses(): void
    {
        if (DB::table('warehouses')->count() > 0) {
            return;
        }

        DB::table('warehouses')->insert([
            [
                'id' => 1,
                'name' => 'Test Shop',
                'phone' => '9991111',
                'email' => null,
                'address' => 'Test address',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Get permission data array.
     *
     * This method should return the full permission data array.
     * For now, it returns an empty array as a placeholder.
     * The actual data should be copied from the original TenantDatabaseSeeder.php
     * (approximately lines 125-1012).
     *
     * @return array<int, array<string, mixed>> Permission data array
     */
    private function getPermissionData(): array
    {
        return [
            [
                'name' => 'taxes-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'taxes-export',
                'guard_name' => 'web',
            ],
            [
                'name' => 'taxes-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'taxes-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'taxes-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'taxes-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'units-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'units-export',
                'guard_name' => 'web',
            ],
            [
                'name' => 'units-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'units-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'units-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'units-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'products-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'products-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'products-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'products-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'products-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchases-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchases-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchases-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchases-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sales-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sales-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sales-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sales-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'quotes-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'quotes-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'quotes-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'quotes-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'transfers-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'transfers-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'transfers-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'transfers-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'returns-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'returns-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'returns-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'returns-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'customers-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'customers-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'customers-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'customers-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'suppliers-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'suppliers-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'suppliers-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'suppliers-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sale-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'customer-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'due-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'users-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'users-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'users-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'users-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'profit-loss',
                'guard_name' => 'web',
            ],
            [
                'name' => 'best-seller',
                'guard_name' => 'web',
            ],
            [
                'name' => 'daily-sale',
                'guard_name' => 'web',
            ],
            [
                'name' => 'monthly-sale',
                'guard_name' => 'web',
            ],
            [
                'name' => 'daily-purchase',
                'guard_name' => 'web',
            ],
            [
                'name' => 'monthly-purchase',
                'guard_name' => 'web',
            ],
            [
                'name' => 'payment-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'warehouse-stock-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product-qty-alert',
                'guard_name' => 'web',
            ],
            [
                'name' => 'supplier-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'expenses-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'expenses-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'expenses-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'expenses-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'general_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'mail_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'pos_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'hrm_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-return-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-return-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-return-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-return-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'account-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'balance-sheet',
                'guard_name' => 'web',
            ],
            [
                'name' => 'account-statement',
                'guard_name' => 'web',
            ],
            [
                'name' => 'department',
                'guard_name' => 'web',
            ],
            [
                'name' => 'attendance',
                'guard_name' => 'web',
            ],
            [
                'name' => 'payroll',
                'guard_name' => 'web',
            ],
            [
                'name' => 'employees-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'employees-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'employees-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'employees-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'user-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'stock_count',
                'guard_name' => 'web',
            ],
            [
                'name' => 'adjustment',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sms_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create_sms',
                'guard_name' => 'web',
            ],
            [
                'name' => 'print_barcode',
                'guard_name' => 'web',
            ],
            [
                'name' => 'empty_database',
                'guard_name' => 'web',
            ],
            [
                'name' => 'customer_group',
                'guard_name' => 'web',
            ],
            [
                'name' => 'gift_card',
                'guard_name' => 'web',
            ],
            [
                'name' => 'coupon',
                'guard_name' => 'web',
            ],
            [
                'name' => 'holiday',
                'guard_name' => 'web',
            ],
            [
                'name' => 'warehouse-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'warehouse',
                'guard_name' => 'web',
            ],
            [
                'name' => 'brands-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'brands-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'brands-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'brands-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'brands-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'brands-export',
                'guard_name' => 'web',
            ],
            [
                'name' => 'billers-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'billers-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'billers-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'billers-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'money-transfer',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delivery',
                'guard_name' => 'web',
            ],
            [
                'name' => 'send_notification',
                'guard_name' => 'web',
            ],
            [
                'name' => 'today_sale',
                'guard_name' => 'web',
            ],
            [
                'name' => 'today_profit',
                'guard_name' => 'web',
            ],
            [
                'name' => 'currency',
                'guard_name' => 'web',
            ],
            [
                'name' => 'backup_database',
                'guard_name' => 'web',
            ],
            [
                'name' => 'reward_point_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'revenue_profit_summary',
                'guard_name' => 'web',
            ],
            [
                'name' => 'cash_flow',
                'guard_name' => 'web',
            ],
            [
                'name' => 'monthly_summary',
                'guard_name' => 'web',
            ],
            [
                'name' => 'yearly_report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'discount_plan',
                'guard_name' => 'web',
            ],
            [
                'name' => 'discount',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product-expiry-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-payment-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-payment-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-payment-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase-payment-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sale-payment-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sale-payment-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sale-payment-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sale-payment-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'all_notification',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sale-report-chart',
                'guard_name' => 'web',
            ],
            [
                'name' => 'dso-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product_history',
                'guard_name' => 'web',
            ],
            [
                'name' => 'supplier-due-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'custom_field',
                'guard_name' => 'web',
            ],
            [
                'name' => 'incomes-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'incomes-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'incomes-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'incomes-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'packing_slip_challan',
                'guard_name' => 'web',
            ],
            [
                'name' => 'biller-report',
                'guard_name' => 'web',
            ],
            [
                'name' => 'payment_gateway_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'barcode_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'language_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'addons',
                'guard_name' => 'web',
            ],
            [
                'name' => 'account-selection',
                'guard_name' => 'web',
            ],
            [
                'name' => 'invoice_setting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'invoice_create_edit_delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'handle_discount',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchases-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sales-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'customers-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'billers-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'suppliers-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'categories-create',
                'guard_name' => 'web',
            ],
            [
                'name' => 'categories-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'categories-index',
                'guard_name' => 'web',
            ],
            [
                'name' => 'categories-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'categories-delete',
                'guard_name' => 'web',
            ],
            [
                'name' => 'categories-export',
                'guard_name' => 'web',
            ],
            [
                'name' => 'role_permission',
                'guard_name' => 'web',
            ],
            [
                'name' => 'cart-product-update',
                'guard_name' => 'web',
            ],
            [
                'name' => 'transfers-import',
                'guard_name' => 'web',
            ],
            [
                'name' => 'change_sale_date',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_product',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_purchase',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_sale',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_quotation',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_transfer',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_expense',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_income',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_accounting',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_hrm',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_people',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_reports',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sidebar_settings',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sale_export',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product_export',
                'guard_name' => 'web',
            ],
            [
                'name' => 'purchase_export',
                'guard_name' => 'web',
            ],
            [
                'name' => 'designations',
                'guard_name' => 'web',
            ],
            [
                'name' => 'shift',
                'guard_name' => 'web',
            ],
            [
                'name' => 'overtime',
                'guard_name' => 'web',
            ],
            [
                'name' => 'leave-type',
                'guard_name' => 'web',
            ],
            [
                'name' => 'leave',
                'guard_name' => 'web',
            ],
            [
                'name' => 'hrm-panel',
                'guard_name' => 'web',
            ],
            [
                'name' => 'sale-agents',
                'guard_name' => 'web',
            ],
        ];
    }

    /**
     * Get basic permission–role mappings for SaaS mode (Admin role).
     * Uses permission and role names; IDs are resolved when seeding.
     *
     * @return array<int, array{permission: string, role: string}>
     */
    private function getBasicPermissionsRole(): array
    {
        $role = 'Admin';
        $permissions = [
            'units-update', 'units-delete', 'units-create', 'units-index', 'units-import', 'units-export',
            'taxes-update', 'taxes-delete', 'taxes-create', 'taxes-index', 'taxes-import', 'taxes-export',
            'brands-index', 'brands-create', 'brands-update', 'brands-delete', 'brands-import', 'brands-export',
            'categories-index', 'categories-create', 'categories-import', 'categories-update', 'categories-delete', 'categories-export',
            'products-index', 'products-create', 'products-update', 'products-delete', 'products-import', 'products-export',
            'purchases-index', 'purchases-create', 'purchases-update', 'purchases-delete', 'purchases-import', 'purchases-export',
            'sales-index', 'sales-create', 'sales-update', 'sales-delete', 'sales-import', 'sales-export',
            'customers-index', 'customers-create', 'customers-update', 'customers-delete', 'customers-import', 'customers-export',
            'suppliers-index', 'suppliers-create', 'suppliers-update', 'suppliers-delete', 'suppliers-import', 'suppliers-export',
            'users-index', 'users-create', 'users-update', 'users-delete',
            'general_setting', 'mail_setting', 'pos_setting',
            'sms_setting', 'create_sms', 'print_barcode', 'empty_database',
            'customer_group', 'gift_card', 'coupon',
            'warehouse', 
            'billers-index', 'billers-create', 'billers-delete', 'money-transfer',
            'category', 'delivery', 'send_notification', 'today_sale', 'today_profit',
            'currency', 'revenue_profit_summary', 'cash_flow', 'monthly_summary', 'yearly_report',
            'discount_plan', 'discount',
            'purchase-payment-index', 'purchase-payment-create', 'purchase-payment-update', 'purchase-payment-delete',
            'sale-payment-index', 'sale-payment-create', 'sale-payment-update', 'sale-payment-delete',
            'all_notification', 'product_history', 'custom_field',
            'incomes-index', 'incomes-create', 'incomes-update', 'incomes-delete',
            'packing_slip_challan', 'payment_gateway_setting', 'barcode_setting', 'language_setting',
            'account-selection', 'invoice_setting', 'invoice_create_edit_delete', 'handle_discount',
            'purchases-import', 'sales-import', 'customers-import', 'billers-import',
            'role_permission', 'cart-product-update',
        ];

        $mappings = [];
        foreach ($permissions as $name) {
            $mappings[] = ['permission' => $name, 'role' => $role];
        }
        return $mappings;
    }

    /**
     * Seed mail settings.
     *
     * @return void
     */
    private function seedMailSettings(): void
    {
        if (DB::table('mail_settings')->count() > 0) {
            return;
        }

        $tenantData = self::$tenantData;
        $smtpConfig = config('mail.mailers.smtp');

        DB::table('mail_settings')->insert([
            [
                'id' => 1,
                'driver' => 'smtp',
                'host' => $smtpConfig['host'] ?? '127.0.0.1',
                'port' => (string) ($smtpConfig['port'] ?? 2525),
                'from_address' => (! empty($tenantData) && isset($tenantData['email'])) ? $tenantData['email'] : config('mail.from.address', 'noreply@example.com'),
                'from_name' => (! empty($tenantData) && isset($tenantData['site_title'])) ? $tenantData['site_title'] : config('mail.from.name', 'Quick Mart'),
                'username' => $smtpConfig['username'] ?? '',
                'password' => $smtpConfig['password'] ?? '',
                'encryption' => $smtpConfig['encryption'] ?? 'tls',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}




