<script setup>
const emit = defineEmits(["go-back"]);

const policySections = [
  {
    title: "1. Data Collection",
    content: "Kami mengumpulkan data berupa Nama, ID Magang, dan lokasi presensi Anda hanya untuk keperluan administrasi internal dan monitoring kehadiran."
  },
  {
    title: "2. Location Services",
    content: "Aplikasi memerlukan akses lokasi (GPS) saat Anda melakukan Clock In dan Clock Out untuk memvalidasi radius kehadiran di area kantor."
  },
  {
    title: "3. Data Security",
    content: "Seluruh data Anda disimpan secara aman di server kami dan tidak akan dibagikan kepada pihak ketiga tanpa persetujuan manajemen."
  },
  {
    title: "4. User Responsibility",
    content: "User bertanggung jawab penuh atas kerahasiaan akun dan penggunaan aplikasi secara jujur selama masa magang berlangsung."
  }
];
</script>

<template>
  <div class="main-wrapper">
    <div class="mobile-frame">
      
      <div class="blue-header">
        <div class="header-content">
          <button class="btn-back" @click="$emit('go-back')">❮</button>
          <h2>Privacy & Policy</h2>
          <div style="width: 24px"></div>
        </div>
      </div>

      <div class="content-area">
        <div class="policy-card">
          <div class="icon-header">
            <div class="icon-circle">
              <svg viewBox="0 0 24 24" class="icon-svg">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              </svg>
            </div>
            <h3>Data Privacy</h3>
            <p>Terakhir diperbarui: April 2026</p>
          </div>

          <div class="divider"></div>

          <div class="scroll-content">
            <div v-for="(section, index) in policySections" :key="index" class="policy-section">
              <h4>{{ section.title }}</h4>
              <p>{{ section.content }}</p>
            </div>
            
            <div class="acceptance-box">
              <p>Dengan menggunakan aplikasi ini, Anda dianggap menyetujui seluruh ketentuan di atas.</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* Main Wrapper hanya untuk demo desktop, di mobile akan menyesuaikan */
.main-wrapper { background-color: #e2e8f0; display: flex; justify-content: center; min-height: 100vh; font-family: "Inter", sans-serif; }

.mobile-frame { 
  width: 100%; 
  max-width: 480px; 
  height: 100vh; 
  background-color: var(--bg-screen); 
  position: relative; 
  overflow: hidden; 
  display: flex; 
  flex-direction: column; 
  transition: background-color 0.3s ease;
}

.blue-header {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  height: 140px; 
  border-bottom-left-radius: 40px; 
  border-bottom-right-radius: 40px;
  padding: 20px 25px; 
  color: white; 
  z-index: 1;
}

.header-content { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
.header-content h2 { font-size: 18px; font-weight: 700; margin: 0; }

.btn-back { 
  border: none; 
  background: rgba(255, 255, 255, 0.2); 
  color: white; 
  padding: 8px 16px; 
  border-radius: 12px; 
  cursor: pointer; 
  backdrop-filter: blur(5px); 
}

.content-area { 
  flex: 1; 
  padding: 0 20px; 
  margin-top: -50px; 
  z-index: 2; 
  display: flex; 
  padding-bottom: 20px; 
  overflow: hidden; /* Mencegah double scrollbar */
}

.policy-card { 
  background-color: var(--bg-card); 
  border-radius: 24px; 
  padding: 25px; 
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); 
  display: flex; 
  flex-direction: column; 
  width: 100%;
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.icon-header { text-align: center; margin-bottom: 20px; }
.icon-circle { 
  width: 50px; 
  height: 50px; 
  background: var(--bg-screen); /* Mengikuti warna dasar layar */
  border-radius: 15px; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  margin: 0 auto 12px;
  border: 1px solid var(--border-color);
}
/* Pakai warna biru agar ikon tetap terlihat "hidup" */
.icon-svg { width: 24px; height: 24px; stroke: #3b82f6; stroke-width: 2; fill: none; }

.icon-header h3 { font-size: 16px; color: var(--text-main); margin: 0; font-weight: 700; }
/* Ganti #94a3b8 ke variabel muted */
.icon-header p { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

.divider { height: 1px; background: var(--border-color); margin-bottom: 20px; opacity: 0.5;}

.scroll-content { overflow-y: auto; flex: 1; padding-right: 5px; scrollbar-width: none; }
.scroll-content::-webkit-scrollbar { display: none; }

.policy-section { margin-bottom: 20px; }
.policy-section h4 { font-size: 14px; color: var(--text-main); margin-bottom: 8px; font-weight: 600; }
.policy-section p { font-size: 13px; color: var(--text-muted); line-height: 1.6; text-align: justify; }

.acceptance-box {
  background: var(--bg-screen); 
  border: 1px dashed var(--border-color); 
  padding: 15px; 
  border-radius: 12px;
  margin-top: 10px;
}
/* Pastikan teks di box bawah tidak terlalu gelap di mode dark */
.acceptance-box p { 
  font-size: 11px; 
  color: var(--text-muted); 
  text-align: center; 
  margin: 0; 
  opacity: 0.8;
}
</style>