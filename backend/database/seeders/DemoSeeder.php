<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        $suppliers = collect([
            ['name' => 'Atlas Trading Co.', 'contact_name' => 'Youssef Amrani', 'phone' => '+212600111222', 'email' => 'sales@atlastrading.com', 'country' => 'Morocco', 'lead_time_days' => 7],
            ['name' => 'Global Import Partners', 'contact_name' => 'Li Wei', 'phone' => '+8613800001111', 'email' => 'contact@gip-supply.com', 'country' => 'China', 'lead_time_days' => 20],
            ['name' => 'EuroStock Distribution', 'contact_name' => 'Marie Dubois', 'phone' => '+33612345678', 'email' => 'hello@eurostock.eu', 'country' => 'France', 'lead_time_days' => 5],
        ])->map(fn ($s) => Supplier::create($s));

        $productCatalog = [
            ['name' => 'Wireless Earbuds Pro', 'category' => 'Electronics', 'price' => 39.90],
            ['name' => 'Portable Bluetooth Speaker', 'category' => 'Electronics', 'price' => 29.50],
            ['name' => 'USB-C Fast Charger 30W', 'category' => 'Electronics', 'price' => 14.90],
            ['name' => 'Ceramic Non-Stick Pan Set', 'category' => 'Home', 'price' => 54.00],
            ['name' => 'Stainless Steel Water Bottle', 'category' => 'Home', 'price' => 12.90],
            ['name' => 'LED Desk Lamp', 'category' => 'Home', 'price' => 22.00],
            ['name' => 'Moisturizing Face Cream', 'category' => 'Beauty', 'price' => 18.50],
            ['name' => 'Argan Oil Hair Serum', 'category' => 'Beauty', 'price' => 15.90],
            ['name' => 'Leather Wallet', 'category' => 'Accessories', 'price' => 24.90],
            ['name' => 'Polarized Sunglasses', 'category' => 'Accessories', 'price' => 19.90],
            ['name' => 'Remote Control Car', 'category' => 'Toys', 'price' => 32.00],
            ['name' => 'Building Blocks Set 200pc', 'category' => 'Toys', 'price' => 27.50],
        ];

        $products = collect($productCatalog)->map(function ($p) use ($suppliers) {
            return Product::create([
                'supplier_id' => $suppliers->random()->id,
                'name' => $p['name'],
                'reference' => 'REF-' . strtoupper(substr(md5($p['name']), 0, 6)),
                'description' => "The {$p['name']} is a popular, reliable product frequently requested by customers. Good quality, competitive price.",
                'category' => $p['category'],
                'price' => $p['price'],
                'buy_price' => round($p['price'] * 0.6, 2),
                'availability' => fake()->randomElement(['in_stock', 'in_stock', 'in_stock', 'on_request', 'unavailable']),
                'is_active' => true,
            ]);
        });

        $clientSeed = [
            ['name' => 'Sara Benali', 'source' => 'whatsapp'],
            ['name' => 'John Carter', 'source' => 'website'],
            ['name' => 'Fatima Zahra', 'source' => 'whatsapp'],
            ['name' => 'Michael Smith', 'source' => 'website'],
            ['name' => 'Amina Idrissi', 'source' => 'manual'],
            ['name' => 'David Johnson', 'source' => 'whatsapp'],
            ['name' => 'Laila Mourad', 'source' => 'website'],
            ['name' => 'Robert Brown', 'source' => 'whatsapp'],
            ['name' => 'Nadia El Amrani', 'source' => 'manual'],
            ['name' => 'Emily Davis', 'source' => 'website'],
        ];

        $clients = collect($clientSeed)->map(function ($c) {
            return Client::create([
                'name' => $c['name'],
                'phone' => fake()->numerify('+1##########'),
                'whatsapp' => $c['source'] === 'whatsapp' ? fake()->numerify('+1##########') : null,
                'country' => fake()->randomElement(['USA', 'Canada', 'Morocco', 'France', 'Spain']),
                'city' => fake()->city(),
                'address' => fake()->streetAddress(),
                'notes' => null,
                'source' => $c['source'],
            ]);
        });

        $statuses = OrderStatus::cases();

        for ($i = 1; $i <= 15; $i++) {
            $client = $clients->random();
            $status = $statuses[($i - 1) % count($statuses)];
            $source = fake()->randomElement(['website', 'whatsapp', 'manual']);

            $order = Order::create([
                'reference' => 'ORD-' . now()->year . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'status' => $status,
                'total' => 0,
                'delivery_name' => $client->name,
                'delivery_phone' => $client->phone,
                'delivery_country' => $client->country,
                'delivery_city' => $client->city,
                'delivery_address' => $client->address,
                'tracking_number' => in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered]) ? strtoupper(fake()->bothify('TRK-########')) : null,
                'carrier' => in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered]) ? fake()->randomElement(['DHL', 'FedEx', 'UPS']) : null,
                'source' => $source,
                'notes' => null,
                'created_at' => now()->subDays(15 - $i),
                'updated_at' => now()->subDays(15 - $i),
            ]);

            $itemCount = fake()->numberBetween(1, 3);
            $total = 0;
            foreach ($products->random($itemCount) as $product) {
                $quantity = fake()->numberBetween(1, 4);
                $unitPrice = $product->price;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                ]);
                $total += $unitPrice * $quantity;
            }
            $order->update(['total' => $total]);

            if ($status === OrderStatus::Cancelled) {
                $history = [OrderStatus::RequestReceived, OrderStatus::Cancelled];
            } else {
                $history = [];
                foreach ($statuses as $stepStatus) {
                    if ($stepStatus === OrderStatus::Cancelled) {
                        continue;
                    }
                    $history[] = $stepStatus;
                    if ($stepStatus === $status) {
                        break;
                    }
                }
            }
            $createdAt = $order->created_at;
            foreach ($history as $index => $stepStatus) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $index === 0 ? null : $history[$index - 1]->value,
                    'to_status' => $stepStatus->value,
                    'changed_by' => 'admin',
                    'created_at' => $createdAt->copy()->addHours($index * 4),
                ]);
            }
        }
    }
}
