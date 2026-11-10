<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div class="public-portal">
    <!-- Hero Principal -->
    <v-card class="hero-card mb-6 mb-md-8 overflow-hidden" rounded="xl" elevation="0">
      <div class="hero-card__content pa-5 pa-sm-8 pa-md-12">
        <v-row align="center">
          <v-col cols="12" md="7">
            <v-chip
              color="accent"
              variant="tonal"
              size="small"
              prepend-icon="mdi-school-outline"
              class="mb-4 font-weight-bold"
            >
              FESOL · Plataforma Universitaria de Conocimiento
            </v-chip>
            <h1 class="text-h4 text-sm-h3 text-md-h2 font-weight-bold text-white mb-4">
              Wiki TDA: Tutoriales, Artículos y Proyectos Académicos
            </h1>
            <p class="text-body-2 text-sm-body-1 text-white opacity-90 mb-6" style="max-width: 620px;">
              Explora la base de conocimiento técnico creada por docentes y estudiantes, o navega por los eventos universitarios, carreras y proyectos desarrollados en cada ciclo académico.
            </p>
            <div class="d-flex flex-column flex-sm-row flex-wrap ga-3">
              <v-btn
                to="/explorar/wiki"
                color="white"
                variant="flat"
                size="large"
                prepend-icon="mdi-book-open-page-variant-outline"
                class="text-primary font-weight-bold w-100 w-sm-auto"
              >
                Explorar Wiki y Tutoriales
              </v-btn>
              <v-btn
                to="/explorar/eventos"
                color="accent"
                variant="outlined"
                size="large"
                prepend-icon="mdi-trophy-outline"
                class="text-white border-opacity-75 w-100 w-sm-auto"
              >
                Ver Eventos y Proyectos
              </v-btn>
              <v-btn
                v-if="!isAuthenticated"
                to="/login"
                variant="text"
                size="large"
                prepend-icon="mdi-login"
                class="text-white w-100 w-sm-auto"
              >
                Acceso Administrativo / Docente
              </v-btn>
            </div>
          </v-col>

          <v-col cols="12" md="5" class="d-flex justify-center mt-4 mt-md-0">
            <v-sheet
              color="rgba(255, 255, 255, 0.1)"
              rounded="xl"
              class="pa-4 pa-sm-6 w-100 backdrop-blur"
              border
            >
              <div class="text-subtitle-2 text-white mb-3 mb-sm-4 d-flex align-center justify-space-between">
                <span>Resumen del Repositorio Académico</span>
                <v-chip size="x-small" color="success" variant="flat">En vivo</v-chip>
              </div>
              <v-row dense>
                <v-col cols="6" v-for="stat in heroStats" :key="stat.label">
                  <v-sheet color="rgba(13, 18, 57, 0.55)" rounded="lg" class="pa-3 pa-sm-4 mb-2 text-white">
                    <v-icon :icon="stat.icon" color="accent" size="24" class="mb-1" />
                    <div class="text-h6 text-sm-h5 font-weight-bold">{{ stat.value }}</div>
                    <div class="text-caption opacity-80">{{ stat.label }}</div>
                  </v-sheet>
                </v-col>
              </v-row>
            </v-sheet>
          </v-col>
        </v-row>
      </div>
    </v-card>

    <!-- Buscador rápido global -->
    <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 mb-md-8 border">
      <v-row align="center">
        <v-col cols="12" md="5">
          <div class="text-h6 font-weight-bold text-primary">
            ¿Qué estás buscando hoy?
          </div>
          <div class="text-body-2 text-medium-emphasis">
            Filtra rápidamente tutoriales de la wiki, eventos académicos, carreras o proyectos estudiantiles.
          </div>
        </v-col>
        <v-col cols="12" md="7">
          <div class="d-flex flex-column flex-sm-row ga-3">
            <v-text-field
              v-model="quickSearch"
              placeholder="Buscar tutoriales, artículos, tecnologías (#laravel, #docker) o proyectos..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="comfortable"
              hide-details
              clearable
              class="flex-grow-1"
              @keyup.enter="irABusqueda"
            />
            <v-btn
              color="primary"
              size="large"
              variant="flat"
              prepend-icon="mdi-book-search-outline"
              @click="irABusqueda"
            >
              Buscar en Wiki
            </v-btn>
            <v-btn
              color="secondary"
              size="large"
              variant="tonal"
              prepend-icon="mdi-folder-search-outline"
              @click="irABusquedaProyectos"
            >
              Buscar en Proyectos
            </v-btn>
          </div>
          <div class="d-flex flex-wrap align-center ga-1 mt-3">
            <span class="text-caption font-weight-bold text-medium-emphasis mr-1">
              Explorar por tecnología:
            </span>
            <v-chip
              v-for="tech in ['laravel', 'vue3', 'docker', 'postgresql', 'git', 'seguridad']"
              :key="tech"
              size="x-small"
              color="primary"
              variant="tonal"
              :to="{ path: '/explorar/wiki', query: { tag: tech } }"
            >
              #{{ tech }}
            </v-chip>
          </div>
        </v-col>
      </v-row>
    </v-card>

    <!-- Dos columnas principales: Wiki Reciente y Eventos -> Carreras -> Proyectos -->
    <v-row class="mb-6">
      <!-- Vista rápida de Wiki: Tutoriales y Artículos -->
      <v-col cols="12" lg="6">
        <v-card rounded="xl" elevation="1" class="pa-6 h-100 border">
          <div class="d-flex align-center justify-space-between mb-4">
            <div>
              <div class="d-flex align-center ga-2">
                <v-icon icon="mdi-book-open-page-variant-outline" color="primary" />
                <h2 class="text-h6 font-weight-bold">Últimos Tutoriales y Artículos</h2>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1">
                Guías técnicas, documentación y artículos publicados en la Wiki.
              </p>
            </div>
            <v-btn
              to="/explorar/wiki"
              variant="tonal"
              color="primary"
              size="small"
              append-icon="mdi-arrow-right"
            >
              Ver todos
            </v-btn>
          </div>

          <v-list lines="two" class="pa-0">
            <v-list-item
              v-for="article in featuredArticles"
              :key="article.id"
              rounded="lg"
              class="mb-3 border pa-3"
              :to="article.slug ? `/explorar/wiki/${article.slug}` : '/explorar/wiki'"
            >
              <template #prepend>
                <v-avatar color="primary" variant="tonal" rounded="lg" size="46">
                  <v-icon :icon="article.type === 'Tutorial' ? 'mdi-school-outline' : 'mdi-file-document-outline'" />
                </v-avatar>
              </template>

              <v-list-item-title class="font-weight-bold mb-1">
                {{ article.title }}
              </v-list-item-title>
              <v-list-item-subtitle class="text-body-2 mb-2">
                {{ article.summary }}
              </v-list-item-subtitle>

              <div class="d-flex flex-wrap align-center ga-2 mt-1">
                <v-chip size="x-small" color="secondary" variant="flat">
                  {{ article.category }}
                </v-chip>
                <v-chip
                  v-for="tag in article.tags"
                  :key="tag"
                  size="x-small"
                  variant="outlined"
                >
                  #{{ tag }}
                </v-chip>
                <v-spacer />
                <span class="text-caption text-medium-emphasis">
                  Por {{ article.author }}
                </span>
              </div>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- Vista rápida de Eventos -> Carreras -> Proyectos (Con Slide / Carrusel cuando hay varios eventos) -->
      <v-col cols="12" lg="6">
        <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 h-100 border">
          <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-4">
            <div>
              <div class="d-flex align-center ga-2">
                <v-icon icon="mdi-calendar-star" color="primary" />
                <h2 class="text-h6 font-weight-bold">Eventos Académicos y Proyectos</h2>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1">
                Navega por Ciclo/Evento <v-icon icon="mdi-chevron-right" size="small" /> Carrera <v-icon icon="mdi-chevron-right" size="small" /> Materia y Proyectos.
              </p>
            </div>
            <div class="d-flex align-center ga-1">
              <v-btn
                v-if="totalEventSlides > 1"
                icon="mdi-chevron-left"
                variant="tonal"
                color="primary"
                size="x-small"
                :disabled="eventSlideIndex <= 0"
                aria-label="Eventos anteriores"
                @click="eventSlideIndex--"
              />
              <span v-if="totalEventSlides > 1" class="text-caption font-weight-bold mx-1">
                {{ eventSlideIndex + 1 }} / {{ totalEventSlides }}
              </span>
              <v-btn
                v-if="totalEventSlides > 1"
                icon="mdi-chevron-right"
                variant="tonal"
                color="primary"
                size="x-small"
                :disabled="eventSlideIndex >= totalEventSlides - 1"
                aria-label="Siguientes eventos"
                @click="eventSlideIndex++"
              />
              <v-btn
                to="/explorar/eventos"
                variant="tonal"
                color="primary"
                size="small"
                append-icon="mdi-arrow-right"
                class="ml-1"
              >
                Explorar Eventos
              </v-btn>
            </div>
          </div>

          <v-window v-model="eventSlideIndex">
            <v-window-item
              v-for="(slideGroup, sIdx) in eventSlideGroups"
              :key="sIdx"
              :value="sIdx"
            >
              <v-card
                v-for="evento in slideGroup"
                :key="evento.id"
                variant="outlined"
                rounded="lg"
                class="pa-3 pa-sm-4 mb-3"
              >
                <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-2">
                  <div class="d-flex flex-wrap align-center ga-2">
                    <v-chip
                      :color="evento.is_active ? 'success' : 'grey'"
                      size="small"
                      variant="flat"
                    >
                      {{ evento.is_active ? 'Evento Activo' : 'Finalizado' }}
                    </v-chip>
                    <span class="text-subtitle-1 font-weight-bold text-break">{{ evento.cicle_name }}</span>
                  </div>
                  <span class="text-caption text-medium-emphasis">
                    Inicio: {{ evento.start_date }}
                  </span>
                </div>

                <div class="text-body-2 text-medium-emphasis mb-3 text-break">
                  Coordinador: <strong>{{ evento.coordinador }}</strong>
                </div>

                <div class="d-flex flex-wrap ga-2 mb-3">
                  <v-chip
                    v-for="carrera in evento.carreras"
                    :key="carrera.code"
                    size="small"
                    color="primary"
                    variant="tonal"
                    prepend-icon="mdi-school-outline"
                  >
                    {{ carrera.name }} ({{ carrera.projectsCount }} proyectos)
                  </v-chip>
                </div>

                <div class="d-flex justify-end">
                  <v-btn
                    to="/explorar/eventos"
                    size="small"
                    color="secondary"
                    variant="text"
                    append-icon="mdi-chevron-right"
                    class="text-none"
                  >
                    Ver carreras y proyectos del evento
                  </v-btn>
                </div>
              </v-card>
            </v-window-item>
          </v-window>
        </v-card>
      </v-col>
    </v-row>

    <!-- Flujo de Roles del Sistema -->
    <v-card rounded="xl" elevation="1" class="pa-6 border">
      <div class="text-h6 font-weight-bold text-primary mb-1">
        Estructura de Roles y Participación en Wiki TDA
      </div>
      <p class="text-body-2 text-medium-emphasis mb-4">
        Cada actor académico cuenta con herramientas específicas dentro de la plataforma.
      </p>
      <v-row>
        <v-col cols="12" sm="6" md="3" v-for="role in roleCards" :key="role.title">
          <v-sheet rounded="lg" class="pa-4 h-100 border bg-surface">
            <v-avatar :color="role.color" variant="tonal" rounded="lg" size="42" class="mb-3">
              <v-icon :icon="role.icon" />
            </v-avatar>
            <div class="text-subtitle-1 font-weight-bold mb-1">{{ role.title }}</div>
            <div class="text-body-2 text-medium-emphasis">{{ role.description }}</div>
          </v-sheet>
        </v-col>
      </v-row>
    </v-card>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { obtenerDashboardStats } from '../services/proyecto/proyecto.service.js';

