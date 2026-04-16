<script setup>
import { defineProps, defineEmits, ref, onMounted, computed } from "vue";
import axios from "axios";

const props = defineProps({
  attendance: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "refresh"]);

const isProcessing = ref(false);
const currentAttendance = ref(props.attendance);
const selectedDate = ref(props.attendance?.attendance_date || new Date().toISOString().split("T")[0]);

// Calendar State
const showCalendar = ref(false);
const activeDates = ref([]);
const leaveDates = ref([]);
const calendarDate = ref(new Date(selectedDate.value));

const fetchActiveDates = async () => {
  try {
    const token = localStorage.getItem("token") || sessionStorage.getItem("token");
    const response = await axios.get("http://localhost:8000/api/attendances", {
      headers: { Authorization: `Bearer ${token}` },
      params: { 
        user_id: props.attendance.user_id,
      },
    });
    // Extract unique dates and normalize to YYYY-MM-DD robustly
    if (response.data.data) {
      activeDates.value = response.data.data
        .map(i => {
          if (!i.attendance_date) return null;
          // Extract only YYYY-MM-DD from various potential formats
          const match = i.attendance_date.match(/^(\d{4}-\d{2}-\d{2})/);
          return match ? match[1] : i.attendance_date.split('T')[0];
        })
        .filter(Boolean);
      activeDates.value = [...new Set(activeDates.value)];
    }

    // Fetch leaves
    try {
      const leaveRes = await axios.get("http://localhost:8000/api/leaves", {
        headers: { Authorization: `Bearer ${token}` },
        params: { user_id: props.attendance.user_id },
      });
      if (leaveRes.data.data) {
        const lDates = [];
        leaveRes.data.data.forEach(l => {
          if (l.status === 'approved' && l.start_date && l.end_date) {
            let curr = new Date(l.start_date);
            const end = new Date(l.end_date);
            while (curr <= end) {
              const y = curr.getFullYear();
              const m = String(curr.getMonth() + 1).padStart(2, '0');
              const d = String(curr.getDate()).padStart(2, '0');
              lDates.push(`${y}-${m}-${d}`);
              curr.setDate(curr.getDate() + 1);
            }
          }
        });
        leaveDates.value = [...new Set(lDates)];
      }
    } catch(err) {
      console.error("Failed to fetch leaves", err);
    }

  } catch (error) {
    console.error("Failed to fetch active dates", error);
  }
};

onMounted(() => {
  fetchActiveDates();
});

const fetchAttendanceByDate = async (date) => {
  try {
    const token = localStorage.getItem("token") || sessionStorage.getItem("token");
    const response = await axios.get("http://localhost:8000/api/attendances", {
      headers: { Authorization: `Bearer ${token}` },
      params: { 
        user_id: props.attendance.user_id,
        attendance_date: date 
      },
    });

    if (response.data.data && response.data.data.length > 0) {
      currentAttendance.value = {
        ...response.data.data[0],
        name: props.attendance.name,
        intern_id: props.attendance.intern_id,
        avatar: props.attendance.avatar
      };
    } else {
      currentAttendance.value = null;
    }
  } catch (error) {
    console.error("Failed to fetch attendance for date", error);
  }
};

// Calendar Logic
const calendarMonthYear = computed(() => {
  return calendarDate.value.toLocaleString('default', { month: 'long', year: 'numeric' });
});

const calendarDays = computed(() => {
  const year = calendarDate.value.getFullYear();
  const month = calendarDate.value.getMonth();
  const todayStr = new Date().toISOString().split('T')[0];
  
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  
  const days = [];
  
  // Padding for start of month
  const startPadding = firstDay.getDay();
  for (let i = 0; i < startPadding; i++) {
    days.push({ day: null });
  }
  
  // Actual days
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
    const isActive = activeDates.value.includes(dateStr);
    const isLeave = leaveDates.value.includes(dateStr);
    
    days.push({
      day: i,
      date: dateStr,
      isActive: isActive && !isLeave, // Prevent overlapping colors if both exist
      isLeave: isLeave,
      isSelected: selectedDate.value === dateStr,
      isToday: todayStr === dateStr
    });
  }
  
  return days;
});

const changeMonth = (offset) => {
  const newDate = new Date(calendarDate.value);
  newDate.setMonth(newDate.getMonth() + offset);
  calendarDate.value = newDate;
};

