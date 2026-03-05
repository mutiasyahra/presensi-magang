<script setup>
import { ref, onMounted } from "vue";
import api from "../../api/axios.js";

// Modular Components (now in the same directory)
import AdminSidebar from "./AdminSidebar.vue";
import AdminOverview from "./AdminOverview.vue";
import AdminAttendance from "./AdminAttendance.vue";
import LiveMonitor from "./LiveMonitor.vue";
import AdminLeaves from "./AdminLeaves.vue";
import AdminReports from "./AdminReports.vue";
import SettingsScreen from "./SettingsScreen.vue";

const activeTab = ref("overview");
const stats = ref({
  present: 0,
  late: 0,
  absent: 0,
  pending_leaves: 0,
  total_interns: 0,
});
const attendanceList = ref([]);
const leaveRequests = ref([]);
const isLoading = ref(false);

const fetchData = async () => {
  isLoading.value = true;
  try {
    const statsRes = await api.get("/stats");
    stats.value = statsRes.data;
    console.log("[Admin] Stats dari API:", stats.value);

    const attRes = await api.get("/attendances");
    attendanceList.value = attRes.data?.data || attRes.data || [];
    console.log(
      "[Admin] Attendance list dari API:",
      attendanceList.value.length,
      "record(s)",
    );

    const leaveRes = await api.get("/leaves");
    leaveRequests.value = leaveRes.data?.data || leaveRes.data || [];
    console.log(
      "[Admin] Leave requests dari API:",
      leaveRequests.value.length,
      "record(s)",
    );
  } catch (error) {
    console.error(
      "[Admin] Gagal mengambil data:",
      error.response?.status,
      error.response?.data || error.message,
    );
  } finally {
    isLoading.value = false;
  }
};

const approveLeave = async (id, status) => {
  try {
    await api.patch(`/leave/${id}`, { status });
    alert(`Leave request ${status}`);
    fetchData();
  } catch (error) {
    alert("Action failed.");
  }
};

onMounted(fetchData);
</script>

<template>
  <div class="admin-shell">
    <AdminSidebar
      v-model:activeTab="activeTab"
      :pendingLeaves="stats.pending_leaves"
      @logout="$emit('logout')"
    />

    <main class="main-content">
      <div v-if="isLoading" class="loader">
        <div class="spinner"></div>
        <p>Updating records...</p>
      </div>

      <div v-else class="content-wrapper">
        <AdminOverview
          v-if="activeTab === 'overview'"
          :stats="stats"
          :attendanceList="attendanceList"
        />

        <AdminAttendance v-if="activeTab === 'attendance'" />

        <AdminLeaves
          v-if="activeTab === 'leaves'"
          :leaveRequests="leaveRequests"
          @approve="approveLeave"
        />

        <AdminReports v-if="activeTab === 'reports'" />

        <LiveMonitor v-if="activeTab === 'live'" />

        <SettingsScreen v-if="activeTab === 'settings'" />
      </div>
    </main>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap");

/* --- Top Header Style --- */
.top-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px; /* Memberi jarak dengan 4 kotak di bawahnya */
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.page-title {
  font-size: 24px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-bar {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
}

.search-bar input {
  padding: 10px 16px 10px 38px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  outline: none;
  width: 260px;
  background-color: white;
  transition: all 0.2s ease;
}

.search-bar input::placeholder {
  color: #94a3b8;
}

.search-bar input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.icon-btn {
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.icon-btn:hover {
  background-color: #f1f5f9;
  color: #0f172a;
}

.admin-shell {
  display: flex;
  height: 100vh;
  width: 100vw;
  background: #f8fafc;
  font-family: "Plus Jakarta Sans", sans-serif;
  overflow: hidden;
}

.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 40px 50px;
  background:
    radial-gradient(
      circle at top right,
      rgba(59, 130, 246, 0.03) 0%,
      transparent 40%
    ),
    radial-gradient(
      circle at bottom left,
      rgba(124, 58, 237, 0.03) 0%,
      transparent 40%
    );
}

.top-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 50px;
  animation: fadeInDown 0.8s ease-out;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.breadcrumb {
  font-size: 11px;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-weight: 700;
  margin-bottom: 8px;
  display: block;
}

.top-header h1 {
  font-size: 34px;
  color: #0f172a;
  font-weight: 800;
  letter-spacing: -1px;
  margin: 0;
}

.user-pill {
  background: white;
  padding: 10px 20px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.8);
  transition: all 0.3s ease;
}

.user-pill:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.06);
}

.avatar-circle {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
}

.admin-name {
  font-weight: 700;
  color: #334155;
  font-size: 14px;
}

.loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 50vh;
  gap: 20px;
  color: #94a3b8;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.1);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.content-wrapper {
  width: 100%;
}
</style>
