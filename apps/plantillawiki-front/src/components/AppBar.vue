<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <v-app-bar color="surface" elevation="0" class="app-bar">
    <v-app-bar-nav-icon
      color="primary"
      aria-label="Alternar menú"
      @click="emit('toggle-drawer')"
    >
      <v-icon icon="mdi-menu" />
    </v-app-bar-nav-icon>

    <v-breadcrumbs :items="breadcrumbs" class="app-bar__breadcrumbs d-none d-sm-flex">
      <template #divider>
        <v-icon icon="mdi-chevron-right" size="small" />
      </template>
      <template #item="{ item }">
        <v-breadcrumbs-item
          :to="item.to"
          :disabled="item.disabled"
          :class="{ 'app-bar__current-page': item.disabled }"
        >
          {{ item.title }}
        </v-breadcrumbs-item>
      </template>
    </v-breadcrumbs>

    <v-spacer />

    <!-- Navegación rápida pública siempre accesible -->
    <div class="d-none d-lg-flex align-center ga-1 mr-3">
      <v-btn
        to="/explorar/wiki"
        variant="text"
        size="small"
        color="primary"
        prepend-icon="mdi-book-open-page-variant-outline"
      >
        Wiki & Tutoriales
      </v-btn>
      <v-btn
        to="/explorar/eventos"
        variant="text"
        size="small"
        color="primary"
        prepend-icon="mdi-trophy-outline"
      >
        Eventos & Proyectos
      </v-btn>
    </div>

    <!-- Botón de Búsqueda Global Unificada (Ctrl + K) -->
    <v-btn
      icon="mdi-magnify"
      variant="text"
      color="secondary"
      class="d-inline-flex d-sm-none mr-1"
      aria-label="Buscar en todo el sistema"
      @click="openSpotlight"
    />
    <v-btn
      variant="outlined"
      color="secondary"
      size="small"
      prepend-icon="mdi-magnify"
      class="app-bar__search-trigger d-none d-sm-inline-flex mr-2 mr-md-3 text-none"
      @click="openSpotlight"
    >
      <span class="mr-2">Buscar...</span>
      <v-chip size="x-small" variant="tonal" color="primary" class="d-none d-md-inline-flex">
        Ctrl + K
      </v-chip>
    </v-btn>

    <v-chip
      variant="outlined"
      color="secondary"
      prepend-icon="mdi-calendar"
      class="app-bar__organization d-none d-md-inline-flex"
    >
      FESOL 2026
    </v-chip>

    <ThemeToggle />

    <v-menu v-if="isAuthenticated" location="bottom end">
      <template #activator="{ props: menuProps }">
        <v-btn
          v-bind="menuProps"
          icon
          variant="text"
          aria-label="Abrir menú de usuario"
          class="app-bar__profile-button"
        >
          <v-avatar color="secondary" size="38">
            <v-img v-if="userAvatar" :src="userAvatar" alt="Foto de perfil" cover />
            <span v-else class="app-bar__initials">{{ userInitials }}</span>
          </v-avatar>
        </v-btn>
      </template>

      <v-list min-width="240" class="app-bar__profile-menu">
        <v-list-item
          :title="userName"
          :subtitle="userEmail"
          prepend-icon="mdi-account-circle-outline"
        />
        <v-divider class="my-1" />
        <v-list-item
          title="Mi Perfil y Contraseña"
          subtitle="Actualizar datos personales"
          prepend-icon="mdi-account-cog-outline"
          to="/perfil"
        />
        <v-divider class="my-1" />
        <v-list-item
          title="Cerrar sesión"
          prepend-icon="mdi-logout"
          :disabled="isLoggingOut"
          @click="cerrarSesion"
        />
      </v-list>
    </v-menu>

    <v-btn
      v-else
      to="/login"
      color="primary"
      variant="flat"
      prepend-icon="mdi-login"
      class="app-bar__action"
    >
      Iniciar sesión
    </v-btn>
  </v-app-bar>

  <!-- Modal Spotlight de Búsqueda Global Unificada (Wiki + Proyectos + Eventos/Carreras) -->
  <v-dialog v-model="spotlightOpen" max-width="720" scrollable>
    <v-card rounded="xl" class="overflow-hidden">
      <div class="pa-4 bg-primary text-white">
        <v-text-field
          v-model="spotlightQuery"
          autofocus
          placeholder="Escribe una tecnología (#laravel, #docker), título de tutorial, proyecto, carrera o estudiante..."
          prepend-inner-icon="mdi-magnify"
          variant="solo"
          bg-color="surface"
          density="comfortable"
          rounded="lg"
          hide-details
          clearable
        />
        <div class="d-flex flex-wrap align-center justify-space-between mt-3 text-caption">
          <div class="d-flex flex-wrap align-center ga-1">
            <span class="opacity-80 mr-1">Filtros rápidos:</span>
            <v-chip
              v-for="quickTag in ['laravel', 'vue3', 'docker', 'git', 'hospital', 'ING-SIS']"
              :key="quickTag"
              size="x-small"
              color="accent"
              variant="tonal"
              class="text-white"
              @click="spotlightQuery = quickTag"
            >
              #{{ quickTag }}
            </v-chip>
          </div>
          <span class="opacity-75">Presiona ESC para cerrar</span>
        </div>
      </div>

      <v-card-text class="pa-4" style="max-height: 460px;">
        <!-- Resultados de Tutoriales y Artículos Wiki -->
        <div class="mb-4">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-bold text-primary text-uppercase">
              Tutoriales y Artículos Wiki ({{ matchingWiki.length }})
            </span>
            <v-btn
              size="x-small"
              variant="text"
              color="primary"
              @click="irARuta('/explorar/wiki', { q: spotlightQuery })"
            >
              Ver todos en Wiki
            </v-btn>
          </div>

          <v-list v-if="matchingWiki.length" density="compact" class="border rounded-lg pa-0">
            <v-list-item
              v-for="art in matchingWiki"
              :key="art.slug"
              :title="art.title"
              :subtitle="`${art.type} · Categoría: ${art.category} · Por ${art.author}`"
              @click="irARuta(`/explorar/wiki/${art.slug}`)"
            >
              <template #prepend>
                <v-icon :icon="art.type === 'Tutorial' ? 'mdi-compass-outline' : 'mdi-file-document-outline'" color="primary" />
              </template>
              <template #append>
                <v-icon icon="mdi-arrow-top-right" size="small" />
              </template>
            </v-list-item>
          </v-list>
          <div v-else class="text-caption text-medium-emphasis pa-2">
            Sin coincidencias en tutoriales o artículos.
          </div>
        </div>

        <!-- Resultados de Proyectos Estudiantiles y Eventos -->
        <div>
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-bold text-secondary text-uppercase">
              Proyectos Estudiantiles, Carreras y Eventos ({{ matchingProjects.length }})
            </span>
            <v-btn
              size="x-small"
              variant="text"
              color="secondary"
              @click="irARuta('/explorar/eventos', { q: spotlightQuery })"
            >
              Abrir Explorador de Eventos
            </v-btn>
          </div>

          <v-list v-if="matchingProjects.length" density="compact" class="border rounded-lg pa-0">
            <v-list-item
              v-for="proj in matchingProjects"
              :key="proj.id"
              :title="proj.title"
              :subtitle="`${proj.event} · ${proj.carrera} · Materia: ${proj.subject}`"
              @click="irARuta('/explorar/eventos', { q: proj.title })"
            >
              <template #prepend>
                <v-icon icon="mdi-folder-star-outline" color="secondary" />
              </template>
              <template #append>
                <v-chip size="x-small" color="primary" variant="tonal">{{ proj.carreraCode }}</v-chip>
              </template>
            </v-list-item>
          </v-list>
          <div v-else class="text-caption text-medium-emphasis pa-2">
            Sin coincidencias en proyectos estudiantiles.
          </div>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useLoginStore } from '../store/auth/useLoginStore';
