<template>
  <div class="screen-container">
    <div class="blue-header">
      <div class="header-content">
        <button class="btn-back" @click="$emit('go-back')">❮</button>
        <div class="header-titles">
          <h2>Detail Presensi</h2>
          <p>{{ formattedDate }}</p>
        </div>
        <div style="width: 24px"></div>
      </div>
    </div>

    <div class="content-area">
      <div v-if="attendance" class="settings-card">
        
        <div class="status-badge-container">
          <div class="status-badge" :class="attendance.status?.toLowerCase() || 'present'">
            {{ attendance.status || 'Present' }}
          </div>
        </div>
        
        <div class="time-row">
          <div class="time-box">
            <span class="label">Masuk</span>
            <span class="value">{{ formatTime(attendance?.attendanceRecord?.clock_in) }}</span>
          </div>
          <div class="vertical-divider"></div>
          <div class="time-box">
            <span class="label">Keluar</span>
            <span class="value">{{ formatTime(attendance?.attendanceRecord?.clock_out) }}</span>
          </div>
        </div>

        <div class="divider"></div>

        <p class="section-label">LOKASI & CATATAN</p>

        <div class="setting-row info">
          <div class="row-left">
            <div class="icon-box-new">
              <svg viewBox="0 0 24 24" class="icon-svg">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
            </div>
            <span class="menu-label">Lokasi</span>
          </div>
          <span class="info-text">{{ attendance.location || 'Kantor Pusat BPS' }}</span>
        </div>

        <div class="setting-row info">
          <div class="row-left">
            <div class="icon-box-new">
              <svg viewBox="0 0 24 24" class="icon-svg">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
            </div>
            <span class="menu-label">Keterangan</span>
          </div>
          <span class="info-text">{{ attendance.notes || '-' }}</span>
        </div>

        <div class="divider"></div>
        <p class="footer-note">Data ini dicatat otomatis oleh sistem presensi.</p>
      </div>

      <div v-else class="settings-card empty-state">
        <p class="menu-label">Ups! Data presensi tidak ditemukan.</p>
        <button @click="$emit('go-back')" class="retry-btn">Kembali</button>
      </div>

      <div v-if="attendance" class="info-section">
        <h4>LOKASI & CATATAN</h4>
        <div class="info-item">
          <span class="info-label">Lokasi Masuk</span>
          <span class="info-value">{{ attendance?.attendanceRecord?.clock_in_location || '-' }}</span>
        </div>
        <div class="info-item" v-if="attendance?.attendanceRecord?.clock_out_location">
          <span class="info-label">Lokasi Keluar</span>
          <span class="info-value">{{ attendance?.attendanceRecord?.clock_out_location }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Rencana Kegiatan</span>
          <span class="info-value">{{ attendance?.attendanceRecord?.rencana_kegiatan || '-' }}</span>
        </div>
        <div class="info-item" v-if="attendance?.attendanceRecord?.progress_kegiatan">
          <span class="info-label">Progress Kegiatan</span>
          <span class="info-value">{{ attendance?.attendanceRecord?.progress_kegiatan }}</span>
        </div>
      </div>

      <div v-if="attendance" class="info-section">
        <h4>BUKTI PRESENSI</h4>
        <div class="photo-grid" v-if="attendance?.attendanceRecord?.clock_in_photo || attendance?.attendanceRecord?.clock_out_photo || attendance?.attendanceRecord?.evidence">
          <div class="photo-card" v-if="attendance?.attendanceRecord?.clock_in_photo">
            <span class="photo-label">Foto Masuk</span>
            <img :src="getImageUrl(attendance.attendanceRecord.clock_in_photo)" alt="Foto Masuk" class="evidence-photo" />
          </div>
          <div class="photo-card" v-if="attendance?.attendanceRecord?.clock_out_photo">
            <span class="photo-label">Foto Keluar</span>
            <img :src="getImageUrl(attendance.attendanceRecord.clock_out_photo)" alt="Foto Keluar" class="evidence-photo" />
          </div>
          <div class="photo-card" v-if="attendance?.attendanceRecord?.evidence">
            <span class="photo-label">Bukti Progress</span>
            <template v-if="attendance.attendanceRecord.evidence.endsWith('.pdf')">
               <a :href="getImageUrl(attendance.attendanceRecord.evidence)" target="_blank" class="pdf-link">Lihat PDF</a>
            </template>
            <template v-else>
               <img :src="getImageUrl(attendance.attendanceRecord.evidence)" alt="Bukti Progress" class="evidence-photo" />
            </template>
          </div>
        </div>
        <div class="info-item" v-else>
          <span class="info-label">Foto Absen</span>
          <span class="info-value">Tidak ada foto / bukti</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
  attendance: {
    type: Object,
    default: null
  }
});

const formattedDate = computed(() => {
  if (!props.attendance?.fullDate) return 'Tanggal tidak tersedia';
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  const d = new Date(props.attendance.fullDate);
  return d.toLocaleDateString('id-ID', options);
});

