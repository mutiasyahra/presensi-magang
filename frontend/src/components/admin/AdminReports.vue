<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import api from '../../api/axios.js';

const attendanceRecords = ref([]);
const stats = ref({});
const isLoading = ref(false);
const searchQuery = ref('');

// Modal Detail State
const showDetailModal = ref(false);
const selectedRecord = ref(null);

const openDetail = (record) => {
  selectedRecord.value = record;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  selectedRecord.value = null;
};

// Advanced Filters
const selectedProject = ref('All Project');
const selectedUniversity = ref('All Universities');
const selectedPeriod = ref('All Periods');

const filterOptions = ref({
  projects: [],
  universities: [],
  periods: []
});

const fetchFilterOptions = async () => {
  try {
    const res = await api.get("/attendance-filters");
    if (res.data) {
      filterOptions.value.projects = res.data.projects || [];
      filterOptions.value.universities = res.data.universities || [];
      filterOptions.value.periods = res.data.periods || [];
    }
  } catch (error) {
    console.error("[AdminReports] Gagal load filter options:", error);
  }
};

const fetchReportData = async () => {
  isLoading.value = true;
  try {
    // Fetch individual stats
    const statsRes = await api.get('/stats');
    stats.value = statsRes.data;

    // Determine dates from selected period
    let start_date = '';
    let end_date = '';
    
    if (selectedPeriod.value !== 'All Periods') {
      const period = filterOptions.value.periods.find(p => formatPeriod(p) === selectedPeriod.value);
      if (period) {
        start_date = period.start_date;
        end_date = period.end_date;
      }
    }

    // Fetch attendance list with filters
    const params = {
      search: searchQuery.value,
      project: selectedProject.value,
      university: selectedUniversity.value,
      start_date,
      end_date
    };

    // Remove default/empty params
    if (params.project === 'All Project') delete params.project;
    if (params.university === 'All Universities') delete params.university;

    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key];
    });

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

const formatPeriod = (period) => {
  if (!period.start_date || !period.end_date) return 'Invalid Period';
  const start = formatDate(period.start_date);
  const end = formatDate(period.end_date);
  return `${start} - ${end}`;
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

const formatLocation = (record) => {
  if (record.clock_in_location) return record.clock_in_location;
  if (!record.clock_in_lat || !record.clock_in_long) return 'Remote';
  return `${parseFloat(record.clock_in_lat).toFixed(4)}, ${parseFloat(record.clock_in_long).toFixed(4)}`;
};

const getInitials = (name) => {
  if (!name) return 'U';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('data:') || path.startsWith('http')) return path;
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace("/api", "");
  return `${baseUrl}/storage/${path}`;
};

// Automatic Refresh on Filter Change
watch([selectedProject, selectedUniversity, selectedPeriod], () => {
  fetchReportData();
  currentPage.value = 1;
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;
const totalPages = computed(() => Math.ceil(attendanceRecords.value.length / itemsPerPage));
const paginatedRecords = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return attendanceRecords.value.slice(start, start + itemsPerPage);
});

