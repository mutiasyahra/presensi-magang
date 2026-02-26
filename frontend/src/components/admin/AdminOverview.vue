<script setup>
defineProps(['stats']);
</script>

<template>
  <section class="overview-grid">
    <div class="stat-card blue" style="--delay: 0.1s">
      <div class="card-content">
        <p class="label">TOTAL INTERNS</p>
        <h3 class="value">{{ stats.total_interns }}</h3>
        <span class="trend">+2 this week</span>
      </div>
      <div class="icon-bg">🎓</div>
    </div>

    <div class="stat-card green" style="--delay: 0.2s">
      <div class="card-content">
        <p class="label">PRESENT TODAY</p>
        <h3 class="value">{{ stats.present }}</h3>
        <span class="trend">98% Attendance</span>
      </div>
      <div class="icon-bg">
        <img src="../../assets/present.png" alt="Present">
      </div>
    </div>

    <div class="stat-card orange" style="--delay: 0.3s">
      <div class="card-content">
        <p class="label">LATE TODAY</p>
        <h3 class="value">{{ stats.late }}</h3>
        <span class="trend text-warning">Needs review</span>
      </div>
      <div class="icon-bg">
        <img src="../../assets/late.png" alt="Late">
      </div>
    </div>

    <div class="stat-card red" style="--delay: 0.4s">
      <div class="card-content">
        <p class="label">ABSENT TODAY</p>
        <h3 class="value">{{ stats.absent }}</h3>
        <span class="trend text-danger">Check notifications</span>
      </div>
      <div class="icon-bg">
        <img src="../../assets/absent.png" alt="Absent">
      </div>
    </div>
  </section>
</template>

<style scoped>
.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 24px;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 24px;
  position: relative;
  overflow: hidden;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  border: 1px solid rgba(255,255,255,0.8);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  animation: slideUp 0.6s ease-out forwards;
  opacity: 0;
  animation-delay: var(--delay);
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.stat-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.card-content {
  position: relative;
  z-index: 2;
}

.label {
  color: #64748B;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.value {
  font-size: 36px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 4px 0;
  letter-spacing: -1px;
}

.trend {
  font-size: 11px;
  font-weight: 600;
  color: #94A3B8;
}

.icon-bg {
  width: 70px; height: 70px;
  background: #F8FAFC;
  border-radius: 20px;
  display: flex; align-items: center; justify-content: center;
  font-size: 32px;
  transition: all 0.3s ease;
}

.icon-bg img { width: 40px; height: 40px; object-fit: contain; }

/* Themes */
.stat-card.blue { border-left: 6px solid #3B82F6; }
.stat-card.green { border-left: 6px solid #10B981; }
.stat-card.orange { border-left: 6px solid #F59E0B; }
.stat-card.red { border-left: 6px solid #EF4444; }

.stat-card.blue:hover .icon-bg { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
.stat-card.green:hover .icon-bg { background: rgba(16, 185, 129, 0.1); }
.stat-card.orange:hover .icon-bg { background: rgba(245, 158, 11, 0.1); }
.stat-card.red:hover .icon-bg { background: rgba(239, 68, 68, 0.1); }

.text-warning { color: #D97706; }
.text-danger { color: #DC2626; }
</style>
