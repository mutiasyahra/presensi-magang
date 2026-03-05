<script setup>
import { ref, computed, onMounted } from "vue"; 
import api from "../../api/axios.js";

// Variabel untuk mengontrol Modal (true = muncul, false = sembunyi)
const showAddModal = ref(false);

// interns will be loaded from backend
const interns = ref([]);
const loading = ref(false);
const error = ref(null);

// Data untuk options yang bisa dicari dan ditambah
const universities = ref([]);
const departments = ref([]);
const mentors = ref([]);
const projects = ref([]);

// Form inputs dengan dropdown
const formData = ref({
  name: "",
  email: "",
  phone: "",
  id: "",
  universityInput: "",
  departmentInput: "",
  mentorInput: "",
  projectInput: "",
  startDate: "",
  endDate: "",
});

const activeDropdown = ref(null);

// Helper: extract unique values from interns to populate options (used as fallback)
const populateOptionsFromInterns = () => {
  const uniSet = new Set();
  const deptSet = new Set();
  const mentorSet = new Set();
  const projSet = new Set();
  interns.value.forEach((i) => {
    if (i.university) uniSet.add(i.university);
    if (i.department) deptSet.add(i.department);
    if (i.mentor) mentorSet.add(i.mentor);
    if (i.project) projSet.add(i.project);
  });
  // only populate if endpoints did not provide options
  if (!universities.value || universities.value.length === 0) {
    universities.value = Array.from(uniSet);
  }
  if (!departments.value || departments.value.length === 0) {
    departments.value = Array.from(deptSet);
  }
  if (!mentors.value || mentors.value.length === 0) {
    mentors.value = Array.from(mentorSet);
  }
  if (!projects.value || projects.value.length === 0) {
    projects.value = Array.from(projSet);
  }
};

// API base - adapt if your backend uses a different prefix
// const API_BASE = "/api"; (replaced by axios)

// Try fetching option lists from dedicated endpoints; if they don't exist, we'll fallback to populate from interns
const fetchOptions = async () => {
  try {
    const uRes = await api.get(`/universities`);
    if (Array.isArray(uRes.data)) universities.value = uRes.data;
  } catch (e) {}
  try {
    const dRes = await api.get(`/departments`);
    if (Array.isArray(dRes.data)) departments.value = dRes.data;
  } catch (e) {}
  try {
    const mRes = await api.get(`/mentors`);
    if (Array.isArray(mRes.data)) mentors.value = mRes.data;
  } catch (e) {}
  try {
    const pRes = await api.get(`/projects`);
    if (Array.isArray(pRes.data)) projects.value = pRes.data;
  } catch (e) {}
};

// Fetch interns from backend
const fetchInterns = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await api.get(`/interns`);
    let fetchedData = res.data?.data || res.data || [];
    interns.value = fetchedData.map(i => ({
      ...i,
      name: i.full_name || 'Unknown',
      id: i.intern_id || i.id,
      attendance: i.attendance_percentage || 0,
      status: i.status || 'Active'
    }));
    populateOptionsFromInterns();
  } catch (err) {
    error.value = err.message || String(err);
  } finally {
    loading.value = false;
  }
};

// stats from backend for cards
const stats = ref({});

const fetchStats = async () => {
  try {
    const res = await api.get(`/stats`);
    stats.value = res.data;
  } catch (e) {
    // keep default empty
  }
};

onMounted(async () => {
  // parallelize requests that don't depend on each other
  await Promise.all([fetchOptions(), fetchInterns(), fetchStats()]);
});

// Filter options berdasarkan input

// helper to pick from list or add
const selectOrAddOption = (field, value) => {
  formData.value[field] = value;
  activeDropdown.value = null;
};

// allow pressing enter to add new option
const maybeAdd = (field, arrRef) => {
  const v = formData.value[field].trim();
  if (
    v &&
    !arrRef.some((item) => item.toLowerCase() === v.toLowerCase())
  ) {
    addNewOption(field, arrRef, v);
  }
};

