<script setup>
import { ref } from 'vue';

// State untuk toggle view
const activeView = ref('clock-in'); // 'clock-in' atau 'clock-out'

// Dummy data untuk kartu presensi
const attendanceCards = ref([
  {
    id: 1,
    name: 'Sarah Johnson',
    role: 'Product Design Intern',
    avatar: 'https://i.pravatar.cc/150?u=sarah',
    status: 'ON TIME',
    isVerified: true,
    time: '08:45 AM',
    location: 'Tech Hub, Jakarta Office',
    isRemote: false
  },
  {
    id: 2,
    name: 'Michael Chen',
    role: 'Backend Developer Intern',
    avatar: 'https://i.pravatar.cc/150?u=michael',
    status: 'LATE',
    isVerified: false,
    time: '09:12 AM',
    location: 'Remote - Bandung, ID',
    isRemote: true
  },
  {
    id: 3,
    name: 'David Miller',
    role: 'Marketing Intern',
    avatar: 'https://i.pravatar.cc/150?u=david',
    status: 'ON TIME',
    isVerified: true,
    time: '08:30 AM',
    location: 'Tech Hub, Jakarta Office',
    isRemote: false
  }
]);

// Fungsi helper untuk warna status/badge
const getStatusClass = (status) => {
  return status === 'ON TIME' ? 'badge-success' : 'badge-warning';
};
</script>

<template>
  <div class="monitor-container">
    <div class="page-header">
      <h1 class="main-title">Daily Attendance Monitor</h1>
      <div class="header-actions">
        <div class="search-box">
          <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" placeholder="Search intern name..." />
        </div>
        <button class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></button>
        <button class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg></button>
      </div>
    </div>

    <div class="controls-section">
      <div class="view-toggles">
        <button 
          class="toggle-btn" 
          :class="{ active: activeView === 'clock-in' }"
          @click="activeView = 'clock-in'"
        >
          Clock-in View
        </button>
        <button 
          class="toggle-btn" 
          :class="{ active: activeView === 'clock-out' }"
          @click="activeView = 'clock-out'"
        >
          Clock-out View
        </button>
      </div>
      
      <div class="right-controls">
        <div class="status-indicators">
          <span class="indicator green"><span class="dot"></span> 42 Active</span>
          <span class="indicator grey">8 Pending</span>
        </div>
        <button class="btn-filter">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
          Filters
        </button>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <p class="stat-label">ON TIME</p>
        <div class="stat-row">
          <h2 class="stat-value">38</h2>
          <span class="stat-percent text-green">90%</span>
        </div>
      </div>
      <div class="stat-card">
        <p class="stat-label">LATE ARRIVALS</p>
        <div class="stat-row">
          <h2 class="stat-value">4</h2>
          <span class="stat-percent text-orange">10%</span>
        </div>
      </div>
      <div class="stat-card">
        <p class="stat-label">OFFICE</p>
        <div class="stat-row">
          <h2 class="stat-value">32</h2>
          <span class="stat-percent text-blue">76%</span>
        </div>
      </div>
      <div class="stat-card">
        <p class="stat-label">REMOTE</p>
        <div class="stat-row">
          <h2 class="stat-value">10</h2>
          <span class="stat-percent text-grey">24%</span>
        </div>
      </div>
    </div>

    <div class="cards-grid">
      <div class="attendance-card" v-for="intern in attendanceCards" :key="intern.id">
        <div class="card-header">
          <div class="profile-area">
            <div class="avatar-wrapper">
              <img :src="intern.avatar" alt="avatar" />
              <span class="status-dot"></span>
            </div>
            <div class="info">
              <div class="name-row">
                <h3 class="name">{{ intern.name }}</h3>
                <span class="verify-badge" :class="intern.isVerified ? 'verified' : 'pending'">
                  <svg v-if="intern.isVerified" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                  {{ intern.isVerified ? 'VERIFIED' : 'PENDING' }}
                </span>
              </div>
              <p class="role">{{ intern.role }}</p>
            </div>
          </div>
        </div>
        
        <div class="time-row">
          <svg class="icon text-blue" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          <span class="time-text">{{ intern.time }}</span>
        </div>

        <div class="divider"></div>

        <div class="location-row">
          <div class="loc-left">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span class="loc-text">{{ intern.location }}</span>
          </div>
          <span class="status-pill" :class="getStatusClass(intern.status)">{{ intern.status }}</span>
        </div>

        <div class="card-actions">
          <button class="btn-primary" :class="{ 'verify-mode': !intern.isVerified }">
            {{ intern.isVerified ? 'View Timeline' : 'Verify Attendance' }}
          </button>
          <button class="btn-more">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
          </button>
        </div>
      </div>

      <div class="attendance-card border-dashed">
        <div class="manual-entry-content">
          <div class="add-icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
          </div>
          <h4>Register New Attendance Entry</h4>
          <p>Add manual check-in for exceptional cases</p>
        </div>
      </div>
    </div>

    <div class="pagination">
      <p class="pagination-info">Showing 1 to 5 of 42 Interns</p>
      <div class="pagination-controls">
        <button class="page-btn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg></button>
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Main Layout */
.monitor-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 20;
  background-color: #F8FAFC; /* Sesuaikan warna background aplikasimu */
  padding-bottom: 16px;
  padding-top: 16px; /* Biar ada jarak saat di-scroll */
}

