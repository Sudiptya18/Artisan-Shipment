<template>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Activity Log</h1>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-history me-1"></i>
                User Activity Logs
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input
                            v-model="search"
                            type="text"
                            class="form-control"
                            placeholder="Search activities..."
                            @input="debouncedSearch"
                        />
                    </div>
                    <div class="col-md-3">
                        <select v-model="filterAction" class="form-select" @change="handleFilterChange">
                            <option value="">All Actions</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="insert">Insert</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                            <option value="view">View</option>
                        </select>
                    </div>
                    <div class="col-md-3 ms-auto">
                        <select v-model="perPage" class="form-select" @change="handlePerPageChange">
                            <option :value="50">50 per page</option>
                            <option :value="100">100 per page</option>
                            <option :value="150">150 per page</option>
                            <option :value="200">200 per page</option>
                            <option :value="0">All</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">Sl No.</th>
                                <th class="text-center">User</th>
                                <th class="text-center">Action</th>
                                <th class="text-center">Page</th>
                                <th class="text-center">Description</th>
                                <th class="text-center">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(log, index) in logs" :key="log.id">
                                <td class="text-center">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="text-center">
                                    {{ log.causer?.name || 'System' }}
                                    <br />
                                    <small class="text-muted">{{ log.causer?.username || '' }}</small>
                                </td>
                                <td class="text-center">
                                    <span :class="getActionBadgeClass(getActionFromDescription(log.description))" class="badge">
                                        {{ getActionFromDescription(log.description) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ log.properties?.page || '-' }}</td>
                                <td class="text-center">{{ log.description || '-' }}</td>
                                <td class="text-center">
                                    {{ formatDate(log.created_at) }}
                                </td>
                            </tr>
                            <tr v-if="logs.length === 0">
                                <td colspan="6" class="text-center">No activity logs found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1 && perPage > 0" class="d-flex justify-content-center mt-4">
                    <vue-awesome-paginate
                        :total-items="totalItems"
                        v-model="currentPage"
                        :items-per-page="perPage"
                        :max-pages-shown="5"
                        paginate-buttons-class="btn"
                        active-page-class="btn-active"
                        back-button-class="back-btn"
                        next-button-class="next-btn"
                        @click="handlePageChange"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import VueAwesomePaginate from 'vue-awesome-paginate';
import 'vue-awesome-paginate/dist/style.css';

const logs = ref([]);
const search = ref('');
const filterAction = ref('');
const currentPage = ref(1);
const perPage = ref(50);
const totalPages = ref(1);
const totalItems = ref(0);

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        fetchLogs();
    }, 500);
};

const fetchLogs = async () => {
    try {
        const params = {
            per_page: perPage.value,
        };

        // Only add page parameter if per_page > 0
        if (perPage.value > 0) {
            params.page = currentPage.value;
        }

        if (search.value) {
            params.search = search.value;
        }

        if (filterAction.value) {
            params.action = filterAction.value;
        }

        const response = await axios.get('/api/activity-logs', { params });
        logs.value = response.data.data ?? [];
        currentPage.value = response.data.current_page ?? 1;
        totalPages.value = response.data.last_page ?? 1;
        totalItems.value = response.data.total ?? 0;
    } catch (error) {
        console.error('Error fetching activity logs:', error);
        if (error.response?.status === 403) {
            alert('You do not have permission to view activity logs.');
        }
    }
};

const handlePageChange = () => {
    fetchLogs();
};

const handlePerPageChange = () => {
    currentPage.value = 1;
    fetchLogs();
};

const handleFilterChange = () => {
    currentPage.value = 1;
    fetchLogs();
};

const getActionFromDescription = (description) => {
    if (!description) return 'unknown';
    const desc = description.toLowerCase();
    if (desc.includes('logged in')) return 'login';
    if (desc.includes('logged out')) return 'logout';
    if (desc.includes('created') || desc.includes('registered')) return 'insert';
    if (desc.includes('updated')) return 'update';
    if (desc.includes('deleted')) return 'delete';
    return 'other';
};

const getActionBadgeClass = (action) => {
    const classes = {
        login: 'bg-success',
        logout: 'bg-secondary',
        insert: 'bg-primary',
        update: 'bg-warning',
        delete: 'bg-danger',
        view: 'bg-info',
        other: 'bg-info',
    };
    return classes[action] || 'bg-secondary';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString();
};

onMounted(() => {
    fetchLogs();
});
</script>

<style scoped>
.btn {
    height: 40px;
    width: 40px;
    border: 1px solid #dee2e6;
    margin-inline: 5px;
    cursor: pointer;
    background-color: #fff;
    color: #212529;
    border-radius: 0.375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease-in-out;
}

.btn:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.back-btn,
.next-btn {
    background-color: #0e9351;
    color: #fff;
    border-color: #0e9351;
}

.back-btn:hover,
.next-btn:hover {
    background-color: #18a950;
    border-color: #18a950;
}

.btn-active {
    background-color: #0e9351;
    color: #fff;
    border-color: #0e9351;
}
</style>