const formatTime = (dateTimeString) => {
  if (!dateTimeString) return '--:--';
  const date = new Date(dateTimeString);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
};

const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('data:') || path.startsWith('http')) return path;
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace("/api", "");
  return `${baseUrl}/storage/${path}`;
};

// Ini untuk fungsi tombol kembali
defineEmits(['go-back']);
</script>

<style scoped>
.screen-container {
  background-color: #f8fafc;
  min-height: 100vh;
  animation: slideInRight 0.3s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}
@keyframes slideInRight {
  from { transform: translateX(30%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}
.blue-header {
  background-color: #2563eb;
  padding: 30px 20px 60px;
  border-bottom-left-radius: 30px;
  border-bottom-right-radius: 30px;
  color: white;
}
.header-top {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: var(--bg-screen);
}

/* HEADER STYLE - Konsisten dengan App Settings */
.blue-header {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  height: 140px;
  border-bottom-left-radius: 40px;
  border-bottom-right-radius: 40px;
  padding: 20px 25px;
  color: white;
  flex-shrink: 0;
}

.content { padding: 0 20px; margin-top: -30px; }

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.detail-card {
  background: white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  text-align: center;
  opacity: 0;
  animation: fadeInUp 0.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}
.status-badge {
  display: inline-block;
  padding: 6px 16px;
  border-radius: 100px;
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 20px;
  text-transform: uppercase;
}
.present { background: #dcfce7; color: #166534; }
.absent { background: #fee2e2; color: #991b1b; }

.time-row { display: flex; justify-content: space-around; align-items: center; }
.time-box { display: flex; flex-direction: column; }
.label { font-size: 12px; color: #64748b; margin-bottom: 5px; }
.value { font-size: 22px; font-weight: 700; color: #1e293b; }
.divider { width: 1px; height: 40px; background: #e2e8f0; }

.info-section { 
  margin-top: 30px; 
  opacity: 0;
  animation: fadeInUp 0.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
  animation-delay: 0.1s;
}
.info-section h4 { font-size: 12px; color: #64748b; margin-bottom: 15px; letter-spacing: 1px; }
.info-item {
  background: white;
  padding: 15px;
  border-radius: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
}

.header-titles {
  text-align: center;
}

.header-titles h2 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.header-titles p {
  font-size: 13px;
  margin: 2px 0 0;
  opacity: 0.8;
}

.btn-back {
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 8px 16px;
  border-radius: 12px;
  cursor: pointer;
}

/* CONTENT AREA */
.content-area {
  flex: 1;
  padding: 0 20px;
  margin-top: -50px;
  overflow-y: auto;
}

/* CARD STYLE */
.settings-card {
  background-color: var(--bg-card) !important;
  border-radius: 24px;
  padding: 25px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  margin-bottom: 20px;
}

/* STATUS BADGE */
.status-badge-container {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 100px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.present { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
.absent { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

/* TIME ROW */
.time-row {
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: 10px 0;
}

.time-box {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.label {
  font-size: 10px;
  color: var(--text-muted);
  font-weight: 700;
  margin-bottom: 5px;
}

.value {
  font-size: 24px;
  font-weight: 800;
  color: var(--text-main);
}

.vertical-divider {
  width: 1px;
  height: 40px;
  background: var(--border-color);
}

/* SECTION LABEL & ROWS */
.section-label {
  color: var(--text-muted);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  margin: 15px 0 10px;
}

.setting-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
}

.row-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.menu-label {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
}

.info-text {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
  max-width: 60%;
  text-align: right;
}

/* ICON STYLING */
.icon-box-new {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background-color: rgba(59, 130, 246, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-svg {
  width: 18px;
  height: 18px;
  stroke: #3b82f6;
  stroke-width: 2;
  fill: none;
}

/* DIVIDER & FOOTER */
.divider {
  height: 1px;
  background: var(--border-color);
  margin: 20px 0;
}

.footer-note {
  font-size: 11px;
  color: var(--text-muted);
  text-align: center;
  font-style: italic;
}

/* EMPTY STATE BUTTON */
.retry-btn {
  margin-top: 15px;
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
}

.empty-state {
  text-align: center;
}
.info-label { color: #94a3b8; font-size: 14px; }
.info-value { color: #1e293b; font-size: 14px; font-weight: 500; text-align: right; }

.photo-grid {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
  overflow-x: auto;
  padding-bottom: 5px;
}
.photo-card {
  flex: 0 0 calc(50% - 7.5px);
  background: white;
  border-radius: 15px;
  padding: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 4px 10px rgba(0,0,0,0.02);
}
.photo-label {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 10px;
  text-align: center;
}
.evidence-photo {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}
.pdf-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 120px;
  background-color: #f1f5f9;
  color: #2563eb;
  font-weight: 600;
  border-radius: 10px;
  text-decoration: none;
  border: 1px solid #e2e8f0;
  font-size: 13px;
}
.pdf-link:hover {
  background-color: #e2e8f0;
}
</style>