const selectDate = (date) => {
  if (!date) return;
  selectedDate.value = date;
  showCalendar.value = false;
  fetchAttendanceByDate(date);
};

const toggleCalendar = () => {
  showCalendar.value = !showCalendar.value;
  if (showCalendar.value) {
    calendarDate.value = new Date(selectedDate.value);
  }
};

const goBack = () => {
  emit("close");
};

const handleVerify = async (status, isVerified) => {
  if (!currentAttendance.value || !currentAttendance.value.id) return;
  isProcessing.value = true;

  try {
    const token =
      localStorage.getItem("token") || sessionStorage.getItem("token");
    await axios.patch(
      `http://localhost:8000/api/attendances/${currentAttendance.value.id}/verify`,
      {
        is_verified: isVerified,
        status: status,
      },
      {
        headers: { Authorization: `Bearer ${token}` },
      },
    );

    // Refresh parent list and close
    emit("refresh");
  } catch (error) {
    console.error("Failed to verify attendance", error);
    alert("Failed to verify attendance");
  } finally {
    isProcessing.value = false;
  }
};
</script>

<template>
  <div class="verification-wrapper">
    <header class="detail-header">
      <div class="header-left">
        <button class="btn-back" @click="goBack">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="m15 18-6-6 6-6" />
          </svg>
          Back
        </button>
        <div class="divider"></div>
        <div class="profile-info" v-if="attendance">
          <img
            :src="attendance.avatar || 'https://i.pravatar.cc/150'"
            alt="Avatar"
            class="avatar"
          />
          <div class="user-details">
            <h3>{{ attendance.name }}</h3>
            <p>{{ attendance.intern_id }}</p>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <div class="calendar-picker-container">
          <div class="date-selector" @click="toggleCalendar">
            <svg
              class="calendar-main-icon"
              xmlns="http://www.w3.org/2000/svg"
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span class="current-date-text">{{ 
              new Date(selectedDate).toLocaleDateString("en-GB", {
                day: '2-digit', month: '2-digit', year: 'numeric'
              })
            }}</span>
          </div>

          <!-- Custom Calendar Dropdown -->
          <div class="custom-calendar-dropdown" v-if="showCalendar">
            <div class="calendar-header">
              <button @click.stop="changeMonth(-1)" class="nav-btn">&lt;</button>
              <span class="month-label">{{ calendarMonthYear }}</span>
              <button @click.stop="changeMonth(1)" class="nav-btn">&gt;</button>
            </div>
            <div class="calendar-grid">
              <span v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']" :key="day" class="day-name">
                {{ day }}
              </span>
              <div 
                v-for="(dateObj, index) in calendarDays" 
                :key="index"
                class="calendar-day"
                :class="{ 
                  'empty': !dateObj.day, 
                  'selected': dateObj.isSelected,
                  'has-activity': dateObj.isActive,
                  'is-leave': dateObj.isLeave
                }"
                @click.stop="selectDate(dateObj.date)"
              >
                {{ dateObj.day }}
                <span class="activity-dot" :class="{'leave-dot': dateObj.isLeave}" v-if="dateObj.isActive || dateObj.isLeave"></span>
              </div>
            </div>
          </div>
        </div>
        <button class="btn-icon">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="18" cy="5" r="3"></circle>
            <circle cx="6" cy="12" r="3"></circle>
            <circle cx="18" cy="19" r="3"></circle>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
          </svg>
        </button>
        <button class="btn-icon">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
          </svg>
        </button>
      </div>
    </header>

    <div class="content-grid" v-if="currentAttendance">
      <div class="timeline-section">
        <div class="section-title">
          <h2>Activity Timeline</h2>
          <span class="date">{{
            new Date(currentAttendance?.attendance_date).toLocaleDateString("en-US", {
              weekday: "long",
              year: "numeric",
              month: "short",
              day: "numeric",
            })
          }}</span>
        </div>

        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-icon icon-blue">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                <polyline points="10 17 15 12 10 7" />
                <line x1="15" y1="12" x2="3" y2="12" />
              </svg>
            </div>
            <div class="timeline-content">
              <div class="event-header">
                <div>
                  <h4>Clock In</h4>
                  <p class="location">📍 Singapore, Science Park Drive</p>
                </div>
                <span class="time">{{
                  currentAttendance?.clock_in
                    ? new Date(currentAttendance.clock_in).toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                      })
                    : "-"
                }}</span>
              </div>

              <div class="evidence-card" v-if="currentAttendance?.clock_in_photo">
                <div
                  class="selfie-placeholder"
                  :style="{
                    backgroundImage: `url(http://localhost:8000/storage/${currentAttendance.clock_in_photo})`,
                  }"
                ></div>
                <div class="evidence-footer">
                  <span class="label">📷 Morning Selfie</span>
                  <span class="status text-green">✅ GPS Verified</span>
                </div>
              </div>

              <div class="info-card">
                <p class="card-title">📝 DAILY WORK PLAN</p>
                <p class="summary-text">
                  {{
                    currentAttendance?.rencana_kegiatan ||
                    "Tidak ada rencana kegiatan."
                  }}
                </p>
              </div>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-icon icon-red">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
              </svg>
            </div>
            <div class="timeline-content">
              <div class="event-header">
                <div>
                  <h4>Clock Out</h4>
                  <p class="location">📍 Singapore, Science Park Drive</p>
                </div>
                <span class="time">{{
                  currentAttendance?.clock_out
                    ? new Date(currentAttendance.clock_out).toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                      })
                    : "-"
                }}</span>
              </div>

              <div class="evidence-card" v-if="currentAttendance?.clock_out_photo">
                <div
                  class="selfie-placeholder"
                  :style="{
                    backgroundImage: `url(http://localhost:8000/storage/${currentAttendance.clock_out_photo})`,
                  }"
                ></div>
                <div class="evidence-footer">
                  <span class="label">📷 Afternoon Selfie</span>
                  <span class="status text-green">✅ Validated</span>
                </div>
              </div>

              <div class="info-card">
                <p class="card-title">📉 DAILY ACTIVITY SUMMARY</p>
                <p class="summary-text">
                  {{
                    attendance?.progress_kegiatan ||
                    "Tidak ada progress kegiatan."
                  }}
                </p>
              </div>

              <div class="attachments" v-if="currentAttendance?.evidence">
                <p class="card-title">📎 EVIDENCE / ATTACHMENTS</p>
                <div class="attachment-images">
                  <a
                    :href="`http://localhost:8000/storage/${currentAttendance.evidence}`"
                    target="_blank"
                    class="img-box"
                    :style="{
                      backgroundImage: currentAttendance.evidence.match(
                        /\.(jpeg|jpg|gif|png)$/i,
                      )
                        ? `url(http://localhost:8000/storage/${currentAttendance.evidence})`
                        : 'none',
                      backgroundSize: 'cover',
                      backgroundPosition: 'center',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      textDecoration: 'none',
                      color: '#94A3B8',
                    }"
                  >
                    <span
                      v-if="
                        !currentAttendance.evidence.match(/\.(jpeg|jpg|gif|png)$/i)
                      "
                      >📄 View File</span
                    >
                  </a>
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
            <template
              v-if="currentAttendance?.clock_in_lat && currentAttendance?.clock_in_long"
            >
              <iframe
                width="100%"
                height="100%"
                frameborder="0"
                style="border: 0; border-radius: 12px; min-height: 140px"
                :src="`https://maps.google.com/maps?q=${currentAttendance.clock_in_lat},${currentAttendance.clock_in_long}&hl=es;z=14&amp;output=embed`"
                allowfullscreen
              >
              </iframe>
            </template>
            <template v-else>
              <div class="pin">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="white"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path
                    d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"
                  ></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
              </div>
              <p>Map view of Singapore</p>
            </template>
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
              <p class="value text-green">
                <strong>{{ currentAttendance?.status?.toUpperCase() || "HADIR" }}</strong>
              </p>
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
    <div class="empty-state" v-else>
      <div class="empty-content">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="empty-icon">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <h3>No Activity Found</h3>
        <p>There is no attendance record for this intern on {{ new Date(selectedDate).toLocaleDateString("en-US", { year: 'numeric', month: 'long', day: 'numeric' }) }}.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.verification-wrapper,
