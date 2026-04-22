<script setup>
import { ref, computed } from 'vue';

const emit = defineEmits(['go-back', 'profile-updated']);

const userInfo = ref({ name: "Aileen" });
const currentAvatar = ref(null); 
const fileInput = ref(null);
const userInitial = computed(() => userInfo.value.name ? userInfo.value.name.charAt(0).toUpperCase() : 'U');

const passwordData = ref({ current: '', new: '', confirm: '' });

const triggerFileInput = () => { fileInput.value.click(); };
const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = (e) => { currentAvatar.value = e.target.result; };
    reader.readAsDataURL(file);
  }
};

const saveChanges = () => {
  if (passwordData.value.new || passwordData.value.confirm) {
    if (!passwordData.value.current) { alert('Masukkan password saat ini.'); return; }
    if (passwordData.value.new !== passwordData.value.confirm) { alert('Password baru tidak cocok.'); return; }
  }
  alert('Perubahan disimpan!');
  emit('go-back'); 
};
</script>

<template>
  <div class="screen-container">
    <div class="blue-header">
      <div class="header-content">
        <button class="btn-back" @click="$emit('go-back')">❮</button>
        <h2>Account Settings</h2>
        <div style="width: 32px"></div> </div>
    </div>

    <div class="content-area">
      <div class="overlap-card">
        
        <div class="editable-avatar-section">
          <div class="avatar-container">
            <img v-if="currentAvatar" :src="currentAvatar" class="avatar-img-preview" />
            <div v-else class="avatar-circle-edit">{{ userInitial }}</div>
            <div class="edit-icon-badge" @click="triggerFileInput">
              <img src="../assets/pencil.png" alt="Change" />
            </div>
            <input type="file" ref="fileInput" class="hidden-input" @change="handleFileChange" accept="image/*" />
          </div>
          <p class="edit-photo-tip" @click="triggerFileInput">Tap to change profile photo</p>
        </div>

        <div class="divider"></div>

        <div class="editable-data-group">
          <h4 class="group-title">SECURITY SETTINGS</h4>
          
          <div class="input-item">
            <label>Current Password</label>
            <div class="input-wrapper">
              <input type="password" v-model="passwordData.current" placeholder="Enter current password" class="form-input" />
              <img src="../assets/lock.png" class="input-icon" />
            </div>
          </div>

          <div class="input-item">
            <label>New Password</label>
            <div class="input-wrapper">
              <input type="password" v-model="passwordData.new" placeholder="Min. 6 characters" class="form-input" />
              <img src="../assets/lock.png" class="input-icon" />
            </div>
          </div>

          <div class="input-item">
            <label>Confirm New Password</label>
            <div class="input-wrapper">
              <input type="password" v-model="passwordData.confirm" placeholder="Repeat new password" class="form-input" />
              <img src="../assets/lock.png" class="input-icon" />
            </div>
          </div>
        </div>

        <button class="btn-save-large" @click="saveChanges">
          Save Changes
        </button>

      </div>
      <div style="height: 40px;"></div>
    </div>
  </div>
</template>

<style scoped>
.screen-container {
  display: flex; flex-direction: column; height: 100vh; width: 100%;
  background-color: #f8fafc; font-family: "Inter", sans-serif; overflow: hidden;
}

.blue-header {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  height: 140px; border-bottom-left-radius: 40px; border-bottom-right-radius: 40px;
  padding: 10px 25px; color: white; flex-shrink: 0;
}

.header-content { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
.header-content h2 { font-size: 18px; font-weight: 700; margin: 0; }
.btn-back { border: none; background: rgba(255, 255, 255, 0.2); color: white; font-size: 18px; padding: 8px 12px; border-radius: 12px; cursor: pointer; }

.content-area { flex: 1; padding: 0 20px; margin-top: -55px; overflow-y: auto; scrollbar-width: none; }
.overlap-card { background: white; border-radius: 24px; padding: 30px 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); }

/* Avatar Styling */
.editable-avatar-section { display: flex; flex-direction: column; align-items: center; margin-bottom: 25px; }
.avatar-container { position: relative; width: 90px; height: 90px; }
.avatar-img-preview { width: 100%; height: 100%; object-fit: cover; border-radius: 24%; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
.avatar-circle-edit { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #3b82f6; border-radius: 24%; color: white; font-size: 32px; font-weight: 700; border: 4px solid white; }
.edit-icon-badge { position: absolute; bottom: -2px; right: -2px; width: 30px; height: 30px; background: #2563eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; }
.edit-icon-badge img { width: 14px; filter: brightness(0) invert(1); }
.edit-photo-tip { font-size: 13px; color: #3b82f6; font-weight: 600; margin-top: 12px; cursor: pointer; }

.divider { height: 1px; background: #f1f5f9; margin-bottom: 25px; }

/* Input Styling (Anti Hitam) */
.group-title { font-size: 11px; color: #94a3b8; font-weight: 700; letter-spacing: 1px; margin-bottom: 15px; text-align: left; }
.input-item { margin-bottom: 18px; text-align: left; }
.input-item label { font-size: 12px; color: #475569; font-weight: 600; margin-left: 2px; display: block; margin-bottom: 6px; }

.input-wrapper { position: relative; width: 100%; }
.form-input {
  width: 100%; padding: 14px 14px 14px 45px; 
  border-radius: 14px; border: 1.5px solid #e2e8f0;
  font-size: 14px; color: #1e293b; background-color: var(--bg-card) !important; /* Paksa Putih */
  box-sizing: border-box; transition: 0.2s;
}
.form-input:focus { border-color: #3b82f6; outline: none; background-color: var(--bg-card); }
.input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; opacity: 0.4; }

/* Tombol Save Baru */
.btn-save-large {
  width: 100%; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white; border: none; padding: 16px; border-radius: 16px;
  font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 10px;
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.25); transition: 0.2s;
}
.btn-save-large:active { transform: scale(0.98); }
.hidden-input { display: none; }
</style>