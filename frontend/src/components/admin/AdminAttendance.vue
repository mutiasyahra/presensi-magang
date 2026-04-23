<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../api/axios.js";
import Swal from "sweetalert2";
import { Mail, Phone, Users, FolderOpen, Calendar } from "lucide-vue-next";

// Variabel untuk mengontrol Modal (true = muncul, false = sembunyi)
const showAddModal = ref(false);
const showDetailModal = ref(false);
const selectedIntern = ref(null);
const isEditing = ref(false);

// interns will be loaded from backend
const interns = ref([]);
const loading = ref(false);
const error = ref(null);

const universities = ref([]);
const departments = ref([]);
const mentors = ref([]);
const projects = ref([]);

const activeDropdown = ref(null);
const searchQuery = ref("");
const selectedUniversity = ref("All Universities");
const selectedProject = ref("All Projects");

const filteredInterns = computed(() => {
  let result = interns.value;

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(
      (i) =>
        (i.name && i.name.toLowerCase().includes(q)) ||
        (i.id && String(i.id).toLowerCase().includes(q)) ||
        (i.mentor && i.mentor.toLowerCase().includes(q)),
    );
  }

  if (
    selectedUniversity.value &&
    selectedUniversity.value !== "All Universities"
  ) {
    result = result.filter((i) => i.university === selectedUniversity.value);
  }

  if (selectedProject.value && selectedProject.value !== "All Projects") {
    result = result.filter((i) => i.project === selectedProject.value);
  }

  return result;
});

