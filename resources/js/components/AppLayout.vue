<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Desktop Sidebar -->
    <aside
      class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700"
    >
      <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
        <div class="flex items-center flex-shrink-0 px-4">
          <h1 class="text-xl font-bold text-gray-900 dark:text-white">
            ERP Lite
          </h1>
        </div>
        <nav class="mt-5 flex-1 px-2 space-y-1">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.to"
            :class="[
              isActive(item.to)
                ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white',
              'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors'
            ]"
            active-class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <component
              :is="item.icon"
              :class="[
                isActive(item.to)
                  ? 'text-gray-500 dark:text-gray-300'
                  : 'text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                'mr-3 flex-shrink-0 h-6 w-6'
              ]"
            />
            {{ item.name }}
          </router-link>
        </nav>
        <div class="flex-shrink-0 flex border-t border-gray-200 dark:border-gray-700 p-4">
          <button
            @click="toggleDarkMode"
            class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <SunIcon v-if="isDark" class="mr-3 h-6 w-6" />
            <MoonIcon v-else class="mr-3 h-6 w-6" />
            {{ isDark ? 'Modo Claro' : 'Modo Oscuro' }}
          </button>
        </div>
      </div>
    </aside>

    <!-- Mobile Bottom Navigation -->
    <nav
      class="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-50"
    >
      <div class="flex justify-around items-center h-16">
        <router-link
          v-for="item in navigation"
          :key="item.name"
          :to="item.to"
          :class="[
            isActive(item.to)
              ? 'text-blue-600 dark:text-blue-400'
              : 'text-gray-600 dark:text-gray-400',
            'flex flex-col items-center justify-center flex-1 min-w-0'
          ]"
          active-class="text-blue-600 dark:text-blue-400"
        >
          <component
            :is="item.icon"
            class="h-6 w-6 mb-1"
          />
          <span class="text-xs truncate">{{ item.name }}</span>
        </router-link>
        <button
          @click="toggleDarkMode"
          :class="[
            'flex flex-col items-center justify-center flex-1 min-w-0',
            'text-gray-600 dark:text-gray-400'
          ]"
        >
          <SunIcon v-if="isDark" class="h-6 w-6 mb-1" />
          <MoonIcon v-else class="h-6 w-6 mb-1" />
          <span class="text-xs truncate">Tema</span>
        </button>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="lg:pl-64 pb-16 lg:pb-0">
      <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';

// Icons (using simple SVG components)
const ShoppingCartIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
  `
};

const CurrencyDollarIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  `
};

const CubeIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
    </svg>
  `
};

const MegaphoneIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
    </svg>
  `
};

const SunIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
  `
};

const MoonIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
  `
};

const route = useRoute();
const isDark = ref(false);

const navigation = [
  { name: 'POS', to: '/pos', icon: ShoppingCartIcon },
  { name: 'Cobros', to: '/cobros', icon: CurrencyDollarIcon },
  { name: 'Inventario', to: '/inventario', icon: CubeIcon },
  { name: 'Campañas', to: '/campanas', icon: MegaphoneIcon },
];

const isActive = (path) => {
  return route.path === path;
};

const toggleDarkMode = () => {
  isDark.value = !isDark.value;
  const html = document.documentElement;
  
  if (isDark.value) {
    html.classList.add('dark');
    localStorage.setItem('darkMode', 'true');
  } else {
    html.classList.remove('dark');
    localStorage.setItem('darkMode', 'false');
  }
};

onMounted(() => {
  // Load dark mode preference from localStorage
  const darkModePreference = localStorage.getItem('darkMode');
  if (darkModePreference === 'true' || (!darkModePreference && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true;
    document.documentElement.classList.add('dark');
  } else {
    isDark.value = false;
    document.documentElement.classList.remove('dark');
  }
});
</script>