import { useAlertStore } from '../store/useAlertStore';
import { obtenerArticulos } from '../services/wiki/wiki.service.js';
import { obtenerProyectos } from '../services/proyecto/proyecto.service.js';
import ThemeToggle from './ThemeToggle.vue';

const router = useRouter();
const authStore = useLoginStore();
const alertStore = useAlertStore();

const props = defineProps({
  nameButton: {
    default: 'Inicio',
    type: String,
  },
});
const emit = defineEmits(['toggle-drawer']);
const isAuthenticated = ref(Boolean(localStorage.getItem('token')));
const user = ref(readStoredUser());
const isLoggingOut = ref(false);

const spotlightOpen = ref(false);
const spotlightQuery = ref('');

const spotlightWikiItems = ref([
  {
    slug: 'guia-apis-restful-laravel-12-jwt-spatie',
    type: 'Tutorial',
    title: 'Guía paso a paso: Configuración de APIs RESTful en Laravel 12 con JWT y Spatie',
    category: 'Desarrollo Backend',
    author: 'Ing. Carlos Méndez',
    tags: ['laravel', 'seguridad', 'docker'],
  },
  {
    slug: 'arquitectura-modular-vue3-vuetify4-pinia',
    type: 'Artículo',
    title: 'Arquitectura Modular en Vue 3 con Vuetify 4, Pinia y Axios Interceptors',
    category: 'Frontend Moderno',
    author: 'Dra. Elena Ríos',
    tags: ['vue3', 'vuetify'],
  },
  {
    slug: 'diseno-esquemas-relacionales-migraciones-postgresql',
    type: 'Tutorial',
    title: 'Diseño de Esquemas Relacionales y Migraciones Seguras en PostgreSQL / SQLite',
    category: 'Base de Datos y SQL',
    author: 'MSc. Roberto Morales',
    tags: ['postgresql', 'laravel'],
  },
  {
    slug: 'empaquetado-proyectos-normativa-git-fesol',
    type: 'Tutorial',
    title: 'Empaquetado de Proyectos Estudiantiles y Normativa de Repositorios Git para FESOL',
    category: 'Estándares de Proyectos',
    author: 'Ing. Carlos Méndez',
    tags: ['git', 'docker'],
  },
]);

