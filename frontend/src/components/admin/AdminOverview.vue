<script setup>
defineProps(['stats']);

// Data bohongan (dummy) untuk tampilan awal. Nanti bisa diganti dengan data dari Backend!
const weeklyAttendance = [
  { day: 'Mon', present: 85, late: 15 },
  { day: 'Tue', present: 92, late: 8 },
  { day: 'Wed', present: 78, late: 22 },
  { day: 'Thu', present: 95, late: 5 },
  { day: 'Fri', present: 88, late: 12 },
  { day: 'Sat', present: 5, late: 2 },
  { day: 'Sun', present: 10, late: 0 },
];

const recentActivities = [
  { id: 1, name: 'Sarah Jenkins', role: 'UI/UX Designer', action: 'Clocked In', time: '08:45 AM', location: 'Main Hub, Jakarta', status: 'ON TIME' },
  { id: 2, name: 'Michael Chen', role: 'Fullstack Dev', action: 'Clocked Out', time: '04:12 PM', location: 'Remote Office', status: 'EARLY OUT' },
  { id: 3, name: 'Aileen', action: 'Clocked In', time: '08:00 AM', location: 'Main Hub, Jakarta', status: 'ON TIME' },
  { id: 4, name: 'Citra Kirana', action: 'Clocked In', time: '09:15 AM', location: 'Remote Office', status: 'LATE ARRIVAL' },
];
</script>

