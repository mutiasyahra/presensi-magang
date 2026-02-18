<template>
  <div class="clock-out-page">
    
    <div class="header-fixed">
      <button class="btn-back" @click="$emit('go-back')">
        <img src="../assets/back.png" alt="Back">
      </button>
      <h2>Clock Out Attendance</h2>
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
              <span class="value">Oct 24, 2023 • 05:00 PM</span>
              <span class="badge-ontime">ON TIME</span>
            </div>
          </div>
        </div>

        <div class="form-card">
          <div class="card-header-row">
            <img src="../assets/daily activity.png" alt="Activity">
            <span class="label-title">DAILY ACTIVITY</span>
          </div>
          <textarea 
            class="custom-textarea" 
            placeholder="What did you accomplish today?"
          ></textarea>
        </div>

        <div class="form-card">
          <div class="card-header-row">
            <img src="../assets/evidence upload.png" alt="Evidence">
            <span class="label-title">EVIDENCE UPLOAD</span>
          </div>
          
          <div class="upload-area">
             <div class="upload-placeholder">
                <img src="../assets/pilih file.png" class="upload-icon" alt="Upload">
                <p class="upload-text">Lampirkan foto atau dokumen pendukung<br>(Format PDF/JPG, Max 5MB)</p>
                <button class="btn-pilih-file">Pilih File</button>
             </div>
          </div>
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
/* --- 1. LAYOUT UTAMA --- */
.clock-out-page {
  background-color: #F8FAFC;
  height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.header-fixed {
  flex-shrink: 0;
  background-color: #F8FAFC;
  padding: 20px 20px 10px 20px;
  display: flex; align-items: center; gap: 15px;
  z-index: 20;
}
.header-fixed h2 {
  font-size: 18px; font-weight: 700; margin: 0; color: #0F172A;
}

/* Scroll Area */
.content-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 0 20px 20px 20px;
  scrollbar-width: none; 
}
.content-scroll::-webkit-scrollbar { display: none; }

/* Footer */
.footer-fixed {
  flex-shrink: 0;
  background: white;
  padding: 20px;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
  text-align: center;
  z-index: 20;
}

/* --- 2. KAMERA & INFO --- */
.btn-back {
  background: white; border: 1px solid #E2E8F0; border-radius: 50%;
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.btn-back img { width: 20px; }

.camera-container { display: flex; justify-content: center; margin-bottom: 25px; margin-top: 10px; }
.camera-wrapper {
  position: relative; width: 100%; max-width: 320px; height: 320px;
  border-radius: 30px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
/* Perbaikan Gambar: Object Fit Cover agar full */
.camera-feed { width: 100%; height: 100%; object-fit: cover; opacity: 0.8; }

.live-badge {
  position: absolute; top: 15px; left: 15px;
  background: rgba(0, 0, 0, 0.6); color: white;
  font-size: 12px; font-weight: 500; padding: 6px 12px;
  border-radius: 20px; display: flex; align-items: center; gap: 6px;
  z-index: 10; backdrop-filter: blur(4px);
}
.dot { width: 8px; height: 8px; background-color: #22C55E; border-radius: 50%; }

/* --- 3. FORM STYLE (DIPERBAIKI AGAR TIDAK MELEBER) --- */
.info-section { display: flex; flex-direction: column; gap: 16px; margin-bottom: 10px; }

.info-card, .form-card {
  background: white; padding: 16px; border-radius: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  width: 100%; 
  box-sizing: border-box; /* PENTING: Agar padding tidak bikin meleber */
}

/* Style Info Lokasi & Waktu */
.info-card { display: flex; gap: 15px; align-items: flex-start; }
.icon-box {
  width: 40px; height: 40px; background: #EFF6FF; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.icon-box img { width: 20px; height: 20px; object-fit: contain; }
.card-content { flex: 1; text-align: left !important; padding-left: 5px; }
.label { font-size: 10px; font-weight: 600; color: #94A3B8; margin-bottom: 4px; display: block; }
.value { font-size: 13px; font-weight: 600; color: #334155; margin: 0; line-height: 1.4; }
.time-row { display: flex; justify-content: space-between; align-items: center; width: 100%; }
.badge-ontime { background: #EFF6FF; color: #2563EB; font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px; }

/* Header Form (Activity & Evidence) */
.card-header-row { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.card-header-row img { width: 16px; opacity: 0.8; }
.label-title { font-size: 10px; font-weight: 600; color: #94A3B8; letter-spacing: 0.5px; text-transform: uppercase; }

/* FIX TEXTAREA MELEBER */
.custom-textarea {
  width: 100%; 
  height: 100px;
  box-sizing: border-box; /* PENTING: Kunci agar tidak meleber */
  border: 1px solid #F1F5F9; background: #F8FAFC; border-radius: 16px;
  padding: 15px; 
  font-family: 'Inter', sans-serif; font-size: 13px; color: #334155;
  resize: none; outline: none;
}
.custom-textarea:focus { border-color: #BFDBFE; background: white; }

/* FIX UPLOAD AREA MELEBER */
.upload-area {
  width: 100%;
  box-sizing: border-box; /* PENTING */
  border: 2px dashed #E2E8F0;
  border-radius: 16px;
  padding: 25px 15px;
  text-align: center;
  background-color: #F8FAFC;
  transition: all 0.2s;
}
.upload-icon { width: 32px; opacity: 0.5; margin-bottom: 5px; }
.upload-text { font-size: 11px; color: #94A3B8; line-height: 1.5; margin: 0; }
.btn-pilih-file {
  margin-top: 10px;
  background: white; border: 1px solid #E2E8F0;
  color: #3B82F6; font-size: 12px; font-weight: 600;
  padding: 8px 16px; border-radius: 8px;
  cursor: pointer;
}

/* BUTTON ACTION */
.btn-submit {
  width: 100%;
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  color: white; border: none; padding: 16px; border-radius: 16px;
  font-size: 14px; font-weight: 600;
  display: flex; justify-content: center; align-items: center; gap: 10px;
  cursor: pointer; box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
  margin-bottom: 15px;
}
.btn-submit:active { transform: scale(0.98); }
.btn-submit img { width: 20px; filter: brightness(0) invert(1); }
.retake-link { color: #64748B; font-size: 13px; font-weight: 500; text-decoration: none; }
</style>