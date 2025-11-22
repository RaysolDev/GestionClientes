<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">POS - Punto de Venta</h1>
    </div>

    <!-- Main Layout: Responsive -->
    <div class="space-y-6">
      <!-- Client Selection - Full width on mobile, left column on desktop -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <ClientAutocomplete v-model="selectedClient" />
      </div>

      <!-- Products and Cart - Stacked on mobile, side by side on desktop -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Products List - Full width on mobile, 2 columns on desktop -->
        <div class="lg:col-span-2">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Productos
            </h2>
            <ProductList @add-to-cart="handleAddToCart" />
          </div>
        </div>

        <!-- Cart Summary - Full width on mobile, 1 column on desktop -->
        <div class="lg:col-span-1">
          <CartSummary
            :cart="cart"
            :selected-client="selectedClient"
            @process-sale="handleProcessSale"
            @update-cart="handleUpdateCart"
          />
        </div>
      </div>
    </div>

    <!-- WhatsApp Modal -->
    <WhatsAppModal
      :show="showWhatsAppModal"
      :whatsapp-link="whatsappLink"
      @close="handleCloseModal"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import ClientAutocomplete from '../components/ClientAutocomplete.vue';
import ProductList from '../components/ProductList.vue';
import CartSummary from '../components/CartSummary.vue';
import WhatsAppModal from '../components/WhatsAppModal.vue';

const selectedClient = ref(null);
const cart = ref([]);
const showWhatsAppModal = ref(false);
const whatsappLink = ref('');

const handleAddToCart = (product) => {
  const existingItem = cart.value.find(item => item.id === product.id);
  
  if (existingItem) {
    if (existingItem.quantity < product.stock) {
      existingItem.quantity += 1;
    } else {
      alert(`Stock insuficiente. Disponible: ${product.stock}`);
    }
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      price: product.sale_price,
      quantity: 1,
      stock: product.stock
    });
  }
};

const handleUpdateCart = (updatedCart) => {
  cart.value = updatedCart;
};

const handleProcessSale = async (saleData) => {
  if (!saleData.client) {
    alert('Por favor seleccione un cliente');
    return;
  }

  if (saleData.products.length === 0) {
    alert('El carrito está vacío');
    return;
  }

  try {
    const response = await axios.post('/api/sales', {
      client: saleData.client.id
        ? { id: saleData.client.id }
        : {
            name: saleData.client.name,
            phone: saleData.client.phone,
            email: saleData.client.email || null
          },
      products: saleData.products,
      payment_method: saleData.payment_method
    });

    if (response.data.success) {
      whatsappLink.value = response.data.data.whatsapp_link;
      showWhatsAppModal.value = true;
      
      // Clear cart and client
      cart.value = [];
      selectedClient.value = null;
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Error al procesar la venta';
    alert(errorMessage);
  }
};

const handleCloseModal = () => {
  showWhatsAppModal.value = false;
  whatsappLink.value = '';
};
</script>