const handleExport = () => {
  const dataToExport = filteredInterns.value;
  if (!dataToExport.length) return;

  const headers = [
    "Full Name",
    "Email Address",
    "Phone Number",
    "Intern ID",
    "University",
    "Department",
    "Mentor",
    "Project",
    "Start Date",
    "End Date",
    "Attendance %",
  ];

  const rows = dataToExport.map((i) => [
    `"${i.name || ""}"`,
    `"${i.email || ""}"`,
    `"${i.phone_number || i.phone || ""}"`,
    `"${i.id || ""}"`,
    `"${i.university || ""}"`,
    `"${i.department || ""}"`,
    `"${i.mentor || ""}"`,
    `"${i.project || ""}"`,
    `"${i.start_date || ""}"`,
    `"${i.end_date || ""}"`,
    `"${i.attendance || 0}%"`,
  ]);

  // Gunakan separator titik koma (;) agar otomatis dipisah kolomnya oleh Excel di regional Indonesia
  // serta tambahkan karakter BOM (\uFEFF) untuk memberitahu Excel bahwa file ini adalah UTF-8.
  let csvContent =
    "data:text/csv;charset=utf-8,\uFEFF" +
    [headers.join(";"), ...rows.map((e) => e.join(";"))].join("\n");

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "interns_export.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

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
    interns.value = fetchedData.map((i) => ({
      ...i,
      name: i.full_name || "Unknown",
      id: i.intern_id || i.id,
      attendance: i.attendance_percentage || 0,
      status: i.status || "Active",
      profile_photo: i.user?.profile_photo || i.profile_photo || null,
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
  if (v && !arrRef.some((item) => item.toLowerCase() === v.toLowerCase())) {
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

const openAddModal = () => {
  isEditing.value = false;
  formData.value = {
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
  };
  showAddModal.value = true;
};

const openEditModal = (intern) => {
  isEditing.value = true;
  formData.value = {
    name: intern.name,
    email: intern.email,
    phone: intern.phone_number || intern.phone,
    id: intern.id || intern.intern_id,
    universityInput: intern.university,
    departmentInput: intern.department,
    mentorInput: intern.mentor,
    projectInput: intern.project,
    startDate: intern.start_date,
    endDate: intern.end_date,
  };
  showAddModal.value = true;
};

// Save intern (POST/PUT) and update UI
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

    if (isEditing.value) {
      const res = await api.put(`/interns/${formData.value.id}`, payload);
      const updated = res.data?.data || res.data || payload;
      const index = interns.value.findIndex((i) => i.id === formData.value.id);
      if (index !== -1) {
        interns.value[index] = {
          ...interns.value[index],
          ...updated,
          name: updated.full_name || updated.name,
          id: updated.intern_id || updated.id,
        };
      }
    } else {
      const res = await api.post(`/interns`, payload);
      const created = res.data?.data || res.data || {};
      // add to interns list and repopulate options
      interns.value.unshift({
        ...created,
        name: created.full_name || "Unknown",
        id: created.intern_id || created.id,
        attendance: created.attendance_percentage || 0,
        status: created.status || "Active",
      });
    }
    populateOptionsFromInterns();
    showAddModal.value = false;
  } catch (err) {
    saveError.value = err.response?.data?.message || err.message || String(err);
  } finally {
    saving.value = false;
  }
};

const deleteIntern = async (id) => {
  const result = await Swal.fire({
    title: "Are you sure?",
    text: "You won't be able to revert this!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, delete it!",
  });

  if (result.isConfirmed) {
    try {
      await api.delete(`/interns/${id}`);
      interns.value = interns.value.filter((i) => i.id !== id);
      populateOptionsFromInterns();
      Swal.fire("Deleted!", "Intern has been deleted.", "success");
    } catch (error) {
      Swal.fire(
        "Error!",
        error.response?.data?.message || "Failed to delete intern.",
        "error",
      );
    }
  }
};

// calculate derived values for stats cards
const activeAttendancePercentage = computed(() => {
  if (stats.value.total_interns) {
    return ((stats.value.present / stats.value.total_interns) * 100).toFixed(1);
  }
  return 0;
});

const universityCount = computed(() => {
  const set = new Set(interns.value.map((i) => i.university).filter(Boolean));
  return set.size;
});

const projectCount = computed(() => {
  const set = new Set(interns.value.map((i) => i.project).filter(Boolean));
  return set.size;
});

// Fungsi warna progress bar
const getProgressColor = (value) => {
  if (value >= 90) return "bg-green";
  if (value > 0) return "bg-orange";
  return "bg-grey";
};

// Buka Modal Detail
const openDetailModal = (intern) => {
  selectedIntern.value = intern;
  showDetailModal.value = true;
};

// Beralih dari Modal Detail ke Modal Edit
const openEditFromDetail = () => {
  showDetailModal.value = false;
  openEditModal(selectedIntern.value);
};

const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith("data:") || path.startsWith("http")) return path;
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace("/api", "");
  return `${baseUrl}/storage/${path}`;
};

function formatDate(dateStr) {
  if (!dateStr) return "—";
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}
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
        <button class="btn-export" @click="handleExport">
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
        <button class="btn-add" @click="openAddModal">
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
        <p class="stat-label">UNIVERSITIES</p>
        <div class="stat-value-group align-end">
          <h2 class="stat-value highlight-orange">
            {{ universityCount }}
          </h2>
          <span class="stat-subtext">Partner universities</span>
        </div>
      </div>

      <div class="stat-card">
        <p class="stat-label">PROJECTS</p>
        <div class="stat-value-group align-end">
          <h2 class="stat-value">{{ projectCount }}</h2>
          <span class="stat-subtext">Active projects</span>
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
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search by name, ID, or mentor..."
        />
      </div>

      <div class="filter-actions">
        <select v-model="selectedUniversity" class="filter-select">
          <option value="All Universities">All Universities</option>
          <option v-for="uni in universities" :key="uni" :value="uni">
            {{ uni }}
          </option>
        </select>
        <select v-model="selectedProject" class="filter-select">
          <option value="All Projects">All Projects</option>
          <option v-for="proj in projects" :key="proj" :value="proj">
            {{ proj }}
          </option>
        </select>
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
          <tr v-for="intern in filteredInterns" :key="intern.id">
            <td>
              <div class="intern-profile">
                <div class="avatar">
                  <img
                    v-if="intern.profile_photo"
                    :src="intern.profile_photo"
                    class="avatar-img-fit"
                  />
                  <span v-else>{{ intern.name.charAt(0) }}</span>
                </div>
                <div class="intern-details-cell">
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
                <button
                  class="action-btn"
                  title="View"
                  @click="openDetailModal(intern)"
                >
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
                <button
                  class="action-btn"
                  title="Edit"
                  @click="openEditModal(intern)"
                >
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
                <button
                  class="action-btn btn-delete"
                  title="Delete"
                  @click="deleteIntern(intern.id)"
                >
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
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path
                      d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
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
          Showing {{ filteredInterns.length > 0 ? 1 : 0 }} to
          {{ filteredInterns.length }} of {{ filteredInterns.length }} interns
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
          <h2 class="modal-title">
            {{ isEditing ? "Edit Intern Details" : "Add New Intern" }}
          </h2>
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
          <span v-else>{{ isEditing ? "Save Changes" : "Save Intern" }}</span>
        </button>
      </div>
    </div>
  </div>
  <!-- Modal Detail Intern -->
  <div
    v-if="showDetailModal && selectedIntern"
    class="modal-overlay"
    @click.self="showDetailModal = false"
  >
    <div class="modal-content detail-modal">
      <!-- Header -->
      <div class="detail-header">
        <button class="btn-back" @click="showDetailModal = false">
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
          Back to Directory
        </button>
        <button class="btn-close" @click="showDetailModal = false">
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

      <!-- Profile Section -->
      <div class="detail-profile">
        <div class="detail-avatar-wrapper">
          <img
            v-if="selectedIntern.profile_photo"
            :src="getImageUrl(selectedIntern.profile_photo)"
            :alt="selectedIntern.name"
          />
          <div v-else class="detail-avatar-fallback">
            {{ selectedIntern.name?.charAt(0) }}
          </div>
          <span
            class="status-dot"
            :class="selectedIntern.status?.toLowerCase()"
          ></span>
        </div>
        <h2 class="detail-name">{{ selectedIntern.name }}</h2>
        <p class="detail-id">ID: {{ selectedIntern.id }}</p>
        <p class="detail-role">
          {{ selectedIntern.university }} | {{ selectedIntern.department }}
        </p>
      </div>

      <!-- Stats Row -->
      <div class="detail-stats">
        <div class="stat-col">
          <span class="d-stat-lbl">ATTENDANCE</span>
          <span class="d-stat-val">{{ selectedIntern.attendance }}%</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-col">
          <span class="d-stat-lbl">TASKS DONE</span>
          <span class="d-stat-val">
            {{ selectedIntern.tasks_done ?? "—" }}
          </span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-col">
          <span class="d-stat-lbl">LEAVE BAL.</span>
          <span class="d-stat-val">{{
            selectedIntern.leave_balance ?? "—"
          }}</span>
        </div>
      </div>

      <!-- Info List -->
      <div class="detail-info-list">
        <div class="info-row">
          <span class="info-label">
            <Mail :size="20" color="#3B82F6" />
            Email Address
          </span>
          <span class="info-value">{{ selectedIntern.email || "—" }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">
            <Phone :size="20" color="#3B82F6" />
            Phone Number
          </span>
          <span class="info-value">{{
            selectedIntern.phone_number || "—"
          }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">
            <Users :size="20" color="#3B82F6" />
            Mentor
          </span>
          <span class="info-value">{{ selectedIntern.mentor || "—" }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">
            <FolderOpen :size="20" color="#3B82F6" />
            Project
          </span>
          <span class="info-value">{{ selectedIntern.project || "—" }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">
            <Calendar :size="20" color="#3B82F6" />
            Internship Period
          </span>
          <span class="info-value"
            >{{ formatDate(selectedIntern.start_date) }} –
            {{
              selectedIntern.end_date
                ? formatDate(selectedIntern.end_date)
                : "—"
            }}</span
          >
        </div>
      </div>

      <!-- Action Button -->
      <div class="detail-action-wrapper">
        <button class="btn-modify" @click="openEditFromDetail">
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
            <path
              d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
            ></path>
            <path
              d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
            ></path>
          </svg>
          Modify Intern Details
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ================= MAIN LAYOUT & TABLE ================= */
.directory-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  position: sticky;
  top: 0;
  z-index: 100;
  padding: 20px 0;
  margin-top: -20px;
  background: var(--bg-app);
  border-bottom: 1px solid var(--border-color);
}

.main-title {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0 0 4px 0;
  letter-spacing: -0.5px;
}

.sub-title {
  font-size: 14px;
  color: var(--text-muted);
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
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
}

.btn-export:hover {
  background: var(--bg-input);
  border-color: var(--text-dim);
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
  opacity: 0.9;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.stat-card {
  background: var(--bg-card);
  padding: 20px 24px;
  border-radius: 16px;
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-label {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted);
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
  color: var(--text-main);
  margin: 0;
  line-height: 1;
}

.highlight-orange {
  color: var(--accent-warning);
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  font-weight: 600;
}

.trend-up {
  color: var(--accent-success);
}

.stat-subtext {
  font-size: 12px;
  color: var(--text-dim);
  line-height: 1.3;
}

/* Filters & Search */
.filter-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--bg-card);
  padding: 16px 24px;
  border-radius: 16px;
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
}

.search-box {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--bg-input);
  padding: 10px 16px;
  border-radius: 8px;
  width: 320px;
  border: 1px solid var(--border-color);
  transition: border 0.2s;
}

.search-box:focus-within {
  border-color: var(--accent-primary);
  background: var(--bg-card);
}

.search-icon {
  color: var(--text-dim);
}

.search-box input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 14px;
  color: var(--text-main);
  outline: none;
}

.search-box input::placeholder {
  color: var(--text-dim);
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
  color: var(--text-muted);
  cursor: pointer;
}

.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: var(--bg-input);
  color: var(--text-main);
}

