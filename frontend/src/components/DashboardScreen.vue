<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

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
    // Simulasi status (Hanya contoh visual agar mirip desain)
    // Random status: 1=Present (Green), 2=Late (Yellow), 0=None
    let status = null;
    if (i < 15 && i % 7 !== 0 && i % 6 !== 0) status = "present";
    if (i === 12 || i === 4) status = "late";
    if (i === 18) status = "absent";

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
});
onUnmounted(() => {
  clearInterval(timer);
});
</script>

<template>
  <div class="main-wrapper">
    <div class="mobile-frame">
      <div class="header-section">
        <div class="top-bar">
          <div class="profile-group">
            <div class="avatar">S</div>
            <div class="text-info">
              <p class="welcome">WELCOME BACK,</p>
              <h2 class="username">Username</h2>
            </div>
          </div>
          <button class="btn-notif">
            <img src="../assets/notif.png" alt="Notif" />
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
              <img src="../assets/present.png" class="stat-icon" />
              <span class="stat-num">20</span>
              <span class="stat-label">Present</span>
            </div>
            <div class="stat-card">
              <img src="../assets/late.png" class="stat-icon" />
              <span class="stat-num">02</span>
              <span class="stat-label">Late</span>
            </div>
            <div class="stat-card">
              <img src="../assets/absent.png" class="stat-icon" />
              <span class="stat-num">01</span>
              <span class="stat-label">Absent</span>
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
              >
                <div
                  v-if="item.type === 'date'"
                  class="date-circle"
                  :class="{ 'current-day': item.isToday }"
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

        <div class="nav-item-scan-wrapper">
          <div class="scan-button">
            <img src="../assets/qr.png" alt="Scan" />
          </div>
        </div>

        <div class="nav-item" @click="$emit('navigate', 'leave')" style="cursor: pointer;">
          <img src="../assets/leave.png" alt="Leave">
          <span>Leave</span>
        </div>
        <div class="nav-item" @click="$emit('navigate', 'profile')">
          <img src="../assets/profile.png" alt="Profile">
          <span>Profile</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

/* --- MAIN LAYOUT --- */
.main-wrapper {
  background-color: #e2e8f0;
  display: flex;
  justify-content: center;
  min-height: 100vh;
  margin: 0;
  font-family: "Poppins", sans-serif;
}
.mobile-frame {
  width: 100%;
  max-width: 430px;
  height: 100vh;
  background-color: #f8fafc;
  position: relative;
  overflow: hidden;
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
}

/* --- HEADER --- */
.header-section {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 50;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  padding: 20px 25px 25px 25px;
  border-bottom-left-radius: 35px;
  border-bottom-right-radius: 35px;
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.2);
}

/* --- SCROLL CONTENT --- */
.scroll-area {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: none;
}
.scroll-area::-webkit-scrollbar {
  display: none;
}

.content-wrapper {
  padding-top: 115px;
  padding-bottom: 120px;
  padding-left: 24px;
  padding-right: 24px;
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
  background: white;
  padding: 20px;
  border-radius: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
}
.rate-info {
  text-align: left;
}
.rate-label {
  margin: 0;
  font-size: 11px;
  color: #94a3b8;
  font-weight: 600;
}
.rate-num {
  margin: 5px 0;
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
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
  background: white;
  border-radius: 30px; /* Lebih bulat */
  padding: 24px;
  text-align: center;
  box-shadow: 0 15px 40px -10px rgba(0, 0, 0, 0.08); /* Shadow halus premium */
  margin-bottom: 25px;
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
  color: #334155;
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
  color: #94a3b8; /* Abu smooth */
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
  color: #0f172a;
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
  transition: transform 0.1s;
}
.action-buttons button:active {
  transform: scale(0.98);
}

.btn-clock-in {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  box-shadow: 0 8px 20px -5px rgba(37, 99, 235, 0.4);
}
.action-btn.clock-out {
  background: white !important; /* Paksa Background Putih */
  border: 1px solid #cbd5e1 !important; /* Garis pinggir abu-abu */
  color: #64748b !important; /* Paksa Teks Abu-abu */
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
  color: #1e293b;
  font-weight: 700;
}
.stats-grid {
  display: flex;
  gap: 10px;
  margin-bottom: 25px;
}
.stat-card {
  flex: 1;
  background: white;
  padding: 14px 6px;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 5px 20px -5px rgba(0, 0, 0, 0.04);
}
.stat-icon {
  width: 28px;
  margin-bottom: 6px;
}
.stat-num {
  display: block;
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
}
.stat-label {
  font-size: 10px;
  color: #94a3b8;
  font-weight: 500;
}

/* --- CALENDAR CARD (NEW) --- */
.calendar-card {
  background: white;
  border-radius: 24px;
  padding: 20px;
  box-shadow: 0 5px 20px -5px rgba(0, 0, 0, 0.04);
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
  color: #1e293b;
  font-weight: 700;
}
.cal-nav {
  display: flex;
  gap: 8px;
}
.nav-btn {
  background: rgb(229, 234, 249);
  border: none;
  width: 30px;
  height: 30px;
  min-width: 30px;
  min-height: 30px;
  flex-shrink: 0;
  padding: 0;
  border-radius: 50%;
  color: #098de4;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  line-height: 1;
}
.cal-days-name {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 10px;
}
.cal-days-name span {
  font-size: 10px;
  color: #94a3b8;
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
  color: #334155;
  border-radius: 50%;
}
/* Highlight Hari Ini */
.current-day {
  background-color: #2563eb;
  color: white;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4);
}

/* Dots Indicator */
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

/* --- HEADER (Perbaikan agar tidak rusak) --- */
.header-section {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 50;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  padding: 20px 25px 25px 25px;
  border-bottom-left-radius: 35px;
  border-bottom-right-radius: 35px;
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.2);
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
}
.avatar {
  width: 45px;
  height: 45px;
  background: #fecaca;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #7f1d1d;
  border: 2px solid rgba(255, 255, 255, 0.3);
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
  background: white;
  height: 80px;
  border-top-left-radius: 30px;
  border-top-right-radius: 30px;
  box-shadow: 0 -5px 30px rgba(0, 0, 0, 0.08);

  /* GRID 5 Kolom agar icon terbagi rata */
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  align-items: center; /* Vertikal center (sejajar) */
  padding: 0 10px;
}

/* Item Navigasi Biasa */
.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  color: #cbd5e1;
  font-size: 10px;
  font-weight: 600;
  cursor: pointer;
  height: 100%;
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
  color: #2563eb;
}
.nav-item.active img {
  opacity: 1;
  filter: grayscale(0%);
  transform: translateY(-2px);
}

/* Wrapper Tombol Tengah */
.nav-item-scan-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

/* Tombol QR (Kotak Sejajar) */
.scan-button {
  /* Hapus absolute agar tidak mengambang */
  position: static;

  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-radius: 16px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
  border: none;
  cursor: pointer;
  margin: 0; /* Pastikan tidak ada margin aneh */
}

/* Efek Klik Halus */
.scan-button:active {
  transform: scale(0.95);
}

.scan-button img {
  width: 24px;
  height: 24px;
  filter: brightness(0) invert(1);
}
</style>
