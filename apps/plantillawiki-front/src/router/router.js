/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { createRouter, createWebHistory } from 'vue-router'
import GuestLayout from '../view/layout/guest-layout.vue'
import AdminLayout from '../view/layout/admin-layout.vue'

import Home from '../view/Home.vue'
import AdminDashboard from '../view/AdminDashboard.vue'
import { routerAuth } from '../view/auth/router.js'
import { configurationRouter } from '../view/configuracion/router.js'
import { routerCarrera } from '../view/carrera/router.js'
import { routerEvento } from '../view/evento/router.js'
import { routerProyecto } from '../view/proyecto/router.js'
import { routerWiki } from '../view/wiki/router.js'
import { routerUsers } from '../view/users/router.js'
import { getStoredList } from '../utils/access.js'
import { isPasswordChangeRequired } from '../utils/authSession.js'
const routes = [
  {
    path: '/',
    component: GuestLayout,
    children: [
      {
        path: '',
        name: 'Home',
        component: Home
      },
      {
        path: 'explorar/wiki',
        name: 'PublicWiki',
        component: () => import('../view/public/PublicWiki.vue'),
      },
      {
        path: 'explorar/wiki/:slug',
        name: 'PublicWikiArticle',
        component: () => import('../view/public/PublicWiki.vue'),
      },
      {
        path: 'explorar/eventos',
        name: 'PublicEventos',
        component: () => import('../view/public/PublicEventos.vue'),
      },
      ...routerAuth,
    ],
  },
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: AdminDashboard,
        meta: {
          requiresAuth: true,
          roles: ['admin'],
        }
      },
      ...configurationRouter,
      ...routerCarrera,
      ...routerEvento,
      ...routerProyecto,
      ...routerWiki,
      ...routerUsers
    ],
    meta: {
      requiresAuth: true,
      
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('token');
    const roles = getStoredList('roles');

    // Si es primer login con contraseña temporal generada, obligar a cambiar contraseña en /perfil
    if (isAuthenticated && isPasswordChangeRequired() && to.name !== 'Perfil') {
        return next({ path: '/perfil', query: { firstLogin: '1' } });
    }

    // Rutas protegidas
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!isAuthenticated) {
            return next({ name: 'Home' });
        }

        const requiredRoles = to.matched.flatMap(record => record.meta.roles ?? []);
        if (requiredRoles.length && !requiredRoles.some(role => roles.includes(role))) {
            return next({ path: '/wiki' });
        }

        return next();
    }

    // Rutas solo para invitados (ej. /login)
    if (to.matched.some(record => record.meta.guestOnly)) {
        if (isAuthenticated) {
            return next(roles.includes('admin') ? { name: 'Dashboard' } : { path: '/wiki' });
        }

        return next();
    }

    next();
});

export default router