/* Table Section */
.table-container {
  background: var(--bg-card);
  border-radius: 16px;
  border: 1px solid var(--border-color);
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
  color: var(--text-dim);
  letter-spacing: 0.5px;
  border-bottom: 1px solid var(--border-color);
}

.directory-table td {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color);
  vertical-align: middle;
}

.directory-table tr:last-child td {
  border-bottom: none;
}
.directory-table tr:hover td {
  background: var(--bg-input);
}

.intern-profile {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatar {
  width: 40px;
  height: 40px;
  min-width: 40px;
  min-height: 40px;
  border-radius: 50%;
  background: var(--bg-input);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  font-weight: 700;
  color: var(--text-dim);
  flex-shrink: 0;
}

.avatar img.avatar-img-fit {
  width: 40px;
  height: 40px;
  min-width: 40px;
  min-height: 40px;
  object-fit: cover;
  border-radius: 50%;
  display: block;
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
  color: var(--text-main);
}
.intern-id {
  margin: 0;
  font-size: 12px;
  color: var(--text-dim);
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
  background: var(--bg-input);
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
  background: var(--surface-grey);
}

.attendance-text {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
  width: 36px;
}

.detail-avatar-fallback {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: var(--bg-input);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  font-weight: 700;
  color: var(--text-dim);
}

.status-dot.inactive {
  background: #94a3b8;
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
  background: var(--bg-input);
  color: var(--text-main);
}

.action-btn.btn-delete:hover {
  color: #ef4444;
  background: #fef2f2;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-top: 1px solid var(--border-color);
}

.pagination-info {
  margin: 0;
  font-size: 14px;
  color: var(--text-muted);
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
  background: var(--bg-input);
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
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}
.modal-content {
  background: var(--bg-card);
  width: 100%;
  max-width: 600px;
  border-radius: 16px;
  border: 1px solid var(--border-color);
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.2),
    0 10px 10px -5px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
}