const addNewOption = (field, fieldArray, value) => {
  const v = value.trim();
  // Check case-insensitively to avoid duplicates
  const exists = fieldArray.some(
    (item) => item.toLowerCase() === v.toLowerCase(),
  );
  if (v && !exists) {
    fieldArray.push(v);
    formData.value[field] = v;
    activeDropdown.value = null;
  }
};

// Save intern (POST) and update UI
const saving = ref(false);
const saveError = ref(null);
const saveIntern = async () => {
  saveError.value = null;
  saving.value = true;
  try {
    // build payload from form inputs (expand as needed)
    const payload = {
      full_name: formData.value.name || "",
      email: formData.value.email || "",
      phone_number: formData.value.phone || "",
      intern_id: formData.value.id || "",
      university: formData.value.universityInput || "",
      department: formData.value.departmentInput || "",
      mentor: formData.value.mentorInput || "",
      project: formData.value.projectInput || "",
      start_date: formData.value.startDate || null,
      end_date: formData.value.endDate || null,
    };

    const res = await api.post(`/interns`, payload);
    const created = res.data?.data || res.data || {};
    // add to interns list and repopulate options
    interns.value.unshift({
      ...created,
      name: created.full_name || 'Unknown',
      id: created.intern_id || created.id,
      attendance: created.attendance_percentage || 0,
      status: created.status || 'Active'
    });
    populateOptionsFromInterns();
    // reset form inputs
    formData.value.universityInput = "";
    formData.value.departmentInput = "";
    formData.value.mentorInput = "";
    formData.value.projectInput = "";
    showAddModal.value = false;
  } catch (err) {
    saveError.value = err.response?.data?.message || err.message || String(err);
  } finally {
    saving.value = false;
  }
};

// calculate derived values for stats cards
const activeAttendancePercentage = computed(() => {
  if (stats.value.total_interns) {
    return ((stats.value.present / stats.value.total_interns) * 100).toFixed(1);
  }
  return 0;
});

const departmentCount = computed(() => {
  const set = new Set(interns.value.map((i) => i.department).filter(Boolean));
  return set.size;
});

// Fungsi untuk menentukan warna progress bar berdasarkan persentase
const getProgressColor = (value) => {
  if (value >= 90) return "bg-green";
  if (value > 0) return "bg-orange";
  return "bg-grey";
};
</script>

