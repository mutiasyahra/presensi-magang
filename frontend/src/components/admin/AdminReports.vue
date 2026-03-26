<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api/axios.js';

const attendanceRecords = ref([]);
const stats = ref({});
const isLoading = ref(false);
const searchQuery = ref('');
const dateRange = ref(''); // Placeholder for simplified range

const fetchReportData = async () => {
  isLoading.value = true;
  try {
    // Fetch individual stats
    const statsRes = await api.get('/stats');
    stats.value = statsRes.data;

    // Fetch attendance list (default all)
    const params = {};
    if (searchQuery.value) params.search = searchQuery.value;
    
    const attRes = await api.get('/attendances', { params });
    attendanceRecords.value = attRes.data?.data || attRes.data || [];
  } catch (error) {
    console.error("[AdminReports] Gagal load data:", error);
  } finally {
    isLoading.value = false;
  }
};

const downloadRecap = async () => {
  try {
    const response = await api.get('/export', { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Attendance_Report_${new Date().toISOString().split('T')[0]}.xlsx`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    alert("Export failed.");
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", { day: "numeric", month: "short", year: "numeric" });
};

const formatTimeOnly = (dateTimeStr) => {
  if (!dateTimeStr) return '-';
  const date = new Date(dateTimeStr);
  return date.toLocaleTimeString("en-US", { hour: '2-digit', minute: '2-digit', hour12: true });
};

const formatDuration = (start, end) => {
  if (!start || !end) return '0h 0m';
  const startTime = new Date(start);
  const endTime = new Date(end);
  const diffMs = endTime - startTime;
  if (diffMs <= 0) return '0h 0m';
  
  const diffHrs = Math.floor(diffMs / 3600000);
  const diffMins = Math.floor((diffMs % 3600000) / 60000);
  return `${diffHrs}h ${diffMins}m`;
};

const formatLocation = (lat, long) => {
  if (!lat || !long) return 'Remote';
  return `${parseFloat(lat).toFixed(4)}, ${parseFloat(long).toFixed(4)}`;
};

const getInitials = (name) => {
  if (!name) return 'U';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

onMounted(fetchReportData);
</script>


<template>
  <div class="reports-container">
    
    <div class="page-header">
      <div class="header-titles">
        <h2>Unified Attendance Reports & Records</h2>
        <p>High-level trends and individual logs in one view.</p>
      </div>
      <button class="btn-export" @click="downloadRecap">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Export Data
      </button>
    </div>

    <div class="filters-card">
      <div class="filter-group">
        <label>DATE RANGE</label>
        <div class="input-wrapper">
          <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          <input type="text" v-model="dateRange" placeholder="Future Implementation..." readonly />
        </div>
      </div>
      <div class="filter-group">
        <label>DEPARTMENT</label>
        <div class="input-wrapper select-wrapper">
          <select disabled>
            <option>All Departments</option>
          </select>
        </div>
      </div>
      <div class="filter-group">
        <label>SEARCH INTERN</label>
        <div class="input-wrapper">
          <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" v-model="searchQuery" @keyup.enter="fetchReportData" placeholder="Name or ID..." />
        </div>
      </div>
      <div class="filter-actions">
        <button class="btn-apply" @click="fetchReportData">
          <svg v-if="!isLoading" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
          <span v-else>Loading...</span>
          Apply Filters
        </button>
      </div>
    </div>

    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Avg. Attendance</span>
        <div class="stat-value-row">
          <h2>{{ stats.avg_attendance ?? '0' }}%</h2>
          <span class="badge badge-grey">Monthly Avg</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">On-Time Rate</span>
        <div class="stat-value-row">
          <h2>{{ stats.on_time_rate ?? '0' }}%</h2>
          <span class="badge badge-blue">Presence Quality</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">Leaves & Permits</span>
        <div class="stat-value-row">
          <h2>{{ stats.pending_leaves ?? 0 }}</h2>
          <span class="badge badge-grey">Awaiting Action</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">Active Interns</span>
        <div class="stat-value-row">
          <h2>{{ stats.total_interns ?? 0 }}</h2>
          <span class="badge badge-blue">{{ stats.total_departments ?? 0 }} Depts</span>
        </div>
      </div>
    </div>

    <div class="main-card">
      <div class="card-header">
        <div class="header-left">
          <h3>Detailed Attendance Records</h3>
          <p v-if="isLoading">Updating records...</p>
          <p v-else>Showing {{ attendanceRecords.length }} records</p>
        </div>
        <div class="header-right">
          <button class="btn-icon" @click="fetchReportData"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg></button>
        </div>
      </div>

      <div class="table-container">
        <table class="reports-table">
          <thead>
            <tr>
              <th>INTERN DETAILS</th>
              <th>DATE</th>
              <th>CLOCK-IN</th>
              <th>CLOCK-OUT</th>
              <th>TOTAL HOURS</th>
              <th>LOCATION</th>
              <th>STATUS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="attendanceRecords.length === 0">
              <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">No attendance records found.</td>
            </tr>
            <tr v-for="record in attendanceRecords" :key="record.id">
              <td>
                <div class="intern-details">
                  <div class="avatar-placeholder" style="background-color: #f1f5f9; color: #3b82f6">
                    {{ getInitials(record.user?.name) }}
                  </div>
                  <div class="info">
                    <p class="name">{{ record.user?.name || 'Unknown' }}</p>
                    <p class="dept">{{ record.user?.intern?.department || 'N/A' }}</p>
                  </div>
                </div>
              </td>
              <td><span class="regular-text">{{ formatDate(record.attendance_date) }}</span></td>
              <td><span class="regular-text">{{ formatTimeOnly(record.clock_in) }}</span></td>
              <td><span class="regular-text">{{ formatTimeOnly(record.clock_out) }}</span></td>
              <td><span class="regular-text">{{ formatDuration(record.clock_in, record.clock_out) }}</span></td>
              <td><span class="regular-text">{{ formatLocation(record.clock_in_lat, record.clock_in_long) }}</span></td>
              <td>
                <span class="status-badge" :class="record.status === 'terlambat' ? 'late-entry' : (record.status === 'hadir' ? 'present' : 'absent')">
                  {{ record.status?.toUpperCase() }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination-footer">
        <p>Showing {{ attendanceRecords.length }} records</p>
        <div class="pagination">
          <button class="page-btn" disabled>Previous</button>
          <button class="page-btn" disabled>Next</button>
        </div>
      </div>
    </div>


  </div>
</template>

<style scoped>
.reports-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding-bottom: 30px;
}

/* 1. Page Header (Sticky) */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 30;
  background-color: #F8FAFC; /* Sesuaikan dengan background utama aplikasi */
  padding: 16px 0;
  margin-bottom: 4px;
}

.header-titles h2 { margin: 0; font-size: 22px; font-weight: 700; color: #0F172A; }
.header-titles p { margin: 4px 0 0 0; font-size: 14px; color: #64748B; }

.btn-export {
  display: flex; align-items: center; gap: 8px;
  background: white; border: 1px solid #E2E8F0; color: #0F172A;
  padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-export:hover { background: #F1F5F9; }

/* 2. Filters Card */
.filters-card {
  background: white;
  padding: 20px 24px;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  display: flex;
  align-items: flex-end;
  gap: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.filter-group label {
  font-size: 11px; font-weight: 700; color: #64748B; letter-spacing: 0.5px;
}

.input-wrapper {
  display: flex; align-items: center; gap: 8px;
  border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 12px;
  background: white;
}
.input-wrapper .icon { color: #94A3B8; flex-shrink: 0; }
.input-wrapper input { border: none; outline: none; font-size: 13px; width: 100%; color: #0F172A; background: transparent; }
.input-wrapper input::placeholder { color: #94A3B8; }

.select-wrapper select {
  border: none; outline: none; font-size: 13px; width: 100%; color: #0F172A; background: transparent; cursor: pointer;
}

.btn-apply {
  display: flex; align-items: center; gap: 8px; justify-content: center;
  background: #3B82F6; color: white; border: none;
  height: 40px; padding: 0 24px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
}
.btn-apply:hover { background: #2563EB; }

/* 3. Stats Row */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.stat-card {
  background: white; padding: 20px; border-radius: 16px;
  border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.stat-label { font-size: 13px; color: #64748B; }

.stat-value-row {
  display: flex; align-items: center; justify-content: space-between; margin-top: 8px;
}
.stat-value-row h2 { margin: 0; font-size: 26px; font-weight: 700; color: #0F172A; }

.badge { font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px; }
.badge-trend-up { background: #DCFCE7; color: #10B981; }
.badge-trend-down { background: #FEF2F2; color: #EF4444; }
.badge-grey { background: #F1F5F9; color: #64748B; }
.badge-blue { background: #EFF6FF; color: #3B82F6; }

/* 4. Main Table Card */
.main-card {
  background: white; border-radius: 16px; border: 1px solid #E2E8F0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;
}

.card-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid #E2E8F0;
}
.header-left h3 { margin: 0; font-size: 16px; font-weight: 700; color: #0F172A; }
.header-left p { margin: 4px 0 0 0; font-size: 13px; color: #64748B; }

.header-right { display: flex; gap: 8px; }
.btn-icon {
  background: white; border: 1px solid #E2E8F0; width: 36px; height: 36px;
  border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748B; cursor: pointer;
}
.btn-icon:hover { background: #F8FAFC; color: #0F172A; }

/* Table Styles */
.table-container { overflow-x: auto; max-height: 500px; /* Opsional: beri tinggi maks jika ingin scroll internal tabel */ }

.reports-table { width: 100%; border-collapse: collapse; text-align: left; }

.reports-table th {
  padding: 16px 24px; font-size: 11px; font-weight: 700; color: #94A3B8;
  border-bottom: 1px solid #E2E8F0; letter-spacing: 0.5px;
  position: sticky; top: 0; background-color: white; z-index: 10; /* Sticky column header */
}

.reports-table td {
  padding: 16px 24px; border-bottom: 1px solid #E2E8F0; vertical-align: middle;
}

/* Column Contents */
.intern-details { display: flex; align-items: center; gap: 12px; }
.avatar-placeholder {
  width: 36px; height: 36px; border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
}
.intern-details .name { margin: 0; font-size: 14px; font-weight: 600; color: #0F172A; }
.intern-details .dept { margin: 0; font-size: 12px; color: #64748B; }

.regular-text { font-size: 13px; color: #0F172A; }

.status-badge {
  font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 99px;
}
.status-badge.present { background: #DCFCE7; color: #10B981; }
.status-badge.late-entry { background: #FEFCE8; color: #D97706; border: 1px solid #FEF08A; }
.status-badge.absent { background: #FEF2F2; color: #EF4444; }

/* Pagination */
.pagination-footer {
  display: flex; justify-content: space-between; align-items: center; padding: 16px 24px;
}
.pagination-footer p { margin: 0; font-size: 13px; color: #64748B; }
.pagination { display: flex; gap: 8px; }
.page-btn {
  padding: 6px 16px; border: 1px solid #E2E8F0; background: white; border-radius: 6px;
  font-size: 13px; font-weight: 500; color: #0F172A; cursor: pointer;
}
.page-btn:hover { background: #F8FAFC; }
</style>