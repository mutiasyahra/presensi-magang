<template>
  <div class="clock-in-page">
    <div class="header-fixed">
      <button class="btn-back" @click="$emit('go-back')">
        <img src="../assets/back.png" alt="Back" />
      </button>
      <h2>Clock In Attendance</h2>
    </div>

    <div class="content-scroll">
      <div class="camera-container">
        <div class="camera-wrapper">
          <div class="live-badge"><span class="dot"></span> Live Preview</div>
          <!-- show either video stream or captured image -->
          <video
            ref="videoRef"
            class="camera-feed"
            autoplay
            playsinline
            v-show="!photoCaptured"
          ></video>
          <canvas
            ref="canvasRef"
            class="camera-feed"
            v-show="photoCaptured"
          ></canvas>
          <div class="scan-overlay"></div>
          <div class="capture-wrapper" v-if="!photoCaptured">
            <button
              class="btn-capture"
              @click="capturePhoto"
              :disabled="!cameraReady"
            >
              Take Photo
            </button>
          </div>
        </div>
      </div>

      <div class="info-section">
        <div class="info-card">
          <div class="icon-box blue-soft">
            <img src="../assets/current location.png" alt="Location" />
          </div>
          <div class="card-content">
            <p class="label">CURRENT LOCATION</p>
            <p class="value">{{ locationName || "Fetching location..." }}</p>
            <p v-if="coords.lat" class="coords">
              {{ coords.lat }}, {{ coords.lng }}
            </p>
          </div>
        </div>
        <div class="info-card">
          <div class="icon-box blue-soft">
            <img src="../assets/timestamp.png" alt="Time" />
          </div>
          <div class="card-content">
            <p class="label">TIMESTAMP</p>
            <div class="time-row">
              <span class="value">{{ currentTimeStr }}</span>
              <span class="badge-ontime">ON TIME</span>
            </div>
          </div>
        </div>

        <div class="work-plan-card">
          <div class="plan-header">
            <img src="../assets/daily plan.png" alt="Plan" />
            <span class="label">DAILY WORK PLAN</span>
          </div>
          <textarea
            v-model="rencanaKegiatan"
            class="plan-input"
            placeholder="List your task..&#10;Example: Finalize UI for Dashboard & Weekly sync meeting"
          ></textarea>
        </div>

        <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>
      </div>
    </div>
    <div class="footer-fixed">
      <button
        class="btn-submit"
        @click="submitAttendance"
        :disabled="isLoading"
      >
        <img src="../assets/camera.png" alt="Cam" />
        {{ isLoading ? "Submitting..." : "Submit Attendance" }}
      </button>
      <a href="#" class="retake-link" @click.prevent="retake">Retake Photo</a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import api from "../api/axios.js";

const emit = defineEmits(["go-back"]);

const rencanaKegiatan = ref("");
const isLoading = ref(false);
const errorMessage = ref("");
const locationName = ref("Tech Innovations Hub, Jakarta (Simulated)");
const coords = ref({ lat: null, lng: null });
const currentTimeStr = ref("");

// camera refs
const videoRef = ref(null);
const canvasRef = ref(null);
const photoCaptured = ref(false);
const photoBlob = ref(null);
const cameraReady = ref(false); // becomes true after camera stream attached

const updateTime = () => {
  const now = new Date();
  const options = {
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  currentTimeStr.value = now
    .toLocaleDateString("en-US", options)
    .replace(",", " •");
};

const getLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        coords.value.lat = position.coords.latitude;
        coords.value.lng = position.coords.longitude;
      },
      (err) => {
        console.error("Error getting location:", err);
        // Fallback or alert
      },
    );
  }
};