<template>
  <div class="directory-container">
    <div class="page-header">
      <div class="header-titles">
        <h1 class="main-title">Intern Directory</h1>
        <p class="sub-title">
          Manage and track internship progress across all departments.
        </p>
      </div>
      <div class="header-actions">
        <button class="btn-export">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          Export
        </button>
        <button class="btn-add" @click="showAddModal = true">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Add Intern
        </button>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <p class="stat-label">TOTAL INTERNS</p>
        <div class="stat-value-group">
          <h2 class="stat-value">
            {{ stats.total_interns ?? interns.length }}
          </h2>
          <span class="stat-trend trend-up">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="14"
              height="14"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
              <polyline points="16 7 22 7 22 13"></polyline>
            </svg>
            <!-- optional trend info could come from stats if available -->
            {{ stats.inter_month_change ?? "" }}
          </span>
        </div>
      </div>

      <div class="stat-card">
        <p class="stat-label">ACTIVE ATTENDANCE</p>
        <div class="stat-value-group align-end">
          <h2 class="stat-value">{{ activeAttendancePercentage }}%</h2>
          <span class="stat-subtext"> vs 90%<br />target </span>
        </div>
      </div>

      <div class="stat-card">
        <p class="stat-label">PENDING LEAVES</p>
        <div class="stat-value-group align-end">
          <h2 class="stat-value highlight-orange">
            {{ stats.pending_leaves ?? 0 }}
          </h2>
          <span class="stat-subtext">Requires action</span>
        </div>
      </div>

      <div class="stat-card">
        <p class="stat-label">DEPARTMENTS</p>
        <div class="stat-value-group align-end">
          <h2 class="stat-value">{{ departmentCount }}</h2>
          <span class="stat-subtext">Active units</span>
        </div>
      </div>
    </div>

    <div class="filter-section">
      <div class="search-box">
        <svg
          class="search-icon"
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
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" placeholder="Search by name, ID, or mentor..." />
      </div>

      <div class="filter-actions">
        <div class="dropdown">
          <span>All Universities</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </div>
        <div class="dropdown">
          <span>All Projects</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
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
            <line x1="21" y1="4" x2="14" y2="4"></line>
            <line x1="10" y1="4" x2="3" y2="4"></line>
            <line x1="21" y1="12" x2="12" y2="12"></line>
            <line x1="8" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="20" x2="16" y2="20"></line>
            <line x1="12" y1="20" x2="3" y2="20"></line>
            <line x1="14" y1="2" x2="14" y2="6"></line>
            <line x1="8" y1="10" x2="8" y2="14"></line>
            <line x1="16" y1="18" x2="16" y2="22"></line>
          </svg>
        </button>
      </div>
    </div>

    <div class="table-container">
      <table class="directory-table">
        <thead>
          <tr>
            <th>INTERN NAME</th>
            <th>UNIVERSITY</th>
            <th>DEPARTMENT</th>
            <th>MENTOR</th>
            <th>ATTENDANCE %</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="intern in interns" :key="intern.id">
            <td>
              <div class="intern-profile">
                <div class="avatar">
                  <span v-if="!intern.avatar">{{ intern.name.charAt(0) }}</span>
                  <img
                    v-else
                    src="https://i.pravatar.cc/150?img=1"
                    alt="avatar"
                  />
                </div>
                <div class="intern-details">
                  <p class="intern-name">{{ intern.name }}</p>
                  <p class="intern-id">ID: {{ intern.id }}</p>
                </div>
              </div>
            </td>
            <td class="td-text">{{ intern.university }}</td>
            <td class="td-text">{{ intern.department }}</td>
            <td class="td-text">{{ intern.mentor }}</td>
            <td>
              <div class="attendance-cell">
                <div class="progress-track">
                  <div
                    class="progress-fill"
                    :class="getProgressColor(intern.attendance)"
                    :style="{ width: intern.attendance + '%' }"
                  ></div>
                </div>
                <span class="attendance-text">{{ intern.attendance }}%</span>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="intern.status.toLowerCase()">{{
                intern.status
              }}</span>
            </td>
            <td>
              <div class="action-buttons">
                <button class="action-btn" title="View">
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
                    <path
                      d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                    ></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
                <button class="action-btn" title="Edit">
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
                    <path
                      d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                    ></path>
                    <path
                      d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                    ></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination">
        <p class="pagination-info">
          Showing 1 to {{ interns.length }} of
          {{ stats.total_interns ?? interns.length }} interns
        </p>
        <div class="pagination-controls">
          <button class="page-btn">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <span class="page-dots">...</span>
          <button class="page-btn">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
  <div
    v-if="showAddModal"
    class="modal-overlay"
    @click.self="showAddModal = false"
  >
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h2 class="modal-title">Add New Intern</h2>
          <p class="modal-subtitle">
            Fill in the details to register a new intern to the system.
          </p>
        </div>
        <button class="btn-close" @click="showAddModal = false">
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
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Full Name</label>
            <input
              type="text"
              v-model="formData.name"
              placeholder="e.g. Johnathan Doe"
            />
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input
              type="email"
              v-model="formData.email"
              placeholder="john@company.com"
            />
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input
              type="text"
              v-model="formData.phone"
              placeholder="+1 (555) 000-0000"
            />
          </div>
          <div class="form-group">
            <label>Intern ID</label>
            <input
              type="text"
              v-model="formData.id"
              placeholder="PRISMA-2026-001"
            />
          </div>
          <div class="form-group">
            <label>University</label>
            <div class="searchable-select">
              <input
                type="text"
                v-model="formData.universityInput"
                placeholder="Type or select university..."
                @focus="activeDropdown = 'university'"
                @blur="setTimeout(() => (activeDropdown = null), 200)"
                @keydown.enter.prevent="
                  maybeAdd('universityInput', universities)
                "
              />
              <div v-if="activeDropdown === 'university'" class="dropdown-menu">
                <div
                  v-for="uni in universities.filter((u) =>
                    u
                      .toLowerCase()
                      .includes(formData.universityInput.toLowerCase()),
                  )"
                  :key="uni"
                  @click="selectOrAddOption('universityInput', uni)"
                  class="dropdown-item"
                >
                  {{ uni }}
                </div>
                <div
                  v-if="
                    formData.universityInput.trim() &&
                    !universities.some(
                      (u) =>
                        u.toLowerCase() ===
                        formData.universityInput.trim().toLowerCase(),
                    )
                  "
                  @click="
                    addNewOption(
                      'universityInput',
                      universities,
                      formData.universityInput,
                    )
                  "
                  class="dropdown-item add-new"
                >
                  Add "{{ formData.universityInput }}"
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Department</label>
            <div class="searchable-select">
              <input
                type="text"
                v-model="formData.departmentInput"
                placeholder="Type or select department..."
                @focus="activeDropdown = 'department'"
                @blur="setTimeout(() => (activeDropdown = null), 200)"
                @keydown.enter.prevent="
                  maybeAdd('departmentInput', departments)
                "
              />
              <div v-if="activeDropdown === 'department'" class="dropdown-menu">
                <div
                  v-for="dept in departments.filter((d) =>
                    d
                      .toLowerCase()
                      .includes(formData.departmentInput.toLowerCase()),
                  )"
                  :key="dept"
                  @click="selectOrAddOption('departmentInput', dept)"
                  class="dropdown-item"
                >
                  {{ dept }}
                </div>
                <div
                  v-if="
                    formData.departmentInput.trim() &&
                    !departments.some(
                      (d) =>
                        d.toLowerCase() ===
                        formData.departmentInput.trim().toLowerCase(),
                    )
                  "
                  @click="
                    addNewOption(
                      'departmentInput',
                      departments,
                      formData.departmentInput,
                    )
                  "
                  class="dropdown-item add-new"
                >
                  Add "{{ formData.departmentInput }}"
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Mentor</label>
            <div class="searchable-select">
              <input
                type="text"
                v-model="formData.mentorInput"
                placeholder="Type or select mentor..."
                @focus="activeDropdown = 'mentor'"
                @blur="setTimeout(() => (activeDropdown = null), 200)"
                @keydown.enter.prevent="maybeAdd('mentorInput', mentors)"
              />
              <div v-if="activeDropdown === 'mentor'" class="dropdown-menu">
                <div
                  v-for="mentor in mentors.filter((m) =>
                    m
                      .toLowerCase()
                      .includes(formData.mentorInput.toLowerCase()),
                  )"
                  :key="mentor"
                  @click="selectOrAddOption('mentorInput', mentor)"
                  class="dropdown-item"
                >
                  {{ mentor }}
                </div>
                <div
                  v-if="
                    formData.mentorInput.trim() &&
                    !mentors.some(
                      (m) =>
                        m.toLowerCase() ===
                        formData.mentorInput.trim().toLowerCase(),
                    )
                  "
                  @click="
                    addNewOption('mentorInput', mentors, formData.mentorInput)
                  "
                  class="dropdown-item add-new"
                >
                  Add "{{ formData.mentorInput }}"
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Project</label>
            <div class="searchable-select">
              <input
                type="text"
                v-model="formData.projectInput"
                placeholder="Type or select project..."
                @focus="activeDropdown = 'project'"
                @blur="setTimeout(() => (activeDropdown = null), 200)"
                @keydown.enter.prevent="maybeAdd('projectInput', projects)"
              />
              <div v-if="activeDropdown === 'project'" class="dropdown-menu">
                <div
                  v-for="proj in projects.filter((p) =>
                    p
                      .toLowerCase()
                      .includes(formData.projectInput.toLowerCase()),
                  )"
                  :key="proj"
                  @click="selectOrAddOption('projectInput', proj)"
                   class="dropdown-item"
                >
                  {{ proj }}
                </div>
                <div
                  v-if="
                    formData.projectInput.trim() &&
                    !projects.some(
                      (p) =>
                        p.toLowerCase() ===
                        formData.projectInput.trim().toLowerCase(),
                    )
                  "
                  @click="
                    addNewOption(
                      'projectInput',
                      projects,
                      formData.projectInput,
                    )
                  "
                  class="dropdown-item add-new"
                >
                  Add "{{ formData.projectInput }}"
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Start Date</label>
            <input type="date" v-model="formData.startDate" />
          </div>
          <div class="form-group">
            <label>End Date</label>
            <input type="date" v-model="formData.endDate" />
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div style="flex: 1">
          <div
            v-if="saveError"
            style="color: #b91c1c; font-size: 13px; margin-bottom: 6px"
          >
            {{ saveError }}
          </div>
        </div>
        <button class="btn-cancel" @click="showAddModal = false">Cancel</button>
        <button class="btn-save" @click="saveIntern" :disabled="saving">
          <span v-if="saving">Saving...</span>
          <span v-else>Save Intern</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Main Layout */
