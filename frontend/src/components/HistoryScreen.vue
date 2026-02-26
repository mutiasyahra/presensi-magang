<script setup>
import { ref, computed } from "vue";

const emit = defineEmits(["navigate", "go-back"]);

const emit = defineEmits(['navigate', 'go-back'])

// --- DATA DUMMY ---
const historyData = ref([
  {
    id: 1,
    day: "Thursday",
    date: "Oct 24, 2023",
    time: "08:41 AM",
    status: "PRESENT",
    type: "clock-in",
    noteTitle: "DAILY WORK PLAN",
    note: "Menyelesaikan slicing UI untuk modul laporan harian dan sinkronisasi berkas magang...",
    icon: "icon present.png",
    noteIcon: "daily plan.png",
  },
  {
    id: 2,
    day: "Tuesday",
    date: "Oct 17, 2023",
    time: "17:15 PM",
    status: "DONE",
    type: "clock-out",
    noteTitle: "DAILY ACTIVITY",
    note: "Menyelesaikan slicing UI untuk modul laporan harian dan sinkronisasi berkas magang...",
    icon: "icon done.png",
    noteIcon: "daily activity.png",
  },
  {
    id: 3,
    day: "Tuesday",
    date: "Oct 17, 2023",
    time: "09:15 AM",
    status: "LATE",
    type: "clock-in",
    noteTitle: "DAILY WORK PLAN",
    note: "Menyelesaikan slicing UI untuk modul laporan harian dan sinkronisasi berkas magang...",
    icon: "icon late.png",
    noteIcon: "daily plan.png",
  },
]);

const activeTab = ref("All");

// --- LOGIC FILTER ---
const filteredHistory = computed(() => {
  if (activeTab.value === "All") return historyData.value;
  const filterType = activeTab.value === "Clock In" ? "clock-in" : "clock-out";
  return historyData.value.filter((item) => item.type === filterType);
});

const getStatusClass = (status) => {
  if (status === "PRESENT") return "status-present";
  if (status === "DONE") return "status-done";
  if (status === "LATE") return "status-late";
  return "";
};
</script>

<template>
  <div class="main-wrapper">
    <div class="mobile-frame">
      <div class="top-fixed-area">
        <div class="header">
          <button class="btn-back" @click="$emit('navigate', 'dashboard')">
            ❮
          </button>
          <h2>Attendance History</h2>
          <div style="width: 24px"></div>
        </div>

        <div class="search-container">
          <div class="search-box">
            <img src="../assets/search.png" class="search-icon" alt="Search" />
            <input type="text" placeholder="Search..." />
          </div>
        </div>

        <div class="filter-tabs">
          <button
            class="tab-btn"
            :class="{ active: activeTab === 'All' }"
            @click="activeTab = 'All'"
          >
            All
          </button>
          <button
            class="tab-btn"
            :class="{ active: activeTab === 'Clock In' }"
            @click="activeTab = 'Clock In'"
          >
            Clock In
          </button>
          <button
            class="tab-btn"
            :class="{ active: activeTab === 'Clock Out' }"
            @click="activeTab = 'Clock Out'"
          >
            Clock Out
          </button>
        </div>
      </div>

      <div class="scroll-area">
        <div class="content-padding">
          <div
            v-for="item in filteredHistory"
            :key="item.id"
            class="history-card"
            :class="getStatusClass(item.status)"
          >
            <div class="card-top">
              <div class="icon-wrapper">
                <img :src="`/src/assets/${item.icon}`" alt="Icon" />
              </div>

              <div class="card-info">
                <span class="day-name">{{ item.day }}</span>
                <div class="time-row">
                  <img src="../assets/jam.png" alt="clock" class="clock-icon" />
                  <span class="date-text"
                    >{{ item.date }} • {{ item.time }}</span
                  >
                </div>
              </div>

              <div class="card-actions">
                <span class="badge" :class="getStatusClass(item.status)">{{
                  item.status
                }}</span>
                <button class="btn-edit">
                  <img src="../assets/pencil.png" alt="Edit" />
                </button>
              </div>
            </div>

            <div class="dashed-box">
              <div class="dashed-header">
                <img :src="`/src/assets/${item.noteIcon}`" alt="Note Icon" />
                <span>{{ item.noteTitle }}</span>
              </div>
              <p class="dashed-content">"{{ item.note }}"</p>
            </div>
          </div>

          <div v-if="filteredHistory.length === 0" class="empty-state">
            <p>No data found.</p>
          </div>

          <div style="height: 100px"></div>
        </div>
      </div>

      <div class="bottom-nav">
        <div class="nav-item" @click="$emit('navigate', 'dashboard')">
          <img src="../assets/home.png" alt="Home" />
          <span>Home</span>
        </div>

        <div class="nav-item active">
          <img src="../assets/history.png" alt="History" />
          <span>History</span>
        </div>

        <div class="nav-item-scan-wrapper">
          <div class="scan-button">
            <img src="../assets/qr.png" alt="Scan" />
          </div>
        </div>

        <div class="nav-item" @click="$emit('navigate', 'leave')">
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
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

