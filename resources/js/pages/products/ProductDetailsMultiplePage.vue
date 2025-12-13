<template>
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between my-4">
            <h1 class="mb-0">Multiple Product Details</h1>
            <div class="d-flex align-items-center gap-3">
                <button @click="saveProductDetails" class="btn btn-success" :disabled="isSaving">
                    <Loader v-if="isSaving" class="me-2" />
                    Save Product Details
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
                Loading product details...
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
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick, watch } from 'vue';
import axios from 'axios';
import { HotTable } from '@handsontable/vue3';
import { registerAllModules } from 'handsontable/registry';
import 'handsontable/dist/handsontable.full.css';
import Swal from 'sweetalert2';
import Loader from '@/components/Loader.vue';

registerAllModules();

const hotTableComponent = ref(null);
const isSaving = ref(false);
const isLoading = ref(false);
const tableData = ref([{}]);

const alert = reactive({
    type: '',
    message: '',
});

const products = ref([]);
const hscodes = ref([]);
const titles = ref([]);
const commodities = ref([]);
const productDetailsMap = ref(new Map()); // Map product_id to product_detail

const dataFilter = reactive({
    global_code: [],
    product_name: [],
    hs_code: [],
    shipment_title: [],
    commodity: [],
});

const hotSettings = computed(() => {
    return {
        height: window.innerHeight - 250,
        startRows: 10,
        startCols: 16,
        rowHeaders: true,
        colHeaders: [
            'Global Code',
            'Product Name',
            'Pcs/Cases',
            'Cases/Pal',
            'Cases/Lay',
            'Container load',
            'Cases / 20 ft Container',
            'Cases / 40 ft Container',
            'Total Shelf life',
            'Gross Weight (CS) - KG',
            'Net Weight (CS) - KG',
            'CBM',
            'HS CODE',
            'RATE',
            'SHIPMENT TITLE',
            'COMMODITY',
        ],
        minSpareRows: 1,
        manualColumnResize: true,
        stretchH: 'all',
        customBorders: true,
        columnSorting: true,
        sortIndicator: true,
        contextMenu: ['undo', 'redo', '---------', 'remove_row'],
        copyPaste: true,
        allowInsertRow: true,
        allowRemoveRow: true,
        columns: [
            { 
                type: 'autocomplete', 
                data: 'global_code', 
                source: dataFilter.global_code, 
                className: '',
                allowInvalid: false,
                strict: true,
            },
            { 
                type: 'text', 
                data: 'product_name', 
                readOnly: true, 
                className: 'htReadOnly' 
            },
            { type: 'text', data: 'pcs_cases', className: '' },
            { type: 'text', data: 'cases_pal', className: '' },
            { type: 'text', data: 'cases_lay', className: '' },
            { type: 'text', data: 'container_load', className: '' },
            { type: 'text', data: 'cases_20ft_container', className: '' },
            { type: 'text', data: 'cases_40ft_container', className: '' },
            { type: 'text', data: 'total_shelf_life', className: '' },
            { type: 'numeric', data: 'gross_weight_cs_kg', numericFormat: { pattern: '0.00' }, className: '' },
            { type: 'numeric', data: 'net_weight_cs_kg', numericFormat: { pattern: '0.00' }, className: '' },
            { type: 'numeric', data: 'cbm', numericFormat: { pattern: '0.00' }, className: '' },
            { 
                type: 'autocomplete', 
                data: 'hs_code', 
                source: dataFilter.hs_code, 
                className: '', 
                allowInvalid: false, 
                strict: true 
            },
            { type: 'numeric', data: 'rate', numericFormat: { pattern: '0.00' }, className: '' },
            { 
                type: 'autocomplete', 
                data: 'shipment_title', 
                source: dataFilter.shipment_title, 
                className: '', 
                allowInvalid: false, 
                strict: true 
            },
            { 
                type: 'autocomplete', 
                data: 'commodity', 
                source: dataFilter.commodity, 
                className: '', 
                allowInvalid: false, 
                strict: true 
            },
        ],
        afterChange: (changes, source) => {
            if (source === 'loadData' || !changes) {
                return;
            }

            const hotInstance = hotTableComponent.value?.hotInstance;
            if (!hotInstance) return;

            // Handle global_code change - auto-populate product_name
            for (const [row, col, oldValue, newValue] of changes) {
                const prop = hotInstance.colToProp(col);
                
                if (prop === 'global_code' && newValue) {
                    const product = products.value.find(p => p.global_code === newValue.toString().trim());
                    if (product) {
                        hotInstance.setDataAtCell(row, hotInstance.propToCol('product_name'), product.product_title);
                        hotInstance.setDataAtCell(row, hotInstance.propToCol('product_id'), product.id);
                    }
                }
            }
        },
        beforeKeyDown: (event) => {
            // Ctrl+E to export as Excel/CSV
            if (event.ctrlKey && (event.key === 'e' || event.key === 'E')) {
                event.preventDefault();
                exportToExcel();
            }
        },
        copyPaste: {
            pasteMode: 'shift_down',
            rowsLimit: 1000,
            columnsLimit: 1000,
        },
    };
});

