<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Campañas de Marketing</h1>
    </div>

    <!-- Quick Filter Buttons -->
    <div class="flex flex-wrap gap-3">
      <button
        @click="setFilter('inactive_30d')"
        :class="[
          'px-4 py-2 rounded-lg font-medium text-sm transition-colors',
          activeFilter === 'inactive_30d'
            ? 'bg-red-600 text-white'
            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
        ]"
      >
        Clientes Perdidos (&gt;30 días)
      </button>
      <button
        @click="setFilter('recent_7d')"
        :class="[
          'px-4 py-2 rounded-lg font-medium text-sm transition-colors',
          activeFilter === 'recent_7d'
            ? 'bg-green-600 text-white'
            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
        ]"
      >
        Clientes Nuevos
      </button>
      <button
        @click="setFilter('vip')"
        :class="[
          'px-4 py-2 rounded-lg font-medium text-sm transition-colors',
          activeFilter === 'vip'
            ? 'bg-yellow-600 text-white'
            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
        ]"
      >
        VIP
      </button>
      <button
        @click="clearFilter"
        :class="[
          'px-4 py-2 rounded-lg font-medium text-sm transition-colors',
          activeFilter === null
            ? 'bg-gray-600 text-white'
            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
        ]"
      >
        Todos
      </button>
    </div>

    <!-- ServerSideTable -->
    <ServerSideTable
      :url="'/api/clients'"
      :columns="columns"
      :additional-params="filterParams"
      ref="tableRef"
    >
      <!-- Custom cell for email -->
      <template #cell-email="{ value }">
        {{ value || '-' }}
      </template>

      <!-- Custom cell for total_spent -->
      <template #cell-total_spent="{ value }">
        <span class="font-semibold text-green-600 dark:text-green-400">
          S/ {{ parseFloat(value || 0).toFixed(2) }}
        </span>
      </template>

      <!-- Custom cell for last_purchase_date -->
      <template #cell-last_purchase_date="{ value }">
        {{ formatDate(value) }}
      </template>

      <!-- Custom cell for WhatsApp actions in desktop view -->
      <template #cell-actions="{ row }">
        <div class="flex items-center space-x-2 min-w-[300px]">
          <input
            v-model="messages[row.id]"
            @keyup.enter="sendWhatsApp(row)"
            type="text"
            placeholder="Escribe un mensaje..."
            class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <button
            @click="sendWhatsApp(row)"
            :disabled="!messages[row.id] || !messages[row.id].trim()"
            :class="[
              'px-4 py-1.5 text-sm font-medium rounded-md transition-colors flex items-center space-x-1 whitespace-nowrap',
              messages[row.id] && messages[row.id].trim()
                ? 'bg-green-600 text-white hover:bg-green-700'
                : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
            ]"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            <span>Enviar</span>
          </button>
        </div>
      </template>

      <!-- Custom card view for mobile with WhatsApp input -->
      <template #card="{ row }">
        <div class="space-y-3">
          <div class="space-y-2">
            <div class="flex justify-between items-start">
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                Nombre:
              </span>
              <span class="text-sm text-gray-900 dark:text-gray-100 text-right flex-1 ml-4">
                {{ row.name }}
              </span>
            </div>
            <div class="flex justify-between items-start">
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                Teléfono:
              </span>
              <span class="text-sm text-gray-900 dark:text-gray-100 text-right flex-1 ml-4">
                {{ row.phone }}
              </span>
            </div>
            <div class="flex justify-between items-start">
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                Total Gastado:
              </span>
              <span class="text-sm font-semibold text-green-600 dark:text-green-400 text-right flex-1 ml-4">
                S/ {{ parseFloat(row.total_spent || 0).toFixed(2) }}
              </span>
            </div>
            <div v-if="row.last_purchase_date" class="flex justify-between items-start">
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                Última Compra:
              </span>
              <span class="text-sm text-gray-900 dark:text-gray-100 text-right flex-1 ml-4">
                {{ formatDate(row.last_purchase_date) }}
              </span>
            </div>
          </div>
          
          <!-- WhatsApp Input for Mobile -->
          <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-2">
              <input
                v-model="messages[row.id]"
                @keyup.enter="sendWhatsApp(row)"
                type="text"
                placeholder="Escribe un mensaje..."
                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <button
                @click="sendWhatsApp(row)"
                :disabled="!messages[row.id] || !messages[row.id].trim()"
                :class="[
                  'px-4 py-2 text-sm font-medium rounded-md transition-colors flex items-center space-x-1',
                  messages[row.id] && messages[row.id].trim()
                    ? 'bg-green-600 text-white hover:bg-green-700'
                    : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                ]"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                <span>Enviar</span>
              </button>
            </div>
          </div>
        </div>
      </template>
    </ServerSideTable>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ServerSideTable from '../components/ServerSideTable.vue';

const tableRef = ref(null);
const activeFilter = ref(null);
const messages = ref({});

const columns = [
  { key: 'name', label: 'Nombre' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'total_spent', label: 'Total Gastado' },
  { key: 'last_purchase_date', label: 'Última Compra' },
  { key: 'actions', label: 'Acciones' }
];

const filterParams = computed(() => {
  const params = {};
  if (activeFilter.value === 'inactive_30d') {
    params.inactive_30d = true;
  } else if (activeFilter.value === 'recent_7d') {
    params.recent_7d = true;
  } else if (activeFilter.value === 'vip') {
    params.vip = true;
  }
  return params;
});

const setFilter = (filter) => {
  activeFilter.value = filter;
  // Table will automatically refresh when filterParams changes due to watch in ServerSideTable
};

const clearFilter = () => {
  activeFilter.value = null;
  // Table will automatically refresh when filterParams changes due to watch in ServerSideTable
};

const sendWhatsApp = (client) => {
  const message = messages.value[client.id];
  if (!message || !message.trim()) {
    return;
  }

  // Format phone number
  let phone = client.phone.replace(/[^0-9]/g, '');
  if (!phone.startsWith('51')) {
    phone = '51' + phone;
  }

  // Create WhatsApp link
  const whatsappLink = `whatsapp://send/?phone=${phone}&text=${encodeURIComponent(message.trim())}`;
  
  // Open WhatsApp
  window.location.href = whatsappLink;
  
  // Clear message after sending
  messages.value[client.id] = '';
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-PE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>
