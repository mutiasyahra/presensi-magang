<script setup>
import { ref, computed, watch, onMounted } from "vue";
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
  ChevronLeft,
  ChevronRight,
} from "lucide-vue-next";

const props = defineProps(["stats", "attendanceList"]);

const weeklyAttendance = computed(() => {
  const list = props.attendanceList || [];
  
  // Format day
  const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  const summaryMap = new Map();
  
  // Initialize the last 7 days including today
  for (let i = 6; i >= 0; i--) {
    const d = new Date();
    d.setDate(d.getDate() - i);
    const dayName = days[d.getDay()];
    // Store date string to ensure correct order
    const dateStr = [d.getFullYear(), d.getMonth() + 1, d.getDate()].join('-');
    summaryMap.set(dateStr, { day: dayName, present: 0, late: 0 });
  }

  // Iterate attendanceList and group them
  const todayDate = new Date();
  todayDate.setHours(23, 59, 59, 999);
  const sevenDaysAgoDate = new Date();
  sevenDaysAgoDate.setDate(todayDate.getDate() - 6);
  sevenDaysAgoDate.setHours(0, 0, 0, 0);

  list.forEach(record => {
    let rawDateStr = record.attendance_date || record.clock_in;
    if (!rawDateStr) return;
    
    let recordDate = new Date(rawDateStr);
    
    if (recordDate >= sevenDaysAgoDate && recordDate <= todayDate) {
       const dateKey = [recordDate.getFullYear(), recordDate.getMonth() + 1, recordDate.getDate()].join('-');
       
       if (summaryMap.has(dateKey)) {
         let stats = summaryMap.get(dateKey);
         if (record.status === "terlambat") {
           stats.late++;
         } else {
           stats.present++;
         }
       }
    }
  });

  const rawArray = Array.from(summaryMap.values());
  let maxTotal = 0;
  rawArray.forEach(d => {
     const t = d.present + d.late;
     if (t > maxTotal) maxTotal = t;
  });
  
  const scaleBase = maxTotal > 0 ? maxTotal : 10; // Default scale if empty
  
  return rawArray.map(d => ({
     day: d.day,
     present: (d.present / scaleBase) * 100,
     late: (d.late / scaleBase) * 100
  }));
});

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
    const status = record.clock_in_status || (record.status === "terlambat" ? "terlambat" : "tepat waktu");
    return status.toUpperCase();
  }
  if (action === "Clocked Out") {
    const status = record.clock_out_status || "tepat waktu";
    return status.toUpperCase();
  }
  return "";
};

const currentPage = ref(1);
const itemsPerPage = 6;

// Computed: ambil semua activity sorted
const allRecentEvents = computed(() => {
  const list = props.attendanceList || [];
  const allEvents = [];

  list.forEach((record, index) => {
    if (record.clock_in) {
      allEvents.push({
        id: `${record.id || index}-in`,
        name: record.user?.name || "Unknown",
        role: record.user?.email || "",
        action: "Clocked In",
        time: formatTime(record.clock_in),
        rawTime: new Date(record.clock_in),
        location: record.clock_in_location || (record.clock_in_lat
          ? `${parseFloat(record.clock_in_lat).toFixed(4)}, ${parseFloat(
              record.clock_in_long,
            ).toFixed(4)}`
          : "Remote"),
        status: getStatus(record, "Clocked In"),
        photo: record.user?.profile_photo || null,
      });
    }

    if (record.clock_out) {
      allEvents.push({
        id: `${record.id || index}-out`,
        name: record.user?.name || "Unknown",
        role: record.user?.email || "",
        action: "Clocked Out",
        time: formatTime(record.clock_out),
        rawTime: new Date(record.clock_out),
        location: record.clock_out_location || (record.clock_out_lat
          ? `${parseFloat(record.clock_out_lat).toFixed(4)}, ${parseFloat(
              record.clock_out_long,
            ).toFixed(4)}`
          : "Remote"),
        status: getStatus(record, "Clocked Out"),
        photo: record.user?.profile_photo || null,
      });
    }
  });

  return allEvents.sort((a, b) => b.rawTime - a.rawTime);
});

const totalPages = computed(() => Math.ceil(filteredEvents.value.length / itemsPerPage));

const recentActivities = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredEvents.value.slice(start, start + itemsPerPage);
});


