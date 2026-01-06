<?php

declare(strict_types=1);

namespace App\Traits;

use App\Mail\TenantCreate;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\landlord\MailSetting;
use App\Models\landlord\Package;
use App\Models\landlord\Tenant;
use App\Models\landlord\TenantPayment;
use App\Models\Product;
use Database\Seeders\Tenant\TenantDatabaseSeeder;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Ecommerce\Database\Seeders\EcommerceDatabaseSeeder;
use RuntimeException;

/**
 * Tenant Info Trait
 *
 * Provides methods for managing multi-tenant functionality including tenant creation,
 * permission management, slug generation, and subdomain management.
 * This trait is designed for SaaS applications using Laravel multi-tenancy.
 *
 * @package App\Traits
 */
trait TenantInfo
{
    use MailInfo;

    /**
     * Get the current tenant ID.
     *
     * @return string|null The tenant ID or null if not in tenant context
     */
    public function getTenantId(): ?string
    {
        if (function_exists('tenant') && tenant()) {
            return tenant()->id;
        }

        return null;
    }

    /**
     * Get available features configuration.
     *
     * Returns an array of all available features with their default states
     * and associated permission IDs.
     *
     * @return array<string, array<string, mixed>> Feature configuration array
     */
    public function features(): array
    {
        $features = [
            'product_and_categories' => [
                'name' => 'Product and Categories',
                'default' => true,
                'permission_ids' => '',
            ],
            'purchase_and_sale' => [
                'name' => 'Purchase and Sale',
                'default' => true,
                'permission_ids' => '',
            ],
            'sale_return' => [
                'name' => 'Sale Return',
                'default' => false,
                'permission_ids' => '24,25,26,27,',
            ],
            'purchase_return' => [
                'name' => 'Purchase Return',
                'default' => false,
                'permission_ids' => '63,64,65,66,',
            ],
            'expense' => [
                'name' => 'Expense',
                'default' => false,
                'permission_ids' => '55,56,57,58,',
            ],
            'income' => [
                'name' => 'Income',
                'default' => false,
                'permission_ids' => '127,128,129,130,',
            ],
            'transfer' => [
                'name' => 'Stock Transfer',
                'default' => false,
                'permission_ids' => '20,21,22,23,',
            ],
            'quotation' => [
                'name' => 'Quotation',
                'default' => false,
                'permission_ids' => '16,17,18,19,',
            ],
            'delivery' => [
                'name' => 'Product Delivery',
                'default' => false,
                'permission_ids' => '99,',
            ],
            'stock_count_and_adjustment' => [
                'name' => 'Stock Count and Adjustment',
                'default' => false,
                'permission_ids' => '78,79,',
            ],
            'report' => [
                'name' => 'Report',
                'default' => false,
                'permission_ids' => '36,37,38,39,40,45,46,47,48,49,50,51,52,53,54,77,90,112,122,123,125,132,',
            ],
            'hrm' => [
                'name' => 'HRM',
                'default' => false,
                'permission_ids' => '62,70,71,72,73,74,75,76,89,',
            ],
            'accounting' => [
                'name' => 'Accounting',
                'default' => false,
                'permission_ids' => '67,68,69,97,',
            ],
        ];

        // Add module-specific features if modules exist
        $moduleFeatures = [
            'manufacturing' => ['name' => 'Manufacturing', 'default' => false, 'permission_ids' => '136,'],
            'ecommerce' => ['name' => 'Ecommerce', 'default' => false, 'permission_ids' => '136,'],
            'woocommerce' => ['name' => 'Woocommerce', 'default' => false, 'permission_ids' => '136,'],
            'restaurant' => ['name' => 'Restaurant', 'default' => false, 'permission_ids' => '136,'],
        ];

        foreach ($moduleFeatures as $module => $config) {
            if (File::exists(base_path("Modules/{$module}"))) {
                $features[$module] = $config;
            }
        }

        return $features;
    }

