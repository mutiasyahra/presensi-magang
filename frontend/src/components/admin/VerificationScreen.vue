<script setup>
// Nanti kamu bisa lempar data intern (props) ke sini saat integrasi API
// Membuat "sinyal" bernama 'close' untuk dikirim ke halaman induk
const emit = defineEmits(['close']);
const goBack = () => {
  emit('close'); // Mengirim sinyal 'close' saat tombol back diklik
  // Logic untuk kembali ke halaman Live Monitor
  // Contoh jika pakai emits: emit('close');
  // Contoh jika pakai router: router.back();
  console.log("Kembali ke Live Monitor");
};
</script>

<template>
  <div class="verification-wrapper">
    <header class="detail-header">
      <div class="header-left">
        <button class="btn-back" @click="goBack">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
          Back
        </button>
        <div class="divider"></div>
        <div class="profile-info">
          <img src="https://i.pravatar.cc/150?img=11" alt="Michael Chen" class="avatar" />
          <div class="user-details">
            <h3>Michael Chen</h3>
            <p>Backend Developer Intern</p>
          </div>
          <span class="badge-warning">⚠️ PENDING VERIFICATION</span>
        </div>
      </div>
      <div class="header-actions">
        <button class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg></button>
        <button class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg></button>
      </div>
    </header>

    <div class="content-grid">
      
      <div class="timeline-section">
        <div class="section-title">
          <h2>Activity Timeline</h2>
          <span class="date">Monday, Oct 24, 2023</span>
        </div>

        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-icon icon-blue">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            </div>
            <div class="timeline-content">
              <div class="event-header">
                <div>
                  <h4>Clock In</h4>
                  <p class="location">📍 Singapore, Science Park Drive</p>
                </div>
                <span class="time">09:41 AM</span>
              </div>
              
              <div class="evidence-card">
                <div class="selfie-placeholder bg-blue"></div>
                <div class="evidence-footer">
                  <span class="label">📷 Morning Selfie</span>
                  <span class="status text-green">✅ GPS Verified</span>
                </div>
              </div>

              <div class="info-card">
                <p class="card-title">📝 DAILY WORK PLAN</p>
                <ol>
                  <li>Complete the integration of the user authentication service.</li>
                  <li>Write unit tests for the new API endpoints.</li>
                  <li>Coordinate with the frontend team regarding JWT storage implementation.</li>
                </ol>
              </div>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-icon icon-red">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </div>
            <div class="timeline-content">
              <div class="event-header">
                <div>
                  <h4>Clock Out</h4>
                  <p class="location">📍 Singapore, Science Park Drive</p>
                </div>
                <span class="time">06:05 PM</span>
              </div>
              
              <div class="evidence-card">
                <div class="selfie-placeholder bg-red"></div>
                <div class="evidence-footer">
                  <span class="label">📷 Afternoon Selfie</span>
                  <span class="status text-green">✅ Validated</span>
                </div>
              </div>

              <div class="info-card">
                <p class="card-title">📉 DAILY ACTIVITY SUMMARY</p>
                <p class="summary-text">Successfully integrated the auth service and passed all unit tests. Fixed 2 minor bugs in the database schema. Documentation updated and pushed to the staging branch for review.</p>
              </div>

              <div class="attachments">
                <p class="card-title">📎 EVIDENCE / ATTACHMENTS</p>
                <div class="attachment-images">
                  <div class="img-box"></div>
                  <div class="img-box"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="sidebar-section">
        
        <div class="widget-card">
          <div class="widget-header">
            <h4>Location Verification</h4>
            <span class="badge-blue">REMOTE</span>
          </div>
          <div class="map-placeholder">
            <div class="pin">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            </div>
            <p>Map view of Singapore</p>
          </div>
          <div class="accuracy-info">
            <span class="icon">📍</span>
            <div>
              <p class="title">Coordinate Accuracy</p>
              <p class="desc">± 12 meters within authorized perimeter</p>
            </div>
          </div>
        </div>

        <div class="widget-card">
          <h4>Daily Stats Summary</h4>
          <div class="stats-row">
            <div class="stat-box">
              <p class="label">TOTAL HOURS</p>
              <p class="value"><strong>8.4</strong> hrs</p>
            </div>
            <div class="stat-box">
              <p class="label">STATUS</p>
              <p class="value text-green"><strong>On Time</strong></p>
            </div>
          </div>
        </div>

        <div class="widget-card">
          <h4>Review Notes</h4>
          <textarea placeholder="Add a comment or internal note..."></textarea>
          <label class="checkbox-container">
            <input type="checkbox" />
            <span class="checkmark"></span>
            Flag for Follow-up
          </label>
        </div>

      </div>
    </div>

    <div class="action-footer">
      <button class="btn-reject">Reject Attendance</button>
      <button class="btn-verify">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        Verify Attendance
      </button>
    </div>
  </div>
</template>

<style scoped>
.verification-wrapper {
  background: white;
  min-height: 100vh;
  position: relative;
  display: flex;
  flex-direction: column;
}

/* --- HEADER --- */
.detail-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 32px; border-bottom: 1px solid #E2E8F0;
  position: sticky; top: 0; background: white; z-index: 10;
}

.header-left { display: flex; align-items: center; gap: 16px; }

