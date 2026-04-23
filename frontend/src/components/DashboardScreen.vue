<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import api from "../api/axios";

// --- USER DATA ---
const user = ref({
  name: "User",
  email: "",
});

const emit = defineEmits(["open-clock-in", "navigate", "logout"]);

// --- LOGIC WAKTU (JAM UTAMA) ---
const timeMain = ref("");
const timeSecond = ref("");
const currentDateStr = ref("");

const updateTime = () => {
  const now = new Date();
  const h = String(now.getHours()).padStart(2, "0");
  const m = String(now.getMinutes()).padStart(2, "0");
  timeMain.value = `${h}:${m}`;
  timeSecond.value = String(now.getSeconds()).padStart(2, "0");

  const options = {
    weekday: "long",
    day: "numeric",
    month: "short",
    year: "numeric",
  };
  currentDateStr.value = now.toLocaleDateString("en-US", options);
};

// Get user initial for avatar
const userInitial = computed(() => {
  return user.value.name ? user.value.name.charAt(0).toUpperCase() : "U";
});

const greetingText = computed(() => {
  const hour = new Date().getHours();
  if (hour >= 5 && hour < 11) return "Selamat Pagi 🌅";
  if (hour >= 11 && hour < 15) return "Selamat Siang ☀️";
  if (hour >= 15 && hour < 19) return "Selamat Sore 🌆";
  return "Selamat Malam 🌙";
});

// --- LOGIC KALENDER ---
const currDate = new Date();
const displayYear = ref(currDate.getFullYear());
const displayMonth = ref(currDate.getMonth()); // 0 = Jan, 11 = Dec

const monthNames = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];
const weekDays = ["MON", "TUE", "WED", "THU", "FRI", "SAT", "SUN"];

const attendances = ref([]);
const holidays = ref([]);
const leaves = ref([]);

const presentCount = computed(() => calendarDays.value.filter(d => d.type === 'date' && d.status === 'present').length);
const lateCount = computed(() => calendarDays.value.filter(d => d.type === 'date' && d.status === 'late').length);
const absentCount = computed(() => calendarDays.value.filter(d => d.type === 'date' && d.status === 'absent').length);
const leaveCount = computed(() => calendarDays.value.filter(d => d.type === 'date' && d.status === 'leave').length);

const fetchCalendarData = async () => {
  try {
    const res = await api.get("/calendar");
    if (res.data) {
      if (res.data.attendance) attendances.value = res.data.attendance;
      if (res.data.leave) leaves.value = res.data.leave;
    }
  } catch (err) {
    console.error("Gagal mengambil data kalender:", err);
  }
};

const fetchHolidays = async (year) => {
  try {
    const res = await fetch(
      `https://date.nager.at/api/v3/PublicHolidays/${year}/ID`,
    );
    if (res.ok) {
      holidays.value = await res.json();
    }
  } catch (err) {
    console.error("Gagal mengambil data hari libur:", err);
  }
};

watch(displayYear, (newYear, oldYear) => {
  if (newYear !== oldYear) {
    fetchHolidays(newYear);
  }
});

// Generate hari dalam bulan
const calendarDays = computed(() => {
  const year = displayYear.value;
  const month = displayMonth.value;

  const firstDayObj = new Date(year, month, 1);
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  // Adjust agar Senin = 0, Minggu = 6
  let startDay = firstDayObj.getDay() - 1;
  if (startDay === -1) startDay = 6;

  const days = [];

  // Padding hari kosong di awal
  for (let i = 0; i < startDay; i++) {
    days.push({ date: "", type: "empty" });
  }

  // Isi tanggal
  for (let i = 1; i <= daysInMonth; i++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;
    const currentDayObj = new Date(year, month, i);
    const dayOfWeek = currentDayObj.getDay();

    // Cek Libur (Sabtu/Minggu atau Hari Libur Nasional)
    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
    const isNationalHoliday = holidays.value.some((h) => h.date === dateStr);
    const isHoliday = isWeekend || isNationalHoliday;

    let status = null;

    // Check if on leave first
    const leaveRecord = leaves.value.find(l => {
      if (l.status !== 'approved') return false;
      return dateStr >= l.start_date && dateStr <= l.end_date;
    });

    if (leaveRecord) {
      status = "leave";
    } else {
      // Cek Presensi
      const attendanceRecord = attendances.value.find(
        (a) => a.attendance_date === dateStr,
      );

      if (attendanceRecord) {
        if (attendanceRecord.status === "hadir" || attendanceRecord.clock_in) {
          status = "present";
        } else if (attendanceRecord.status === "terlambat") {
          status = "late";
        } else if (
          attendanceRecord.status === "alpha" ||
          attendanceRecord.status === "izin"
        ) {
          status = "absent";
        }
      }
    }

    // Cek apakah hari ini
    const today = new Date();
    const isToday =
      i === today.getDate() &&
      month === today.getMonth() &&
      year === today.getFullYear();

    days.push({
      date: i,
      type: "date",
      isToday: isToday,
      status: status,
      isHoliday: isHoliday,
    });
  }
  return days;
});

