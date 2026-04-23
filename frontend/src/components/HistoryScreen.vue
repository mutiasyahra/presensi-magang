<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../api/axios";

const emit = defineEmits(["navigate", "go-back"]);

const getImageUrl = (name) => {
  return new URL(`../assets/${name}`, import.meta.url).href;
};

const historyData = ref([]);

const fetchHistoryData = async () => {
  try {
    const res = await api.get("/calendar");
    if (res.data && res.data.attendance) {
      const attendances = res.data.attendance;
      const historyList = [];

      const todayStr = new Date().toDateString();

      attendances.forEach(att => {
        const attDateStr = new Date(att.attendance_date).toDateString();
        const isEditable = attDateStr === todayStr;

        if (att.clock_in) {
          const inDate = new Date(att.clock_in);
          
          let label = (att.clock_in_status || 'tepat waktu').toUpperCase();
          let icon = "icon present.png";
          if (att.clock_in_status === 'terlambat') {
             icon = "icon late.png";
          }
          
          historyList.push({
            id: att.id + "-in",
            attendanceId: att.id,
            rawDate: inDate,
            day: inDate.toLocaleDateString("en-US", { weekday: "long" }),
            date: inDate.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }),
            time: inDate.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" }),
            status: label,
            type: "clock-in",
            isEditable: isEditable,
            location: att.clock_in_location || null,
            noteTitle: "DAILY WORK PLAN",
            note: att.rencana_kegiatan || "-",
            icon: icon,
            noteIcon: "daily plan.png",
          });
        }
        
        if (att.clock_out) {
          const outDate = new Date(att.clock_out);
          let label = (att.clock_out_status || 'tepat waktu').toUpperCase();

          historyList.push({
            id: att.id + "-out",
            attendanceId: att.id,
            rawDate: outDate,
            day: outDate.toLocaleDateString("en-US", { weekday: "long" }),
            date: outDate.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }),
            time: outDate.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" }),
            status: label,
            type: "clock-out",
            isEditable: isEditable,
            location: att.clock_out_location || null,
            noteTitle: "DAILY ACTIVITY",
            note: att.progress_kegiatan || "-",
            icon: "icon done.png",
            noteIcon: "daily activity.png",
          });
        }
      });
      
      historyList.sort((a, b) => b.rawDate - a.rawDate);
      historyData.value = historyList;
    }
  } catch (err) {
    console.error("Gagal mengambil data history:", err);
  }
};

onMounted(() => {
  fetchHistoryData();
});

const activeTab = ref("All");
const searchQuery = ref("");
const showRangeModal = ref(false);
const selectedRange = ref("7 Hari Terakhir"); // Default: Last 7 Days
const customStartDate = ref("");
const customEndDate = ref("");
const selectedMonth = ref(new Date().toISOString().slice(0, 7)); // Default: Current month YYYY-MM

// Temp state for modal
const tempRange = ref("7 Hari Terakhir");
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
};

// --- LOGIC FILTER ---
const filteredHistory = computed(() => {
  let list = historyData.value;

  // 1. Filter by Tab (Clock In / Clock Out)
  if (activeTab.value !== "All") {
    const filterType = activeTab.value === "Clock In" ? "clock-in" : "clock-out";
    list = list.filter((item) => item.type === filterType);
  }

  // 2. Filter by Search Query
  if (searchQuery.value.trim() !== "") {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((item) => 
      item.note.toLowerCase().includes(q) || 
      item.location?.toLowerCase().includes(q) ||
      item.day.toLowerCase().includes(q) ||
      item.date.toLowerCase().includes(q)
    );
  }

  // 3. Filter by Date Range
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  if (selectedRange.value === "Hari ini") {
    list = list.filter(item => {
      const d = new Date(item.rawDate);
      return d.toDateString() === today.toDateString();
    });
  } else if (selectedRange.value === "7 Hari Terakhir") {
    const sevenDaysAgo = new Date(today);
    sevenDaysAgo.setDate(today.getDate() - 7);
    list = list.filter(item => new Date(item.rawDate) >= sevenDaysAgo);
  } else if (selectedRange.value === "Pilih Bulan") {
    const [year, month] = selectedMonth.value.split("-").map(Number);
    list = list.filter(item => {
      const d = new Date(item.rawDate);
      return d.getFullYear() === year && (d.getMonth() + 1) === month;
    });
  } else if (selectedRange.value === "Pilih Tanggal") {
    if (customStartDate.value && customEndDate.value) {
      const start = new Date(customStartDate.value);
      start.setHours(0, 0, 0, 0);
      const end = new Date(customEndDate.value);
      end.setHours(23, 59, 59, 999);
      list = list.filter(item => {
        const d = new Date(item.rawDate);
        return d >= start && d <= end;
      });
    }
  }

  return list;
});

