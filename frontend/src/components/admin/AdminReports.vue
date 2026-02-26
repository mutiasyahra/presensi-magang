<script setup>
import api from '../../api/axios.js';

const downloadRecap = async () => {
  try {
    const response = await api.get('/recap-monthly', { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'Recap_Absensi.pdf');
    document.body.appendChild(link);
    link.click();
  } catch (error) {
    alert("Download failed.");
  }
};
</script>

<template>
  <section class="reports-container">
    <div class="report-card primary" style="--delay: 0.1s">
      <div class="report-content">
        <div class="icon-bubble">
          <img src="../../assets/daily report.png" alt="PDF">
        </div>
        <h3>Monthly Recap</h3>
        <p>Generate a professional PDF summary of all intern activities and statuses.</p>
        <button class="btn-download" @click="downloadRecap">
          <span>Download PDF</span>
          <span class="arrow">→</span>
        </button>
      </div>
    </div>

    <div class="report-card secondary" style="--delay: 0.2s">
      <div class="report-content">
        <div class="icon-bubble">
          <img src="../../assets/history.png" alt="Excel">
        </div>
        <h3>Raw Data Export</h3>
        <p>Export all row records to Microsoft Excel for custom data analysis.</p>
        <button class="btn-download">
          <span>Export Excel</span>
          <span class="arrow">→</span>
        </button>
      </div>
    </div>
  </section>
</template>

<style scoped>
.reports-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 30px;
}

.report-card {
  background: white;
  border-radius: 32px;
  padding: 40px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  border: 1px solid rgba(255,255,255,0.8);
  animation: slideUp 0.8s ease-out forwards;
  opacity: 0;
  animation-delay: var(--delay);
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.report-content {
  position: relative;
  z-index: 2;
}

.icon-bubble {
  width: 60px; height: 60px;
  background: #F1F5F9;
  border-radius: 20px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 25px;
  transition: transform 0.3s;
}
.icon-bubble img { width: 30px; }

.report-card:hover .icon-bubble { transform: scale(1.1) rotate(5deg); }

.report-card h3 { font-size: 22px; font-weight: 800; color: #0F172A; margin: 0 0 12px 0; }
.report-card p { font-size: 14px; color: #64748B; line-height: 1.6; margin: 0 0 30px 0; }

.btn-download {
  background: #3B82F6;
  color: white;
  border: none;
  padding: 14px 24px;
  border-radius: 16px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  width: 100%;
  transition: all 0.3s;
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.2);
}

.btn-download:hover { background: #2563EB; transform: translateY(-2px); box-shadow: 0 12px 25px rgba(59, 130, 246, 0.3); }

.report-card.secondary .btn-download {
  background: #F1F5F9;
  color: #475569;
  box-shadow: none;
}
.report-card.secondary .btn-download:hover { background: #E2E8F0; }

.report-card::after {
  content: '';
  position: absolute;
  top: -50px; right: -50px;
  width: 150px; height: 150px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0) 70%);
  border-radius: 50%;
}
</style>
