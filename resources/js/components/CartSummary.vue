<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
      Resumen de Venta
    </h2>

    <!-- Cart Items -->
    <div v-if="cart.length > 0" class="space-y-3 mb-4 max-h-64 overflow-y-auto">
      <div
        v-for="(item, index) in cart"
        :key="index"
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
      >
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900 dark:text-white">
            {{ item.name }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            S/ {{ item.price }} x {{ item.quantity }}
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <button
            @click="decreaseQuantity(index)"
            class="w-8 h-8 flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
          </button>
          <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">
            {{ item.quantity }}
          </span>
          <button
            @click="increaseQuantity(index)"
            :disabled="item.quantity >= item.stock"
            :class="[
              'w-8 h-8 flex items-center justify-center rounded-md transition-colors',
              item.quantity >= item.stock
                ? 'bg-gray-200 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                : 'bg-blue-600 text-white hover:bg-blue-700'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>
          <button
            @click="removeItem(index)"
            class="ml-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
      <p>El carrito está vacío</p>
    </div>

    <!-- Total -->
    <div v-if="cart.length > 0" class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
      <div class="flex justify-between items-center mb-4">
        <span class="text-lg font-semibold text-gray-900 dark:text-white">Total:</span>
        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
          S/ {{ total.toFixed(2) }}
        </span>
      </div>

      <!-- Payment Method -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Método de Pago
        </label>
        <select
          v-model="paymentMethod"
          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="CASH">Efectivo</option>
          <option value="CARD">Tarjeta</option>
          <option value="YAPE">Yape</option>
          <option value="PLIN">Plin</option>
          <option value="CREDIT">Crédito</option>
        </select>
      </div>

      <!-- Process Button -->
      <button
        @click="processSale"
        :disabled="!canProcess"
        :class="[
          'w-full py-3 px-4 rounded-lg font-semibold text-white transition-colors',
          canProcess
            ? 'bg-blue-600 hover:bg-blue-700'
            : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed'
        ]"
      >
        {{ paymentMethod === 'CREDIT' ? 'Registrar Venta' : 'Pagar' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  cart: {
    type: Array,
    required: true
  },
  selectedClient: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['process-sale', 'update-cart']);

const paymentMethod = ref('CASH');

const total = computed(() => {
  return props.cart.reduce((sum, item) => {
    return sum + (item.price * item.quantity);
  }, 0);
});

const canProcess = computed(() => {
  return props.cart.length > 0 && props.selectedClient !== null;
});

const increaseQuantity = (index) => {
  const item = props.cart[index];
  if (item.quantity < item.stock) {
    const updatedCart = [...props.cart];
    updatedCart[index].quantity += 1;
    emit('update-cart', updatedCart);
  }
};

const decreaseQuantity = (index) => {
  const updatedCart = [...props.cart];
  if (updatedCart[index].quantity > 1) {
    updatedCart[index].quantity -= 1;
  } else {
    updatedCart.splice(index, 1);
  }
  emit('update-cart', updatedCart);
};

const removeItem = (index) => {
  const updatedCart = [...props.cart];
  updatedCart.splice(index, 1);
  emit('update-cart', updatedCart);
};

const processSale = () => {
  if (!canProcess.value) return;
  
  emit('process-sale', {
    client: props.selectedClient,
    products: props.cart.map(item => ({
      id: item.id,
      quantity: item.quantity
    })),
    payment_method: paymentMethod.value,
    total: total.value
  });
};
</script>

