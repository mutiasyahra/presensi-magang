<script setup>
import { ref } from 'vue';
import DashboardScreen from './components/DashboardScreen.vue'; // Pastikan nama file Dashboardmu benar
import LandingScreen from './components/LandingScreen.vue';     // File baru yang tadi

const isLoggedIn = ref(false);

const handleLogin = () => {
  isLoggedIn.value = true;
};
</script>

<template>
  <LandingScreen v-if="!isLoggedIn" @login-success="handleLogin" />

  <DashboardScreen v-else />
</template>

<style>
body {
  margin: 0;
  padding: 0;
  background-color: #E2E8F0; /* Biar background nyatu */
}
</style>