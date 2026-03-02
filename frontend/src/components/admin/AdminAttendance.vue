<script setup>
import { ref } from 'vue'; // Tambahkan import ref ini
// Variabel untuk mengontrol Modal (true = muncul, false = sembunyi)
const showAddModal = ref(false);

// Dummy data untuk menyesuaikan dengan tampilan desain Figma
const internsData = [
  { id: 'INT-2023-084', name: 'Sarah Jenkins', department: 'Product Design', mentor: 'Michael Scott', attendance: 96, status: 'Active', avatar: '../../assets/profile.png' },
  { id: 'INT-2023-112', name: 'James Chen', department: 'Engineering', mentor: 'Pam Beesly', attendance: 78, status: 'Active', avatar: '../../assets/profile_m.png' },
  { id: 'INT-2023-095', name: 'Elena Rodriguez', department: 'Marketing', mentor: 'Jim Halpert', attendance: 0, status: 'Inactive', avatar: '../../assets/profile_f.png' },
];

// Fungsi untuk menentukan warna progress bar berdasarkan persentase
const getProgressColor = (value) => {
  if (value >= 90) return 'bg-green';
  if (value > 0) return 'bg-orange';
  return 'bg-grey';
};
</script>

<template>
  <div class="directory-container">
    <div class="page-header">
      <div class="header-titles">
        <h1 class="main-title">Intern Directory</h1>
        <p class="sub-title">Manage and track internship progress across all departments.</p>
      </div>
      <div class="header-actions">
        <button class="btn-export">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          Export
        </button>
        <button class="btn-add" @click="showAddModal = true">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          Add Intern
        </button>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <p class="stat-label">TOTAL INTERNS</p>
        <div class="stat-value-group">
          <h2 class="stat-value">128</h2>
          <span class="stat-trend trend-up">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
            12%
          </span>
        </div>
      </div>
      
      <div class="stat-card">
        <p class="stat-label">ACTIVE ATTENDANCE</p>
        <div class="stat-value-group align-end">
          <h2 class="stat-value">94.8%</h2>
          <span class="stat-subtext">vs 90%<br>target</span>
        </div>
      </div>

      <div class="stat-card">
        <p class="stat-label">PENDING LEAVES</p>
        <div class="stat-value-group align-end">
          <h2 class="stat-value highlight-orange">12</h2>
          <span class="stat-subtext">Requires action</span>
        </div>
      </div>

      <div class="stat-card">
        <p class="stat-label">DEPARTMENTS</p>
        <div class="stat-value-group align-end">
          <h2 class="stat-value">06</h2>
          <span class="stat-subtext">Active units</span>
        </div>
      </div>
    </div>

    <div class="filter-section">
      <div class="search-box">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" placeholder="Search by name, ID, or mentor..." />
      </div>
      
      <div class="filter-actions">
        <div class="dropdown">
          <span>All Departments</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <div class="dropdown">
          <span>All Batches</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <button class="btn-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="4" x2="14" y2="4"></line><line x1="10" y1="4" x2="3" y2="4"></line><line x1="21" y1="12" x2="12" y2="12"></line><line x1="8" y1="12" x2="3" y2="12"></line><line x1="21" y1="20" x2="16" y2="20"></line><line x1="12" y1="20" x2="3" y2="20"></line><line x1="14" y1="2" x2="14" y2="6"></line><line x1="8" y1="10" x2="8" y2="14"></line><line x1="16" y1="18" x2="16" y2="22"></line></svg>
        </button>
      </div>
    </div>

    <div class="table-container">
      <table class="directory-table">
        <thead>
          <tr>
            <th>INTERN NAME</th>
            <th>DEPARTMENT</th>
            <th>MENTOR</th>
            <th>ATTENDANCE %</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="intern in internsData" :key="intern.id">
            <td>
              <div class="intern-profile">
                <div class="avatar">
                  <span v-if="!intern.avatar">{{ intern.name.charAt(0) }}</span>
                  <img v-else src="https://i.pravatar.cc/150?img=1" alt="avatar" />
                </div>
                <div class="intern-details">
                  <p class="intern-name">{{ intern.name }}</p>
                  <p class="intern-id">ID: {{ intern.id }}</p>
                </div>
              </div>
            </td>
            <td class="td-text">{{ intern.department }}</td>
            <td class="td-text">{{ intern.mentor }}</td>
            <td>
              <div class="attendance-cell">
                <div class="progress-track">
                  <div class="progress-fill" :class="getProgressColor(intern.attendance)" :style="{ width: intern.attendance + '%' }"></div>
                </div>
                <span class="attendance-text">{{ intern.attendance }}%</span>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="intern.status.toLowerCase()">{{ intern.status }}</span>
            </td>
            <td>
              <div class="action-buttons">
                <button class="action-btn" title="View">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
                <button class="action-btn" title="Edit">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination">
        <p class="pagination-info">Showing 1 to {{ internsData.length }} of 128 interns</p>
        <div class="pagination-controls">
          <button class="page-btn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg></button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <span class="page-dots">...</span>
          <button class="page-btn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></button>
        </div>
      </div>
    </div>
  </div>
  <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Add New Intern</h2>
            <p class="modal-subtitle">Fill in the details to register a new intern to the system.</p>
          </div>
          <button class="btn-close" @click="showAddModal = false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </div>

        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" placeholder="e.g. Johnathan Doe" />
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="email" placeholder="john@company.com" />
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" placeholder="+1 (555) 000-0000" />
            </div>
            <div class="form-group">
              <label>Intern ID</label>
              <input type="text" placeholder="INT-2024-XXX" />
            </div>
            <div class="form-group">
              <label>Department</label>
              <select>
                <option value="" disabled selected>Select Department</option>
                <option>Product Design</option>
                <option>Engineering</option>
                <option>Marketing</option>
              </select>
            </div>
            <div class="form-group">
              <label>Mentor</label>
              <select>
                <option value="" disabled selected>Select Mentor</option>
                <option>Michael Scott</option>
                <option>Pam Beesly</option>
                <option>Jim Halpert</option>
              </select>
            </div>
            <div class="form-group">
              <label>Batch Number</label>
              <input type="text" placeholder="Batch 2024 - Q1" />
            </div>
            <div class="empty-spacer"></div> <div class="form-group">
              <label>Start Date</label>
              <input type="date" />
            </div>
            <div class="form-group">
              <label>End Date</label>
              <input type="date" />
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancel" @click="showAddModal = false">Cancel</button>
          <button class="btn-save">Save Intern</button>
        </div>
      </div>
    </div>