// Navigasi Bulan
const prevMonth = () => {
  if (displayMonth.value === 0) {
    displayMonth.value = 11;
    displayYear.value--;
  } else {
    displayMonth.value--;
  }
};
const nextMonth = () => {
  if (displayMonth.value === 11) {
    displayMonth.value = 0;
    displayYear.value++;
  } else {
    displayMonth.value++;
  }
};

let timer;
onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);

  // Load user data from localStorage
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    user.value = JSON.parse(storedUser);
  }

  fetchCalendarData();
  fetchHolidays(displayYear.value);
});
onUnmounted(() => {
  clearInterval(timer);
});
</script>

<template>
  <div class="screen-container">
    <div class="header-section">
      <div class="top-bar">
        <div class="profile-group" @click="$emit('navigate', 'profile')">
          <div class="avatar-wrapper">
             <div class="avatar">{{ userInitial }}</div>
             <div class="active-status"></div>
          </div>
          <div class="text-info">
            <p class="welcome">{{ greetingText }}</p>
            <h2 class="username">{{ user.name }}</h2>
          </div>
        </div>
        <button class="btn-notif" @click="$emit('navigate', 'profile')">
          <div class="notif-wrapper">
            <img src="../assets/notif.png" alt="Notif" />
            <div class="notif-badge-pulse"></div>
          </div>
        </button>
      </div>
    </div>

    <div class="scroll-area">
      <div class="content-wrapper">
        <div class="location-card">
          <div class="loc-icon-wrapper">
            <img src="../assets/location.png" alt="Loc" class="loc-img" />
          </div>
          <div class="loc-text">
            <p class="loc-label">OFFICE LOCATION</p>
            <h3 class="loc-name">Tech Innovations Hub, Jakarta</h3>
          </div>
          <div class="arrow-right">></div>
        </div>

        <div class="stats-simple-card">
          <div class="rate-info">
            <p class="rate-label">ATTENDANCE RATE</p>
            <h1 class="rate-num">94.8%</h1>
            <p class="rate-desc">↗ +2.4% from last month</p>
          </div>
          <div class="rate-circle">
            <span>GOOD</span>
          </div>
        </div>

        <div class="time-card">
          <div class="date-row">
            <span class="date-text">{{ currentDateStr }}</span>
            <span class="work-hours">09:00 - 17:00</span>
          </div>

          <p class="working-label">Current Working Time</p>

          <div class="clock-wrapper">
            <h1 class="digital-clock">{{ timeMain }}</h1>
            <span class="seconds-text">{{ timeSecond }}</span>
          </div>

          <div class="action-buttons">
            <button class="btn-clock-in" @click="$emit('open-clock-in')">
              <img src="../assets/clock in.png" alt="In" />
              Clock In
            </button>
            <button
              class="action-btn clock-out"
              @click="$emit('navigate', 'clock-out')"
            >
              <div class="icon-wrapper">
                <img src="../assets/clock out.png" alt="Out" />
              </div>
              <span>Clock Out</span>
            </button>
          </div>
        </div>

        <div class="section-title">
          <h3>Monthly Attendance</h3>
        </div>

        <div class="stats-grid">
  <div class="stat-card">
    <div class="icon-square present-bg">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="icon-svg">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>
    </div>
    <span class="stat-num">{{ presentCount < 10 && presentCount > 0 ? '0' + presentCount : presentCount }}</span>
    <span class="stat-label">Present</span>
  </div>

  <div class="stat-card">
    <div class="icon-square late-bg">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="icon-svg">
        <circle cx="12" cy="12" r="10"></circle>
        <polyline points="12 6 12 12 16 14"></polyline>
      </svg>
    </div>
    <span class="stat-num">{{ lateCount < 10 && lateCount > 0 ? '0' + lateCount : lateCount }}</span>
    <span class="stat-label">Late</span>
  </div>

  <div class="stat-card">
    <div class="icon-square absent-bg">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="icon-svg">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
    </div>
    <span class="stat-num">{{ absentCount < 10 && absentCount > 0 ? '0' + absentCount : absentCount }}</span>
    <span class="stat-label">Absent</span>
  </div>

  <div class="stat-card">
    <div class="icon-square leave-bg">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="icon-svg">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line>
        <line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
      </svg>
    </div>
    <span class="stat-num">{{ leaveCount < 10 && leaveCount > 0 ? '0' + leaveCount : leaveCount }}</span>
    <span class="stat-label">Leave</span>
  </div>
