<template>
  <div class="settings-container">
    <div class="page-header">
      <h1 class="main-title">Admin System Settings</h1>
      <p class="subtitle">Manage your administrative account and global platform configurations.</p>
    </div>

    <div class="settings-card">
      <h2 class="card-title">Account Settings</h2>
      
      <div class="form-grid">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" v-model="account.fullName" placeholder="Enter your full name" />
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" v-model="account.email" placeholder="Enter your email" />
        </div>
      </div>

      <div class="form-group mt-4">
        <label>Update Password</label>
        <input type="password" v-model="account.password" placeholder="Enter new password" />
        <span class="help-text">Leave blank if you don't want to change it.</span>
      </div>

      <div class="card-footer">
        <button class="btn-primary" @click="saveAccount">Save Changes</button>
      </div>
    </div>

    <div class="settings-card">
      <h2 class="card-title">System Configuration</h2>
      
      <div class="form-grid">
        <div class="form-group">
          <label>
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Work Hours Setup
          </label>
          <div class="time-inputs">
            <input type="time" v-model="system.startTime" />
            <span class="to-text">to</span>
            <input type="time" v-model="system.endTime" />
          </div>
          <span class="help-text">System will automatically mark interns as late after the start time.</span>
        </div>

        <div class="form-group full-width">
          <label>
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            Office Location & Geofencing
          </label>
          <div class="location-setup">
            <div class="address-input-wrapper">
              <div class="form-group mb-2">
                <label class="sub-label">OFFICE NAME</label>
                <input type="text" v-model="system.officeName" placeholder="e.g. Tech Innovations Hub" />
              </div>
              <div class="form-group">
                <label class="sub-label">FULL ADDRESS</label>
                <textarea 
                  v-model="system.officeAddress" 
                  placeholder="Enter office full address..."
                  rows="3"
                  class="address-textarea"
                ></textarea>
              </div>
              <button class="btn-detect" @click="detectOfficeLocation" :disabled="isDetecting">
                <svg v-if="!isDetecting" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span v-else class="spinner-small"></span>
                {{ isDetecting ? 'Detecting...' : 'Detect Current' }}
              </button>
            </div>
            <div class="coords-grid">
              <div class="coord-input">
                <span class="coord-label">LATITUDE</span>
                <input type="text" v-model="system.officeLat" placeholder="-6.200000" />
              </div>
              <div class="coord-input">
                <span class="coord-label">LONGITUDE</span>
                <input type="text" v-model="system.officeLng" placeholder="106.816666" />
              </div>
            </div>
          </div>
          <span class="help-text">This location will be used to verify intern attendance and shown on their dashboard.</span>
        </div>
      </div>

      <div class="card-footer">
        <button class="btn-primary" @click="saveSystem">Update Settings</button>
      </div>
    </div>

    <div class="settings-card">
      <h2 class="card-title">Notification Preferences</h2>
      
      <div class="toggle-row">
        <div class="toggle-info">
          <h3>Late Intern Alerts</h3>
          <p>Send an immediate email notification when an intern is 15 minutes late.</p>
        </div>
        <label class="switch">
          <input type="checkbox" v-model="notifications.lateAlerts">
          <span class="slider round"></span>
        </label>
      </div>

      <div class="divider"></div>

      <div class="toggle-row">
        <div class="toggle-info">
          <h3>Leave Request Notifications</h3>
          <p>Get notified whenever a new leave request is submitted for approval.</p>
        </div>
        <label class="switch">
          <input type="checkbox" v-model="notifications.leaveRequests">
          <span class="slider round"></span>
        </label>
      </div>
    </div>

    <div class="settings-card theme-card">
      <div class="toggle-info">
        <h2 class="card-title mb-1">App Theme</h2>
        <p>Switch between light and dark visual modes.</p>
      </div>
      <button class="theme-slider-switch" :class="{ 'is-dark': isDarkMode }" @click="toggleTheme">
        <div class="theme-slider-thumb">
          <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
        </div>
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import api from '../../api/axios.js';
import Swal from 'sweetalert2';

const localUserRaw = localStorage.getItem('user');
let localUser = {};
try {
  if (localUserRaw) localUser = JSON.parse(localUserRaw);
} catch (e) {}

// Data State
const account = reactive({
  fullName: localUser.name || '',
  email: localUser.email || '',
  password: ''
});

const system = reactive({
  startTime: '09:00',
  endTime: '17:00',
  officeName: '',
  officeAddress: '',
  officeLat: '',
  officeLng: ''
});

const isDetecting = ref(false);

