<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../api/axios.js';

const emit = defineEmits(['go-back', 'profile-updated']);

const userInfo = ref({ name: "User", profile_photo: null });
const currentAvatar = ref(null); 
const fileInput = ref(null);
const isLoading = ref(false);
const userInitial = computed(() => userInfo.value.name ? userInfo.value.name.charAt(0).toUpperCase() : 'U');

const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('data:') || path.startsWith('http')) return path;
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace("/api", "");
  return `${baseUrl}/storage/${path}`;
};

const passwordData = ref({ current: '', new: '', confirm: '' });

onMounted(async () => {
  try {
    const response = await api.get('/settings/me');
    if (response.data && response.data.status === 'success') {
      userInfo.value = {
        name: response.data.data.fullName,
        profile_photo: response.data.data.profile_photo
      };
      if (userInfo.value.profile_photo) {
        currentAvatar.value = userInfo.value.profile_photo;
      }
    }
  } catch (error) {
    console.error("Gagal mengambil data profil:", error);
  }
});

const triggerFileInput = () => { fileInput.value.click(); };
const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file && file.type.startsWith('image/')) {
    userInfo.value.new_photo = file;
    const reader = new FileReader();
    reader.onload = (e) => { currentAvatar.value = e.target.result; };
    reader.readAsDataURL(file);
  }
};

const saveChanges = async () => {
  if (passwordData.value.new && passwordData.value.new.length < 6) {
    alert('Password baru minimal 6 karakter.');
    return;
  }

  isLoading.value = true;
  try {
    const formData = new FormData();
    
    if (passwordData.value.new) {
      if (!passwordData.value.current) { 
        alert('Masukkan password saat ini untuk mengubah password.'); 
        isLoading.value = false;
        return; 
      }
      if (passwordData.value.new !== passwordData.value.confirm) { 
        alert('Konfirmasi password baru tidak cocok.'); 
        isLoading.value = false;
        return; 
      }
      formData.append('current_password', passwordData.value.current);
      formData.append('new_password', passwordData.value.new);
      formData.append('new_password_confirmation', passwordData.value.confirm);
    }

    if (userInfo.value.new_photo) {
      console.log("Appending file to FormData:", userInfo.value.new_photo);
      formData.append('profile_photo', userInfo.value.new_photo);
    }

    // We use POST now for better file upload compatibility

    const response = await api.post('/settings/me', formData);

    if (response.data && response.data.status === 'success') {
      // Update local storage to keep session in sync
      const storedUser = JSON.parse(localStorage.getItem('user') || '{}');
      
      // Update fields
      if (response.data.data.fullName) storedUser.name = response.data.data.fullName;
      if (response.data.data.profile_photo) storedUser.profile_photo = response.data.data.profile_photo;
      
      localStorage.setItem('user', JSON.stringify(storedUser));
      
      alert('Perubahan berhasil disimpan!');
      emit('profile-updated', storedUser);
      emit('go-back');
    }
  } catch (error) {
    console.error("Gagal menyimpan perubahan:", error);
    let msg = 'Terjadi kesalahan saat menyimpan perubahan.';
    
    if (error.response?.data?.errors) {
      // Handle Laravel validation errors specifically
      const errors = error.response.data.errors;
      msg = Object.values(errors).flat().join('\n');
    } else if (error.response?.data?.message) {
      msg = error.response.data.message;
    }
    
    alert(msg);
  } finally {
    isLoading.value = false;
  }
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
            <img v-if="currentAvatar" :src="getImageUrl(currentAvatar)" class="avatar-img-preview" />
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
              <div class="input-icon-svg">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="input-item">
            <label>New Password</label>
            <div class="input-wrapper">
              <input type="password" v-model="passwordData.new" placeholder="Min. 6 characters" class="form-input" />
              <div class="input-icon-svg">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="input-item">
            <label>Confirm New Password</label>
            <div class="input-wrapper">
              <input type="password" v-model="passwordData.confirm" placeholder="Repeat new password" class="form-input" />
              <div class="input-icon-svg">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </div>
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
.form-input:focus { border-color: #3b82f6; outline: none; background-color: #ffffff; }
.input-icon-svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; color: #94a3b8; }

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