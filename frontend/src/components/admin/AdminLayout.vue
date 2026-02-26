<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api/axios.js';

// Modular Components (now in the same directory)
import AdminSidebar from './AdminSidebar.vue';
import AdminOverview from './AdminOverview.vue';
import AdminAttendance from './AdminAttendance.vue';
import AdminLeaves from './AdminLeaves.vue';
import AdminReports from './AdminReports.vue';

const activeTab = ref('overview');
const stats = ref({
  present: 0,
  late: 0,
  absent: 0,
  pending_leaves: 0,
  total_interns: 0
});
const attendanceList = ref([]);
const leaveRequests = ref([]);
const isLoading = ref(false);

const fetchData = async () => {
  isLoading.value = true;
  try {
    const statsRes = await api.get('/stats');
    stats.value = statsRes.data;

    const attRes = await api.get('/attendances');
    attendanceList.value = attRes.data.data;

    const leaveRes = await api.get('/leaves');
    leaveRequests.value = leaveRes.data.data;
  } catch (error) {
    console.error("Error fetching admin data:", error);
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
      <header class="top-header">
        <div class="title-section">
          <span class="breadcrumb">ADMIN PORTAL / {{ activeTab }}</span>
          <h1>{{ activeTab.charAt(0).toUpperCase() + activeTab.slice(1) }}</h1>
        </div>
        <div class="user-pill">
          <div class="avatar-circle">👨‍💼</div>
          <span class="admin-name">Administrator</span>
        </div>
      </header>

      <div v-if="isLoading" class="loader">
        <div class="spinner"></div>
        <p>Updating records...</p>
      </div>

      <div v-else class="content-wrapper">
        <AdminOverview v-if="activeTab === 'overview'" :stats="stats" />
        
        <AdminAttendance v-if="activeTab === 'attendance'" :attendanceList="attendanceList" />
        
        <AdminLeaves v-if="activeTab === 'leaves'" :leaveRequests="leaveRequests" @approve="approveLeave" />
        
        <AdminReports v-if="activeTab === 'reports'" />
      </div>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.admin-shell {
  display: flex;
  height: 100vh;
  width: 100vw;
  background: #F8FAFC;
  font-family: 'Plus Jakarta Sans', sans-serif;
  overflow: hidden;
}

.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 40px 50px;
  background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.03) 0%, transparent 40%),
              radial-gradient(circle at bottom left, rgba(124, 58, 237, 0.03) 0%, transparent 40%);
}

.top-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 50px;
  animation: fadeInDown 0.8s ease-out;
}

@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

.breadcrumb {
  font-size: 11px;
  color: #94A3B8;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-weight: 700;
  margin-bottom: 8px;
  display: block;
}

.top-header h1 {
  font-size: 34px;
  color: #0F172A;
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
  box-shadow: 0 10px 20px rgba(0,0,0,0.03);
  border: 1px solid rgba(255,255,255,0.8);
  transition: all 0.3s ease;
}

.user-pill:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(0,0,0,0.06); }

.avatar-circle {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
}

.admin-name { font-weight: 700; color: #334155; font-size: 14px; }

.loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 50vh;
  gap: 20px;
  color: #94A3B8;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #E2E8F0;
  border-top-color: #3B82F6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.1);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.content-wrapper {
  width: 100%;
}
</style>