const detectOfficeLocation = () => {
    if (!navigator.geolocation) {
        Swal.fire('Error', 'Browser Anda tidak mendukung geolokasi.', 'error');
        return;
    }

    isDetecting.value = true;
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            system.officeLat = lat.toFixed(6);
            system.officeLng = lng.toFixed(6);

            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                    headers: { 'Accept-Language': 'id' }
                });
                const data = await res.json();
                system.officeAddress = data.display_name || "Location detected";
            } catch (err) {
                console.error("Reverse Geocoding error:", err);
                system.officeAddress = "Location detected (Address unavailable)";
            } finally {
                isDetecting.value = false;
            }
        },
        (err) => {
            isDetecting.value = false;
            console.error("Error getting location:", err);
            Swal.fire('Error', 'Gagal mengakses lokasi. Pastikan izin lokasi diberikan.', 'error');
        }
    );
};

const notifications = reactive({
  lateAlerts: localUser.notify_late_alerts !== undefined ? !!localUser.notify_late_alerts : true,
  leaveRequests: localUser.notify_leave_requests !== undefined ? !!localUser.notify_leave_requests : false
});

const isDarkMode = ref(document.documentElement.classList.contains('dark'));
const isLoaded = ref(false);

const loadSettings = async () => {
    try {
        console.log("[Settings] Loading settings from server...");
        const userRes = await api.get('/settings/me');
        if (userRes.data?.data) {
            const u = userRes.data.data;
            console.log("[Settings] User data fetched:", u);
            
            account.fullName = u.fullName || '';
            account.email = u.email || '';
            
            // Sync preferences
            notifications.lateAlerts = u.notifyLateAlerts == true || u.notifyLateAlerts == 1 || u.notifyLateAlerts == "1";
            notifications.leaveRequests = u.notifyLeaveRequests == true || u.notifyLeaveRequests == 1 || u.notifyLeaveRequests == "1";
            
            // Sync local storage to keep it updated with server truth
            const userStr = localStorage.getItem('user');
            if (userStr) {
                try {
                    const userObj = JSON.parse(userStr);
                    userObj.notify_late_alerts = notifications.lateAlerts;
                    userObj.notify_leave_requests = notifications.leaveRequests;
                    localStorage.setItem('user', JSON.stringify(userObj));
                } catch (e) {}
            }
        }

        const sysRes = await api.get('/settings/system');
        if (sysRes.data?.data) {
            const s = sysRes.data.data;
            system.startTime = s.startTime || '09:00';
            system.endTime = s.endTime || '17:00';
            system.officeName = s.officeName || '';
            system.officeAddress = s.officeAddress || '';
            system.officeLat = s.officeLat || '';
            system.officeLng = s.officeLng || '';
        }
    } catch (error) {
        console.error("Failed to load settings:", error);
    } finally {
        // Use a small delay to ensure reactivity has settled
        setTimeout(() => {
            isLoaded.value = true;
            console.log("[Settings] Load complete, isLoaded set to true");
        }, 300);
    }
};

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    
    // Save to localStorage immediately so App.vue uses it
    const userStr = localStorage.getItem('user');
    if (userStr) {
        try {
            const userObj = JSON.parse(userStr);
            userObj.is_dark_mode = isDarkMode.value;
            localStorage.setItem('user', JSON.stringify(userObj));
        } catch (e) {
            console.error("Failed to parse user from localStorage", e);
        }
    }
};

const saveAccount = async (silent = false) => {
    try {
        const payload = {
            fullName: account.fullName,
            email: account.email,
            isDarkMode: isDarkMode.value,
            notifyLateAlerts: notifications.lateAlerts,
            notifyLeaveRequests: notifications.leaveRequests,
        };

        if (account.password.trim() !== '') {
            payload.password = account.password;
        }

        await api.post('/settings/me', payload);

        // Update localStorage user object so App.vue uses latest settings on fresh load
        const userStr = localStorage.getItem('user');
        if (userStr) {
            try {
                const userObj = JSON.parse(userStr);
                userObj.name = account.fullName;
                userObj.email = account.email;
                userObj.is_dark_mode = isDarkMode.value;
                userObj.notify_late_alerts = notifications.lateAlerts;
                userObj.notify_leave_requests = notifications.leaveRequests;
                localStorage.setItem('user', JSON.stringify(userObj));
            } catch (e) {
                console.error("Failed to parse user from localStorage", e);
            }
        }

        if (!silent) {
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan',
                text: 'Pengaturan akun berhasil diperbarui!',
                timer: 1500,
                showConfirmButton: false
            });
            account.password = ''; // clear password field
        }
    } catch (error) {
        console.error("Failed to save account settings", error);
        if (!silent) {
            Swal.fire('Error', 'Gagal menyimpan pengaturan akun: ' + (error.response?.data?.message || 'Terjadi kesalahan sistem.'), 'error');
        }
    }
};

const saveSystem = async () => {
    try {
        const payload = {
            startTime: system.startTime,
            endTime: system.endTime,
            officeName: system.officeName,
            officeAddress: system.officeAddress,
            officeLat: system.officeLat,
            officeLng: system.officeLng,
        };
        await api.put('/settings/system', payload);
        Swal.fire({
            icon: 'success',
            title: 'Tersimpan',
            text: 'Konfigurasi sistem berhasil diperbarui!',
            timer: 1500,
            showConfirmButton: false
        });
    } catch (error) {
        console.error("Failed to save system settings", error);
        Swal.fire('Error', 'Gagal menyimpan konfigurasi sistem: ' + (error.response?.data?.message || 'Terjadi kesalahan sistem.'), 'error');
    }
};