const submitAttendance = async () => {
  if (!rencanaKegiatan.value) {
    errorMessage.value = "Please fill in your work plan.";
    return;
  }

  // if photo not captured yet, capture automatically
  if (!photoCaptured.value) {
    capturePhoto();
    await new Promise((r) => setTimeout(r, 200));
  }

  if (!photoCaptured.value || !photoBlob.value) {
    errorMessage.value = "Failed to capture photo. Please try again.";
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";

  try {
    const formData = new FormData();
    formData.append("rencana_kegiatan", rencanaKegiatan.value);
    formData.append("lat", coords.value.lat || -6.2); // Default if geo fails
    formData.append("long", coords.value.lng || 106.816666);
    formData.append("photo", photoBlob.value, "attendance.png");

    await api.post("/clock-in", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    alert("Clock-in successful!");
    emit("go-back");
  } catch (error) {
    // Laravel returns 422 with errors object for validation
    if (error.response?.status === 422 && error.response.data?.errors) {
      const firstKey = Object.keys(error.response.data.errors)[0];
      errorMessage.value = error.response.data.errors[firstKey][0];
    } else {
      errorMessage.value =
        error.response?.data?.message || "Failed to submit attendance.";
    }
  } finally {
    isLoading.value = false;
  }
};

const startCamera = async () => {
  try {
    // wait for DOM to render video element
    await nextTick();
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    if (videoRef.value) {
      videoRef.value.srcObject = stream;
      await videoRef.value.play();
      cameraReady.value = true;
    } else {
      console.warn("[Camera] videoRef still null when starting camera");
    }
  } catch (err) {
    console.error("Camera error:", err);
    errorMessage.value = "Unable to access camera.";
  }
};

const capturePhoto = () => {
  console.log("[Camera] capturePhoto called", videoRef.value, canvasRef.value);
  if (!videoRef.value || !canvasRef.value) {
    console.warn("[Camera] refs not ready", {
      video: videoRef.value,
      canvas: canvasRef.value,
    });
    return;
  }
  if (!videoRef.value.srcObject) {
    console.warn("[Camera] no stream attached yet");
    return;
  }
  const take = () => {
    const width = videoRef.value.videoWidth;
    const height = videoRef.value.videoHeight;
    if (width === 0 || height === 0) {
      // wait briefly and retry
      return setTimeout(take, 50);
    }

    canvasRef.value.width = width;
    canvasRef.value.height = height;
    const ctx = canvasRef.value.getContext("2d");
    ctx.drawImage(videoRef.value, 0, 0, width, height);

    canvasRef.value.toBlob((blob) => {
      if (blob) {
        photoBlob.value = blob;
      } else {
        // fallback using dataURL
        const dataUrl = canvasRef.value.toDataURL("image/png");
        const arr = dataUrl.split(","),
          mime = arr[0].match(/:(.*?);/)[1],
          bstr = atob(arr[1]);
        let n = bstr.length;
        let u8arr = new Uint8Array(n);
        while (n--) u8arr[n] = bstr.charCodeAt(n);
        photoBlob.value = new Blob([u8arr], { type: mime });
      }
      photoCaptured.value = true;
      // stop camera stream
      const tracks = videoRef.value.srcObject?.getTracks() || [];
      tracks.forEach((t) => t.stop());
    }, "image/png");
  };
  take();
};

const retake = () => {
  photoCaptured.value = false;
  photoBlob.value = null;
  startCamera();
};

onMounted(() => {
  updateTime();
  getLocation();
  startCamera();
});
</script>

<style scoped>
/* =========================================
   1. LAYOUT UTAMA (Fixed Header & Footer)
   ========================================= */

/* Container Full Screen HP */
.clock-in-page {
  background-color: #f8fafc;
  height: 100vh; /* Tinggi pas layar */
  display: flex;
  flex-direction: column; /* Susun atas-bawah */
  overflow: hidden; /* Kunci scroll body utama */
}

/* Header (Diam di Atas) */
.header-fixed {
  flex-shrink: 0; /* Tidak boleh mengecil */
  background-color: #f8fafc;
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
  color: #0f172a;
}

/* Area Tengah (Bisa Scroll) */
.content-scroll {
  flex: 1; /* Ambil sisa ruang */
  overflow-y: auto; /* Scroll aktif di sini */
  padding: 0 20px 20px 20px;
  /* Sembunyikan scrollbar agar bersih */
  scrollbar-width: none;
}
.content-scroll::-webkit-scrollbar {
  display: none;
}

/* Footer (Diam di Bawah) */
.footer-fixed {
  flex-shrink: 0;
  background: white;
  padding: 20px;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
  text-align: center;
  z-index: 20;
}

/* =========================================
   2. KOMPONEN UI
   ========================================= */

/* Tombol Back */
.btn-back {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.btn-back img {
  width: 20px;
}

/* Camera Section */
.camera-container {
  display: flex;
  justify-content: center;
  margin-bottom: 25px;
  margin-top: 10px;
}
.camera-wrapper {
  position: relative;
  width: 100%;
  max-width: 320px;
  height: 320px;
  border-radius: 30px;
  overflow: visible; /* allow button outside if needed */
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
.camera-feed {
  width: 100%;
  height: 100%;
  object-fit: cover;
  pointer-events: none;
}

/* Capture button overlay */
.capture-wrapper {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 30;
  pointer-events: auto;
}
.btn-capture {
  background-color: #2563eb;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.btn-capture {
  cursor: pointer;
} /* explicit */
.btn-capture:hover {
  background-color: #1d4ed8;
}

.btn-capture:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.live-badge {
  position: absolute;
  top: 15px;
  left: 15px;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  font-size: 12px;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 6px;
  z-index: 10;
  backdrop-filter: blur(4px);
}
.dot {
  width: 8px;
  height: 8px;
  background-color: #22c55e;
  border-radius: 50%;
}
.scan-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 70%;
  height: 70%;
  border: 2px dashed rgba(255, 255, 255, 0.5);
  border-radius: 24px;
  pointer-events: none;
}

/* =========================================
   3. INFO CARDS (Alignment Fix)
   ========================================= */

.info-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 10px;
}

.info-card {
  background: white;
  padding: 16px;
  border-radius: 20px;
  display: flex;
  gap: 15px;
  align-items: flex-start; /* Ikon dan teks sejajar atas */
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.icon-box {
  width: 40px;
  height: 40px;
  background: #eff6ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.icon-box img {
  width: 20px;
  height: 20px;
  object-fit: contain;
}

/* KUNCI RATA KIRI */
.card-content {
  flex: 1;
  text-align: left !important; /* Paksa teks ke kiri */
  padding-left: 5px;
}

.label {
  font-size: 10px;
  font-weight: 600;
  color: #94a3b8;
  letter-spacing: 0.5px;
  margin: 0 0 4px 0;
  text-transform: uppercase;
  display: block;
  text-align: left;
}

.value {
  font-size: 13px;
  font-weight: 600;
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
  background: #eff6ff;
  color: #2563eb;
  font-size: 10px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  flex-shrink: 0;
}

/* Work Plan Input */
.work-plan-card {
  background: white;
  padding: 20px;
  border-radius: 24px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}
.plan-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}
.plan-header img {
  width: 16px;
  opacity: 0.6;
}

.plan-input {
  width: 100%;
  height: 80px;
  border: 1px solid #f1f5f9;
  background: #f8fafc;
  border-radius: 16px;
  padding: 12px;
  font-family: "Inter", sans-serif;
  font-size: 13px;
  color: #334155;
  resize: none;
  outline: none;
}
.plan-input:focus {
  border-color: #bfdbfe;
  background: white;
}
.plan-input::placeholder {
  color: #cbd5e1;
}

/* =========================================
   4. TOMBOL ACTION
   ========================================= */

.btn-submit {
  width: 100%;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  padding: 16px;
  border-radius: 16px;
  font-size: 14px;
  font-weight: 600;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
  margin-bottom: 15px;
  transition: transform 0.1s;
}
.btn-submit:active {
  transform: scale(0.98);
}
.btn-submit img {
  width: 20px;
  filter: brightness(0) invert(1);
}

.retake-link {
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  text-decoration: none;
}
</style>
