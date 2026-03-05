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

        <div class="form-group">
          <label>
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            Geofencing Radius
          </label>
          <div class="input-with-suffix">
            <input type="number" v-model="system.radius" />
            <span class="suffix">METERS</span>
          </div>
          <span class="help-text">Distance within which interns are allowed to clock in via mobile app.</span>
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
      <button class="theme-toggle-btn" :class="{ 'is-dark': isDarkMode }" @click="isDarkMode = !isDarkMode">
        <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

// Data State (Biar formnya ada isinya persis seperti di desain)
const account = reactive({
  fullName: 'Alex Richardson',
  email: 'alex.richardson@company.com',
  password: ''
});

const system = reactive({
  startTime: '08:00',
  endTime: '17:00',
  radius: 100
});

const notifications = reactive({
  lateAlerts: true,
  leaveRequests: false
});

const isDarkMode = ref(false);

// Fungsi simulasi klik tombol
const saveAccount = () => alert('Account settings saved!');
const saveSystem = () => alert('System configuration updated!');
</script>

<style scoped>
.settings-container {
  padding: 32px;
  max-width: 1000px; /* Biar ngga terlalu lebar di layar besar */
  margin: 0 auto;
  color: #1e293b;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.page-header {
  margin-bottom: 32px;
}

.main-title {
  font-size: 24px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 8px 0;
}

.subtitle {
  color: #64748b;
  font-size: 15px;
  margin: 0;
}

/* Card Styles */
.settings-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 24px 32px;
  margin-bottom: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
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
  color: #334155;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-group label .icon {
  color: #3b82f6;
}

.mt-4 { margin-top: 24px; }

input[type="text"],
input[type="email"],
input[type="password"],
input[type="number"],
input[type="time"] {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #334155;
  background-color: #f8fafc;
  transition: all 0.2s;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #3b82f6;
  background-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.help-text {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 8px;
}

/* Custom Inputs Layouts */
.time-inputs {
  display: flex;
  align-items: center;
  gap: 12px;
}

.to-text {
  color: #64748b;
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
  color: #94a3b8;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

/* Buttons */
.card-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 24px;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background-color: #2563eb;
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
  color: #1e293b;
}

.toggle-info p {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

.divider {
  height: 1px;
  background-color: #f1f5f9;
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
  background-color: #cbd5e1;
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
  background-color: #3b82f6;
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

.theme-toggle-btn {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.theme-toggle-btn:hover {
  background: #f1f5f9;
}

.theme-toggle-btn.is-dark {
  background: #1e293b;
  color: #fbbf24;
  border-color: #1e293b;
}

/* Responsive */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr; /* Jadi satu kolom di HP */
  }
}
</style>