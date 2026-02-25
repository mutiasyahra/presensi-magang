<template>
  <div class="login-container">
    <div class="card">
      <div class="header">
        <img src="/src/assets/bps.png" alt="Logo BPS" class="logo-img" />
        <h1 class="app-name">InternTrack</h1>
        <p class="subtitle">Student Portal</p>
      </div>

      <div class="welcome-text">
        <h2>Welcome back</h2>
        <p>Log in to track your attendance</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="input-group">
          <label>EMAIL OR ID</label>
          <input type="text" placeholder="student@university.edu" />
        </div>

        <div class="input-group">
          <label>PASSWORD</label>
          <input type="password" placeholder="••••••••" />
        </div>

        <div class="forgot-pass">
          <a href="#">Forgot Password?</a>
        </div>

        <button @click="$emit('login-success')" class="btn-login">
          Log In ➔
        </button>
      </form>

      <div class="footer">
        <p>Don't have an account? <a href="#">Contact Faculty</a></p>
      </div>
    </div>
  </div>
</template>

<script setup>
const handleLogin = () => {
  alert("Tombol Login Ditekan! (Nanti kita sambung ke Backend)");
}
</script>

<style scoped>
/* Container Utama (Bikin rata tengah) */
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
}

/* Kartu Putih (Mirip Desain Figma) */
.card {
  background: white;
  width: 100%;
  max-width: 400px;
  padding: 40px;
  border-radius: 24px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  text-align: center;
}

/* Bagian Logo */
.logo-icon {
  font-size: 40px;
  background-color: #E0E7FF;
  width: 70px;
  height: 70px;
  line-height: 70px;
  border-radius: 20px;
  margin: 0 auto 15px;
  color: #4F46E5;
}

.app-name {
  margin: 0;
  font-size: 24px;
  color: #111827;
  font-weight: 700;
}

.subtitle {
  margin: 5px 0 30px;
  color: #6B7280;
  font-size: 14px;
}

/* Bagian Welcome */
.welcome-text {
  text-align: left;
  margin-bottom: 30px;
}

.welcome-text h2 {
  margin: 0;
  font-size: 22px;
  color: #1F2937;
}

.welcome-text p {
  margin: 5px 0 0;
  color: #9CA3AF;
  font-size: 14px;
}

/* Form Input */
.input-group {
  text-align: left;
  margin-bottom: 20px;
}

.input-group label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #9CA3AF;
  margin-bottom: 8px;
  letter-spacing: 1px;
}

.input-group input {
  width: 100%;
  padding: 14px;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  font-size: 14px;
  outline: none;
  box-sizing: border-box; /* Agar padding gak bikin lebar */
  transition: border-color 0.2s;
}

.input-group input:focus {
  border-color: #4F46E5;
}

.forgot-pass {
  text-align: right;
  margin-bottom: 25px;
}

.forgot-pass a {
  color: #4F46E5;
  font-size: 13px;
  text-decoration: none;
  font-weight: 500;
}

/* Tombol Login Biru */
.btn-login {
  width: 100%;
  background-color: #2563EB;
  color: white;
  padding: 16px;
  border: none;
  border-radius: 14px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-login:hover {
  background-color: #1D4ED8;
}

/* Footer */
.footer {
  margin-top: 30px;
  font-size: 14px;
  color: #6B7280;
}

.footer a {
  color: #2563EB;
  text-decoration: none;
  font-weight: 600;
}
</style>