const router = useRouter();
const quickSearch = ref('');
const isAuthenticated = ref(Boolean(localStorage.getItem('token')));
const eventSlideIndex = ref(0);
const eventsPerSlide = 2;

const heroStats = ref([
  { label: 'Artículos & Tutoriales', value: '48+', icon: 'mdi-book-open-variant' },
  { label: 'Eventos Académicos', value: '6', icon: 'mdi-calendar-check-outline' },
  { label: 'Carreras Activas', value: '5', icon: 'mdi-school-outline' },
  { label: 'Proyectos Subidos', value: '124', icon: 'mdi-folder-star-outline' },
]);

const featuredArticles = ref([
  {
    id: 1,
    slug: 'guia-apis-restful-laravel-12-jwt-spatie',
    type: 'Tutorial',
    title: 'Guía paso a paso: Despliegue de APIs en Laravel 12 con Docker',
    summary: 'Configuración de contenedores Nginx, PHP-FPM y PostgreSQL para proyectos de cátedra.',
    category: 'Desarrollo Backend',
    tags: ['laravel', 'docker', 'api'],
    author: 'Ing. Carlos Méndez',
  },
  {
    id: 2,
    slug: 'arquitectura-modular-vue3-vuetify4-pinia',
    type: 'Artículo',
    title: 'Arquitectura limpia en aplicaciones Vue 3 + Pinia + Vuetify',
    summary: 'Buenas prácticas de separación por servicios, stores, composables y control de acceso por roles.',
    category: 'Frontend Moderno',
    tags: ['vue3', 'vuetify', 'pinia'],
    author: 'Dra. Elena Ríos',
  },
  {
    id: 3,
    slug: 'empaquetado-proyectos-normativa-git-fesol',
    type: 'Tutorial',
    title: ' Flujo de trabajo Git y estándares para entrega de proyectos FESOL',
    summary: 'Cómo estructurar el repositorio, documentación técnica y empaquetado de entregables.',
    category: 'Ingeniería de Software',
    tags: ['git', 'documentacion', 'estandares'],
    author: 'Coord. Roberto Morales',
  },
]);

