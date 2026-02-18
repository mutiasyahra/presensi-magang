<script setup>
// Mendefinisikan event agar bisa lapor ke App.vue
const emit = defineEmits(['login-success']);

const onSignIn = () => {
  // Kirim sinyal "login-success" ke App.vue
  emit('login-success');
};
</script>

<template>
  <div class="main-wrapper">
    <div class="mobile-frame">
      
      <div class="decoration-container">
        <img src="../assets/imlek kareem.png" alt="Imlek Ramadhan" class="hero-image">
      </div>

      <div class="content-area">
        <div class="text-header">
          <h1 class="app-title">Presensi Magang</h1>
          <p class="app-subtitle">
            Harmoni Keberkahan &<br>Semangat Baru
          </p>
        </div>

        <div class="auth-buttons">
          <button class="btn-primary" @click="$emit('click-login')">
            Sign In
          </button>
          
          <button class="btn-secondary">
            Create Account
          </button>
        </div>

        <div class="footer-text">
          <p>Version 2.0.1 • Edisi Spesial</p>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* --- MAIN LAYOUT --- */
.main-wrapper {
  background-color: #E2E8F0;
  display: flex; justify-content: center; align-items: center;
  min-height: 100vh; margin: 0;
  font-family: 'Poppins', sans-serif;
}

.mobile-frame {
  width: 100%; max-width: 430px; height: 100vh;
  
  /* BACKGROUND GRADASI BARU: UNGU (Atas) -> KUNING (Bawah) */
  background: linear-gradient(180deg, #7C3AED 0%, #D97706 80%, #F59E0B 100%);
  
  position: relative; overflow: hidden;
  box-shadow: 0 0 40px rgba(0,0,0,0.2);
  display: flex; flex-direction: column;
  color: white; /* Default text putih */
}

/* --- DECORATION --- */
.decoration-container {
  flex: 1.5; /* Gambar ambil porsi lebih banyak */
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 40px;
}

/* Glow Effect di belakang gambar */
.decoration-container::before {
  content: '';
  position: absolute;
  width: 280px; height: 280px;
  background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 70%);
  border-radius: 50%;
  z-index: 0;
}

.hero-image {
  width: 100%;
  max-width: 300px; /* Gambar sedikit diperbesar */
  object-fit: contain;
  z-index: 1;
  animation: float 6s ease-in-out infinite;
  filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
  100% { transform: translateY(0px); }
}

/* --- CONTENT AREA --- */
.content-area {
  flex: 1;
  /* Background putih dihapus agar transparan */
  background: transparent; 
  padding: 20px 30px 50px 30px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end; /* Konten rapat ke bawah */
  gap: 30px;
}

.text-header {
  text-align: center;
}

.app-title {
  color: #FFFFFF; /* Putih Solid */
  font-size: 26px;
  font-weight: 700;
  margin: 0 0 10px 0;
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.app-subtitle {
  color: rgba(255, 255, 255, 0.9); /* Putih agak transparan */
  font-size: 15px;
  line-height: 1.6;
  margin: 0;
  font-weight: 500;
}

/* --- BUTTONS --- */
.auth-buttons {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.btn-primary {
  /* Tombol Biru Kontras dengan Background Kuning */
  background: #2563EB;
  color: white;
  border: none;
  padding: 16px;
  border-radius: 16px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex; justify-content: center; align-items: center; gap: 10px;
  box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
  transition: transform 0.2s;
}

.btn-primary:active { transform: scale(0.98); }

.arrow { font-size: 18px; }

.btn-secondary {
  /* Tombol Putih agar bersih di atas kuning */
  background: rgba(255, 255, 255, 0.9);
  color: #1E293B; /* Teks gelap */
  border: none;
  padding: 16px;
  border-radius: 16px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.btn-secondary:active { background: #FFFFFF; }

/* --- FOOTER --- */
.footer-text {
  text-align: center;
}
.footer-text p {
  font-size: 10px;
  color: rgba(255, 255, 255, 0.6); /* Putih samar */
  margin: 0;
  letter-spacing: 1px;
  text-transform: uppercase;
  font-weight: 600;
}
</style>