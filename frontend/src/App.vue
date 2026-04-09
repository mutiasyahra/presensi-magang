<script setup>
import { ref, onMounted } from "vue";
import QRScanner from "./components/QRScanner.vue";
import PersonalInfoDetail from "./components/PersonalInfoDetail.vue";
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
const editType = ref("in"); 

// Fungsi Navigasi yang sudah diperbaiki
const navigateTo = (page, type = "in") => {
  currentPage.value = page;
  
  // Jika pindah ke halaman edit, simpan tipenya (in/out)
  if (page === "edit-attendance") {
    editType.value = type;
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
    if (savedPage === "login") {
      currentPage.value = "login";
    } else {
      currentPage.value = "landing";
      setTimeout(() => {
        navigateTo("login");
      }, 3000);
    }
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
  navigateTo("landing");
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
      <LandingScreen
        v-if="currentPage === 'landing'"
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
      />

      <EditAttendance 
        v-else-if="currentPage === 'edit-attendance'" 
        :type="editType" 
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
        @go-back="currentPage = 'profile'" 
      />
    </div>
  </div>
</template>

<style>
body {
  margin: 0;
  padding: 0;
  font-family: "Inter", sans-serif;
  background-color: #f8fafc;
}

.app-background {
  background-color: #e2e8f0;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.mobile-frame {
  width: 100%;
  max-width: 480px;
  min-width: 360px;
  min-height: 100vh;
  background-color: #ffffff;
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow-x: hidden;
}

@media screen and (max-width: 480px) {
  .app-background {
    background-color: #ffffff;
    display: block;
  }
  .mobile-frame {
    max-width: 100%;
    min-width: 100%;
    box-shadow: none;
    min-height: 100vh;
  }
}
</style>