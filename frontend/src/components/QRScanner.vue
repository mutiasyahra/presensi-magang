<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import jsQR from "jsqr";
import Swal from 'sweetalert2';

const emit = defineEmits(["go-back"]);

const videoRef = ref(null);
const canvasRef = ref(null);
const fileInputRef = ref(null);
const scanError = ref("");
const isFlashOn = ref(false);
let stream = null;
let requestAnimationId = null;
let isScanning = true;

const processQR = (qrData) => {
  isScanning = false;
  stopCamera();
  
  if (qrData.startsWith("http://") || qrData.startsWith("https://")) {
    window.location.href = qrData;
  } else {
    Swal.fire({
      title: 'QR Terdeteksi',
      text: qrData,
      icon: 'success',
      confirmButtonColor: '#3B82F6'
    }).then(() => {
      emit("go-back");
    });
  }
};

const startCamera = async () => {
  try {
    await nextTick();
    stream = await navigator.mediaDevices.getUserMedia({ 
      video: { facingMode: "environment" } 
    });
    
    if (videoRef.value) {
      videoRef.value.srcObject = stream;
      videoRef.value.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
      videoRef.value.play();
      requestAnimationFrame(tick);
    }
  } catch (err) {
    console.error("Camera error:", err);
    scanError.value = "Unable to access camera.";
  }
};

const tick = () => {
  if (!isScanning) return;
  
  if (videoRef.value && videoRef.value.readyState === videoRef.value.HAVE_ENOUGH_DATA) {
    if (!canvasRef.value) return; // avoid error when unmounted
    
    const canvas = canvasRef.value;
    const video = videoRef.value;
    const ctx = canvas.getContext("2d", { willReadFrequently: true });
    
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Get image data
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    
    // Scan for QR code
    const code = jsQR(imageData.data, imageData.width, imageData.height, {
      inversionAttempts: "dontInvert",
    });
    
    if (code && code.data) {
      console.log("Found QR code", code.data);
      processQR(code.data);
      return; // Stop scanning
    }
  }
  
  // Continue scanning
  requestAnimationId = requestAnimationFrame(tick);
};

const stopCamera = () => {
  if (stream) {
    const tracks = stream.getTracks();
    tracks.forEach((track) => {
      // Matikan flash jika sedang nyala
      if (isFlashOn.value && track.getCapabilities && track.applyConstraints) {
        track.applyConstraints({ advanced: [{ torch: false }] }).catch(()=>{});
      }
      track.stop();
    });
  }
  if (requestAnimationId) {
    cancelAnimationFrame(requestAnimationId);
  }
};

const toggleFlashlight = async () => {
  if (!stream) return;
  const track = stream.getVideoTracks()[0];
  
  if (track && track.getCapabilities && track.applyConstraints) {
    const capabilities = track.getCapabilities();
    if (capabilities.torch) {
      isFlashOn.value = !isFlashOn.value;
      try {
        await track.applyConstraints({
          advanced: [{ torch: isFlashOn.value }]
        });
      } catch (err) {
        console.error("Error toggling flash:", err);
        isFlashOn.value = !isFlashOn.value;
      }
    } else {
      Swal.fire({
        icon: 'info',
        title: 'Info',
        text: 'Flashlight tidak didukung di perangkat ini.',
        confirmButtonColor: '#3B82F6'
      });
    }
  } else {
    Swal.fire({
      icon: 'error',
      title: 'Kesalahan',
      text: 'Flashlight tidak didukung di perangkat atau browser ini.',
      confirmButtonColor: '#EF4444'
    });
  }
};

