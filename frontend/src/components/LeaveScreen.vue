<script setup>
import { ref } from 'vue'
import api from '../api/axios.js'

const emit = defineEmits(['go-back', 'navigate'])

const selectedType = ref('sakit')
const startDate = ref('')
const endDate = ref('')
const reason = ref('')
const fileInput = ref(null)
const selectedFileName = ref('')

const triggerFileInput = () => {
  fileInput.value.click()
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFileName.value = file.name
  }
}

const submitLeave = async () => {
  if (!startDate.value || !endDate.value || !reason.value) {
    alert('Harap lengkapi rentang tanggal dan alasan!')
    return
  }

  const formData = new FormData()
  formData.append('start_date', startDate.value)
  formData.append('end_date', endDate.value)
  formData.append('type', selectedType.value)
  formData.append('reason', reason.value)
  
  if (fileInput.value && fileInput.value.files[0]) {
    formData.append('file', fileInput.value.files[0])
  }

  try {
    const res = await api.post('/leave', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    console.log('[Leave] Submit leave sukses:', res.data)
    alert('Pengajuan Izin Berhasil Dikirim!')
    emit('navigate', 'dashboard')
  } catch (error) {
    console.error('[Leave] Gagal submit:', error)
    alert('Gagal mengirim pengajuan izin.')
  }
}
</script>

<template>
  <div class="screen-container">
    <div class="header">
      <img src="../assets/back.png" class="back-icon" @click="$emit('navigate', 'dashboard')" alt="Back" />
      <h2 class="title">Pengajuan Izin</h2>
      <div class="spacer"></div>
    </div>

    <div class="content">
      <div class="info-alert">
        <img src="../assets/i.png" class="info-icon" alt="Info" />
        <p>Silakan lengkapi formulir di bawah untuk mengajukan izin. Pastikan bukti terlampir untuk kategori Sakit.</p>
      </div>

      <div class="form-group">
        <label class="section-label">JENIS IZIN</label>
        <div class="type-container">
          <div :class="['type-card', selectedType === 'sakit' ? 'active' : '']" @click="selectedType = 'sakit'">
            <img src="../assets/sakit.png" class="type-icon" alt="Sakit" />
            <span>Sakit</span>
          </div>
          <div :class="['type-card', selectedType === 'izin' ? 'active' : '']" @click="selectedType = 'izin'">
            <img src="../assets/izin.png" class="type-icon" alt="Izin" />
            <span>Izin</span>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="section-label">RENTANG TANGGAL</label>
        <div class="date-row">
          <div class="date-input-group">
            <span class="date-label">Mulai</span>
            <input type="date" v-model="startDate" class="date-input" />
          </div>
          <div class="date-input-group">
            <span class="date-label">Selesai</span>
            <input type="date" v-model="endDate" class="date-input" />
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="section-label">ALASAN</label>
        <textarea v-model="reason" class="reason-input" placeholder="Tuliskan alasan pengajuan Anda secara detail..."></textarea>
      </div>

      <div class="form-group">
        <label class="section-label">UPLOAD BUKTI (OPSIONAL)</label>
        <div class="upload-box" @click="triggerFileInput">
          <img src="../assets/pilih file.png" class="upload-icon" alt="Upload" />
          <p class="upload-text">
            Lampirkan foto atau dokumen pendukung<br>
            <span class="upload-subtext">(Format PDF/JPG, Max 5MB)</span>
          </p>
          <button class="btn-pilih-file">{{ selectedFileName ? 'Ubah File' : 'Pilih File' }}</button>
          <p v-if="selectedFileName" class="file-name">{{ selectedFileName }}</p>
          <input type="file" ref="fileInput" class="hidden-input" @change="handleFileUpload" accept=".pdf,.jpg,.jpeg,.png" />
        </div>
      </div>
      
      <div class="submit-container">
        <button class="btn-submit" @click="submitLeave">
          Kirim Pengajuan
          <img src="../assets/paper airplane.png" class="send-icon" alt="Send" />
        </button>
      </div>
    </div>

    <div class="bottom-nav">
      <div class="nav-item" @click="$emit('navigate', 'dashboard')">
        <img src="../assets/home.png" alt="Home">
        <span>Home</span>
      </div>
      <div class="nav-item" @click="$emit('navigate', 'history')">
        <img src="../assets/history.png" alt="History">
        <span>History</span>
      </div>
      
      <div class="nav-item scan-btn" @click="$emit('navigate', 'scan')">
        <div class="scan-circle">
          <img src="../assets/qr.png" alt="Scan QR" class="qr-icon">
        </div>
      </div>
      
      <div class="nav-item active" @click="$emit('navigate', 'leave')">
        <img src="../assets/leave.png" alt="Leave">
        <span>Leave</span>
      </div>
      <div class="nav-item" @click="$emit('navigate', 'profile')">
        <img src="../assets/profile.png" alt="Profile"> 
        <span>Profile</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 1. PERBAIKAN STRUKTUR LAYOUT UTAMA */
.screen-container {
  display: flex;
  flex-direction: column;
  height: 100vh; /* Kunci tinggi layar */
  background-color: var(--bg-app);
  overflow: hidden; /* Matikan scroll di layar utama */
  transition: background-color 0.3s ease;
}

/* Header tidak ikut scroll */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  background-color: var(--bg-app);
  flex-shrink: 0;
  transition: background-color 0.3s ease;
}

/* 2. AREA TENGAH YANG BISA DI-SCROLL */
/* 2. AREA TENGAH YANG BISA DI-SCROLL */
.content {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  padding-bottom: 40px;
  
  /* --- TAMBAHKAN INI UNTUK HILANGKAN GARIS HITAM/SCROLLBAR --- */
  -ms-overflow-style: none;  /* Untuk Internet Explorer & Edge */
  scrollbar-width: none;     /* Untuk Firefox */
}

