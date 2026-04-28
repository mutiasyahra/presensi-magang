<script setup>
import { ref, onMounted, computed } from "vue";
import api from "../../api/axios.js";
import VerificationScreen from "./VerificationScreen.vue";

const isVerifying = ref(false);
const activeView = ref("clock-in");

const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('data:') || path.startsWith('http')) return path;
  const baseUrl = "http://localhost:8000"; // Fallback to localhost if env not set
  const apiBase = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api";
  const realBase = apiBase.replace("/api", "");
  return `${realBase}/storage/${path}`;
};

const attendanceCards = ref([]);
const selectedAttendance = ref(null);

const fetchAttendances = async () => {
  try {
    const response = await api.get("/attendances");

    // Group by user_id and take the latest attendance
    const grouped = response.data.data.reduce((acc, item) => {
      if (!acc[item.user_id]) {
        acc[item.user_id] = item;
      }
      return acc;
    }, {});

    attendanceCards.value = Object.values(grouped).map((item) => ({
      ...item,
      name: item.user?.name || "Unknown Intern",
      intern_id: item.user?.intern?.intern_id || "-",
      role: "Intern",
      photo: item.user?.profile_photo || null,
      statusDisplay: item.status ? item.status.toUpperCase() : "UNKNOWN",
      periodDisplay: item.user?.intern?.start_date && item.user?.intern?.end_date
        ? `${new Date(item.user.intern.start_date).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
          })} - ${new Date(item.user.intern.end_date).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
          })}`
        : "No period set",
      locationInfo: item.clock_in_lat
        ? `${item.clock_in_lat.substring(0, 8)}, ${item.clock_in_long.substring(
            0,
            8
          )}`
        : "Remote",
    }));
  } catch (error) {
    console.error("Error fetching attendances:", error);
  }
};

onMounted(() => {
  fetchAttendances();
});

const openVerification = (record) => {
  selectedAttendance.value = record;
  isVerifying.value = true;
};

// Fungsi helper untuk warna status/badge
const getStatusClass = (status) => {
  return status === "ON TIME" ? "badge-success" : "badge-warning";
};

const stats = computed(() => {
  const total = attendanceCards.value.length;
  if (total === 0) {
    return {
      onTime: 0, onTimePct: '0%',
      late: 0, latePct: '0%',
      office: 0, officePct: '0%',
      remote: 0, remotePct: '0%',
      active: 0,
      pending: 0
    };
  }

  const onTime = attendanceCards.value.filter(c => c.clock_in_status === 'tepat waktu').length;
  const late = attendanceCards.value.filter(c => c.clock_in_status === 'terlambat').length;
  const office = attendanceCards.value.filter(c => c.clock_in_lat).length;
  const remote = total - office;
  const pending = attendanceCards.value.filter(c => !c.is_verified).length;

  return {
    onTime, onTimePct: Math.round((onTime / total) * 100) + '%',
    late, latePct: Math.round((late / total) * 100) + '%',
    office, officePct: Math.round((office / total) * 100) + '%',
    remote, remotePct: Math.round((remote / total) * 100) + '%',
    active: total,
    pending
  };
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = 9;
const totalPages = computed(() => Math.ceil(attendanceCards.value.length / itemsPerPage));
const paginatedCards = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return attendanceCards.value.slice(start, start + itemsPerPage);
});
</script>

