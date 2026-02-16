<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

// --- LOGIC WAKTU ---
const timeMain = ref("");   
const timeSecond = ref(""); 
const currentDate = ref("");

const updateTime = () => {
  const now = new Date();
  const h = String(now.getHours()).padStart(2, '0');
  const m = String(now.getMinutes()).padStart(2, '0');
  timeMain.value = `${h}:${m}`;
  timeSecond.value = String(now.getSeconds()).padStart(2, '0');

  const options = { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' };
  currentDate.value = now.toLocaleDateString('en-US', options);
};

let timer;
onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});
onUnmounted(() => {
  clearInterval(timer);
});
</script>

<template>
  <div class="main-wrapper">
    <div class="mobile-frame">
      
      <div class="header-section">
        <div class="top-bar">
          <div class="profile-group">
            <div class="avatar">S</div> 
            <div class="text-info">
              <p class="welcome">WELCOME BACK,</p>
              <h2 class="username">Username</h2>
            </div>
          </div>
          <button class="btn-notif">
            <img src="../assets/notif.png" alt="Notif">
          </button>
        </div>

        <div class="location-card">
          <div class="loc-icon-wrapper">
            <img src="../assets/location.png" alt="Loc" class="loc-img">
          </div>
          <div class="loc-text">
            <p class="loc-label">OFFICE LOCATION</p>
            <h3 class="loc-name">Tech Innovations Hub, Jakarta</h3>
          </div>
          <div class="arrow-right">></div>
        </div>
      </div>

      <div class="scroll-area">
        <div class="content-wrapper">
          
          <div class="time-card">
            <div class="date-row">
              <span class="date-text">{{ currentDate }}</span>
              <span class="work-hours">09:00 - 17:00</span>
            </div>
            <div class="clock-wrapper">
              <h1 class="digital-clock">{{ timeMain }}</h1>
              <span class="seconds-text">{{ timeSecond }}</span>
            </div>
            <p class="clock-label">Current Working Time</p>
            <div class="action-buttons">
              <button class="btn-clock-in">
                <img src="../assets/clock in.png" alt="In">
                Clock In
              </button>
              <button class="btn-clock-out">
                <img src="../assets/clock out.png" alt="Out">
                Clock Out
              </button>
            </div>
          </div>

          <div class="section-title">
            <h3>Monthly Attendance</h3>
            <span class="link">October ▼</span>
          </div>

          <div class="stats-grid">
            <div class="stat-card">
              <img src="../assets/present.png" class="stat-icon">
              <span class="stat-num">20</span>
              <span class="stat-label">Present</span>
            </div>
            <div class="stat-card">
              <img src="../assets/late.png" class="stat-icon">
              <span class="stat-num">02</span>
              <span class="stat-label">Late</span>
            </div>
            <div class="stat-card">
              <img src="../assets/absent.png" class="stat-icon">
              <span class="stat-num">01</span>
              <span class="stat-label">Absent</span>
            </div>
          </div>

          <div class="section-title">
            <h3>Daily Activity</h3>
            <span class="link">View All</span>
          </div>

          <div class="activity-card">
            <div class="act-content">
              <img src="../assets/daily report.png" class="act-icon" alt="Report">
              <div class="act-text">
                <h4>Daily Progress Report</h4>
                <p>Due today at 17:00</p>
              </div>
            </div>
            <button class="btn-submit">Submit</button>
          </div>
          
          <div style="height: 50px;"></div> </div>
      </div>

      <div class="bottom-nav">
        <div class="nav-item active">
          <img src="../assets/home.png" alt="Home">
          <span>Home</span>
        </div>
        <div class="nav-item">
          <img src="../assets/history.png" alt="History">
          <span>History</span>
        </div>
        
        <div class="nav-item-scan-wrapper">
          <div class="scan-button">
             <img src="../assets/qr.png" alt="Scan">
          </div>
        </div>

        <div class="nav-item">
          <img src="../assets/leave.png" alt="Leave">
          <span>Leave</span>
        </div>
        <div class="nav-item">
          <img src="../assets/profile.png" alt="Profile">
          <span>Profile</span>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* --- MAIN LAYOUT --- */