const featuredEvents = ref([
  {
    id: 1,
    cicle_name: 'FESOL Ciclo I - 2026',
    start_date: '2026-02-15',
    is_active: true,
    coordinador: 'Ing. Roberto Morales (Coordinador de Carrera)',
    carreras: [
      { code: 'ING-SIS', name: 'Ingeniería en Sistemas', projectsCount: 18 },
      { code: 'ING-SW', name: 'Ingeniería de Software', projectsCount: 14 },
      { code: 'TEC-RED', name: 'Redes y Telecomunicaciones', projectsCount: 9 },
    ],
  },
  {
    id: 2,
    cicle_name: 'Feria de Innovación Tecnológica II - 2025',
    start_date: '2025-08-10',
    is_active: false,
    coordinador: 'MSc. Laura Gómez (Coordinadora)',
    carreras: [
      { code: 'ING-SIS', name: 'Ingeniería en Sistemas', projectsCount: 22 },
      { code: 'ING-IA', name: 'Ciencia de Datos e IA', projectsCount: 11 },
    ],
  },
]);

const roleCards = [
  {
    title: 'Administrador',
    icon: 'mdi-shield-crown-outline',
    color: 'primary',
    description: 'Gestiona usuarios, roles, permisos, catálogo de carreras, categorías/etiquetas de la Wiki y configuración global.',
  },
  {
    title: 'Coordinador de Carrera',
    icon: 'mdi-calendar-Account-outline',
    color: 'secondary',
    description: 'Crea y administra los Eventos académicos de su carrera, supervisando las materias y proyectos participantes.',
  },
  {
    title: 'Docente',
    icon: 'mdi-human-male-board',
    color: 'info',
    description: 'Registra sus materias dentro de cada evento, sube y gestiona los proyectos de los estudiantes y publica tutoriales.',
  },
  {
    title: 'Estudiante / Público',
    icon: 'mdi-account-school-outline',
    color: 'success',
    description: 'Consulta libremente tutoriales y artículos en la Wiki, y explora eventos, carreras y proyectos con filtros avanzados.',
  },
];