const fetchLookups = async () => {
    try {
        const [productsRes, lookupsRes] = await Promise.all([
            axios.get('/api/product-details/products'),
            axios.get('/api/product-details/lookups'),
        ]);

        products.value = productsRes.data.data || [];
        hscodes.value = lookupsRes.data.hscodes || [];
        titles.value = lookupsRes.data.titles || [];
        commodities.value = lookupsRes.data.commodities || [];

        // Populate dataFilter for autocomplete (remove duplicates and sort)
        dataFilter.global_code = [...new Set(products.value.map(p => p.global_code).filter(Boolean))].sort();
        dataFilter.product_name = [...new Set(products.value.map(p => p.product_title).filter(Boolean))].sort();
        dataFilter.hs_code = [...new Set(hscodes.value.map(h => h.hscode).filter(Boolean))].sort();
        dataFilter.shipment_title = [...new Set(titles.value.map(t => t.name).filter(Boolean))].sort();
        dataFilter.commodity = [...new Set(commodities.value.map(c => c.name).filter(Boolean))].sort();

        console.log('Loaded products:', products.value.length);
        console.log('Loaded lookups:', {
            hscodes: hscodes.value.length,
            titles: titles.value.length,
            commodities: commodities.value.length,
        });
    } catch (error) {
        console.error('Failed to fetch lookups:', error);
        alert.type = 'danger';
        alert.message = 'Failed to load dropdown options.';
    }
};

const fetchProductDetails = async () => {
    isLoading.value = true;
    try {
        // Fetch product details
        const response = await axios.get('/api/product-details');
        const productDetails = response.data.data || [];

        // Create a map of product_id to product_detail for quick lookup
        productDetailsMap.value = new Map();
        productDetails.forEach(detail => {
            productDetailsMap.value.set(detail.product_id, detail);
        });

        // Transform all products to table format (all products from products table)
        const formattedData = products.value.map((product) => {
            const detail = productDetailsMap.value.get(product.id);
            
            return {
                id: detail?.id || null,
                product_id: product.id,
                global_code: product.global_code || '',
                product_name: product.product_title || '',
                pcs_cases: detail?.pcs_cases || '',
                cases_pal: detail?.cases_pal || '',
                cases_lay: detail?.cases_lay || '',
                container_load: detail?.container_load || '',
                cases_20ft_container: detail?.cases_20ft_container || '',
                cases_40ft_container: detail?.cases_40ft_container || '',
                total_shelf_life: detail?.total_shelf_life || '',
                gross_weight_cs_kg: detail?.gross_weight_cs_kg || '',
                net_weight_cs_kg: detail?.net_weight_cs_kg || '',
                cbm: detail?.cbm || '',
                hs_code: detail?.hsCode?.hscode || '',
                rate: detail?.rate || '',
                shipment_title: detail?.shipmentTitle?.name || '',
                commodity: detail?.commodity?.name || '',
            };
        });

        // Add empty row for new entry
        formattedData.push({});

        console.log('Formatted data rows:', formattedData.length);
        tableData.value = formattedData;

        // Wait for HOT to be ready, then load data
        await nextTick();
        
        // Try multiple times to ensure HOT is ready
        const loadData = () => {
            const hotInstance = hotTableComponent.value?.hotInstance;
            if (hotInstance) {
                console.log('Loading data into HOT...');
                hotInstance.loadData(formattedData);
                return true;
            }
            return false;
        };

        // Try immediately
        if (!loadData()) {
            // Try after short delay
            setTimeout(() => {
                if (!loadData()) {
                    // Try one more time
                    setTimeout(loadData, 500);
                }
            }, 300);
        }
    } catch (error) {
        console.error('Failed to fetch product details:', error);
        alert.type = 'danger';
        alert.message = 'Failed to load existing product details.';
    } finally {
        isLoading.value = false;
    }
};

