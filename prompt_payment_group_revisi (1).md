# Prompt (Revisi): Implementasi Fitur Payment Group — Status Pembayaran Bertahap

Versi ini memperbaiki beberapa celah desain dari referensi awal (lihat catatan
di bagian paling bawah file). Tempelkan prompt di bawah ini ke Claude Code
atau AI coding assistant lain di dalam project Laravel SIMAKSA/SANTRA kamu.

---

## PROMPT (salin dari sini ke bawah)

Saya sedang mengembangkan sistem informasi pencatatan dan rekap transaksi
keuangan berbasis web untuk CV. Santhi Graha menggunakan Laravel. Sistem saat
ini sudah berjalan dan memiliki fitur pencatatan transaksi, proyek, kategori,
pengguna, approval transaksi, serta rekap/laporan.

Saya ingin menambahkan fitur berdasarkan revisi dosen:

> "Tambahkan fitur untuk status pembayaran (apakah uang muka, proses,
> selesai) dalam 1 proyek dan dalam 1 kategori."

### Tujuan fitur

Sistem harus dapat menangani pembayaran bertahap dalam **proyek dan kategori
yang sama**, tanpa membuat kategori baru untuk setiap pembayaran, dan tanpa
salah menggabungkan dua kesepakatan pembayaran yang sebenarnya tidak
berhubungan hanya karena kebetulan proyek+kategorinya sama.

Contoh pembayaran bertahap (harus tetap satu proyek, satu kategori):

- Pembayaran 1: Rp2.000.000 → Uang Muka
- Pembayaran 2: Rp3.000.000 → Proses
- Pembayaran 3: Rp5.000.000 → Selesai

Contoh transaksi sekali bayar langsung lunas (juga valid):

- Rp10.000.000 → Selesai

### Konsep: Payment Group / Kelompok Pembayaran

Satu Payment Group menghubungkan 1 proyek, 1 kategori, dan beberapa transaksi
pembayaran:

```
Project
  └─ Payment Group (bisa lebih dari satu per kombinasi proyek+kategori)
       ├─ Transaction 1 (punya payment_stage sendiri)
       ├─ Transaction 2 (punya payment_stage sendiri)
       └─ Transaction 3 (punya payment_stage sendiri)
```

Penting: **setiap transaksi individual JUGA menyimpan `payment_stage`
miliknya sendiri** (bukan hanya Payment Group), supaya riwayat pembayaran
bisa ditampilkan per baris seperti contoh di atas. `payment_groups.payment_status`
adalah nilai cache/ringkasan yang selalu disamakan dengan `payment_stage`
milik transaksi ber-status approved paling baru dalam group tersebut.

**Aturan urutan "transaksi paling baru":** urutkan berdasarkan
`transaction_date` (tanggal transaksi), dan jika ada lebih dari satu
transaksi di tanggal yang sama, gunakan `created_at` sebagai tie-breaker.

### Status tidak pernah terkunci permanen

Status "Selesai" pada Payment Group **tidak mengunci** group tersebut. Jika
ada pembayaran lanjutan untuk proyek + kategori yang sama, status Payment
Group harus bisa kembali berubah:

```
Rp2.000.000 → Selesai        (status group: Selesai)
Rp3.000.000 → Proses         (status group berubah: Proses)
Rp5.000.000 → Selesai        (status group berubah lagi: Selesai)
```

### Alur input transaksi

1. **Pegawai Lapangan maupun Admin** membuat transaksi seperti biasa
   (Proyek, Kategori, Nominal, Tanggal, Keterangan, Bukti) **ditambah**
   field baru "Status Pembayaran" (Uang Muka / Proses / Selesai). Field ini
   hanya muncul untuk transaksi tipe `pengeluaran` (sesuai sistem existing,
   Pegawai hanya bisa mengajukan pengeluaran, sedangkan Admin bisa
   pemasukan maupun pengeluaran — field ini tidak relevan untuk pemasukan).

   **Logic deteksi Payment Group (langkah 2 di bawah) berlaku sama persis
   untuk Pegawai maupun Admin** — pengecekan itu berdasarkan kombinasi
   proyek+kategori yang dipilih, bukan berdasarkan siapa yang input.

   **Perbedaan hanya pada status approval transaksinya**, mengikuti
   perilaku sistem existing:
   - Transaksi dari **Pegawai** → tersimpan dengan status `pending`,
     menunggu approval Admin. `payment_stage` yang dipilih Pegawai boleh
     **dikoreksi oleh Admin saat approval** jika kurang sesuai.
   - Transaksi dari **Admin** → tersimpan langsung dengan status
     `approved` (tidak ada tahap approval terpisah, sama seperti perilaku
     transaksi Admin yang sudah ada di sistem ini). Karena tidak ada tahap
     approval belakangan, `payment_stage` yang dipilih Admin saat input
     **langsung final** — tidak ada kesempatan koreksi susulan.
   - Karena Payment Group's `payment_status` (cache) hanya dihitung dari
     transaksi yang **approved**, transaksi Admin akan langsung
     memperbarui status Payment Group saat itu juga, sedangkan transaksi
     Pegawai baru memperbarui status Payment Group setelah disetujui
     Admin.

