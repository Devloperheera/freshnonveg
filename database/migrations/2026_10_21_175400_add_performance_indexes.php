<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->indexIfMissing('orders', ['status', 'created_at'], 'orders_status_created_at_index');
        $this->indexIfMissing('orders', ['admin_seen_at'], 'orders_admin_seen_at_index');
        $this->indexIfMissing('complaints', ['status', 'created_at'], 'complaints_status_created_at_index');
        $this->indexIfMissing('cart_items', ['product_id'], 'cart_items_product_id_index');
        $this->indexIfMissing('cart_items', ['updated_at'], 'cart_items_updated_at_index');
        $this->indexIfMissing('wishlist_items', ['product_id'], 'wishlist_items_product_id_index');
        $this->indexIfMissing('wishlist_items', ['created_at'], 'wishlist_items_created_at_index');
        $this->indexIfMissing('products', ['status', 'is_featured'], 'products_status_featured_index');
        $this->indexIfMissing('products', ['category_id', 'status'], 'products_category_status_index');
        $this->indexIfMissing('banners', ['status'], 'banners_status_index');
        $this->indexIfMissing('categories', ['status'], 'categories_status_index');
        $this->indexIfMissing('users', ['status'], 'users_status_index');
        $this->indexIfMissing('wallet_transactions', ['user_id', 'type'], 'wallet_transactions_user_type_index');
    }

    public function down(): void
    {
        foreach ([
            'orders' => ['orders_status_created_at_index', 'orders_admin_seen_at_index'],
            'complaints' => ['complaints_status_created_at_index'],
            'cart_items' => ['cart_items_product_id_index', 'cart_items_updated_at_index'],
            'wishlist_items' => ['wishlist_items_product_id_index', 'wishlist_items_created_at_index'],
            'products' => ['products_status_featured_index', 'products_category_status_index'],
            'banners' => ['banners_status_index'],
            'categories' => ['categories_status_index'],
            'users' => ['users_status_index'],
            'wallet_transactions' => ['wallet_transactions_user_type_index'],
        ] as $table => $indexes) {
            foreach ($indexes as $name) {
                $this->dropIndexIfExists($table, $name);
            }
        }
    }

    /** @param  list<string>  $columns */
    private function indexIfMissing(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? '') === $name || ($index['columns'] ?? []) === $columns) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name): void {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existing = collect(Schema::getIndexes($table))->pluck('name')->all();
        if (! in_array($name, $existing, true)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name): void {
            $blueprint->dropIndex($name);
        });
    }
};