<template>
  <div class="monitor-wrapper">
    <div class="monitor-container" v-if="!isVerifying">
      <div class="page-header">
        <h1 class="main-title">Daily Attendance Monitor</h1>
        <div class="header-actions">
          <div class="search-box">
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
            <input type="text" placeholder="Search intern name..." />
          </div>
          <button class="btn-icon">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
          </button>
          <button class="btn-icon">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
          </button>
        </div>
      </div>

      <div class="controls-section">
        <div class="left-stats-placeholder">
          <!-- Toggles removed based on user request -->
        </div>

        <div class="right-controls">
          <div class="status-indicators">
            <span class="indicator green"
              ><span class="dot"></span> {{ stats.active }} Active</span
            >
            <span class="indicator grey">{{ stats.pending }} Pending</span>
          </div>
          <button class="btn-filter">
            <svg
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
              <line x1="4" y1="21" x2="4" y2="14"></line>
              <line x1="4" y1="10" x2="4" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12" y2="3"></line>
              <line x1="20" y1="21" x2="20" y2="16"></line>
              <line x1="20" y1="12" x2="20" y2="3"></line>
              <line x1="1" y1="14" x2="7" y2="14"></line>
              <line x1="9" y1="8" x2="15" y2="8"></line>
              <line x1="17" y1="16" x2="23" y2="16"></line>
            </svg>
            Filters
          </button>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <p class="stat-label">ON TIME</p>
          <div class="stat-row">
            <h2 class="stat-value">{{ stats.onTime }}</h2>
            <span class="stat-percent text-green">{{ stats.onTimePct }}</span>
          </div>
        </div>
        <div class="stat-card">
          <p class="stat-label">LATE ARRIVALS</p>
          <div class="stat-row">
            <h2 class="stat-value">{{ stats.late }}</h2>
            <span class="stat-percent text-orange">{{ stats.latePct }}</span>
          </div>
        </div>
        <div class="stat-card">
          <p class="stat-label">OFFICE</p>
          <div class="stat-row">
            <h2 class="stat-value">{{ stats.office }}</h2>
            <span class="stat-percent text-blue">{{ stats.officePct }}</span>
          </div>
        </div>
        <div class="stat-card">
          <p class="stat-label">REMOTE</p>
          <div class="stat-row">
            <h2 class="stat-value">{{ stats.remote }}</h2>
            <span class="stat-percent text-grey">{{ stats.remotePct }}</span>
          </div>
        </div>
      </div>

      <div class="cards-grid">
        <div
          class="attendance-card"
          v-for="intern in paginatedCards"
          :key="intern.id"
        >
          <div class="card-header">
            <div class="profile-area">
              <div class="avatar-wrapper">
                <img v-if="intern.photo" :src="getImageUrl(intern.photo)" class="avatar-img-fit" />
                <div v-else class="avatar-initial-box">
                  {{ (intern.name || 'U').split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) }}
                </div>
                <span class="status-dot"></span>
              </div>
              <div class="info">
                <div class="name-row">
                  <h3 class="name">{{ intern.name }}</h3>
                </div>
                <p class="role">{{ intern.intern_id }}</p>
              </div>
            </div>
          </div>

          <div class="time-row">
            <svg
              class="icon text-blue"
              xmlns="http://www.w3.org/2000/svg"
              width="14"
              height="14"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span class="time-text">{{ intern.periodDisplay }}</span>
          </div>

          <div class="divider"></div>
          <div class="location-row">
            <div class="loc-left">
              <svg
                class="icon"
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
              <span class="loc-text">{{ intern.locationInfo }}</span>
            </div>
            <span
              class="status-pill"
              :class="getStatusClass(intern.statusDisplay)"
              >{{ intern.statusDisplay }}</span
            >
          </div>

          <div class="card-actions">
            <button
              class="btn-primary"
              :class="{ 'verify-mode': !intern.is_verified }"
              @click="openVerification(intern)"
            >
              View Timeline
            </button>
            <button class="btn-more">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <circle cx="12" cy="12" r="1"></circle>
                <circle cx="19" cy="12" r="1"></circle>
                <circle cx="5" cy="12" r="1"></circle>
              </svg>
            </button>
          </div>
        </div>

        <div class="attendance-card border-dashed">
          <div class="manual-entry-content">
            <div class="add-icon-wrapper">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
              </svg>
            </div>
            <h4>Register New Attendance Entry</h4>
            <p>Add manual check-in for exceptional cases</p>
          </div>
        </div>
      </div>

      <div class="pagination">
        <p class="pagination-info">Showing {{ attendanceCards.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}–{{ Math.min(currentPage * itemsPerPage, attendanceCards.length) }} of {{ attendanceCards.length }} Interns</p>
        <div class="pagination-controls" v-if="totalPages > 1">
          <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
          </button>
          <button
            v-for="p in totalPages"
            :key="p"
            class="page-btn"
            :class="{ active: currentPage === p }"
            @click="currentPage = p"
          >{{ p }}</button>
          <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </button>
        </div>
      </div>
    </div>

    <VerificationScreen
      v-else
      :attendance="selectedAttendance"
      @close="isVerifying = false"
      @refresh="
        fetchAttendances;
        isVerifying = false;
      "
    />
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
  background-color: var(--bg-app);
  padding-bottom: 16px;
  padding-top: 16px;
}

.main-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-main);
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
  background: var(--bg-card);
  padding: 8px 16px;
  border-radius: 20px;
  border: 1px solid var(--border-color);
  width: 260px;
}
.search-box .icon {
  color: var(--text-dim);
}
.search-box input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 14px;
  width: 100%;
  color: var(--text-main);
}
.search-box input::placeholder {
  color: var(--text-dim);
}

