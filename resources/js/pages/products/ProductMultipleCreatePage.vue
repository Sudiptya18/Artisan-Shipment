<template>
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between my-4">
            <h1 class="mb-0">Create Multiple Products</h1>
            <div class="d-flex align-items-center gap-3">
                <button @click="saveProducts" class="btn btn-success" :disabled="isSaving">
                    <Loader v-if="isSaving" class="me-2" />
                    Save Products
                </button>
            </div>
        </div>

        <!-- Alert Message -->
        <div v-if="alert.message" :class="`alert alert-${alert.type} mx-4 mt-4`" role="alert">
            {{ alert.message }}
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="card">
            <div class="card-body text-center py-5">
                <Loader class="me-2" />
                Loading products...
            </div>
        </div>

        <!-- Handsontable -->
        <div v-else class="card position-relative">
            <!-- Preloader Overlay -->
            <div v-if="isSaving" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: rgba(255, 255, 255, 0.8); z-index: 1000; border-radius: 0.375rem;">
                <div class="text-center">
                    <Loader class="mb-2" style="font-size: 2rem;" />
                    <p class="mb-0 text-muted">Processing... Please wait</p>
                </div>
            </div>
            <div class="card-body p-0">
                <hot-table
                    ref="hotTableComponent"
                    :data="tableData"
                    :settings="hotSettings"
                    :license-key="'non-commercial-and-evaluation'"
                ></hot-table>
            </div>
        </div>

        <!-- Success Modal -->
        <SuccessModal
            v-model:show="showSuccessModal"
            title="Success!"
            :message="successMessage"
            @close="handleModalClose"
        />

        <!-- Failed Modal -->
        <FailedModal
            v-model:show="showFailedModal"
            title="Failed!"
            :message="failedMessage"
            @close="handleModalClose"
        />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { HotTable } from '@handsontable/vue3';
import { registerAllModules } from 'handsontable/registry';
import 'handsontable/dist/handsontable.full.css';
import SuccessModal from '@/components/SuccessModal.vue';
import FailedModal from '@/components/FailedModal.vue';
import Loader from '@/components/Loader.vue';

registerAllModules();

const router = useRouter();
const hotTableComponent = ref(null);
const isSaving = ref(false);
const isLoading = ref(false);
const showSuccessModal = ref(false);
const showFailedModal = ref(false);
const successMessage = ref('');
const failedMessage = ref('');
const tableData = ref([{}]);
let modalTimeout = null;

const alert = reactive({
    type: '',
    message: '',
});

const brands = ref([]);
const categories = ref([]);
const formats = ref([]);
const origins = ref([]);
const dataFilter = reactive({
    brand: [],
    category: [],
    format: [],
    origin: [],
    status: [
        'ACTIVE',
        'DISCONTINUED-UI',
        'DISCONTINUED-ARTISAN',
        'REPLACEMENT',
        'REPLACEMENT & DISCONTINUED',
        'NEW CODE',
        'FUTURE DISCONTINUED',
        'NEW TENTATIVE',
        'DISCONTINUED',
    ],
});

const hotSettings = computed(() => {
    return {
        height: window.innerHeight - 250,
        startRows: 10,
        startCols: 10,
        rowHeaders: true,
        colHeaders: [
            'Global Code',
            'Product Title',
            'Description',
            'Benefits',
            'Pack Size',
            'Brand',
            'Category',
            'Format',
            'Origin',
            'Status',
        ],
        minSpareRows: 1,
        manualColumnResize: true,
        stretchH: 'all',
        customBorders: true,
        columnSorting: true,
        sortIndicator: true,
        contextMenu: ['undo', 'redo', '---------', 'remove_row'],
        columns: [
            { type: 'text', data: 'global_code', className: '', validator: 'uniqueRender' },
            { type: 'text', data: 'product_title', className: '' },
            { type: 'text', data: 'description', className: '' },
            { type: 'text', data: 'benefits', className: '' },
            { type: 'text', data: 'pack_size', className: '' },
            { type: 'autocomplete', data: 'brand', source: dataFilter.brand, className: '', allowInvalid: true, strict: false },
            { type: 'autocomplete', data: 'category', source: dataFilter.category, className: '', allowInvalid: true, strict: false },
            { type: 'autocomplete', data: 'format', source: dataFilter.format, className: '', allowInvalid: true, strict: false },
            { type: 'autocomplete', data: 'origin', source: dataFilter.origin, className: '', allowInvalid: true, strict: false },
            { type: 'autocomplete', data: 'status', source: dataFilter.status, className: '', allowInvalid: false, strict: true },
        ],
        cells: function (row, col, prop) {
            const cellProperties = {};
            if (dataFilter[prop]) {
                cellProperties.source = dataFilter[prop];
            }
            return cellProperties;
        },
        afterChange: async (changes, source) => {
            if (source === 'loadData' || !changes) {
                return;
            }

            // Handle auto-creation of new dropdown values
            for (const [row, col, oldValue, newValue] of changes) {
                if (!newValue || newValue === oldValue) continue;

                const hotInstance = hotTableComponent.value?.hotInstance;
                if (!hotInstance) continue;

                const prop = hotInstance.colToProp(col);
                
                // Check if it's a dropdown column and value doesn't exist
                if (['brand', 'category', 'format', 'origin'].includes(prop)) {
                    const currentSource = dataFilter[prop] || [];
                    const newValueStr = newValue.toString().trim();
                    
                    // If value doesn't exist in dropdown, create it
                    if (newValueStr && !currentSource.includes(newValueStr)) {
                        await createLookupItem(prop, newValueStr);
                    }
                }
                // Status column doesn't need auto-creation
            }
        },
    };
});