const getStatusClass = (status) => {
  const s = status.toUpperCase();
  if (s === "TEPAT WAKTU") return "status-present";
  if (s === "TERLAMBAT") return "status-late";
  if (s === "TERLALU CEPAT") return "status-warning";
  return "";
};
</script>

<template>
  <div class="screen-container">
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
            <input type="text" v-model="searchQuery" placeholder="Search..." />
          </div>
          <button class="btn-range" @click="openRangeModal">
            <div class="range-content">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="filter-icon"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
              <span>{{ selectedRange }}</span>
            </div>
          </button>
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
                <img :src="getImageUrl(item.icon)" alt="Icon" />
              </div>

              <div class="card-info">
                <span class="day-name">{{ item.day }}</span>
                <div class="time-row">
                  <img src="../assets/jam.png" alt="clock" class="clock-icon" />
                  <span class="date-text"
                    >{{ item.date }} • {{ item.time }}</span
                  >
                </div>
                <!-- Menampilkan Lokasi jika ada -->
                <div v-if="item.location" class="location-row">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="location-icon"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                  <span class="location-text">{{ item.location }}</span>
                </div>
              </div>

              <div class="card-actions">
                <span class="badge" :class="getStatusClass(item.status)">{{
                  item.status
                }}</span>
                <button v-if="item.isEditable" class="btn-edit" @click="$emit('navigate', 'edit-attendance', item.type === 'clock-in' ? 'in' : 'out', item.attendanceId)">
                <img src="../assets/pencil.png" alt="Edit" />
                </button>
              </div>
            </div>

            <div class="dashed-box">
              <div class="dashed-header">
                <img :src="getImageUrl(item.noteIcon)" alt="Note Icon" />
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

        <div class="nav-item-scan-wrapper" @click="$emit('navigate', 'qr-scan')">
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

      <!-- RANGE MODAL -->
      <transition name="fade">
        <div v-if="showRangeModal" class="modal-overlay" @click.self="showRangeModal = false">
          <div class="modal-content">
            <div class="modal-header">
              <button class="btn-close-modal" @click="showRangeModal = false">❮</button>
              <h3>Rentang Waktu</h3>
              <div style="width: 24px"></div>
            </div>

            <div class="modal-body">
              <div class="range-options">
                <label class="range-option">
                  <span>Hari ini</span>
                  <input type="radio" value="Hari ini" v-model="tempRange" />
                  <span class="radio-custom"></span>
                </label>

                <label class="range-option">
                  <span>7 Hari Terakhir</span>
                  <input type="radio" value="7 Hari Terakhir" v-model="tempRange" />
                  <span class="radio-custom"></span>
                </label>

                <label class="range-option">
                  <div class="option-with-input">
                    <span>Pilih Bulan</span>
                    <input v-if="tempRange === 'Pilih Bulan'" type="month" v-model="tempMonth" class="inline-date-input" />
                  </div>
                  <input type="radio" value="Pilih Bulan" v-model="tempRange" />
                  <span class="radio-custom"></span>
                </label>

                <label class="range-option">
                  <div class="option-with-input">
                    <span>Pilih Tanggal</span>
                    <div v-if="tempRange === 'Pilih Tanggal'" class="date-range-inputs">
                      <input type="date" v-model="tempStartDate" class="inline-date-input" />
                      <span class="to-text">to</span>
                      <input type="date" v-model="tempEndDate" class="inline-date-input" />
                    </div>
                  </div>
                  <input type="radio" value="Pilih Tanggal" v-model="tempRange" />
                  <span class="radio-custom"></span>
                </label>
              </div>

              <div class="modal-footer">
                <button class="btn-apply" @click="applyRange">Terapkan</button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

/* --- MAIN LAYOUT (Sama seperti Dashboard) --- */
.screen-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
background-color: var(--bg-screen);
  overflow: hidden;
  font-family: "Inter", sans-serif;
  position: relative;
  transition: background-color 0.3s ease;
}

/* --- TOP FIXED AREA --- */
.top-fixed-area {
  background-color: var(--bg-screen);
  padding: 20px 20px 0 20px;
  z-index: 10;
  flex-shrink: 0;
  transition: background-color 0.3s ease;
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
  color: var(--text-main);
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
  background-color: var(--bg-card); /* Sudah benar, pastikan variabel terdefinisi */
  border: 1px solid var(--border-color); /* GANTI dari #e2e8f0 */
  border-radius: 12px;
  padding: 12px 12px 12px 45px;
  font-size: 14px;
  color: var(--text-main); /* GANTI agar teks input terbaca */
  outline: none;
  transition: all 0.3s ease;
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  opacity: 0.5;
}

/* Search Container Layout */
.search-container {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}
.search-box {
  flex: 1;
  position: relative;
}

.btn-range {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 0 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}
.range-content {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-main);
  font-size: 13px;
  font-weight: 600;
}
.filter-icon {
  color: #2563eb;
}