/* --- MAIN LAYOUT (Sama seperti Dashboard) --- */
.main-wrapper {
  background-color: #e2e8f0;
  display: flex;
  justify-content: center;
  min-height: 100vh;
  margin: 0;
  font-family: "Inter", sans-serif;
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

/* --- TOP FIXED AREA --- */
.top-fixed-area {
  background-color: #f8fafc;
  padding: 20px 20px 0 20px;
  z-index: 10;
  flex-shrink: 0;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.header h2 {
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.btn-back {
  border: none;
  background: none;
  font-size: 20px;
  cursor: pointer;
  color: #2563eb;
  font-weight: bold;
}

/* Search */
.search-container {
  margin-bottom: 20px;
}
.search-box {
  position: relative;
  width: 100%;
}
.search-box input {
  width: 100%;
  box-sizing: border-box;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 12px 12px 45px;
  font-size: 14px;
  color: #64748b;
  outline: none;
}
.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  opacity: 0.5;
}

/* Tabs */
.filter-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}
.tab-btn {
  flex: 1;
  padding: 10px;
  border-radius: 25px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.tab-btn.active {
  background: #2563eb;
  color: white;
  border-color: #2563eb;
}

/* --- SCROLL AREA --- */
.scroll-area {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: none;
}
.scroll-area::-webkit-scrollbar {
  display: none;
}
.content-padding {
  padding: 10px 20px 20px 20px;
}

/* --- CARD STYLE --- */
.history-card {
  background: white;
  border-radius: 16px;
  padding: 16px;
  margin-bottom: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  border-left: 5px solid transparent;
}
.history-card.status-present {
  border-left-color: #22c55e;
}
.history-card.status-done {
  border-left-color: #3b82f6;
}
.history-card.status-late {
  border-left-color: #eab308;
}

.card-top {
  display: flex;
  gap: 12px;
  margin-bottom: 15px;
  align-items: flex-start;
}
.icon-wrapper img {
  width: 44px;
  height: 44px;
}

/* REVISI: Text Align Left & Flex Start */
.card-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: flex-start; /* KUNCI: Rata Kiri */
  justify-content: center;
  gap: 4px;
}
.day-name {
  font-weight: 700;
  font-size: 15px;
  color: #0f172a;
}
.time-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.clock-icon {
  width: 14px;
  opacity: 0.6;
}
.date-text {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
}

.card-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 12px;
}
.badge {
  font-size: 10px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  text-transform: uppercase;
}
.badge.status-present {
  background: #dcfce7;
  color: #15803d;
}
.badge.status-done {
  background: #dbeafe;
  color: #1e40af;
}
.badge.status-late {
  background: #fef9c3;
  color: #a16207;
}

.btn-edit {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}
.btn-edit img {
  width: 16px;
  opacity: 0.4;
}

.dashed-box {
  border: 1.5px dashed #e2e8f0;
  border-radius: 12px;
  padding: 12px;
  background: #f8fafc;
}
.dashed-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}
.dashed-header img {
  width: 14px;
}
.dashed-header span {
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.5px;
}
.dashed-content {
  margin: 0;
  font-size: 12px;
  color: #475569;
  font-style: italic;
  line-height: 1.5;
  text-align: left;
}
.empty-state {
  text-align: center;
  color: #94a3b8;
  font-size: 14px;
  margin-top: 30px;
}

/* --- BOTTOM NAV (COPY DARI DASHBOARD) --- */
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
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  align-items: center;
  padding: 0 10px;
}

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

/* REVISI: Active State untuk History */
.nav-item { 
  display: flex; flex-direction: column; align-items: center; gap: 4px; cursor: pointer; 
  color: #94A3B8; /* Warna teks abu-abu */
}

.nav-item img { 
  width: 24px; height: 24px; 
  /* Trik CSS: Paksa semua icon jadi warna abu-abu senada */
  filter: brightness(0) saturate(100%) invert(75%) sepia(11%) saturate(545%) hue-rotate(182deg) brightness(87%) contrast(85%);
  transition: all 0.2s ease;
}

.nav-item span { font-size: 10px; font-weight: 600; }

.nav-item.active { 
  color: #2563EB; /* Warna teks biru untuk yang aktif */
}

.nav-item.active img { 
  /* Trik CSS: Paksa icon yang aktif (Leave) jadi warna biru! */
  filter: brightness(0) saturate(100%) invert(26%) sepia(93%) saturate(3015%) hue-rotate(213deg) brightness(96%) contrast(97%);
}
.nav-item-scan-wrapper { display: flex; justify-content: center; align-items: center; height: 100%; }
.scan-button {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-radius: 16px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
}
.scan-button img {
  width: 24px;
  height: 24px;
  filter: brightness(0) invert(1);
}
</style>