const activityFilter = ref("All");
const selectedRange = ref("Hari ini"); // Default: Today
const customStartDate = ref("");
const customEndDate = ref("");
const selectedMonth = ref(new Date().toISOString().slice(0, 7)); // Default: Current month YYYY-MM
const showRangeModal = ref(false);

// Temp state for modal
const tempRange = ref("Hari ini");
const tempStartDate = ref("");
const tempEndDate = ref("");
const tempMonth = ref(new Date().toISOString().slice(0, 7));

const openRangeModal = () => {
  tempRange.value = selectedRange.value;
  tempStartDate.value = customStartDate.value;
  tempEndDate.value = customEndDate.value;
  tempMonth.value = selectedMonth.value;
  showRangeModal.value = true;
};

const applyRange = () => {
  selectedRange.value = tempRange.value;
  customStartDate.value = tempStartDate.value;
  customEndDate.value = tempEndDate.value;
  selectedMonth.value = tempMonth.value;
  showRangeModal.value = false;
  currentPage.value = 1;
};

const setFilter = (filter) => {
  activityFilter.value = filter;
  currentPage.value = 1;
};

const resetDate = () => {
  selectedRange.value = "Hari ini";
  customStartDate.value = "";
  customEndDate.value = "";
  currentPage.value = 1;
};

const filteredEvents = computed(() => {
  let all = allRecentEvents.value;

  // Phase 1: Range Filter
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  if (selectedRange.value === "Hari ini") {
    all = all.filter(e => e.rawTime.toDateString() === today.toDateString());
  } else if (selectedRange.value === "7 Hari Terakhir") {
    const limit = today.getTime() - 7 * 24 * 60 * 60 * 1000;
    all = all.filter(e => e.rawTime.getTime() >= limit);
  } else if (selectedRange.value === "Pilih Bulan") {
    const [year, month] = selectedMonth.value.split("-").map(Number);
    all = all.filter(e => {
      const d = e.rawTime;
      return d.getFullYear() === year && (d.getMonth() + 1) === month;
    });
  } else if (selectedRange.value === "Pilih Tanggal") {
    if (customStartDate.value && customEndDate.value) {
      const start = new Date(customStartDate.value);
      start.setHours(0, 0, 0, 0);
      const end = new Date(customEndDate.value);
      end.setHours(23, 59, 59, 999);
      all = all.filter(e => e.rawTime >= start && e.rawTime <= end);
    }
  }

  // Phase 2: Action Type Filter
  if (activityFilter.value === "Clock-in")
    all = all.filter((e) => e.action === "Clocked In");
  if (activityFilter.value === "Clock-out")
    all = all.filter((e) => e.action === "Clocked Out");

  return all;
});

// Live Indicator Label
const rangeLabel = computed(() => {
  if (selectedRange.value === "Hari ini") return "Showing today's activity";
  return `Viewing: ${selectedRange.value}`;
});
const getImageUrl = (path) => {
  if (!path) return null;
  // If it's already a full URL (data: or http), return as is
  if (path.startsWith('data:') || path.startsWith('http')) return path;
  
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace("/api", "");
  return `${baseUrl}/storage/${path}`;
};




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

const attendanceStatusToday = computed(() => {
  const list = props.attendanceList || [];
  const today = toLocalDateString(new Date().toISOString());
  const todayList = list.filter(r => (r.attendance_date || toLocalDateString(r.clock_in)) === today);
  
  const total = props.stats?.total_interns || 0;
  const onTime = todayList.filter(r => r.clock_in_status === 'tepat waktu').length;
  const late = todayList.filter(r => r.clock_in_status === 'terlambat').length;
  const leave = todayList.filter(r => r.status === 'izin').length;
  const absent = total - (onTime + late + leave);

  return [
    { label: "On Time", value: onTime, color: "#10b981" },
    { label: "Late", value: late, color: "#f59e0b" },
    { label: "Leave", value: leave, color: "#3b82f6" },
    { label: "Absent", value: Math.max(0, absent), color: "#f43f5e" },
  ];
});

const projectPerformance = computed(() => {
  const list = props.attendanceList || [];
  const projectMap = new Map();

  list.forEach(record => {
    const project = record.user?.intern?.project || "Unassigned";
    if (!projectMap.has(project)) {
      projectMap.set(project, { name: project, present: 0, total: 0 });
    }
    const stats = projectMap.get(project);
    stats.total++;
    if (["hadir", "terlambat"].includes(record.status)) {
      stats.present++;
    }
  });

  return Array.from(projectMap.values())
    .map(p => ({
      ...p,
      rate: p.total > 0 ? Math.round((p.present / p.total) * 100) : 0
    }))
    .sort((a, b) => b.rate - a.rate)
    .slice(0, 5);
});

