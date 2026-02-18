<template>
  <div class="clock-in-page">
    
    <div class="header-fixed">
      <button class="btn-back" @click="$emit('go-back')">
        <img src="../assets/back.png" alt="Back">
      </button>
      <h2>Clock In Attendance</h2>
    </div>

    <div class="content-scroll">
      
      <div class="camera-container">
        <div class="camera-wrapper">
          <div class="live-badge"><span class="dot"></span> Live Preview</div>
          <img src="../assets/imagee.png" class="camera-feed" alt="Camera Feed">
          <div class="scan-overlay"></div>
        </div>
      </div>

      <div class="info-section">
        <div class="info-card">
          <div class="icon-box blue-soft">
            <img src="../assets/current location.png" alt="Location">
          </div>
          <div class="card-content">
            <p class="label">CURRENT LOCATION</p>
            <p class="value">Building 4, Tech Innovation Hub, Science Park Drive, Singapore 118222</p>
          </div>
        </div>
        <div class="info-card">
          <div class="icon-box blue-soft">
            <img src="../assets/timestamp.png" alt="Time">
          </div>
          <div class="card-content">
            <p class="label">TIMESTAMP</p>
            <div class="time-row">
              <span class="value">Oct 24, 2023 • 09:41 AM</span>
              <span class="badge-ontime">ON TIME</span>
            </div>
          </div>
        </div>

         <div class="work-plan-card">
          <div class="plan-header">
            <img src="../assets/daily plan.png" alt="Plan">
            <span class="label">DAILY WORK PLAN</span>
          </div>
          <textarea 
            class="plan-input" 
            placeholder="List your task..&#10;Example: Finalize UI for Dashboard & Weekly sync meeting"
          ></textarea>
        </div>

      </div> 
      </div> <div class="footer-fixed">
      <button class="btn-submit">
        <img src="../assets/camera.png" alt="Cam">
        Submit Attendance
      </button>
      <a href="#" class="retake-link">Retake Photo</a>
    </div>

  </div>
</template>

<style scoped>
/* =========================================
   1. LAYOUT UTAMA (Fixed Header & Footer)
   ========================================= */

/* Container Full Screen HP */
.clock-in-page {
  background-color: #F8FAFC;
  height: 100vh; /* Tinggi pas layar */
  display: flex;
  flex-direction: column; /* Susun atas-bawah */
  overflow: hidden; /* Kunci scroll body utama */
}

/* Header (Diam di Atas) */
.header-fixed {
  flex-shrink: 0; /* Tidak boleh mengecil */
  background-color: #F8FAFC;
  padding: 20px 20px 10px 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  z-index: 20;
}

.header-fixed h2 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  color: #0F172A;
}

/* Area Tengah (Bisa Scroll) */
.content-scroll {
  flex: 1; /* Ambil sisa ruang */
  overflow-y: auto; /* Scroll aktif di sini */
  padding: 0 20px 20px 20px;
  /* Sembunyikan scrollbar agar bersih */
  scrollbar-width: none; 
}
.content-scroll::-webkit-scrollbar { display: none; }

/* Footer (Diam di Bawah) */
.footer-fixed {
  flex-shrink: 0;
  background: white;
  padding: 20px;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
  text-align: center;
  z-index: 20;
}


/* =========================================
   2. KOMPONEN UI
   ========================================= */

/* Tombol Back */
.btn-back {
  background: white;
  border: 1px solid #E2E8F0;
  border-radius: 50%;
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
}
.btn-back img { width: 20px; }

/* Camera Section */
.camera-container {
  display: flex; justify-content: center;
  margin-bottom: 25px;
  margin-top: 10px;
}
.camera-wrapper {
  position: relative;
  width: 100%; max-width: 320px;
  height: 320px;
  border-radius: 30px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.camera-feed { width: 100%; height: 100%; object-fit: cover; }
.live-badge {
  position: absolute; top: 15px; left: 15px;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  font-size: 12px; font-weight: 500;
  padding: 6px 12px;
  border-radius: 20px;
  display: flex; align-items: center; gap: 6px;
  z-index: 10; backdrop-filter: blur(4px);
}
.dot { width: 8px; height: 8px; background-color: #22C55E; border-radius: 50%; }
.scan-overlay {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 70%; height: 70%;
  border: 2px dashed rgba(255, 255, 255, 0.5);
  border-radius: 24px; pointer-events: none;
}


/* =========================================
   3. INFO CARDS (Alignment Fix)
   ========================================= */

.info-section { display: flex; flex-direction: column; gap: 16px; margin-bottom: 10px; }

.info-card {
  background: white;
  padding: 16px;
  border-radius: 20px;
  display: flex;
  gap: 15px;
  align-items: flex-start; /* Ikon dan teks sejajar atas */
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.icon-box {
  width: 40px; height: 40px;
  background: #EFF6FF;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.icon-box img { width: 20px; height: 20px; object-fit: contain; }

/* KUNCI RATA KIRI */
.card-content {
  flex: 1;
  text-align: left !important; /* Paksa teks ke kiri */
  padding-left: 5px;
}

.label {
  font-size: 10px; font-weight: 600;
  color: #94A3B8;
  letter-spacing: 0.5px;
  margin: 0 0 4px 0;
  text-transform: uppercase;
  display: block; text-align: left;
}

.value {
  font-size: 13px; font-weight: 600;
  color: #334155;
  margin: 0;
  line-height: 1.4;
}

/* Row Waktu & Badge */
.time-row {
  display: flex;
  justify-content: space-between; /* Jam kiri, Badge kanan */
  align-items: center;
  width: 100%;
}
.badge-ontime {
  background: #EFF6FF; color: #2563EB;
  font-size: 10px; font-weight: 700;
  padding: 4px 8px; border-radius: 6px;
  flex-shrink: 0;
}

/* Work Plan Input */
.work-plan-card {
  background: white;
  padding: 20px;
  border-radius: 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.plan-header { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.plan-header img { width: 16px; opacity: 0.6; }

.plan-input {
  width: 100%; height: 80px;
  border: 1px solid #F1F5F9;
  background: #F8FAFC;
  border-radius: 16px;
  padding: 12px;
  font-family: 'Inter', sans-serif;
  font-size: 13px; color: #334155;
  resize: none; outline: none;
}
.plan-input:focus { border-color: #BFDBFE; background: white; }
.plan-input::placeholder { color: #CBD5E1; }


/* =========================================
   4. TOMBOL ACTION
   ========================================= */

.btn-submit {
  width: 100%;
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  color: white;
  border: none;
  padding: 16px;
  border-radius: 16px;
  font-size: 14px; font-weight: 600;
  display: flex; justify-content: center; align-items: center; gap: 10px;
  cursor: pointer;
  box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
  margin-bottom: 15px;
  transition: transform 0.1s;
}
.btn-submit:active { transform: scale(0.98); }
.btn-submit img { width: 20px; filter: brightness(0) invert(1); }

.retake-link {
  color: #64748B;
  font-size: 13px; font-weight: 500;
  text-decoration: none;
}
</style>