    /**
     * Create a new tenant with all necessary setup.
     *
     * This method is called from tenantCheckout() in the payment controller.
     * It handles the complete tenant creation process including:
     * - Package and feature processing
     * - Tenant and domain creation
     * - Payment recording
     * - Database seeding
     * - Logo file copying
     * - Ecommerce module setup (if applicable)
     * - Subdomain creation
     * - Welcome email sending
     *
     * @param object $request The request object containing tenant creation data:
     *                       - package_id: The selected package ID
     *                       - subscription_type: 'monthly' or 'yearly'
     *                       - tenant: The tenant identifier/subdomain
     *                       - name: Tenant owner name
     *                       - email: Tenant owner email
     *                       - password: Tenant owner password (will be hashed)
     *                       - phone_number: Tenant owner phone
     *                       - company_name: Tenant company name
     *                       - price: Package price
     *                       - payment_method: Optional payment method
     * @return string Success message
     * @throws RuntimeException If required data is missing
     */
    public function createTenant(object $request): string
    {
        // Get general settings with cache support
        $generalSetting = cache()->has('general_setting')
            ? cache()->get('general_setting')
            : DB::table('general_settings')->latest()->first();

        if (!$generalSetting) {
            throw new RuntimeException('General settings not found. Cannot create tenant.');
        }

        // Get package information
        // Note: Adjust namespace if your landlord models are in a different location
        $package = $this->getPackageModel()::select('is_free_trial', 'features', 'role_permission_values')
            ->find($request->package_id);

        if (!$package) {
            throw new RuntimeException("Package with ID {$request->package_id} not found.");
        }

        $features = json_decode($package->features ?? '[]', true) ?? [];
        $modules = $this->extractModulesFromFeatures($features);

        // Calculate expiry date
        $numberOfDaysToExpired = $this->calculateExpiryDays(
            (bool)($package->is_free_trial ?? false),
            $request->subscription_type ?? 'monthly',
            (int)($generalSetting->free_trial_limit ?? 0)
        );

        $paidBy = $request->payment_method ?? '';

        // Create tenant
        $tenant = $this->getTenantModel()::create(['id' => $request->tenant]);
        $centralDomain = config('app.central_domain', env('CENTRAL_DOMAIN', ''));

        if (method_exists($tenant, 'domains')) {
            $tenant->domains()->create(['domain' => "{$request->tenant}.{$centralDomain}"]);
        }

        // Record payment if provided
        if ($paidBy) {
            $this->getTenantPaymentModel()::create([
                'tenant_id' => $tenant->id,
                'amount' => (float)($request->price ?? 0),
                'paid_by' => $paidBy,
            ]);
        }

        // Prepare tenant data for seeder
        $packagePermissionsRole = $this->parsePackagePermissions($package->role_permission_values ?? '');

        $tenantData = [
            'site_title' => $generalSetting->site_title ?? '',
            'site_logo' => $generalSetting->site_logo ?? '',
            'package_id' => (int)$request->package_id,
            'subscription_type' => $request->subscription_type ?? 'monthly',
            'developed_by' => $generalSetting->developed_by ?? 'Lioncoders',
            'modules' => $modules,
            'expiry_date' => date('Y-m-d', strtotime("+{$numberOfDaysToExpired} days")),
            'name' => (string)$request->name,
            'email' => (string)$request->email,
            'password' => bcrypt((string)$request->password),
            'phone' => (string)($request->phone_number ?? ''),
            'company_name' => (string)($request->company_name ?? ''),
            'package_permissions_role' => $packagePermissionsRole,
        ];

        // Run tenant database seeder
        TenantDatabaseSeeder::$tenantData = $tenantData;
        Artisan::call('tenants:seed', [
            '--tenants' => $request->tenant,
            '--force' => true,
        ]);

        // Copy logo file
        $logoPath = public_path("landlord/images/logo/") . ($generalSetting->site_logo ?? '');
        $targetPath = public_path("logo/") . ($generalSetting->site_logo ?? '');
        if (File::exists($logoPath) && !empty($generalSetting->site_logo)) {
            File::copy($logoPath, $targetPath);
        }

        // Setup ecommerce module if included
        if ($modules && str_contains($modules, 'ecommerce')) {
            $this->setupEcommerceModule($tenant, $generalSetting);
        }

        // Add subdomain if not using wildcard
        if (!config('app.wildcard_subdomain', env('WILDCARD_SUBDOMAIN', false))) {
            $this->addSubdomain($tenant);
        }

        // Update tenant information
        $tenant->update([
            'package_id' => (int)$request->package_id,
            'subscription_type' => $request->subscription_type ?? 'monthly',
            'company_name' => (string)($request->company_name ?? ''),
            'phone_number' => (string)($request->phone_number ?? ''),
            'email' => (string)$request->email,
            'expiry_date' => date('Y-m-d', strtotime("+{$numberOfDaysToExpired} days")),
        ]);

        // Send welcome email
        return $this->sendWelcomeEmail($request, $generalSetting);
    }