const triggerFileInput = () => {
  if (fileInputRef.value) {
    fileInputRef.value.click();
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const reader = new FileReader();
  reader.onload = (e) => {
    const img = new Image();
    img.onload = () => {
      // Draw to a temporary canvas for scanning
      const tempCanvas = document.createElement("canvas");
      tempCanvas.width = img.width;
      tempCanvas.height = img.height;
      const ctx = tempCanvas.getContext("2d");
      ctx.drawImage(img, 0, 0, tempCanvas.width, tempCanvas.height);
      const imageData = ctx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
      
      const code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: "dontInvert"
      });
      
      if (code && code.data) {
        processQR(code.data);
      } else {
        Swal.fire({
          icon: 'warning',
          title: 'Tidak Ditemukan',
          text: 'QR Code tidak ditemukan dalam gambar.',
          confirmButtonColor: '#3B82F6'
        });
        // reset file input so the same file could be selected again if failed
        if (fileInputRef.value) fileInputRef.value.value = "";
      }
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
};

onMounted(() => {
  startCamera();
});

onUnmounted(() => {
  isScanning = false;
  stopCamera();
});
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
        <!-- Live Camera Feed -->
        <video ref="videoRef" class="camera-bg" autoplay playsinline></video>
        <!-- Hidden Canvas for jsQR processing -->
        <canvas ref="canvasRef" style="display: none;"></canvas>

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
          <span v-if="scanError" class="error-msg">{{ scanError }}</span>
        </div>
      </div>

      <div class="action-footer">
        <div class="icon-btn" @click="toggleFlashlight" style="cursor: pointer;">
          <div class="circle-btn" :class="{ 'flash-on': isFlashOn }">🔦</div>
          <span>Flashlight</span>
        </div>
        <div class="icon-btn" @click="triggerFileInput" style="cursor: pointer;">
          <div class="circle-btn">🖼️</div>
          <span>Gallery</span>
        </div>
      </div>
      
      <!-- Hidden file input for gallery selection -->
      <input type="file" ref="fileInputRef" accept="image/*" style="display: none" @change="handleFileUpload" />

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
  position: relative; 
}

.btn-circle-back {
  width: 40px; height: 40px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
  backdrop-filter: blur(5px);
  transition: transform 0.2s ease, background 0.2s ease;
}

.btn-circle-back:active {
  transform: scale(0.9);
  background: rgba(255, 255, 255, 0.3);
}

.header-title { color: white; font-weight: 600; font-size: 16px; }

/* Scanner UI */
.camera-simulation {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative; /* Untuk memposisikan kamera di belakang */
  border-radius: 24px;
  margin: 0 10px;
  overflow: hidden;
  box-shadow: 0 0 0 100vmax rgba(0,0,0,0.5);
  clip-path: inset(0 -100vmax); /* Dimming background behind */
}

/* Base style for live camera */
.camera-bg {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  object-fit: cover;
  z-index: 0;
  background-color: #111;
}

.scan-window {
  width: 250px;
  height: 250px;
  position: relative;
  z-index: 1; /* di atas video */
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  /* Shadow untuk menggelapkan background diluar kotak scan (opsional jika ga butuh dihapus aja) */
  box-shadow: 0 0 0 4000px rgba(0, 0, 0, 0.4);
}

.corner {
  position: absolute;
  width: 35px; height: 35px;
  border: 4px solid var(--accent-primary, #3b82f6); /* Warna biru sesuai dashboard */
}

.tl { top: -2px; left: -2px; border-right: none; border-bottom: none; border-top-left-radius: 15px; }
.tr { top: -2px; right: -2px; border-left: none; border-bottom: none; border-top-right-radius: 15px; }
.bl { bottom: -2px; left: -2px; border-right: none; border-top: none; border-bottom-left-radius: 15px; }
.br { bottom: -2px; right: -2px; border-left: none; border-top: none; border-bottom-right-radius: 15px; }

/* Animasi Laser */
.laser-scanner {
  width: 100%;
  height: 2px;
  background: var(--accent-primary, #3b82f6);
  box-shadow: 0 0 15px var(--accent-primary, #3b82f6);
  position: absolute;
  top: 0;
  animation: scan-move 2.5s infinite ease-in-out;
}

@keyframes scan-move {
  0% { top: 0%; }
  50% { top: 100%; }
  100% { top: 0%; }
}

.info-text { text-align: center; margin-top: 35px; padding: 0 40px; z-index: 1; }
.info-text p { color: white; font-size: 14px; font-weight: 500; margin: 0; }
.info-text span { color: #94a3b8; font-size: 12px; margin-top: 8px; display: block; }
.info-text .error-msg { color: #ef4444; font-weight: 600; margin-top: 10px; }

/* Action Buttons */
.action-footer {
  display: flex;
  justify-content: center;
  gap: 50px;
  padding: 30px 0 60px;
  position: relative;
  z-index: 10;
}

.icon-btn { display: flex; flex-direction: column; align-items: center; gap: 8px; }

.circle-btn {
  width: 55px; height: 55px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  transition: transform 0.2s cubic-bezier(0.4, 0.0, 0.2, 1), background 0.2s ease;
}

.icon-btn:active .circle-btn {
  transform: scale(0.9);
  background: rgba(255, 255, 255, 0.2);
}

.icon-btn span { color: #cbd5e1; font-size: 12px; }

.flash-on {
  background: white !important;
  color: black !important;
  box-shadow: 0 0 15px rgba(255,255,255,0.8);
}
</style>