const irABusqueda = () => {
  router.push({
    path: '/explorar/wiki',
    query: quickSearch.value ? { q: quickSearch.value } : {},
  });
};

const eventSlideGroups = computed(() => {
  const groups = [];
  for (let i = 0; i < featuredEvents.value.length; i += eventsPerSlide) {
    groups.push(featuredEvents.value.slice(i, i + eventsPerSlide));
  }
  return groups.length ? groups : [[]];
});

const totalEventSlides = computed(() => eventSlideGroups.value.length);

const irABusquedaProyectos = () => {
  router.push({
    path: '/explorar/eventos',
    query: quickSearch.value ? { q: quickSearch.value } : {},
  });
};

onMounted(async () => {
  try {
    const res = await obtenerDashboardStats();
    const data = res.data?.data;
    if (!data) return;

    const c = data.counts || {};
    heroStats.value = [
      { label: 'Artículos & Tutoriales', value: String(c.published_articles ?? c.articles ?? 0), icon: 'mdi-book-open-variant' },
      { label: 'Eventos Académicos', value: String(c.events ?? 0), icon: 'mdi-calendar-check-outline' },
      { label: 'Carreras Activas', value: String(c.carreras ?? 0), icon: 'mdi-school-outline' },
      { label: 'Proyectos Subidos', value: String(c.projects ?? 0), icon: 'mdi-folder-star-outline' },
    ];

    if (data.latest_articles?.length) {
      featuredArticles.value = data.latest_articles.map((a) => ({
        id: a.id,
        slug: a.slug,
        type: a.type === 'tutorial' || a.type === 'Tutorial' ? 'Tutorial' : 'Artículo',
        title: a.title,
        summary: a.summary || String(a.content || '').slice(0, 140),
        category: a.category?.name || 'General',
        tags: (a.tags || []).map((t) => t.name),
        author: a.author?.name || 'Docente',
      }));
    }

    if (data.latest_events?.length) {
      featuredEvents.value = data.latest_events.map((ev) => {
        const carreraMap = new Map();
        for (const sub of ev.event_subjects || []) {
          if (sub.carrera) {
            const curr = carreraMap.get(sub.carrera.id) || {
              code: sub.carrera.code,
              name: sub.carrera.name,
              projectsCount: 0,
            };
            curr.projectsCount += (sub.projects || []).length;
            carreraMap.set(sub.carrera.id, curr);
          }
        }
        return {
          id: ev.id,
          cicle_name: ev.cicle_name,
          start_date: String(ev.start_date || '').slice(0, 10),
          is_active: Boolean(ev.is_active),
          coordinador: ev.coordinador?.name || 'Coordinador',
          carreras: Array.from(carreraMap.values()),
        };
      });
    }
  } catch (e) {
    // Mantiene respaldo visual si el servidor está apagado
  }
});
</script>

<style scoped>
.hero-card {
  background:
    radial-gradient(circle at 85% 20%, rgba(130, 177, 255, 0.28), transparent 45%),
    linear-gradient(135deg, #0d1239 0%, #1f2d7f 100%);
}
</style>
