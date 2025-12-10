<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\InboundOperation;
use App\Models\Invoice;
use App\Models\OutboundOperation;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Supplier;
use App\Observers\PurchaseOrderItemObserver;
use App\Observers\SalesOrderItemObserver;
use App\Policies\CustomerPolicy;
use App\Policies\InboundOperationPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\OutboundOperationPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\SalesOrderPolicy;
use App\Policies\SupplierPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Customer::class => CustomerPolicy::class,
        Supplier::class => SupplierPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        SalesOrder::class => SalesOrderPolicy::class,
        InboundOperation::class => InboundOperationPolicy::class,
        OutboundOperation::class => OutboundOperationPolicy::class,
        Invoice::class => InvoicePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS if enabled in environment
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Register observers
        PurchaseOrderItem::observe(PurchaseOrderItemObserver::class);
        SalesOrderItem::observe(SalesOrderItemObserver::class);
    }
}