    /**
     * Get the Package model class.
     * Override this method if your landlord models are in a different namespace.
     *
     * @return string
     */
    protected function getPackageModel(): string
    {
        return Package::class;
    }

    /**
     * Extract module names from package features.
     *
     * @param array<string> $features Array of feature names
     * @return string|null Comma-separated module names or null
     */
    private function extractModulesFromFeatures(array $features): ?string
    {
        $modules = [];
        $moduleNames = ['manufacturing', 'ecommerce', 'woocommerce'];

        foreach ($moduleNames as $module) {
            if (in_array($module, $features, true)) {
                $modules[] = $module;
            }
        }

        return count($modules) > 0 ? implode(',', $modules) : null;
    }

    /**
     * Calculate number of days until expiry based on subscription type.
     *
     * @param bool $isFreeTrial Whether the package is a free trial
     * @param string $subscriptionType 'monthly' or 'yearly'
     * @param int $freeTrialLimit Number of days for free trial
     * @return int Number of days until expiry
     */
    private function calculateExpiryDays(bool $isFreeTrial, string $subscriptionType, int $freeTrialLimit): int
    {
        if ($isFreeTrial) {
            return $freeTrialLimit;
        }

        return match ($subscriptionType) {
            'monthly' => 30,
            'yearly' => 365,
            default => 30,
        };
    }

    /**
     * Get the Tenant model class.
     * Override this method if your landlord models are in a different namespace.
     *
     * @return string
     */
    protected function getTenantModel(): string
    {
        return Tenant::class;
    }

    /**
     * Get the TenantPayment model class.
     * Override this method if your landlord models are in a different namespace.
     *
     * @return string
     */
    protected function getTenantPaymentModel(): string
    {
        return TenantPayment::class;
    }

    /**
     * Parse package permission role values into array format.
     *
     * @param string $rolePermissionValues String in format "(permission_id,role_id),(permission_id,role_id)"
     * @return array<int, array<string, int>> Array of permission-role mappings
     */
    private function parsePackagePermissions(string $rolePermissionValues): array
    {
        if (empty($rolePermissionValues)) {
            return [];
        }

        $packPermRolePairs = explode('),(', trim($rolePermissionValues, '()'));

        if ($packPermRolePairs === ['']) {
            return [];
        }

        return array_map(function ($pkPermRoleP) {
            [$permissionId, $roleId] = explode(',', $pkPermRoleP);
            return [
                'permission_id' => (int)$permissionId,
                'role_id' => (int)$roleId,
            ];
        }, $packPermRolePairs);
    }

