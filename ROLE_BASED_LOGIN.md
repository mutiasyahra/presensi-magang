# 🔐 Panduan Sistem Login Berbasis Role (Role-Based Login)

## 📋 Daftar Isi

1. [Gambaran Umum](#gambaran-umum)
2. [Alur Login](#alur-login)
3. [Struktur Database & Model](#struktur-database--model)
4. [Komponen Frontend](#komponen-frontend)
5. [Middleware & API Routes](#middleware--api-routes)
6. [Cara Menguji Sistem](#cara-menguji-sistem)
7. [Perbedaan Tampilan per Role](#perbedaan-tampilan-per-role)

---

## 🎯 Gambaran Umum

Sistem ini mengimplementasikan login berbasis role (peran pengguna) dengan dua jenis user:

- **Admin**: Akses ke dashboard admin dengan fitur manajemen penuh
- **User**: Akses ke dashboard mobile untuk absensi dan manajemen cuti

Ketika user berhasil login, sistem akan **otomatis mengarahkan ke halaman yang sesuai** berdasarkan rolenya.

---

## 🔄 Alur Login

```
┌─────────────────────────────────────────────────────────┐
│  1. User membuka aplikasi                               │
│     (Landing Screen selama 3 detik, lalu ke Login)      │
└──────────────────┬──────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────────┐
│  2. User input email & password di LoginScreen          │
│     POST /api/login                                     │
└──────────────────┬──────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────────┐
│  3. Backend verifikasi email & password                 │
│     ❌ Jika salah: Return error 401                     │
│     ✅ Jika benar: Return token + user data + role      │
└──────────────────┬──────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────────┐
│  4. Frontend simpan ke localStorage:                    │
│     - token (untuk request API)                         │
│     - user (berisi: name, email, role)                  │
└──────────────────┬──────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────────┐
│  5. App.vue cek role user:                              │
│     - Jika role === 'admin' → Redirect ke Admin Panel   │
│     - Jika role !== 'admin' → Redirect ke Dashboard     │
└─────────────────────────────────────────────────────────┘
```

---

## 💾 Struktur Database & Model

### Tabel `users`

```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,      -- Hashed with bcrypt/Hash::make()
  role VARCHAR(50) NOT NULL,           -- 'admin' atau 'user'
  email_verified_at TIMESTAMP NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Model: `app/Models/User.php`

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',  // ✅ Role field sudah ada
];
```

---

## 🎨 Komponen Frontend

### 1. **App.vue** - Routing Manager

- Mengecek token & role di `onMounted()`
- Menentukan halaman pertama yang ditampilkan
- Handle emisi logout dari child components

```javascript
// Jika user sudah login & punya token:
if (user.role === "admin") {
  currentPage.value = "admin"; // AdminLayout
} else {
  currentPage.value = "dashboard"; // DashboardScreen
}
```

### 2. **LoginScreen.vue** - Form Login

- User input email & password
- Kirim ke `/api/login`
- Simpan response token & user ke localStorage
- Emit `login-success` dengan data user
- Trigger redirect ke App.vue

```javascript
const handleLogin = async () => {
  const response = await api.post("/login", { email, password });
  localStorage.setItem("token", response.data.token);
  localStorage.setItem("user", JSON.stringify(response.data.user));
  emit("login-success", response.data.user); // → App.vue cek role
};
```

### 3. **DashboardScreen.vue** - User Dashboard

- ✅ Menampilkan nama user dari localStorage
- 📅 Kalender attendance
- ⏰ Clock In / Clock Out
- 📋 History & Leave requests
- 👤 Profile & Logout

**Baru ditambah:**

```javascript
const user = ref({
  name: "User",
  email: "",
});

onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    user.value = JSON.parse(storedUser);
  }
});
```

### 4. **ProfileScreen.vue** - User Profile

- ✅ Menampilkan nama & email dari localStorage
- ⚙️ Settings section
- 🚪 Logout button (baru improved)

**Baru ditambah:**

```javascript
const handleLogout = async () => {
  try {
    await api.post("/logout"); // Clear token di backend
  } finally {
    emit("logout"); // Clear localStorage & navigate
  }
};
```

### 5. **AdminLayout.vue** - Admin Dashboard

- 📊 Statistik (Present, Late, Absent, etc)
- 👥 Daftar Attendance Karyawan
- 📝 Leave Requests approval
- 📄 Reports & Export

---

## 🔒 Middleware & API Routes

### Backend: `app/Http/Middleware/AdminMiddleware.php`

```php
public function handle(Request $request, Closure $next)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Akses ditolak. Hanya admin.'
        ], 403);
    }
    return $next($request);
}
```

### Register di Kernel: `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    // ...
];
```

### API Routes: `routes/api.php`

**Public Routes (Tanpa Autentikasi):**

```php
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
```

**User Routes (Auth + User):**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/leave', [LeaveController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
```

**Admin Routes (Auth + Admin Middleware):**

```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::patch('/leave/{id}', [LeaveController::class, 'approve']);
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::get('/stats', [AttendanceController::class, 'stats']);
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::get('/recap-monthly', [AttendanceController::class, 'monthlyRecap']);
});
```

---

## ✅ Cara Menguji Sistem

> **Catatan penting:** sistem absensi sekarang **tidak lagi memerlukan lokasi di dalam radius kantor**. Kamu dapat mencoba clock-in/out dari rumah atau mana saja.
> Kamera juga diaktifkan pada halaman clock-in / clock-out; sistem akan meminta izin kamera dan menyimpan foto yang diambil ke kolom `clock_in_photo` / `clock_out_photo`.

### Persiapan

1. **Backend berjalan di**: `http://localhost:8000` (atau sesuaikan di `.env` frontend)
2. **Frontend running**: `npm run dev` (port 5173 default)
3. **Database sudah terisi** dengan test users

### Test Admin Login

**Buat User Admin di Database:**

```bash
# Terminal backend
php artisan tinker
```

```php
// Di Tinker:
User::create([
    'name' => 'Admin User',
    'email' => 'admin@test.com',
    'password' => Hash::make('password123'),
    'role' => 'admin'
]);
```

**Login Test:**

1. Buka `http://localhost:5173`
2. Tunggu 3 detik, otomatis ke halaman login
3. Input:
   - Email: `admin@test.com`
   - Password: `password123`
4. ✅ **Harusnya redirect ke Admin Panel** (AdminLayout)
5. Lihat data: Overview, Attendance, Leaves, Reports

### Test User Login

**Buat User Biasa:**

```php
User::create([
    'name' => 'John Doe',
    'email' => 'user@test.com',
    'password' => Hash::make('password123'),
    'role' => 'user'
]);
```

**Login Test:**

1. Logout dari admin (atau clear localStorage)
2. Login dengan:
   - Email: `user@test.com`
   - Password: `password123`
3. ✅ **Harusnya redirect ke User Dashboard** (Mobile view)
4. Lihat nama di header: "WELCOME BACK, **John Doe**"
5. Bisa akses: Clock In, Clock Out, Leave, History

### Test Logout

1. Dari User Dashboard → Profile icon (atas kanan)
2. Klik Logout
3. ✅ **Harusnya kembali ke Landing Screen**
4. localStorage di-clear

### Test Admin API Protection

Coba akses admin route dengan user biasa:

```bash
curl -X GET http://localhost:8000/api/stats \
  -H "Authorization: Bearer [USER_TOKEN]"

# Response: 403 Forbidden - "Akses ditolak. Hanya admin."
```

---

## 🎨 Perbedaan Tampilan per Role

### Admin Dashboard

```
┌─────────────────────────────────────────────┐
│ DESKTOP VIEW - Full Admin Console           │
├─────────────┬───────────────────────────────┤
│   SIDEBAR   │  MAIN CONTENT                 │
│             │  ┌──────────────┐             │
│ • Overview  │  │ Overview     │             │
│ • Attendance│  │ Stats & Stats│             │
│ • Leaves    │  │              │             │
│ • Reports   │  │              │             │
│             │  └──────────────┘             │
│ [Logout]    │                               │
└─────────────┴───────────────────────────────┘

Fitur:
✅ Lihat statistik kehadiran
✅ Kelola semua absensi staff
✅ Approve/Reject leave requests
✅ Export reports ke Excel PDF
```

### User Dashboard

```
┌───────────────────────┐
│ MOBILE VIEW - User    │
├───────────────────────┤
│ Header: Welcome, User │ ← Nama dinamis
│                       │
│ Location Card         │
│ Attendance Rate: 94.8%│
│ Current Time: 09:15   │
│                       │
│ [Clock In]  [Clock Out]
│                       │
│ Monthly Attendance:   │
│ Present: 20           │
│ Late: 3               │
│ Absent: 1             │
├───────────────────────┤
│ Bottom Nav:           │
│ Home | History | Leave│
│ History | Profile     │
└───────────────────────┘

Fitur:
✅ Clock In/Out dengan foto
✅ Lihat attendance history
✅ Submit leave requests
✅ Edit profile
✅ Logout
```

---

## 🔧 File yang Dimodifikasi

| File                                              | Perubahan                                    |
| ------------------------------------------------- | -------------------------------------------- |
| `frontend/src/App.vue`                            | ✅ Added @logout handler untuk ProfileScreen |
| `frontend/src/components/DashboardScreen.vue`     | ✅ Display user name dari localStorage       |
| `frontend/src/components/ProfileScreen.vue`       | ✅ Display user info, improved logout        |
| `backend/app/Http/Kernel.php`                     | ✅ Admin middleware sudah registered         |
| `backend/app/Http/Middleware/AdminMiddleware.php` | ✅ Check role === 'admin'                    |
| `backend/routes/api.php`                          | ✅ Routes sudah dibedakan per role           |

---

## 🎓 Kesimpulan

Sistem Presensi Magang telah diimplementasikan dengan **role-based login** yang robust:

✅ **Auto-redirect berdasarkan role** setelah login
✅ **Admin dapat mengakses admin panel** dengan full fitur management
✅ **User mobile dapat clock in/out** dan manage leave (now works from any location – radius check removed)
✅ **Foto kamera aktif** terhubung ke `clock_in_photo` & `clock_out_photo` dan tampil di dashboard admin
✅ **Token & role disimpan secure** di localStorage
✅ **API routes dilindungi** dengan middleware admin
✅ **Logout membersihkan semua data** user dari client

---

## 📞 Troubleshooting

| Problem                     | Solusi                          |
| --------------------------- | ------------------------------- |
| Login tapi tdk redirect     | Clear cache, check localStorage |
| Nama user tdk muncul        | Reload, check username di DB    |
| Admin role tdk access admin | Cek role di DB = 'admin'        |
| CORS error                  | Check backend CORS config       |
| Token expired               | Auto-logout jika 401 error      |