const fetchLookups = async () => {
    try {
        const response = await axios.get('/api/products/lookups');
        brands.value = response.data.brands || [];
        categories.value = response.data.categories || [];
        formats.value = response.data.formats || [];
        origins.value = response.data.origins || [];

        // Populate dataFilter for autocomplete (remove duplicates)
        dataFilter.brand = [...new Set(brands.value.map((b) => b.name))];
        dataFilter.category = [...new Set(categories.value.map((c) => c.name))];
        dataFilter.format = [...new Set(formats.value.map((f) => f.name))];
        dataFilter.origin = [...new Set(origins.value.map((o) => o.name))];

        // Update hot table settings
        updateHotTableSources();
    } catch (error) {
        console.error('Failed to fetch lookups:', error);
        alert.type = 'danger';
        alert.message = 'Failed to load dropdown options.';
    }
};

const updateHotTableSources = () => {
    if (hotTableComponent.value?.hotInstance) {
        hotTableComponent.value.hotInstance.updateSettings({
            cells: function (row, col, prop) {
                const cellProperties = {};
                if (dataFilter[prop]) {
                    cellProperties.source = dataFilter[prop];
                }
                return cellProperties;
            },
        });
    }
};

const createLookupItem = async (type, name) => {
    if (!name || !name.trim()) return;

    const nameTrimmed = name.trim();
    
    // Check if already exists (case-insensitive)
    const currentSource = dataFilter[type] || [];
    if (currentSource.some(item => item.toLowerCase() === nameTrimmed.toLowerCase())) {
        return; // Already exists, don't create duplicate
    }

    try {
        let endpoint = '';
        let payload = {};

        switch (type) {
            case 'brand':
                endpoint = '/api/brands';
                payload = { brand_name: nameTrimmed };
                break;
            case 'category':
                endpoint = '/api/categories';
                payload = { category_name: nameTrimmed };
                break;
            case 'format':
                endpoint = '/api/formats';
                payload = { format_name: nameTrimmed };
                break;
            case 'origin':
                endpoint = '/api/origins';
                payload = { origin_name: nameTrimmed };
                break;
            default:
                return;
        }

        const response = await axios.post(endpoint, payload);
        
        // Add to appropriate array
        if (type === 'brand') {
            brands.value.push({ id: response.data.data.id, name: response.data.data.brand_name });
            dataFilter.brand.push(nameTrimmed);
        } else if (type === 'category') {
            categories.value.push({ id: response.data.data.id, name: response.data.data.category_name });
            dataFilter.category.push(nameTrimmed);
        } else if (type === 'format') {
            formats.value.push({ id: response.data.data.id, name: response.data.data.format_name });
            dataFilter.format.push(nameTrimmed);
        } else if (type === 'origin') {
            origins.value.push({ id: response.data.data.id, name: response.data.data.origin_name });
            dataFilter.origin.push(nameTrimmed);
        }

        // Remove duplicates and sort
        dataFilter.brand = [...new Set(dataFilter.brand)].sort();
        dataFilter.category = [...new Set(dataFilter.category)].sort();
        dataFilter.format = [...new Set(dataFilter.format)].sort();
        dataFilter.origin = [...new Set(dataFilter.origin)].sort();

        // Update hot table sources
        updateHotTableSources();

    } catch (error) {
        console.error(`Failed to create ${type}:`, error);
        // Don't show error to user, just log it
    }
};

