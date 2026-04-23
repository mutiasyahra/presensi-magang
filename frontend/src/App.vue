<script setup>
import { ref, onMounted } from "vue";
import EditProfile from "./components/EditProfile.vue";
import AttendanceDetail from "./components/AttendanceDetail.vue";
import QRScanner from "./components/QRScanner.vue";
import PersonalInfoDetail from "./components/PersonalInfoDetail.vue";
import SplashScreen from "./components/SplashScreen.vue";
import LandingScreen from "./components/LandingScreen.vue";
import LoginScreen from "./components/LoginScreen.vue";
import DashboardScreen from "./components/DashboardScreen.vue";
import ClockInScreen from "./components/ClockInScreen.vue";
import ClockOutScreen from "./components/ClockOutScreen.vue";
import HistoryScreen from "./components/HistoryScreen.vue";
import LeaveScreen from "./components/LeaveScreen.vue";
import ProfileScreen from "./components/ProfileScreen.vue";
import EditAttendance from "./components/EditAttendance.vue";
import AdminLayout from "./components/admin/AdminLayout.vue";
import AppSettings from "./components/AppSettings.vue";
import PrivacyPolicy from "./components/PrivacyPolicy.vue";

// State halaman dan tipe edit
const currentPage = ref("landing");
const selectedAttendance = ref(null); // <--- TAMBAHKAN INI
const editType = ref("in");
const editId = ref(null);
const showSplash = ref(false);

const onSplashDone = () => {
  showSplash.value = false;
  navigateTo("login");
};

// Fungsi Navigasi yang sudah diperbaiki
const navigateTo = (page, dataOrType = "in", id = null) => {
  currentPage.value = page;
  
  // 1. Jika ke Detail
  if (page === "attendance-detail") {
    selectedAttendance.value = dataOrType;
  }

  // 2. Jika ke Edit Attendance (Ganti 'type' jadi 'dataOrType')
  if (page === "edit-attendance") {
    editType.value = dataOrType; // Tadi di sini lo nulis 'type', itu yang bikin error
    editId.value = id;
  }
  
  localStorage.setItem("currentPage", page);
  window.scrollTo(0, 0);
};

onMounted(() => {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  const savedPage = localStorage.getItem("currentPage");

  if (token) {
    if (savedPage && savedPage !== "landing" && savedPage !== "login") {
      currentPage.value = savedPage;
    } else if (user.role === "admin") {
      currentPage.value = "admin";
    } else {
      currentPage.value = "dashboard";
    }
  } else {
    // Selalu tampilkan splash saat belum login
    currentPage.value = "login";
    showSplash.value = true;
  }
});

const onLoginSuccess = (user) => {
  if (user.role === "admin") {
    navigateTo("admin");
  } else {
    navigateTo("dashboard");
  }
};

const onLogout = () => {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
  localStorage.removeItem("currentPage");
  document.documentElement.classList.remove("dark");
  navigateTo("login");
};

onMounted(() => {
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    if (user && user.is_dark_mode) {
        document.documentElement.classList.add("dark");
    } else {
        document.documentElement.classList.remove("dark");
    }
});
</script>

<template>
  <AdminLayout v-if="currentPage === 'admin'" @logout="onLogout" />

  <div v-else class="app-background">
    <div class="mobile-frame">
      <SplashScreen v-if="showSplash" @done="onSplashDone" />

      <LandingScreen
        v-else-if="currentPage === 'landing'"
        @click-login="navigateTo('login')"
      />

      <QRScanner 
        v-else-if="currentPage === 'qr-scan'" 
        @go-back="navigateTo('dashboard')" 
      />

      <LoginScreen
        v-else-if="currentPage === 'login'"
        @login-success="onLoginSuccess"
      />

      <DashboardScreen
        v-else-if="currentPage === 'dashboard'"
        @open-clock-in="navigateTo('clock-in')"
        @navigate="navigateTo"
        @logout="onLogout"
        @open-detail="(data) => navigateTo('attendance-detail', data)"
      />

      <AttendanceDetail 
        v-else-if="currentPage === 'attendance-detail'"
        :attendance="selectedAttendance"
        @go-back="navigateTo('dashboard')"
      />

      <EditAttendance 
        v-else-if="currentPage === 'edit-attendance'" 
        :type="editType" 
        :attendance-id="editId"
        @navigate="navigateTo" 
      />

      <ClockInScreen
        v-else-if="currentPage === 'clock-in'"
        @go-back="navigateTo('dashboard')"
      />

      <ClockOutScreen
        v-else-if="currentPage === 'clock-out'"
        @go-back="navigateTo('dashboard')"
      />

      <HistoryScreen
        v-else-if="currentPage === 'history'"
        @go-back="navigateTo('dashboard')"
        @navigate="navigateTo"
      />

      <LeaveScreen
        v-else-if="currentPage === 'leave'"
        @go-back="navigateTo('dashboard')"
        @navigate="navigateTo"
      />

      <ProfileScreen 
        v-else-if="currentPage === 'profile'" 
        @navigate="navigateTo"
        @logout="onLogout"
        @open-personal-info="currentPage = 'profile-detail'"
        @open-settings="currentPage = 'app-settings'"
        @open-privacy="currentPage = 'privacy-policy'" 
      />

      <PrivacyPolicy 
        v-else-if="currentPage === 'privacy-policy'" 
        @go-back="currentPage = 'profile'" 
      />

      <AppSettings 
        v-else-if="currentPage === 'app-settings'" 
        @go-back="currentPage = 'profile'" 
      />

      <PersonalInfoDetail 
  v-else-if="currentPage === 'profile-detail'" 
  @go-back="navigateTo('profile')" 
/>

<EditProfile 
  v-else-if="currentPage === 'edit-profile'" 
  @go-back="navigateTo('profile')"
  @profile-updated="() => console.log('Profil Diperbarui!')"
/>
    </div>
  </div>
</template>

<style>
  /* Warna Default (Light Mode) */
:root {
  --bg-screen: #f8fafc;       /* Warna layar paling belakang */
  --bg-card: #ffffff;         /* Warna kotak/card utama */
  --text-main: #1e293b;       /* Warna font utama (gelap) */
  --text-muted: #64748b;     /* Warna font sekunder */
  --border-color: #e2e8f0;    /* Warna garis border/input */
  --icon-filter: none;
}

/* Warna saat Dark Mode aktif */
.dark {
  --bg-screen: #0f172a;       /* Layar belakang jadi biru sangat gelap */
  --bg-card: #1e293b;         /* Kotak/card lebih terang sedikit dari layar (seperti di image_06ee44.png) */
  --text-main: #f8fafc;       /* Font jadi putih/terang agar terbaca */
  --text-muted: #94a3b8;     /* Font sekunder jadi abu-abu terang */
  --border-color: #334155;    /* Border jadi lebih gelap agar tidak kontras */
  --icon-filter: brightness(0) invert(1);
}
/* Di bawah selector .dark { ... } lo tadi */

body {
  margin: 0;
  padding: 0;
  font-family: "Inter", sans-serif;
  /* GANTI INI: panggil variabelnya */
  background-color: var(--bg-screen); 
  color: var(--text-main);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.app-background {
  background-color: #e2e8f0; /* Biarkan ini untuk tampilan desktop */
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.mobile-frame {
  width: 100%;
  max-width: 480px;
  min-height: 100vh;
  /* GANTI INI: Pakai bg-screen supaya layar putihnya hilang */
  background-color: var(--bg-screen); 
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow-x: hidden;
}
</style>