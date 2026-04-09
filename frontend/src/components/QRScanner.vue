<script setup>
const emit = defineEmits(["go-back"]);
</script>

<template>
  <div class="scanner-wrapper">
    <div class="scanner-frame">
      
      <div class="header-nav">
        <button class="btn-circle-back" @click="$emit('go-back')">❮</button>
        <span class="header-title">Scan QR Code</span>
        <div style="width: 40px"></div>
      </div>

      <div class="camera-simulation">
        <div class="scan-window">
          <div class="corner tl"></div>
          <div class="corner tr"></div>
          <div class="corner bl"></div>
          <div class="corner br"></div>
          
          <div class="laser-scanner"></div>
        </div>
        
        <div class="info-text">
          <p>Arahkan kamera ke QR Code Kantor</p>
          <span>Pastikan pencahayaan cukup agar terbaca otomatis</span>
        </div>
      </div>

      <div class="action-footer">
        <div class="icon-btn">
          <div class="circle-btn">🔦</div>
          <span>Flashlight</span>
        </div>
        <div class="icon-btn">
          <div class="circle-btn">🖼️</div>
          <span>Gallery</span>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* Reset dasar untuk layar penuh */
.scanner-wrapper {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #000;
  display: flex;
  justify-content: center;
  z-index: 9999;
}

.scanner-frame {
  width: 100%;
  max-width: 480px;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
}

/* Navigasi Atas */
.header-nav {
  padding: 40px 20px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 10;
}

.btn-circle-back {
  width: 40px; height: 40px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
  backdrop-filter: blur(5px);
}

.header-title { color: white; font-weight: 600; font-size: 16px; }

/* Scanner UI */
.camera-simulation {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.scan-window {
  width: 250px;
  height: 250px;
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
}

.corner {
  position: absolute;
  width: 35px; height: 35px;
  border: 4px solid #3b82f6; /* Warna biru sesuai dashboard */
}

.tl { top: -2px; left: -2px; border-right: none; border-bottom: none; border-top-left-radius: 15px; }
.tr { top: -2px; right: -2px; border-left: none; border-bottom: none; border-top-right-radius: 15px; }
.bl { bottom: -2px; left: -2px; border-right: none; border-top: none; border-bottom-left-radius: 15px; }
.br { bottom: -2px; right: -2px; border-left: none; border-top: none; border-bottom-right-radius: 15px; }

/* Animasi Laser */
.laser-scanner {
  width: 100%;
  height: 2px;
  background: #3b82f6;
  box-shadow: 0 0 15px #3b82f6;
  position: absolute;
  top: 0;
  animation: scan-move 2.5s infinite ease-in-out;
}

@keyframes scan-move {
  0% { top: 0%; }
  50% { top: 100%; }
  100% { top: 0%; }
}

.info-text { text-align: center; margin-top: 35px; padding: 0 40px; }
.info-text p { color: white; font-size: 14px; font-weight: 500; margin: 0; }
.info-text span { color: #94a3b8; font-size: 12px; margin-top: 8px; display: block; }

/* Action Buttons */
.action-footer {
  display: flex;
  justify-content: center;
  gap: 50px;
  padding-bottom: 60px;
}

.icon-btn { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.circle-btn {
  width: 55px; height: 55px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
}
.icon-btn span { color: #cbd5e1; font-size: 12px; }
</style>