.verification-wrapper * {
  box-sizing: border-box;
}

.verification-wrapper {
  background: var(--bg-app);
  min-height: 100vh;
  position: relative;
  display: flex;
  flex-direction: column;
}

/* --- HEADER --- */
.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 32px;
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  background: var(--bg-app);
  z-index: 10;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-back {
  display: flex;
  align-items: center;
  gap: 4px;
  border: none;
  background: transparent;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 8px;
  transition: 0.2s;
}
.btn-back:hover {
  background: var(--bg-input);
}

.divider {
  width: 1px;
  height: 32px;
  background: var(--border-color);
}

.profile-info {
  display: flex;
  align-items: center;
  gap: 12px;
}
.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}
.user-details h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: var(--text-main);
}
.user-details p {
  margin: 0;
  font-size: 12px;
  color: var(--text-muted);
}

.badge-warning {
  background: #fefce8;
  color: #d97706;
  border: 1px solid #fef08a;
  font-size: 10px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  letter-spacing: 0.5px;
}

.header-actions {
  display: flex;
  gap: 8px;
}
.btn-icon {
  background: transparent;
  border: none;
  color: var(--text-muted);
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s;
}
.btn-icon:hover {
  background: var(--bg-input);
  color: var(--text-main);
}

/* --- CUSTOM CALENDAR --- */
.calendar-picker-container {
  position: relative;
}

