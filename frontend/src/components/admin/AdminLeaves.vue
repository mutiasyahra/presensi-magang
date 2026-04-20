<script setup>
import { computed } from 'vue';

const props = defineProps(['leaveRequests']);
const emit = defineEmits(['approve']);

const pendingCount = computed(() => props.leaveRequests?.filter(l => l.status === 'pending').length || 0);

const isToday = (dateString) => {
  if (!dateString) return false;
  const d = new Date(dateString);
  const today = new Date();
  return d.getDate() === today.getDate() && d.getMonth() === today.getMonth() && d.getFullYear() === today.getFullYear();
};

const approvedTotalCount = computed(() => props.leaveRequests?.filter(l => l.status === 'approved').length || 0);
const rejectedTotalCount = computed(() => props.leaveRequests?.filter(l => l.status === 'rejected').length || 0);

const totalLeaveRate = computed(() => {
  if (!props.leaveRequests?.length) return '0%';
  const processed = props.leaveRequests.filter(l => l.status !== 'pending').length;
  return Math.round((processed / props.leaveRequests.length) * 100) + '%';
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", { day: "numeric", month: "short", year: "numeric" });
};

const getDuration = (start, end) => {
  if (!start || !end) return '1 Day';
  const s = new Date(start);
  const e = new Date(end);
  const diffTime = Math.abs(e - s);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
  return diffDays + (diffDays > 1 ? ' Days' : ' Day');
};

const getFileUrl = (filePath) => {
  if (!filePath) return '#';
  if (filePath.startsWith('http')) return filePath;
  const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace(/\/api$/, '') || 'http://127.0.0.1:8000';
  return `${baseUrl}/storage/${filePath}`;
};
</script>

<template>
  <div class="leave-approval-container">

    <div class="page-header">
      <h2>Leave & Permission Requests</h2>
      <div class="header-actions">
        <button class="btn-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </button>
        <button class="btn-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
      </div>
    </div>
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">PENDING</span>
        <div class="stat-value-row">
          <h2>{{ pendingCount < 10 && pendingCount > 0 ? '0' + pendingCount : pendingCount }}</h2>
          <span class="badge badge-blue">Awaiting Action</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">TOTAL APPROVED</span>
        <div class="stat-value-row">
          <h2>{{ approvedTotalCount < 10 && approvedTotalCount > 0 ? '0' + approvedTotalCount : approvedTotalCount }}</h2>
          <span class="badge badge-green">Cumulative</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">TOTAL REJECTED</span>
        <div class="stat-value-row">
          <h2>{{ rejectedTotalCount < 10 && rejectedTotalCount > 0 ? '0' + rejectedTotalCount : rejectedTotalCount }}</h2>
          <span class="badge badge-red">Overall</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">COMPLETION RATE</span>
        <div class="stat-value-row">
          <h2>{{ totalLeaveRate }}</h2>
          <span class="badge badge-grey">Processed</span>
        </div>
      </div>
    </div>

    <div class="main-card">
      <div class="card-header">
        <div class="header-left">
          <div class="blue-indicator"></div>
          <h3>Active Submissions</h3>
        </div>
        <div class="header-right">
          <div class="search-box">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" placeholder="Search intern name..." />
          </div>
          <button class="btn-filter">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
          </button>
        </div>
      </div>

      <div class="table-container">
        <table class="leaves-table">
          <thead>
            <tr>
              <th>INTERN DETAILS</th>
              <th>CATEGORY</th>
              <th>DURATION</th>
              <th>REASON & EVIDENCE</th>
              <th>STATUS</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="leave in leaveRequests" :key="leave.id">
              
              <td>
                <div class="intern-details">
                  <div class="avatar-placeholder">{{ leave.user?.name?.charAt(0) || 'U' }}</div>
                  <div class="info">
                    <p class="name">{{ leave.user?.name || 'Unknown Intern' }}</p>
                    <p class="id">ID: {{ leave.user?.intern?.intern_id || leave.user?.id || '202308000' }}</p>
                  </div>
                </div>
              </td>

              <td>
                <span class="category-badge" :class="leave.type?.toLowerCase()">
                  {{ leave.type }}
                </span>
              </td>

              <td>
                <div class="duration-info">
                  <p class="dates">{{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}</p>
                  <p class="days">{{ getDuration(leave.start_date, leave.end_date) }}</p>
                </div>
              </td>

              <td>
                <div class="reason-evidence">
                  <p class="reason">"{{ leave.reason }}"</p>
                  <a v-if="leave.file" :href="getFileUrl(leave.file)" target="_blank" class="evidence-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    View Document
                  </a>
                  <p v-else class="no-evidence">No evidence provided</p>
                </div>
              </td>

              <td>
                <span class="badge" :class="{
                  'badge-blue': leave.status === 'pending',
                  'badge-green': leave.status === 'approved',
                  'badge-red': leave.status === 'rejected'
                }">
                  {{ leave.status.toUpperCase() }}
                </span>
              </td>

              <td>
                <div class="action-buttons" v-if="leave.status === 'pending'">
                  <button class="btn-approve" @click="$emit('approve', leave.id, 'approved')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Approve
                  </button>
                  <button class="btn-reject" @click="$emit('approve', leave.id, 'rejected')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                  </button>
                </div>
                <div v-else class="action-done">
                  <span class="text-muted">No actions needed</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination-footer">
        <p>Showing {{ leaveRequests.length }} total requests</p>
        <div class="pagination">
          <button class="page-btn">&lt;</button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">&gt;</button>
        </div>
      </div>
    </div>

    <div class="info-row">
      <div class="info-box box-blue">
        <div class="info-icon">i</div>
        <div class="info-content">
          <h4>Approval Policy</h4>
          <p>Review evidence carefully. For medical leave longer than 2 days, a valid doctor's note is required as per the internship agreement Section 4.B.</p>
        </div>
      </div>
      <div class="info-box box-yellow">
        <div class="info-icon warning-icon">!</div>
        <div class="info-content">
          <h4>Batch Quota Warning</h4>
          <p>Batch 'October Alpha' currently has 15% of staff on leave. Approving more requests for this week may affect production schedules.</p>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
/* Styling & Sticky untuk Page Header Utama */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  
  position: sticky;
  top: 0;
  z-index: 30;
  background-color: var(--bg-app);
  padding: 16px 0;
  margin-bottom: 8px;
}

