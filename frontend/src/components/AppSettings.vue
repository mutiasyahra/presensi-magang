<script setup>
import { ref } from "vue";

const emit = defineEmits(["go-back"]);

// State untuk toggle (simulasi)
const isDarkMode = ref(document.documentElement.classList.contains("dark"));
const isNotificationOn = ref(true);

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  // documentElement theme logic removed for user side

  // Save to localStorage immediately so App.vue uses it
  const userStr = localStorage.getItem("user");
  if (userStr) {
    try {
      const userObj = JSON.parse(userStr);
      userObj.is_dark_mode = isDarkMode.value;
      localStorage.setItem("user", JSON.stringify(userObj));
    } catch (e) {
      console.error("Failed to parse user from localStorage", e);
    }
  }
};
</script>

<template>
  <div class="screen-container">
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
                <path
                  d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"
                ></path>
              </svg>
            </div>
            <span class="menu-label">Dark Mode</span>
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
            <span class="menu-label">Push Notifications</span>
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
                <path
                  d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"
                ></path>
              </svg>
            </div>
            <span class="menu-label">Language</span>
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
            <span class="menu-label">App Version</span>
          </div>
          <span class="version-tag">v1.0.4-stable</span>
        </div>

        <div class="divider"></div>
        <p class="footer-note">
          Aplikasi ini dikelola oleh Tim IT Presensi Magang.
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Pastikan font Inter sudah ter-import */
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

.main-wrapper {
  background-color: var(--bg-mobile-shell, #e2e8f0);
  display: flex;
  justify-content: center;
  min-height: 100vh;
  font-family: "Inter", sans-serif;
  transition: background-color 0.3s ease;
}

/* HEADER STYLE */
.blue-header {
background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);  height: 140px;
  border-bottom-left-radius: 40px;
  border-bottom-right-radius: 40px;
  padding: 20px 25px;
  color: white;
  z-index: 1;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.header-content { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
.header-content h2 { font-size: 18px; font-weight: 700; margin: 0; }
.btn-back { border: none; background: rgba(255, 255, 255, 0.2); color: white; padding: 8px 16px; border-radius: 12px; cursor: pointer; }

/* CONTENT AREA */
.content-area { flex: 1; padding: 0 20px; margin-top: -50px; overflow-y: auto; }

/* KARTU SETTINGS (DINAMIS) */
.settings-card {
  background-color: var(--bg-card) !important; /* Pakai variabel agar bisa gelap/terang */
  border-radius: 24px;
  padding: 25px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.section-label {
  color: var(--text-muted); /* Gunakan variabel font sekunder */
  font-size: 11px; font-weight: 700; letter-spacing: 1px; margin-bottom: 10px;
}

.setting-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 5px; cursor: pointer; }
.row-left { display: flex; align-items: center; gap: 15px; }

.menu-label {
  font-size: 14px; font-weight: 500;
  color: var(--text-main); /* Font otomatis jadi putih saat gelap */
}

/* ICON STYLING */
.icon-box-new {
  width: 36px; height: 36px; border-radius: 10px;
  background-color: rgba(59, 130, 246, 0.1); /* Biru transparan agar tidak nabrak background card */
  display: flex; align-items: center; justify-content: center;
}

.icon-svg { width: 18px; height: 18px; stroke: #3b82f6; stroke-width: 2; fill: none; }

/* TOGGLE & DIVIDER */
.toggle-switch { width: 44px; height: 24px; background: #cbd5e1; border-radius: 20px; position: relative; transition: 0.3s; }
.toggle-switch.active { background: #3b82f6; }
.ball { width: 18px; height: 18px; background: white; border-radius: 50%; position: absolute; top: 3px; left: 3px; transition: 0.3s; }
.active .ball { left: 23px; }

.divider { height: 1px; background: var(--border-color); margin: 15px 0; }
.lang-text, .version-tag { font-size: 13px; color: var(--text-muted); font-weight: 500; }
.footer-note { font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 20px; font-style: italic; }
</style>