onMounted(() => {
  fetchReportData();
  fetchFilterOptions();
});
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
          <h2>{{ stats.total_leaves ?? 0 }}</h2>
          <span class="badge badge-grey">Total Records</span>
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

    <div class="filters-card">
      <div class="filter-group">
        <label>INTERNSHIP PERIOD</label>
        <div class="input-wrapper select-wrapper">
          <select v-model="selectedPeriod">
            <option>All Periods</option>
            <option v-for="period in filterOptions.periods" :key="period.start_date + period.end_date" :value="formatPeriod(period)">
              {{ formatPeriod(period) }}
            </option>
          </select>
        </div>
      </div>
      
      <div class="filter-group">
        <label>PROJECT</label>
        <div class="input-wrapper select-wrapper">
          <select v-model="selectedProject">
            <option>All Project</option>
            <option
              v-for="proj in filterOptions.projects"
              :key="proj"
              :value="proj"
            >
              {{ proj }}
            </option>
          </select>
        </div>
      </div>

      <div class="filter-group">
        <label>UNIVERSITY</label>
        <div class="input-wrapper select-wrapper">
          <select v-model="selectedUniversity">
            <option>All Universities</option>
            <option
              v-for="univ in filterOptions.universities"
              :key="univ"
              :value="univ"
            >
              {{ univ }}
            </option>
          </select>
        </div>
      </div>

      <div class="filter-group" style="flex: 1.5">
        <label>SEARCH INTERN</label>
        <div class="input-wrapper">
          <svg
            class="icon"
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input
            type="text"
            v-model="searchQuery"
            @keyup.enter="fetchReportData"
            placeholder="Name or ID..."
          />
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
              <th>IN STATUS</th>
              <th>PLANNING</th>
              <th>CLOCK-OUT</th>
              <th>OUT STATUS</th>
              <th>WORK REPORT</th>
              <th>TOTAL HOURS</th>
              <th>LOCATION</th>
              <th>EVIDENCE</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="attendanceRecords.length === 0">
              <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">No attendance records found.</td>
            </tr>
            <tr v-for="record in paginatedRecords" :key="record.id">
              <td>
                <div class="intern-details">
                  <div class="avatar-placeholder">
                    <img v-if="record.user?.profile_photo" :src="getImageUrl(record.user.profile_photo)" class="avatar-img-fit" />
                    <span v-else>{{ getInitials(record.user?.name) }}</span>
                  </div>
                  <div class="info">
                    <p class="name">{{ record.user?.name || 'Unknown' }}</p>
                    <p class="dept">{{ record.user?.intern?.department || 'N/A' }}</p>
                  </div>
                </div>
              </td>
              <td class="td-date"><span class="regular-text">{{ formatDate(record.attendance_date) }}</span></td>
              <td><span class="regular-text">{{ formatTimeOnly(record.clock_in) }}</span></td>
              <td>
                <span v-if="['izin', 'sakit'].includes(record.status)" class="status-badge" style="background: #e0f2fe; color: #0284c7;">
                  {{ record.status.toUpperCase() }}
                </span>
                <span v-else-if="record.clock_in" class="status-badge" :class="record.clock_in_status === 'terlambat' ? 'late-entry' : 'present'">
                  {{ (record.clock_in_status || 'tepat waktu').toUpperCase() }}
                </span>
                <span v-else class="status-badge absent">N/A</span>
              </td>
              <td class="td-wide"><span class="regular-text">{{ record.rencana_kegiatan || '-' }}</span></td>
              <td><span class="regular-text">{{ formatTimeOnly(record.clock_out) }}</span></td>
              <td>
                <span v-if="['izin', 'sakit'].includes(record.status)" class="status-badge" style="background: #e0f2fe; color: #0284c7;">
                  {{ record.status.toUpperCase() }}
                </span>
                <span v-else-if="record.clock_out" class="status-badge" :class="record.clock_out_status === 'terlambat' ? 'late-entry' : (record.clock_out_status === 'terlalu cepat' ? 'warning-entry' : 'present')">
                  {{ (record.clock_out_status || 'tepat waktu').toUpperCase() }}
                </span>
                <span v-else class="status-badge absent">PENDING</span>
              </td>
              <td class="td-wide"><span class="regular-text">{{ record.progress_kegiatan || '-' }}</span></td>
              <td><span class="regular-text">{{ formatDuration(record.clock_in, record.clock_out) }}</span></td>
              <td class="td-location"><span class="regular-text">{{ formatLocation(record) }}</span></td>
              <td>
                <div v-if="record.evidence" class="evidence-cell">
                  <a :href="getImageUrl(record.evidence)" target="_blank" class="btn-evidence">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    View
                  </a>
                </div>
                <span v-else class="regular-text">-</span>
              </td>
              <td>
                <div class="action-buttons">
                  <button class="btn-action" @click="openDetail(record)" title="View Details">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination-footer">
        <p>Showing {{ attendanceRecords.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}–{{ Math.min(currentPage * itemsPerPage, attendanceRecords.length) }} of {{ attendanceRecords.length }} records</p>
        <div class="pagination" v-if="totalPages > 1">
          <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">Previous</button>
          <button
            v-for="p in totalPages"
            :key="p"
            class="page-btn"
            :class="{ active: currentPage === p }"
            @click="currentPage = p"
          >{{ p }}</button>
          <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">Next</button>
        </div>
        <div v-else></div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetail">
      <div class="modal-card">
        <div class="modal-header">
          <div class="header-info">
            <h3>Attendance Detail</h3>
            <p>{{ formatDate(selectedRecord.attendance_date) }}</p>
          </div>
          <button class="btn-close" @click="closeDetail">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="detail-section user-overview">
            <div class="avatar-large">
              <img v-if="selectedRecord.user?.profile_photo" :src="getImageUrl(selectedRecord.user.profile_photo)" class="avatar-img-fit" />
              <span v-else>{{ getInitials(selectedRecord.user?.name) }}</span>
            </div>
            <div class="user-meta">
              <h4>{{ selectedRecord.user?.name }}</h4>
              <p>{{ selectedRecord.user?.intern?.intern_id }} • {{ selectedRecord.user?.intern?.department }}</p>
              <p class="project-tag">{{ selectedRecord.user?.intern?.project }}</p>
            </div>
          </div>

          <div class="detail-grid">
            <div class="grid-item">
              <label>Clock In</label>
              <div class="value-row">
                <span class="time">{{ formatTimeOnly(selectedRecord.clock_in) }}</span>
                <span class="status-badge" :class="selectedRecord.clock_in_status === 'terlambat' ? 'late-entry' : 'present'">
                  {{ (selectedRecord.clock_in_status || 'tepat waktu').toUpperCase() }}
                </span>
              </div>
            </div>
            <div class="grid-item">
              <label>Clock Out</label>
              <div class="value-row">
                <span class="time">{{ formatTimeOnly(selectedRecord.clock_out) }}</span>
                <span v-if="selectedRecord.clock_out" class="status-badge" :class="selectedRecord.clock_out_status === 'terlambat' ? 'late-entry' : (selectedRecord.clock_out_status === 'terlalu cepat' ? 'warning-entry' : 'present')">
                  {{ (selectedRecord.clock_out_status || 'tepat waktu').toUpperCase() }}
                </span>
                <span v-else class="status-badge absent">PENDING</span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <label>Planning (Rencana Kegiatan)</label>
            <div class="description-box">
              {{ selectedRecord.rencana_kegiatan || 'No description provided.' }}
            </div>
          </div>

          <div class="detail-section">
            <label>Work Report (Progress Kegiatan)</label>
            <div class="description-box">
              {{ selectedRecord.progress_kegiatan || 'No report provided.' }}
            </div>
          </div>

          <div class="detail-section">
            <label>Location</label>
            <div class="location-box">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              {{ formatLocation(selectedRecord) }}
            </div>
          </div>

          <div class="detail-section" v-if="selectedRecord.evidence">
            <label>Evidence</label>
            <div class="evidence-preview">
              <img :src="getImageUrl(selectedRecord.evidence)" alt="Evidence" class="preview-img" />
              <a :href="getImageUrl(selectedRecord.evidence)" target="_blank" class="btn-expand">
                Open Full Resource
              </a>
            </div>
          </div>
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
  background-color: var(--bg-app);
  padding: 16px 0;
  margin-bottom: 4px;
}