const spotlightProjectItems = ref([
  {
    id: 1001,
    title: 'Sistema de Gestión Hospitalaria OpenSource con Telemedicina',
    event: 'FESOL Ciclo I - 2026',
    carrera: 'Ingeniería en Sistemas',
    carreraCode: 'ING-SIS',
    subject: 'Ingeniería de Software II',
    keywords: 'laravel vue3 docker hospital telemedicina ana sofia diego',
  },
  {
    id: 1002,
    title: 'Plataforma de Trazabilidad Agrícola con Sensores IoT',
    event: 'FESOL Ciclo I - 2026',
    carrera: 'Ingeniería en Sistemas',
    carreraCode: 'ING-SIS',
    subject: 'Ingeniería de Software II',
    keywords: 'iot esp32 laravel vue3 maria fernanda',
  },
  {
    id: 1003,
    title: 'Repositorio Académico y Wiki Colaborativa Universitaria',
    event: 'FESOL Ciclo I - 2026',
    carrera: 'Ingeniería de Software',
    carreraCode: 'ING-SW',
    subject: 'Desarrollo de Aplicaciones Web',
    keywords: 'vue3 laravel postgresql git gabriel valeria',
  },
  {
    id: 1004,
    title: 'Laboratorio Automatizado de Detección de Intrusos con Suricata y Wazuh',
    event: 'FESOL Ciclo I - 2026',
    carrera: 'Redes y Telecomunicaciones',
    carreraCode: 'TEC-RED',
    subject: 'Seguridad en Redes y Servidores Linux',
    keywords: 'docker seguridad linux cristian',
  },
]);

const matchingWiki = computed(() => {
  const q = spotlightQuery.value?.trim().toLowerCase();
  if (!q) return spotlightWikiItems.value.slice(0, 4);
  return spotlightWikiItems.value.filter(
    (w) =>
      w.title.toLowerCase().includes(q) ||
      w.category.toLowerCase().includes(q) ||
      w.author.toLowerCase().includes(q) ||
      w.tags.some((t) => t.toLowerCase().includes(q))
  );
});

const matchingProjects = computed(() => {
  const q = spotlightQuery.value?.trim().toLowerCase();
  if (!q) return spotlightProjectItems.value.slice(0, 4);
  return spotlightProjectItems.value.filter(
    (p) =>
      p.title.toLowerCase().includes(q) ||
      p.event.toLowerCase().includes(q) ||
      p.carrera.toLowerCase().includes(q) ||
      p.carreraCode.toLowerCase().includes(q) ||
      p.subject.toLowerCase().includes(q) ||
      p.keywords.toLowerCase().includes(q)
  );
});

