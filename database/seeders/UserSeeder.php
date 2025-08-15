<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pondok;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data pondok tetap dibuat secara dinamis untuk efisiensi
        $pondokData = [
            [
                'nama' => 'PPPM Royan Al-Manshurien Bangkalan',
                'status' => 'pppm',
                'nomor_telepon' => '087826009516',
                'alamat_lengkap' => 'Griya Manshurin, Karanganyar, Banyu Ajuh, Kec. Kamal, Kabupaten Bangkalan',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Bangkalan',
                'kecamatan' => 'Kamal',
                'kelurahan' => 'Banyuajuh',
                'kode_pos' => '69162',
                'daerah_sambung' => 'Bangkalan',
            ],
            [
                'nama' => "PPM Syafi'ur Rohman Jember",
                'status' => 'ppm',
                'nomor_telepon' => '082229517549',
                'alamat_lengkap' => 'Jl. Brantas XXVI No.251, Krajan Timur',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Jember',
                'kecamatan' => 'Sumbersari',
                'kelurahan' => 'Sumbersari',
                'kode_pos' => '68121',
                'daerah_sambung' => 'Jember Kota',
            ],
            [
                'nama' => 'PPPM Baitul Makmur Surabaya Selatan',
                'status' => 'pppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Jetis Kulon Gg. VII No.10, Wonokromo, Kec. Wonokromo, Surabaya',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Surabaya',
                'kecamatan' => 'Wonokromo',
                'kelurahan' => 'Wonokromo',
                'kode_pos' => '60243',
                'daerah_sambung' => 'Surabaya Selatan',
            ],
            [
                'nama' => 'PPM Al Hikmah Semarang Barat',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Menoreh Tengah XII No.8 RT07, RW.04, Sampangan, Kec. Gajahmungkur, Kota Semarang',
                'provinsi' => 'Jawa Tengah',
                'kota' => 'Semarang',
                'kecamatan' => 'Gajahmungkur',
                'kelurahan' => 'Sampangan',
                'kode_pos' => '50232',
                'daerah_sambung' => 'Semarang Barat',
            ],
            [
                'nama' => 'PPM Bina Khoirul Insan Semarang Selatan',
                'status' => 'ppm',
                'nomor_telepon' => '081359918191',
                'alamat_lengkap' => 'Jl. Ngesrep Tim. V No.8, Sumurboto, Kec. Banyumanik, Kota Semarang',
                'provinsi' => 'Jawa Tengah',
                'kota' => 'Semarang',
                'kecamatan' => 'Banyumanik',
                'kelurahan' => 'Sumurboto',
                'kode_pos' => '50269',
                'daerah_sambung' => 'Semarang Selatan',
            ],
            [
                'nama' => "PPPM Baitul A'la Malang Kepanjen",
                'status' => 'pppm',
                'nomor_telepon' => '082189071653',
                'alamat_lengkap' => 'Jl. Krapyak RT. 30 RW. 03 Cepokomulyo Kec. Kepanjen, Kabupaten Malang',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Malang',
                'kecamatan' => 'Kepanjen',
                'kelurahan' => 'Tegalsari',
                'kode_pos' => '65163',
                'daerah_sambung' => 'Malang Kepanjen',
            ],
            [
                'nama' => 'PPM Baitul Jannah Malang Tengah',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Bend. Nawangan No.13, Sumbersari, Kec. Lowokwaru, Kota Malang,',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Malang',
                'kecamatan' => 'Lowokwaru',
                'kelurahan' => 'Sumbersari',
                'kode_pos' => '65145',
                'daerah_sambung' => 'Malang Tengah',
            ],
            [
                'nama' => 'PPM Nur Muhammad Malang Tengah',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Tirto Utomo Gg. IV No.58A, Dusun Rambaan, Landungsari, Kec. Dau, Kabupaten Malang',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Malang',
                'kecamatan' => 'Dau',
                'kelurahan' => 'Landungsari',
                'kode_pos' => '65151',
                'daerah_sambung' => 'Malang Tengah',
            ],
            [
                'nama' => 'PPM Al-Kautsar Bina Insani Purwokerto',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Gn. Sumbing Gg. Cendana, Karangmiri, Sumampir, Kec. Purwokerto Utara, Kabupaten Banyumas',
                'provinsi' => 'Jawa Tengah',
                'kota' => 'Banyumas',
                'kecamatan' => 'Purwokerto Utara',
                'kelurahan' => 'Sumampir',
                'kode_pos' => '53125',
                'daerah_sambung' => 'Purwokerto',
            ],
            [
                'nama' => 'PPPM Nurul Islam Samarinda',
                'status' => 'pppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Dadi Mulya, Kec. Samarinda Ulu, Kota Samarinda',
                'provinsi' => 'Kalimantan Timur',
                'kota' => 'Samarinda',
                'kecamatan' => 'Samarinda Ulu',
                'kelurahan' => 'Dadimulya',
                'kode_pos' => '75242',
                'daerah_sambung' => 'Samarinda',
            ],
            [
                'nama' => 'PPM Ar-Royyaan Baitul Hamdi Yogyakarta',
                'status' => 'ppm',
                'nomor_telepon' => '085640693543',
                'alamat_lengkap' => 'Jl. Kepuh GK III No.850, Klitren, Kec. Gondokusuman, Kota Yogyakarta',
                'provinsi' => 'D.I. Yogyakarta',
                'kota' => 'Yogyakarta',
                'kecamatan' => 'Gondokusuman',
                'kelurahan' => 'Klitren',
                'kode_pos' => '55222',
                'daerah_sambung' => 'Jogja 2',
            ],
            [
                'nama' => 'PPM Khoirul Huda 2 Keputih Surabaya Tengah',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Keputih Tegal Bakti I No.21, Keputih, Kec. Sukolilo, Surabaya',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Surabaya',
                'kecamatan' => 'Sukolilo',
                'kelurahan' => 'Keputih',
                'kode_pos' => '60111',
                'daerah_sambung' => 'Surabaya Tengah',
            ],
            [
                'nama' => 'PPM Khoirul Huda 1 Nginden Surabaya Tengah',
                'status' => 'ppm',
                'nomor_telepon' => '0315949591',
                'alamat_lengkap' => 'Jl. Nginden III No.50, Nginden Jangkungan, Kec. Sukolilo, Surabaya',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Surabaya',
                'kecamatan' => 'Sukolilo',
                'kelurahan' => 'Nginden Jangkungan',
                'kode_pos' => '60118',
                'daerah_sambung' => 'Surabaya Tengah',
            ],
            [
                'nama' => 'PPM Khoirul Huda 3 Semampir Surabaya Tengah',
                'status' => 'ppm',
                'nomor_telepon' => '085648094354',
                'alamat_lengkap' => 'Jl. Semampir Selatan IIA No.114, Medokan Semampir, Kec. Sukolilo, Surabaya',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Surabaya',
                'kecamatan' => 'Semampir',
                'kelurahan' => 'Medokan',
                'kode_pos' => '60119',
                'daerah_sambung' => 'Surabaya Tengah',
            ],
            [
                'nama' => 'PPPM Subulussalam Surabaya Utara',
                'status' => 'pppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Ploso Tim. VI No.51, Ploso, Kec. Tambaksari, Surabaya',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Surabaya',
                'kecamatan' => 'Tambaksari',
                'kelurahan' => 'Ploso',
                'kode_pos' => '60133',
                'daerah_sambung' => 'Surabaya Utara',
            ],
            [
                'nama' => 'PPM Al Kautsar Malang Tengah',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Jombang III, Gading Kasri, Kec. Klojen, Kota Malang',
                'provinsi' => 'Jawa Timur',
                'kota' => 'Malang',
                'kecamatan' => 'Klojen',
                'kelurahan' => 'Gading Kasri',
                'kode_pos' => '65115',
                'daerah_sambung' => 'Malang Tengah',
            ],
            [
                'nama' => 'PPM Nurhasan Cibeber Cimahi',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Padat Karya No.32, Cibeber, Kec. Cimahi Sel., Kota Cimahi',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Cimahi',
                'kecamatan' => 'Cimahi Selatan',
                'kelurahan' => 'Cibeber',
                'kode_pos' => '40631',
                'daerah_sambung' => 'Bandung Barat',
            ],
            [
                'nama' => 'PPM Roudhotul Jannah Bandung Selatan',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Sukabirus.A1a Citeureup Kec. Dayeuhkolot, Jawa, Barat, Kabupaten Bandung',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Bandung',
                'kecamatan' => 'Dayeuhkolot',
                'kelurahan' => 'Citereup',
                'kode_pos' => '40257',
                'daerah_sambung' => 'Bandung Selatan 2',
            ],
            [
                'nama' => 'PPM Minhajul Haq Bandung Utara',
                'status' => 'ppm',
                'nomor_telepon' => '087889827126',
                'alamat_lengkap' => 'Jl. Bijaksana II No.8, Pasteur, Kec. Sukajadi, Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Bandung',
                'kecamatan' => 'Sukajadi',
                'kelurahan' => 'Pasteur',
                'kode_pos' => '40161',
                'daerah_sambung' => 'Bandung Utara',
            ],
            [
                'nama' => "PPM Baitul 'Ilmaini Bogor Selatan",
                'status' => 'ppm',
                'nomor_telepon' => '088803279811',
                'alamat_lengkap' => 'Jl. Batu Hulung 1 No.3, RT.02/RW.01, Margajaya, Kec. Bogor Bar., Kota Bogor',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Bogor',
                'kecamatan' => 'Bogor Barat',
                'kelurahan' => 'Margajaya',
                'kode_pos' => '16116',
                'daerah_sambung' => 'Bogor Selatan',
            ],
            [
                'nama' => 'PPM Al Faqih Mandiri Depok',
                'status' => 'ppm',
                'nomor_telepon' => '085882685011',
                'alamat_lengkap' => 'Jl. Sawo No.33b, Pondok Cina, Kecamatan Beji, Kota Depok',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Depok',
                'kecamatan' => 'Beji',
                'kelurahan' => 'Pondok Cina',
                'kode_pos' => '16424',
                'daerah_sambung' => 'Depok',
            ],
            [
                'nama' => 'PPM Bina Insan Mulia Bintaro',
                'status' => 'ppm',
                'nomor_telepon' => '0818660038',
                'alamat_lengkap' => 'Jl. Genteng No 17, Perumahan Pondok Jaya, Pondok Karya',
                'provinsi' => 'Banten',
                'kota' => 'Tangerang Selatan',
                'kecamatan' => 'Pondok Aren',
                'kelurahan' => 'Pondok Karya',
                'kode_pos' => '15225',
                'daerah_sambung' => 'Tangerang',
            ],
            [
                'nama' => 'PPPM Miftahul Huda Pekanbaru',
                'status' => 'pppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Gg. Buntu Indah, Tuah Karya, Kec. Tampan, Kota Pekanbaru, Riau',
                'provinsi' => 'Riau',
                'kota' => 'Pekanbaru',
                'kecamatan' => 'Tampan',
                'kelurahan' => 'Tuah Karya',
                'kode_pos' => '28293',
                'daerah_sambung' => 'Pekanbaru',
            ],
            [
                'nama' => 'PPM Sulthon Aulia Jakarta Timur',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Rawamangun Muka Selatan No.15, RT.2/RW.12, Rawamangun, Kec. Pulo Gadung, Kota Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
                'kota' => 'Jakarta Timur',
                'kecamatan' => 'Pulo Gadung',
                'kelurahan' => 'Rawamangun',
                'kode_pos' => '13220',
                'daerah_sambung' => 'Jakarta Timur',
            ],
            [
                'nama' => 'PPM Achmad Basyarie Kota Serang',
                'status' => 'ppm',
                'nomor_telepon' => '081908979650',
                'alamat_lengkap' => 'Komplek Untirta Blok F No.3, Banjaragung, Kec. Cipocok Jaya, Kota Serang',
                'provinsi' => 'Banten',
                'kota' => 'Serang',
                'kecamatan' => 'Cipocok Jaya',
                'kelurahan' => 'Banjaragung',
                'kode_pos' => '42121',
                'daerah_sambung' => 'Banten',
            ],
            [
                'nama' => 'PPM Roudhotul Jannah Solo Selatan',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Porong, Pucangsawit, Kec. Jebres, Kota Surakarta, Jawa Tengah',
                'provinsi' => 'Jawa Tengah',
                'kota' => 'Surakarta',
                'kecamatan' => 'Jebres',
                'kelurahan' => 'Pucangsawit',
                'kode_pos' => '57125',
                'daerah_sambung' => 'Solo Selatan',
            ],
            [
                'nama' => 'PPM Nurul Hakim Bandung Timur 2',
                'status' => 'ppm',
                'nomor_telepon' => '08157123736',
                'alamat_lengkap' => 'Jl. Raya Jatinangor No.138, RT.01/RW.01, Cikeruh, Kec. Jatinangor, Kabupaten Sumedang',
                'provinsi' => 'Jawa Barat',
                'kota' => 'Sumedang',
                'kecamatan' => 'Jatinangor',
                'kelurahan' => 'Cikeruh',
                'kode_pos' => '45363',
                'daerah_sambung' => 'Bandung Timur 2',
            ],
            [
                'nama' => 'PPM Al Musawwa Solo Barat',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Kp. Baru, Honggobayan, Pabelan, Kec. Kartasura, Kabupaten Sukoharjo',
                'provinsi' => 'Jawa Tengah',
                'kota' => 'Sukoharjo',
                'kecamatan' => 'Kartasura',
                'kelurahan' => 'Pabelan',
                'kode_pos' => '57169',
                'daerah_sambung' => 'Solo Barat',
            ],
            [
                'nama' => 'PPM Daqwatul Haq Kendari Lepo-lepo',
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Beringin, Lalolara, Kec. Kambu, Kota Kendari',
                'provinsi' => 'Sulawesi Tenggara',
                'kota' => 'Kendari',
                'kecamatan' => 'Kambu',
                'kelurahan' => 'Laolara',
                'kode_pos' => '93561',
                'daerah_sambung' => 'Kendari Lepo-lepo',
            ],
            [
                'nama' => "PPM Al A'la Semarang Barat",
                'status' => 'ppm',
                'nomor_telepon' => null,
                'alamat_lengkap' => 'Jl. Sedayu, RT.01/RW.01, Kalisegoro, Gunung Pati, Kota Semarang',
                'provinsi' => 'Jawa Tengah',
                'kota' => 'Semarang',
                'kecamatan' => 'Gunung Pati',
                'kelurahan' => 'Kalisegoro',
                'kode_pos' => '50229',
                'daerah_sambung' => 'Semarang Barat',
            ],
        ];

        $createdPondoks = [];
        foreach ($pondokData as $data) {
            $pondok = Pondok::create($data);
            $createdPondoks[] = $pondok;
        }

        // Create super admin
        $superAdmin = User::create([
            'nama' => 'Super Admin',
            'email' => 'superadmin@ppwb.my.id',
            'nomor_telepon' => '085786537295',
            'password' => Hash::make('pass354313'),
            'pondok_id' => null,
        ]);
        Artisan::call('shield:super-admin', ['--user' => $superAdmin->id]);

        // Create admin pusat
        $adminPusat = User::create([
            'nama' => 'Admin Pusat',
            'email' => 'pusat@ppwb.my.id',
            'nomor_telepon' => '081234567892',
            'password' => Hash::make('password'),
            'pondok_id' => null,
        ]);

        // =================================================================
        // == PEMBUATAN AKUN ADMIN PONDOK SECARA STATIS ==
        // =================================================================

        // 1. Admin PPPM Royan Al-Manshurien Bangkalan
        User::create([
            'nama' => 'Admin Royan Al-Manshurien Bangkalan',
            'email' => 'pppm_rm@ppwb.my.id',
            'nomor_telepon' => '081234567901',
            'password' => Hash::make('K4F8N2'),
            'pondok_id' => $createdPondoks[0]->id,
        ])->assignRole('Admin Pondok');

        // 2. Admin PPM Syafi'ur Rohman Jember
        User::create([
            'nama' => "Admin Syafi'ur Rohman Jember",
            'email' => 'ppmsr@ppwb.my.id',
            'nomor_telepon' => '081234567902',
            'password' => Hash::make('L9P3J7'),
            'pondok_id' => $createdPondoks[1]->id,
        ])->assignRole('Admin Pondok');

        // 3. Admin PPPM Baitul Makmur Surabaya Selatan
        User::create([
            'nama' => 'Admin Baitul Makmur Surabaya Selatan',
            'email' => 'pppm_bm@ppwb.my.id',
            'nomor_telepon' => '081234567903',
            'password' => Hash::make('M2B6V1'),
            'pondok_id' => $createdPondoks[2]->id,
        ])->assignRole('Admin Pondok');

        // 4. Admin PPM Al Hikmah Semarang Barat
        User::create([
            'nama' => 'Admin Al Hikmah Semarang Barat',
            'email' => 'ppm_al_hikmah@ppwb.my.id',
            'nomor_telepon' => '081234567904',
            'password' => Hash::make('C5X9Z4'),
            'pondok_id' => $createdPondoks[3]->id,
        ])->assignRole('Admin Pondok');

        // 5. Admin PPM Bina Khoirul Insan Semarang Selatan
        User::create([
            'nama' => 'Admin Bina Khoirul Insan Semarang Selatan',
            'email' => 'ppmbki@ppwb.my.id',
            'nomor_telepon' => '081234567905',
            'password' => Hash::make('G8T3R6'),
            'pondok_id' => $createdPondoks[4]->id,
        ])->assignRole('Admin Pondok');

        // 6. Admin PPPM Baitul A'la Malang Kepanjen
        User::create([
            'nama' => "Admin Baitul A'la Malang Kepanjen",
            'email' => 'pontabakepanjen@ppwb.my.id',
            'nomor_telepon' => '081234567906',
            'password' => Hash::make('D7S3K9'),
            'pondok_id' => $createdPondoks[5]->id,
        ])->assignRole('Admin Pondok');

        // 7. Admin PPM Baitul Jannah Malang Tengah
        User::create([
            'nama' => 'Admin Baitul Jannah Malang Tengah',
            'email' => 'ppm_bj@ppwb.my.id',
            'nomor_telepon' => '081234567907',
            'password' => Hash::make('A1F5H8'),
            'pondok_id' => $createdPondoks[6]->id,
        ])->assignRole('Admin Pondok');

        // 8. Admin PPM Nur Muhammad Malang Tengah
        User::create([
            'nama' => 'Admin Nur Muhammad Malang Tengah',
            'email' => 'ppm_nm@ppwb.my.id',
            'nomor_telepon' => '081234567908',
            'password' => Hash::make('Z4Q2W7'),
            'pondok_id' => $createdPondoks[7]->id,
        ])->assignRole('Admin Pondok');

        // 9. Admin PPM Al-Kautsar Bina Insani Purwokerto
        User::create([
            'nama' => 'Admin Al-Kautsar Bina Insani Purwokerto',
            'email' => 'ppm_pwt@ppwb.my.id',
            'nomor_telepon' => '081234567909',
            'password' => Hash::make('E9R6T1'),
            'pondok_id' => $createdPondoks[8]->id,
        ])->assignRole('Admin Pondok');

        // 10. Admin PPPM Nurul Islam Samarinda
        User::create([
            'nama' => 'Admin Nurul Islam Samarinda',
            'email' => 'pppm_nuris@ppwb.my.id',
            'nomor_telepon' => '081234567910',
            'password' => Hash::make('Y5U8I2'),
            'pondok_id' => $createdPondoks[9]->id,
        ])->assignRole('Admin Pondok');

        // 11. Admin PPM Ar-Royyaan Baitul Hamdi Yogyakarta
        User::create([
            'nama' => 'Admin Ar-Royyaan Baitul Hamdi Yogyakarta',
            'email' => 'ppm_jogja@ppwb.my.id',
            'nomor_telepon' => '081234567911',
            'password' => Hash::make('O4P7L3'),
            'pondok_id' => $createdPondoks[10]->id,
        ])->assignRole('Admin Pondok');

        // 12. Admin PPM Khoirul Huda 2 Keputih Surabaya Tengah
        User::create([
            'nama' => 'Admin Khoirul Huda 2 Keputih Surabaya Tengah',
            'email' => 'ppmkh2sby@ppwb.my.id',
            'nomor_telepon' => '081234567912',
            'password' => Hash::make('K9J6H3'),
            'pondok_id' => $createdPondoks[11]->id,
        ])->assignRole('Admin Pondok');

        // 13. Admin PPM Khoirul Huda 1 Nginden Surabaya Tengah
        User::create([
            'nama' => 'Admin Khoirul Huda 1 Nginden Surabaya Tengah',
            'email' => 'ppmkhsby@ppwb.my.id',
            'nomor_telepon' => '081234567913',
            'password' => Hash::make('G5F2D1'),
            'pondok_id' => $createdPondoks[12]->id,
        ])->assignRole('Admin Pondok');

        // 14. Admin PPM Khoirul Huda 3 Semampir Surabaya Tengah
        User::create([
            'nama' => 'Admin Khoirul Huda 3 Semampir Surabaya Tengah',
            'email' => 'ppmkh3sby@ppwb.my.id',
            'nomor_telepon' => '081234567914',
            'password' => Hash::make('S8A4Q1'),
            'pondok_id' => $createdPondoks[13]->id,
        ])->assignRole('Admin Pondok');

        // 15. Admin PPPM Subulussalam Surabaya Utara
        User::create([
            'nama' => 'Admin Subulussalam Surabaya Utara',
            'email' => 'pppmss.sby@ppwb.my.id',
            'nomor_telepon' => '081234567915',
            'password' => Hash::make('W7E3R9'),
            'pondok_id' => $createdPondoks[14]->id,
        ])->assignRole('Admin Pondok');

        // 16. Admin PPM Al-Kautsar Malang Tengah
        User::create([
            'nama' => 'Admin Al-Kautsar Malang Tengah',
            'email' => 'ppm_alkautsar@ppwb.my.id',
            'nomor_telepon' => '081234567916',
            'password' => Hash::make('T2Y6U1'),
            'pondok_id' => $createdPondoks[15]->id,
        ])->assignRole('Admin Pondok');

        // 17. Admin PPM Nurhasan Cibeber Cimahi
        User::create([
            'nama' => 'Admin Nurhasan Cibeber Cimahi',
            'email' => 'ppm.nurhasan354@ppwb.my.id',
            'nomor_telepon' => '081234567917',
            'password' => Hash::make('I4O8P5'),
            'pondok_id' => $createdPondoks[16]->id,
        ])->assignRole('Admin Pondok');

        // 18. Admin PPM Roudhotul Jannah Bandung Selatan
        User::create([
            'nama' => 'Admin Roudhotul Jannah Bandung Selatan',
            'email' => 'ppmrj@ppwb.my.id',
            'nomor_telepon' => '081234567918',
            'password' => Hash::make('L7K3J9'),
            'pondok_id' => $createdPondoks[17]->id,
        ])->assignRole('Admin Pondok');

        // 19. Admin PPM Minhajul Haq Bandung Utara
        User::create([
            'nama' => 'Admin Minhajul Haq Bandung Utara',
            'email' => 'ppm.mh@ppwb.my.id',
            'nomor_telepon' => '081234567919',
            'password' => Hash::make('H6G2F8'),
            'pondok_id' => $createdPondoks[18]->id,
        ])->assignRole('Admin Pondok');

        // 20. Admin PPM Baitul 'Ilmaini Bogor Selatan
        User::create([
            'nama' => "Admin Baitul 'Ilmaini Bogor Selatan",
            'email' => 'ppm.bi@ppwb.my.id',
            'nomor_telepon' => '081234567920',
            'password' => Hash::make('D4S1A7'),
            'pondok_id' => $createdPondoks[19]->id,
        ])->assignRole('Admin Pondok');

        // 21. Admin PPM Al Faqih Mandiri Depok
        User::create([
            'nama' => 'Admin Al Faqih Mandiri Depok',
            'email' => 'ppmafm@ppwb.my.id',
            'nomor_telepon' => '081234567921',
            'password' => Hash::make('Q5W8E2'),
            'pondok_id' => $createdPondoks[20]->id,
        ])->assignRole('Admin Pondok');

        // 22. Admin PPM Bina Insan Mulia Bintaro
        User::create([
            'nama' => 'Admin Bina Insan Mulia Bintaro',
            'email' => 'ppmbim@ppwb.my.id',
            'nomor_telepon' => '081234567922',
            'password' => Hash::make('R9T4Y1'),
            'pondok_id' => $createdPondoks[21]->id,
        ])->assignRole('Admin Pondok');

        // 23. Admin PPPM Miftahul Huda Pekanbaru
        User::create([
            'nama' => 'Admin Miftahul Huda Pekanbaru',
            'email' => 'pppmmh@ppwb.my.id',
            'nomor_telepon' => '081234567923',
            'password' => Hash::make('U3I6O9'),
            'pondok_id' => $createdPondoks[22]->id,
        ])->assignRole('Admin Pondok');

        // 24. Admin PPM Sulthon Aulia Jakarta Timur
        User::create([
            'nama' => 'Admin Sulthon Aulia Jakarta Timur',
            'email' => 'ppmsulthonaulia@ppwb.my.id',
            'nomor_telepon' => '081234567924',
            'password' => Hash::make('P2L5K8'),
            'pondok_id' => $createdPondoks[23]->id,
        ])->assignRole('Admin Pondok');

        // 25. Admin PPM Achmad Basyarie Kota Serang
        User::create([
            'nama' => 'Admin Achmad Basyarie Kota Serang',
            'email' => 'ppm_serang@ppwb.my.id',
            'nomor_telepon' => '081234567925',
            'password' => Hash::make('J1H4G7'),
            'pondok_id' => $createdPondoks[24]->id,
        ])->assignRole('Admin Pondok');

        // 26. Admin PPM Roudhotul Jannah Solo Selatan
        User::create([
            'nama' => 'Admin Roudhotul Jannah Solo Selatan',
            'email' => 'ppmrjska@ppwb.my.id',
            'nomor_telepon' => '081234567926',
            'password' => Hash::make('F3D6S9'),
            'pondok_id' => $createdPondoks[25]->id,
        ])->assignRole('Admin Pondok');

        // 27. Admin PPM Nurul Hakim Bandung Timur 2
        User::create([
            'nama' => 'Admin Nurul Hakim Bandung Timur 2',
            'email' => 'ppm_nh@ppwb.my.id',
            'nomor_telepon' => '081234567927',
            'password' => Hash::make('A2Z5X8'),
            'pondok_id' => $createdPondoks[26]->id,
        ])->assignRole('Admin Pondok');

        // 28. Admin PPM Al Musawwa Solo Barat
        User::create([
            'nama' => 'Admin Al Musawwa Solo Barat',
            'email' => 'ppm_solobarat@ppwb.my.id',
            'nomor_telepon' => '081234567928',
            'password' => Hash::make('C4V7B1'),
            'pondok_id' => $createdPondoks[27]->id,
        ])->assignRole('Admin Pondok');

        // 29. Admin PPM Daqwatul Haq Kendari Lepo-lepo
        User::create([
            'nama' => 'Admin Daqwatul Haq Kendari Lepo-lepo',
            'email' => 'ppmdaqwatulhaq@ppwb.my.id',
            'nomor_telepon' => '081234567929',
            'password' => Hash::make('N6M9P3'),
            'pondok_id' => $createdPondoks[28]->id,
        ])->assignRole('Admin Pondok');

        // 30. Admin PPM Al A'la Semarang Barat
        User::create([
            'nama' => "Admin Al A'la Semarang Barat",
            'email' => 'ppm_al_ala@ppwb.my.id',
            'nomor_telepon' => '081234567930',
            'password' => Hash::make('C3X1Z2'),
            'pondok_id' => $createdPondoks[29]->id,
        ])->assignRole('Admin Pondok');


        $this->command->info('Akun Super Admin, Admin Pusat, dan semua Akun Pondok statis berhasil dibuat.');
    }
}
