<script setup>
defineProps(['attendanceList']);
const formatTime = (time) => {
  if (!time) return '-';
  return new Date(time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
  <section class="attendance-container" style="--delay: 0.1s">
    <div class="card-header">
      <div class="header-main">
        <h3>Daily Attendance Logs</h3>
        <p class="subtitle">Monitor real-time student activity</p>
      </div>
      <div class="header-actions">
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input type="text" placeholder="Search student name...">
        </div>
        <button class="btn-filter">
          <img src="../../assets/trending.png" alt="Filter">
          Filter
        </button>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>DATE</th>
            <th>STUDENT</th>
            <th>LOG IN</th>
            <th>LOG OUT</th>
            <th>STATUS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="att in attendanceList" :key="att.id" class="table-row">
            <td class="col-date">{{ att.attendance_date }}</td>
            <td class="col-user">
              <div class="user-cell">
                <div class="user-avatar">{{ att.user?.name?.charAt(0) || 'U' }}</div>
                <span>{{ att.user?.name || 'Unknown' }}</span>
              </div>
            </td>
            <td class="col-time">{{ formatTime(att.clock_in) }}</td>
            <td class="col-time">{{ formatTime(att.clock_out) }}</td>
            <td class="col-status">
              <span class="badge-status" :class="att.status">
                {{ att.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<style scoped>
.attendance-container {
  background: white;
  border-radius: 24px;
  padding: 32px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 30px;
  gap: 20px;
  flex-wrap: wrap;
}

.header-main h3 {
  font-size: 20px;
  font-weight: 700;
  color: #0F172A;
  margin: 0 0 4px 0;
}
.header-main .subtitle {
  font-size: 13px;
  color: #64748B;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-box {
  background: #F1F5F9;
  border-radius: 12px;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  width: 260px;
}
.search-icon { font-size: 14px; }
.search-box input {
  background: transparent;
  border: none;
  font-size: 14px;
  color: #334155;
  width: 100%;
}
.search-box input:focus { outline: none; }

.btn-filter {
  background: white;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-filter img { width: 14px; }
.btn-filter:hover { background: #F8FAFC; border-color: #CBD5E1; }

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.data-table th {
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: #94A3B8;
  letter-spacing: 1px;
  padding: 12px 16px;
  border-bottom: 2px solid #F1F5F9;
}

.table-row td {
  padding: 18px 16px;
  border-bottom: 1px solid #F1F5F9;
  font-size: 14px;
  color: #334155;
  transition: background 0.2s;
}

.table-row:hover td {
  background: #F8FAFC;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 32px; height: 32px;
  background: #E0E7FF;
  color: #4F46E5;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 12px;
}

.col-date { font-weight: 600; color: #64748B; }
.col-time { font-family: 'JetBrains Mono', monospace; font-size: 13px; }

.badge-status {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.badge-status.hadir { background: #DCFCE7; color: #15803D; }
.badge-status.terlambat { background: #FEF3C7; color: #92400E; }
.badge-status.alfa { background: #FEE2E2; color: #B91C1C; }
</style>
