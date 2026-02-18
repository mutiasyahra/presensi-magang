<script setup>
import { ref } from 'vue'
import LandingScreen from './components/LandingScreen.vue'
import DashboardScreen from './components/DashboardScreen.vue'
import ClockInScreen from './components/ClockInScreen.vue'
import ClockOutScreen from './components/ClockOutScreen.vue'
import HistoryScreen from './components/HistoryScreen.vue' // 1. IMPORT BARU

const currentPage = ref('landing')

const navigateTo = (page) => {
  currentPage.value = page
  window.scrollTo(0, 0)
}
</script>

<template>
  <div class="app-background">
    <div class="mobile-frame">
      
      <LandingScreen v-if="currentPage === 'landing'" @click-login="navigateTo('dashboard')" />

      <DashboardScreen 
        v-if="currentPage === 'dashboard'" 
        @open-clock-in="navigateTo('clock-in')"
        @navigate="navigateTo" 
      />

      <ClockInScreen v-if="currentPage === 'clock-in'" @go-back="navigateTo('dashboard')" />

      <ClockOutScreen v-if="currentPage === 'clock-out'" @go-back="navigateTo('dashboard')" />
      
      <HistoryScreen 
        v-if="currentPage === 'history'" 
        @go-back="navigateTo('dashboard')"
        @navigate="navigateTo"
      />
      
    </div>
  </div>
</template>

<style>
/* Reset Dasar */
body { 
  margin: 0; 
  padding: 0; 
  font-family: 'Inter', sans-serif;
  background-color: #F8FAFC; 
}

/* LOGIK DESKTOP (Tampilan default di Laptop) */
.app-background {
  background-color: #e2e8f0; /* Warna abu-abu di luar HP */
  min-height: 100vh;
  display: flex;
  justify-content: center; /* Posisi di tengah layar */
  align-items: flex-start;
  padding-top: 0;
}

.mobile-frame {
  width: 100%;
  max-width: 480px; /* Maksimal lebar seperti HP */
  min-width: 360px; /* PENTING: Jangan biarkan mengecil di bawah 360px agar tidak hancur */
  min-height: 100vh;
  background-color: #ffffff;
  box-shadow: 0 0 40px rgba(0,0,0,0.1); /* Efek bayangan */
  position: relative;
  overflow-x: hidden;
}

/* LOGIK MOBILE (Jika dibuka di HP beneran / Layar sempit) */
@media screen and (max-width: 480px) {
  .app-background {
    background-color: #ffffff; /* Background jadi putih */
    display: block; /* Matikan mode tengah */
  }
  
  .mobile-frame {
    max-width: 100%; /* Penuhi layar */
    min-width: 100%;
    box-shadow: none; /* Hilangkan bayangan */
    min-height: 100vh;
  }
}
</style>