const chartGradient = computed(() => {
  const list = attendanceStatusToday.value;
  const total = props.stats?.total_interns || 1;
  let cumulative = 0;
  const stops = [];

  list.forEach((item) => {
    if (item.value <= 0) return;
    const start = (cumulative / total) * 100;
    cumulative += item.value;
    const end = (cumulative / total) * 100;
    stops.push(`${item.color} ${start}% ${end}%`);
  });

  if (stops.length === 0) return "conic-gradient(#e2e8f0 0% 100%)";
  return `conic-gradient(${stops.join(", ")})`;
});

const internPerformance = computed(() => {
  const list = props.attendanceList || [];
  const internMap = new Map();

  list.forEach(record => {
    const userId = record.user_id;
    if (!record.user) return;
    
    if (!internMap.has(userId)) {
      internMap.set(userId, { 
        name: record.user.name || "Unknown", 
        email: record.user.email || "",
        present: 0 
      });
    }
    const stats = internMap.get(userId);
    if (["hadir", "terlambat"].includes(record.status)) {
      stats.present++;
    }
  });

  return Array.from(internMap.values())
    .sort((a, b) => b.present - a.present)
    .slice(0, 5);
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

    <section class="dashboard-content performance-row">
      <div class="content-card status-card">
        <h3 class="section-title">Attendance Status (Today)</h3>
        <div class="status-chart-container">
          <div class="chart-visual-box">
            <div
              class="chart-circle"
              :style="{ background: chartGradient }"
            ></div>
            <div class="chart-center">
              <span class="center-label">Total</span>
              <span class="center-value">{{
                props.stats?.total_interns || 0
              }}</span>
            </div>
          </div>

          <div class="chart-legend">
            <div
              v-for="item in attendanceStatusToday"
              :key="item.label"
              class="legend-item"
            >
              <div class="legend-row">
                <div
                  class="legend-dot"
                  :style="{ backgroundColor: item.color }"
                ></div>
                <span class="legend-label">{{ item.label }}</span>
                <span class="legend-value">{{ item.value }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="content-card performance-card">
        <h3 class="section-title">Project Performance</h3>
        <div class="project-list">
          <div
            v-for="project in projectPerformance"
            :key="project.name"
            class="project-item"
          >
            <div class="project-name-row">
              <span class="project-name">{{ project.name }}</span>
              <span class="project-rate">{{ project.rate }}%</span>
            </div>
            <div class="progress-container">
              <div
                class="progress-fill"
                :style="{ width: project.rate + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <div class="content-card intern-card">
        <h3 class="section-title">Intern Performance</h3>
        <div class="intern-performance-list">
          <div
            v-for="(intern, idx) in internPerformance"
            :key="intern.email"
            class="intern-perf-item"
          >
            <div class="intern-perf-info">
              <div class="intern-perf-avatar">
                {{ intern.name.charAt(0).toUpperCase() }}
              </div>
              <div class="intern-perf-details">
                <span class="intern-perf-name">{{ intern.name }}</span>
                <span class="intern-perf-subtext"
                  >{{ intern.present }} Present Days</span
                >
              </div>
            </div>
            <div class="perf-rank">
              <span class="rank-badge">#{{ idx + 1 }}</span>
            </div>
          </div>
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
          <div class="activity-filters">
            <button
              v-for="f in ['All', 'Clock-in', 'Clock-out']"
              :key="f"
              :class="['filter-pill', { active: activityFilter === f }]"
              @click="setFilter(f)"
            >
              {{ f }}
            </button>
          </div>
        </div>

        <div class="calendar-filter-bar">
          <div class="live-indicator-small">
            <span :class="['pulse-dot', { active: selectedRange === 'Hari ini' }]"></span>
            {{ rangeLabel }}
          </div>

          <div class="calendar-picker-container">
            <div
              :class="['date-selector', { active: showRangeModal }]"
              @click="openRangeModal"
            >
              <Calendar class="cal-main-icon" size="18" />
              <span class="current-date-text">{{ selectedRange }}</span>
            </div>

            <!-- Custom Range Modal (Admin Desktop Style) -->
            <transition name="fade">
              <div class="admin-range-modal" v-if="showRangeModal" @click.self="showRangeModal = false">
                <div class="admin-modal-content">
                  <div class="admin-modal-header">
                    <h3>Rentang Waktu</h3>
                    <button class="close-btn" @click="showRangeModal = false">&times;</button>
                  </div>
                  
                  <div class="admin-modal-body">
                    <div class="admin-range-options">
                      <label v-for="opt in ['Hari ini', '7 Hari Terakhir', 'Pilih Bulan', 'Pilih Tanggal']" :key="opt" class="admin-range-option">
                        <div class="opt-left">
                           <span class="opt-text">{{ opt }}</span>
                           <!-- Inputs for specific options -->
                           <div v-if="tempRange === 'Pilih Bulan' && opt === 'Pilih Bulan'" class="opt-input-wrapper">
                             <input type="month" v-model="tempMonth" class="admin-inline-input" />
                           </div>
                           <div v-if="tempRange === 'Pilih Tanggal' && opt === 'Pilih Tanggal'" class="opt-input-wrapper date-range">
                             <input type="date" v-model="tempStartDate" class="admin-inline-input" />
                             <span>to</span>
                             <input type="date" v-model="tempEndDate" class="admin-inline-input" />
                           </div>
                        </div>
                        <div class="opt-right">
                          <input type="radio" :value="opt" v-model="tempRange" name="admin-range" />
                          <span class="admin-radio-custom"></span>
                        </div>
                      </label>
                    </div>
                  </div>

                  <div class="admin-modal-footer">
                    <button class="admin-btn-apply" @click="applyRange">Terapkan Filter</button>
                  </div>
                </div>
              </div>
            </transition>
          </div>
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
                  <img v-if="activity.photo" :src="getImageUrl(activity.photo)" class="activity-avatar-img" />
                  <span v-else class="avatar-initial">{{
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
                      success: ['ON TIME', 'TEPAT WAKTU'].includes(activity.status),
                      warning: activity.status === 'TERLALU CEPAT',
                      late: ['LATE ARRIVAL', 'TERLAMBAT'].includes(activity.status),
                    },
                  ]"
                  >{{ activity.status }}</span
                >
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination Controls -->
        <div v-if="filteredEvents.length > itemsPerPage" class="pagination-area">
          <p class="pagination-info">
            Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to
            {{ Math.min(currentPage * itemsPerPage, filteredEvents.length) }} of
            {{ filteredEvents.length }} Activities
          </p>
          <div class="pagination-btns">
            <button 
              class="page-nav-btn" 
              :disabled="currentPage === 1" 
              @click="currentPage--"
            >
              <ChevronLeft size="18" />
            </button>
            <span class="page-indicator">Page {{ currentPage }} of {{ totalPages }}</span>
            <button 
              class="page-nav-btn" 
              :disabled="currentPage === totalPages" 
              @click="currentPage++"
            >
              <ChevronRight size="18" />
            </button>
          </div>
        </div>
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
  background: var(--bg-app);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border-color);
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.page-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
}

.page-subtitle {
  font-size: 14px;
  color: var(--text-muted);
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
  color: var(--text-dim);
}

.search-bar input {
  padding: 10px 16px 10px 38px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  color: var(--text-main);
  outline: none;
  width: 260px;
  background-color: var(--bg-card);
  transition: all 0.2s ease;
}

.search-bar input::placeholder {
  color: var(--text-dim);
}

.search-bar input:focus {
  border-color: var(--accent-primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.icon-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.icon-btn:hover {
  background-color: var(--bg-input);
  color: var(--text-main);
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
  background: var(--bg-card);
  padding: 20px;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--border-color);
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
  background: rgba(0, 212, 255, 0.1);
  color: #00d4ff;
}
.green-icon {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}
.orange-icon {
  background: rgba(255, 215, 0, 0.1);
  color: #ffd700;
}
.red-icon {
  background: rgba(244, 63, 94, 0.1);
  color: #f43f5e;
}

.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}
.badge-green {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
}
.badge-grey {
  background: transparent;
  color: var(--text-muted);
  font-weight: 500;
  font-size: 13px;
}
.badge-orange {
  background: rgba(255, 215, 0, 0.1);
  color: #fcd34d;
}
.badge-red {
  background: rgba(244, 63, 94, 0.15);
  color: #fb7185;
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.label {
  color: var(--text-muted);
  font-size: 14px;
  font-weight: 500;
  margin: 0;
}
.value {
  font-size: 32px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
}

/* --- Bagian Bawah (Grafik & Status Biru) --- */
.dashboard-content {
  display: grid;
  grid-template-columns: 2fr 1fr; /* Kolom kiri lebih lebar (2:1) */
  gap: 20px;
}

.content-card {
  background: var(--bg-card);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
}

.dropdown {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
}

.dropdown-icon {
  color: var(--text-dim);
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
  border-bottom: 1px dashed var(--border-color);
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
  background-color: var(--bg-input);
} /* Biru pudar untuk data lama */
.bar-late {
  background-color: var(--bg-input);
} /* Biru pudar untuk data lama */

/* Gaya untuk Hari Ini (Aesthetic kontras) */
.bar-group:nth-child(4) .bar-present {
  background-color: var(--accent-primary);
} /* Biru tebal untuk data hari ini */
.bar-group:nth-child(4) .bar-late {
  background-color: var(--accent-primary);
} /* Biru tebal untuk data hari ini */

.day-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
}

/* --- Kanan: Status Biru (Informatif, Lucu, Aesthetic) --- */
.live-status-blue {
  background: var(--accent-primary); /* Kotak biru estetis */
  color: white;
  border-color: var(--accent-primary);
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
  background-color: rgba(255, 255, 255, 0.9);
  color: var(--accent-primary);
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.2s ease;
}

.view-logs-btn:hover {
  background-color: white;
}

/* --- Recent Activity Tabel (Di Bawah Diagram & Live Status) --- */
.recent-activity-full {
  grid-template-columns: 1fr;
}

.view-all-btn {
  color: var(--accent-primary);
  font-size: 13px;
  font-weight: 600;
  background: none;
  border: none;
  cursor: pointer;
}

.activity-filters {
  display: flex;
  background: var(--bg-input);
  padding: 4px;
  border-radius: 10px;
  gap: 4px;
}

.filter-pill {
  padding: 6px 14px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-pill:hover {
  color: var(--text-main);
}

.filter-pill.active {
  background: var(--bg-card);
  color: var(--accent-primary);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Calendar Filter Styles */
.calendar-filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 14px;
  padding: 0 4px;
  border-top: 1px solid var(--border-color);
  padding-top: 14px;
}

.history-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--accent-primary);
}