const breadcrumbs = computed(() => {
  if (props.nameButton === 'Inicio') {
    return [{ title: 'Inicio', disabled: true }];
  }

  return [
    { title: 'Inicio', to: '/' },
    { title: props.nameButton, disabled: true },
  ];
});

const userName = computed(() => user.value?.name || 'Usuario');
const userEmail = computed(() => user.value?.email || '');
const userAvatar = computed(() => user.value?.avatar_url || null);
const userInitials = computed(() => {
  const initials = userName.value
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('');

  return initials.toUpperCase() || 'U';
});

function readStoredUser() {
  const storedUser = localStorage.getItem('user');

  if (!storedUser) {
    return null;
  }

  try {
    return JSON.parse(storedUser);
  } catch (error) {
    console.error('No se pudo leer el usuario de la sesión.', error);
    return null;
  }
}

const openSpotlight = async () => {
  spotlightOpen.value = true;
  try {
    const [artRes, projRes] = await Promise.all([
      obtenerArticulos({ is_published: true }),
      obtenerProyectos(),
    ]);
    if (artRes.data?.data?.length) {
      spotlightWikiItems.value = artRes.data.data.map((a) => ({
        slug: a.slug,
        type: a.type === 'tutorial' || a.type === 'Tutorial' ? 'Tutorial' : 'Artículo',
        title: a.title,
        category: a.category?.name || 'General',
        author: a.author?.name || 'Autor',
        tags: (a.tags || []).map((t) => t.name),
      }));
    }
    if (projRes.data?.data?.length) {
      spotlightProjectItems.value = projRes.data.data.map((p) => ({
        id: p.id,
        title: p.title,
        event: p.event_subject?.event?.cicle_name || 'Evento',
        carrera: p.event_subject?.carrera?.name || 'Carrera',
        carreraCode: p.event_subject?.carrera?.code || 'CAR',
        subject: p.event_subject?.subject_name || 'Materia',
        keywords: `${p.description || ''} ${(p.members || []).map((m) => m.student?.name || '').join(' ')}`,
      }));
    }
  } catch (e) {
    // Usa catálogo local si el backend está apagado
  }
};

const irARuta = (path, query = {}) => {
  spotlightOpen.value = false;
  router.push({ path, query });
};

const handleKeydown = (event) => {
  if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault();
    openSpotlight();
  }
};

const cerrarSesion = async () => {
  if (isLoggingOut.value) {
    return;
  }

  isLoggingOut.value = true;
  try {
    await authStore.logout();
  } catch (error) {
    console.error('No se pudo invalidar la sesión en el servidor.', error);
    alertStore.showAlert(
      'La sesión se cerró en este dispositivo, pero no se pudo confirmar con el servidor.',
      'warning'
    );
  } finally {
    isAuthenticated.value = false;
    user.value = null;
    isLoggingOut.value = false;
    await router.push('/');
  }
};

const handleUserUpdated = (event) => {
  user.value = event?.detail ?? readStoredUser();
  isAuthenticated.value = Boolean(localStorage.getItem('token'));
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
  window.addEventListener('wiki-user-updated', handleUserUpdated);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown);
  window.removeEventListener('wiki-user-updated', handleUserUpdated);
});
</script>

<style scoped>
.app-bar {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  color: rgb(var(--v-theme-on-surface));
}

.app-bar__breadcrumbs {
  padding: 0 8px;
}

.app-bar__breadcrumbs :deep(.v-breadcrumbs-item) {
  color: rgb(var(--v-theme-secondary));
}

.app-bar__breadcrumbs :deep(.app-bar__current-page) {
  color: rgb(var(--v-theme-on-surface));
  font-weight: 600;
}

.app-bar__organization {
  margin-inline-end: 12px;
  font-weight: 500;
}

.app-bar__profile-button {
  margin-inline: 4px 20px;
}

.app-bar__initials {
  color: rgb(var(--v-theme-on-secondary));
  font-size: 0.85rem;
  font-weight: 600;
}

.app-bar__profile-menu {
  margin-top: 8px;
}

@media (max-width: 600px) {
  .app-bar__organization,
  .app-bar__search-trigger span {
    display: none;
  }

  .app-bar__action {
    margin-inline: 4px 12px;
  }

  .app-bar__profile-button {
    margin-inline: 0 8px;
  }
}
</style>
