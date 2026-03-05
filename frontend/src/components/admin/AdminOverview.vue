<script setup>
import { computed } from "vue";
import {
  LogIn,
  LogOut,
  MapPin,
  Search,
  Bell,
  Moon,
  Users,
  CheckCircle,
  Gift,
  Clock,
  ChevronDown,
  Wifi,
  Calendar,
} from "lucide-vue-next";

const props = defineProps(["stats", "attendanceList"]);

// Data untuk chart mingguan (tetap dummy, tidak ada endpoint khusus untuk ini)
const weeklyAttendance = [
  { day: "Mon", present: 85, late: 15 },
  { day: "Tue", present: 92, late: 8 },
  { day: "Wed", present: 78, late: 22 },
  { day: "Thu", present: 95, late: 5 },
  { day: "Fri", present: 88, late: 12 },
  { day: "Sat", present: 5, late: 2 },
  { day: "Sun", present: 10, late: 0 },
];

// Helper: format waktu dari ISO string ke "DD MMM, HH:MM AM/PM"
const formatTime = (isoString) => {
  if (!isoString) return "-";
  const date = new Date(isoString);
  const day = String(date.getDate()).padStart(2, "0");
  const monthNames = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec",
  ];
  const month = monthNames[date.getMonth()];
  const time = date.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
  return `${day} ${month}, ${time}`;
};

// Helper: format tanggal ke string "YYYY-MM-DD" lokal
const toLocalDateString = (isoString) => {
  if (!isoString) return "";
  const date = new Date(isoString);
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, "0");
  const d = String(date.getDate()).padStart(2, "0");
  return `${y}-${m}-${d}`;
};

// Helper: tentukan status berdasarkan jenis event (clock-in atau clock-out)
const getStatus = (record, action) => {
  if (action === "Clocked In") {
    // terlambat hanya berpengaruh pada saat masuk
    return record.status === "terlambat" ? "LATE ARRIVAL" : "ON TIME";
  }
  if (action === "Clocked Out") {
    return "CLOCKED OUT";
  }
  return "";
};

// Computed: ambil 10 activity terbaru dari attendanceList nyata
// Sekarang menghasilkan entri terpisah untuk clock-in dan clock-out
const recentActivities = computed(() => {
  const list = props.attendanceList || [];
  const activities = [];

  // Ambil 10 data attendance terakhir terlebih dahulu
  list.slice(0, 10).forEach((record, index) => {
    // baris clock-in
    activities.push({
      id: `${record.id || index}-in`,
      name: record.user?.name || "Unknown",
      role: record.user?.email || "",
      action: "Clocked In",
      time: formatTime(record.clock_in),
      location: record.clock_in_lat
        ? `${parseFloat(record.clock_in_lat).toFixed(4)}, ${parseFloat(
            record.clock_in_long,
          ).toFixed(4)}`
        : "Remote",
      status: getStatus(record, "Clocked In"),
    });

    // baris clock-out jika sudah ada
    if (record.clock_out) {
      activities.push({
        id: `${record.id || index}-out`,
        name: record.user?.name || "Unknown",
        role: record.user?.email || "",
        action: "Clocked Out",
        time: formatTime(record.clock_out),
        location: record.clock_out_lat
          ? `${parseFloat(record.clock_out_lat).toFixed(4)}, ${parseFloat(
              record.clock_out_long,
            ).toFixed(4)}`
          : "Remote",
        status: getStatus(record, "Clocked Out"),
      });
    }
  });

  return activities;
});

// Computed: hitung jumlah clock-in hari ini
const todayCheckins = computed(() => {
  const list = props.attendanceList || [];
  const today = toLocalDateString(new Date().toISOString());
  return list.filter((record) => {
    const recDate =
      record.attendance_date || toLocalDateString(record.clock_in);
    return recDate === today;
  }).length;
});

// Computed: jam sekarang untuk Live Status
const liveTime = computed(() => {
  const now = new Date();
  return now.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
  });
});

const liveAmPm = computed(() => {
  const now = new Date();
  return now.getHours() < 12 ? "AM" : "PM";
});
</script>

