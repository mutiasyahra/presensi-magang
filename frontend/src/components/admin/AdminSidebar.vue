<script setup>
import { ref } from 'vue';
import bpsLogo from '../../assets/bps.png';

defineProps(['activeTab', 'pendingLeaves']);
defineEmits(['update:activeTab', 'logout']);

const collapsed = ref(false);
const toggleSidebar = () => { collapsed.value = !collapsed.value; };
</script>

<template>
  <aside :class="['sidebar', { 'sidebar--collapsed': collapsed }]">
    <!-- Floating toggle button on right edge -->
    <button
      class="toggle-pill"
      @click="toggleSidebar"
      :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="14" height="14"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        :class="['toggle-icon', { 'toggle-icon--rotated': collapsed }]"
      >
        <polyline points="15 18 9 12 15 6"></polyline>
      </svg>
    </button>

    <div class="brand">
      <img :src="bpsLogo" alt="BPS Logo" class="bps-logo" />
      <span v-if="!collapsed" class="brand-name">InternTrack</span>
    </div>

    <nav class="nav-links">
      <button :class="['nav-item', { active: activeTab === 'overview' }]" @click="$emit('update:activeTab', 'overview')" :title="collapsed ? 'Dashboard' : ''">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span v-if="!collapsed" class="nav-label">Dashboard</span>
      </button>

      <button :class="['nav-item', { active: activeTab === 'attendance' }]" @click="$emit('update:activeTab', 'attendance')" :title="collapsed ? 'Interns' : ''">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span v-if="!collapsed" class="nav-label">Interns</span>
      </button>

      <button :class="['nav-item', { active: activeTab === 'live' }]" @click="$emit('update:activeTab', 'live')" :title="collapsed ? 'Live Monitor' : ''">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="2"></circle><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"></path></svg>
        <span v-if="!collapsed" class="nav-label">Live Monitor</span>
      </button>

      <button :class="['nav-item', { active: activeTab === 'leaves' }]" @click="$emit('update:activeTab', 'leaves')" :title="collapsed ? 'Leave Approval' : ''">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
        <span v-if="!collapsed" class="nav-label">Leave Approval</span>
        <span v-if="pendingLeaves > 0" class="badge" :class="{ 'badge--dot': collapsed }">{{ collapsed ? '' : pendingLeaves }}</span>
      </button>

      <button :class="['nav-item', { active: activeTab === 'reports' }]" @click="$emit('update:activeTab', 'reports')" :title="collapsed ? 'Reports' : ''">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span v-if="!collapsed" class="nav-label">Reports</span>
      </button>

      <button :class="['nav-item', { active: activeTab === 'settings' }]" @click="$emit('update:activeTab', 'settings')" :title="collapsed ? 'Settings' : ''">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
        <span v-if="!collapsed" class="nav-label">Settings</span>
      </button>
    </nav>

    <div class="user-profile" :class="{ 'user-profile--collapsed': collapsed }">
      <div class="avatar">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
      </div>
      <template v-if="!collapsed">
        <div class="user-info">
          <p class="name">Super Admin</p>
          <p class="role">Management</p>
        </div>
        <button class="logout-btn" title="Logout" @click="$emit('logout')">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </button>
      </template>
      <button v-else class="logout-btn" title="Logout" @click="$emit('logout')">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  width: 260px;
  height: 100vh;
  background-color: #ffffff;
  border-right: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  padding: 24px 16px;
  box-sizing: border-box;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: visible;
  position: relative;
  flex-shrink: 0;
}

.sidebar--collapsed {
  width: 70px;
  padding: 24px 10px;
}

/* D-shaped Toggle Tab */
.toggle-pill {
  position: absolute;
  top: 48px;
  right: -22px;
  width: 22px;
  height: 48px;
  border-radius: 0 30px 30px 0;
  background: #ffffff;
  border: 1px solid #e8edf3;
  border-left: none;
  color: #94a3b8;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 4px 0 12px rgba(15, 23, 42, 0.07);
  z-index: 10;
  transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
  padding: 0;
  outline: none;
}

.toggle-pill:hover {
  background: #f0f6ff;
  color: #3b82f6;
  box-shadow: 4px 0 16px rgba(59, 130, 246, 0.15);
}

.toggle-pill:active {
  background: #dbeafe;
}

.toggle-icon {
  transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: -3px;
}

.toggle-icon--rotated {
  transform: rotate(180deg);
}

/* Logo */
.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 40px;
  padding-left: 4px;
  min-width: 0;
}

.sidebar--collapsed .brand {
  justify-content: center;
  padding-left: 0;
}

.bps-logo {
  width: 36px;
  height: 36px;
  object-fit: contain;
  flex-shrink: 0;
}

.brand-name {
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.5px;
}

/* Navigasi */
.nav-links {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 12px;
  color: #64748b;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.2s ease;
  position: relative;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  font-family: inherit;
  white-space: nowrap;
  overflow: hidden;
}

.sidebar--collapsed .nav-item {
  justify-content: center;
  padding: 12px 0;
  gap: 0;
}

.nav-label {
  transition: opacity 0.2s ease;
}

.nav-item .icon {
  color: #94a3b8;
  transition: color 0.2s ease;
}

/* Hover Menu Inaktif */
.nav-item:hover:not(.active) {
  background-color: #f8fafc;
  color: #0f172a;
}
.nav-item:hover:not(.active) .icon {
  color: #64748b;
}

/* Menu Aktif (Dashboard) */
.nav-item.active {
  background-color: #3b82f6;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
}

.nav-item.active .icon {
  color: #ffffff;
}

/* Badge Notifikasi Merah */
.badge {
  background-color: #ef4444;
  color: white;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: auto;
}

/* Badge dot saat collapsed */
.badge--dot {
  width: 8px;
  height: 8px;
  padding: 0;
  border-radius: 50%;
  position: absolute;
  top: 8px;
  right: 8px;
  min-width: unset;
}

/* Profil Bawah */
.user-profile {
  display: flex;
  align-items: center;
  padding: 12px;
  background-color: #f8fafc;
  border-radius: 16px;
  gap: 12px;
  transition: all 0.3s ease;
}

.user-profile--collapsed {
  justify-content: center;
  padding: 10px;
  flex-direction: column;
  gap: 8px;
}

.avatar {
  width: 36px;
  height: 36px;
  background-color: #e2e8f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.user-info {
  flex: 1;
  overflow: hidden;
}

.name {
  margin: 0;
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.role {
  margin: 2px 0 0 0;
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
}

.logout-btn {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.logout-btn:hover {
  color: #ef4444;
}
</style>