import { createRouter, createWebHistory } from 'vue-router';
import AppLayout from '../components/AppLayout.vue';

// Import views
import POS from '../views/POS.vue';
import Cobros from '../views/Cobros.vue';
import Inventario from '../views/Inventario.vue';
import Campanas from '../views/Campanas.vue';

// Router configuration
const routes = [
    {
        path: '/',
        component: AppLayout,
        children: [
            {
                path: '',
                redirect: '/pos'
            },
            {
                path: 'pos',
                name: 'pos',
                component: POS,
                meta: { title: 'POS - Ventas' }
            },
            {
                path: 'cobros',
                name: 'cobros',
                component: Cobros,
                meta: { title: 'Cobros' }
            },
            {
                path: 'inventario',
                name: 'inventario',
                component: Inventario,
                meta: { title: 'Inventario' }
            },
            {
                path: 'campanas',
                name: 'campanas',
                component: Campanas,
                meta: { title: 'Campañas' }
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;

