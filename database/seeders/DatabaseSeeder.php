<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['glow-daily-serum', 'Glow daily serum', 'Face care', 'A little glow, every morning.', 'A light, silky-feeling serum for an unhurried morning routine. A fictional personal-care product with a simple botanical-inspired scent.', '30 ml', 'serum', '#f1dfd2', 2800, 18, true],
            ['gentle-face-cleanser', 'Gentle face cleanser', 'Face care', 'A fresh start, made simple.', 'A soft-textured daily cleanser with a light botanical-inspired aroma. Designed as a fictional example of a straightforward everyday routine.', '150 ml', 'cleanser', '#e1e8df', 1900, 20, true],
            ['cloud-barrier-cream', 'Cloud daily cream', 'Face care', 'Soft texture. Quiet moments.', 'A rich-feeling face cream in a reusable-style jar. An original fictional product for a slow, simple personal-care ritual.', '50 ml', 'moisturizer', '#ebe4d8', 2600, 14, true],
            ['everyday-face-lotion', 'Everyday face lotion', 'Face care', 'An easy everyday companion.', 'A light-feeling lotion with a soft finish and a subtle fresh scent. This fictional product makes no SPF or treatment claims.', '75 ml', 'sunscreen', '#e7e1ef', 2400, 16, false],
            ['soft-reset-body-wash', 'Soft reset body wash', 'Body care', 'Make a small moment yours.', 'A smooth body wash with a botanical-inspired scent and a simple pump bottle. Fictional sample product for the Haven collection.', '300 ml', 'bodywash', '#e1e8df', 1800, 22, false],
            ['nourish-body-lotion', 'Nourish body lotion', 'Body care', 'A soft finish to your day.', 'A creamy-feeling body lotion with a subtle warm scent. A fictional everyday personal-care item with an easy-to-use pump.', '250 ml', 'bodylotion', '#f1dfd2', 2200, 15, true],
            ['balance-botanical-shampoo', 'Balance botanical shampoo', 'Hair care', 'A fresh take on wash day.', 'A fresh-scented shampoo in a simple bottle, created as fictional catalog data. An uncomplicated addition to an everyday hair routine.', '250 ml', 'shampoo', '#e7e1ef', 2100, 17, false],
            ['evening-mineral-soak', 'Evening mineral soak', 'Wellbeing', 'Leave a little room for rest.', 'A jar of lightly scented bath crystals for a quiet evening ritual. Fictional sample product; no therapeutic or medical benefits are claimed.', '400 g', 'bathsalts', '#ebe4d8', 1600, 6, false],
        ];
        foreach ($products as $row) {
            $data = array_combine(['slug', 'name', 'category', 'tagline', 'description', 'size', 'image', 'accent', 'price_cents', 'stock', 'featured'], $row);
            Product::firstOrCreate(['slug' => $data['slug']], $data + ['published' => true]);
        }
    }
}
