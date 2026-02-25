<script setup>
import { ref } from 'vue'

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

const submitLeave = () => {
  if (!startDate.value || !endDate.value || !reason.value) {
    alert('Harap lengkapi rentang tanggal dan alasan!')
    return
  }
  alert('Pengajuan Izin Berhasil Dikirim! (Simulasi)')
  emit('navigate', 'dashboard')
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
  background-color: #F8FAFC;
  overflow: hidden; /* Matikan scroll di layar utama */
}

/* Header tidak ikut scroll */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  background-color: white;
  flex-shrink: 0;
}

/* 2. AREA TENGAH YANG BISA DI-SCROLL */
.content {
  flex: 1; /* Penuhi sisa layar antara header dan navbar */
  overflow-y: auto; /* Hanya area ini yang bisa di-scroll */
  padding: 20px;
  padding-bottom: 40px; /* Jarak aman sebelum mentok navbar */
}

/* Styling Komponen Lainnya */
.back-icon { width: 24px; height: 24px; cursor: pointer; }
.title { font-size: 18px; font-weight: 700; color: #1E293B; margin: 0; }
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
  justify-content: center; padding: 16px; background-color: white;
  border: 1px solid #E2E8F0; border-radius: 16px; cursor: pointer; transition: all 0.2s;
}
.type-card.active { border: 2px solid #3B82F6; background-color: #EFF6FF; }
.type-icon { width: 30px; height: 35px; margin-bottom: 8px; }
.type-card span { font-size: 14px; font-weight: 600; color: #64748B; }
.type-card.active span { color: #3B82F6; }

.date-row { display: flex; gap: 16px; }
.date-input-group { flex: 1; }
.date-label { display: block; font-size: 12px; color: #94A3B8; margin-bottom: 4px; text-align: left; }
.date-input { width: 100%; padding: 12px; border: 1px solid #E2E8F0; border-radius: 12px; font-family: inherit; font-size: 14px; color: #1E293B; box-sizing: border-box; background-color: white; }

.reason-input { width: 100%; height: 100px; padding: 16px; border: 1px solid #E2E8F0; border-radius: 12px; font-family: inherit; font-size: 14px; color: #1E293B; resize: none; box-sizing: border-box; background-color: white; }
.reason-input::placeholder { color: #94A3B8; }

.upload-box { border: 2px dashed #CBD5E1; border-radius: 16px; padding: 24px 16px; text-align: center; background-color: white; cursor: pointer; transition: background-color 0.2s; }
.upload-box:hover { background-color: #F8FAFC; }
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
.bottom-nav {
  flex-shrink: 0; /* Pastikan ukurannya tidak mengecil */
  width: 100%;
  height: 70px;
  background-color: #ffffff;
  display: flex;
  justify-content: space-around;
  align-items: center;
  border-top: 1px solid #f1f5f9;
  z-index: 100;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
}

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
/* 4. PERBAIKAN TOMBOL QR JADI KOTAK (SQUIRCLE) */
.scan-circle {
  width: 56px;
  height: 56px;
  background-color: #2563EB;
  border-radius: 16px; /* BUKAN 50% LAGI, SEKARANG KOTAK MELENGKUNG */
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
  /* Border putih dihapus agar persis desain Figma */
}
.scan-circle .qr-icon { width: 28px; height: 28px; opacity: 1; filter: brightness(0) invert(1); }
</style>