watch(
  [() => notifications.lateAlerts, () => notifications.leaveRequests, isDarkMode],
  ([newLate, newLeave, newDark], [oldLate, oldLeave, oldDark]) => {
    if (!isLoaded.value) return;
    
    // Only save if values actually changed to avoid unnecessary API calls
    if (newLate !== oldLate || newLeave !== oldLeave || newDark !== oldDark) {
        console.log("[Settings] Preference change detected, auto-saving...");
        saveAccount(true); 
    }
  }
);

onMounted(() => {
    loadSettings();
});
</script>

<style scoped>
.settings-container {
  padding: 32px;
  max-width: 1000px;
  margin: 0 auto;
  color: var(--text-main);
  font-family: var(--font-main);
}

.page-header {
  margin-bottom: 32px;
}

.main-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0 0 8px 0;
}

.subtitle {
  color: var(--text-muted);
  font-size: 15px;
  margin: 0;
}

/* Card Styles */
.settings-card {
  background: var(--bg-card);
  border-radius: 12px;
  padding: 24px 32px;
  margin-bottom: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  border: 1px solid var(--border-color);
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-main);
  margin: 0 0 24px 0;
}

.mb-1 { margin-bottom: 4px; }

/* Form Elements */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-group label .icon {
  color: var(--accent-primary);
}

.mt-4 { margin-top: 24px; }

input[type="text"],
input[type="email"],
input[type="password"],
input[type="number"],
input[type="time"] {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  color: var(--text-main);
  background-color: var(--bg-input);
  transition: all 0.2s;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: var(--accent-primary);
  background-color: var(--bg-card);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.help-text {
  font-size: 12px;
  color: var(--text-dim);
  margin-top: 8px;
}

/* Custom Inputs Layouts */
.time-inputs {
  display: flex;
  align-items: center;
  gap: 12px;
}

.to-text {
  color: var(--text-muted);
  font-size: 14px;
}

.input-with-suffix {
  position: relative;
  display: flex;
  align-items: center;
}

.input-with-suffix input {
  padding-right: 80px; /* Ruang untuk text METERS */
}

.input-with-suffix .suffix {
  position: absolute;
  right: 14px;
  color: var(--text-dim);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

/* Location Setup Styles */
.full-width {
    grid-column: 1 / -1;
}

.location-setup {
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: var(--bg-input);
    padding: 24px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
}

.sub-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: var(--text-dim) !important;
    margin-bottom: 6px !important;
    letter-spacing: 0.5px;
}

.mb-2 { margin-bottom: 12px; }

.address-input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.address-textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
    background-color: var(--bg-input);
    color: var(--text-main);
    resize: none;
    font-family: inherit;
    transition: all 0.2s;
    box-sizing: border-box;
}

.address-textarea:focus {
    outline: none;
    border-color: var(--accent-primary);
    background-color: var(--bg-card);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-detect {
    align-self: flex-start;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--accent-primary);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-detect:hover:not(:disabled) {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

.btn-detect:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.coords-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.coord-input {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.coord-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-dim);
    letter-spacing: 0.5px;
}

.spinner-small {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Buttons */
.card-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid var(--border-color);
}

.btn-primary {
  background-color: var(--accent-primary);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  filter: brightness(1.1);
}

/* Toggles & Lists */
.toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.toggle-info h3 {
  font-size: 15px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: var(--text-main);
}

.toggle-info p {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0;
}

.divider {
  height: 1px;
  background-color: var(--border-color);
  margin: 20px 0;
}

/* CSS Toggle Switch (Mirip iOS/Figma) */
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--text-dim);
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
}

input:checked + .slider {
  background-color: var(--accent-success);
}

input:checked + .slider:before {
  transform: translateX(20px);
}

.slider.round {
  border-radius: 24px;
}

.slider.round:before {
  border-radius: 50%;
}

/* App Theme Card Layout */
.theme-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.theme-slider-switch {
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  border-radius: 20px;
  width: 56px;
  height: 28px;
  display: flex;
  align-items: center;
  padding: 3px;
  cursor: pointer;
  transition: all 0.3s ease;
  outline: none;
}

.theme-slider-switch:hover {
  background: var(--border-color);
}

.theme-slider-switch.is-dark {
  background: #1e293b;
  border-color: var(--accent-primary);
}

.theme-slider-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: white;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1), background 0.3s ease;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
  color: #64748b;
}

.theme-slider-switch.is-dark .theme-slider-thumb {
  transform: translateX(28px);
  background: #0f1015;
  color: var(--accent-warning);
}

/* Responsive */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr; /* Jadi satu kolom di HP */
  }
}
</style>