<template>
  <header class="top-header">
        <div class="header-left">
          <h1 class="page-title">Admin Dashboard</h1>
          <p class="page-subtitle">Welcome back, Admin. Here's an overview of current attendance.</p>
        </div>
        
        <div class="header-right">
          <div class="search-bar">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" placeholder="Search interns..." />
          </div>
          
          <button class="icon-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          </button>
          
          <button class="icon-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
          </button>
        </div>
    </header>
  <div class="dashboard-container">
    <section class="overview-grid">
      <div class="stat-card" style="--delay: 0.1s">
        <div class="card-header">
          <div class="icon-wrapper blue-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <span class="badge badge-green">+12%</span>
        </div>
        <div class="card-body">
          <p class="label">Total Interns</p>
          <h3 class="value">{{ stats?.total_interns || 1248 }}</h3>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.2s">
        <div class="card-header">
          <div class="icon-wrapper green-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          </div>
          <span class="badge badge-grey">94.8% Rate</span>
        </div>
        <div class="card-body">
          <p class="label">Present Today</p>
          <h3 class="value">{{ stats?.present || 1184 }}</h3>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.3s">
        <div class="card-header">
          <div class="icon-wrapper orange-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21.5 4c0 0-2 .5-3.5 2L14.5 9.5 6.3 7.7 4 9.5l6.5 3.5L7 16.5 4 16l-2 2 4.5 1.5L8 24l2-2-.5-3 3.5-3.5 3.5 6.5 1.8-2.3z"></path></svg>
          </div>
          <span class="badge badge-orange">4 Pending</span>
        </div>
        <div class="card-body">
          <p class="label">On Leave</p>
          <h3 class="value">{{ stats?.absent || 42 }}</h3>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.4s">
        <div class="card-header">
          <div class="icon-wrapper red-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          </div>
          <span class="badge badge-red">+2 today</span>
        </div>
        <div class="card-body">
          <p class="label">Late Arrivals</p>
          <h3 class="value">{{ stats?.late || 22 }}</h3>
        </div>
      </div>
    </section>

    <section class="dashboard-content">
      
      <div class="content-card chart-area">
        <div class="card-header-flex">
          <h3 class="section-title">Weekly Attendance Summary</h3>
          <div class="dropdown">
            <span class="dropdown-label">Last 7 Days</span>
            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </div>
        </div>
        
        <div class="bar-chart">
          <div class="bar-group" v-for="(data, index) in weeklyAttendance" :key="index">
            <div class="bar-wrapper">
              <div class="bar bar-present" :style="{ height: data.present + '%' }" title="Present"></div>
              <div class="bar bar-late" :style="{ height: data.late + '%' }" title="Late"></div>
            </div>
            <span class="day-label">{{ data.day }}</span>
          </div>
        </div>
      </div>

      <div class="content-card live-status-blue">
        <div class="card-header-flex">
          <div class="info-group">
            <h3 class="live-title">Live Status</h3>
            <span class="live-subtitle">Current session monitoring</span>
          </div>
          <svg class="live-icon-white" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49"/><path d="M17.31 4.69a10 10 0 0 1 0 14.62"/><path d="M7.76 16.24a6 6 0 0 1 0-8.49"/><path d="M4.69 17.31a10 10 0 0 1 0-14.62"/></svg>
        </div>
        
        <div class="live-time-wrap">
          <p class="live-time">09:42<span class="ampm">AM</span></p>
          <div class="active-pulse-group">
            <div class="active-dot dot-green dot-pulse"></div>
            <p class="active-text">Active monitoring in progress</p>
          </div>
        </div>

        <div class="live-stats-group">
          <div class="live-stat-item">
            <p class="live-stat-label">Recent Check-ins</p>
            <p class="live-stat-value">128</p>
          </div>
          <div class="live-stat-item">
            <p class="live-stat-label">Average Delay</p>
            <p class="live-stat-value">14 min</p>
          </div>
        </div>
        
        <button class="view-logs-btn">View Live Logs</button>
      </div>
    </section>
    
    <section class="dashboard-content recent-activity-full">
      <div class="content-card recent-activity">
        <div class="card-header-flex">
          <h3 class="section-title">Recent Activity</h3>
          <button class="view-all-btn">View All →</button>
        </div>
        
        <table class="activity-table">
          <thead>
            <tr>
              <th>Intern</th>
              <th>Event Type</th>
              <th>Timestamp</th>
              <th>Location</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="activity in recentActivities" :key="activity.id" class="activity-row">
              <td class="td-intern">
                <div class="avatar-box">
                  <img :src="activity.id % 2 === 0 ? '../../assets/profile.png' : '../../assets/profile_f.png'" alt="Profile">
                </div>
                <div class="intern-info">
                  <p class="activity-name">{{ activity.name }}</p>
                  <p class="activity-role">{{ activity.role }}</p>
                </div>
              </td>
              <td class="td-event">
                <div class="event-icon-group">
                  <svg v-if="activity.action === 'Clocked In'" class="icon-green" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M21 3 13 11"/><path d="M12 21 4 13"/><path d="M2 12 10 4"/></svg>
                  <svg v-else class="icon-orange" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H3v-6"/><path d="M3 21l8-8"/><path d="M12 3l8 8"/><path d="M22 12l-8 8"/></svg>
                  <p class="activity-action">{{ activity.action }}</p>
                </div>
              </td>
              <td class="activity-time td-timestamp">{{ activity.time }}</td>
              <td class="activity-location td-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ activity.location }}
              </td>
              <td class="td-status">
                <span :class="['badge-status', { success: activity.status === 'ON TIME', warning: activity.status === 'EARLY OUT', late: activity.status === 'LATE ARRIVAL' }]">{{ activity.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* =========================================
   HEADER STYLES (DIPINDAHKAN DARI LAYOUT)
   ========================================= */
.top-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;

  /* --- Efek Sticky (Menempel di atas) --- */
  position: sticky;
  top: 0;
  z-index: 100; /* Memastikan header selalu berada di atas konten lain */
  padding: 20px 0; /* Memberi jarak aman agar rapi saat di-scroll */
  margin-top: -20px; /* Menyeimbangkan padding atas jika diperlukan */
  
  /* --- Efek Kaca (Glassmorphism) Aesthetic --- */
  background: rgba(248, 250, 252, 0.8); /* Warna dasar semi-transparan (sesuaikan jika background app-mu putih murni) */
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px); /* Dukungan untuk browser Safari */
  border-bottom: 1px solid rgba(226, 232, 240, 0.5); /* Garis batas tipis transparan */
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.page-title {
  font-size: 24px;
  font-weight: 700;
  color: #0F172A;
  margin: 0;
}

.page-subtitle {
  font-size: 14px;
  color: #64748B;
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-bar {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: #94A3B8;
}

.search-bar input {
  padding: 10px 16px 10px 38px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 14px;
  color: #0F172A;
  outline: none;
  width: 260px;
  background-color: white;
  transition: all 0.2s ease;
}

.search-bar input::placeholder {
  color: #94A3B8;
}

.search-bar input:focus {
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.icon-btn {
  background: none;
  border: none;
  color: #64748B;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.icon-btn:hover {
  background-color: #F1F5F9;
  color: #0F172A;
}

/* =========================================
   DASHBOARD CONTENT STYLES
   ========================================= */
/* Kontainer Utama */
.dashboard-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* --- 4 Kotak Atas (Grid) --- */
.overview-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
  border: 1px solid #F1F5F9;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08);
}

.card-header {
  display: flex; justify-content: space-between; align-items: flex-start;
}

.icon-wrapper {
  width: 44px; height: 44px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
}

.blue-icon { background: #EFF6FF; color: #3B82F6; }
.green-icon { background: #ECFDF5; color: #10B981; }
.orange-icon { background: #FFF7ED; color: #F59E0B; }
.red-icon { background: #FEF2F2; color: #EF4444; }

.badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-green { background: #DCFCE7; color: #166534; }
.badge-grey { background: transparent; color: #64748B; font-weight: 500; font-size: 13px; }
.badge-orange { background: #FFEDD5; color: #C2410C; }
.badge-red { background: #FEE2E2; color: #B91C1C; }

.card-body { display: flex; flex-direction: column; gap: 4px; }
.label { color: #64748B; font-size: 14px; font-weight: 500; margin: 0; }
.value { font-size: 32px; font-weight: 700; color: #0F172A; margin: 0; }

/* --- Bagian Bawah (Grafik & Status Biru) --- */
.dashboard-content {
  display: grid;
  grid-template-columns: 2fr 1fr; /* Kolom kiri lebih lebar (2:1) */
  gap: 20px;
}

.content-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #F1F5F9;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #0F172A;
  margin: 0;
}

.dropdown {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748B;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
}

.dropdown-icon {
  color: #94A3B8;
}

.card-header-flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

/* Kiri: Bar Chart Murni CSS (Tebal, Minimalis, Aesthetic) */
.bar-chart {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  height: 250px;
  padding-bottom: 20px;
  border-bottom: 1px dashed #E2E8F0;
}

.bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  height: 100%;
  flex: 1; /* Mendorong bar ke seluruh lebar */
}

.bar-wrapper {
  display: flex;
  gap: 2px; /* Minimalis gap */
  align-items: flex-end;
  height: 100%;
  width: 60px; /* Tebal minimalis */
}

.bar {
  width: 100%;
  border-radius: 8px 8px 0 0; /* Aesthetic rounded corners */
  transition: height 0.5s ease;
}

.bar-present { background-color: #A3C9FE; } /* Biru pudar untuk data lama */
.bar-late { background-color: #A3C9FE; } /* Biru pudar untuk data lama */

/* Gaya untuk Hari Ini (Aesthetic kontras) */
.bar-group:nth-child(4) .bar-present { background-color: #3B82F6; } /* Biru tebal untuk data hari ini */
.bar-group:nth-child(4) .bar-late { background-color: #3B82F6; } /* Biru tebal untuk data hari ini */

.day-label {
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
}

/* --- Kanan: Status Biru (Informatif, Lucu, Aesthetic) --- */
.live-status-blue {
  background: #3B82F6; /* Kotak biru estetis */
  color: white;
  border-color: #3B82F6;
}

.live-title {
  font-size: 16px;
  font-weight: 700;
  color: white;
  margin: 0;
}

.live-subtitle {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

.live-icon-white {
  color: white;
}

.live-time-wrap {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 20px;
}

.live-time {
  font-size: 44px;
  font-weight: 800;
  color: white;
  margin: 0;
  letter-spacing: -1.5px;
}

.ampm {
  font-size: 20px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.8);
  margin-left: 6px;
}

.active-pulse-group {
  display: flex;
  align-items: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.9);
  font-size: 13px;
}

.active-dot {
  width: 10px; height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.dot-green { background-color: #10B981; }

.dot-pulse {
  animation: pulse 2s infinite ease-out;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
  70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.live-stats-group {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 20px;
}

.live-stat-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.live-stat-label {
  color: rgba(255, 255, 255, 0.8);
  font-size: 13px;
  font-weight: 600;
}

.live-stat-value {
  color: white;
  font-size: 20px;
  font-weight: 700;
  margin: 0;
}

.view-logs-btn {
  width: 100%;
  margin-top: 20px;
  padding: 12px;
  background-color: white;
  color: #3B82F6;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.2s ease;
}

.view-logs-btn:hover {
  background-color: #F1F5F9;
}

/* --- Recent Activity Tabel (Di Bawah Diagram & Live Status) --- */
.recent-activity-full {
  grid-template-columns: 1fr;
}

.view-all-btn {
  color: #3B82F6;
  font-size: 13px;
  font-weight: 600;
  background: none;
  border: none;
  cursor: pointer;
}

.activity-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.activity-table th {
  text-align: left;
  font-size: 12px;
  color: #64748B;
  font-weight: 700;
  text-transform: uppercase;
  padding: 16px;
  letter-spacing: 0.5px;
}

.activity-table td {
  padding: 16px;
  border-bottom: 1px solid #F1F5F9;
}

.activity-row:last-child td {
  border-bottom: none;
}

.activity-row:hover {
  background-color: #F8FAF9;
}

.td-intern {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-box {
  width: 36px; height: 36px;
  border-radius: 50%;
  overflow: hidden;
  background-color: #F1F5F9;
}

.avatar-box img {
  width: 100%; height: 100%;
  object-fit: cover;
}

.intern-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.activity-name {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #0F172A;
}

.activity-role {
  margin: 0;
  font-size: 12px;
  color: #64748B;
}

.td-event {
  width: 250px;
}

.event-icon-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.icon-green { color: #10B981; }
.icon-orange { color: #F59E0B; }

.activity-action {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: #0F172A;
}

.td-timestamp {
  font-size: 13px;
  font-weight: 500;
  color: #64748B;
}

.td-location {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748B;
  font-size: 13px;
}

.td-status {
  text-align: right;
}

.badge-status {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 20px;
}

.badge-status.success { background-color: #ECFDF5; color: #166534; }
.badge-status.warning { background-color: #FEF3C7; color: #C2410C; }
.badge-status.late { background-color: #FEE2E2; color: #B91C1C; }

/* Responsif */
@media (max-width: 1024px) {
  .overview-grid { grid-template-columns: repeat(2, 1fr); }
  .dashboard-content { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .overview-grid { grid-template-columns: 1fr; }
}
</style>