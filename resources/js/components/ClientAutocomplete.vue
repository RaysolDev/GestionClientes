<template>
  <div class="relative">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
      Cliente
    </label>
    <div class="relative">
      <input
        v-model="searchQuery"
        @input="handleInput"
        @focus="showSuggestions = true"
        @blur="handleBlur"
        type="text"
        placeholder="Buscar cliente o crear nuevo..."
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />
      <div v-if="loading" class="absolute right-3 top-2.5">
        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
      </div>
    </div>

    <!-- Suggestions Dropdown -->
    <div
      v-if="showSuggestions && (suggestions.length > 0 || canCreateNew)"
      class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
    >
      <!-- Existing Clients -->
      <div
        v-for="client in suggestions"
        :key="client.id"
        @mousedown="selectClient(client)"
        class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-colors"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ client.name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ client.phone }}</p>
          </div>
          <span v-if="client.total_spent > 0" class="text-xs text-green-600 dark:text-green-400">
            S/ {{ client.total_spent }}
          </span>
        </div>
      </div>

      <!-- Create New Client Option -->
      <div
        v-if="canCreateNew && searchQuery.trim()"
        @mousedown="createNewClient"
        class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 cursor-pointer transition-colors"
      >
        <div class="flex items-center space-x-2">
          <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <p class="text-sm font-medium text-blue-600 dark:text-blue-400">
            Crear nuevo: "{{ searchQuery }}"
          </p>
        </div>
      </div>
    </div>

    <!-- Selected Client Display -->
    <div v-if="selectedClient" class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ selectedClient.name }}</p>
          <p class="text-xs text-blue-700 dark:text-blue-300">{{ selectedClient.phone }}</p>
        </div>
        <button
          @click="clearSelection"
          class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const suggestions = ref([]);
const showSuggestions = ref(false);
const loading = ref(false);
const selectedClient = ref(props.modelValue);

let searchTimeout = null;

const canCreateNew = computed(() => {
  return searchQuery.value.trim().length > 0 && 
         !suggestions.value.some(c => 
           c.name.toLowerCase() === searchQuery.value.toLowerCase()
         );
});

const handleInput = () => {
  clearTimeout(searchTimeout);
  
  if (searchQuery.value.length < 2) {
    suggestions.value = [];
    return;
  }

  searchTimeout = setTimeout(async () => {
    loading.value = true;
    try {
      const response = await axios.get('/api/clients', {
        params: {
          search: searchQuery.value,
          per_page: 5
        }
      });
      suggestions.value = response.data.data || [];
    } catch (error) {
      console.error('Error searching clients:', error);
      suggestions.value = [];
    } finally {
      loading.value = false;
    }
  }, 300);
};

const selectClient = (client) => {
  selectedClient.value = client;
  searchQuery.value = client.name;
  showSuggestions.value = false;
  emit('update:modelValue', client);
};

const createNewClient = () => {
  const name = searchQuery.value.trim();
  if (!name) return;

  // Show modal for creating new client
  const phone = prompt('Ingrese el teléfono del cliente (requerido):');
  if (!phone || !phone.trim()) {
    return;
  }

  const email = prompt('Ingrese el email del cliente (opcional):') || null;

  loading.value = true;
  axios.post('/api/clients', {
    name: name,
    phone: phone.trim(),
    email: email?.trim() || null
  })
  .then(response => {
    const newClient = response.data.data;
    selectedClient.value = newClient;
    searchQuery.value = newClient.name;
    showSuggestions.value = false;
    emit('update:modelValue', newClient);
  })
  .catch(error => {
    alert('Error al crear cliente: ' + (error.response?.data?.message || error.message));
  })
  .finally(() => {
    loading.value = false;
  });
};

const clearSelection = () => {
  selectedClient.value = null;
  searchQuery.value = '';
  suggestions.value = [];
  emit('update:modelValue', null);
};

const handleBlur = () => {
  // Delay to allow click on suggestions
  setTimeout(() => {
    showSuggestions.value = false;
  }, 200);
};

watch(() => props.modelValue, (newValue) => {
  selectedClient.value = newValue;
  if (newValue) {
    searchQuery.value = newValue.name;
  }
});
</script>