/* --- MODAL STYLES --- */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 480px;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
  align-items: flex-end;
}
.modal-content {
  background: var(--bg-app);
  width: 100%;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  padding: 24px;
  box-sizing: border-box; /* Pastikan padding tidak menambah lebar */
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

@media screen and (max-width: 480px) {
  .modal-overlay {
    max-width: 100%;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: var(--text-main);
}
.btn-close-modal {
  background: none;
  border: none;
  font-size: 20px;
  color: var(--text-main);
  cursor: pointer;
}

.range-options {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 32px;
}
.range-option {
  display: flex;
  justify-content: space-between;
  align-items: flex-start; /* Alihkan ke atas agar sejajar dengan baris pertama */
  padding: 16px 0;
  cursor: pointer;
  border-bottom: 1px solid var(--border-color);
  position: relative;
}
.range-option span {
  font-size: 15px;
  color: var(--text-main);
  font-weight: 500;
  line-height: 24px; /* Samakan dengan tinggi radio button untuk alignment */
}

/* Radio Customization */
.range-option input[type="radio"] {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}
.radio-custom {
  height: 22px;
  width: 22px;
  background-color: transparent;
  border: 2px solid #cbd5e1;
  border-radius: 50%;
  display: flex; /* Gunakan flex untuk centering dot */
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 1px; /* Halus penyesuaian agar pas dengan teks */
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.range-option input:checked ~ .radio-custom {
  border-color: #2563eb;
  background-color: white;
}
.range-option input:checked ~ .radio-custom::after {
  content: "";
  display: block;
  width: 12px;
  height: 12px;
  background: #2563eb;
  border-radius: 50%;
}

.option-with-input {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}
.inline-date-input {
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 8px;
  font-size: 13px;
  background: var(--bg-card);
  color: var(--text-main);
  outline: none;
}
.date-range-inputs {
  display: flex;
  align-items: center;
  gap: 8px;
}
.to-text {
  font-size: 12px;
  color: var(--text-muted);
}

.modal-footer {
  margin-top: 10px;
}
.btn-apply {
  width: 100%;
  background: #2563eb;
  color: white;
  border: none;
  padding: 16px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

/* Fade Transition */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
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
  border: 1px solid var(--border-color); /* GANTI dari #e2e8f0 */
  background-color: var(--bg-card);
  color: var(--text-muted); /* GANTI dari #64748b */
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
  background-color: var(--bg-card);
  border-radius: 16px;
  padding: 16px;
  margin-bottom: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  border-left: 5px solid transparent;
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}
.history-card.status-present {
  border-left-color: #22c55e;
}
.history-card.status-late {
  border-left-color: #ef4444;
}
.history-card.status-warning {
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
  color: var(--text-main);
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
  color: var(--text-muted);
  font-weight: 500;
}

.location-row {
  display: flex;
  align-items: flex-start;
  gap: 4px;
  margin-top: 2px;
}
.location-icon {
  color: #ef4444;
  margin-top: 2px;
  flex-shrink: 0;
}
.location-text {
  font-size: 11px;
  color: #64748b;
  line-height: 1.3;
  text-align: left;
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
.badge.status-late {
  background: #fef2f2;
  color: #991b1b;
}
.badge.status-warning {
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
  border: 1.5px dashed var(--border-color); /* GANTI dari #e2e8f0 */
  border-radius: 12px;
  padding: 12px;
  background: var(--bg-screen); /* GANTI dari #f8fafc agar lebih gelap dari card */
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
  color: var(--text-muted); /* GANTI dari #475569 */
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
/* --- BOTTOM NAV (PENYELARASAN HISTORY) --- */
.bottom-nav {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: var(--bg-card); /* GANTI dari white */
  height: 75px; 
  border-top-left-radius: 30px;
  border-top-right-radius: 30px;
  border-top: 1px solid var(--border-color); /* Tambahkan border tipis */
  box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.2); /* Gelapkan shadow sedikit */
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  align-items: center; 
  padding: 0 5px;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  color: #94A3B8; /* Standar warna pasif */
  font-size: 10px;
  font-weight: 600;
  cursor: pointer;
  height: 100%;
  transition: all 0.2s ease;
}

.nav-item img {
  width: 24px;
  height: 24px;
  object-fit: contain;
  /* Filter abu-abu pasif */
  filter: brightness(0) saturate(100%) invert(75%) sepia(11%) saturate(545%) hue-rotate(182deg) brightness(87%) contrast(85%);
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

/* Keadaan Aktif di Halaman History */
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
  border-radius: 16px; 
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
  border: none;
  cursor: pointer;
}

.scan-button img {
  width: 26px;
  height: 26px;
  filter: brightness(0) invert(1) !important;
}

.dark .btn-edit img,
.dark .clock-icon,
.dark .search-icon {
  filter: brightness(0) invert(1);
}
</style>