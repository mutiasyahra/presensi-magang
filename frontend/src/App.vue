<script setup>
import { ref, onMounted } from "vue";
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

// State halaman dan tipe edit
const currentPage = ref("landing");
const editType = ref("in");
const showSplash = ref(false);

const onSplashDone = () => {
  showSplash.value = false;
  navigateTo("login");
};

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
      <SplashScreen v-if="showSplash" @done="onSplashDone" />

      <LandingScreen
        v-if="!showSplash && currentPage === 'landing'"
        @click-login="navigateTo('login')"
      />

      <LoginScreen
        v-if="currentPage === 'login'"
        @login-success="onLoginSuccess"
      />

      <DashboardScreen
        v-if="currentPage === 'dashboard'"
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
        v-if="currentPage === 'clock-in'"
        @go-back="navigateTo('dashboard')"
      />

      <ClockOutScreen
        v-if="currentPage === 'clock-out'"
        @go-back="navigateTo('dashboard')"
      />

      <HistoryScreen
        v-if="currentPage === 'history'"
        @go-back="navigateTo('dashboard')"
        @navigate="navigateTo"
      />

      <LeaveScreen
        v-if="currentPage === 'leave'"
        @go-back="navigateTo('dashboard')"
        @navigate="navigateTo"
      />

      <ProfileScreen
        v-if="currentPage === 'profile'"
        @navigate="navigateTo"
        @logout="onLogout"
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