.date-selector {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--bg-input);
  padding: 8px 16px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.date-selector:hover {
  background: var(--bg-input);
  border-color: var(--border-color);
}

.calendar-main-icon {
  color: var(--text-muted);
}

.current-date-text {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-main);
}

.custom-calendar-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 280px;
  background: var(--bg-card);
  border-radius: 16px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
  border: 1px solid var(--border-color);
  padding: 16px;
  z-index: 1000;
  user-select: none;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.month-label {
  font-weight: 700;
  color: var(--text-main);
  font-size: 14px;
}

.nav-btn {
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-muted);
  font-weight: 700;
}

.nav-btn:hover {
  background: var(--bg-input);
  color: var(--text-main);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}

.day-name {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-dim);
  text-align: center;
  padding-bottom: 8px;
}

.calendar-day {
  aspect-ratio: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
  border-radius: 12px;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
  padding-bottom: 2px;
}

.calendar-day:hover:not(.empty) {
  background: var(--bg-input);
  color: var(--text-main);
}

.calendar-day.is-today:not(.selected) {
  border: 1px solid var(--accent-primary);
  color: var(--accent-primary);
}

.calendar-day.empty {
  cursor: default;
}

.calendar-day.selected {
  background: var(--accent-primary);
  color: white !important;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.activity-dot {
  position: absolute;
  bottom: 6px;
  left: 50%;
  transform: translateX(-50%);
  width: 3px;
  height: 3px;
  background: var(--accent-success);
  border-radius: 50%;
}

.activity-dot.leave-dot {
  background: #a855f7;
}

.selected .activity-dot {
  background: white;
}

.calendar-day.has-activity:not(.selected) {
  color: var(--accent-success);
  font-weight: 800;
  background: rgba(16, 185, 129, 0.05);
}

.calendar-day.is-leave:not(.selected) {
  color: #a855f7;
  font-weight: 800;
  background: rgba(168, 85, 247, 0.05);
}

/* --- EMPTY STATE --- */
.empty-state {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 64px 32px;
  background: var(--bg-app);
}

.empty-content {
  text-align: center;
  max-width: 400px;
}

.empty-icon {
  color: var(--text-dim);
  margin-bottom: 24px;
}

.empty-content h3 {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0 0 12px 0;
}

.empty-content p {
  font-size: 15px;
  color: var(--text-muted);
  line-height: 1.6;
  margin: 0;
}

/* --- CONTENT GRID --- */
.content-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 32px;
  padding: 32px;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
  padding-bottom: 100px;
}

/* --- KIRI: TIMELINE --- */
.section-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}
.section-title h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: var(--text-main);
}
.section-title .date {
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 500;
}

.timeline {
  position: relative;
  padding-left: 16px;
}
.timeline::before {
  content: "";
  position: absolute;
  left: 23px;
  top: 8px;
  bottom: 0;
  width: 2px;
  background: var(--border-color);
}