2. **Deteksi Payment Group yang relevan** untuk kombinasi proyek+kategori
   yang dipilih:
   - **Jika belum ada Payment Group sama sekali** untuk kombinasi itu →
     buat Payment Group baru secara otomatis, tanpa perlu konfirmasi apa pun.
   - **Jika sudah ada Payment Group dan status terakhirnya BUKAN "Selesai"**
     (masih Uang Muka atau Proses) → gabungkan otomatis ke Payment Group
     tersebut **tanpa bertanya** (secara logis ini jelas masih pembayaran
     yang sama, tidak perlu konfirmasi tambahan yang mengganggu UX).
   - **Jika sudah ada Payment Group dan status terakhirnya "Selesai"** →
     di sinilah ambigu (bisa jadi kesepakatan baru, bisa jadi pembayaran
     susulan dari yang dianggap sudah lunas). Tampilkan info:
     > "Ditemukan pembayaran sebelumnya untuk Proyek X — Kategori Y.
     > Total: Rp sekian. Status terakhir: Selesai."
     dan tanyakan: **"Apakah ini pembayaran lanjutan dari kelompok
     tersebut?"**
     - **Ya** → transaksi baru masuk ke Payment Group yang sama, status
       group berubah sesuai `payment_stage` transaksi baru ini.
     - **Tidak** → buat Payment Group baru. Wajib isi field `label`/
       keterangan singkat untuk Payment Group baru ini (misalnya
       "Pembelian material tahap 2 — kontrak baru") supaya Admin bisa
       membedakan dari Payment Group sebelumnya saat kombinasi
       proyek+kategori yang sama muncul lebih dari satu group.

3. **Jangan pernah menghapus atau mengubah transaksi lama** akibat adanya
   transaksi baru.

### Approval

- Mekanisme approval transaksi yang sudah ada (`transactions.status`:
  pending/approved/rejected) **dipertahankan sepenuhnya**, tidak boleh
  tercampur dengan `payment_stage`.
- Saat Admin melakukan approval, **Admin boleh mengoreksi `payment_stage`**
  yang tadinya dipilih Pegawai jika ternyata kurang sesuai (Admin punya
  wewenang akhir atas klasifikasi ini, konsisten dengan wewenangnya
  menyetujui/menolak transaksi).
- Payment Group's `payment_status` (cache) hanya dihitung ulang dari
  transaksi yang **sudah approved** — transaksi yang masih pending atau
  ditolak tidak mempengaruhi status Payment Group.

### Struktur database yang diharapkan

**Analisis migration dan model existing terlebih dahulu sebelum melakukan
perubahan apa pun.** Jangan berasumsi terhadap struktur kode yang belum
diperiksa — kalau perlu, minta file migration/model/controller yang relevan
dikirimkan dulu.

Jika sesuai dengan struktur sistem existing, buat:

**Tabel baru `payment_groups`:**
- `id`
- `project_id` (FK ke projects)
- `category_id` (FK ke categories)
- `payment_status` ENUM(`uang_muka`,`proses`,`selesai`) — nilai cache dari
  transaksi approved paling baru
- `label` (nullable, string) — keterangan pembeda kalau ada lebih dari satu
  group untuk kombinasi proyek+kategori yang sama
- `total_amount` (opsional, bisa dihitung on-the-fly dari SUM transaksi
  approved alih-alih disimpan, untuk menghindari data tidak sinkron —
  pertimbangkan mana yang lebih sesuai dengan pola existing di sistem ini)