.main-wrapper {
  background-color: #E2E8F0;
  display: flex; justify-content: center;
  min-height: 100vh; margin: 0;
  font-family: 'Poppins', sans-serif;
}
.mobile-frame {
  width: 100%; max-width: 430px; height: 100vh;
  background-color: #F8FAFC; 
  position: relative; overflow: hidden;
  box-shadow: 0 0 40px rgba(0,0,0,0.15);
  display: flex; flex-direction: column;
}

/* --- HEADER --- */
.header-section {
  position: absolute; top: 0; left: 0; right: 0; z-index: 50;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
  color: white;
  padding: 40px 25px 35px 25px; 
  border-bottom-left-radius: 35px; border-bottom-right-radius: 35px;
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.2);
}

/* --- SCROLL CONTENT --- */
.scroll-area {
  flex: 1; overflow-y: auto;
  scrollbar-width: none;
}
.scroll-area::-webkit-scrollbar { display: none; }

.content-wrapper {
  padding-top: 230px;  
  padding-bottom: 120px; /* Tambah padding bawah agar konten terbawah aman */
  padding-left: 24px; padding-right: 24px;
}

/* --- BOTTOM NAV & FLOATING BUTTON --- */
.bottom-nav {
  position: absolute; bottom: 0; left: 0; right: 0; z-index: 100;
  background: white; 
  height: 80px; /* Tinggi fix navbar */
  border-top-left-radius: 30px; border-top-right-radius: 30px;
  box-shadow: 0 -5px 30px rgba(0,0,0,0.08);
  
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  align-items: center; /* Vertikal center */
  padding: 0 10px;
}

/* Item Biasa (Home, History, dll) */
.nav-item { 
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 5px; 
  color: #CBD5E1; font-size: 10px; font-weight: 600; 
  cursor: pointer; 
  height: 100%; /* Full height container */
  padding-top: 10px; /* Sedikit padding atas */
}
.nav-item img { 
  width: 22px; height: 22px; 
  object-fit: contain;
  opacity: 0.5; filter: grayscale(100%); 
  transition: 0.3s; 
}
.nav-item.active { color: #2563EB; }
.nav-item.active img { opacity: 1; filter: grayscale(0%); transform: translateY(-2px); }

/* --- KHUSUS TOMBOL SCAN (THE BIG ONE) --- */
.nav-item-scan-wrapper {
  display: flex; 
  justify-content: center; 
  align-items: flex-end; /* Posisi relatif terhadap navbar */
  height: 100%;
  position: relative;
}

.scan-button {
  width: 60px; height: 60px; /* UKURAN BESAR */
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); /* Warna Biru */
  border-radius: 20px;
  display: flex; justify-content: center; align-items: center;
  
  /* Membuat tombol "melayang" ke atas keluar dari navbar */
  position: absolute; 
  bottom: 30px; /* Dorong ke atas */
  
  /* Shadow & Border agar terlihat misah */
  box-shadow: 0 10px 25px rgba(37, 99, 235, 0.5);
  border: 4px solid #FFFFFF; 
  cursor: pointer;
  transition: transform 0.2s;
}

.scan-button:active { transform: scale(0.95); bottom: 28px; }

.scan-button img {
  width: 28px; height: 28px;
  filter: brightness(0) invert(1); /* PENTING: Mengubah icon QR jadi PUTIH */
}

/* --- COMPONENT STYLES --- */
.top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.profile-group { display: flex; align-items: center; gap: 14px; }
.avatar {
  width: 45px; height: 45px; background: #FECACA; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; color: #7F1D1D; border: 2px solid rgba(255,255,255,0.3);
}
.text-info h2 { margin: 0; font-size: 16px; font-weight: 600; }
.text-info .welcome { margin: 0; font-size: 10px; opacity: 0.9; }
.btn-notif { background: none; border: none; padding: 0; }
.btn-notif img { width: 42px; height: 42px; border-radius: 12px; }

