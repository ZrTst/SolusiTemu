# 🛠️ Syarikat SolusiTemu - Sistem Janjitemu
[Bahasa Melayu] | [English](README_en.md)
Ini adalah Kerja Kursus Tingkatan 4 - Tingkatan 5 Sains Komputer
Projek ini dibangunkan dengan PHP, HTML, CSS, dan JS

---

## 📌 1. Peranan 
Sistem ini membahagikan pengguna kepada 3 peranan utama berdasarkan awalan (prefix) ID Pengguna:

*   **A (Admin)** -> `peranan: admin` (Menguruskan keseluruhan sistem: Pengguna, Perkhidmatan, Data Import/Eksport, dan Laporan).
*   **J (Juruteknik)** -> `peranan: juruteknik` (Menguruskan tugasan kerja dan mengemas kini status operasi).
*   **P (Pelanggan)** -> `peranan: pelanggan` (Membuat carian perkhidmatan/juruteknik dan menempah serta menyemak janjitemu sendiri).

---

## 🗺️ 2. Jadual Kandungan
Semua halaman perniagaan dipanggil secara dinamik menggunakan `include` di dalam `dashboard.php` melalui parameter `?page=xxx` tanpa mengubah URL asas.

```text
.
├── README.md
├── README_en.md
├── .htaccess          
└── pages/   
    ├── errors/         # Halaman ralat respons (400, 401, 403, 404)
    ├── sub_pages/      # Fail serpihan UI perniagaan
    └── api/            # Skrip pemprosesan data backend
├── private_core/       # Konfigurasi pangkalan data & fungsi keselamatan global
├── public/             # Fail aset statik
├── images/             # Fail imej sistem
```

---

## 🔄 3. Aliran Data Operasi 

Setiap kali pengguna menghantar data borang (Form Submission), aliran data diproses melalui 3 fasa:

1.  **UI (Paparan)**: Pengguna mengisi borang di `pages/sub_pages/janjitemu.php`.
2.  **API (Proses)**: Data dihantar ke `pages/api/add_janjitemu.php` melalui kaedah `POST`. Backend melakukan semakan keselamatan Session, mengesahkan peranan pengguna, dan memasukkan data menggunakan PDO Prepared Statements.
3.  **Hala Tuju (Redirect)**: Setelah berjaya dimasukkan ke database, backend mengalihkan pengguna kembali ke `dashboard.php?page=user_janjitemu&msg=success`.

---

## 🗃️ 4. Senarai Semak Pembangunan

### 🔓 Fasa 1: Log Masuk & Sesi 
- [x] Reka bentuk antaramuka `login.php` (Guna ID / Guna Nama).
- [x] Semakan kata laluan selamat (`db_verifyPassword`di dalam fail /private_core/functions.php).
- [x] Pengurusan Session automatik berdasarkan prefix ID (P/A/J).

### 📐 Fasa 2: Layout Dashboard & Router 
- [ ] Implementasi susun atur Flexbox (Kiri Fixed Sidebar, Kanan Flex Main Content).
- [ ] Struktur nested `if-else` + `switch-case` di dalam `dashboard.php`.
- [ ] Menyediakan folder `pages/sub_pages/` dan `pages/api/`.

### 💾 Fasa 3: Logik Perniagaan Teras (CRUD Operations)
- [ ] Borang Tambah Janjitemu Baharu (Pelanggan).
- [ ] Carian & Paparan Perkhidmatan dan Juruteknik yang aktif (Pelanggan).
- [ ] Paparan Jadual Sejarah Janjitemu dengan kawalan hak akses data terasing (Pelanggan, Juruteknik, Admin).
- [ ] Fungsi Kelulusan & Pengagihan Janjitemu (Juruteknik, Admin).
- [ ] Operasi pentadbiran global: `delete`, `update`, `add` untuk entiti Janjitemu/Pengguna/Perkhidmatan (Admin sahaja).
- [ ] Fungsi Import Data pukal daripada fail luaran (Admin).
- [ ] Fungsi Output Penjanaan Laporan berformat (Admin).

### ❗ Fasa 4: Pengujian kerentanan dan pemulihan ralat
- [ ] Pengujian ralat siber dan analisis kerentanan kod (Vulnerability testing: Suntikan SQL , XSS).
- [ ] Pengendalian ralat runtime dan pemulihan logik pangkalan data bagi memastikan sistem bebas ralat.

---

© 2026-2027 ZrTst. Dibina menggunakan Teknologi Web Asli & Logik Tulen.