const exportToExcel = () => {
    if (!hotTableComponent.value?.hotInstance) {
        return;
    }

    try {
        const hotInstance = hotTableComponent.value.hotInstance;
        
        // Get all data
        const data = hotInstance.getData();
        const headers = hotInstance.getColHeader();
        
        // Convert to CSV format
        let csv = headers.join(',') + '\n';
        
        data.forEach(row => {
            const csvRow = row.map(cell => {
                if (cell === null || cell === undefined) return '';
                const cellStr = String(cell);
                // Escape commas and quotes
                if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                    return '"' + cellStr.replace(/"/g, '""') + '"';
                }
                return cellStr;
            });
            csv += csvRow.join(',') + '\n';
        });
        
        // Create blob and download
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        const date = new Date().toISOString().split('T')[0];
        
        link.setAttribute('href', url);
        link.setAttribute('download', `ProductDetails_${date}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Export failed:', error);
        Swal.fire({
            title: 'Export Failed',
            text: 'Failed to export data. Please try again.',
            icon: 'error',
            timer: 3000,
        });
    }
};

const saveProductDetails = async () => {
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
        const productDetails = [];

        // Convert table data to product detail format
        for (let i = 0; i < sourceData.length; i++) {
            const row = sourceData[i];
            if (!row) continue;

            const globalCode = row.global_code?.toString().trim() || '';
            if (!globalCode) continue; // Skip if global_code is empty

            // Find product by global_code
            const product = products.value.find(p => p.global_code === globalCode);
            if (!product) {
                continue; // Skip if product not found
            }

            const productDetail = {
                id: row.id || null,
                product_id: product.id,
                pcs_cases: row.pcs_cases?.toString().trim() || null,
                cases_pal: row.cases_pal?.toString().trim() || null,
                cases_lay: row.cases_lay?.toString().trim() || null,
                container_load: row.container_load?.toString().trim() || null,
                cases_20ft_container: row.cases_20ft_container?.toString().trim() || null,
                cases_40ft_container: row.cases_40ft_container?.toString().trim() || null,
                total_shelf_life: row.total_shelf_life?.toString().trim() || null,
                gross_weight_cs_kg: row.gross_weight_cs_kg ? parseFloat(row.gross_weight_cs_kg) : null,
                net_weight_cs_kg: row.net_weight_cs_kg ? parseFloat(row.net_weight_cs_kg) : null,
                cbm: row.cbm ? parseFloat(row.cbm) : null,
                hs_code: row.hs_code?.toString().trim() || null,
                rate: row.rate ? parseFloat(row.rate) : null,
                shipment_title: row.shipment_title?.toString().trim() || null,
                commodity: row.commodity?.toString().trim() || null,
            };

            productDetails.push(productDetail);
        }

        if (productDetails.length === 0) {
            alert.type = 'warning';
            alert.message = 'No product details to save. Please add at least one product detail with Global Code.';
            isSaving.value = false;
            return;
        }

        // Send to backend
        const response = await axios.post('/api/product-details/bulk', {
            product_details: productDetails,
        });

        if (response.data.success) {
            isSaving.value = false;
            const message = `Successfully ${response.data.created > 0 ? `created ${response.data.created} product detail(s)` : ''}${response.data.created > 0 && response.data.updated > 0 ? ' and ' : ''}${response.data.updated > 0 ? `updated ${response.data.updated} product detail(s)` : ''}.`;

            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 5000,
                timerProgressBar: true,
            }).then(() => {
                window.location.reload();
            });
        }
    } catch (error) {
        console.error('Failed to save product details:', error);
        isSaving.value = false;

        if (error.response?.status === 422 && error.response?.data?.errors) {
            const errors = error.response.data.errors;
            const errorRows = Object.keys(errors).map(row => `Row ${row}`).join(', ');
            const errorMessages = Object.values(errors).flat().join('; ');

            Swal.fire({
                title: 'Validation Error!',
                html: `<p><strong>Errors found in rows: ${errorRows}</strong></p><p>${errorMessages}</p>`,
                icon: 'error',
                confirmButtonText: 'OK',
                timer: 10000,
                timerProgressBar: true,
            });
        } else {
            const errorMessage = error.response?.data?.message || 'Failed to save product details. Please check your data.';

            Swal.fire({
                title: 'Failed!',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK',
                timer: 5000,
                timerProgressBar: true,
            }).then(() => {
                window.location.reload();
            });
        }
    }
};

// Watch for HOT instance to be ready
watch(() => hotTableComponent.value?.hotInstance, (hotInstance) => {
    if (hotInstance && tableData.value.length > 0 && !isLoading.value) {
        // HOT is ready and we have data, load it
        setTimeout(() => {
            hotInstance.loadData(tableData.value);
        }, 100);
    }
});

onMounted(async () => {
    await fetchLookups();
    // Wait for HOT to initialize
    await nextTick();
    setTimeout(async () => {
        await fetchProductDetails();
    }, 300);
});
</script>

<style scoped>
.card {
    margin-bottom: 2rem;
}

.handsontable {
    font-size: 0.875rem;
}

.htReadOnly {
    background-color: #f5f5f5;
}
</style>
