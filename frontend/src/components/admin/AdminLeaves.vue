<script setup>
defineProps(['leaveRequests']);
const emit = defineEmits(['approve']);
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
          <h2>12</h2>
          <span class="badge badge-blue">+3 today</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">APPROVED TODAY</span>
        <div class="stat-value-row">
          <h2>08</h2>
          <span class="badge badge-green">Good pace</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">REJECTED TODAY</span>
        <div class="stat-value-row">
          <h2>02</h2>
          <span class="badge badge-red">Low quota</span>
        </div>
      </div>
      <div class="stat-card">
        <span class="stat-label">TOTAL LEAVE RATE</span>
        <div class="stat-value-row">
          <h2>4.2%</h2>
          <span class="badge badge-grey">Avg/Intern</span>
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
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="leave in leaveRequests.filter(l => l.status === 'pending')" :key="leave.id">
              
              <td>
                <div class="intern-details">
                  <div class="avatar-placeholder">{{ leave.user?.name?.charAt(0) || 'U' }}</div>
                  <div class="info">
                    <p class="name">{{ leave.user?.name || 'Unknown Intern' }}</p>
                    <p class="id">ID: {{ leave.user?.id || '202308000' }}</p>
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
                  <p class="dates">{{ leave.date }}</p>
                  <p class="days">{{ leave.duration || '1 Working Day' }}</p>
                </div>
              </td>

              <td>
                <div class="reason-evidence">
                  <p class="reason">"{{ leave.reason }}"</p>
                  <a v-if="leave.attachment" href="#" class="evidence-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    {{ leave.attachment }}
                  </a>
                  <p v-else class="no-evidence">No evidence provided</p>
                </div>
              </td>

              <td>
                <div class="action-buttons">
                  <button class="btn-approve" @click="$emit('approve', leave.id, 'approved')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Approve
                  </button>
                  <button class="btn-reject" @click="$emit('approve', leave.id, 'rejected')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination-footer">
        <p>Showing {{ leaveRequests.filter(l => l.status === 'pending').length }} pending requests</p>
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
  
  /* Mantra Sticky-nya */
  position: sticky;
  top: 0;
  z-index: 30; /* Harus lebih tinggi dari z-index tabel agar tidak tertimpa */
  background-color: #F8FAFC; /* Pastikan warnanya sama dengan background halamanmu, biasanya F8FAFC atau putih */
  padding: 16px 0;
  margin-bottom: 8px;
}

.page-header h2 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  color: #0F172A;
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
  color: #64748B;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-icon:hover {
  background: #E2E8F0;
  color: #0F172A;
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
  background: white;
  padding: 20px;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.stat-label {
  font-size: 12px;
  font-weight: 700;
  color: #64748B;
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
  color: #0F172A;
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
  background: white;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #E2E8F0;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.blue-indicator {
  width: 6px;
  height: 24px;
  background: #3B82F6;
  border-radius: 4px;
}

.header-left h3 {
  margin: 0;
  font-size: 18px;
  color: #0F172A;
}

.header-right {
  display: flex;
  gap: 12px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #F8FAFC;
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid #E2E8F0;
  width: 250px;
}
.search-box input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  width: 100%;
}
.search-box .icon { color: #94A3B8; }

.btn-filter {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 8px;
  border: 1px solid #E2E8F0;
  background: white;
  color: #64748B;
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
  color: #94A3B8;
  border-bottom: 1px solid #E2E8F0;
  letter-spacing: 0.5px;
}

.leaves-table td {
  padding: 16px 24px;
  border-bottom: 1px solid #E2E8F0;
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
  background: #F1F5F9; color: #3B82F6;
  border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-weight: bold;
}
.intern-details .name { margin: 0; font-size: 14px; font-weight: 600; color: #0F172A; }
.intern-details .id { margin: 0; font-size: 12px; color: #64748B; }

.category-badge {
  font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px; text-transform: uppercase;
}
.category-badge.sick, .category-badge.sick\ leave { background: #FEF3C7; color: #D97706; }
.category-badge.urgent, .category-badge.personal { background: #EFF6FF; color: #3B82F6; }

.duration-info .dates { margin: 0; font-size: 13px; font-weight: 600; color: #0F172A; }
.duration-info .days { margin: 0; font-size: 12px; color: #64748B; }

.reason-evidence .reason { margin: 0 0 4px 0; font-size: 13px; font-style: italic; color: #475569; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.evidence-link { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #3B82F6; text-decoration: none; font-weight: 500; }
.no-evidence { margin: 0; font-size: 12px; color: #94A3B8; font-style: italic; }

.action-buttons {
  display: flex;
  gap: 8px;
}
.btn-approve {
  display: flex; align-items: center; gap: 6px;
  background: #3B82F6; color: white; border: none;
  padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
}
.btn-reject {
  display: flex; align-items: center; justify-content: center;
  background: white; color: #EF4444; border: 1px solid #FECACA;
  width: 36px; height: 36px; border-radius: 8px; cursor: pointer;
}

/* Pagination */
.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
}
.pagination-footer p { margin: 0; font-size: 13px; color: #64748B; }
.pagination { display: flex; gap: 4px; }
.page-btn {
  width: 32px; height: 32px; border: 1px solid #E2E8F0; background: white; border-radius: 6px;
  color: #64748B; cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.page-btn.active { background: #3B82F6; color: white; border-color: #3B82F6; }

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

.info-content h4 { margin: 0 0 6px 0; font-size: 14px; color: #0F172A; }
.info-content p { margin: 0; font-size: 13px; color: #475569; line-height: 1.5; }
</style>