.calendar-picker-container {
  position: relative;
}

.date-selector {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--bg-input);
  padding: 8px 16px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.date-selector:hover,
.date-selector.active {
  background: var(--bg-input);
  border-color: var(--accent-primary);
}

.cal-main-icon {
  color: var(--text-muted);
}

.current-date-text {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-main);
}

.custom-calendar-dropdown {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 280px;
  background: var(--bg-card);
  border-radius: 16px;
  box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.4);
  border: 1px solid var(--border-color);
  padding: 16px;
  z-index: 1000;
  user-select: none;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.month-label {
  font-weight: 700;
  color: var(--text-main);
  font-size: 14px;
}

.nav-btn {
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-muted);
  font-weight: 700;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}

.day-name {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-dim);
  text-align: center;
  padding-bottom: 8px;
}

.admin-range-modal {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 320px;
  background: var(--bg-card);
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  border: 1px solid var(--border-color);
  z-index: 1000;
  overflow: hidden;
  animation: slideInDown 0.2s ease-out;
}

@keyframes slideInDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.admin-modal-header {
  padding: 16px 20px;
  background: var(--bg-input);
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
}

.admin-modal-header h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: var(--text-main);
}

.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  color: var(--text-dim);
  cursor: pointer;
}

.admin-modal-body {
  padding: 8px 0;
}