.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  color: var(--text-muted);
  cursor: pointer;
  transition: background 0.2s;
}
.btn-icon:hover {
  background: var(--bg-input);
  color: var(--text-main);
}

/* Controls Section */
.controls-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.view-toggles {
  display: flex;
  background: var(--bg-input);
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
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
}
.toggle-btn.active {
  background: var(--bg-card);
  color: var(--text-main);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.right-controls {
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-indicators {
  display: flex;
  gap: 12px;
}
.indicator {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}
.indicator.green {
  background: #dcfce7;
  color: #166534;
}
.indicator.grey {
  background: var(--bg-input);
  color: var(--text-muted);
}
.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
}

.btn-filter {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
  cursor: pointer;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stat-card {
  background: var(--bg-card);
  padding: 20px;
  border-radius: 16px;
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
}

.stat-label {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted);
  margin: 0 0 12px 0;
}
.stat-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.stat-value {
  font-size: 32px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0;
  line-height: 1;
}
.stat-percent {
  font-size: 13px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 12px;
}

.text-green {
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}
.text-orange {
  color: #f59e0b;
  background: rgba(245, 158, 11, 0.1);
}
.text-blue {
  color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);
}
.text-grey {
  color: #64748b;
  background: rgba(100, 116, 139, 0.1);
}

/* Cards Grid */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.attendance-card {
  background: var(--bg-card);
  padding: 24px;
  border-radius: 16px;
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.border-dashed {
  border: 2px dashed var(--border-color);
  background: transparent;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: border-color 0.2s;
  grid-column: 1 / -1;
  min-height: 120px;
}
.border-dashed:hover {
  border-color: var(--text-dim);
}

.manual-entry-content {
  text-align: center;
}
.add-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--bg-input);
  color: var(--text-dim);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}
.manual-entry-content h4 {
  margin: 0 0 4px 0;
  font-size: 15px;
  color: var(--text-muted);
}
.manual-entry-content p {
  margin: 0;
  font-size: 13px;
  color: var(--text-dim);
}

/* Card Content Details */
.profile-area {
  display: flex;
  gap: 12px;
}
.avatar-wrapper {
  position: relative;
  width: 48px;
  height: 48px;
}
.avatar-wrapper img.avatar-img-fit {
  width: 100%;
  height: 100%;
  border-radius: 12px;
  object-fit: cover;
}
.avatar-initial-box {
  width: 100%;
  height: 100%;
  border-radius: 12px;
  background-color: var(--bg-input);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 700;
  color: var(--text-main);
}
.status-dot {
  position: absolute;
  bottom: -4px;
  right: -4px;
  width: 14px;
  height: 14px;
  background: #10b981;
  border: 2px solid white;
  border-radius: 50%;
}

.info {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 4px;
}
.name-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.name {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: var(--text-main);
}

.verify-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
}
.verify-badge.verified {
  background: #eff6ff;
  color: #3b82f6;
}
.verify-badge.pending {
  background: #fef3c7;
  color: #f59e0b;
}

.role {
  margin: 0;
  font-size: 13px;
  color: var(--text-muted);
}

.time-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.time-text {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
}

.divider {
  height: 1px;
  background: #f1f5f9;
}

.location-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.loc-left {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-muted);
}
.loc-text {
  font-size: 13px;
}

.status-pill {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 4px;
}
.badge-success {
  background: #dcfce7;
  color: #166534;
}
.badge-warning {
  background: #fef3c7;
  color: #b45309;
}

/* Card Actions */
.card-actions {
  display: flex;
  gap: 12px;
  margin-top: 4px;
}
.btn-primary {
  flex: 1;
  padding: 10px;
  border-radius: 8px;
  border: none;
  background: var(--accent-primary);
  color: white;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-primary:hover {
  opacity: 0.9;
}
.btn-primary.verify-mode {
  background: #3b82f6;
} /* Bisa diubah warnanya jika verify mode beda */

.btn-more {
  width: 40px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  border-radius: 8px;
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.btn-more:hover {
  background: var(--bg-input);
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid var(--border-color);
}
.pagination-info {
  margin: 0;
  font-size: 14px;
  color: var(--text-muted);
}
.pagination-controls {
  display: flex;
  gap: 4px;
}
.page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}
.page-btn:hover {
  background: var(--bg-input);
}
.page-btn.active {
  background: var(--accent-primary);
  color: white;
  font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .cards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 768px) {
  .controls-section {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  .cards-grid {
    grid-template-columns: 1fr;
  }
}
</style>
