<script setup>
import { ref } from "vue";

// Define emit untuk navigasi kembali
const emit = defineEmits(["go-back"]);

// --- DATA DUMMY (Disamakan dengan field di Gambar 2) ---
const userInfo = ref({
  fullName: "Siti Nur Kamila",
  email: "kamila@email.com",
  phoneNumber: "+62 812-3456-7890",
  internId: "PRISMA-2024-001",
  university: "Universitas Indonesia",
  department: "Engineering",
  mentor: "Meutya Syahra",
  project: "09_Prisma (Presensi Magang)",
  startDate: "Jan 01, 2024",
  endDate: "Dec 31, 2024"
});
</script>

<template>
  <div class="screen-container">
    <div class="blue-header">
      <div class="header-content">
        <button class="btn-back" @click="$emit('go-back')">❮</button>
        <h2>Personal Information</h2>
        <div style="width: 24px"></div>
      </div>
    </div>

    <div class="content-area">
      <div class="info-card">
        <p class="card-desc">Berikut adalah detail informasi data diri Anda yang terdaftar di sistem.</p>

        <div class="data-group">
          <div class="data-item">
            <label>Full Name</label>
            <p>{{ userInfo.fullName }}</p>
          </div>

          <div class="data-item">
            <label>Email Address</label>
            <p>{{ userInfo.email }}</p>
          </div>

          <div class="data-item">
            <label>Phone Number</label>
            <p>{{ userInfo.phoneNumber }}</p>
          </div>

          <div class="divider"></div>

          <div class="data-item">
            <label>Intern ID</label>
            <p class="highlight-text">{{ userInfo.internId }}</p>
          </div>

          <div class="data-item">
            <label>University</label>
            <p>{{ userInfo.university }}</p>
          </div>

          <div class="data-item">
            <label>Department</label>
            <p>{{ userInfo.department }}</p>
          </div>

          <div class="divider"></div>

          <div class="data-item">
            <label>Mentor</label>
            <p>{{ userInfo.mentor }}</p>
          </div>

          <div class="data-item">
            <label>Project</label>
            <p>{{ userInfo.project }}</p>
          </div>

          <div class="divider"></div>

          <div class="date-grid">
            <div class="data-item">
              <label>Start Date</label>
              <p>{{ userInfo.startDate }}</p>
            </div>
            <div class="data-item">
              <label>End Date</label>
              <p>{{ userInfo.endDate }}</p>
            </div>
          </div>
        </div>
      </div>
      <div style="height: 40px"></div>
    </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

/* --- LAYOUT UTAMA (Full Screen) --- */
.screen-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  width: 100%;
  /* PENTING: Ini harus warna layar paling belakang */
  background-color: var(--bg-screen); 
  font-family: "Inter", sans-serif;
  overflow: hidden;
  position: relative;
}

/* --- HEADER BIRU --- */
.blue-header {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  height: 140px;
  border-bottom-left-radius: 40px;
  border-bottom-right-radius: 40px;
  padding: 20px 25px;
  color: white;
  flex-shrink: 0;
  z-index: 1;
  transition: all 0.3s ease;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
}

.header-content h2 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  letter-spacing: -0.5px;
}

.btn-back {
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  font-size: 18px;
  padding: 8px 16px;
  border-radius: 12px;
  cursor: pointer;
  backdrop-filter: blur(5px);
  transition: 0.2s;
}

/* --- AREA KARTU MELAYANG --- */
.content-area {
  flex: 1;
  padding: 0 20px;
  margin-top: -70px; /* Menaikkan kartu ke atas area biru */
  position: relative;
  z-index: 2;
  overflow-y: auto;
  scrollbar-width: none; /* Sembunyikan scrollbar Firefox */
}

.content-area::-webkit-scrollbar {
  display: none; /* Sembunyikan scrollbar Chrome/Safari */
}

.info-card {
  /* Gunakan bg-card yang seharusnya sedikit lebih terang dari bg-screen */
  background-color: var(--bg-card); 
  border-radius: 24px;
  padding: 25px;
  /* TAMBAHKAN INI: Shadow tipis agar kartu terlihat melayang */
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); 
  text-align: left;
  /* Tambahkan border tipis agar batas kartu jelas */
  border: 1px solid var(--border-color); 
}

.card-desc {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 25px;
  line-height: 1.5;
}

/* --- STYLING DATA --- */
.data-group {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.data-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.data-item label {
  font-size: 11px;
  text-transform: uppercase;
  /* Gunakan text-muted agar tidak seputih isi datanya */
  color: var(--text-muted); 
  font-weight: 600;
  letter-spacing: 0.5px;
}

.data-item p {
  font-size: 14px;
  color: #1e293b;
  font-weight: 500;
  margin: 0;
}

.highlight-text {
  color: var(--accent-primary) !important;
  font-weight: 700 !important;
}

.divider {
  height: 1px;
  /* Gunakan warna border yang sudah lo definisikan di App.vue */
  background-color: var(--border-color); 
  margin: 15px 0;
  opacity: 0.5; /* Biar lebih subtle */
}

.date-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}
</style>