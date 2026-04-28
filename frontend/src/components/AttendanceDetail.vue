<template>
  <div class="screen-container">
    <!-- Header -->
    <div class="blue-header">
      <div class="header-content">
        <button class="btn-back" @click="$emit('go-back')">
          <ChevronLeft :size="20" color="white" />
        </button>
        <div class="header-titles">
          <h2>Detail Presensi</h2>
          <p>{{ formattedDate }}</p>
        </div>
        <div style="width: 40px"></div>
      </div>
    </div>

    <div class="content-area" v-if="attendance">
      <!-- Status + Waktu Card -->
      <div class="main-card">
        <div class="status-badge-container">
          <div class="status-badge" :class="statusClass">
            <span class="status-dot-indicator"></span>
            {{ statusLabel }}
          </div>
        </div>

        <div class="time-row">
          <div class="time-box">
            <span class="time-label">MASUK</span>
            <span class="time-value">{{
              formatTime(attendance?.attendanceRecord?.clock_in)
            }}</span>
          </div>
          <div class="time-divider">
            <div class="divider-line"></div>
            <div class="duration-badge" v-if="duration">{{ duration }}</div>
            <div class="divider-line"></div>
          </div>
          <div class="time-box">
            <span class="time-label">KELUAR</span>
            <span class="time-value">{{
              formatTime(attendance?.attendanceRecord?.clock_out)
            }}</span>
          </div>
        </div>
      </div>

      <!-- Lokasi & Catatan -->
      <div class="section-card">
        <h4 class="section-title">LOKASI & CATATAN</h4>

        <div class="info-row">
          <div class="info-left">
            <div class="icon-box">
              <MapPin :size="16" color="#3b82f6" />
            </div>
            <span class="row-label">Lokasi Masuk</span>
          </div>
          <span class="row-value">{{
            attendance?.attendanceRecord?.clock_in_location || "—"
          }}</span>
        </div>

        <div
          class="info-row"
          v-if="attendance?.attendanceRecord?.clock_out_location"
        >
          <div class="info-left">
            <div class="icon-box">
              <MapPinOff :size="16" color="#ef4444" />
            </div>
            <span class="row-label">Lokasi Keluar</span>
          </div>
          <span class="row-value">{{
            attendance?.attendanceRecord?.clock_out_location
          }}</span>
        </div>

        <div class="info-row">
          <div class="info-left">
            <div class="icon-box">
              <ClipboardList :size="16" color="#3b82f6" />
            </div>
            <span class="row-label">Rencana Kegiatan</span>
          </div>
          <span class="row-value">{{
            attendance?.attendanceRecord?.rencana_kegiatan || "—"
          }}</span>
        </div>

        <div
          class="info-row"
          v-if="attendance?.attendanceRecord?.progress_kegiatan"
        >
          <div class="info-left">
            <div class="icon-box">
              <CheckSquare :size="16" color="#3b82f6" />
            </div>
            <span class="row-label">Progress Kegiatan</span>
          </div>
          <span class="row-value">{{
            attendance?.attendanceRecord?.progress_kegiatan
          }}</span>
        </div>

        <div
          class="info-row"
          v-if="attendance?.attendanceRecord?.notes || attendance?.notes"
        >
          <div class="info-left">
            <div class="icon-box orange">
              <MessageSquare :size="16" color="#f59e0b" />
            </div>
            <span class="row-label">Keterangan</span>
          </div>
          <span class="row-value">{{
            attendance?.attendanceRecord?.notes || attendance?.notes
          }}</span>
        </div>
      </div>

      <!-- Bukti Presensi -->
      <div class="section-card">
        <h4 class="section-title">BUKTI PRESENSI</h4>

        <div
          class="photo-grid"
          v-if="
            attendance?.attendanceRecord?.clock_in_photo ||
            attendance?.attendanceRecord?.clock_out_photo ||
            attendance?.attendanceRecord?.evidence
          "
        >
          <div
            class="photo-card"
            v-if="attendance?.attendanceRecord?.clock_in_photo"
          >
            <span class="photo-label">📷 Foto Masuk</span>
            <img
              :src="getImageUrl(attendance.attendanceRecord.clock_in_photo)"
              alt="Foto Masuk"
              class="evidence-photo"
            />
          </div>
          <div
            class="photo-card"
            v-if="attendance?.attendanceRecord?.clock_out_photo"
          >
            <span class="photo-label">📷 Foto Keluar</span>
            <img
              :src="getImageUrl(attendance.attendanceRecord.clock_out_photo)"
              alt="Foto Keluar"
              class="evidence-photo"
            />
          </div>
          <div class="photo-card" v-if="attendance?.attendanceRecord?.evidence">
            <span class="photo-label">📎 Bukti Progress</span>
            <template
              v-if="attendance.attendanceRecord.evidence.endsWith('.pdf')"
            >
              <a
                :href="getImageUrl(attendance.attendanceRecord.evidence)"
                target="_blank"
                class="pdf-link"
                >Lihat PDF</a
              >
            </template>
            <template v-else>
              <img
                :src="getImageUrl(attendance.attendanceRecord.evidence)"
                alt="Bukti Progress"
                class="evidence-photo"
              />
            </template>
          </div>
        </div>

        <div class="no-photo" v-else>
          <ImageOff :size="40" color="#cbd5e1" />
          <p>Tidak ada foto / bukti tersedia</p>
        </div>
      </div>

      <!-- Footer -->
      <p class="footer-note">Data ini dicatat otomatis oleh sistem presensi.</p>
    </div>

    <!-- Empty State -->
    <div class="content-area" v-else>
      <div class="section-card empty-state">
        <AlertCircle :size="48" color="#cbd5e1" />
        <p>Data presensi tidak ditemukan.</p>
        <button @click="$emit('go-back')" class="retry-btn">Kembali</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  ChevronLeft,
  MapPin,
  MapPinOff,
  ClipboardList,
  CheckSquare,
  MessageSquare,
  ImageOff,
  AlertCircle,
} from "lucide-vue-next";