- timestamps

**Tambahan kolom di tabel `transactions` yang sudah ada:**
- `payment_group_id` — FK ke `payment_groups`, **nullable** (supaya
  transaksi lama sebelum fitur ini tetap valid tanpa migrasi data mundur)
- `payment_stage` ENUM(`uang_muka`,`proses`,`selesai`), nullable (hanya
  relevan untuk transaksi tipe `pengeluaran`)

**Pertahankan** field `transactions.status` seperti sekarang (pending/
approved/rejected untuk approval) — jangan dicampur dengan `payment_stage`.

### Relasi Laravel yang diharapkan (sesuaikan nama dengan kode existing)

- `Project` → `hasMany PaymentGroup`
- `Category` → `hasMany PaymentGroup`
- `PaymentGroup` → `belongsTo Project`, `belongsTo Category`,
  `hasMany Transaction`
- `Transaction` → `belongsTo PaymentGroup` (nullable)

### Penyesuaian halaman input transaksi

- Tambahkan field "Status Pembayaran" ke form transaksi yang sudah ada,
  **tanpa menghilangkan field yang sudah ada**.
- Implementasikan logic deteksi Payment Group sesuai alur di atas (auto
  attach jika belum "Selesai", tanya konfirmasi kalau statusnya "Selesai").

### Penyesuaian halaman Admin

Admin/Owner harus dapat melihat, per kombinasi proyek + kategori:

```
Pembangunan Rumah A
  → Pembelian Material
      Total: Rp10.000.000
      Status terakhir: Selesai
      [Lihat Riwayat]
```

Saat "Lihat Riwayat" dibuka, tampilkan daftar transaksi dalam Payment Group
tersebut beserta `payment_stage` masing-masing (data ini datang dari kolom
`payment_stage` di tabel `transactions`, bukan hanya dari Payment Group).

Jika ada lebih dari satu Payment Group untuk kombinasi proyek+kategori yang
sama, tampilkan keduanya secara terpisah dengan `label` masing-masing supaya
tidak tertukar.

### Rekap dan laporan

- Transaksi individual tetap tampil seperti sekarang, tidak berubah.
- Tambahkan opsi melihat rekap berdasarkan Payment Group (total per
  proyek+kategori+group, status terakhir).
- **Pastikan tidak ada penghitungan ganda**: total per Payment Group harus
  dihitung dari SUM transaksi approved miliknya sendiri, bukan dijumlahkan
  ulang dari total proyek/kategori secara keseluruhan.

### Aturan penting (jangan dilanggar)

1. Jangan merusak fitur existing.
2. Jangan menghapus data transaksi lama.
3. Jangan mengubah fungsi status approval (`transactions.status`) menjadi
   status pembayaran.
4. Jangan membuat kategori baru untuk setiap pembayaran.
5. Pembayaran lanjutan harus bisa masuk ke Payment Group yang sama —
   otomatis jika status terakhir belum "Selesai", via konfirmasi jika
   status terakhir "Selesai".
6. Transaksi tetap disimpan sebagai histori individual, masing-masing
   dengan `payment_stage` sendiri.
7. Status "Selesai" tidak mengunci Payment Group secara permanen.
8. `payment_group_id` di tabel `transactions` harus nullable — data lama
   sebelum fitur ini harus tetap berfungsi tanpa migrasi mundur.
9. Payment Group's `payment_status` (cache) hanya dihitung dari transaksi
   yang sudah **approved**.
10. Validasi harus mencegah data pembayaran yang tidak valid (misalnya
    `payment_stage` terisi untuk transaksi tipe `pemasukan`).
11. Gunakan migration, model relationship, controller/service, validation,
    dan UI yang sesuai dengan arsitektur Laravel existing di project ini.

### Cara mengerjakan

Jangan langsung memberikan kode. Kerjakan bertahap:

**Tahap 1 — Analisis sistem existing.** Periksa migration, model,
controller, route, request/validation, blade/view, relasi database, dan
fitur transaksi existing. Jelaskan bagaimana transaksi saat ini dibuat,
disimpan, disetujui, ditampilkan, dan direkap.