</div>

        <div class="calendar-card">
          <div class="cal-header">
            <h3>{{ monthNames[displayMonth] }} {{ displayYear }}</h3>
            <div class="cal-nav">
              <button @click="prevMonth" class="nav-btn">‹</button>
              <button @click="nextMonth" class="nav-btn">›</button>
            </div>
          </div>

          <div class="cal-days-name">
            <span v-for="day in weekDays" :key="day">{{ day }}</span>
          </div>

          <div class="cal-grid">
            <div
              v-for="(item, index) in calendarDays"
              :key="index"
              class="cal-cell"
              @click="item.type === 'date' ? $emit('open-detail', item) : null"
            >
              <div
                v-if="item.type === 'date'"
                class="date-circle"
                :class="{
                  'current-day': item.isToday,
                  'is-holiday': item.isHoliday,
                }"
              >
                {{ item.date }}
              </div>

              <div
                v-if="item.status"
                class="status-dot"
                :class="item.status"
              ></div>
            </div>
          </div>
        </div>

        <div style="height: 50px"></div>
      </div>
    </div>

    <div class="bottom-nav">
      <div class="nav-item active">
        <img src="../assets/home.png" alt="Home" />
        <span>Home</span>
      </div>
      <div class="nav-item" @click="$emit('navigate', 'history')">
        <img src="../assets/history.png" alt="History" />
        <span>History</span>
      </div>

      <div class="nav-item-scan-wrapper" @click="$emit('navigate', 'qr-scan')">
        <div class="scan-button">
          <img src="../assets/qr.png" alt="Scan" />
        </div>
      </div>

      <div
        class="nav-item"
        @click="$emit('navigate', 'leave')"
        style="cursor: pointer"
      >
        <img src="../assets/leave.png" alt="Leave" />
        <span>Leave</span>
      </div>
      <div class="nav-item" @click="$emit('navigate', 'profile')">
        <img src="../assets/profile.png" alt="Profile" />
        <span>Profile</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

/* --- MAIN LAYOUT --- */
.screen-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  height: 100dvh;
  background-color: var(--bg-screen);
  overflow: hidden;
  font-family: "Poppins", sans-serif;
  position: relative;
  transition: background-color 0.3s ease;
}

/* --- HEADER --- */
.header-section {
  position: sticky; /* Ganti dari absolute ke sticky */
  top: 0;
  z-index: 100; /* Pastikan lebih tinggi dari kartu lainnya */
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  padding: 20px 25px 25px 25px;
  border-bottom-left-radius: 35px;
  border-bottom-right-radius: 35px;
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.2);
  transition: all 0.3s ease;
}

/* --- SCROLL CONTENT --- */
.scroll-area {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
}
.scroll-area::-webkit-scrollbar {
  display: none;
}

.content-wrapper {
  padding: 20px 24px 120px 24px; /* Atur ulang padding agar pas */
}

/* --- COMPONENT STYLES --- */
.location-card {
  background: linear-gradient(135deg, #1e40af 0%, #172554 100%);
  color: white;
  border-radius: 20px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  box-shadow: 0 10px 25px -5px rgba(30, 58, 138, 0.4);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  position: relative;
  z-index: 1;
}
.location-card:active {
  transform: scale(0.97);
  box-shadow: 0 5px 15px -5px rgba(30, 58, 138, 0.6);
}
.location-card:hover {
  box-shadow: 0 15px 30px -5px rgba(30, 58, 138, 0.5);
}
.loc-icon-wrapper {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.loc-img {
  width: 20px;
  height: 20px;
  filter: brightness(0) invert(1);
  object-fit: contain;
}
.loc-text {
  flex: 1;
  text-align: left;
}
.loc-label {
  margin: 0;
  font-size: 10px;
  opacity: 0.8;
  letter-spacing: 0.5px;
  color: var(--text-muted) !important;
}
.loc-name {
  margin: 2px 0 0;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
}
.arrow-right {
  font-size: 16px;
  opacity: 0.8;
}

/* Stats Simple */
.stats-simple-card {
  background-color: var(--bg-card) !important;
  color: var(--text-main) !important;
  border: 1px solid var(--border-color);  padding: 20px;
  border-radius: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  z-index: 1;
}
.stats-simple-card:active {
  transform: scale(0.97);
  background-color: #f1f5f9;
}
.stats-simple-card:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}
.rate-info {
  text-align: left;
}
.rate-label {
  margin: 0;
  font-size: 11px;
  color: var(--text-muted) !important;
  font-weight: 600;
}
.rate-num {
  margin: 5px 0;
  font-size: 28px;
  font-weight: 700;
  color: var(--text-main) !important;
}
.rate-desc {
  margin: 0;
  font-size: 11px;
  color: #22c55e;
  font-weight: 600;
}
.rate-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: 4px solid #3b82f6;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #3b82f6;
  font-weight: 700;
  font-size: 11px;
}

