<script setup>
defineProps(['leaveRequests']);
const emit = defineEmits(['approve']);
</script>

<template>
  <section class="leaves-container">
    <div class="grid-header">
      <div class="header-info">
        <h3>Pending Requests</h3>
        <p>Review and manage student leave applications</p>
      </div>
      <div class="header-stats">
        <span class="stat-badge">
          <strong>{{ leaveRequests.filter(l => l.status === 'pending').length }}</strong> Pending
        </span>
      </div>
    </div>

    <div class="leaves-grid">
      <div v-for="(leave, index) in leaveRequests" :key="leave.id" 
           class="leave-card" :style="{'--delay': (index * 0.1) + 's'}">
        
        <div class="leave-header">
          <div class="user-meta">
            <div class="avatar-sm">{{ leave.user?.name?.charAt(0) || 'U' }}</div>
            <div>
              <h4>{{ leave.user?.name || 'User' }}</h4>
              <span class="leave-date">{{ leave.date }}</span>
            </div>
          </div>
          <div class="type-tag" :class="leave.type.toLowerCase()">
            {{ leave.type }}
          </div>
        </div>

        <div class="leave-body">
          <p class="reason-label">REASON</p>
          <p class="leave-reason">"{{ leave.reason }}"</p>
        </div>

        <div class="leave-footer">
          <div class="leave-actions" v-if="leave.status === 'pending'">
            <button class="btn-reject" @click="$emit('approve', leave.id, 'rejected')">Decline</button>
            <button class="btn-approve" @click="$emit('approve', leave.id, 'approved')">Approve Request</button>
          </div>
          <div v-else class="status-result" :class="leave.status">
            <span class="icon">{{ leave.status === 'approved' ? '✅' : '❌' }}</span>
            {{ leave.status.toUpperCase() }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.leaves-container {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.grid-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-info h3 { font-size: 20px; font-weight: 700; color: #0F172A; margin: 0 0 4px 0; }
.header-info p { font-size: 13px; color: #64748B; margin: 0; }

.stat-badge {
  background: #F1F5F9;
  padding: 6px 16px;
  border-radius: 99px;
  font-size: 13px;
  color: #475569;
}
.stat-badge strong { color: #3B82F6; }

.leaves-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 24px;
}

.leave-card {
  background: white;
  padding: 24px;
  border-radius: 24px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  gap: 20px;
  border: 1px solid rgba(255,255,255,0.8);
  animation: slideUp 0.6s ease-out forwards;
  opacity: 0;
  animation-delay: var(--delay);
  transition: transform 0.3s;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.leave-card:hover { transform: translateY(-5px); }

.leave-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.user-meta {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-sm {
  width: 40px; height: 40px;
  background: #F1F5F9;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; color: #3B82F6;
}

.user-meta h4 { font-size: 15px; margin: 0; color: #0F172A; }
.leave-date { font-size: 12px; color: #94A3B8; }

.type-tag {
  font-size: 10px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 8px;
  text-transform: uppercase;
}
.type-tag.sick { background: #fee2e2; color: #ef4444; }
.type-tag.personal { background: #eff6ff; color: #3b82f6; }

.reason-label {
  font-size: 10px;
  font-weight: 700;
  color: #94A3B8;
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.leave-reason {
  font-size: 14px;
  color: #475569;
  line-height: 1.6;
  margin: 0;
}

.leave-footer {
  margin-top: auto;
  padding-top: 15px;
  border-top: 1px solid #F1F5F9;
}

.leave-actions {
  display: flex;
  gap: 12px;
}

.leave-actions button {
  flex: 1;
  padding: 12px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-approve { background: #3B82F6; color: white; border: none; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); }
.btn-approve:hover { background: #2563EB; transform: scale(1.02); }

.btn-reject { background: #F8FAFC; color: #64748B; border: 1px solid #E2E8F0; }
.btn-reject:hover { background: #F1F5F9; border-color: #CBD5E1; }

.status-result {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 800;
  padding: 10px;
  border-radius: 12px;
}
.status-result.approved { color: #10B981; background: #DCFCE7; }
.status-result.rejected { color: #EF4444; background: #FEE2E2; }
</style>