.location-card {
  background: rgba(255, 255, 255, 0.15); 
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 18px; padding: 12px 16px;
  display: flex; align-items: center; gap: 12px;
}
.loc-icon-wrapper { width: 38px; height: 38px; flex-shrink: 0; }
.loc-img { width: 100%; height: 100%; border-radius: 10px; }
.loc-text { flex: 1; text-align: left; }
.loc-label { margin: 0; font-size: 9px; opacity: 0.9; }
.loc-name { margin: 2px 0 0; font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
.arrow-right { font-size: 16px; opacity: 0.7; }

/* TIME CARD */
.time-card {
  background: white; border-radius: 28px; padding: 20px; text-align: center;
  box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06); margin-bottom: 25px;
}
.date-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.date-text { font-weight: 600; color: #334155; font-size: 13px; }
.work-hours { background: #EFF6FF; color: #2563EB; font-size: 11px; padding: 5px 12px; border-radius: 15px; font-weight: 600; }
.clock-wrapper { display: flex; justify-content: center; align-items: baseline; gap: 6px; margin: 10px 0 5px; }
.digital-clock { font-size: 42px; color: #0F172A; margin: 0; font-weight: 700; letter-spacing: -1px; line-height: 1; }
.seconds-text { font-size: 20px; color: #CBD5E1; font-weight: 600; }
.clock-label { color: #94A3B8; font-size: 12px; margin-bottom: 25px; }
.action-buttons { display: flex; gap: 14px; }
.action-buttons button {
  flex: 1; padding: 14px; border-radius: 18px; border: none; cursor: pointer;
  display: flex; flex-direction: column; align-items: center; gap: 6px; font-weight: 600; font-size: 13px;
}
.btn-clock-in { background: #2563EB; color: white; box-shadow: 0 8px 20px -5px rgba(37, 99, 235, 0.4); }
.btn-clock-out { background: #F1F5F9; color: #94A3B8; }
.action-buttons img { width: 22px; }

/* STATS */
.section-title { display: flex; justify-content: space-between; align-items: center; margin: 10px 0 15px; }
.section-title h3 { margin: 0; font-size: 15px; color: #1E293B; font-weight: 700; }
.link { color: #2563EB; font-size: 11px; font-weight: 600; cursor: pointer; }
.stats-grid { display: flex; gap: 10px; margin-bottom: 25px; }
.stat-card {
  flex: 1; background: white; padding: 14px 6px; border-radius: 20px; text-align: center;
  box-shadow: 0 5px 20px -5px rgba(0,0,0,0.04);
}
.stat-icon { width: 28px; margin-bottom: 6px; }
.stat-num { display: block; font-size: 16px; font-weight: 700; color: #1E293B; }
.stat-label { font-size: 10px; color: #94A3B8; font-weight: 500; }

/* ACTIVITY CARD */
.activity-card {
  background: white; padding: 16px; border-radius: 20px; 
  display: flex; align-items: center; 
  justify-content: space-between; 
  box-shadow: 0 5px 20px -5px rgba(0,0,0,0.04);
  gap: 15px; 
}
.act-content { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }
.act-icon { width: 38px; height: 38px; flex-shrink: 0; }
.act-text { text-align: left; }
.act-text h4 { margin: 0; font-size: 13px; color: #1E293B; font-weight: 700; line-height: 1.4; }
.act-text p { margin: 2px 0 0; font-size: 10px; color: #F59E0B; font-weight: 500; }
.btn-submit { 
  background: #2563EB; color: white; border: none; 
  padding: 10px 20px; border-radius: 12px; 
  font-weight: 600; font-size: 11px; flex-shrink: 0; 
}
</style>