const props = defineProps({
  attendance: {
    type: Object,
    default: null,
  },
});

defineEmits(["go-back"]);

const formattedDate = computed(() => {
  if (!props.attendance?.fullDate) return "Tanggal tidak tersedia";
  const d = new Date(props.attendance.fullDate);
  return d.toLocaleDateString("id-ID", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
});

const statusLabel = computed(() => {
  const s = props.attendance?.status;
  if (!s) return "Hadir";
  const map = {
    present: "Hadir",
    late: "Terlambat",
    absent: "Alpha",
    leave: "Izin",
    hadir: "Hadir",
    terlambat: "Terlambat",
    alpha: "Alpha",
    izin: "Izin",
  };
  return map[s.toLowerCase()] || s;
});

const statusClass = computed(() => {
  const s = props.attendance?.status?.toLowerCase() || "present";
  if (s === "hadir" || s === "present") return "status-present";
  if (s === "terlambat" || s === "late") return "status-late";
  if (s === "alpha" || s === "absent") return "status-absent";
  if (s === "leave" || s === "izin") return "status-leave";
  return "status-present";
});

const duration = computed(() => {
  const ci = props.attendance?.attendanceRecord?.clock_in;
  const co = props.attendance?.attendanceRecord?.clock_out;
  if (!ci || !co) return null;
  const diffMs = new Date(co) - new Date(ci);
  if (diffMs <= 0) return null;
  const hrs = Math.floor(diffMs / 3600000);
  const mins = Math.floor((diffMs % 3600000) / 60000);
  return `${hrs}j ${mins}m`;
});

const formatTime = (dateTimeString) => {
  if (!dateTimeString) return "--:--";
  return new Date(dateTimeString).toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });
};

const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith("data:") || path.startsWith("http")) return path;
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace("/api", "");
  return `${baseUrl}/storage/${path}`;
};
</script>

<style scoped>
.screen-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: var(--bg-screen, #f8fafc);
  font-family: "Poppins", sans-serif;
  animation: slideInRight 0.3s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}