/* Modal Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 24px 24px 16px;
  border-bottom: 1px solid var(--border-color);
}

.modal-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0 0 4px 0;
}
.modal-subtitle {
  font-size: 14px;
  color: var(--text-muted);
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
  background: var(--bg-input);
  color: var(--text-main);
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
  color: var(--text-main);
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
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  color: var(--text-main);
  background: var(--bg-input);
  outline: none;
  transition: border-color 0.2s;
  font-family: inherit;
  box-sizing: border-box;
}

.searchable-select input:focus {
  border-color: var(--accent-primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-top: none;
  border-radius: 0 0 8px 8px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  pointer-events: auto;
}

.dropdown-item {
  padding: 10px 14px;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 14px;
  color: var(--text-muted);
  pointer-events: auto;
  user-select: none;
}

.dropdown-item:hover {
  background: var(--bg-input);
  color: var(--text-main);
}

.dropdown-item.add-new {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--accent-primary);
  font-weight: 600;
  border-top: 1px solid var(--border-color);
  background: var(--surface-info);
}

.dropdown-item.add-new:hover {
  background: #e0efff;
}

.form-group input::placeholder {
  color: var(--text-dim);
}
.form-group input:focus {
  border-color: var(--accent-primary);
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
  color: var(--text-muted);
  cursor: pointer;
  padding: 10px 16px;
  border-radius: 8px;
  transition: background 0.2s;
}
.btn-cancel:hover {
  background: var(--bg-input);
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
  opacity: 0.9;
}

/* ================= MODAL DETAIL STYLES ================= */
.detail-modal {
  width: 100%;
  max-width: 540px;
}
.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-bottom: 1px solid #f1f5f9;
}
.btn-back {
  display: flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  font-size: 14px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: color 0.2s;
}
.btn-back:hover {
  color: #0f172a;
}

