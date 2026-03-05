<template>
  <div class="clock-out-page">
    <div class="header-fixed">
      <button class="btn-back" @click="$emit('go-back')">
        <img src="../assets/back.png" alt="Back" />
      </button>
      <h2>Clock Out Attendance</h2>
    </div>

    <div class="content-scroll">
      <div class="info-section">
        <!-- camera capture for clock-out evidence -->
        <div class="camera-container">
          <div class="camera-wrapper">
            <div class="live-badge"><span class="dot"></span> Live Preview</div>
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

        <!-- daily activity card -->
        <div class="form-card">
          <div class="card-header-row">
            <img src="../assets/daily activity.png" alt="Activity" />
            <span class="label-title">DAILY ACTIVITY</span>
          </div>
          <textarea
            v-model="progressKegiatan"
            class="custom-textarea"
            placeholder="What did you accomplish today?"
          ></textarea>
        </div>

        <!-- upload card moved above activity card for separation -->
        <div class="form-card">
          <div class="card-header-row">
            <img src="../assets/pilih file.png" alt="Upload" />
            <span class="label-title">EVIDENCE (optional)</span>
          </div>
          <div class="upload-area" @click="triggerFileInput">
            <img
              src="../assets/pilih file.png"
              class="upload-icon"
              alt="Upload"
            />
            <p class="upload-text">
              Tap to select a file as evidence<br />
              <span class="upload-subtext">(JPEG/PNG/PDF only, max 5MB)</span>
            </p>
            <button class="btn-pilih-file">
              {{ selectedFileName ? "Change File" : "Choose File" }}
            </button>
            <input
              type="file"
              ref="fileInput"
              class="hidden-input"
              @change="handleFileUpload"
              accept="image/jpeg,image/png,application/pdf"
              style="display: none"
            />
          </div>
          <div class="selected-file" v-if="selectedFileName">
            <p>{{ selectedFileName }}</p>
            <button class="btn-remove" @click="removeFile">Remove</button>
          </div>
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

const progressKegiatan = ref("");
const isLoading = ref(false);
const errorMessage = ref("");
const locationName = ref("Tech Innovations Hub, Jakarta (Simulated)");
const coords = ref({ lat: null, lng: null });
const currentTimeStr = ref("");

// camera
const videoRef = ref(null);
const canvasRef = ref(null);
const photoCaptured = ref(false);
const photoBlob = ref(null);
const cameraReady = ref(false);

// file upload fallback
const fileInput = ref(null);
const selectedFile = ref(null);
const selectedFileName = ref("");

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
      (err) => console.error("Error getting location:", err),
    );
  }
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Validate file size (max 5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
      errorMessage.value = "File size must not exceed 5MB";
      return;
    }

    // Validate file type
    const validTypes = ["image/jpeg", "image/png", "application/pdf"];
    if (!validTypes.includes(file.type)) {
      errorMessage.value = "Only JPEG, PNG, or PDF files are allowed";
      return;
    }

    selectedFile.value = file;
    selectedFileName.value = file.name;
    errorMessage.value = "";
  }
};

const removeFile = () => {
  selectedFile.value = null;
  selectedFileName.value = "";
};