@keyframes slideInRight {
  from {
    transform: translateX(20px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* ===== HEADER ===== */
.blue-header {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  padding: 20px 20px 60px;
  border-bottom-left-radius: 36px;
  border-bottom-right-radius: 36px;
  color: white;
  flex-shrink: 0;
}

.header-content {
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
  font-size: 12px;
  margin: 3px 0 0;
  opacity: 0.8;
}

.btn-back {
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.back-icon {
  width: 20px;
  height: 20px;
  stroke: white;
  stroke-width: 2.5;
  fill: none;
}

/* ===== CONTENT AREA ===== */
.content-area {
  padding: 0 16px 32px;
  margin-top: -40px;
}

/* ===== MAIN CARD (Status + Waktu) ===== */
.main-card {
  background: var(--bg-card, #ffffff);
  border-radius: 24px;
  padding: 24px 20px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  margin-bottom: 16px;
  border: 1px solid var(--border-color, #e2e8f0);
}

.status-badge-container {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 16px;
  border-radius: 100px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
}

.status-dot-indicator {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
}

.status-present {
  background: rgba(34, 197, 94, 0.15);
  color: #16a34a;
}
.status-late {
  background: rgba(245, 158, 11, 0.15);
  color: #d97706;
}
.status-absent {
  background: rgba(239, 68, 68, 0.15);
  color: #dc2626;
}
.status-leave {
  background: rgba(168, 85, 247, 0.15);
  color: #9333ea;
}

/* Time Row */
.time-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 8px;
}

.time-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
}

.time-label {
  font-size: 10px;
  color: var(--text-muted, #64748b);
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 6px;
}

.time-value {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-main, #1e293b);
  letter-spacing: -1px;
}

.time-divider {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 0 12px;
}

.divider-line {
  width: 1px;
  height: 16px;
  background: var(--border-color, #e2e8f0);
}

.duration-badge {
  background: var(--bg-screen, #f8fafc);
  border: 1px solid var(--border-color, #e2e8f0);
  border-radius: 20px;
  padding: 3px 10px;
  font-size: 10px;
  font-weight: 700;
  color: var(--text-muted, #64748b);
  white-space: nowrap;
}

/* ===== SECTION CARD ===== */
.section-card {
  background: var(--bg-card, #ffffff);
  border-radius: 24px;
  padding: 20px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
  margin-bottom: 16px;
  border: 1px solid var(--border-color, #e2e8f0);
}

.section-title {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1px;
  color: var(--text-muted, #94a3b8);
  margin: 0 0 16px 0;
}

/* Info Row */
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid var(--border-color, #f1f5f9);
}

.info-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.info-row:first-of-type {
  padding-top: 0;
}

.info-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.row-label {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main, #1e293b);
}

.row-value {
  font-size: 13px;
  color: var(--text-muted, #64748b);
  font-weight: 500;
  max-width: 55%;
  text-align: right;
  word-break: break-word;
}

/* Icon Boxes */
.icon-box {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-box.blue {
  background: rgba(59, 130, 246, 0.12);
}
.icon-box.red {
  background: rgba(239, 68, 68, 0.12);
}
.icon-box.purple {
  background: rgba(168, 85, 247, 0.12);
}
.icon-box.green {
  background: rgba(34, 197, 94, 0.12);
}
.icon-box.orange {
  background: rgba(245, 158, 11, 0.12);
}

.row-icon {
  width: 16px;
  height: 16px;
  stroke-width: 2;
  fill: none;
}

.icon-box.blue .row-icon {
  stroke: #3b82f6;
}
.icon-box.red .row-icon {
  stroke: #ef4444;
}
.icon-box.purple .row-icon {
  stroke: #a855f7;
}
.icon-box.green .row-icon {
  stroke: #22c55e;
}
.icon-box.orange .row-icon {
  stroke: #f59e0b;
}

/* ===== BUKTI PRESENSI ===== */
.photo-grid {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.photo-card {
  flex: 0 0 calc(50% - 6px);
  background: var(--bg-screen, #f8fafc);
  border: 1px solid var(--border-color, #e2e8f0);
  border-radius: 16px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.photo-label {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted, #64748b);
  text-align: center;
}

.evidence-photo {
  width: 100%;
  height: 130px;
  object-fit: cover;
  border-radius: 10px;
}

.pdf-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 130px;
  background: var(--bg-card, #ffffff);
  color: #2563eb;
  font-weight: 600;
  border-radius: 10px;
  text-decoration: none;
  border: 1px dashed #93c5fd;
  font-size: 13px;
}

.no-photo {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px 0 8px;
  gap: 10px;
  color: var(--text-muted, #94a3b8);
}

.no-photo-icon {
  width: 40px;
  height: 40px;
  stroke: var(--text-muted, #cbd5e1);
  stroke-width: 1.5;
  fill: none;
}

.no-photo p {
  font-size: 13px;
  margin: 0;
}

/* ===== FOOTER ===== */
.footer-note {
  font-size: 11px;
  color: var(--text-muted, #94a3b8);
  text-align: center;
  font-style: italic;
  margin: 4px 0 16px;
}

/* ===== EMPTY STATE ===== */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 32px 20px;
  gap: 12px;
  text-align: center;
}

.empty-icon {
  width: 48px;
  height: 48px;
  stroke: var(--text-muted, #cbd5e1);
  stroke-width: 1.5;
  fill: none;
}

.empty-state p {
  font-size: 14px;
  color: var(--text-muted, #64748b);
  margin: 0;
}

.retry-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
}
</style>