**Tahap 2 — Rancang perubahan.** Jelaskan tabel/kolom yang perlu
ditambahkan, relasi antar tabel, perubahan model/controller/route/form/
halaman Admin/rekap, dan risiko terhadap data existing.

**Tahap 3 — Implementasi bertahap.** Migration → Model & relationship →
Validation → Controller/service → Route → Form input transaksi → Logic
pengecekan Payment Group → Tampilan riwayat pembayaran → Tampilan status →
Penyesuaian Admin → Penyesuaian rekap. Untuk tiap perubahan, tunjukkan file
yang diubah, bagian yang ditambahkan, kode lengkap, dan alasan perubahan.

**Tahap 4 — Testing.** Buat skenario minimal untuk: transaksi sekali bayar
langsung selesai; pembayaran uang muka; pembayaran kedua pada
proyek+kategori sama; perubahan Uang Muka→Proses; perubahan Proses→Selesai;
pembayaran lanjutan setelah status sebelumnya Selesai (termasuk kasus
konfirmasi "Ya" dan "Tidak"); dua kategori berbeda dalam satu proyek; satu
kategori sama pada proyek berbeda; transaksi ditolak Admin (pastikan tidak
memengaruhi status Payment Group); transaksi lama tetap tersimpan setelah
pembayaran baru dibuat; total tidak terhitung ganda; data existing sebelum
fitur ini tetap berfungsi; **transaksi pengeluaran yang diinput langsung
oleh Admin (status approved otomatis) memperbarui status Payment Group
seketika itu juga, tanpa menunggu approval terpisah.**

**Tahap 5 — Penjelasan untuk dokumentasi TA.** Setelah implementasi,
berikan penjelasan dalam Bahasa Indonesia sederhana untuk saya gunakan
menjelaskan revisi ini ke dosen: tujuan fitur, perubahan alur sistem,
perubahan database, alasan menggunakan Payment Group, cara status
pembayaran bekerja (termasuk kenapa `payment_stage` disimpan di dua level),
contoh kasus pembayaran bertahap, dan bagaimana sistem menangani transaksi
yang awalnya dianggap selesai tapi kemudian punya pembayaran lanjutan.

Jangan membuat asumsi terhadap struktur kode yang belum diberikan — minta
saya mengirimkan file yang relevan terlebih dahulu jika diperlukan.

---

## Catatan perubahan dari referensi awal

1. Menambahkan kolom `payment_stage` di tabel `transactions` (referensi awal
   hanya menaruh status di `payment_groups`, padahal tampilan riwayat butuh
   status per transaksi).
2. Menambahkan aturan urutan eksplisit untuk "transaksi paling baru"
   (`transaction_date` lalu `created_at`).
3. Konfirmasi "pembayaran lanjutan?" sekarang **hanya muncul saat status
   terakhir "Selesai"** — kalau masih Uang Muka/Proses, langsung
   digabungkan otomatis tanpa tanya, supaya tidak mengganggu alur input
   yang sudah dikenal user.
4. Menambahkan field `label` wajib pada Payment Group baru yang dibuat dari
   jawaban "Tidak", supaya Admin bisa membedakan beberapa Payment Group
   dalam kombinasi proyek+kategori yang sama.
5. Menegaskan `payment_group_id` harus nullable untuk kompatibilitas data
   lama.
6. Memperjelas siapa yang mengisi `payment_stage` (Pegawai saat input,
   Admin bisa koreksi saat approval) — sebelumnya tidak disebutkan sama
   sekali di referensi awal.
7. Menegaskan Payment Group's `payment_status` hanya dihitung dari
   transaksi berstatus approved.
8. Menegaskan bahwa transaksi dari Admin mengikuti logic deteksi Payment
   Group yang sama seperti Pegawai, tapi karena transaksinya langsung
   berstatus approved (tanpa tahap approval terpisah), status Payment
   Group ikut ter-update seketika — berbeda dari transaksi Pegawai yang
   baru ter-update setelah disetujui Admin. Sebelumnya referensi awal tidak
   membahas kasus Admin sama sekali.

Kalau setelah dipakai kamu merasa fitur "konfirmasi lanjutan" ini tetap
kurang perlu untuk kasus proyekmu (misalnya kategori jarang dipakai ulang
untuk kesepakatan berbeda), bagian itu bisa disederhanakan lagi — beri tahu
saya dan aku bantu sesuaikan.