.directory-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;

  /* --- Efek Sticky & Glassmorphism --- */
  position: sticky;
  top: 0;
  z-index: 100;
  padding: 20px 0;
  margin-top: -20px; /* Menyeimbangkan padding atas agar sejajar */
  background: rgba(248, 250, 252, 0.8); /* Sama seperti Dashboard */
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.5);
}

.main-title {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px 0;
  letter-spacing: -0.5px;
}

.sub-title {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-export:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #3b82f6;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  color: white;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-add:hover {
  background: #2563eb;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stat-card {
  background: white;
  padding: 20px 24px;
  border-radius: 16px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-label {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.5px;
  margin: 0;
}

.stat-value-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.align-end {
  align-items: flex-end;
}

.stat-value {
  font-size: 32px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  line-height: 1;
}

.highlight-orange {
  color: #f59e0b;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  font-weight: 600;
}

.trend-up {
  color: #10b981;
}

.stat-subtext {
  font-size: 12px;
  color: #94a3b8;
  line-height: 1.3;
}

/* Filters & Search */
.filter-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 16px 24px;
  border-radius: 16px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
}

.search-box {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  padding: 10px 16px;
  border-radius: 8px;
  width: 320px;
  border: 1px solid transparent;
  transition: border 0.2s;
}

.search-box:focus-within {
  border-color: #3b82f6;
  background: white;
}

