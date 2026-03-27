<script setup>
import { ref, onMounted } from "vue";

const emit = defineEmits(["done"]);

const phase = ref(0); // 0=hidden, 1=in, 2=out

onMounted(() => {
  requestAnimationFrame(() => { phase.value = 1; });

  // Total 2.2 detik
  setTimeout(() => { phase.value = 2; }, 1800);
  setTimeout(() => emit("done"), 2400);
});
</script>

<template>
  <div class="sp" :class="['ph-' + phase]">

    <!-- Animated mesh background -->
    <div class="mesh"></div>

    <!-- Floating particles -->
    <span v-for="i in 12" :key="i" class="particle" :class="'p' + i"></span>

    <!-- Diagonal accent bar -->
    <div class="accent-bar"></div>

    <!-- Main card -->
    <div class="card">
      <!-- Shimmer sweep -->
      <div class="shimmer"></div>

      <!-- Logo BPS -->
      <img src="../assets/bps.png" alt="BPS" class="logo" />

      <!-- Decorative dot grid -->
      <div class="dot-grid">
        <span v-for="d in 9" :key="d" class="dot" :class="'d' + d"></span>
      </div>
    </div>

    <!-- Morphing bottom shape -->
    <div class="morph-bottom"></div>

    <!-- Bottom bar loader -->
    <div class="bar-wrap">
      <div class="bar-fill"></div>
    </div>

  </div>
</template>

<style scoped>
/* ── BASE ── */
.sp {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: #020e24;
  z-index: 100;
}

/* phases */
.sp.ph-0 { opacity: 0; }
.sp.ph-1 { opacity: 1; transition: opacity 0.3s ease; }
.sp.ph-2 {
  opacity: 0;
  transform: scale(1.06);
  transition: opacity 0.5s ease, transform 0.5s ease;
}

/* ── MESH BG ── */
.mesh {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 80% 60% at 20% 10%, rgba(0, 90, 220, 0.45) 0%, transparent 70%),
    radial-gradient(ellipse 60% 50% at 80% 85%, rgba(0, 180, 255, 0.25) 0%, transparent 65%),
    radial-gradient(ellipse 40% 40% at 50% 50%, rgba(0, 40, 120, 0.6) 0%, transparent 80%);
  animation: mesh-shift 3s ease-in-out infinite alternate;
}
@keyframes mesh-shift {
  from { filter: hue-rotate(0deg); }
  to   { filter: hue-rotate(15deg); }
}

