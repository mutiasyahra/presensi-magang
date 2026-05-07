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

const toLocalDateString = (isoString) => {
  if (!isoString) return "";
  const date = new Date(isoString);
  return date.toLocaleDateString('en-CA'); // YYYY-MM-DD
};

const props = defineProps(["stats", "attendanceList"]);

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
const itemsPerPage = 10;

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

    if (!record.clock_in && !record.clock_out && ['izin', 'sakit'].includes(record.status)) {
      allEvents.push({
        id: `${record.id || index}-leave`,
        name: record.user?.name || "Unknown",
        role: record.user?.email || "",
        action: record.status === "izin" ? "Izin" : "Sakit",
        time: formatTime(record.attendance_date + "T08:00:00"),
        rawTime: new Date(record.attendance_date + "T08:00:00"),
        location: "-",
        status: record.status.toUpperCase(),
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
    if (customStartDate.value) {
      const start = new Date(customStartDate.value);
      start.setHours(0, 0, 0, 0);
      
      let end;
      if (customEndDate.value) {
        end = new Date(customEndDate.value);
      } else {
        end = new Date(customStartDate.value); // fallback to start date
      }
      end.setHours(23, 59, 59, 999);
      
      all = all.filter(e => e.rawTime >= start && e.rawTime <= end);
    }
  }

  // Phase 2: Action Type Filter
  if (activityFilter.value === "Clock-in")
    all = all.filter((e) => e.action === "Clocked In");
  if (activityFilter.value === "Clock-out")
    all = all.filter((e) => e.action === "Clocked Out");
  if (activityFilter.value === "Izin/Sakit")
    all = all.filter((e) => e.action === "Izin" || e.action === "Sakit");

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
  const statsDist = props.stats?.project_distribution || [];
  const projectMap = new Map();

  // Helper to calculate working days since a specific date
  const getElapsedWorkingDaysSince = (startDateStr) => {
    if (!startDateStr) return 1;
    const start = new Date(startDateStr);
    const end = new Date();
    if (start > end) return 1;
    
    let count = 0;
    const curr = new Date(start);
    curr.setDate(curr.getDate() + 1); 
    
    while (curr <= end) {
      const day = curr.getDay();
      if (day !== 0 && day !== 6) count++; 
      curr.setDate(curr.getDate() + 1);
    }
    return count > 0 ? count : 1;
  };

  // Initialize from official stats
  statsDist.forEach(item => {
    const rawName = item.project || "Unassigned";
    const projName = rawName.trim();
    if (projName !== "Unassigned") {
      projectMap.set(projName.toLowerCase(), {
        name: projName,
        count: parseInt(item.count || 0),
        interns: new Map(),
        color: projName.toLowerCase().includes("prisma") ? "#8b5cf6" : "#3b82f6"
      });
    }
  });

  // Calculate progress per intern from attendanceList
  list.forEach(record => {
    const userId = record.user_id;
    if (!record.user) return;
    
    const rawProject = record.user.intern?.project || "Unassigned";
    const project = rawProject.trim();
    if (project === "Unassigned") return;

    const key = project.toLowerCase();
    if (!projectMap.has(key)) {
       projectMap.set(key, { 
         name: project, 
         count: 0, 
         interns: new Map(), 
         color: project.toLowerCase().includes("prisma") ? "#8b5cf6" : "#3b82f6" 
       });
    }
    
    const pData = projectMap.get(key);
    if (!pData.interns.has(userId)) {
      pData.interns.set(userId, { 
        present: 0,
        startDate: record.user.intern?.start_date
      });
    }
    
    const iStats = pData.interns.get(userId);
    if (["hadir", "terlambat", "izin", "sakit"].includes(record.status)) {
      iStats.present++;
    }
  });

  return Array.from(projectMap.values())
    .map(p => {
      const internList = Array.from(p.interns.values()).map(i => {
        const elapsed = getElapsedWorkingDaysSince(i.startDate);
        return {
          ...i,
          rate: Math.min(100, Math.round((i.present / elapsed) * 100))
        };
      });
      
      const totalRate = internList.reduce((acc, curr) => acc + curr.rate, 0);
      
      const finalCount = p.count > 0 ? p.count : p.interns.size;
      const avgRate = finalCount > 0 ? Math.round(totalRate / finalCount) : 0;
      
      return {
        name: p.name,
        count: finalCount,
        rate: avgRate,
        color: p.color
      };
    })
    .sort((a, b) => b.rate - a.rate);
});

