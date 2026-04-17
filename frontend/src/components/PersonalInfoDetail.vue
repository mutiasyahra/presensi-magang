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
  <div class="main-wrapper">
    <div class="mobile-frame">
      
      <div class="blue-header">
        <div class="header-content">
          <button class="btn-back" @click="$emit('go-back')">❮</button>
          <h2>Personal Information</h2>
          <div style="width: 24px"></div> </div>
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

          </div> </div> </div> </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

/* --- DASAR & LAYOUT (Sama seperti Profile) --- */
.main-wrapper {
  background-color: var(--bg-mobile-shell, #e2e8f0);
  display: flex;
  justify-content: center;
  min-height: 100vh;
  margin: 0;
  font-family: "Inter", sans-serif;
  transition: background-color 0.3s ease;
}

.mobile-frame {
  width: 100%;
  max-width: 430px;
  height: 100vh;
  background-color: var(--bg-app, #f8fafc);
  position: relative;
  overflow: hidden;
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease;
}

/* --- HEADER BIRU MELENGKUNG (Style Match image_1.png) --- */
.blue-header {
  background: var(--header-gradient, linear-gradient(135deg, #3b82f6 0%, #2563eb 100%));
  height: 160px; /* Sedikit lebih pendek karena tidak ada foto besar */
  border-bottom-left-radius: 40px;
  border-bottom-right-radius: 40px;
  padding: 10px 25px;
  color: white;
  position: relative;
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
  padding: 8px 12px;
  border-radius: 12px;
  cursor: pointer;
  backdrop-filter: blur(5px);
  transition: 0.2s;
}

.btn-back:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* --- AREA KONTEN (Floating Card) --- */
.content-area {
  flex: 1;
  padding: 0 20px;
  margin-top: -60px; /* Nempel naik ke header biru */
  position: relative;
  z-index: 2;
  overflow-y: auto; /* Bisa di-scroll kalau datanya panjang */
  padding-bottom: 40px;
  scrollbar-width: none; /* Hide scrollbar UI */
}

.content-area::-webkit-scrollbar { display: none; }

.info-card {
  background: var(--bg-card);
  border-radius: 24px;
  padding: 25px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.card-desc {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0 0 25px 0;
  line-height: 1.5;
}

/* --- DATA STYLING (The core info) --- */
.data-group {
  display: flex;
  flex-direction: column;
  gap: 18px; /* Jarak antar field */
}

.data-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.data-item label {
  font-size: 11px;
  text-transform: uppercase;
  color: var(--text-dim); /* Abu-abu muda untuk label */
  font-weight: 600;
  letter-spacing: 0.5px;
}

.data-item p {
  font-size: 14px;
  color: var(--text-main); /* Hitam pekat untuk isi data */
  font-weight: 500;
  margin: 0;
  line-height: 1.4;
}

/* Spesifik style untuk ID Intern agar menonjol */
.highlight-text {
  color: var(--accent-primary) !important;
  font-weight: 700 !important;
}

.divider {
  height: 1px;
  background-color: var(--border-color);
  margin: 5px 0;
}

/* Grid 2 kolom untuk Tanggal */
.date-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}
</style>