const submitAttendance = async () => {
  if (!progressKegiatan.value) {
    errorMessage.value = "Please fill in your daily activity.";
    return;
  }

  // if no photo captured yet, take a photo
  if (!photoCaptured.value) {
    capturePhoto();
    await new Promise((r) => setTimeout(r, 200));
  }

  if (!photoCaptured.value || !photoBlob.value) {
    errorMessage.value = "Please take a photo for clock-out.";
    return;
  }

  isLoading.value = true;
  errorMessage.value = "";

  try {
    const formData = new FormData();
    formData.append("progress_kegiatan", progressKegiatan.value);
    formData.append("lat", coords.value.lat || -6.2);
    formData.append("long", coords.value.lng || 106.816666);
    // always use camera photo for clock-out
    formData.append("photo", photoBlob.value);
    // evidence is optional - only add if selected
    if (selectedFile.value) {
      formData.append("evidence", selectedFile.value);
    }

    await api.post("/clock-out", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    alert("Clock-out successful!");
    emit("go-back");
  } catch (error) {
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
        const dataUrl = canvasRef.value.toDataURL("image/png");
        const arr = dataUrl.split(",");
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        let u8arr = new Uint8Array(n);
        while (n--) u8arr[n] = bstr.charCodeAt(n);
        photoBlob.value = new Blob([u8arr], { type: mime });
      }
      photoCaptured.value = true;
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
.error-text {
  color: #ef4444;
  font-size: 12px;
  margin-top: 10px;
  text-align: center;
}
.selected-file p {
  font-size: 13px;
  color: #334155;
  margin-bottom: 8px;
}
.btn-remove {
  background: #fee2e2;
  color: #ef4444;
  border: none;
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 11px;
  cursor: pointer;
}
.coords {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 2px;
}
</style>

<style scoped>
/* --- 1. LAYOUT UTAMA --- */
.clock-out-page {
  background-color: #f8fafc;
  height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.header-fixed {
  flex-shrink: 0;
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

/* Scroll Area */
.content-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 0 20px 20px 20px;
  scrollbar-width: none;
}
.content-scroll::-webkit-scrollbar {
  display: none;
}

/* Footer */
.footer-fixed {
  flex-shrink: 0;
  background: white;
  padding: 20px;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
  text-align: center;
  z-index: 20;
}

/* --- 2. KAMERA & INFO --- */
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
  overflow: visible;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
/* Perbaikan Gambar: Object Fit Cover agar full */
.camera-feed {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Capture button overlay */
.capture-wrapper {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 20;
  pointer-events: auto;
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

/* --- 3. FORM STYLE (DIPERBAIKI AGAR TIDAK MELEBER) --- */
.info-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 10px;
}

.info-card,
.form-card {
  background: white;
  padding: 16px;
  border-radius: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
  width: 100%;
  box-sizing: border-box; /* PENTING: Agar padding tidak bikin meleber */
}

/* Style Info Lokasi & Waktu */
.info-card {
  display: flex;
  gap: 15px;
  align-items: flex-start;
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
.card-content {
  flex: 1;
  text-align: left !important;
  padding-left: 5px;
}
.label {
  font-size: 10px;
  font-weight: 600;
  color: #94a3b8;
  margin-bottom: 4px;
  display: block;
}
.value {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  margin: 0;
  line-height: 1.4;
}
.time-row {
  display: flex;
  justify-content: space-between;
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
}

/* Header Form (Activity & Evidence) */
.card-header-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}
.card-header-row img {
  width: 16px;
  opacity: 0.8;
}
.label-title {
  font-size: 10px;
  font-weight: 600;
  color: #94a3b8;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

/* FIX TEXTAREA MELEBER */
.custom-textarea {
  width: 100%;
  height: 100px;
  box-sizing: border-box; /* PENTING: Kunci agar tidak meleber */
  border: 1px solid #f1f5f9;
  background: #f8fafc;
  border-radius: 16px;
  padding: 15px;
  font-family: "Inter", sans-serif;
  font-size: 13px;
  color: #334155;
  resize: none;
  outline: none;
}
.custom-textarea:focus {
  border-color: #bfdbfe;
  background: white;
}

/* FIX UPLOAD AREA MELEBER */
.upload-area {
  width: 100%;
  box-sizing: border-box; /* PENTING */
  border: 2px dashed #e2e8f0;
  border-radius: 16px;
  padding: 25px 15px;
  text-align: center;
  background-color: #f8fafc;
  transition: all 0.2s;
}
.upload-icon {
  width: 32px;
  opacity: 0.5;
  margin-bottom: 5px;
}
.upload-text {
  font-size: 11px;
  color: #94a3b8;
  line-height: 1.5;
  margin: 0;
}
.btn-pilih-file {
  margin-top: 10px;
  background: white;
  border: 1px solid #e2e8f0;
  color: #3b82f6;
  font-size: 12px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
}

/* BUTTON ACTION */
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
