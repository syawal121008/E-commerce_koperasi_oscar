<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->error('Tidak ada user admin di database. Buat dulu user admin sebelum seed product.');
            return;
        }

        // Ambil ID kategori berdasarkan nama
        $makananId   = Category::where('name', 'Makanan')->first()->category_id;
        $minumanId   = Category::where('name', 'Minuman')->first()->category_id;
        $alatTulisId = Category::where('name', 'Alat Tulis')->first()->category_id;
        $atributId   = Category::where('name', 'Atribut & Perlengkapan Lainnya')->first()->category_id;

        $products = [
            // ===== Makanan =====
            [
                'name' => 'Indomie Goreng',
                'description' => "Mi goreng instan dengan rasa original yang gurih dan nikmat. 
Mudah dimasak hanya dalam beberapa menit sehingga praktis untuk siapa saja. 
Cocok disantap kapan saja, baik siang maupun malam, sebagai teman santai ataupun pengganjal lapar.",
                'price' => 3500, 'modal_price' => 2500, 'stock' => 100,
                'category_id' => $makananId,
            ],
            [
                'name' => 'Chitato Sapi Panggang',
                'description' => "Keripik kentang dengan rasa sapi panggang yang lezat dan menggoda. 
Renyah di setiap gigitan dengan bumbu khas Chitato yang bikin nagih. 
Pas untuk camilan saat santai atau kumpul bersama teman-teman.",
                'price' => 8000, 'modal_price' => 6000, 'stock' => 80,
                'category_id' => $makananId,
            ],
            [
                'name' => 'Oreo Original',
                'description' => "Biskuit sandwich legendaris dengan krim vanilla manis dan lembut. 
Bisa dinikmati dengan cara diputar, dijilat, lalu dicelup ke susu. 
Cemilan klasik yang disukai oleh anak-anak hingga orang dewasa.",
                'price' => 6000, 'modal_price' => 4000, 'stock' => 120,
                'category_id' => $makananId,
            ],
            [
                'name' => 'SilverQueen Chunky Bar',
                'description' => "Cokelat kacang mete khas Indonesia yang terkenal enak dan berkualitas. 
Potongan kacang mete yang besar berpadu dengan cokelat lembut. 
Cocok untuk dinikmati sendiri atau dibagikan bersama orang terdekat.",
                'price' => 15000, 'modal_price' => 11000, 'stock' => 60,
                'category_id' => $makananId,
            ],

            // ===== Minuman =====
            [
                'name' => 'Teh Botol Sosro',
                'description' => "Minuman teh jasmine asli Indonesia yang menyegarkan. 
Rasanya manis pas, cocok diminum dingin maupun suhu ruang. 
Teman setia saat makan siang, bepergian, atau kumpul santai bersama keluarga.",
                'price' => 5000, 'modal_price' => 3500, 'stock' => 50,
                'category_id' => $minumanId,
            ],
            [
                'name' => 'Aqua Botol 600ml',
                'description' => "Air mineral murni dalam kemasan botol 600ml yang praktis. 
Menjaga tubuh tetap segar dan terhidrasi sepanjang hari. 
Mudah dibawa ke sekolah, kantor, atau aktivitas luar ruangan.",
                'price' => 4000, 'modal_price' => 2000, 'stock' => 200,
                'category_id' => $minumanId,
            ],
            [
                'name' => 'Pocari Sweat',
                'description' => "Minuman isotonik yang membantu mengembalikan ion tubuh dengan cepat. 
Mencegah dehidrasi dan menjaga kondisi tubuh tetap segar. 
Cocok dikonsumsi setelah olahraga atau aktivitas fisik berat.",
                'price' => 8000, 'modal_price' => 5000, 'stock' => 100,
                'category_id' => $minumanId,
            ],
            [
                'name' => 'Good Day Cappuccino',
                'description' => "Minuman kopi instan rasa cappuccino yang creamy dan nikmat. 
Hadir dalam kemasan praktis untuk menemani waktu istirahat singkat. 
Cocok diminum saat butuh semangat tambahan di pagi atau sore hari.",
                'price' => 7000, 'modal_price' => 5000, 'stock' => 90,
                'category_id' => $minumanId,
            ],

            // ===== Alat Tulis =====
            [
                'name' => 'Pulpen Pilot Hitam',
                'description' => "Pulpen gel hitam yang nyaman digunakan untuk menulis. 
Tinta mengalir lancar sehingga tulisan terlihat lebih jelas. 
Pilihan tepat untuk keperluan sekolah, kantor, maupun catatan harian.",
                'price' => 7000, 'modal_price' => 4000, 'stock' => 200,
                'category_id' => $alatTulisId,
            ],
            [
                'name' => 'Pensil 2B Faber Castell',
                'description' => "Pensil 2B berkualitas yang pas untuk ujian maupun menggambar. 
Tajam dan tidak mudah patah saat digunakan menulis. 
Menjadi pilihan utama bagi siswa dan mahasiswa.",
                'price' => 3000, 'modal_price' => 1500, 'stock' => 300,
                'category_id' => $alatTulisId,
            ],
            [
                'name' => 'Penghapus Staedtler',
                'description' => "Penghapus karet putih dengan kualitas tinggi. 
Mampu menghapus tulisan pensil dengan bersih tanpa merusak kertas. 
Ideal untuk kegiatan belajar dan pekerjaan kantor.",
                'price' => 5000, 'modal_price' => 2500, 'stock' => 150,
                'category_id' => $alatTulisId,
            ],
            [
                'name' => 'Buku Tulis University 58 Lembar',
                'description' => "Buku tulis bergaris dengan isi 58 lembar. 
Kertas tebal yang nyaman digunakan menulis dengan pulpen maupun pensil. 
Cocok untuk pelajar, mahasiswa, ataupun catatan sehari-hari.",
                'price' => 4000, 'modal_price' => 2000, 'stock' => 500,
                'category_id' => $alatTulisId,
            ],

            // ===== Atribut & Perlengkapan =====
            [
                'name' => 'Topi Sekolah',
                'description' => "Topi sekolah SMK dengan warna abu-abu standar. 
Bahan kuat dan nyaman digunakan untuk kegiatan sehari-hari. 
Wajib dipakai sesuai aturan seragam sekolah.",
                'price' => 12000, 'modal_price' => 8000, 'stock' => 70,
                'category_id' => $atributId,
            ],
            [
                'name' => 'Dasi Sekolah',
                'description' => "Dasi sekolah warna abu-abu untuk pelajar SMK. 
Desain formal dan sesuai aturan seragam resmi. 
Cocok dipakai pada upacara maupun kegiatan belajar mengajar.",
                'price' => 8000, 'modal_price' => 5000, 'stock' => 100,
                'category_id' => $atributId,
            ],
            [
                'name' => 'Sabuk Sekolah',
                'description' => "Sabuk seragam sekolah warna hitam dengan kualitas baik. 
Bahan kuat dan awet untuk penggunaan jangka panjang. 
Membuat penampilan lebih rapi sesuai aturan sekolah.",
                'price' => 15000, 'modal_price' => 10000, 'stock' => 80,
                'category_id' => $atributId,
            ],
            [
                'name' => 'Kaos Kaki Sekolah',
                'description' => "Kaos kaki putih untuk pelengkap seragam sekolah. 
Bahan lembut, elastis, dan nyaman dipakai sepanjang hari. 
Wajib dipakai agar penampilan lebih rapi dan sesuai aturan sekolah.",
                'price' => 10000, 'modal_price' => 7000, 'stock' => 150,
                'category_id' => $atributId,
            ],
        ];

        foreach ($products as $product) {
            Product::create([
                'admin_id' => $admin->user_id,
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'modal_price' => $product['modal_price'],
                'stock' => $product['stock'],
                'category_id' => $product['category_id'],
                'image' => 'products/' . Str::slug($product['name']) . '.jpeg',
                'image_url' => 'storage/products/' . Str::slug($product['name']) . '.jpeg',
                'is_active' => true,
            ]);
        }
    }
}
