<template>
  <div class="screen-container">
    <div class="blue-header">
      <div class="header-top">
        <button class="back-btn" @click="$emit('go-back')">
          <span class="chevron">‹</span>
        </button>
        <div class="header-titles">
          <h2>Detail Presensi</h2>
          <p>{{ attendance?.date || 'Tanggal tidak tersedia' }}</p>
        </div>
      </div>
    </div>

    <div class="content">
      <div v-if="attendance" class="detail-card">
        <div class="status-badge" :class="attendance.status?.toLowerCase() || 'present'">
          {{ attendance.status || 'Present' }}
        </div>
        
        <div class="time-row">
          <div class="time-box">
            <span class="label">Masuk</span>
            <span class="value">{{ attendance.clock_in || '--:--' }}</span>
          </div>
          <div class="divider"></div>
          <div class="time-box">
            <span class="label">Keluar</span>
            <span class="value">{{ attendance.clock_out || '--:--' }}</span>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <p>Ups! Data presensi tidak ditemukan.</p>
        <button @click="$emit('go-back')" class="retry-btn">Kembali</button>
      </div>

      <div v-if="attendance" class="info-section">
        <h4>LOKASI & CATATAN</h4>
        <div class="info-item">
          <span class="info-label">Lokasi</span>
          <span class="info-value">{{ attendance.location || 'Kantor Pusat BPS' }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Keterangan</span>
          <span class="info-value">{{ attendance.notes || '-' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

// Ini 'tangan' untuk menerima paket data dari App.vue
const props = defineProps({
  attendance: {
    type: Object,
    default: null
  }
});

// Ini untuk fungsi tombol kembali
defineEmits(['go-back']);
</script>

<style scoped>
.screen-container {
  background-color: #f8fafc;
  min-height: 100vh;
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
  align-items: center;
  gap: 15px;
}
.back-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  font-size: 24px;
  cursor: pointer;
}
.header-titles h2 { margin: 0; font-size: 18px; }
.header-titles p { margin: 0; font-size: 14px; opacity: 0.8; }

.content { padding: 0 20px; margin-top: -30px; }

.detail-card {
  background: white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  text-align: center;
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

.info-section { margin-top: 30px; }
.info-section h4 { font-size: 12px; color: #64748b; margin-bottom: 15px; letter-spacing: 1px; }
.info-item {
  background: white;
  padding: 15px;
  border-radius: 15px;
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.info-label { color: #94a3b8; font-size: 14px; }
.info-value { color: #1e293b; font-size: 14px; font-weight: 500; }
</style>