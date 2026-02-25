<script setup>
import { ref } from 'vue'

const emit = defineEmits(['navigate'])

const handleLogout = () => {
  // Simulasi Logout: Kembalikan user ke halaman login
  emit('navigate', 'login')
}
</script>

<template>
  <div class="screen-container">
    
    <div class="blue-header">
      <div class="header-top">
        <div class="header-icon-box">
          <img src="../assets/profile with roundabout.png" alt="User" class="header-icon" />
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
          <img src="../assets/profile with roundabout.png" alt="Avatar" class="avatar-img" />
          
          <div class="edit-icon">
            <img src="../assets/pencil.png" alt="Edit" />
          </div>
        </div>
        
        <h3 class="username">Username</h3>
        <p class="user-id">ID: 2023988231</p>
        
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

        <div class="setting-item">
          <div class="setting-icon-wrapper">
            <img src="../assets/profile full color.png" alt="Personal Info">
          </div>
          <span class="setting-text">Personal Information</span>
          <span class="chevron">›</span>
        </div>

        <div class="setting-item">
          <div class="setting-icon-wrapper">
            <img src="../assets/app settings.png" alt="App Settings">
          </div>
          <span class="setting-text">App Settings</span>
          <span class="chevron">›</span>
        </div>

        <div class="setting-item">
          <div class="setting-icon-wrapper">
            <img src="../assets/privacy.png" alt="Privacy Policy">
          </div>
          <span class="setting-text">Privacy & Policy</span>
          <span class="chevron">›</span>
        </div>

        <div class="setting-item logout-item" @click="handleLogout">
          <div class="setting-icon-wrapper logout-icon-wrapper">
            <img src="../assets/logout.png" alt="Logout">
          </div>
          <span class="setting-text logout-text">Logout</span>
        </div>
      </div>

    </div>

    <div class="bottom-nav">
      <div class="nav-item" @click="$emit('navigate', 'dashboard')">
        <img src="../assets/home.png" alt="Home">
        <span>Home</span>
      </div>
      <div class="nav-item" @click="$emit('navigate', 'history')">
        <img src="../assets/history.png" alt="History">
        <span>History</span>
      </div>
      
      <div class="nav-item scan-btn" @click="$emit('navigate', 'scan')">
        <div class="scan-circle">
          <img src="../assets/qr.png" alt="Scan QR" class="qr-icon">
        </div>
      </div>
      
      <div class="nav-item" @click="$emit('navigate', 'leave')">
        <img src="../assets/leave.png" alt="Leave">
        <span>Leave</span>
      </div>
      
      <div class="nav-item active">
        <img src="../assets/profile.png" alt="Profile"> 
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
  background-color: #F8FAFC;
  overflow: hidden;
}

/* 2. BACKGROUND BIRU HEADER */
.blue-header {
  background-color: #2563EB;
  height: 100px;
  border-bottom-left-radius: 40px;
  border-bottom-right-radius: 40px;
  padding: 40px 24px;
  flex-shrink: 0;
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
  color: white;
}

.header-titles p {
  margin: 4px 0 0 0;
  font-size: 12px;
  color: #DBEAFE;
}

/* 3. AREA KONTEN (Overlap ke atas) */
.content {
  flex: 1;
  overflow-y: auto;
  padding: 0 24px 40px;
  margin-top: -50px;
  z-index: 2;
  scrollbar-width: none;
}
.content::-webkit-scrollbar { display: none; }

/* 4. KARTU PROFIL UTAMA */
.profile-card {
  background: white;
  border-radius: 24px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  text-align: center;
  margin-bottom: 30px;
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
  background-color: white;
  border: 4px solid white;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

/* POSISI ICON PENSIL DI POJOK KANAN BAWAH */
.edit-icon {
  position: absolute;
  bottom: -6px;
  right: -6px; 
  width: 32px;
  height: 32px;
  background-color: #3B82F6;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  border: 3px solid white;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
  color: #1E293B;
  margin: 0 0 4px;
}

.user-id {
  font-size: 13px;
  font-weight: 600;
  color: #94A3B8;
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
  background-color: #EFF6FF;
  color: #3B82F6;
}

.badge.gray {
  background-color: #F8FAFC;
  color: #64748B;
}

.company-info {
  display: flex;
  justify-content: space-around;
  border-top: 1px solid #F1F5F9;
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
  color: #94A3B8;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 13px;
  font-weight: 700;
  color: #1E293B;
}

/* 5. LIST MENU SETTING */
.section-title {
  font-size: 12px;
  font-weight: 700;
  color: #94A3B8;
  margin: 0 0 16px 4px;
  letter-spacing: 0.5px;
  text-align: left;
  width: 100%;
}

.setting-item {
  display: flex;
  align-items: center;
  background: white;
  padding: 16px;
  border-radius: 16px;
  margin-bottom: 12px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
  transition: transform 0.2s;
  text-align: left;
  width: 100%;
}

.setting-item:active {
  transform: scale(0.98);
}

.setting-icon-wrapper {
  width: 40px;
  height: 40px;
  background: #F8FAFC;
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
  color: #1E293B;
}

.chevron {
  font-size: 20px;
  color: #CBD5E1;
  font-weight: 600;
}

/* Tombol Logout Khusus */
.logout-item {
  background: #FEF2F2;
  box-shadow: none;
  margin-top: 24px;
}

.logout-icon-wrapper {
  background: transparent;
}

.logout-text {
  color: #EF4444;
}

/* 6. NAVBAR FIX TERKUNCI DI BAWAH */
.bottom-nav {
  flex-shrink: 0;
  width: 100%;
  height: 70px;
  background-color: #ffffff;
  display: flex;
  justify-content: space-around;
  align-items: center;
  border-top: 1px solid #f1f5f9;
  z-index: 100;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
}

.nav-item { 
  display: flex; flex-direction: column; align-items: center; gap: 4px; cursor: pointer; 
  color: #94A3B8; 
}
.nav-item img { 
  width: 24px; height: 24px; 
  filter: brightness(0) saturate(100%) invert(75%) sepia(11%) saturate(545%) hue-rotate(182deg) brightness(87%) contrast(85%);
  transition: all 0.2s ease;
}
.nav-item span { font-size: 10px; font-weight: 600; }

/* STATE AKTIF UNTUK PROFILE */
.nav-item.active { color: #2563EB; }
.nav-item.active img { 
  filter: brightness(0) saturate(100%) invert(26%) sepia(93%) saturate(3015%) hue-rotate(213deg) brightness(96%) contrast(97%);
}

.scan-btn { position: static; }

.scan-circle {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  border-radius: 16px; 
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
  margin: 0;
}
.scan-circle .qr-icon { width: 24px; height: 24px; filter: brightness(0) invert(1); }
</style>