.admin-range-options {
  display: flex;
  flex-direction: column;
}

.admin-range-option {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 12px 20px;
  cursor: pointer;
  transition: background 0.2s;
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.admin-range-option:hover {
  background: var(--bg-input);
}

.opt-left {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.opt-text {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
  line-height: 20px;
}

.opt-input-wrapper {
  margin-top: 4px;
}

.admin-inline-input {
  width: 100%;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-main);
  font-size: 12px;
  outline: none;
}

.date-range {
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-range span {
  font-size: 11px;
  color: var(--text-muted);
}

.opt-right {
  display: flex;
  align-items: center;
  height: 20px;
  margin-left: 12px;
  position: relative;
}

.admin-radio-custom {
  height: 18px;
  width: 18px;
  border: 2px solid #cbd5e1;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.admin-range-option input[type="radio"] {
  position: absolute;
  opacity: 0;
}

.admin-range-option input:checked ~ .admin-radio-custom {
  border-color: var(--accent-primary);
}

.admin-range-option input:checked ~ .admin-radio-custom::after {
  content: "";
  width: 10px;
  height: 10px;
  background: var(--accent-primary);
  border-radius: 50%;
}

.admin-modal-footer {
  padding: 16px 20px;
  border-top: 1px solid var(--border-color);
}

.admin-btn-apply {
  width: 100%;
  padding: 10px;
  background: var(--accent-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.2s;
}

.admin-btn-apply:hover {
  opacity: 0.9;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.live-indicator-small {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: var(--text-dim);
  font-weight: 500;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  background: #10b981;
  border-radius: 50%;
  animation: pulse-small 2s infinite ease-out;
}

@keyframes pulse-small {
  0% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
}

.activity-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.activity-table th {
  text-align: left;
  font-size: 12px;
  color: var(--text-dim);
  font-weight: 700;
  text-transform: uppercase;
  padding: 16px;
  letter-spacing: 0.5px;
}

.activity-table td {
  padding: 16px;
  border-bottom: 1px solid var(--border-color);
}

.activity-row:last-child td {
  border-bottom: none;
}

.activity-row:hover {
  background-color: var(--bg-input);
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
  background-color: var(--bg-input);
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
  color: var(--accent-primary);
  background: var(--bg-input);
  border-radius: 50%;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 48px 0;
  color: var(--text-dim);
  font-size: 14px;
}

.activity-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
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
  color: var(--text-main);
}

.activity-role {
  margin: 0;
  font-size: 12px;
  color: var(--text-muted);
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
  color: var(--text-main);
}

.td-timestamp {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
}

.td-location {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-muted);
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

/* Pagination Area Styles */
.pagination-area {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid var(--border-color);
}

.pagination-info {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
  margin: 0;
}

.pagination-btns {
  display: flex;
  align-items: center;
  gap: 16px;
}

.page-nav-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-main);
  cursor: pointer;
  transition: all 0.2s ease;
}

.page-nav-btn:hover:not(:disabled) {
  background: var(--bg-input);
  border-color: var(--text-dim);
}

.page-nav-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-indicator {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
}

/* New Section Styles */
.performance-row {
  margin-bottom: 0px;
  grid-template-columns: repeat(3, 1fr) !important;
}

.status-distribution {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 20px;
}

.status-chart-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
  margin-top: 24px;
}

