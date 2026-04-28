<script setup>
import { ref, onMounted, computed } from "vue";
import api from "../api/axios.js";

// Gabungkan semua event di sini
const emit = defineEmits([
  "navigate",
  "logout",
  "open-personal-info",
  "open-settings",
  "open-privacy",
]);

const user = ref({
  name: "User",
  email: "",
  id: "",
  profile_photo: null,
});

const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('data:') || path.startsWith('http')) return path;
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace("/api", "");
  return `${baseUrl}/storage/${path}`;
};

const userInitial = computed(() => {
  return user.value.name ? user.value.name.charAt(0).toUpperCase() : "U";
});

const handleLogout = async () => {
  try {
    // Call logout API endpoint
    await api.post("/logout");
  } catch (error) {
    console.error("Logout error:", error);
  } finally {
    // Always emit logout to clear local state
    emit("logout");
  }
};

onMounted(() => {
  // Load user data from localStorage
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    user.value = JSON.parse(storedUser);
  }
});
</script>

<template>
  <div class="screen-container">
    <div class="blue-header">
      <div class="header-top">
        <div class="header-icon-box">
          <img
            src="../assets/profile with roundabout.png"
            alt="User"
            class="header-icon"
          />
        </div>
        <div class="header-titles">
          <h2>User Profile</h2>
          <p>Student Dashboard</p>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="profile-card">
        <div class="avatar-container">
          <img v-if="user.profile_photo" :src="getImageUrl(user.profile_photo)" class="avatar-img" />
          <div v-else class="avatar-circle-profile">{{ userInitial }}</div>

          <div class="edit-icon" @click="$emit('navigate', 'edit-profile')">
            <img src="../assets/pencil.png" alt="Edit Profile" />
          </div>
        </div>

        <h3 class="username">{{ user.name }}</h3>
        <p class="user-id">{{ user.email }}</p>

        <div class="badges">
          <span class="badge blue">UI/UX Intern</span>
          <span class="badge gray">Cohort X Echo 1</span>
        </div>

        <div class="company-info">
          <div class="info-block">
            <span class="info-label">COMPANY</span>
            <span class="info-value">Astra International</span>
          </div>
          <div class="info-block">
            <span class="info-label">LOCATION</span>
            <span class="info-value">Jakarta, ID</span>
          </div>
        </div>
      </div>

      <div class="settings-section">
        <h4 class="section-title">SETTINGS & INFORMATION</h4>

        <div class="setting-item" @click="$emit('open-personal-info')">
          <div class="setting-icon-wrapper">
            <img src="../assets/profile full color.png" alt="Personal Info" />
          </div>
          <span class="setting-text">Personal Information</span>
          <span class="chevron">›</span>
        </div>

        <div class="setting-item" @click="$emit('open-settings')">
          <div class="setting-icon-wrapper">
            <img src="../assets/app settings.png" alt="App Settings" />
          </div>
          <span class="setting-text">App Settings</span>
          <span class="chevron">›</span>
        </div>

        <div class="setting-item" @click="$emit('open-privacy')">
          <div class="setting-icon-wrapper">
            <img src="../assets/privacy.png" alt="Privacy Policy" />
          </div>
          <span class="setting-text">Privacy & Policy</span>
          <span class="chevron">›</span>
        </div>

        <div class="setting-item logout-item" @click="handleLogout">
          <div class="setting-icon-wrapper logout-icon-wrapper">
            <img src="../assets/logout.png" alt="Logout" />
          </div>
          <span class="setting-text logout-text">Logout</span>
        </div>
      </div>
    </div>

    <div class="bottom-nav">
      <div class="nav-item" @click="$emit('navigate', 'dashboard')">
        <img src="../assets/home.png" alt="Home" />
        <span>Home</span>
      </div>

      <div class="nav-item" @click="$emit('navigate', 'history')">
        <img src="../assets/history.png" alt="History" />
        <span>History</span>
      </div>

      <div class="nav-item-scan-wrapper" @click="$emit('navigate', 'qr-scan')">
        <div class="scan-button">
          <img src="../assets/qr.png" alt="Scan" />
        </div>
      </div>

      <div class="nav-item" @click="$emit('navigate', 'leave')">
        <img src="../assets/leave.png" alt="Leave" />
        <span>Leave</span>
      </div>

      <div class="nav-item active">
        <img src="../assets/profile.png" alt="Profile" />
        <span>Profile</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 1. LAYOUT UTAMA */
.screen-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: var(--bg-screen);
  overflow: hidden;
  transition: background-color 0.3s ease;
}

/* 2. BACKGROUND BIRU HEADER */
.blue-header {
  background: #2563eb; 
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  
  height: 100px; /* Samakan dengan Privacy Policy agar proporsional */
  border-bottom-left-radius: 40px;
  border-bottom-right-radius: 40px;
  padding: 40px 24px;
  flex-shrink: 0;
  z-index: 1;
  transition: all 0.3s ease;
}

.header-top {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon-box {
  width: 48px;
  height: 48px;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 14px;
  display: flex;
  justify-content: center;
  align-items: center;
  backdrop-filter: blur(4px);
}

.header-icon {
  width: 24px;
  height: 24px;
  filter: brightness(0) invert(1);
}

.header-titles h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #ffffff !important;
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);}

.header-titles p {
  margin: 4px 0 0 0;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.8) !important;
}