.search-icon {
  color: #94a3b8;
}

.search-box input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 14px;
  color: #0f172a;
  outline: none;
}

.search-box input::placeholder {
  color: #94a3b8;
}

.filter-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.dropdown {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
}

.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #f8fafc;
  color: #0f172a;
}

/* Table Section */
.table-container {
  background: white;
  border-radius: 16px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
  overflow: hidden; /* agar border radius terlihat rapi */
  overflow-x: auto; /* Horizontal scroll */
}

.directory-table {
  width: 100%;
  min-width: 1000px; /* Minimum width agar tidak terlalu sempit */
  border-collapse: collapse;
}

.directory-table th {
  text-align: left;
  padding: 20px 24px;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e2e8f0;
}

.directory-table td {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.directory-table tr:last-child td {
  border-bottom: none;
}
.directory-table tr:hover td {
  background: #f8faf9;
}

.intern-profile {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  font-weight: 700;
  color: #64748b;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.intern-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.intern-name {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
}
.intern-id {
  margin: 0;
  font-size: 12px;
  color: #94a3b8;
}

.td-text {
  font-size: 14px;
  color: #475569;
}

/* Attendance Progress */
.attendance-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.progress-track {
  width: 120px;
  height: 6px;
  background: #f1f5f9;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
}
.bg-green {
  background: #10b981;
}
.bg-orange {
  background: #f59e0b;
}
.bg-grey {
  background: #e2e8f0;
}

.attendance-text {
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
  width: 36px;
}

/* Status Badge */
.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}