/* ── DIAGONAL ACCENT ── */
.accent-bar {
  position: absolute;
  width: 200%;
  height: 3px;
  background: linear-gradient(90deg, transparent, #00d4ff, #ffd700, transparent);
  top: 35%;
  left: -50%;
  transform: rotate(-8deg);
  opacity: 0.35;
  animation: bar-slide 2.2s ease forwards;
}
@keyframes bar-slide {
  from { transform: rotate(-8deg) translateX(-60%); opacity: 0; }
  30%  { opacity: 0.35; }
  to   { transform: rotate(-8deg) translateX(60%); opacity: 0; }
}

/* ── PARTICLES ── */
.particle {
  position: absolute;
  border-radius: 50%;
  background: rgba(0, 200, 255, 0.7);
  animation: float-up 2s ease-out forwards;
}

/* sizes + positions */
.p1  { width:4px;  height:4px;  left:15%; bottom:20%; animation-delay:0.1s; }
.p2  { width:3px;  height:3px;  left:25%; bottom:40%; animation-delay:0.3s; background:rgba(255,215,0,0.8); }
.p3  { width:5px;  height:5px;  left:70%; bottom:25%; animation-delay:0.2s; }
.p4  { width:3px;  height:3px;  left:80%; bottom:55%; animation-delay:0.5s; background:rgba(255,215,0,0.6); }
.p5  { width:4px;  height:4px;  left:50%; bottom:15%; animation-delay:0.4s; }
.p6  { width:2px;  height:2px;  left:35%; bottom:70%; animation-delay:0.6s; background:rgba(255,255,255,0.8); }
.p7  { width:5px;  height:5px;  left:60%; bottom:80%; animation-delay:0.2s; background:rgba(255,215,0,0.7); }
.p8  { width:3px;  height:3px;  left:10%; bottom:65%; animation-delay:0.7s; }
.p9  { width:4px;  height:4px;  left:88%; bottom:30%; animation-delay:0.35s; background:rgba(255,255,255,0.6); }
.p10 { width:3px;  height:3px;  left:45%; bottom:85%; animation-delay:0.55s; }
.p11 { width:2px;  height:2px;  left:20%; bottom:88%; animation-delay:0.15s; background:rgba(255,215,0,0.9); }
.p12 { width:4px;  height:4px;  left:75%; bottom:70%; animation-delay:0.45s; background:rgba(255,255,255,0.7); }

@keyframes float-up {
  0%   { transform: translateY(0) scale(0); opacity: 0; }
  20%  { opacity: 1; }
  80%  { opacity: 0.6; }
  100% { transform: translateY(-80px) scale(1.5); opacity: 0; }
}

/* ── CARD ── */
.card {
  position: relative;
  width: 130px;
  height: 130px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(20px);
  box-shadow:
    0 0 0 1px rgba(0, 200, 255, 0.2),
    0 20px 60px rgba(0, 0, 0, 0.5),
    inset 0 1px 0 rgba(255,255,255,0.12);
  overflow: hidden;
  animation: card-in 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both;
  animation-delay: 0.15s;
}

@keyframes card-in {
  from { transform: scale(0.4) rotate(-10deg); opacity: 0; }
  to   { transform: scale(1) rotate(0deg); opacity: 1; }
}

/* shimmer sweep */
.shimmer {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    115deg,
    transparent 30%,
    rgba(255, 255, 255, 0.18) 50%,
    transparent 70%
  );
  transform: translateX(-100%);
  animation: shimmer 1.8s ease 0.5s forwards;
}
@keyframes shimmer {
  to { transform: translateX(200%); }
}

/* ── LOGO ── */
.logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
  filter: drop-shadow(0 4px 16px rgba(0, 180, 255, 0.5));
  animation: logo-glow 2s ease-in-out infinite alternate;
  position: relative;
  z-index: 1;
}
@keyframes logo-glow {
  from { filter: drop-shadow(0 4px 16px rgba(0, 180, 255, 0.4)); }
  to   { filter: drop-shadow(0 4px 28px rgba(0, 220, 255, 0.9)); }
}

/* ── DOT GRID ── */
.dot-grid {
  position: absolute;
  bottom: 8px;
  right: 8px;
  display: grid;
  grid-template-columns: repeat(3, 5px);
  gap: 3px;
}
.dot {
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  animation: dot-blink 1.5s ease-in-out infinite;
}
.d1 { animation-delay: 0s; }
.d2 { animation-delay: 0.1s; }
.d3 { animation-delay: 0.2s; }
.d4 { animation-delay: 0.15s; }
.d5 { animation-delay: 0.3s; background: rgba(0,200,255,0.6); }
.d6 { animation-delay: 0.1s; }
.d7 { animation-delay: 0.25s; }
.d8 { animation-delay: 0.2s; }
.d9 { animation-delay: 0.05s; background: rgba(255,215,0,0.6); }

@keyframes dot-blink {
  0%, 100% { opacity: 0.3; }
  50%       { opacity: 1; }
}

/* ── MORPH BOTTOM ── */
.morph-bottom {
  position: absolute;
  bottom: -60px;
  left: -20px;
  right: -20px;
  height: 180px;
  background: radial-gradient(ellipse at 50% 100%, rgba(0, 120, 255, 0.3) 0%, transparent 70%);
  border-radius: 50% 50% 0 0;
  animation: morph 3s ease-in-out infinite alternate;
}
@keyframes morph {
  from { border-radius: 60% 40% 0 0; transform: scaleX(1); }
  to   { border-radius: 40% 60% 0 0; transform: scaleX(1.1); }
}

/* ── LOADER BAR ── */
.bar-wrap {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: rgba(255, 255, 255, 0.08);
}
.bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #0057ff, #00d4ff, #ffd700);
  box-shadow: 0 0 8px #00d4ff;
  animation: bar 1.9s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
@keyframes bar {
  from { width: 0; }
  to   { width: 100%; }
}
</style>