.main-title {
  font-size: 24px;
  font-weight: 700;
  color: #0F172A;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: white;
  padding: 8px 16px;
  border-radius: 20px;
  border: 1px solid #E2E8F0;
  width: 260px;
}
.search-box .icon { color: #94A3B8; }
.search-box input {
  border: none; outline: none; background: transparent;
  font-size: 14px; width: 100%; color: #0F172A;
}
.search-box input::placeholder { color: #94A3B8; }

.btn-icon {
  display: flex; align-items: center; justify-content: center;
  width: 40px; height: 40px; border-radius: 50%;
  background: white; border: none; color: #64748B;
  cursor: pointer; transition: background 0.2s;
}
.btn-icon:hover { background: #F1F5F9; color: #0F172A; }

/* Controls Section */
.controls-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.view-toggles {
  display: flex;
  background: #F1F5F9;
  padding: 4px;
  border-radius: 12px;
}

.toggle-btn {
  padding: 8px 20px;
  border: none;
  background: transparent;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #64748B;
  cursor: pointer;
  transition: all 0.2s;
}
.toggle-btn.active { background: white; color: #0F172A; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

.right-controls {
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-indicators {
  display: flex; gap: 12px;
}
.indicator {
  display: flex; align-items: center; gap: 6px;
  padding: 6px 12px; border-radius: 20px;
  font-size: 13px; font-weight: 600;
}
.indicator.green { background: #DCFCE7; color: #166534; }
.indicator.grey { background: #F1F5F9; color: #64748B; }
.dot { width: 8px; height: 8px; border-radius: 50%; background: #10B981; }

.btn-filter {
  display: flex; align-items: center; gap: 8px;
  padding: 8px 16px; background: white;
  border: 1px solid #E2E8F0; border-radius: 8px;
  font-size: 14px; font-weight: 600; color: #0F172A;
  cursor: pointer;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stat-card {
  background: white; padding: 20px;
  border-radius: 16px; border: 1px solid #F1F5F9;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
}

.stat-label { font-size: 12px; font-weight: 700; color: #64748B; margin: 0 0 12px 0; }
.stat-row { display: flex; align-items: center; justify-content: space-between; }
.stat-value { font-size: 32px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1; }
.stat-percent { font-size: 13px; font-weight: 700; padding: 4px 8px; border-radius: 12px; }

.text-green { color: #10B981; background: #DCFCE7; }
.text-orange { color: #F59E0B; background: #FEF3C7; }
.text-blue { color: #3B82F6; background: #EFF6FF; }
.text-grey { color: #64748B; background: #F1F5F9; }

/* Cards Grid */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.attendance-card {
  background: white;
  padding: 24px;
  border-radius: 16px;
  border: 1px solid #F1F5F9;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
  display: flex; flex-direction: column; gap: 16px;
}

.border-dashed {
  border: 2px dashed #E2E8F0;
  background: transparent;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: border-color 0.2s;
  grid-column: 1 / -1; 
  min-height: 120px;
}
.border-dashed:hover { border-color: #CBD5E1; }

.manual-entry-content { text-align: center; }
.add-icon-wrapper {
  width: 48px; height: 48px; border-radius: 50%;
  background: #F1F5F9; color: #94A3B8;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 16px;
}
.manual-entry-content h4 { margin: 0 0 4px 0; font-size: 15px; color: #64748B; }
.manual-entry-content p { margin: 0; font-size: 13px; color: #94A3B8; }

/* Card Content Details */
.profile-area { display: flex; gap: 12px; }
.avatar-wrapper { position: relative; width: 48px; height: 48px; }
.avatar-wrapper img { width: 100%; height: 100%; border-radius: 8px; object-fit: cover; }
.status-dot {
  position: absolute; bottom: -4px; right: -4px;
  width: 14px; height: 14px; background: #10B981;
  border: 2px solid white; border-radius: 50%;
}

.info { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 4px; }
.name-row { display: flex; align-items: center; gap: 8px; }
.name { margin: 0; font-size: 16px; font-weight: 700; color: #0F172A; }

.verify-badge {
  display: flex; align-items: center; gap: 4px;
  font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;
}
.verify-badge.verified { background: #EFF6FF; color: #3B82F6; }
.verify-badge.pending { background: #FEF3C7; color: #F59E0B; }

.role { margin: 0; font-size: 13px; color: #64748B; }

.time-row { display: flex; align-items: center; gap: 8px; }
.time-text { font-size: 14px; font-weight: 600; color: #0F172A; }

.divider { height: 1px; background: #F1F5F9; }

.location-row { display: flex; justify-content: space-between; align-items: center; }
.loc-left { display: flex; align-items: center; gap: 8px; color: #64748B; }
.loc-text { font-size: 13px; }

.status-pill { font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 4px; }
.badge-success { background: #DCFCE7; color: #166534; }
.badge-warning { background: #FEF3C7; color: #B45309; }

/* Card Actions */
.card-actions { display: flex; gap: 12px; margin-top: 4px; }
.btn-primary {
  flex: 1; padding: 10px; border-radius: 8px;
  border: none; background: #3B82F6; color: white;
  font-size: 14px; font-weight: 600; cursor: pointer;
  transition: background 0.2s;
}
.btn-primary:hover { background: #2563EB; }
.btn-primary.verify-mode { background: #3B82F6; } /* Bisa diubah warnanya jika verify mode beda */

.btn-more {
  width: 40px; border: 1px solid #E2E8F0; background: white;
  border-radius: 8px; color: #64748B; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}
.btn-more:hover { background: #F8FAFC; }

/* Pagination */
.pagination {
  display: flex; justify-content: space-between; align-items: center;
  padding-top: 16px; border-top: 1px solid #E2E8F0;
}
.pagination-info { margin: 0; font-size: 14px; color: #64748B; }
.pagination-controls { display: flex; gap: 4px; }
.page-btn {
  display: flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border: none; background: transparent;
  color: #475569; font-size: 14px; font-weight: 500;
  border-radius: 8px; cursor: pointer; transition: background 0.2s;
}
.page-btn:hover { background: #F1F5F9; }
.page-btn.active { background: #3B82F6; color: white; font-weight: 600; }

/* Responsive adjustments */
@media (max-width: 1200px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .cards-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .controls-section { flex-direction: column; align-items: flex-start; gap: 16px; }
  .cards-grid { grid-template-columns: 1fr; }
}
</style>