.detail-profile {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 28px 24px 20px;
}
.detail-avatar-wrapper {
  position: relative;
  width: 88px;
  height: 88px;
  border-radius: 50%;
  padding: 4px;
  border: 2px solid white;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  margin-bottom: 16px;
}
.detail-avatar-wrapper img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}
.status-dot {
  position: absolute;
  bottom: 2px;
  right: 6px;
  width: 16px;
  height: 16px;
  background: #10b981;
  border: 3px solid white;
  border-radius: 50%;
}

.detail-name {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 4px 0;
}
.detail-id {
  font-size: 12px;
  font-weight: 700;
  color: #f59e0b;
  margin: 0 0 6px 0;
  letter-spacing: 0.5px;
}
.detail-role {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.detail-stats {
  display: flex;
  align-items: center;
  padding: 24px 0;
  margin: 16px 24px;
  border-top: 1px solid #f1f5f9;
  border-bottom: 1px solid #f1f5f9;
}
.stat-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}
.d-stat-lbl {
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 1px;
}
.d-stat-val {
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
}
.stat-divider {
  width: 1px;
  height: 32px;
  background: #f1f5f9;
}

.detail-info-list {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.info-label {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  color: #64748b;
}
.info-value {
  font-size: 14px;
  font-weight: 600;
  color: #0f172a;
  text-align: right;
}

.detail-action-wrapper {
  padding: 0 24px 24px;
}
.btn-modify {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  background: #3b82f6;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
}
.btn-modify:hover {
  background: #2563eb;
}

/* Responsive adjustments */
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
.filter-select {
  padding: 10px 38px 10px 16px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  color: var(--text-main);
  background-color: var(--bg-card);
  outline: none;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  transition: all 0.2s ease;
}

.filter-select:focus {
  border-color: var(--accent-primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