const totalProjects = computed(() => projectPerformance.value.length);
const chartPeriod = ref("weekly"); // 'weekly' or 'monthly'

const weeklyAttendance = computed(() => {
  const list = props.attendanceList || [];
  const now = new Date();
  
  if (chartPeriod.value === "weekly") {
    const days = [];
    for (let i = 6; i >= 0; i--) {
      const d = new Date();
      d.setDate(now.getDate() - i);
      const dateStr = toLocalDateString(d.toISOString());
      const dayLabel = d.toLocaleDateString("en-US", { weekday: "short" });
      days.push({ date: dateStr, label: dayLabel, onTime: 0, late: 0 });
    }

    list.forEach(record => {
      const recDate = record.attendance_date || toLocalDateString(record.clock_in);
      const dayData = days.find(d => d.date === recDate);
      if (dayData) {
        if (record.clock_in_status === "tepat waktu" || record.status === "hadir") {
          dayData.onTime++;
        } else if (record.clock_in_status === "terlambat" || record.status === "terlambat") {
          dayData.late++;
        }
      }
    });

    const maxVal = Math.max(...days.map(d => d.onTime + d.late), 5);
    return days.map(d => ({
      label: d.label,
      onTime: d.onTime,
      late: d.late,
      onTimeHeight: (d.onTime / maxVal) * 100,
      lateHeight: (d.late / maxVal) * 100
    }));
  } else {
    // Monthly View: Group into 4 weeks (last 28 days)
    const weeks = [
      { label: "Week 1", startOffset: 27, endOffset: 21, onTime: 0, late: 0 },
      { label: "Week 2", startOffset: 20, endOffset: 14, onTime: 0, late: 0 },
      { label: "Week 3", startOffset: 13, endOffset: 7, onTime: 0, late: 0 },
      { label: "Week 4", startOffset: 6, endOffset: 0, onTime: 0, late: 0 }
    ];

    const getDayDate = (offset) => {
      const d = new Date();
      d.setDate(now.getDate() - offset);
      return toLocalDateString(d.toISOString());
    };

    list.forEach(record => {
      const recDate = record.attendance_date || toLocalDateString(record.clock_in);
      const recTime = new Date(recDate).getTime();
      
      weeks.forEach(w => {
        const start = new Date(getDayDate(w.startOffset)).getTime();
        const end = new Date(getDayDate(w.endOffset)).getTime();
        if (recTime >= start && recTime <= end) {
          if (record.clock_in_status === "tepat waktu" || record.status === "hadir") {
            w.onTime++;
          } else if (record.clock_in_status === "terlambat" || record.status === "terlambat") {
            w.late++;
          }
        }
      });
    });

    const maxVal = Math.max(...weeks.map(w => w.onTime + w.late), 10);
    return weeks.map(w => ({
      label: w.label,
      onTime: w.onTime,
      late: w.late,
      onTimeHeight: (w.onTime / maxVal) * 100,
      lateHeight: (w.late / maxVal) * 100
    }));
  }
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

const projectDonutSegments = computed(() => {
  const projects = projectPerformance.value;
  const totalInterns = projects.reduce((acc, p) => acc + p.count, 0) || 1;
  let cumulativeOffset = 0;

  return projects.map(p => {
    const percentage = (p.count / totalInterns) * 100;
    const dashArray = `${percentage} 100`;
    const dashOffset = -cumulativeOffset;
    cumulativeOffset += percentage;
    return {
      ...p,
      dashArray,
      dashOffset
    };
  });
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
            <Users size="18" />
          </div>
          <span class="trend text-green">+4.2%</span>
        </div>
        <div class="card-body">
          <h3 class="value">{{ stats?.total_interns ?? "-" }}</h3>
          <p class="label">Total Interns</p>
        </div>
        <div class="sparkline blue-spark">
          <div class="spark-bar" style="height: 40%"></div>
          <div class="spark-bar" style="height: 50%"></div>
          <div class="spark-bar" style="height: 45%"></div>
          <div class="spark-bar" style="height: 60%"></div>
          <div class="spark-bar" style="height: 55%"></div>
          <div class="spark-bar" style="height: 70%"></div>
          <div class="spark-bar" style="height: 65%"></div>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.2s">
        <div class="card-header">
          <div class="icon-wrapper green-icon">
            <CheckCircle size="18" />
          </div>
          <span class="trend text-green">Stable</span>
        </div>
        <div class="card-body">
          <h3 class="value">{{ stats?.present ?? "-" }}</h3>
          <p class="label">Active Present</p>
        </div>
        <div class="sparkline green-spark">
          <div class="spark-bar" style="height: 60%"></div>
          <div class="spark-bar" style="height: 65%"></div>
          <div class="spark-bar" style="height: 60%"></div>
          <div class="spark-bar" style="height: 70%"></div>
          <div class="spark-bar" style="height: 75%"></div>
          <div class="spark-bar" style="height: 70%"></div>
          <div class="spark-bar" style="height: 80%"></div>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.3s">
        <div class="card-header">
          <div class="icon-wrapper purple-icon">
            <Gift size="18" />
          </div>
          <span class="trend text-orange">{{ stats?.pending_leaves ?? 0 }} Action</span>
        </div>
        <div class="card-body">
          <h3 class="value">{{ stats?.pending_leaves ?? "-" }}</h3>
          <p class="label">Pending Leaves</p>
        </div>
        <div class="sparkline purple-spark">
          <div class="spark-bar" style="height: 30%"></div>
          <div class="spark-bar" style="height: 20%"></div>
          <div class="spark-bar" style="height: 40%"></div>
          <div class="spark-bar" style="height: 35%"></div>
          <div class="spark-bar" style="height: 25%"></div>
          <div class="spark-bar" style="height: 30%"></div>
          <div class="spark-bar" style="height: 45%"></div>
        </div>
      </div>

      <div class="stat-card" style="--delay: 0.4s">
        <div class="card-header">
          <div class="icon-wrapper orange-icon">
            <Clock size="18" />
          </div>
          <span class="trend text-purple">{{ totalProjects }} Active</span>
        </div>
        <div class="card-body">
          <h3 class="value">{{ totalProjects }}</h3>
          <p class="label">Total Projects</p>
        </div>
        <div class="sparkline orange-spark">
          <div class="spark-bar" style="height: 50%"></div>
          <div class="spark-bar" style="height: 45%"></div>
          <div class="spark-bar" style="height: 40%"></div>
          <div class="spark-bar" style="height: 35%"></div>
          <div class="spark-bar" style="height: 30%"></div>
          <div class="spark-bar" style="height: 25%"></div>
          <div class="spark-bar" style="height: 20%"></div>
        </div>
      </div>
    </section>

    <!-- MAIN CHARTS ROW -->
    <div class="main-charts-row">
      <!-- Attendance Trends (Left) -->
      <div class="content-card chart-area main-trend">
        <div class="card-header-flex">
          <div class="title-group">
             <h3 class="section-title">Attendance Trends</h3>
             <p class="section-subtitle">{{ chartPeriod === 'weekly' ? 'Weekly' : 'Monthly' }} activity overview</p>
          </div>
          <div class="dropdown chart-period-dropdown">
            <select v-model="chartPeriod" class="period-select">
               <option value="weekly">Last 7 Days</option>
               <option value="monthly">Last 30 Days</option>
            </select>
            <ChevronDown class="dropdown-icon" size="14" />
          </div>
        </div>

        <div class="bar-chart-container">
          <div class="y-axis">
            <span>100</span>
            <span>75</span>
            <span>50</span>
            <span>25</span>
            <span>0</span>
          </div>
          <div class="bar-chart-main">
            <div
              class="bar-group"
              v-for="(data, index) in weeklyAttendance"
              :key="index"
            >
              <div class="bar-wrapper">
                <div
                  class="bar bar-on-time"
                  :style="{ height: data.onTimeHeight + '%' }"
                >
                  <div class="bar-tooltip">On Time: {{ data.onTime }}</div>
                </div>
                <div
                  class="bar bar-late"
                  :style="{ height: data.lateHeight + '%' }"
                >
                  <div class="bar-tooltip">Late: {{ data.late }}</div>
                </div>
              </div>
              <span class="bar-label">{{ data.label }}</span>
            </div>
          </div>
        </div>
        <div class="chart-footer-legend">
           <div class="legend-item"><div class="dot blue"></div> On Time</div>
           <div class="legend-item"><div class="dot orange"></div> Late</div>
        </div>
      </div>

      <!-- Project Distribution (Right) -->
      <div class="content-card distribution-card">
        <h3 class="section-title">Project Distribution</h3>
        <p class="section-subtitle">Interns per project</p>
        
        <div class="donut-chart-container">
          <svg viewBox="0 0 36 36" class="circular-chart">
            <path class="circle-bg"
              d="M18 2.0845
                a 15.9155 15.9155 0 0 1 0 31.831
                a 15.9155 15.9155 0 0 1 0 -31.831"
            />
            <path 
              v-for="seg in projectDonutSegments"
              :key="seg.name"
              class="circle"
              :stroke-dasharray="seg.dashArray"
              :stroke-dashoffset="seg.dashOffset"
              d="M18 2.0845
                a 15.9155 15.9155 0 0 1 0 31.831
                a 15.9155 15.9155 0 0 1 0 -31.831"
              :style="{ stroke: seg.color }"
            />
          </svg>
          <div class="donut-inner">
             <span class="donut-val">{{ totalProjects }}</span>
             <span class="donut-lab">Projects</span>
          </div>
        </div>

        <div class="distribution-list">
           <div class="dist-item-wrapper" v-for="p in projectPerformance.slice(0,4)" :key="p.name">
              <div class="dist-item">
                 <div class="dist-info">
                    <span class="dist-name">{{ p.name }}</span>
                    <span class="dist-count">{{ p.count }} Interns</span>
                 </div>
                 <div class="progress-container-side">
                    <div class="dist-bar-bg">
                       <div class="dist-bar-fill" :style="{ width: p.rate + '%', backgroundColor: p.color }"></div>
                    </div>
                    <span class="progress-val-side">{{ p.rate }}%</span>
                 </div>
              </div>
           </div>
        </div>
      </div>
    </div>

    <section class="dashboard-content recent-activity-full">
      <div class="content-card recent-activity">
        <div class="card-header-flex">
          <h3 class="section-title">Recent Activity</h3>
          <div class="activity-filters">
            <button
              v-for="f in ['All', 'Clock-in', 'Clock-out', 'Izin/Sakit']"
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
                  <LogOut v-else-if="activity.action === 'Clocked Out'" class="icon-orange" size="18" />
                  <Calendar v-else size="18" style="color: #3b82f6;" />
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
  flex-shrink: 0;
}

.admin-radio-custom {
  height: 18px;
  width: 18px;
  flex-shrink: 0;
  border: 2px solid #cbd5e1;
  border-radius: 50%;
  position: relative;
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
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
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
.overview-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: var(--bg-card);
  border-radius: 20px;
  padding: 24px;
  border: 1px solid var(--border-color);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
}

.stat-card .card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.stat-card .value {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0;
  line-height: 1;
}

.stat-card .label {
  font-size: 13px;
  color: var(--text-muted);
  margin: 4px 0 0 0;
  font-weight: 500;
}

.trend {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  background: var(--bg-input);
}

.sparkline {
  display: flex;
  align-items: flex-end;
  gap: 3px;
  height: 30px;
  margin-top: 8px;
}

.spark-bar {
  flex: 1;
  border-radius: 2px;
  min-height: 4px;
}

.blue-spark .spark-bar { background: var(--accent-primary); opacity: 0.6; }
.green-spark .spark-bar { background: #10b981; opacity: 0.6; }
.purple-spark .spark-bar { background: #a855f7; opacity: 0.6; }
.orange-spark .spark-bar { background: #f59e0b; opacity: 0.6; }

/* --- MAIN CHARTS ROW --- */
.main-charts-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  margin-bottom: 32px;
}

.main-trend {
  padding: 32px;
}

.section-subtitle {
  margin: 4px 0 0 0;
  font-size: 13px;
  color: var(--text-muted);
}

.bar-chart-container {
  display: flex;
  gap: 20px;
  height: 240px;
  margin: 32px 0 16px 0;
  position: relative;
}

.y-axis {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: var(--text-dim);
  font-size: 11px;
  font-weight: 600;
  width: 24px;
  padding-bottom: 24px;
}

.bar-chart-main {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  position: relative;
}

.bar-chart-main::before {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: 23px;
  height: 1px;
  background: var(--border-color);
  opacity: 0.5;
}

.bar-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  height: 100%;
}

.bar-wrapper {
  flex: 1;
  width: 32px;
  display: flex;
  align-items: flex-end;
  gap: 4px;
  position: relative;
}

.bar {
  flex: 1;
  border-radius: 6px 6px 0 0;
  position: relative;
  transition: height 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.bar-on-time { background: linear-gradient(to top, #3b82f6, #60a5fa); }
.bar-late { background: linear-gradient(to top, #f59e0b, #fbbf24); }

.bar-tooltip {
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--text-main);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 10px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  white-space: nowrap;
}

.bar:hover .bar-tooltip {
  opacity: 1;
}

.day-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
}

.chart-period-dropdown {
  position: relative;
  display: flex;
  align-items: center;
}

.period-select {
  appearance: none;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  padding: 8px 36px 8px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  color: var(--text-main);
  cursor: pointer;
  outline: none;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.period-select:hover {
  border-color: var(--accent-primary);
  background: var(--bg-input);
}

.chart-period-dropdown .dropdown-icon {
  position: absolute;
  right: 12px;
  pointer-events: none;
  color: var(--text-muted);
}

.chart-footer-legend {
  display: flex;
  gap: 20px;
  justify-content: center;
  margin-top: 16px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
}

.dot { width: 10px; height: 10px; border-radius: 50%; }
.dot.blue { background: #3b82f6; }
.dot.orange { background: #f59e0b; }

/* Donut Chart Style */
.distribution-card {
  padding: 32px;
  display: flex;
  flex-direction: column;
}

.donut-chart-container {
  position: relative;
  width: 160px;
  height: 160px;
  margin: 32px auto;
}

.circular-chart {
  display: block;
  width: 100%;
  height: 100%;
}

.circle-bg {
  fill: none;
  stroke: var(--bg-input);
  stroke-width: 3.8;
}

.circle {
  fill: none;
  stroke-width: 3.8;
  stroke-linecap: round;
  transition: stroke-dasharray 1s ease;
}

.donut-inner {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.progress-container-side {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
}

.dist-bar-bg {
  flex: 1;
  height: 6px;
  background: var(--bg-input);
  border-radius: 3px;
  overflow: hidden;
}

.progress-val-side {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-muted);
  min-width: 35px;
  text-align: right;
}

.donut-val {
  font-size: 24px;
  font-weight: 800;
  color: var(--text-main);
}

.donut-lab {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
}

.distribution-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.dist-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.dist-info {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 600;
}

.dist-name { color: var(--text-main); }
.dist-count { color: var(--text-muted); }

.dist-bar-bg {
  height: 6px;
  background: var(--bg-input);
  border-radius: 3px;
  overflow: hidden;
}

.dist-bar-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 1s ease;
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