<template>
  <header class="top-header">
    <div class="header-left">
      <h1 class="page-title">Admin Dashboard</h1>
      <p class="page-subtitle">
        Welcome back, Admin. Here's an overview of current attendance.
      </p>
    </div>

    <div class="header-right">
      <div class="search-bar">
        <Search class="search-icon" size="16" />
        <input type="text" placeholder="Search interns..." />
      </div>

      <button class="icon-btn">
        <Bell size="20" />
      </button>

      <button class="icon-btn">
        <Moon size="20" />
      </button>
    </div>
  </header>
  <div class="dashboard-container">
    <section class="overview-grid">
      <div class="stat-card" style="--delay: 0.1s">
        <div class="card-header">
          <div class="icon-wrapper blue-icon">
            <Users size="20" />
          </div>
          <span class="badge badge-green">+12%</span>
        </div>
        <div class="card-body">
          <p class="label">Total Peserta Magang</p>
          <h3 class="value">{{ stats?.total_interns ?? "-" }}</h3>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.2s">
        <div class="card-header">
          <div class="icon-wrapper green-icon">
            <CheckCircle size="20" />
          </div>
          <span class="badge badge-grey">Hadir Bulan Ini</span>
        </div>
        <div class="card-body">
          <p class="label">Hadir (Bulan Ini)</p>
          <h3 class="value">{{ stats?.present ?? "-" }}</h3>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.3s">
        <div class="card-header">
          <div class="icon-wrapper orange-icon">
            <Gift size="20" />
          </div>
          <span class="badge badge-orange"
            >{{ stats?.pending_leaves ?? 0 }} Pending</span
          >
        </div>
        <div class="card-body">
          <p class="label">Izin / Cuti</p>
          <h3 class="value">{{ stats?.absent ?? "-" }}</h3>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.4s">
        <div class="card-header">
          <div class="icon-wrapper red-icon">
            <Clock size="20" />
          </div>
          <span class="badge badge-red">Bulan Ini</span>
        </div>
        <div class="card-body">
          <p class="label">Terlambat</p>
          <h3 class="value">{{ stats?.late ?? "-" }}</h3>
        </div>
      </div>
    </section>

    <section class="dashboard-content">
      <div class="content-card chart-area">
        <div class="card-header-flex">
          <h3 class="section-title">Weekly Attendance Summary</h3>
          <div class="dropdown">
            <span class="dropdown-label">Last 7 Days</span>
            <ChevronDown class="dropdown-icon" size="16" />
          </div>
        </div>

        <div class="bar-chart">
          <div
            class="bar-group"
            v-for="(data, index) in weeklyAttendance"
            :key="index"
          >
            <div class="bar-wrapper">
              <div
                class="bar bar-present"
                :style="{ height: data.present + '%' }"
                title="Present"
              ></div>
              <div
                class="bar bar-late"
                :style="{ height: data.late + '%' }"
                title="Late"
              ></div>
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
          <Wifi class="live-icon-white" size="20" />
        </div>

        <div class="live-time-wrap">
          <p class="live-time">
            {{ liveTime }}<span class="ampm">{{ liveAmPm }}</span>
          </p>
          <div class="active-pulse-group">
            <div class="active-dot dot-green dot-pulse"></div>
            <p class="active-text">Active monitoring in progress</p>
          </div>
        </div>

        <div class="live-stats-group">
          <div class="live-stat-item">
            <p class="live-stat-label">Check-ins Hari Ini</p>
            <p class="live-stat-value">{{ todayCheckins }}</p>
          </div>
          <div class="live-stat-item">
            <p class="live-stat-label">Total Presensi</p>
            <p class="live-stat-value">
              {{ (props.attendanceList || []).length }}
            </p>
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

        <!-- Empty state jika belum ada data presensi -->
        <div v-if="recentActivities.length === 0" class="empty-state">
          <Calendar size="40" color="#cbd5e1" stroke-width="1.5" />
          <p>Belum ada data presensi</p>
        </div>

        <table v-else class="activity-table">
          <thead>
            <tr>
              <th>Peserta Magang</th>
              <th>Event</th>
              <th>Waktu</th>
              <th>Lokasi</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="activity in recentActivities"
              :key="activity.id"
              class="activity-row"
            >
              <td class="td-intern">
                <div class="avatar-box">
                  <span class="avatar-initial">{{
                    activity.name.charAt(0).toUpperCase()
                  }}</span>
                </div>
                <div class="intern-info">
                  <p class="activity-name">{{ activity.name }}</p>
                  <p class="activity-role">{{ activity.role }}</p>
                </div>
              </td>
              <td class="td-event">
                <div class="event-icon-group">
                  <LogIn
                    v-if="activity.action === 'Clocked In'"
                    class="icon-green"
                    size="18"
                  />
                  <LogOut v-else class="icon-orange" size="18" />
                  <p class="activity-action">{{ activity.action }}</p>
                </div>
              </td>
              <td class="activity-time td-timestamp">{{ activity.time }}</td>
              <td class="activity-location td-location">
                <MapPin size="16" />
                {{ activity.location }}
              </td>
              <td class="td-status">
                <span
                  :class="[
                    'badge-status',
                    {
                      success: activity.status === 'ON TIME',
                      warning: activity.status === 'CLOCKED OUT',
                      late: activity.status === 'LATE ARRIVAL',
                    },
                  ]"
                  >{{ activity.status }}</span
                >
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
  background: rgba(
    248,
    250,
    252,
    0.8
  ); /* Warna dasar semi-transparan (sesuaikan jika background app-mu putih murni) */
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
  color: #0f172a;
  margin: 0;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
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
  color: #94a3b8;
}

