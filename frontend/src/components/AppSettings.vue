<script setup>
import { ref } from "vue";

const emit = defineEmits(["go-back"]);

// State untuk toggle (simulasi)
const isDarkMode = ref(false);
const isNotificationOn = ref(true);

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  // Logika ganti tema bisa ditaruh di sini nanti
  if (isDarkMode.value) {
    document.documentElement.classList.add("dark");
  } else {
    document.documentElement.classList.remove("dark");
  }
};
</script>

<template>
  <div class="main-wrapper">
    <div class="mobile-frame">
      
      <div class="blue-header">
        <div class="header-content">
          <button class="btn-back" @click="$emit('go-back')">❮</button>
          <h2>App Settings</h2>
          <div style="width: 24px"></div>
        </div>
      </div>

      <div class="content-area">
        <div class="settings-card">
          
          <p class="section-label">PREFERENCES</p>
          
          <div class="setting-row" @click="toggleDarkMode">
            <div class="row-left">
              <div class="icon-box-new">
                <svg viewBox="0 0 24 24" class="icon-svg">
                  <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
              </div>
              <span>Dark Mode</span>
            </div>
            <div class="toggle-switch" :class="{ active: isDarkMode }">
              <div class="ball"></div>
            </div>
          </div>

          <div class="setting-row" @click="isNotificationOn = !isNotificationOn">
            <div class="row-left">
              <div class="icon-box-new">
                <svg viewBox="0 0 24 24" class="icon-svg">
                  <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                  <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
              </div>
              <span>Push Notifications</span>
            </div>
            <div class="toggle-switch" :class="{ active: isNotificationOn }">
              <div class="ball"></div>
            </div>
          </div>

          <div class="divider"></div>

          <p class="section-label">SYSTEM & INFO</p>

          <div class="setting-row action">
            <div class="row-left">
              <div class="icon-box-new">
                <svg viewBox="0 0 24 24" class="icon-svg">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="2" y1="12" x2="22" y2="12"></line>
                  <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
              </div>
              <span>Language</span>
            </div>
            <span class="lang-text">Indonesia (ID)</span>
          </div>

          <div class="setting-row action">
            <div class="row-left">
              <div class="icon-box-new">
                <svg viewBox="0 0 24 24" class="icon-svg">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="16" x2="12" y2="12"></line>
                  <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
              </div>
              <span>App Version</span>
            </div>
            <span class="version-tag">v1.0.4-stable</span>
          </div>

          <div class="divider"></div>
          <p class="footer-note">Aplikasi ini dikelola oleh Tim IT Presensi Magang.</p>

            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
/* Pastikan font Inter sudah ter-import */
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

.main-wrapper { 
  background-color: #e2e8f0; 
  display: flex; 
  justify-content: center; 
  min-height: 100vh; 
  font-family: "Inter", sans-serif; 
}

.mobile-frame { 
  width: 100%; 
  max-width: 480px; 
  height: 100vh; 
  background-color: #f8fafc; 
  position: relative; 
  overflow: hidden; 
  display: flex; 
  flex-direction: column; 
}

/* HEADER BIRU */
.blue-header {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  height: 140px; 
  border-bottom-left-radius: 40px; 
  border-bottom-right-radius: 40px;
  padding: 20px 25px; 
  color: white; 
  z-index: 1;
}

.header-content { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
.header-content h2 { font-size: 18px; font-weight: 700; margin: 0; }

.btn-back { 
  border: none; 
  background: rgba(255, 255, 255, 0.2); 
  color: white; 
  padding: 8px 12px; 
  border-radius: 12px; 
  cursor: pointer; 
  backdrop-filter: blur(5px); 
}

/* CONTENT AREA */
.content-area { flex: 1; padding: 0 20px; margin-top: -50px; z-index: 2; overflow-y: auto; }

.settings-card { 
  background: white; 
  border-radius: 24px; 
  padding: 20px; 
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); 
}

.section-label { 
  font-size: 11px; 
  font-weight: 700; 
  color: #94a3b8; 
  letter-spacing: 1px; 
  margin: 15px 0 10px 5px; 
  text-transform: uppercase;
}

.setting-row {
  display: flex; 
  justify-content: space-between; 
  align-items: center;
  padding: 12px 5px; 
  cursor: pointer;
}

.row-left { display: flex; align-items: center; gap: 15px; }
.row-left span { font-size: 14px; font-weight: 500; color: #1e293b; }

/* --- HAPUS STYLE ICON LAMA, GANTI DENGAN INI --- */

.icon-box-new {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  /* Pakai warna biru muda transparan yang seragam kayak di Profile */
  background-color: #eff6ff; 
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-svg {
  width: 18px;
  height: 18px;
  /* Warna biru utama yang estetik */
  stroke: #3b82f6; 
  stroke-width: 2; /* Ketebalan garis icon */
  fill: none; /* Biar cuma garis, gak ada isinya (estetik) */
  stroke-linecap: round;
  stroke-linejoin: round;
}

/* --- JANGAN LUPA CSS TOGGLE & CARD LAINNYA TETAP SAMA --- */

/* Toggle Switch Style */
.toggle-switch {
  width: 44px; 
  height: 24px; 
  background: #cbd5e1; 
  border-radius: 20px;
  position: relative; 
  transition: 0.3s;
}

.toggle-switch.active { background: #3b82f6; }

.ball {
  width: 18px; 
  height: 18px; 
  background: white; 
  border-radius: 50%;
  position: absolute; 
  top: 3px; 
  left: 3px; 
  transition: 0.3s;
}

.active .ball { left: 23px; }

.lang-text, .version-tag { font-size: 13px; color: #64748b; font-weight: 500; }
.divider { height: 1px; background: #f1f5f9; margin: 10px 0; }

.footer-note {
  font-size: 11px;
  color: #94a3b8;
  text-align: center;
  margin-top: 20px;
  font-style: italic;
  line-height: 1.4;
}
</style>