@media (min-width: 1400px) {
  .status-chart-container {
    flex-direction: row;
    gap: 32px;
  }
}

.chart-visual-box {
  position: relative;
  width: 170px;
  height: 170px;
  flex-shrink: 0;
}

.chart-circle {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  transition: background 0.5s ease;
}

.chart-center {
  position: absolute;
  top: 15%;
  left: 15%;
  width: 70%;
  height: 70%;
  background: var(--bg-card);
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
}

.center-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
}

.center-value {
  font-size: 22px;
  font-weight: 800;
  color: var(--text-main);
}

.chart-legend {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.legend-item {
  padding: 2px 0;
}

.legend-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.legend-label {
  flex: 1;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
}

.legend-value {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-main);
}

.intern-performance-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 20px;
}

.intern-perf-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid var(--border-color);
}

.intern-perf-item:last-child {
  border-bottom: none;
}

.intern-perf-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.intern-perf-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: var(--bg-input);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
  color: var(--accent-primary);
}

.intern-perf-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.intern-perf-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
}

.intern-perf-subtext {
  font-size: 11px;
  color: var(--text-muted);
}

.rank-badge {
  font-size: 11px;
  font-weight: 700;
  color: var(--accent-primary);
  background: rgba(59, 130, 246, 0.1);
  padding: 4px 8px;
  border-radius: 6px;
}

.status-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.status-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
}

.status-value {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-main);
}

.status-bar-bg {
  height: 8px;
  background: var(--bg-input);
  border-radius: 4px;
  overflow: hidden;
}

.status-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.project-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 20px;
}

.project-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.project-name-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.project-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
}

.project-rate {
  font-size: 12px;
  font-weight: 700;
  color: var(--accent-primary);
}

.progress-container {
  height: 6px;
  background: var(--bg-input);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: var(--accent-primary);
  border-radius: 3px;
  transition: width 0.5s ease;
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