/* --- TIME CARD REVISION --- */
.time-card {
background-color: var(--bg-card) !important;
  color: var(--text-main) !important;
  border: 1px solid var(--border-color);
  border-radius: 30px; /* Lebih bulat */
  padding: 24px;
  text-align: center;
  box-shadow: 0 15px 40px -10px rgba(0, 0, 0, 0.08); /* Shadow halus premium */
  margin-bottom: 25px;
  position: relative;
  transition: all 0.3s ease;
  z-index: 1;
}

/* Header Tanggal & Pill */
.date-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.date-text {
  font-weight: 700;
  color: var(--text-main) !important;
  font-size: 13px;
}
.work-hours {
  background: #eff6ff;
  color: #3b82f6;
  font-size: 11px;
  padding: 6px 14px;
  border-radius: 20px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

/* Label "Current Working Time" */
.working-label {
  font-size: 12px;
  color: var(--text-muted) !important;
  margin: 0 0 5px 0;
  font-weight: 500;
}

/* Jam Digital */
.clock-wrapper {
  display: flex;
  justify-content: center;
  align-items: baseline; /* Sejajar bawah */
  gap: 4px;
  margin-bottom: 25px;
}
.digital-clock {
  font-size: 56px; /* Ukuran Besar */
  color: var(--text-main);
  margin: 0;
  font-weight: 700;
  letter-spacing: -2px; /* Huruf rapat biar modern */
  line-height: 1;
}
.seconds-text {
  font-size: 28px;
  color: #e2e8f0; /* Abu sangat muda (pasif) */
  font-weight: 600;
}

/* Tombol Action */
.action-buttons {
  display: flex;
  gap: 16px;
}
.action-buttons button {
  flex: 1;
  padding: 16px;
  border-radius: 20px;
  border: none;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.action-buttons button:active {
  transform: scale(0.95);
}
.action-buttons button:hover {
  transform: translateY(-3px);
}

.btn-clock-in {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  box-shadow: 0 8px 20px -5px rgba(37, 99, 235, 0.4);
}
.btn-clock-in:hover {
  box-shadow: 0 12px 25px -5px rgba(37, 99, 235, 0.5);
  filter: brightness(1.1);
}
.action-btn.clock-out {
  background-color: var(--bg-card);
  border: 1px solid var(--border-color) !important;
  color: var(--text-muted) !important;  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.action-btn.clock-out:hover {
  border-color: #3b82f6 !important;
  color: #3b82f6 !important;
  background-color: rgba(59, 130, 246, 0.1) !important; 
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
.action-btn.clock-out:hover span {
  color: var(--accent-primary) !important;
}

/* 2. Pastikan Teks di dalamnya juga ikut abu-abu */
.action-btn.clock-out span {
  color: #64748b !important;
}
.action-buttons img {
  width: 24px;
  height: 24px;
}
/* Grid Stats */
.section-title {
  margin: 10px 0 15px;
  text-align: left;
}
.section-title h3 {
  margin: 0;
  font-size: 15px;
  color: var(--text-main) !important;
  font-weight: 700;
}
.stats-grid {
  display: flex;
  gap: 10px;
  margin-bottom: 25px;
  position: relative;
  z-index: 1;
}
.stat-card {
  flex: 1;
  background-color: var(--bg-card) !important;
  color: var(--text-main) !important;
  border: 1px solid var(--border-color);
  padding: 14px 6px;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 5px 20px -5px rgba(0, 0, 0, 0.04);
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
  border: 1px solid var(--border-color);
}
.stat-card:active {
  transform: scale(0.92);
  background-color: #f8fafc;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.08);
}
/* Container Box (Squircle) */
.icon-square {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 8px;
}

/* Ukuran Ikon SVG di dalam Box */
.icon-svg {
  width: 20px;
  height: 20px;
}

/* Warna Spesifik Tiap Status (Background & Stroke) */
.present-bg { background-color: rgba(34, 197, 94, 0.15); color: #22c55e; }
.late-bg    { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.absent-bg  { background-color: rgba(239, 68, 68, 0.15);  color: #ef4444; }
.leave-bg   { background-color: rgba(168, 85, 247, 0.15); color: #a855f7; }

/* Pastikan SVG memakai warna dari parent-nya */
.icon-svg {
  stroke: currentColor;
}
.stat-num {
  display: block;
  font-size: 16px;
  font-weight: 700;
  color: var(--text-main) !important;
}
.stat-label {
  font-size: 10px;
  color: var(--text-muted) !important;
  font-weight: 500;
}

/* --- CALENDAR CARD (NEW) --- */
.calendar-card {
  background-color: var(--bg-card) !important;
  color: var(--text-main) !important;
  border: 1px solid var(--border-color);
  border-radius: 24px;
  padding: 20px;
  box-shadow: 0 5px 20px -5px rgba(0, 0, 0, 0.04);
  transition: all 0.3s ease;
  position: relative;
  z-index: 1;
}
.calendar-card:hover {
  box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.06);
}
.cal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}
.cal-header h3 {
  margin: 0;
  font-size: 16px;
  color: var(--text-main) !important;
  font-weight: 700;
}
.cal-nav {
  display: flex;
  gap: 8px;
  background: var(--bg-screen); /* Tombol navigasi kalender */
  color: #3b82f6;
}
.nav-btn {
  background: var(--bg-screen);
  border: none;
  width: 30px;
  height: 30px;
  min-width: 30px;
  min-height: 30px;
  flex-shrink: 0;
  padding: 0;
  border-radius: 50%;
  color: #3b82f6;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  line-height: 1;
  transition: all 0.2s;
}
.nav-btn:active {
  transform: scale(0.85);
  background: #dbeafe;
}
.nav-btn:hover {
  background: #dbeafe;
}
.cal-days-name {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 10px;
}
.cal-days-name span {
  font-size: 10px;
  color: var(--text-muted) !important;
  font-weight: 600;
}

.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  row-gap: 10px;
}
.cal-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  min-height: 40px;
  justify-content: start;
}
.date-circle {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
  border-radius: 50%;
}
/* Highlight Hari Ini */
.current-day {
  background-color: #2563eb;
  color: white !important;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4);
}

/* Warna merah untuk hari libur (Sabtu/Minggu/Tanggal Merah) */
.date-circle.is-holiday {
  color: #ef4444;
}

/* Calendar Dots Indicator and Avatar Wrapper */
.avatar-wrapper {
  position: relative;
  display: inline-block;
}
.status-dot {
  width: 4px;
  height: 4px;
  border-radius: 50%;
}
.status-dot.present {
  background-color: #22c55e;
}
.status-dot.late {
  background-color: #f59e0b;
}
.status-dot.absent {
  background-color: #ef4444;
}
.status-dot.leave {
  background-color: #a855f7;
}

/* --- HEADER (Perbaikan agar tidak rusak) --- */
.header-section {
  position: sticky; /* Ubah dari absolute ke sticky */
  top: 0;
  z-index: 100; 
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  padding: 20px 25px 25px 25px;
  border-bottom-left-radius: 35px;
  border-bottom-right-radius: 35px;
  /* Hapus left: 0 dan right: 0 jika sudah pakai sticky di flex container */
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.profile-group {
  display: flex;
  align-items: center;
  gap: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.profile-group:active {
  transform: scale(0.96);
  opacity: 0.8;
}
.profile-group:active .avatar {
  transform: scale(0.9);
}
.avatar {
  width: 48px;
  height: 48px;
  background: #fecaca;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #7f1d1d;
  border: 2px solid rgba(255, 255, 255, 0.8);
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2), 0 0 15px rgba(0,0,0,0.1);
  transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  z-index: 1;
}
.avatar-wrapper::before {
  content: "";
  position: absolute;
  top: -3px; left: -3px; right: -3px; bottom: -3px;
  background: linear-gradient(45deg, #fbbf24, #ef4444, #3b82f6);
  border-radius: 50%;
  z-index: 0;
  opacity: 0.7;
}
.active-status {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #22c55e;
  border: 2px solid white;
  border-radius: 50%;
  z-index: 2;
}
.text-info h2 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}
.text-info .welcome {
  margin: 0;
  font-size: 10px;
  opacity: 0.9;
}

.btn-notif {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.btn-notif:active {
  transform: scale(0.85);
}
.notif-wrapper {
  position: relative;
}
.notif-badge-pulse {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 10px;
  height: 100%; /* Placeholder for aspect ratio circle */
  width: 10px;
  height: 10px;
  background: #ef4444;
  border-radius: 50%;
  border: 2px solid #2563eb;
  animation: pulse-animation 2s infinite;
}
@keyframes pulse-animation {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.btn-notif:hover {
  transform: translateY(-2px);
}
/* PENTING: Ukuran pasti agar tidak menjadi raksasa */
.btn-notif img {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  object-fit: cover;
}

/* --- BOTTOM NAV (Fokus: QR Sejajar & Rapi) --- */
.bottom-nav {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: var(--bg-card);
  border-top: 1px solid var(--border-color);
  height: 75px; 
  border-top-left-radius: 30px;
  border-top-right-radius: 30px;
  box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.06);
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  align-items: center; 
  padding: 0 5px;
}

/* Item Navigasi (Teks & Icon) */
.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  color: #94A3B8; /* Warna abu-abu standar Leave */
  font-size: 10px;
  font-weight: 600;
  cursor: pointer;
  height: 100%;
  transition: all 0.2s;
}
.nav-item:active {
  transform: scale(0.9);
}
.nav-item img {
  width: 18px;
  height: 18px;
  object-fit: contain;
  opacity: 0.5;
  filter: grayscale(100%);
  transition: 0.3s;
}
.nav-item.active {
  color: var(--accent-primary, #2563eb);
}
.nav-item.active img {
  opacity: 1;
  filter: grayscale(0%) brightness(1.2);
  transform: translateY(-2px);
}

/* Styling Icon Biasa */
.nav-item img {
  width: 24px;
  height: 24px;
  object-fit: contain;
  /* Filter agar warna icon konsisten abu-abu saat tidak aktif */
  filter: brightness(0) saturate(100%) invert(75%) sepia(11%) saturate(545%) hue-rotate(182deg) brightness(87%) contrast(85%);
  transition: 0.3s;
}

/* Keadaan Aktif (Biru Dashboard) */
.nav-item.active {
  color: #2563EB;
}

.nav-item.active img {
  /* Filter warna Biru (#2563EB) */
  filter: brightness(0) saturate(100%) invert(26%) sepia(93%) saturate(3015%) hue-rotate(213deg) brightness(96%) contrast(97%);
}

/* --- TOMBOL QR (SQUIRCLE) --- */
.nav-item-scan-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.scan-button {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  border-radius: 16px; /* Bentuk Kotak Melengkung (Squircle) */
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
  border: none;
  cursor: pointer;
  transition: transform 0.2s;
}

.scan-button:active {
  transform: scale(0.92);
}

.scan-button img {
  width: 26px;
  height: 26px;
  filter: brightness(0) invert(1) !important; /* Paksa Putih Bersih */
}
/* Icon Leave Terbaru */
/* Container stat-card agar tetap rapi */
.stat-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px; /* Sesuaikan gap agar rapat seperti ikon lainnya */
}

/* Membuat kotak persegi melengkung untuk SVG */
.icon-square {
  width: 30px;  /* Sesuaikan dengan ukuran pembungkus ikon gambar (.png) */
  height: 30px; /* Pastikan persegi sempurna */
  border-radius: 12px; /* Sudut melengkung, cocokan dengan ikon gambar */
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 6px;
  overflow: hidden; /* Pastikan tidak ada yang keluar kotak */
}

/* Warna ungu transparan khusus Leave (TANPA GARIS/BORDER) */
.leave-bg-clean {
  background-color: rgba(168, 85, 247, 0.15) !important; /* Background ungu muda */
}

/* Mengontrol ukuran SVG di dalam kotak agar kecil dan presisi */
.icon-svg-leave {
  width: 22px;
  height: 22px;
  color: #a855f7 !important; /* Warna ungu tegas */
  stroke: #a855f7 !important; /* Paksa warna garisnya juga ungu */
}

/* Menyesuaikan angka dan label agar serasi dengan ikon gambar */
.stat-num {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 10px; /* Samakan ukuran teks label dengan ikon lainnya */
  color: #94a3b8;
  font-weight: 500;
}
</style>