.search-bar input {
  padding: 10px 16px 10px 38px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  outline: none;
  width: 260px;
  background-color: white;
  transition: all 0.2s ease;
}

.search-bar input::placeholder {
  color: #94a3b8;
}

.search-bar input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.icon-btn {
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.icon-btn:hover {
  background-color: #f1f5f9;
  color: #0f172a;
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
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.icon-wrapper {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.blue-icon {
  background: #eff6ff;
  color: #3b82f6;
}
.green-icon {
  background: #ecfdf5;
  color: #10b981;
}
.orange-icon {
  background: #fff7ed;
  color: #f59e0b;
}
.red-icon {
  background: #fef2f2;
  color: #ef4444;
}

.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}
.badge-green {
  background: #dcfce7;
  color: #166534;
}
.badge-grey {
  background: transparent;
  color: #64748b;
  font-weight: 500;
  font-size: 13px;
}
.badge-orange {
  background: #ffedd5;
  color: #c2410c;
}
.badge-red {
  background: #fee2e2;
  color: #b91c1c;
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.label {
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
  margin: 0;
}
.value {
  font-size: 32px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

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
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.dropdown {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
}

.dropdown-icon {
  color: #94a3b8;
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
  border-bottom: 1px dashed #e2e8f0;
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

.bar-present {
  background-color: #a3c9fe;
} /* Biru pudar untuk data lama */
.bar-late {
  background-color: #a3c9fe;
} /* Biru pudar untuk data lama */

/* Gaya untuk Hari Ini (Aesthetic kontras) */
.bar-group:nth-child(4) .bar-present {
  background-color: #3b82f6;
} /* Biru tebal untuk data hari ini */
.bar-group:nth-child(4) .bar-late {
  background-color: #3b82f6;
} /* Biru tebal untuk data hari ini */

.day-label {
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
}

/* --- Kanan: Status Biru (Informatif, Lucu, Aesthetic) --- */
.live-status-blue {
  background: #3b82f6; /* Kotak biru estetis */
  color: white;
  border-color: #3b82f6;
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
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.dot-green {
  background-color: #10b981;
}

.dot-pulse {
  animation: pulse 2s infinite ease-out;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
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
  color: #3b82f6;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.2s ease;
}

.view-logs-btn:hover {
  background-color: #f1f5f9;
}

/* --- Recent Activity Tabel (Di Bawah Diagram & Live Status) --- */
.recent-activity-full {
  grid-template-columns: 1fr;
}

.view-all-btn {
  color: #3b82f6;
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
  color: #64748b;
  font-weight: 700;
  text-transform: uppercase;
  padding: 16px;
  letter-spacing: 0.5px;
}

.activity-table td {
  padding: 16px;
  border-bottom: 1px solid #f1f5f9;
}

.activity-row:last-child td {
  border-bottom: none;
}

.activity-row:hover {
  background-color: #f8faf9;
}

.td-intern {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-box {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
  background-color: #f1f5f9;
}

.avatar-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-initial {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 700;
  color: #3b82f6;
  background: #eff6ff;
  border-radius: 50%;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 48px 0;
  color: #94a3b8;
  font-size: 14px;
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
  color: #0f172a;
}

.activity-role {
  margin: 0;
  font-size: 12px;
  color: #64748b;
}

.td-event {
  width: 120px;
}

.event-icon-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.icon-green {
  color: #10b981;
}
.icon-orange {
  color: #f59e0b;
}

.activity-action {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
}

.td-timestamp {
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
}

.td-location {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748b;
  font-size: 13px;
}

.td-status {
  text-align: right;
}

.badge-status {
  font-size: 12px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 20px;
  white-space: nowrap;
  display: inline-block;
}

.badge-status.success {
  background-color: #ecfdf5;
  color: #166534;
}
.badge-status.warning {
  background-color: #fef3c7;
  color: #c2410c;
}
.badge-status.late {
  background-color: #fee2e2;
  color: #b91c1c;
}

/* Responsif */
@media (max-width: 1024px) {
  .overview-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .dashboard-content {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 640px) {
  .overview-grid {
    grid-template-columns: 1fr;
  }
}
</style>
