<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../api/axios";

// Menerima data 'type' dari App.vue
const props = defineProps({
  type: {
    type: String,
    default: "in"
  },
  attendanceId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(["navigate"]);

const workPlan = ref("");
const imageUrl = ref("");
const locationText = ref("");
const timestampText = ref("");
const isLate = ref(false);

// Membuat teks otomatis berubah tergantung 'type'
const pageTitle = computed(() => props.type === 'in' ? 'Edit Clock In' : 'Edit Clock Out');
const sectionTitle = computed(() => props.type === 'in' ? 'DAILY WORK PLAN' : 'DAILY ACTIVITY');

const getStorageUrl = (path) => {
  if (!path) return "";
  const baseUrl = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api";
  return baseUrl.replace('/api', '') + '/storage/' + path;
};

const fetchAttendanceData = async () => {
  if (!props.attendanceId) return;
  try {
    const res = await api.get("/calendar");
    if (res.data && res.data.attendance) {
      const att = res.data.attendance.find(a => a.id == props.attendanceId);
      if (att) {
        if (props.type === "in") {
          workPlan.value = att.rencana_kegiatan || "-";
          imageUrl.value = getStorageUrl(att.clock_in_photo);
          locationText.value = `Lat: ${att.clock_in_lat || "-"}, Long: ${att.clock_in_long || "-"}`;
          
          if (att.clock_in) {
            const d = new Date(att.clock_in);
            timestampText.value = d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }) + " • " + d.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });
          } else {
            timestampText.value = "-";
          }
          isLate.value = att.status === "terlambat";
        } else {
          workPlan.value = att.progress_kegiatan || "-";
          imageUrl.value = getStorageUrl(att.clock_out_photo);
          locationText.value = `Lat: ${att.clock_out_lat || "-"}, Long: ${att.clock_out_long || "-"}`;
          
          if (att.clock_out) {
             const d = new Date(att.clock_out);
             timestampText.value = d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }) + " • " + d.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });
          } else {
             timestampText.value = "-";
          }
        }
      }
    }
  } catch (err) {
    console.error("Gagal mengambil data attendance:", err);
  }
};

onMounted(() => {
  fetchAttendanceData();
});

const saveChanges = () => {
  alert("Perubahan berhasil disimpan! (Dummy - Update fitur tidak tersedia di backend)");
};
</script>

<template>
  <div class="screen-container">
      <div class="top-fixed-area">
        <div class="header">
          <button class="btn-back" @click="$emit('navigate', 'history')">
            ❮
          </button>
          <h2>{{ pageTitle }}</h2>
          <div style="width: 24px"></div>
        </div>
      </div>

      <div class="scroll-area">
        <div class="content-padding">
          
          <div class="section-title">
            <img src="../assets/camera.png" alt="Camera" class="section-icon" />
            <span>IDENTITY VERIFICATION</span>
          </div>
          <div class="photo-wrapper">
            <img
              v-if="imageUrl"
              :src="imageUrl"
              alt="Camera photo"
              class="captured-photo"
            />
            <div v-else class="captured-photo empty-photo flex items-center justify-center bg-gray-200">
               <span class="text-gray-500">No Photo</span>
            </div>
          </div>

          <div class="info-card-container">
            <div class="info-card">
              <div class="icon-box blue-soft">
                <img src="../assets/current location.png" alt="Location" />
              </div>
              <div class="card-content">
                <p class="label">CURRENT LOCATION</p>
                <p class="value text-left">
                  {{ locationText }}
                </p>
              </div>
            </div>

            <hr class="divider" />

            <div class="info-card">
              <div class="icon-box blue-soft">
                <img src="../assets/jam.png" alt="Time" /> 
              </div>
              <div class="card-content time-row">
                <div>
                  <p class="label text-left">TIMESTAMP</p>
                  <p class="value">{{ timestampText }}</p>
                </div>
                <span v-if="props.type === 'in' && isLate" class="badge-late">LATE</span>
                <span v-else-if="props.type === 'in'" class="badge-ontime">ON TIME</span>
                <span v-else class="badge-out">DONE</span>
              </div>
            </div>
          </div>

          <div class="section-title">
            <img src="../assets/daily plan.png" alt="Plan" class="section-icon" />
            <span>{{ sectionTitle }}</span>
          </div>
          <div class="work-plan-card relative-box">
            <textarea
              v-model="workPlan"
              class="plan-input"
              rows="5"
            ></textarea>
            <img src="../assets/pencil.png" alt="Edit" class="edit-icon-corner" />
          </div>

          <button class="btn-submit" @click="saveChanges">
            Simpan Perubahan
          </button>

          <div style="height: 100px"></div>
        </div>
      </div>

      <div class="bottom-nav">
        <div class="nav-item" @click="$emit('navigate', 'dashboard')">
          <img src="../assets/home.png" alt="Home" />
          <span>Home</span>
        </div>

        <div class="nav-item active" @click="$emit('navigate', 'history')">
          <img src="../assets/history.png" alt="History" />
          <span>History</span>
        </div>

        <div class="nav-item-scan-wrapper">
          <div class="scan-button">
            <img src="../assets/qr.png" alt="Scan" />
          </div>
        </div>

        <div class="nav-item" @click="$emit('navigate', 'leave')">
          <img src="../assets/leave.png" alt="Leave">
          <span>Leave</span>
        </div>

        <div class="nav-item" @click="$emit('navigate', 'profile')">
          <img src="../assets/profile.png" alt="Profile">
          <span>Profile</span>
        </div>
      </div>

  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

