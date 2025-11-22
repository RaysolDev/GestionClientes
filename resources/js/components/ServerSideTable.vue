<template>
  <div class="w-full">
    <!-- Search Bar -->
    <div class="mb-4">
      <div class="relative">
        <input
          v-model="searchQuery"
          @input="debouncedSearch"
          type="text"
          placeholder="Buscar..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
      <p class="text-red-800 dark:text-red-200">{{ error }}</p>
    </div>
    <template v-else>
      <!-- Desktop Table View (md and up) -->
      <div class="hidden md:block overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800 rounded-lg shadow">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              @click="sortBy(column.key)"
              :class="[
                'px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors',
                sortColumn === column.key && 'bg-gray-100 dark:bg-gray-600'
              ]"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <span v-if="sortColumn === column.key" class="text-blue-600 dark:text-blue-400">
                  <svg v-if="sortDirection === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="(row, index) in data"
            :key="index"
            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="getNestedValue(row, column.key)">
                {{ getNestedValue(row, column.key) }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <!-- Mobile Card View (below md) -->
      <div class="md:hidden space-y-4">
      <div
        v-for="(row, index) in data"
        :key="index"
        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700"
      >
        <slot name="card" :row="row">
          <div class="space-y-2">
            <div
              v-for="column in columns"
              :key="column.key"
              class="flex justify-between items-start"
            >
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                {{ column.label }}:
              </span>
              <span class="text-sm text-gray-900 dark:text-gray-100 text-right flex-1 ml-4">
                <slot :name="`cell-${column.key}`" :row="row" :value="getNestedValue(row, column.key)">
                  {{ getNestedValue(row, column.key) }}
                </slot>
              </span>
            </div>
          </div>
        </slot>
      </div>
      </div>
    </template>

    <!-- Empty State -->
    <div v-if="!loading && !error && data.length === 0" class="text-center py-12">
      <p class="text-gray-500 dark:text-gray-400">No se encontraron registros</p>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && !error && pagination" class="mt-4 flex flex-col sm:flex-row items-center justify-between space-y-2 sm:space-y-0">
      <div class="text-sm text-gray-700 dark:text-gray-300">
        Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total || 0 }} resultados
      </div>
      <div class="flex items-center space-x-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-md transition-colors',
            pagination.current_page === 1
              ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
          ]"
        >
          Anterior
        </button>
        
        <div class="flex space-x-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-md transition-colors',
              page === pagination.current_page
                ? 'bg-blue-600 text-white'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </button>
        </div>

        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-md transition-colors',
            pagination.current_page === pagination.last_page
              ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
          ]"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  url: {
    type: String,
    required: true
  },
  columns: {
    type: Array,
    required: true,
    validator: (value) => {
      return value.every(col => col.key && col.label);
    }
  },
  searchParam: {
    type: String,
    default: 'search'
  },
  sortParam: {
    type: String,
    default: 'sort'
  },
  orderParam: {
    type: String,
    default: 'order'
  },
  pageParam: {
    type: String,
    default: 'page'
  },
  perPageParam: {
    type: String,
    default: 'per_page'
  },
  perPage: {
    type: Number,
    default: 15
  },
  initialSort: {
    type: String,
    default: null
  },
  initialOrder: {
    type: String,
    default: 'asc'
  },
  additionalParams: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['data-loaded', 'error']);

const data = ref([]);
const loading = ref(false);
const error = ref(null);
const searchQuery = ref('');
const sortColumn = ref(props.initialSort);
const sortDirection = ref(props.initialOrder);
const pagination = ref(null);
const currentPage = ref(1);

// Debounce search
let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchData();
  }, 500);
};

// Get nested value from object (e.g., 'user.name')
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, prop) => current?.[prop], obj) ?? '';
};

// Sort by column
const sortBy = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  currentPage.value = 1;
  fetchData();
};

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= (pagination.value?.last_page || 1)) {
    currentPage.value = page;
    fetchData();
  }
};

// Calculate visible pages for pagination
const visiblePages = computed(() => {
  if (!pagination.value) return [];
  
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const delta = 2;
  const range = [];
  const rangeWithDots = [];

  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i);
  }

  if (current - delta > 2) {
    rangeWithDots.push(1, '...');
  } else {
    rangeWithDots.push(1);
  }

  rangeWithDots.push(...range);

  if (current + delta < last - 1) {
    rangeWithDots.push('...', last);
  } else if (last > 1) {
    rangeWithDots.push(last);
  }

  return rangeWithDots.filter((page, index, array) => {
    return page !== '...' || array[index - 1] !== '...';
  });
});

// Fetch data from API
const fetchData = async () => {
  loading.value = true;
  error.value = null;

  try {
    const params = {
      [props.pageParam]: currentPage.value,
      [props.perPageParam]: props.perPage,
      ...props.additionalParams
    };

    if (searchQuery.value) {
      params[props.searchParam] = searchQuery.value;
    }

    if (sortColumn.value) {
      params[props.sortParam] = sortColumn.value;
      params[props.orderParam] = sortDirection.value;
    }

    const response = await axios.get(props.url, { params });

    // Handle Laravel pagination response
    if (response.data.data) {
      data.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to
      };
    } else {
      // Handle non-paginated response
      data.value = Array.isArray(response.data) ? response.data : [];
      pagination.value = null;
    }

    emit('data-loaded', data.value);
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar los datos';
    emit('error', err);
    data.value = [];
    pagination.value = null;
  } finally {
    loading.value = false;
  }
};

// Watch for additional params changes
watch(() => props.additionalParams, () => {
  currentPage.value = 1;
  fetchData();
}, { deep: true });

// Initial fetch
onMounted(() => {
  fetchData();
});

// Expose methods for parent components
defineExpose({
  fetchData
});
</script>