.status-badge.active {
  background: #dcfce7;
  color: #166534;
}
.status-badge.inactive {
  background: #f1f5f9;
  color: #64748b;
}

/* Actions */
.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-top: 1px solid #e2e8f0;
}

.pagination-info {
  margin: 0;
  font-size: 14px;
  color: #64748b;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}

.page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: #475569;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover {
  background: #f1f5f9;
}
.page-btn.active {
  background: #3b82f6;
  color: white;
  font-weight: 600;
}
.page-dots {
  color: #94a3b8;
  padding: 0 8px;
}

/* Responsif Dasar */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .filter-section {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  .search-box {
    width: 100%;
  }
}
/* ================= Modal Styles ================= */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.4); /* Efek gelap/dim */
  backdrop-filter: blur(4px); /* Efek blur ala figma */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999; /* Memastikan modal paling depan */
}

.modal-content {
  background: white;
  width: 100%;
  max-width: 600px;
  border-radius: 16px;
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
}

/* Modal Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 24px 24px 16px;
  border-bottom: 1px solid #f1f5f9;
}

.modal-title {
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 4px 0;
}
.modal-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: all 0.2s;
}
.btn-close:hover {
  background: #f1f5f9;
  color: #0f172a;
}

/* Modal Body & Form Grid */
.modal-body {
  padding: 24px;
  overflow: visible;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  overflow: visible;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  position: relative;
  overflow: visible;
}
.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  background: white;
  outline: none;
  transition: border-color 0.2s;
  font-family: inherit;
  box-sizing: border-box;
}

/* Searchable Select Styles */
.searchable-select {
  position: relative;
  width: 100%;
  display: block;
  pointer-events: auto;
}
/* arrow indicator inside searchable select */
.searchable-select::after {
  content: "";
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 10px;
  height: 10px;
  background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9' fill='none' stroke='%23747569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E")
    no-repeat center center;
  pointer-events: none;
}

.searchable-select input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #0f172a;
  background: white;
  outline: none;
  transition: border-color 0.2s;
  font-family: inherit;
  box-sizing: border-box;
}

.searchable-select input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-top: none;
  border-radius: 0 0 8px 8px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  pointer-events: auto;
}

.dropdown-item {
  padding: 10px 14px;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 14px;
  color: #475569;
  pointer-events: auto;
  user-select: none;
}

.dropdown-item:hover {
  background: #f8fafc;
  color: #0f172a;
}

.dropdown-item.add-new {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #3b82f6;
  font-weight: 600;
  border-top: 1px solid #e2e8f0;
  background: #f0f7ff;
}

.dropdown-item.add-new:hover {
  background: #e0efff;
}

.form-group input::placeholder {
  color: #94a3b8;
}
.form-group input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Modal Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  padding: 16px 24px 24px;
}

.btn-cancel {
  background: transparent;
  border: none;
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  padding: 10px 16px;
  border-radius: 8px;
  transition: background 0.2s;
}
.btn-cancel:hover {
  background: #f8fafc;
}

.btn-save {
  background: #3b82f6;
  border: none;
  font-size: 14px;
  font-weight: 600;
  color: white;
  cursor: pointer;
  padding: 10px 20px;
  border-radius: 8px;
  transition: background 0.2s;
}
.btn-save:hover {
  background: #2563eb;
}

/* Responsive adjustments for mobile */
@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .empty-spacer {
    display: none;
  }
  .modal-content {
    height: 100vh;
    max-height: 100vh;
    border-radius: 0;
  }
  .modal-body {
    overflow-y: auto;
  }
}
</style>