</template>

<style scoped>
/* Main Layout */
.directory-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;

  /* --- Efek Sticky & Glassmorphism --- */
  position: sticky;
  top: 0;
  z-index: 100;
  padding: 20px 0;
  margin-top: -20px; /* Menyeimbangkan padding atas agar sejajar */
  background: rgba(248, 250, 252, 0.8); /* Sama seperti Dashboard */
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.5);
}

.main-title {
  font-size: 28px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 4px 0;
  letter-spacing: -0.5px;
}

.sub-title {
  font-size: 14px;
  color: #64748B;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-export {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 16px;
  background: white;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-export:hover { background: #F8FAFC; border-color: #CBD5E1; }

.btn-add {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 16px;
  background: #3B82F6;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  color: white;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-add:hover { background: #2563EB; }

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stat-card {
  background: white;
  padding: 20px 24px;
  border-radius: 16px;
  border: 1px solid #F1F5F9;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-label {
  font-size: 12px;
  font-weight: 700;
  color: #64748B;
  letter-spacing: 0.5px;
  margin: 0;
}

.stat-value-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.align-end {
  align-items: flex-end;
}

.stat-value {
  font-size: 32px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  line-height: 1;
}

.highlight-orange { color: #F59E0B; }

.stat-trend {
  display: flex; align-items: center; gap: 4px;
  font-size: 13px;
  font-weight: 600;
}

.trend-up { color: #10B981; }

.stat-subtext {
  font-size: 12px;
  color: #94A3B8;
  line-height: 1.3;
}

/* Filters & Search */
.filter-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 16px 24px;
  border-radius: 16px;
  border: 1px solid #F1F5F9;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
}

.search-box {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #F8FAFC;
  padding: 10px 16px;
  border-radius: 8px;
  width: 320px;
  border: 1px solid transparent;
  transition: border 0.2s;
}

.search-box:focus-within {
  border-color: #3B82F6;
  background: white;
}

.search-icon { color: #94A3B8; }

.search-box input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 14px;
  color: #0F172A;
  outline: none;
}

.search-box input::placeholder { color: #94A3B8; }

.filter-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.dropdown {
  display: flex; align-items: center; gap: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
}

.btn-icon {
  display: flex; align-items: center; justify-content: center;
  width: 40px; height: 40px;
  background: white;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  color: #64748B;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover { background: #F8FAFC; color: #0F172A; }

/* Table Section */
.table-container {
  background: white;
  border-radius: 16px;
  border: 1px solid #F1F5F9;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
  overflow: hidden; /* agar border radius terlihat rapi */
}

.directory-table {
  width: 100%;
  border-collapse: collapse;
}

.directory-table th {
  text-align: left;
  padding: 20px 24px;
  font-size: 12px;
  font-weight: 700;
  color: #64748B;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #E2E8F0;
}

.directory-table td {
  padding: 20px 24px;
  border-bottom: 1px solid #F1F5F9;
  vertical-align: middle;
}

.directory-table tr:last-child td { border-bottom: none; }
.directory-table tr:hover td { background: #F8FAF9; }

.intern-profile {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatar {
  width: 40px; height: 40px;
  border-radius: 50%;
  background: #E2E8F0;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
  font-weight: 700; color: #64748B;
}

.avatar img { width: 100%; height: 100%; object-fit: cover; }

.intern-details { display: flex; flex-direction: column; gap: 4px; }
.intern-name { margin: 0; font-size: 14px; font-weight: 600; color: #0F172A; }
.intern-id { margin: 0; font-size: 12px; color: #94A3B8; }

.td-text { font-size: 14px; color: #475569; }

/* Attendance Progress */
.attendance-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.progress-track {
  width: 120px;
  height: 6px;
  background: #F1F5F9;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill { height: 100%; border-radius: 4px; }
.bg-green { background: #10B981; }
.bg-orange { background: #F59E0B; }
.bg-grey { background: #E2E8F0; }

.attendance-text { font-size: 14px; font-weight: 600; color: #0F172A; width: 36px; }

/* Status Badge */
.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}

.status-badge.active { background: #DCFCE7; color: #166534; }
.status-badge.inactive { background: #F1F5F9; color: #64748B; }

/* Actions */
.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  background: none;
  border: none;
  color: #94A3B8;
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
  transition: all 0.2s;
}

.action-btn:hover { background: #F1F5F9; color: #0F172A; }

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-top: 1px solid #E2E8F0;
}

.pagination-info { margin: 0; font-size: 14px; color: #64748B; }

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}

.page-btn {
  display: flex; align-items: center; justify-content: center;
  width: 32px; height: 32px;
  border: none;
  background: transparent;
  color: #475569;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover { background: #F1F5F9; }
.page-btn.active { background: #3B82F6; color: white; font-weight: 600; }
.page-dots { color: #94A3B8; padding: 0 8px; }

/* Responsif Dasar */
@media (max-width: 1200px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .stats-grid { grid-template-columns: 1fr; }
  .filter-section { flex-direction: column; align-items: stretch; gap: 16px; }
  .search-box { width: 100%; }
}
/* ================= Modal Styles ================= */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.4); /* Efek gelap/dim */
  backdrop-filter: blur(4px); /* Efek blur ala figma */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999; /* Memastikan modal paling depan */
}

.modal-content {
  background: white;
  width: 100%;
  max-width: 600px;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
}

/* Modal Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 24px 24px 16px;
  border-bottom: 1px solid #F1F5F9;
}

.modal-title { font-size: 20px; font-weight: 700; color: #0F172A; margin: 0 0 4px 0; }
.modal-subtitle { font-size: 14px; color: #64748B; margin: 0; }

.btn-close {
  background: none; border: none; color: #94A3B8;
  cursor: pointer; padding: 4px; border-radius: 6px; transition: all 0.2s;
}
.btn-close:hover { background: #F1F5F9; color: #0F172A; }

/* Modal Body & Form Grid */
.modal-body { padding: 24px; }

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 13px; font-weight: 600; color: #0F172A; }

.form-group input, .form-group select {
  padding: 10px 14px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 14px;
  color: #0F172A;
  background: white;
  outline: none;
  transition: border-color 0.2s;
  font-family: inherit;
}

.form-group input::placeholder { color: #94A3B8; }
.form-group input:focus, .form-group select:focus {
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Custom styling for select dropdown icon */
.form-group select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 36px;
  cursor: pointer;
}

/* Modal Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  padding: 16px 24px 24px;
}

.btn-cancel {
  background: transparent; border: none;
  font-size: 14px; font-weight: 600; color: #475569;
  cursor: pointer; padding: 10px 16px; border-radius: 8px;
  transition: background 0.2s;
}
.btn-cancel:hover { background: #F8FAFC; }

.btn-save {
  background: #3B82F6; border: none;
  font-size: 14px; font-weight: 600; color: white;
  cursor: pointer; padding: 10px 20px; border-radius: 8px;
  transition: background 0.2s;
}
.btn-save:hover { background: #2563EB; }

/* Responsive adjustments for mobile */
@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
  .empty-spacer { display: none; }
  .modal-content { height: 100vh; max-height: 100vh; border-radius: 0; }
  .modal-body { overflow-y: auto; }
}
</style>