/* --- MAIN LAYOUT (Sama seperti Dashboard) --- */
.screen-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: #f8fafc;
  overflow: hidden;
  font-family: "Inter", sans-serif;
  position: relative;
}

/* --- TOP FIXED AREA --- */
.top-fixed-area {
  background-color: #f8fafc;
  padding: 20px 20px 0 20px;
  z-index: 10;
  flex-shrink: 0;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.header h2 {
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.btn-back {
  border: none;
  background: none;
  font-size: 20px;
  cursor: pointer;
  color: #2563eb;
  font-weight: bold;
}

/* --- SCROLL AREA --- */
.scroll-area {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: none;
}
.scroll-area::-webkit-scrollbar {
  display: none;
}
.content-padding {
  padding: 10px 20px 20px 20px;
}

/* --- COMPONENT STYLES --- */
.section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}
.section-icon {
  width: 16px;
  height: 16px;
  opacity: 0.5;
  /* Paksa ikon hitam jadi abu-abu */
  filter: brightness(0) saturate(100%) invert(64%) sepia(12%) saturate(417%) hue-rotate(182deg) brightness(94%) contrast(90%);
}
.section-title span {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.5px;
}

/* Photo */
.photo-wrapper {
  margin-bottom: 24px;
}
.captured-photo {
  width: 100%;
  aspect-ratio: 4/3;
  object-fit: cover;
  border-radius: 30px;
  border: 4px solid white;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

/* Info Cards Container (Glassmorphism look) */
.info-card-container {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
  border-radius: 30px;
  padding: 20px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
  margin-bottom: 24px;
}

.info-card {
  display: flex;
  gap: 15px;
  align-items: flex-start;
}

.icon-box {
  width: 44px;
  height: 44px;
  background: #f1f5f9;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.icon-box img {
  width: 22px;
  height: 22px;
  object-fit: contain;
  filter: brightness(0) saturate(100%) invert(43%) sepia(74%) saturate(3015%) hue-rotate(213deg) brightness(96%) contrast(97%); /* Warna biru icon */
}

.card-content {
  flex: 1;
}
.text-left {
  text-align: left;
}

.label {
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  margin: 0 0 6px 0;
  text-transform: uppercase;
}
.value {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  margin: 0;
  line-height: 1.5;
}

.divider {
  border: 0;
  border-top: 1px solid #f1f5f9;
  margin: 16px -20px; /* Tarik garis mentok kiri kanan */
}

.time-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.badge-ontime {
  background: #eff6ff;
  color: #2563eb;
  font-size: 11px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 20px;
  letter-spacing: 0.5px;
}
.badge-late {
  background: #fef9c3;
  color: #a16207;
  font-size: 11px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 20px;
  letter-spacing: 0.5px;
}
.badge-out {
  background: #dbeafe;
  color: #1e40af;
  font-size: 11px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 20px;
  letter-spacing: 0.5px;
}
.empty-photo {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f1f5f9;
  border-radius: 24px;
}
.text-gray-500 {
  color: #64748b;
  font-weight: 600;
}

/* Work Plan */
.work-plan-card {
  background: white;
  border-radius: 24px;
  padding: 20px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
  margin-bottom: 30px;
}
.relative-box {
  position: relative;
}
.plan-input {
  width: 100%;
  border: none;
  background: transparent;
  font-family: "Inter", sans-serif;
  font-size: 13px;
  color: #475569;
  line-height: 1.6;
  resize: none;
  outline: none;
}
.edit-icon-corner {
  position: absolute;
  bottom: 0px;
  right: 0px;
  width: 16px;
  opacity: 0.3;
}

/* Button Submit */
.btn-submit {
  width: 100%;
  background: #2563eb;
  color: white;
  border: none;
  padding: 18px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
  transition: transform 0.1s;
}
.btn-submit:active {
  transform: scale(0.98);
}

/* --- BOTTOM NAV (Sama Persis History) --- */
.bottom-nav {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: white;
  height: 80px;
  border-top-left-radius: 30px;
  border-top-right-radius: 30px;
  box-shadow: 0 -5px 30px rgba(0, 0, 0, 0.05);
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  align-items: center;
  padding: 0 10px;
}
.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  color: #94A3B8;
}
.nav-item img {
  width: 24px;
  height: 24px;
  filter: brightness(0) saturate(100%) invert(75%) sepia(11%) saturate(545%) hue-rotate(182deg) brightness(87%) contrast(85%);
  transition: all 0.2s ease;
}
.nav-item span { font-size: 10px; font-weight: 600; }
.nav-item.active { color: #2563EB; }
.nav-item.active img {
  filter: brightness(0) saturate(100%) invert(26%) sepia(93%) saturate(3015%) hue-rotate(213deg) brightness(96%) contrast(97%);
}
.nav-item-scan-wrapper { display: flex; justify-content: center; align-items: center; height: 100%; }
.scan-button {
  width: 50px;
  height: 50px;
  background: #2563eb;
  border-radius: 16px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
  margin-top: -30px;
  border: 4px solid white;
}
.scan-button img {
  width: 24px;
  height: 24px;
  filter: brightness(0) invert(1);
}
</style>