.btn-back {
  display: flex; align-items: center; gap: 4px; border: none; background: transparent;
  font-size: 14px; font-weight: 600; color: #0F172A; cursor: pointer; padding: 8px 12px;
  border-radius: 8px; transition: 0.2s;
}
.btn-back:hover { background: #F1F5F9; }

.divider { width: 1px; height: 32px; background: #E2E8F0; }

.profile-info { display: flex; align-items: center; gap: 12px; }
.avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.user-details h3 { margin: 0; font-size: 15px; font-weight: 700; color: #0F172A; }
.user-details p { margin: 0; font-size: 12px; color: #64748B; }

.badge-warning {
  background: #FEFCE8; color: #D97706; border: 1px solid #FEF08A;
  font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px; letter-spacing: 0.5px;
}

.header-actions { display: flex; gap: 8px; }
.btn-icon {
  background: transparent; border: none; color: #64748B; width: 36px; height: 36px;
  border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;
}
.btn-icon:hover { background: #F1F5F9; color: #0F172A; }

/* --- CONTENT GRID --- */
.content-grid {
  display: grid; grid-template-columns: 2fr 1fr; gap: 32px;
  padding: 32px; max-width: 1200px; margin: 0 auto; width: 100%;
  padding-bottom: 100px; /* Ruang untuk sticky footer */
}

/* --- KIRI: TIMELINE --- */
.section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.section-title h2 { margin: 0; font-size: 18px; font-weight: 700; color: #0F172A; }
.section-title .date { font-size: 14px; color: #64748B; font-weight: 500; }

.timeline { position: relative; padding-left: 16px; }
.timeline::before {
  content: ''; position: absolute; left: 23px; top: 8px; bottom: 0;
  width: 2px; background: #E2E8F0;
}

.timeline-item { display: flex; gap: 24px; margin-bottom: 40px; position: relative; z-index: 2; }
.timeline-item:last-child { margin-bottom: 0; }

.timeline-icon {
  width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
  background: white; flex-shrink: 0; margin-top: 4px;
}
.icon-blue { color: #3B82F6; box-shadow: 0 0 0 4px white; }
.icon-red { color: #EF4444; box-shadow: 0 0 0 4px white; }

.timeline-content { flex: 1; }

.event-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
.event-header h4 { margin: 0 0 4px 0; font-size: 16px; font-weight: 700; color: #0F172A; }
.event-header .location { margin: 0; font-size: 13px; color: #64748B; }
.event-header .time { background: #F1F5F9; color: #64748B; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 6px; }

.evidence-card { border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; margin-bottom: 16px; }
.selfie-placeholder { height: 200px; background-size: cover; background-position: center; }
.bg-blue { background-color: #0F172A; /* Ganti dgn URL foto asli nanti */ }
.bg-red { background-color: #0F172A; }

.evidence-footer { display: flex; justify-content: space-between; padding: 12px 16px; background: white; font-size: 13px; font-weight: 600; }
.evidence-footer .label { color: #64748B; }
.text-green { color: #10B981; }

.info-card { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
.card-title { margin: 0 0 12px 0; font-size: 11px; font-weight: 700; color: #94A3B8; letter-spacing: 0.5px; }
.info-card ol { margin: 0; padding-left: 20px; color: #334155; font-size: 14px; line-height: 1.6; }
.summary-text { margin: 0; color: #334155; font-size: 14px; line-height: 1.6; }

.attachments { margin-top: 24px; }
.attachment-images { display: flex; gap: 12px; }
.img-box { width: 160px; height: 100px; background: #1E293B; border-radius: 8px; }

/* --- KANAN: SIDEBAR --- */
.sidebar-section { display: flex; flex-direction: column; gap: 24px; }

.widget-card { border: 1px solid #E2E8F0; border-radius: 16px; padding: 20px; background: white; }
.widget-card h4 { margin: 0 0 16px 0; font-size: 14px; font-weight: 700; color: #0F172A;  }

.widget-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.widget-header h4 { margin: 0; }
.badge-blue { background: #EFF6FF; color: #3B82F6; font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 4px; }

.map-placeholder {
  height: 140px; background: #DBEAFE; border-radius: 12px; display: flex; flex-direction: column;
  align-items: center; justify-content: center; margin-bottom: 16px; gap: 8px;
}
.map-placeholder .pin { width: 40px; height: 40px; background: #3B82F6; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(59,130,246,0.3); }
.map-placeholder p { margin: 0; font-size: 12px; color: #60A5FA; font-weight: 600; }

.accuracy-info { display: flex; gap: 12px; align-items: flex-start; }
.accuracy-info .title { margin: 0; font-size: 13px; font-weight: 600; color: #0F172A; }
.accuracy-info .desc { margin: 2px 0 0 0; font-size: 12px; color: #64748B; }

.stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.stat-box { background: #F8FAFC; padding: 16px; border-radius: 12px; border: 1px solid #E2E8F0; }
.stat-box .label { margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #64748B; }
.stat-box .value { margin: 0; font-size: 14px; color: #0F172A; }

textarea {
  width: 100%; height: 100px; border: 1px solid #E2E8F0; border-radius: 12px; padding: 12px;
  font-family: inherit; font-size: 14px; color: #0F172A; resize: none; outline: none; margin-bottom: 16px; background: transparent;
}
textarea:focus { border-color: #3B82F6; }

.checkbox-container { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #0F172A; cursor: pointer; background: transparent; }

/* --- FOOTER ACTIONS --- */
.action-footer {
  position: fixed; bottom: 0; right: 0; width: 100%; /* Atau sesuaikan dengan lebar area konten utama */
  background: white; border-top: 1px solid #E2E8F0; padding: 16px 32px;
  display: flex; justify-content: flex-end; gap: 16px; z-index: 20;
}

.btn-reject {
  background: white; border: 1px solid #EF4444; color: #EF4444; padding: 10px 24px;
  border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-reject:hover { background: #FEF2F2; }

.btn-verify {
  background: #3B82F6; border: none; color: white; padding: 10px 24px;
  border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s;
  display: flex; align-items: center; gap: 8px;
}
.btn-verify:hover { background: #2563EB; }
</style>