/* Untuk Chrome, Safari, dan Opera */
.content::-webkit-scrollbar {
  display: none;
}

/* Styling Komponen Lainnya */
.back-icon { width: 24px; height: 24px; cursor: pointer; }
.title { font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0; }
.spacer { width: 24px; }

.info-alert {
  display: flex; align-items: flex-start;
  background-color: #EFF6FF; border-radius: 12px;
  padding: 12px 16px; margin-bottom: 24px;
}
.info-icon { width: 20px; height: 20px; margin-right: 12px; margin-top: 2px; }
.info-alert p { margin: 0; font-size: 13px; color: #3B82F6; line-height: 1.5; text-align: left; width: 100%; }

.form-group { margin-bottom: 24px; }
.section-label {
  display: block; font-size: 12px; font-weight: 700; color: #64748B;
  margin-bottom: 12px; letter-spacing: 0.5px; text-align: left;
}

.type-container { display: flex; gap: 16px; }
.type-card {
  flex: 1; display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 16px; background-color: var(--bg-card);
  border: 1px solid var(--border-color); border-radius: 16px; cursor: pointer; transition: all 0.2s;
}
.type-card.active { border: 2px solid var(--accent-primary); background-color: var(--surface-info); }
.type-icon { width: 30px; height: 35px; margin-bottom: 8px; }
.type-card span { font-size: 14px; font-weight: 600; color: var(--text-muted); }
.type-card.active span { color: var(--accent-primary); }

.date-row { display: flex; gap: 16px; }
.date-input-group { flex: 1; }
.date-label { display: block; font-size: 12px; color: #94A3B8; margin-bottom: 4px; text-align: left; }
.date-input { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 12px; font-family: inherit; font-size: 14px; color: var(--text-main); box-sizing: border-box; background-color: var(--bg-input); transition: all 0.3s ease; }

.reason-input { width: 100%; height: 100px; padding: 16px; border: 1px solid var(--border-color); border-radius: 12px; font-family: inherit; font-size: 14px; color: var(--text-main); resize: none; box-sizing: border-box; background-color: var(--bg-input); transition: all 0.3s ease; }
.reason-input::placeholder { color: #94A3B8; }

.upload-box { border: 2px dashed var(--input-border); border-radius: 16px; padding: 24px 16px; text-align: center; background-color: var(--bg-card); cursor: pointer; transition: all 0.2s; }
.upload-box:hover { background-color: var(--bg-input); }
.upload-icon { width: 40px; height: 40px; margin-bottom: 12px; }
.upload-text { font-size: 13px; color: #64748B; margin: 0 0 16px 0; line-height: 1.5; }
.upload-subtext { font-size: 11px; color: #94A3B8; }
.btn-pilih-file { background-color: transparent; border: 1px solid #3B82F6; color: #3B82F6; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; }
.hidden-input { display: none; }
.file-name { margin-top: 12px; font-size: 12px; color: #10B981; font-weight: 600; }

.submit-container { margin-top: 10px; }
.btn-submit { width: 100%; background-color: #2563EB; color: white; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 600; display: flex; justify-content: center; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
.send-icon { width: 20px; height: 20px; }

/* 3. NAVBAR FIX TERKUNCI DI BAWAH */
/* --- BOTTOM NAV (PENYELARASAN LEAVE) --- */
.bottom-nav {
  flex-shrink: 0;
  width: 100%;
  /* 1. Samakan tinggi dengan Dashboard & History */
  height: 75px; 
  background-color: #ffffff;
  
  /* 2. Tambahkan lengkungan khas Dashboard */
  border-top-left-radius: 30px;
  border-top-right-radius: 30px;
  
  /* 3. Layout Grid agar presisi 5 kolom */
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  align-items: center;
  
  /* 4. Shadow halus ke arah atas */
  box-shadow: 0 -8px 25px rgba(0,0,0,0.06);
  z-index: 100;
  padding: 0 5px;
}

.nav-item { 
  display: flex; 
  flex-direction: column; 
  align-items: center; 
  justify-content: center;
  gap: 4px; 
  cursor: pointer; 
  color: #94A3B8; 
  height: 100%;
}

.nav-item img { 
  width: 24px; 
  height: 24px; 
  object-fit: contain;
  filter: brightness(0) saturate(100%) invert(75%) sepia(11%) saturate(545%) hue-rotate(182deg) brightness(87%) contrast(85%);
  transition: all 0.2s ease;
}

.nav-item span { 
  font-size: 10px; 
  font-weight: 600; 
}

/* Keadaan Aktif (Tab Leave sedang terpilih) */
.nav-item.active { 
  color: #2563EB; 
}

.nav-item.active img { 
  filter: brightness(0) saturate(100%) invert(26%) sepia(93%) saturate(3015%) hue-rotate(213deg) brightness(96%) contrast(97%);
}

/* --- TOMBOL QR (SQUIRCLE) --- */
.nav-item.scan-btn {
  display: flex;
  justify-content: center;
  align-items: center;
}

.scan-circle {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  border-radius: 16px; /* Squircle identik dengan Dashboard */
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
  transition: transform 0.2s;
}

.scan-circle:active {
  transform: scale(0.92);
}

.scan-button {
  /* Hapus absolute agar tidak mengambang */
  position: static; 
  
  width: 50px; height: 50px; 
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  border-radius: 16px; 
  display: flex; justify-content: center; align-items: center;
  box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
  border: none; cursor: pointer;
  margin: 0; /* Pastikan tidak ada margin aneh */
}
.scan-circle .qr-icon { 
  width: 26px; 
  height: 26px; 
  filter: brightness(0) invert(1) !important; 
}
</style>