/* 3. AREA KONTEN (Overlap ke atas) */
.content {
  flex: 1;
  overflow-y: auto;
  padding: 0 24px 40px;
  margin-top: -50px;
  z-index: 2;
  scrollbar-width: none;
  padding-bottom: 120px;
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.content::-webkit-scrollbar {
  display: none;
}

/* 4. KARTU PROFIL UTAMA */
.profile-card {
  background-color: var(--bg-card); /* GANTI INI */
  border-radius: 24px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  border: 1px solid var(--border-color); /* Tambahkan border */
  text-align: center;
  margin-bottom: 30px;
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.avatar-container {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 16px;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Cover agar gambar penuh tanpa ruang kosong */
  border-radius: 24%;
  background-color: var(--bg-card);
  border: 4px solid var(--bg-card);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.avatar-circle-profile {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-radius: 24%;
  border: 4px solid var(--bg-card);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  font-weight: 700;
  color: white;
}

/* POSISI ICON PENSIL DI POJOK KANAN BAWAH */
.edit-icon {
  position: absolute;
  bottom: -6px;
  right: -6px;
  width: 32px;
  height: 32px;
  background-color: #3b82f6;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  border: 3px solid var(--bg-card);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  cursor: pointer;
}

.edit-icon img {
  width: 16px;
  height: 16px;
  filter: brightness(0) invert(1);
}

.username {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0 0 4px;
}

.user-id {
  font-size: 13px;
  font-weight: 600;
  color: #94a3b8;
  margin: 0 0 16px;
}

.badges {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-bottom: 24px;
}

.badge {
  padding: 8px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
}

.badge.blue {
  background-color: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.badge.gray {
  background-color: var(--bg-screen);
  color: var(--text-muted);
}

.company-info {
  display: flex;
  justify-content: space-around;
  border-top: 1px solid var(--border-color);
  padding-top: 20px;
}

.info-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.info-label {
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-main);
}

/* 5. LIST MENU SETTING */
.section-title {
  font-size: 12px;
  font-weight: 700;
  color: #94a3b8;
  margin: 0 0 16px 4px;
  letter-spacing: 0.5px;
  text-align: left;
  width: 100%;
}

.setting-item {
  display: flex;
  align-items: center;
  background-color: var(--bg-card); /* GANTI INI */
  border: 1px solid var(--border-color); /* Tambahkan border tipis */
  padding: 16px 20px; /* Ditambah padding kanan agar tidak mepet */
  border-radius: 16px;
  margin-bottom: 12px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
  transition: all 0.2s ease;
  text-align: left;
  width: 100%;
  box-sizing: border-box; /* Memastikan padding tidak merusak lebar */
  border: 1px solid var(--border-color);
}

.setting-item:active {
  transform: scale(0.98);
}

.setting-icon-wrapper {
  width: 40px;
  height: 40px;
  background-color: var(--bg-screen);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
}

.setting-icon-wrapper img {
  width: 20px;
  height: 20px;
  object-fit: contain;
}

.setting-text {
  flex: 1;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
  margin-right: 10px;
}

.chevron {
  font-size: 18px;
  color: var(--text-muted);
  font-weight: 600;
  width: 20px; /* Beri lebar tetap */
  display: flex;
  justify-content: flex-end; /* Paksa menempel ke arah kanan dengan rapi */
  line-height: 1;
}

/* Tombol Logout Khusus */
.logout-item {
  background-color: rgba(239, 68, 68, 0.1); /* Merah transparan */
  border: 1px solid rgba(239, 68, 68, 0.2);
  box-shadow: none;
  margin-top: 24px;
}

.logout-icon-wrapper {
  background: transparent;
}

.logout-text {
  color: #ef4444;
}

/* 6. NAVBAR FIX TERKUNCI DI BAWAH */
/* --- BOTTOM NAV (PENYELARASAN TOTAL DENGAN HISTORY) --- */
.bottom-nav {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background-color: var(--bg-card); /* GANTI INI */
  border-top: 1px solid var(--border-color);
  height: 75px; /* Sinkron 75px */
  border-top-left-radius: 30px; /* Sinkron radius 30px */
  border-top-right-radius: 30px;
  box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.2);
  display: grid;
  grid-template-columns: repeat(
    5,
    1fr
  ); /* Wajib Grid agar posisi ikon simetris */
  align-items: center;
  padding: 0 5px;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  color: #94a3b8;
  cursor: pointer;
  height: 100%;
}

.nav-item img {
  width: 24px;
  height: 24px;
  object-fit: contain;
  /* Filter abu-abu pasif agar seragam di semua halaman */
  filter: brightness(0) saturate(100%) invert(75%) sepia(11%) saturate(545%)
    hue-rotate(182deg) brightness(87%) contrast(85%);
  transition: all 0.2s ease;
}

.nav-item span {
  font-size: 10px;
  font-weight: 600;
}

/* State Aktif (Warna Biru Presisi) */
.nav-item.active {
  color: #3b82f6; /* Warna biru lebih terang sedikit agar pop up */
}

.nav-item.active img {
  filter: brightness(0) saturate(100%) invert(26%) sepia(93%) saturate(3015%)
    hue-rotate(213deg) brightness(96%) contrast(97%);
}

/* --- TOMBOL QR (SQUIRCLE VERSION) --- */
.nav-item-scan-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.scan-button {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-radius: 16px; /* Squircle style */
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
  cursor: pointer;
}

.scan-button img {
  width: 26px;
  height: 26px;
  filter: brightness(0) invert(1) !important; /* Paksa tetap putih */
}
</style>