.timeline-item {
  display: flex;
  gap: 24px;
  margin-bottom: 24px;
  position: relative;
  z-index: 2;
}
.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-icon {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--border-color);
  flex-shrink: 0;
  margin-top: 4px;
}
.icon-blue {
  color: var(--accent-primary);
  box-shadow: 0 0 0 4px var(--bg-app);
}
.icon-red {
  color: var(--accent-danger);
  box-shadow: 0 0 0 4px var(--bg-app);
}

.timeline-content {
  flex: 1;
}

.event-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}
.event-header h4 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 700;
  color: var(--text-main);
}
.event-header .location {
  margin: 0;
  font-size: 13px;
  color: var(--text-muted);
}
.event-header .time {
  background: var(--bg-input);
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
}

.evidence-card {
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 8px;
}
.selfie-placeholder {
  height: 200px;
  background-size: cover;
  background-position: center;
}
.bg-blue {
  background-color: var(--bg-card);
}
.bg-red {
  background-color: var(--bg-card);
}

.evidence-footer {
  display: flex;
  justify-content: space-between;
  padding: 12px 16px;
  background: var(--bg-card);
  font-size: 13px;
  font-weight: 600;
}
.evidence-footer .label {
  color: var(--text-muted);
}
.text-green {
  color: var(--accent-success);
}

.info-card {
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}
.card-title {
  margin: 0 0 12px 0;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-dim);
  letter-spacing: 0.5px;
}
.info-card ol {
  margin: 0;
  padding-left: 20px;
  color: var(--text-muted);
  font-size: 14px;
  line-height: 1.6;
}
.summary-text {
  margin: 0;
  color: var(--text-muted);
  font-size: 14px;
  line-height: 1.6;
}

.attachments {
  margin-top: 8px;
}
.attachment-images {
  display: flex;
  gap: 12px;
}
.img-box {
  width: 160px;
  height: 100px;
  background: var(--bg-card);
  border-radius: 8px;
}

/* --- KANAN: SIDEBAR --- */
.sidebar-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.widget-card {
  border: 1px solid var(--border-color);
  border-radius: 16px;
  padding: 20px;
  background: var(--bg-card);
}
.widget-card h4 {
  margin: 0 0 16px 0;
  font-size: 14px;
  font-weight: 700;
  color: var(--text-main);
}

.widget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.widget-header h4 {
  margin: 0;
}
.badge-blue {
  background: #eff6ff;
  color: var(--accent-primary);
  font-size: 10px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 4px;
}

.map-placeholder {
  height: 140px;
  background: var(--bg-input);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  gap: 8px;
}
.map-placeholder .pin {
  width: 40px;
  height: 40px;
  background: var(--accent-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}
.map-placeholder p {
  margin: 0;
  font-size: 12px;
  color: var(--accent-primary);
  font-weight: 600;
}

.accuracy-info {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}
.accuracy-info .title {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
}
.accuracy-info .desc {
  margin: 2px 0 0 0;
  font-size: 12px;
  color: var(--text-muted);
}

.stats-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.stat-box {
  background: var(--bg-input);
  padding: 16px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}
.stat-box .label {
  margin: 0 0 8px 0;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
}
.stat-box .value {
  margin: 0;
  font-size: 14px;
  color: var(--text-main);
}

textarea {
  width: 100%;
  height: 100px;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 12px;
  font-family: inherit;
  font-size: 14px;
  color: var(--text-main);
  resize: none;
  outline: none;
  margin-bottom: 16px;
  background: transparent;
}
textarea:focus {
  border-color: var(--accent-primary);
}

.checkbox-container {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--text-main);
  cursor: pointer;
  background: transparent;
}

/* --- FOOTER ACTIONS --- */
.action-footer {
  position: fixed;
  bottom: 0;
  right: 0;
  width: 100%;
  background: var(--bg-card);
  border-top: 1px solid var(--border-color);
  padding: 16px 32px;
  display: flex;
  justify-content: flex-end;
  gap: 16px;
  z-index: 20;
}

.btn-reject {
  background: transparent;
  border: 1px solid var(--accent-danger);
  color: var(--accent-danger);
  padding: 10px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}
.btn-reject:hover {
  background: var(--surface-danger);
}

.btn-verify {
  background: var(--accent-primary);
  border: none;
  color: white;
  padding: 10px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-verify:hover {
  opacity: 0.9;
}
</style>