.header-titles h2 { margin: 0; font-size: 22px; font-weight: 700; color: var(--text-main); }
.header-titles p { margin: 4px 0 0 0; font-size: 14px; color: var(--text-muted); }

.btn-export {
  display: flex; align-items: center; gap: 8px;
  background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);
  padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-export:hover { background: var(--bg-input); }

/* 2. Filters Card */
.filters-card {
  background: var(--bg-card);
  padding: 20px 24px;
  border-radius: 16px;
  border: 1px solid var(--border-color);
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
  font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;
}

.input-wrapper {
  display: flex; align-items: center; gap: 8px;
  border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px;
  background: var(--bg-input);
  transition: all 0.2s ease;
}
.input-wrapper:focus-within {
  border-color: var(--accent-primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.input-wrapper .icon { color: var(--text-dim); flex-shrink: 0; }
.input-wrapper input { border: none; outline: none; font-size: 13px; width: 100%; color: var(--text-main); background: transparent; }
.input-wrapper input::placeholder { color: var(--text-dim); }

.select-wrapper select {
  border: none; outline: none; font-size: 13px; width: 100%; color: var(--text-main); background: transparent; cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0px center;
  padding-right: 24px;
}

.select-wrapper select option {
  background-color: var(--bg-card);
  color: var(--text-main);
}

.btn-apply {
  display: flex; align-items: center; gap: 8px; justify-content: center;
  background: var(--accent-primary); color: white; border: none;
  height: 40px; padding: 0 24px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
}
.btn-apply:hover { opacity: 0.9; }

/* 3. Stats Row */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.stat-card {
  background: var(--bg-card); padding: 20px; border-radius: 16px;
  border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.stat-label { font-size: 13px; color: var(--text-muted); }

.stat-value-row {
  display: flex; align-items: center; justify-content: space-between; margin-top: 8px;
}
.stat-value-row h2 { margin: 0; font-size: 26px; font-weight: 700; color: var(--text-main); }

.badge { font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px; }
.badge-trend-up { background: #DCFCE7; color: #10B981; }
.badge-trend-down { background: #FEF2F2; color: #EF4444; }
.badge-grey { background: var(--bg-input); color: var(--text-muted); }
.badge-blue { background: #EFF6FF; color: #3B82F6; }

/* 4. Main Table Card */
.main-card {
  background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color);
  box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;
}

.card-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid var(--border-color);
}
.header-left h3 { margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main); }
.header-left p { margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted); }

.header-right { display: flex; gap: 8px; }
.btn-icon {
  background: var(--bg-card); border: 1px solid var(--border-color); width: 36px; height: 36px;
  border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); cursor: pointer;
}
.btn-icon:hover { background: var(--bg-input); color: var(--text-main); }

/* Table Styles */
.table-container { 
  overflow-x: auto; 
  max-height: 600px; 
  border-radius: 0 0 16px 16px; 
}

.reports-table { 
  width: 100%; 
  border-collapse: collapse; 
  text-align: left; 
  table-layout: auto;
  min-width: 1200px; /* Force minimum width to enable scrolling instead of squishing */
}

.reports-table th {
  padding: 16px 20px; font-size: 11px; font-weight: 700; color: var(--text-dim);
  border-bottom: 1px solid var(--border-color); letter-spacing: 0.5px;
  position: sticky; top: 0; background-color: var(--bg-card); z-index: 10;
  white-space: nowrap;
}

.reports-table td {
  padding: 16px 20px; border-bottom: 1px solid var(--border-color); vertical-align: middle;
}

.td-date {
  min-width: 140px;
}

.td-wide {
  min-width: 180px;
  max-width: 250px;
}

.td-wide .regular-text {
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.4;
}

.td-location {
  min-width: 150px;
  max-width: 200px;
}

.td-location .regular-text {
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Column Contents */
.intern-details { 
  display: flex; align-items: center; gap: 12px; 
  min-width: 180px;
}
.avatar-placeholder {
  width: 36px; height: 36px; border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
}
.intern-details .name { margin: 0; font-size: 14px; font-weight: 600; color: var(--text-main); }
.intern-details .dept { margin: 0; font-size: 12px; color: var(--text-muted); }

.regular-text { font-size: 13px; color: var(--text-main); }

.status-badge {
  font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 99px;
  white-space: nowrap;
}
.status-badge.present { background: #f0fdf4; color: #16a34a; }
.status-badge.late-entry { background: #fef2f2; color: #dc2626; }
.status-badge.warning-entry { background: #fffbeb; color: #d97706; }
.status-badge.absent { background: #f8fafc; color: #64748b; }

.evidence-cell {
  display: flex;
}

.btn-evidence {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-main);
  text-decoration: none;
  transition: all 0.2s;
}

.btn-evidence:hover {
  background: var(--border-color);
  color: var(--accent-primary);
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background: var(--bg-input);
  color: var(--accent-primary);
  border-color: var(--accent-primary);
}

/* Modal Styling */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

.modal-card {
  background: var(--bg-card);
  width: 100%;
  max-width: 500px;
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.2);
  border: 1px solid var(--border-color);
  overflow: hidden;
  animation: slideUp 0.3s ease-out;
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-info h3 { margin: 0; font-size: 18px; font-weight: 700; color: var(--text-main); }
.header-info p { margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted); }

.btn-close {
  background: none; border: none; color: var(--text-muted); cursor: pointer;
  padding: 4px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
}
.btn-close:hover { background: var(--bg-input); color: var(--text-main); }

.modal-body {
  padding: 24px;
  max-height: 80vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.detail-section label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-dim);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.avatar-placeholder {
  width: 36px; height: 36px; border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
  background-color: #f1f5f9; color: #3b82f6; overflow: hidden;
}

.user-overview {
  display: flex;
  align-items: center;
  gap: 16px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--border-color);
}

.avatar-large {
  width: 50px; height: 50px;
  background: var(--bg-input);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; font-weight: 700; color: var(--accent-primary);
  overflow: hidden;
}

.avatar-img-fit {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.intern-details .name { margin: 0; font-size: 14px; font-weight: 600; color: var(--text-main); }

.user-meta h4 { margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main); }
.user-meta p { margin: 2px 0; font-size: 13px; color: var(--text-muted); }
.project-tag { font-weight: 600; color: var(--accent-primary) !important; font-size: 12px !important; }

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.grid-item .value-row {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.grid-item .time {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-main);
}

.description-box {
  background: var(--bg-input);
  padding: 12px;
  border-radius: 12px;
  font-size: 14px;
  color: var(--text-main);
  line-height: 1.6;
  white-space: pre-wrap;
}

.location-box {
  display: flex;
  gap: 8px;
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.4;
}

.location-box svg { flex-shrink: 0; margin-top: 2px; color: var(--accent-primary); }

.evidence-preview {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.preview-img {
  width: 100%;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  max-height: 250px;
  object-fit: cover;
}

.btn-expand {
  display: block;
  text-align: center;
  padding: 10px;
  background: var(--accent-primary);
  color: white;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* Pagination */
.pagination-footer {
  display: flex; justify-content: space-between; align-items: center; padding: 16px 24px;
}
.pagination-footer p { margin: 0; font-size: 13px; color: var(--text-muted); }
.pagination { display: flex; gap: 8px; }
.page-btn {
  padding: 6px 16px; border: 1px solid var(--border-color); background: var(--bg-card); border-radius: 6px;
  font-size: 13px; font-weight: 500; color: var(--text-main); cursor: pointer;
}
.page-btn:hover { background: var(--bg-input); }
</style>