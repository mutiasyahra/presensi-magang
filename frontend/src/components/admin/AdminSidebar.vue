<script setup>
defineProps(['activeTab', 'pendingLeaves']);
defineEmits(['update:activeTab', 'logout']);
</script>

<template>
  <aside class="sidebar">
    <div class="sidebar-glow"></div>
    
    <div class="brand">
      <div class="logo-box">
        <span class="logo-icon">✨</span>
      </div>
      <div class="brand-text">
        <h2 class="title">InternTrack</h2>
        <p class="subtitle">Admin Console</p>
      </div>
    </div>
    
    <nav class="nav-menu">
      <button :class="{ active: activeTab === 'overview' }" @click="$emit('update:activeTab', 'overview')">
        <div class="icon-wrap"><img src="../../assets/home.png" alt="Home"></div>
        <span>Overview</span>
      </button>

      <button :class="{ active: activeTab === 'attendance' }" @click="$emit('update:activeTab', 'attendance')">
        <div class="icon-wrap"><img src="../../assets/history.png" alt="History"></div>
        <span>Attendance</span>
      </button>

      <button :class="{ active: activeTab === 'leaves' }" @click="$emit('update:activeTab', 'leaves')">
        <div class="icon-wrap"><img src="../../assets/leave.png" alt="Leave"></div>
        <span>Leave Requests</span>
        <span v-if="pendingLeaves > 0" class="notif-badge">{{ pendingLeaves }}</span>
      </button>

      <button :class="{ active: activeTab === 'reports' }" @click="$emit('update:activeTab', 'reports')">
        <div class="icon-wrap"><img src="../../assets/daily report.png" alt="Reports"></div>
        <span>Reports</span>
      </button>
    </nav>

    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="avatar-box">
           <img src="../../assets/profile.png" alt="Profile">
        </div>
        <div class="profile-info">
          <p class="name">Super Admin</p>
          <p class="role">Management</p>
        </div>
      </div>
      <button class="btn-logout" @click="$emit('logout')">
        <span class="logout-icon">🚪</span>
        Logout
      </button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  width: 280px;
  background: #0F172A;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 20px 16px; /* Reduced padding */
  height: 100vh;
  position: relative;
  overflow-y: auto;
  box-shadow: 10px 0 30px rgba(0,0,0,0.2);
  z-index: 10;
}

.sidebar-glow {
  position: absolute;
  top: -100px; left: -100px;
  width: 300px; height: 300px;
  background: radial-gradient(circle, rgba(124, 58, 237, 0.15) 0%, rgba(124, 58, 237, 0) 70%);
  pointer-events: none;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 30px; /* Reduced margin */
  padding: 0 10px;
}

.logo-box {
  width: 40px; height: 40px; /* Smaller logo */
  background: linear-gradient(135deg, #7C3AED 0%, #2563EB 100%);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  box-shadow: 0 8px 15px rgba(37, 99, 235, 0.3);
}

.brand-text .title {
  font-size: 18px; font-weight: 700; color: white; margin: 0;
  letter-spacing: -0.5px;
}
.brand-text .subtitle {
  font-size: 10px; color: #94A3B8; margin: 0;
  text-transform: uppercase; letter-spacing: 1.5px;
}

.nav-menu {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px; /* Reduced gap */
}

.nav-menu button {
  background: transparent;
  border: none;
  color: #94A3B8;
  padding: 12px 14px; /* Reduced padding */
  border-radius: 12px;
  text-align: left;
  width: 100%;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
}

.icon-wrap {
  width: 20px; height: 20px;
  display: flex; align-items: center; justify-content: center;
}
.icon-wrap img { width: 100%; height: 100%; object-fit: contain; filter: grayscale(1) opacity(0.7); }

.nav-menu button:hover {
  background: rgba(255,255,255,0.05);
  color: #E2E8F0;
}

.nav-menu button.active {
  background: linear-gradient(90deg, rgba(37, 99, 235, 0.15) 0%, rgba(37, 99, 235, 0) 100%);
  color: #3B82F6;
}
.nav-menu button.active .icon-wrap img { filter: none opacity(1); }

.nav-menu button.active::before {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 4px; background: #3B82F6;
  border-radius: 0 4px 4px 0;
  box-shadow: 2px 0 10px rgba(59, 130, 246, 0.5);
}

.notif-badge {
  background: #EF4444;
  color: white;
  font-size: 10px; font-weight: 700;
  padding: 1px 6px;
  border-radius: 8px;
  margin-left: auto;
  box-shadow: 0 0 10px rgba(239, 68, 68, 0.4);
}

.sidebar-footer {
  margin-top: auto;
  display: flex; flex-direction: column; gap: 15px; /* Reduced gap */
  padding: 15px 0 0 0;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.admin-profile {
  display: flex; align-items: center; gap: 10px;
  padding: 0 10px;
}
.avatar-box {
  width: 36px; height: 36px;
  border-radius: 50%; background: #334155;
  padding: 6px; border: 2px solid #1E293B;
}
.avatar-box img { width: 100%; height: 100%; }

.profile-info .name { font-size: 13px; font-weight: 600; margin: 0; }
.profile-info .role { font-size: 10px; color: #64748B; margin: 0; }

.btn-logout {
  width: 100%;
  padding: 10px; /* Reduced padding */
  background: rgba(239, 68, 68, 0.1);
  color: #F87171;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all 0.2s ease;
}
.btn-logout:hover {
  background: #EF4444;
  color: white;
}
</style>