.page-header h2 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  color: var(--text-main);
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: var(--bg-input);
  color: var(--text-main);
}

.leave-approval-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* 1. Stats Row */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.stat-card {
  background: var(--bg-card);
  padding: 20px;
  border-radius: 16px;
  border: 1px solid var(--border-color);
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.stat-label {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted);
  letter-spacing: 0.5px;
}

.stat-value-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 12px;
}

.stat-value-row h2 {
  margin: 0;
  font-size: 28px;
  color: var(--text-main);
}

.badge {
  font-size: 11px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
}
.badge-blue { background: #EFF6FF; color: #3B82F6; }
.badge-green { background: #DCFCE7; color: #10B981; }
.badge-red { background: #FEF2F2; color: #EF4444; }
.badge-grey { background: #F1F5F9; color: #64748B; }

/* 2. Main Card & Header */
.main-card {
  background: var(--bg-card);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.blue-indicator {
  width: 6px;
  height: 24px;
  background: var(--accent-primary);
  border-radius: 4px;
}

.header-left h3 {
  margin: 0;
  font-size: 18px;
  color: var(--text-main);
}

.header-right {
  display: flex;
  gap: 12px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-input);
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  width: 250px;
}
.search-box input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  width: 100%;
}
.search-box .icon { color: var(--text-dim); }

.btn-filter {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-muted);
  cursor: pointer;
}

/* Table Styles */
.table-container {
  overflow-x: auto;
}

.leaves-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.leaves-table th {
  padding: 16px 24px;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-dim);
  border-bottom: 1px solid var(--border-color);
  letter-spacing: 0.5px;
}

.leaves-table td {
  padding: 16px 24px;
  border-bottom: 1px solid var(--border-color);
  vertical-align: middle;
}

/* Column Contents */
.intern-details {
  display: flex;
  align-items: center;
  gap: 12px;
}
.avatar-placeholder {
  width: 36px; height: 36px;
  background: var(--bg-input); color: var(--accent-primary);
  border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-weight: bold;
}
.intern-details .name { margin: 0; font-size: 14px; font-weight: 600; color: var(--text-main); }
.intern-details .id { margin: 0; font-size: 12px; color: var(--text-muted); }

.category-badge {
  font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px; text-transform: uppercase;
}
.category-badge.sick, .category-badge.sick\ leave { background: #FEF3C7; color: #D97706; }
.category-badge.urgent, .category-badge.personal { background: #EFF6FF; color: #3B82F6; }

.duration-info .dates { margin: 0; font-size: 13px; font-weight: 600; color: var(--text-main); }
.duration-info .days { margin: 0; font-size: 12px; color: var(--text-muted); }

.reason-evidence .reason { margin: 0 0 4px 0; font-size: 13px; font-style: italic; color: var(--text-muted); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.evidence-link { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--accent-primary); text-decoration: none; font-weight: 500; }
.no-evidence { margin: 0; font-size: 12px; color: var(--text-dim); font-style: italic; }

.action-buttons {
  display: flex;
  gap: 8px;
}
.btn-approve {
  display: flex; align-items: center; gap: 6px;
  background: var(--accent-primary); color: white; border: none;
  padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
}
.btn-reject {
  display: flex; align-items: center; justify-content: center;
  background: var(--bg-card); color: var(--accent-danger); border: 1px solid var(--accent-danger);
  width: 36px; height: 36px; border-radius: 8px; cursor: pointer;
}

/* Pagination */
.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
}
.pagination-footer p { margin: 0; font-size: 13px; color: var(--text-muted); }
.pagination { display: flex; gap: 4px; }
.page-btn {
  width: 32px; height: 32px; border: 1px solid var(--border-color); background: var(--bg-card); border-radius: 6px;
  color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.page-btn.active { background: var(--accent-primary); color: white; border-color: var(--accent-primary); }

/* 3. Bottom Info Box */
.info-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.info-box {
  display: flex; gap: 16px; padding: 20px; border-radius: 12px;
}
.box-blue { background: #EFF6FF; }
.box-yellow { background: #FEFCE8; border: 1px solid #FEF08A; }

.info-icon {
  width: 24px; height: 24px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: bold; font-size: 12px; color: white; background: #3B82F6; flex-shrink: 0;
}
.warning-icon { background: #F59E0B; }

.info-content h4 { margin: 0 0 6px 0; font-size: 14px; color: var(--text-main); }
.info-content p { margin: 0; font-size: 13px; color: var(--text-muted); line-height: 1.5; }
</style>