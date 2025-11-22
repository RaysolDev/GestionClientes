<template>
  <div class="w-full">
    <div class="mb-4">
      <input
        v-model="searchQuery"
        @input="handleSearch"
        type="text"
        placeholder="Buscar productos..."
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />
    </div>

    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="product in filteredProducts"
        :key="product.id"
        @click="addToCart(product)"
        :class="[
          'bg-white dark:bg-gray-800 rounded-lg shadow p-4 cursor-pointer transition-all hover:shadow-lg border-2',
          product.stock <= product.min_stock
            ? 'border-red-300 dark:border-red-700'
            : 'border-transparent hover:border-blue-300 dark:hover:border-blue-700',
          product.stock === 0 && 'opacity-50 cursor-not-allowed'
        ]"
      >
        <div class="flex items-start justify-between mb-2">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2">
            {{ product.name }}
          </h3>
          <span
            v-if="product.stock <= product.min_stock"
            class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded"
          >
            Stock bajo
          </span>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
          <span v-if="product.code">Código: {{ product.code }}</span>
          <span>Stock: {{ product.stock }}</span>
        </div>

        <div class="flex items-center justify-between mt-3">
          <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
            S/ {{ product.sale_price }}
          </span>
          <button
            @click.stop="addToCart(product)"
            :disabled="product.stock === 0 || !product.is_active"
            :class="[
              'px-3 py-1 text-sm font-medium rounded-md transition-colors',
              product.stock === 0 || !product.is_active
                ? 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                : 'bg-blue-600 text-white hover:bg-blue-700'
            ]"
          >
            Agregar
          </button>
        </div>
      </div>
    </div>

    <div v-if="!loading && filteredProducts.length === 0" class="text-center py-8">
      <p class="text-gray-500 dark:text-gray-400">No se encontraron productos</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['add-to-cart']);

const products = ref([]);
const searchQuery = ref('');
const loading = ref(false);

let searchTimeout = null;

const filteredProducts = computed(() => {
  if (!searchQuery.value.trim()) {
    return products.value.filter(p => p.is_active);
  }
  
  const query = searchQuery.value.toLowerCase();
  return products.value.filter(p => 
    p.is_active && (
      p.name.toLowerCase().includes(query) ||
      (p.code && p.code.toLowerCase().includes(query))
    )
  );
});

const handleSearch = () => {
  // Search is handled by computed property
};

const addToCart = (product) => {
  if (product.stock === 0 || !product.is_active) return;
  emit('add-to-cart', product);
};

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/inventory', {
      params: {
        is_active: true,
        per_page: 100
      }
    });
    products.value = response.data.data || [];
  } catch (error) {
    console.error('Error fetching products:', error);
    products.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchProducts();
});
</script>