const fetchProducts = async () => {
    isLoading.value = true;
    try {
        // Fetch all products (per_page=0 returns all)
        const response = await axios.get('/api/products', {
            params: { per_page: 0 }
        });

        const products = response.data.data || response.data || [];
        
        // Transform products to table format (keep id for updates but don't display)
        const formattedData = products.map((product) => {
            const row = {
                id: product.id || null, // Keep ID for updates
                global_code: product.global_code || '',
                product_title: product.product_title || '',
                description: product.description || '',
                benefits: product.benefits || '',
                pack_size: product.pack_size || '',
                brand: product.brand?.name || '',
                category: product.category?.name || '',
                format: product.format?.name || '',
                origin: product.origin?.name || '',
                status: product.status || 'ACTIVE',
            };
            
            // Add new values to dropdowns if they don't exist
            if (row.brand && !dataFilter.brand.includes(row.brand)) {
                dataFilter.brand.push(row.brand);
            }
            if (row.category && !dataFilter.category.includes(row.category)) {
                dataFilter.category.push(row.category);
            }
            if (row.format && !dataFilter.format.includes(row.format)) {
                dataFilter.format.push(row.format);
            }
            if (row.origin && !dataFilter.origin.includes(row.origin)) {
                dataFilter.origin.push(row.origin);
            }
            
            return row;
        });

        // Add one empty row at the end for new entries
        formattedData.push({});

        tableData.value = formattedData;

        // Load data into Handsontable after it's initialized
        if (hotTableComponent.value?.hotInstance) {
            // Remove duplicates and sort dropdown sources
            dataFilter.brand = [...new Set(dataFilter.brand)].sort();
            dataFilter.category = [...new Set(dataFilter.category)].sort();
            dataFilter.format = [...new Set(dataFilter.format)].sort();
            dataFilter.origin = [...new Set(dataFilter.origin)].sort();
            
            updateHotTableSources();
            hotTableComponent.value.hotInstance.loadData(formattedData);
        }
    } catch (error) {
        console.error('Failed to fetch products:', error);
        alert.type = 'danger';
        alert.message = 'Failed to load existing products.';
    } finally {
        isLoading.value = false;
    }
};

const saveProducts = async () => {
    if (!hotTableComponent.value?.hotInstance) {
        alert.type = 'danger';
        alert.message = 'Table not initialized.';
        return;
    }

    isSaving.value = true;
    alert.message = '';

    try {
        const hotInstance = hotTableComponent.value.hotInstance;
        const sourceData = hotInstance.getSourceData();
        const products = [];

        // Convert table data to product format
        for (let i = 0; i < sourceData.length; i++) {
            const row = sourceData[i];
            if (!row) continue;
            
            const globalCode = row.global_code?.toString().trim() || '';
            if (!globalCode) continue; // Skip if global_code is empty

            const product = {
                id: row.id || null,
                global_code: globalCode,
                product_title: row.product_title?.toString().trim() || null,
                description: row.description?.toString().trim() || null,
                benefits: row.benefits?.toString().trim() || null,
                pack_size: row.pack_size?.toString().trim() || null,
                brand: row.brand?.toString().trim() || null,
                category: row.category?.toString().trim() || null,
                format: row.format?.toString().trim() || null,
                origin: row.origin?.toString().trim() || null,
                status: row.status?.toString().trim() || 'ACTIVE', // Use status from row or default
                active: true, // Default active
            };

            // Find IDs for brand, category, format, origin (case-insensitive)
            if (product.brand) {
                const brand = brands.value.find((b) => b.name.toLowerCase() === product.brand.toLowerCase());
                product.brand_id = brand ? brand.id : null;
            }

            if (product.category) {
                const category = categories.value.find((c) => c.name.toLowerCase() === product.category.toLowerCase());
                product.category_id = category ? category.id : null;
            }

            if (product.format) {
                const format = formats.value.find((f) => f.name.toLowerCase() === product.format.toLowerCase());
                product.format_id = format ? format.id : null;
            }

            if (product.origin) {
                const origin = origins.value.find((o) => o.name.toLowerCase() === product.origin.toLowerCase());
                product.origin_id = origin ? origin.id : null;
            }

            products.push(product);
        }

        if (products.length === 0) {
            alert.type = 'warning';
            alert.message = 'No products to save. Please add at least one product with Global Code.';
            isSaving.value = false;
            return;
        }

        // Send to backend
        const response = await axios.post('/api/products/bulk', { products });

        if (response.data.success) {
            isSaving.value = false; // Hide preloader
            successMessage.value = `Successfully ${response.data.created > 0 ? `created ${response.data.created} product(s)` : ''}${response.data.created > 0 && response.data.updated > 0 ? ' and ' : ''}${response.data.updated > 0 ? `updated ${response.data.updated} product(s)` : ''}.`;
            showSuccessModal.value = true;

            // Auto-close modal after 5 seconds and reload
            clearTimeout(modalTimeout);
            modalTimeout = setTimeout(() => {
                showSuccessModal.value = false;
                window.location.reload();
            }, 5000);
        }
    } catch (error) {
        console.error('Failed to save products:', error);
        isSaving.value = false; // Hide preloader
        failedMessage.value = error.response?.data?.message || 'Failed to save products. Please check your data.';
        showFailedModal.value = true;

        // Auto-close modal after 5 seconds and reload
        clearTimeout(modalTimeout);
        modalTimeout = setTimeout(() => {
            showFailedModal.value = false;
            window.location.reload();
        }, 5000);
    }
};

const handleModalClose = () => {
    clearTimeout(modalTimeout);
    showSuccessModal.value = false;
    showFailedModal.value = false;
    window.location.reload();
};

onMounted(async () => {
    await fetchLookups();
    await fetchProducts();
});
</script>

<style scoped>
.card {
    margin-bottom: 2rem;
}

.handsontable {
    font-size: 0.875rem;
}
</style>

