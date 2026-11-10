<template>
  <div v-if="isLoginPage" class="login-viewport">
    <router-view />
  </div>

  <template v-else>
    <AppBar
      :name-button="pageTitle"
      @toggle-drawer="toggleDrawer"
    />

    <MenuLateral
      v-model:drawer="drawer"
      :permanent="!mobile"
      :rail="!mobile && rail"
      @navigate="closeMobileDrawer"
    />

    <v-main class="app-shell__main">
      <v-container fluid class="app-shell__content">
        <router-view />
      </v-container>
    </v-main>

    <v-footer
      v-if="showFooter"
      class="app-shell__footer"
      color="surface"
    >
      <div class="app-shell__footer-inner">
        <span>
          &copy; 2026 <strong>Wiki TDA</strong> · Creador: <strong>Irwin Jose Maria Esperanza Guardado</strong>
        </span>
        <span class="app-shell__footer-sep d-none d-sm-inline">·</span>
        <a
          href="https://creativecommons.org/licenses/by-nc-sa/4.0/deed.es"
          target="_blank"
          rel="noopener noreferrer"
          class="app-shell__footer-badge"
          title="Prohibido patentar o registrar como software propio"
        >
          <v-icon icon="mdi-creative-commons" size="13" class="mr-1" />
          CC BY-NC-SA 4.0 · No patentable
        </a>
      </div>
    </v-footer>
  </template>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import { useRoute } from 'vue-router';
import AppBar from './AppBar.vue';
import MenuLateral from './MenuLateral.vue';

defineProps({
  showFooter: {
    type: Boolean,
    default: false,
  },
});

const route = useRoute();
const { mobile } = useDisplay();
const drawer = ref(!mobile.value);
const rail = ref(false);
const isLoginPage = computed(() => route.name === 'Login');

const pageTitle = computed(() => {
  const titles = {
    Home: 'Inicio',
    PublicWiki: 'Wiki & Tutoriales',
    PublicWikiArticle: 'Lectura de Wiki & Tutoriales',
    PublicEventos: 'Eventos & Proyectos',
    Dashboard: 'Dashboard Administrativo',
    '/carrera': 'Gestión de Carreras',
    '/evento': 'Gestión de Eventos y Materias',
    '/proyecto': 'Gestión de Proyectos',
    '/wiki': 'Gestión de Wiki y Tutoriales',
    Usuarios: 'Administración de Usuarios',
    Perfil: 'Mi Perfil de Usuario',
    Configuracion: 'Configuración del Sistema',
    Login: 'Iniciar sesión',
  };

  return titles[route.name] ?? 'Wiki TDA';
});

const toggleDrawer = () => {
  if (mobile.value) {
    drawer.value = !drawer.value;
    return;
  }

  rail.value = !rail.value;
};

const closeMobileDrawer = () => {
  if (mobile.value) {
    drawer.value = false;
  }
};

watch(mobile, (isMobile) => {
  drawer.value = !isMobile;
  rail.value = false;
});
</script>

<style scoped>
.login-viewport {
  min-height: 100vh;
  width: 100%;
}

.app-shell__main {
  background: rgb(var(--v-theme-background));
}

.app-shell__content {
  padding: 28px;
}

.app-shell__footer {
  padding: 8px 16px !important;
  min-height: 36px !important;
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  color: rgb(var(--v-theme-on-surface-variant));
}

.app-shell__footer-inner {
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 4px 8px;
  font-size: 0.72rem;
  line-height: 1.3;
  opacity: 0.85;
}

.app-shell__footer-badge {
  display: inline-flex;
  align-items: center;
  padding: 1px 7px;
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));
  font-weight: 600;
  font-size: 0.68rem;
  text-decoration: none;
  transition: opacity 0.15s ease;
}

.app-shell__footer-badge:hover {
  opacity: 1;
  text-decoration: underline;
}

@media (max-width: 960px) {
  .app-shell__content {
    padding: 18px;
  }
}

@media (max-width: 600px) {
  .app-shell__content {
    padding: 12px;
  }
}
</style>