    /**
     * Setup ecommerce module for tenant.
     *
     * @param mixed $tenant The tenant instance
     * @param object $generalSetting General settings object
     * @return void
     */
    private function setupEcommerceModule(mixed $tenant, object $generalSetting): void
    {
        if (!class_exists(EcommerceDatabaseSeeder::class)) {
            return;
        }

        // Run ecommerce seeder
        Artisan::call('tenants:seed', [
            '--tenants' => $tenant->id,
            '--class' => EcommerceDatabaseSeeder::class,
            '--force' => true,
        ]);

        // Update slugs and icons
        $tenant->run(function () use ($generalSetting) {
            $this->brandSlug();
            $this->categorySlug();
            $this->productSlug();

            // Update category icons
            DB::table('categories')->whereIn('id', [1, 6, 12, 23, 29, 30, 31, 33, 39])->update([
                'icon' => DB::raw("
                    CASE
                        WHEN id = 1 THEN '20240117121500.png'
                        WHEN id = 6 THEN '20240117121330.png'
                        WHEN id = 12 THEN '20240117121400.png'
                        WHEN id = 23 THEN '20240117121523.png'
                        WHEN id = 29 THEN '20240117121304.png'
                        WHEN id = 30 THEN '20240117121238.png'
                        WHEN id = 31 THEN '20240117122452.png'
                        WHEN id = 33 THEN '20240117121224.png'
                        WHEN id = 39 THEN '20240204050037.png'
                    END
                "),
            ]);

            // Set all products as online
            DB::table('products')->update(['is_online' => 1]);
        });

        // Copy logo to frontend
        $logoPath = public_path("logo/") . ($generalSetting->site_logo ?? '');
        $frontendLogoPath = public_path("frontend/images/") . ($generalSetting->site_logo ?? '');
        if (File::exists($logoPath) && !empty($generalSetting->site_logo)) {
            File::copy($logoPath, $frontendLogoPath);
        }
    }

    /**
     * Generate slug for brands that don't have one.
     *
     * @return void
     */
    public function brandSlug(): void
    {
        Brand::whereNull('slug')
            ->chunkById(100, function ($brands) {
                foreach ($brands as $brand) {
                    $brand->slug = Str::slug($brand->name, '-');
                    $brand->save();
                }
            });
    }

    /**
     * Generate slug for categories that don't have one.
     *
     * @return void
     */
    public function categorySlug(): void
    {
        Category::whereNull('slug')
            ->chunkById(100, function ($categories) {
                foreach ($categories as $category) {
                    $category->slug = Str::slug($category->name, '-');
                    $category->save();
                }
            });
    }

    /**
     * Generate slug for products that don't have one.
     *
     * @return void
     */
    public function productSlug(): void
    {
        Product::whereNull('slug')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $product->slug = Str::slug($product->name, '-');
                    $product->save();
                }
            });
    }

    /**
     * Add subdomain for a tenant using cPanel or Plesk API.
     *
     * @param mixed $tenant The tenant instance
     * @return bool True if successful, false otherwise
     */
    public function addSubdomain(mixed $tenant): bool
    {
        $serverType = config('app.server_type', env('SERVER_TYPE'));
        $subdomain = $tenant->id ?? '';

        if (empty($subdomain)) {
            Log::error('TenantInfo: Cannot add subdomain - tenant ID is empty');
            return false;
        }

        try {
            return match ($serverType) {
                'cpanel' => $this->addSubdomainCpanel($subdomain),
                'plesk' => $this->addSubdomainPlesk($tenant, $subdomain),
                default => $this->handleUnknownServerType($serverType),
            };
        } catch (Exception $e) {
            Log::error('TenantInfo: Exception while adding subdomain', [
                'message' => $e->getMessage(),
                'server_type' => $serverType,
            ]);

            return false;
        }
    }

    /**
     * Add subdomain using cPanel API.
     *
     * @param string $subdomain The subdomain to add
     * @return bool True if successful, false otherwise
     */
    private function addSubdomainCpanel(string $subdomain): bool
    {
        $centralDomain = config('app.central_domain', env('CENTRAL_DOMAIN', ''));
        $cpanelUser = config('app.cpanel_user_name', env('CPANEL_USER_NAME', ''));
        $cpanelApiKey = config('app.cpanel_api_key', env('CPANEL_API_KEY', ''));
        $rootDomain = config('app.root_domain', env('ROOT_DOMAIN'));

        if (empty($centralDomain) || empty($cpanelUser) || empty($cpanelApiKey)) {
            Log::error('TenantInfo: Missing cPanel configuration');
            return false;
        }

        $dir = $rootDomain ? 'public_html' : $centralDomain;
        $url = "https://{$centralDomain}:2083/json-api/cpanel?" . http_build_query([
                'cpanel_jsonapi_func' => 'addsubdomain',
                'cpanel_jsonapi_module' => 'SubDomain',
                'cpanel_jsonapi_version' => '2',
                'domain' => $subdomain,
                'rootdomain' => $centralDomain,
                'dir' => $dir,
            ]);

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Authorization' => "cpanel {$cpanelUser}:{$cpanelApiKey}",
                'Content-Type' => 'text/plain',
            ])->withoutVerifying()->post($url);

            $statusCode = $response->status();
            return $statusCode >= 200 && $statusCode < 300;
        } catch (Exception $e) {
            Log::error('TenantInfo: cPanel API error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Add subdomain using Plesk API.
     *
     * @param mixed $tenant The tenant instance
     * @param string $subdomain The subdomain to add
     * @return bool True if successful, false otherwise
     */
    private function addSubdomainPlesk(mixed $tenant, string $subdomain): bool
    {
        $host = config('app.central_domain', env('CENTRAL_DOMAIN', ''));
        $username = config('app.plesk_user_name', env('PLESK_USER_NAME', ''));
        $password = config('app.plesk_password', env('PLESK_PASSWORD', ''));

        if (empty($host) || empty($username) || empty($password)) {
            Log::error('TenantInfo: Missing Plesk configuration');
            return false;
        }

        $pleskApiUrl = "https://{$host}:8443/api/v2/domains";
        $domainData = [
            'name' => "{$subdomain}.{$host}",
            'hosting_type' => 'virtual',
            'hosting_settings' => [
                'document_root' => '/httpdocs',
            ],
            'parent_domain' => [
                'name' => $host,
            ],
        ];

        try {
            /** @var Response $response */
            $response = Http::withBasicAuth($username, $password)
                ->withoutVerifying()
                ->post($pleskApiUrl, $domainData);

            $statusCode = $response->status();
            if ($statusCode >= 200 && $statusCode < 300) {
                /** @var array<string, mixed>|null $data */
                $data = $response->json();
                if (is_array($data) && isset($data['id']) && method_exists($tenant, 'setInternal')) {
                    $tenant->setInternal('domain_id', $data['id']);
                }
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error('TenantInfo: Plesk API error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Handle unknown server type.
     *
     * @param string $serverType The server type
     * @return bool Always returns false
     */
    private function handleUnknownServerType(string $serverType): bool
    {
        Log::warning('TenantInfo: Unknown server type', ['server_type' => $serverType]);
        return false;
    }

    /**
     * Send welcome email to new tenant.
     *
     * @param object $request The request object
     * @param object $generalSetting General settings object
     * @return string Success or error message
     */
    private function sendWelcomeEmail(object $request, object $generalSetting): string
    {
        $mailSetting = $this->getMailSettingModel()::latest()->first();

        if (!$mailSetting) {
            return 'Client created successfully. Please setup your <a href="mail_setting">mail setting</a> to send mail.';
        }

        try {
            $this->setMailInfo($mailSetting);

            $mailData = [
                'email' => (string)$request->email,
                'company_name' => (string)($request->company_name ?? ''),
                'superadmin_company_name' => (string)($generalSetting->site_title ?? ''),
                'subdomain' => (string)$request->tenant,
                'name' => (string)$request->name,
                'password' => (string)$request->password, // Plain password for email
                'superadmin_email' => (string)($generalSetting->email ?? ''),
            ];

            $tenantCreateClass = TenantCreate::class;
            if (class_exists($tenantCreateClass)) {
                /** @var Mailable $mailable */
                $mailable = new $tenantCreateClass($mailData);
                Mail::to($mailData['email'])->send($mailable);
            }

            return 'Client created successfully.';
        } catch (Exception $e) {
            Log::error('TenantInfo: Failed to send welcome email', [
                'message' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return 'Client created successfully. Please setup your <a href="mail_setting">mail setting</a> to send mail.';
        }
    }

    /**
     * Get the MailSetting model class.
     * Override this method if your landlord models are in a different namespace.
     *
     * @return string
     */
    protected function getMailSettingModel(): string
    {
        return MailSetting::class;
    }

    /**
     * Change permissions for an existing tenant.
     *
     * This method updates tenant permissions, runs migrations and seeders,
     * and optionally sets up ecommerce module.
     *
     * @param mixed $tenant The tenant instance
     * @param string|null $abandonedPermissionIds JSON string of permission IDs to remove
     * @param string|null $permissionIds JSON string of permission IDs to add
     * @param int $packageId The package ID
     * @param string|null $modules Comma-separated module names
     * @param string|null $expiryDate Expiry date in Y-m-d format
     * @param string|null $subscriptionType Subscription type ('monthly' or 'yearly')
     * @return void
     */
    public function changePermission(
        mixed   $tenant,
        ?string $abandonedPermissionIds,
        ?string $permissionIds,
        int     $packageId,
        ?string $modules = null,
        ?string $expiryDate = null,
        ?string $subscriptionType = null
    ): void
    {
        $abandonedPermissionIdsArray = json_decode($abandonedPermissionIds ?? '[]', true) ?? [];
        $permissionIdsArray = json_decode($permissionIds ?? '[]', true) ?? [];

        $tenant->run(function () use ($abandonedPermissionIdsArray, $permissionIdsArray, $packageId, $modules, $expiryDate, $subscriptionType) {
            // Remove abandoned permissions
            if (count($abandonedPermissionIdsArray) > 0) {
                DB::table('role_has_permissions')
                    ->whereIn('permission_id', $abandonedPermissionIdsArray)
                    ->delete();
            }

            // Add new permissions
            if (count($permissionIdsArray) > 0) {
                foreach ($permissionIdsArray as $permissionId) {
                    $exists = DB::table('role_has_permissions')
                        ->where('role_id', 1)
                        ->where('permission_id', $permissionId)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_has_permissions')->insert([
                            'role_id' => 1,
                            'permission_id' => $permissionId,
                        ]);
                    }
                }
            }

            // Update general settings
            $generalSetting = GeneralSetting::latest()->first();
            if (!$generalSetting) {
                throw new RuntimeException('General settings not found in tenant database.');
            }

            $updateData = ['package_id' => $packageId, 'modules' => $modules];
            if ($expiryDate !== null && $subscriptionType !== null) {
                $updateData['expiry_date'] = $expiryDate;
                $updateData['subscription_type'] = $subscriptionType;
            }

            $generalSetting->update($updateData);

            // Run migrations and seeders
            Artisan::call('tenants:migrate', [
                '--tenants' => tenant()->id,
                '--force' => true,
            ]);

            Artisan::call('tenants:seed', [
                '--tenants' => tenant()->id,
                '--force' => true,
            ]);

            // Setup ecommerce if module is included
            if ($modules && str_contains($modules, 'ecommerce')) {
                $this->setupEcommerceForChangePermission($generalSetting);
            }
        });
    }

    /**
     * Setup ecommerce module during permission change.
     *
     * @param GeneralSetting $generalSetting General settings model
     * @return void
     */
    private function setupEcommerceForChangePermission(GeneralSetting $generalSetting): void
    {
        if (class_exists(EcommerceDatabaseSeeder::class)) {
            Artisan::call('tenants:seed', [
                '--tenants' => tenant()->id,
                '--class' => EcommerceDatabaseSeeder::class,
                '--force' => true,
            ]);
        }

        $this->categorySlug();
        $this->brandSlug();
        $this->productSlug();

        // Copy logo to frontend
        $logoPath = public_path("logo/") . ($generalSetting->site_logo ?? '');
        $frontendLogoPath = public_path("frontend/images/") . ($generalSetting->site_logo ?? '');
        if (File::exists($logoPath) && !empty($generalSetting->site_logo)) {
            File::copy($logoPath, $frontendLogoPath);
        }
    }

    /**
     * Delete subdomain for a tenant using cPanel or Plesk API.
     *
     * @param mixed $tenant The tenant instance
     * @return bool True if successful, false otherwise
     */
    public function deleteSubdomain(mixed $tenant): bool
    {
        $serverType = config('app.server_type', env('SERVER_TYPE'));
        $subdomain = $tenant->id ?? '';

        if (empty($subdomain)) {
            Log::error('TenantInfo: Cannot delete subdomain - tenant ID is empty');
            return false;
        }

        try {
            return match ($serverType) {
                'cpanel' => $this->deleteSubdomainCpanel($subdomain),
                'plesk' => $this->deleteSubdomainPlesk($tenant),
                default => $this->handleUnknownServerType($serverType),
            };
        } catch (Exception $e) {
            Log::error('TenantInfo: Exception while deleting subdomain', [
                'message' => $e->getMessage(),
                'server_type' => $serverType,
            ]);

            return false;
        }
    }

    /**
     * Delete subdomain using cPanel API.
     *
     * @param string $subdomain The subdomain to delete
     * @return bool True if successful, false otherwise
     */
    private function deleteSubdomainCpanel(string $subdomain): bool
    {
        $centralDomain = config('app.central_domain', env('CENTRAL_DOMAIN', ''));
        $cpanelUser = config('app.cpanel_user_name', env('CPANEL_USER_NAME', ''));
        $cpanelApiKey = config('app.cpanel_api_key', env('CPANEL_API_KEY', ''));

        if (empty($centralDomain) || empty($cpanelUser) || empty($cpanelApiKey)) {
            Log::error('TenantInfo: Missing cPanel configuration');
            return false;
        }

        $url = "https://{$centralDomain}:2083/json-api/cpanel?" . http_build_query([
                'cpanel_jsonapi_func' => 'delsubdomain',
                'cpanel_jsonapi_module' => 'SubDomain',
                'cpanel_jsonapi_version' => '2',
                'domain' => "{$subdomain}.{$centralDomain}",
            ]);

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Authorization' => "cpanel {$cpanelUser}:{$cpanelApiKey}",
                'Content-Type' => 'text/plain',
            ])->withoutVerifying()->post($url);

            $statusCode = $response->status();
            return $statusCode >= 200 && $statusCode < 300;
        } catch (Exception $e) {
            Log::error('TenantInfo: cPanel API error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete subdomain using Plesk API.
     *
     * @param mixed $tenant The tenant instance
     * @return bool True if successful, false otherwise
     */
    private function deleteSubdomainPlesk(mixed $tenant): bool
    {
        $host = config('app.central_domain', env('CENTRAL_DOMAIN', ''));
        $username = config('app.plesk_user_name', env('PLESK_USER_NAME', ''));
        $password = config('app.plesk_password', env('PLESK_PASSWORD', ''));

        if (empty($host) || empty($username) || empty($password)) {
            Log::error('TenantInfo: Missing Plesk configuration');
            return false;
        }

        $domainId = null;
        if (method_exists($tenant, 'getInternal')) {
            $domainId = $tenant->getInternal('domain_id');
        }

        if (empty($domainId)) {
            Log::error('TenantInfo: Cannot delete subdomain - domain ID not found');
            return false;
        }

        $pleskApiUrl = "https://{$host}:8443/api/v2/domains/{$domainId}";

        try {
            /** @var Response $response */
            $response = Http::withBasicAuth($username, $password)
                ->withoutVerifying()
                ->delete($pleskApiUrl);

            $statusCode = $response->status();
            return $statusCode >= 200 && $statusCode < 300;
        } catch (Exception $e) {
            Log::error('TenantInfo: Plesk API error', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
