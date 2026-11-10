<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Encabezado y buscador público de la Wiki -->
    <v-card rounded="xl" elevation="0" class="wiki-hero pa-5 pa-sm-6 pa-md-8 mb-6">
      <v-row align="center">
        <v-col cols="12" md="7">
          <div class="d-flex flex-wrap align-center ga-2 mb-2">
            <v-chip color="accent" variant="tonal" size="small" prepend-icon="mdi-earth">
              Acceso Público · Base de Conocimiento Universitaria
            </v-chip>
            <v-chip
              v-if="activeArticle"
              color="white"
              variant="outlined"
              size="small"
              prepend-icon="mdi-book-open-variant"
            >
              Modo Lectura Documental
            </v-chip>
          </div>
          <h1 class="text-h5 text-sm-h4 font-weight-bold text-white mb-2">
            Wiki Universitaria: Tutoriales y Artículos
          </h1>
          <p class="text-body-2 text-white opacity-85">
            Explora guías paso a paso, documentación técnica, estándares de cátedra y artículos publicados por nuestros docentes e investigadores.
          </p>
        </v-col>
        <v-col cols="12" md="5">
          <v-text-field
            v-model="searchQuery"
            placeholder="Buscar por título, contenido, autor o etiqueta (#tag)..."
            prepend-inner-icon="mdi-magnify"
            variant="solo"
            bg-color="surface"
            density="comfortable"
            rounded="lg"
            hide-details
            clearable
            @update:model-value="salirModoLecturaSiBusca"
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- ========================================================================= -->
    <!-- MODO 1: VISTA DE LECTURA DEDICADA DE 3 COLUMNAS (ESTILO GITBOOK / NOTION) -->
    <!-- ========================================================================= -->
    <div v-if="activeArticle">
      <!-- Barra superior de navegación del documento -->
      <v-card rounded="xl" elevation="1" class="pa-3 pa-sm-4 mb-6 border d-flex flex-wrap align-center justify-space-between ga-2">
        <div class="d-flex align-center flex-wrap ga-2">
          <v-btn
            color="primary"
            variant="tonal"
            size="small"
            prepend-icon="mdi-arrow-left"
            @click="cerrarLectura"
          >
            Volver al Catálogo Wiki
          </v-btn>
          <v-divider vertical class="mx-1 d-none d-sm-inline" />
          <v-chip size="small" color="secondary" variant="tonal">
            {{ activeArticle.category.name }}
          </v-chip>
          <span class="text-caption text-medium-emphasis d-none d-md-inline">
            /explorar/wiki/{{ activeArticle.slug }}
          </span>
        </div>

        <div class="d-flex align-center ga-2">
          <v-btn
            size="small"
            variant="outlined"
            color="primary"
            prepend-icon="mdi-link-variant"
            @click="copiarEnlaceArticulo"
          >
            {{ linkCopied ? '¡Enlace copiado!' : 'Copiar enlace directo' }}
          </v-btn>
        </div>
      </v-card>

      <v-row>
        <!-- Columna Izquierda (3 cols en md+, aparece debajo del artículo en móvil): Índice de la misma Categoría -->
        <v-col cols="12" md="3" class="order-3 order-md-1">
          <v-card rounded="xl" elevation="1" class="pa-4 border sticky-panel">
            <div class="text-caption font-weight-bold text-primary text-uppercase mb-2">
              En {{ activeArticle.category.name }}
            </div>
            <v-list density="compact" nav class="pa-0 mb-4">
              <v-list-item
                v-for="sibling in articlesInSameCategory"
                :key="sibling.id"
                :title="sibling.title"
                :active="sibling.slug === activeArticle.slug"
                color="primary"
                rounded="lg"
                @click="abrirArticulo(sibling)"
              >
                <template #prepend>
                  <v-icon
                    size="small"
                    :icon="sibling.type === 'Tutorial' ? 'mdi-compass-outline' : 'mdi-file-document-outline'"
                  />
                </template>
              </v-list-item>
            </v-list>

            <v-divider class="my-3" />

            <div class="text-caption font-weight-bold text-medium-emphasis text-uppercase mb-2">
              Explorar otras Categorías
            </div>
            <div class="d-flex flex-column ga-1">
              <v-btn
                v-for="cat in categoryOptions"
                :key="cat.slug"
                variant="text"
                size="small"
                class="justify-start text-none"
                prepend-icon="mdi-folder-outline"
                @click="filtrarPorCategoriaDesdeLectura(cat.slug)"
              >
                {{ cat.name }}
              </v-btn>
            </div>
          </v-card>
        </v-col>

        <!-- Columna Central (6 cols en md+, aparece PRIMERO en móvil): Contenido Completo del Tutorial / Artículo -->
        <v-col cols="12" md="6" class="order-1 order-md-2">
          <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 pa-md-8 border">
            <div class="d-flex flex-wrap align-center ga-2 mb-3">
              <v-chip
                :color="activeArticle.type === 'Tutorial' ? 'info' : 'secondary'"
                size="small"
                variant="flat"
                :prepend-icon="activeArticle.type === 'Tutorial' ? 'mdi-compass-outline' : 'mdi-file-document-outline'"
              >
                {{ activeArticle.type }}
              </v-chip>
              <v-chip size="small" color="primary" variant="tonal">
                {{ activeArticle.category.name }}
              </v-chip>
              <span class="text-caption text-medium-emphasis">
                Publicado el {{ activeArticle.created_at }}
              </span>
            </div>

            <h2 class="text-h5 text-sm-h4 font-weight-bold mb-4" style="line-height: 1.25;">
              {{ activeArticle.title }}
            </h2>

            <!-- Ficha de Autor -->
            <v-sheet rounded="lg" class="pa-3 mb-6 border bg-surface d-flex align-center justify-space-between flex-wrap ga-2">
              <div class="d-flex align-center ga-3">
                <v-avatar color="primary" size="40">
                  <span class="text-white font-weight-bold">
                    {{ activeArticle.author.name.charAt(0) }}
                  </span>
                </v-avatar>
                <div>
                  <div class="text-subtitle-2 font-weight-bold">{{ activeArticle.author.name }}</div>
                  <div class="text-caption text-medium-emphasis">{{ activeArticle.author.role }}</div>
                </div>
              </div>
              <div class="d-flex flex-wrap ga-1">
                <v-chip
                  v-for="t in activeArticle.tags"
                  :key="t.slug"
                  size="small"
                  color="secondary"
                  variant="tonal"
                  @click="filtrarPorTagDesdeLectura(t.slug)"
                >
                  #{{ t.name }}
                </v-chip>
              </div>
            </v-sheet>

            <!-- Resumen Ejecutivo -->
            <v-alert
              color="primary"
              variant="tonal"
              border="start"
              class="mb-6 text-body-2"
              icon="mdi-lightbulb-on-outline"
            >
              <strong>Resumen:</strong> {{ activeArticle.excerpt }}
            </v-alert>

            <!-- Secciones o contenido enriquecido del artículo -->
            <div v-if="isRichHtmlContent" class="article-body wiki-rich-render" v-html="sanitizedRichHtml" />
            <div v-else class="article-body">
              <div
                v-for="(sec, index) in parsedSections"
                :key="index"
                :id="`section-${index}`"
                class="mb-6"
              >
                <h3 v-if="sec.heading" class="text-h6 font-weight-bold text-primary mb-2">
                  {{ sec.heading }}
                </h3>
                <p class="text-body-1" style="white-space: pre-line; line-height: 1.8;">
                  {{ sec.body }}
                </p>
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- Columna Derecha (3 cols en md+, aparece segunda en móvil): Tabla de Contenidos (TOC) + Conexión Semántica -->
        <v-col cols="12" md="3" class="order-2 order-md-3">
          <div class="sticky-panel">
            <!-- Índice "En esta página" -->
            <v-card rounded="xl" elevation="1" class="pa-4 mb-4 border">
              <div class="text-caption font-weight-bold text-primary text-uppercase mb-2 d-flex align-center ga-1">
                <v-icon icon="mdi-format-list-bulleted" size="small" />
                En esta página
              </div>
              <v-list density="compact" class="pa-0">
                <v-list-item
                  v-for="(sec, index) in parsedSections"
                  :key="index"
                  :title="sec.heading || `Sección ${index + 1}`"
                  rounded="lg"
                  class="text-caption"
                  @click="scrollToSection(`section-${index}`)"
                />
              </v-list>
            </v-card>

            <!-- Conexión Semántica: Proyectos Estudiantiles con las mismas tecnologías -->
            <v-card rounded="xl" elevation="1" class="pa-4 border">
              <div class="text-caption font-weight-bold text-secondary text-uppercase mb-1 d-flex align-center ga-1">
                <v-icon icon="mdi-rocket-launch-outline" size="small" />
                Proyectos Relacionados
              </div>
              <p class="text-caption text-medium-emphasis mb-3">
                Proyectos universitarios que aplican las tecnologías de esta guía:
              </p>

              <div v-for="t in activeArticle.tags" :key="t.slug" class="mb-2">
                <v-btn
                  block
                  size="small"
                  color="secondary"
                  variant="tonal"
                  class="justify-space-between text-none"
                  append-icon="mdi-arrow-right"
                  :to="{ path: '/explorar/eventos', query: { tag: t.slug } }"
                >
                  <span>Proyectos con #{{ t.name }}</span>
                </v-btn>
              </div>
            </v-card>
          </div>
        </v-col>
      </v-row>
    </div>

    <!-- ========================================================================= -->
    <!-- MODO 2: CATÁLOGO DE EXPLORACIÓN CON PANEL LATERAL DE CATEGORÍAS Y TAGS    -->
    <!-- ========================================================================= -->
    <v-row v-else>
      <!-- Sidebar Izquierdo de Filtros Facetados (Categorías y Etiquetas) -->
      <v-col cols="12" md="3">
        <v-card rounded="xl" elevation="1" class="pa-4 border sticky-panel">
          <div class="d-flex align-center justify-space-between mb-3">
            <span class="text-subtitle-2 font-weight-bold text-primary d-flex align-center ga-1">
              <v-icon icon="mdi-filter-variant" size="small" />
              Explorador por Categorías
            </span>
            <v-btn
              v-if="hasActiveFilters"
              size="x-small"
              variant="text"
              color="error"
              @click="resetFilters"
            >
              Limpiar
            </v-btn>
          </div>

          <v-list density="compact" nav class="pa-0 mb-4">
            <v-list-item
              title="Todas las categorías"
              prepend-icon="mdi-view-grid-outline"
              :active="!selectedCategory"
              color="primary"
              rounded="lg"
              @click="selectedCategory = null"
            >
              <template #append>
                <v-chip size="x-small" variant="tonal">{{ articles.length }}</v-chip>
              </template>
            </v-list-item>

            <v-list-item
              v-for="cat in categoryOptions"
              :key="cat.slug"
              :title="cat.name"
              prepend-icon="mdi-folder-outline"
              :active="selectedCategory === cat.slug"
              color="primary"
              rounded="lg"
              @click="selectedCategory = selectedCategory === cat.slug ? null : cat.slug"
            >
              <template #append>
                <v-chip size="x-small" variant="tonal">
                  {{ contarPorCategoria(cat.slug) }}
                </v-chip>
              </template>
            </v-list-item>
          </v-list>

          <v-divider class="my-3" />

          <div class="text-subtitle-2 font-weight-bold text-primary mb-2 d-flex align-center ga-1">
            <v-icon icon="mdi-tag-multiple-outline" size="small" />
            Filtrar por Tecnología (#Tag)
          </div>
          <div class="d-flex flex-wrap ga-1 mb-4">
            <v-chip
              v-for="tag in tagOptions"
              :key="tag.slug"
              size="small"
              :color="selectedTag === tag.slug ? 'primary' : undefined"
              :variant="selectedTag === tag.slug ? 'flat' : 'tonal'"
              @click="selectedTag = selectedTag === tag.slug ? null : tag.slug"
            >
              #{{ tag.name }}
            </v-chip>
          </div>

          <!-- Puente semántico directo a Proyectos cuando hay un tag seleccionado -->
          <v-sheet
            v-if="selectedTag"
            rounded="lg"
            color="secondary"
            class="pa-3 text-white"
          >
            <div class="text-caption font-weight-bold mb-1">
              ¿Buscas proyectos hechos con #{{ selectedTag }}?
            </div>
            <v-btn
              size="x-small"
              color="white"
              variant="flat"
              block
              class="text-primary font-weight-bold text-none mt-1"
              :to="{ path: '/explorar/eventos', query: { tag: selectedTag } }"
              append-icon="mdi-arrow-right"
            >
              Ver Proyectos con #{{ selectedTag }}
            </v-btn>
          </v-sheet>
        </v-card>
      </v-col>

      <!-- Panel Derecho Principal: Pestañas de Tipo + Cuadrícula de Tutoriales y Artículos -->
      <v-col cols="12" md="9">
        <v-card rounded="xl" elevation="1" class="pa-3 pa-sm-4 mb-4 border d-flex flex-wrap align-center justify-space-between ga-2">
          <v-tabs v-model="selectedType" color="primary" density="comfortable" show-arrows class="w-100 w-sm-auto">
            <v-tab value="all">Todos ({{ filteredArticles.length }})</v-tab>
            <v-tab value="Tutorial" prepend-icon="mdi-compass-outline">Tutoriales Paso a Paso</v-tab>
            <v-tab value="Artículo" prepend-icon="mdi-file-document-outline">Artículos Técnicos</v-tab>
          </v-tabs>

          <div v-if="hasActiveFilters" class="d-flex flex-wrap align-center ga-1">
            <v-chip
              v-if="selectedCategory"
              size="small"
              closable
              color="primary"
              variant="tonal"
              @click:close="selectedCategory = null"
            >
              Categoría: {{ selectedCategory }}
            </v-chip>
            <v-chip
              v-if="selectedTag"
              size="small"
              closable
              color="secondary"
              variant="tonal"
              @click:close="selectedTag = null"
            >
              #{{ selectedTag }}
            </v-chip>
          </div>
        </v-card>

        <v-row v-if="filteredArticles.length">
          <v-col
            v-for="article in filteredArticles"
            :key="article.id"
            cols="12"
            md="6"
          >
            <v-card
              rounded="xl"
              elevation="1"
              class="h-100 d-flex flex-column border article-card"
              @click="abrirArticulo(article)"
            >
              <v-card-item class="pb-2">
                <div class="d-flex align-center justify-space-between mb-2">
                  <v-chip
                    :color="article.type === 'Tutorial' ? 'info' : 'secondary'"
                    size="small"
                    variant="tonal"
                    :prepend-icon="article.type === 'Tutorial' ? 'mdi-compass-outline' : 'mdi-file-document-outline'"
                  >
                    {{ article.type }}
                  </v-chip>
                  <v-chip size="x-small" color="primary" variant="outlined">
                    {{ article.category.name }}
                  </v-chip>
                </div>
                <v-card-title class="text-h6 font-weight-bold text-wrap">
                  {{ article.title }}
                </v-card-title>
              </v-card-item>

              <v-card-text class="flex-grow-1">
                <p class="text-body-2 text-medium-emphasis mb-4">
                  {{ article.excerpt }}
                </p>
                <div class="d-flex flex-wrap ga-1">
                  <v-chip
                    v-for="t in article.tags"
                    :key="t.slug"
                    size="x-small"
                    variant="tonal"
                    color="secondary"
                    @click.stop="selectedTag = t.slug"
                  >
                    #{{ t.name }}
                  </v-chip>
                </div>
              </v-card-text>

              <v-divider />

              <v-card-actions class="px-4 py-3 d-flex align-center justify-space-between">
                <div class="d-flex align-center ga-2">
                  <v-avatar size="28" color="primary" variant="tonal">
                    <span class="text-caption font-weight-bold">
                      {{ article.author.name.charAt(0) }}
                    </span>
                  </v-avatar>
                  <div>
                    <div class="text-caption font-weight-semibold">{{ article.author.name }}</div>
                    <div class="text-caption text-medium-emphasis">{{ article.created_at }}</div>
                  </div>
                </div>
                <v-btn
                  color="primary"
                  variant="tonal"
                  size="small"
                  append-icon="mdi-book-open-page-variant-outline"
                  @click.stop="abrirArticulo(article)"
                >
                  Abrir Guía
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>

        <!-- Estado vacío -->
        <v-card v-else rounded="xl" class="pa-12 text-center border" elevation="0">
          <v-icon icon="mdi-book-search-outline" size="64" color="secondary" class="mb-3" />
          <h3 class="text-h6 font-weight-bold mb-1">No se encontraron tutoriales ni artículos</h3>
          <p class="text-body-2 text-medium-emphasis mb-4">
            Intenta ajustar los filtros de búsqueda, categoría o etiquetas.
          </p>
          <v-btn color="primary" variant="tonal" @click="resetFilters">
            Restablecer búsqueda
          </v-btn>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  obtenerArticulos,
  obtenerCategorias,
  obtenerTags,
} from '../../services/wiki/wiki.service.js';

const route = useRoute();
const router = useRouter();

const searchQuery = ref('');
const selectedType = ref('all');
const selectedCategory = ref(null);
const selectedTag = ref(null);
const activeArticle = ref(null);
const linkCopied = ref(false);

const categoryOptions = ref([
  { id: 1, name: 'Desarrollo Backend', slug: 'desarrollo-backend' },
  { id: 2, name: 'Frontend Moderno', slug: 'frontend-moderno' },
  { id: 3, name: 'Base de Datos y SQL', slug: 'base-de-datos-sql' },
  { id: 4, name: 'DevOps & Contenedores', slug: 'devops-contenedores' },
  { id: 5, name: 'Estándares de Proyectos', slug: 'estandares-proyectos' },
]);

const tagOptions = ref([
  { id: 1, name: 'laravel', slug: 'laravel' },
  { id: 2, name: 'vue3', slug: 'vue3' },
  { id: 3, name: 'docker', slug: 'docker' },
  { id: 4, name: 'postgresql', slug: 'postgresql' },
  { id: 5, name: 'git', slug: 'git' },
  { id: 6, name: 'seguridad', slug: 'seguridad' },
]);

const articles = ref([
  {
    id: 1,
    type: 'Tutorial',
    title: 'Guía paso a paso: Configuración de APIs RESTful en Laravel 12 con JWT y Spatie Permissions',
    slug: 'guia-apis-restful-laravel-12-jwt-spatie',
    category: { id: 1, name: 'Desarrollo Backend', slug: 'desarrollo-backend' },
    author: { id: 2, name: 'Ing. Carlos Méndez', role: 'Docente de Ingeniería' },
    is_published: true,
    created_at: '2026-10-18',
    tags: [
      { name: 'laravel', slug: 'laravel' },
      { name: 'seguridad', slug: 'seguridad' },
      { name: 'docker', slug: 'docker' },
    ],
    excerpt: 'Aprende cómo estructurar controladores, proteger rutas mediante guard api y asignar roles y permisos para proyectos universitarios.',
    content: `1. Introducción a la Arquitectura de Autenticación
En este tutorial revisaremos la configuración completa de autenticación JWT junto con control de acceso basado en roles (RBAC) usando Spatie Laravel Permission.

2. Estructura de Rutas (routes/api.php)
Todas las rutas administrativas deben agruparse bajo el middleware auth:api y verificar permisos específicos antes de ejecutar cambios sobre las tablas principales.

3. Respuestas Estandarizadas con ApiResponse
Utiliza siempre la clase ApiResponse::success($mensaje, $codigo, $data, $pagination) para mantener el contrato uniforme con el cliente en Vue 3.`,
  },
  {
    id: 2,
    type: 'Artículo',
    title: 'Arquitectura Modular en Vue 3 con Vuetify 4, Pinia y Axios Interceptors',
    slug: 'arquitectura-modular-vue3-vuetify4-pinia',
    category: { id: 2, name: 'Frontend Moderno', slug: 'frontend-moderno' },
    author: { id: 3, name: 'Dra. Elena Ríos', role: 'Docente Investigadora' },
    is_published: true,
    created_at: '2026-10-22',
    tags: [
      { name: 'vue3', slug: 'vue3' },
    ],
    excerpt: 'Patrones recomendados para separar vistas, composables, stores y servicios HTTP manteniendo soporte para tema claro y oscuro.',
    content: `1. Separación de Capas en el Frontend
El proyecto frontend de Wiki TDA está organizado en capas limpias: services/, store/, view/ y directives/.

2. Manejo de Estado Reactivo y JWT
Los interceptores de Axios renuevan automáticamente el token en segundo plano y sincronizan los roles con el almacenamiento local.

3. Directivas de Control de Acceso
Usa v-role y v-permission para mostrar acciones administrativas únicamente a los perfiles autorizados.`,
  },
  {
    id: 3,
    type: 'Tutorial',
    title: 'Diseño de Esquemas Relacionales y Migraciones Seguras en PostgreSQL / SQLite',
    slug: 'diseno-esquemas-relacionales-migraciones-postgresql',
    category: { id: 3, name: 'Base de Datos y SQL', slug: 'base-de-datos-sql' },
    author: { id: 1, name: 'MSc. Roberto Morales', role: 'Coordinador de Carrera' },
    is_published: true,
    created_at: '2026-10-28',
    tags: [
      { name: 'postgresql', slug: 'postgresql' },
      { name: 'laravel', slug: 'laravel' },
    ],
    excerpt: 'Cómo definir llaves foráneas con restrictOnDelete y cascadeOnDelete para proteger la integridad histórica de los eventos y proyectos.',
    content: `1. Reglas de Integridad Referencial
Evita borrar usuarios o carreras físicamente si tienen historial académico en eventos o proyectos.

2. Uso de Columnas de Estado
Utiliza columnas booleanas de estado para conservar la trazabilidad de cada ciclo universitario.

3. Indexación para Búsquedas Rápidas
Indexa siempre las columnas de búsqueda frecuente como title, slug y carnet_or_code.`,
  },
  {
    id: 4,
    type: 'Tutorial',
    title: 'Empaquetado de Proyectos Estudiantiles y Normativa de Repositorios Git para FESOL',
    slug: 'empaquetado-proyectos-normativa-git-fesol',
    category: { id: 5, name: 'Estándares de Proyectos', slug: 'estandares-proyectos' },
    author: { id: 2, name: 'Ing. Carlos Méndez', role: 'Docente de Ingeniería' },
    is_published: true,
    created_at: '2026-11-01',
    tags: [
      { name: 'git', slug: 'git' },
      { name: 'docker', slug: 'docker' },
    ],
    excerpt: 'Requisitos obligatorios para subir el código fuente, manual técnico en PDF y contenedor Docker en los eventos de fin de ciclo.',
    content: `1. Estructura del Repositorio Institucional
Cada equipo de proyecto registrado por el docente en su materia debe incluir enlace al repositorio Git oficial.

2. Documentación y Variables de Entorno
Debe incluirse un archivo README.md detallado y un archivo .env.example libre de credenciales sensibles.

3. Entregables en la Plataforma
Sube el manual técnico en PDF y el paquete de versión estable en la sección de archivos del proyecto.`,
  },
]);

const hasActiveFilters = computed(() => {
  return Boolean(
    searchQuery.value ||
      selectedType.value !== 'all' ||
      selectedCategory.value ||
      selectedTag.value
  );
});

const filteredArticles = computed(() => {
  return articles.value.filter((item) => {
    const matchesType = selectedType.value === 'all' || item.type === selectedType.value;
    const matchesCategory = !selectedCategory.value || item.category.slug === selectedCategory.value;
    const matchesTag = !selectedTag.value || item.tags.some((t) => t.slug === selectedTag.value);

    const q = searchQuery.value?.trim().toLowerCase();
    const matchesSearch =
      !q ||
      item.title.toLowerCase().includes(q) ||
      item.excerpt.toLowerCase().includes(q) ||
      item.author.name.toLowerCase().includes(q) ||
      item.tags.some((t) => t.name.toLowerCase().includes(q));

    return matchesType && matchesCategory && matchesTag && matchesSearch;
  });
});

const articlesInSameCategory = computed(() => {
  if (!activeArticle.value) return [];
  return articles.value.filter(
    (a) => a.category.slug === activeArticle.value.category.slug
  );
});

const isRichHtmlContent = computed(() => {
  const raw = activeArticle.value?.content || '';
  return /<[a-z][\s\S]*>/i.test(raw);
});

const sanitizedRichHtml = computed(() => {
  const raw = activeArticle.value?.content || '';
  return String(raw).replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
});

const parsedSections = computed(() => {
  if (!activeArticle.value?.content) return [];
  const raw = String(activeArticle.value.content);
  if (/<[a-z][\s\S]*>/i.test(raw)) {
    const matches = [...raw.matchAll(/<h[23][^>]*>(.*?)<\/h[23]>/gi)];
    if (matches.length) {
      return matches.map((m, idx) => ({
        heading: m[1].replace(/<[^>]+>/g, '').trim() || `Sección ${idx + 1}`,
        body: '',
      }));
    }
    return [{ heading: 'Contenido principal', body: '' }];
  }

  const blocks = raw
    .split(/\n\s*\n/)
    .map((b) => b.trim())
    .filter(Boolean);

  return blocks.map((block) => {
    const lines = block.split('\n');
    if (lines.length > 1 && /^\d+\./.test(lines[0])) {
      return {
        heading: lines[0],
        body: lines.slice(1).join('\n'),
      };
    }
    return {
      heading: null,
      body: block,
    };
  });
});

const contarPorCategoria = (catSlug) => {
  return articles.value.filter((a) => a.category.slug === catSlug).length;
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedType.value = 'all';
  selectedCategory.value = null;
  selectedTag.value = null;
};

const abrirArticulo = (article) => {
  activeArticle.value = article;
  router.push({ path: `/explorar/wiki/${article.slug}` });
};

const cerrarLectura = () => {
  activeArticle.value = null;
  router.push({ path: '/explorar/wiki' });
};

const salirModoLecturaSiBusca = (val) => {
  if (val && activeArticle.value) {
    activeArticle.value = null;
    router.replace({ path: '/explorar/wiki', query: { q: val } });
  }
};

const filtrarPorCategoriaDesdeLectura = (catSlug) => {
  activeArticle.value = null;
  selectedCategory.value = catSlug;
  router.push({ path: '/explorar/wiki' });
};

const filtrarPorTagDesdeLectura = (tagSlug) => {
  activeArticle.value = null;
  selectedTag.value = tagSlug;
  router.push({ path: '/explorar/wiki' });
};

const copiarEnlaceArticulo = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href);
    linkCopied.value = true;
    setTimeout(() => {
      linkCopied.value = false;
    }, 2000);
  } catch (e) {
    linkCopied.value = false;
  }
};

const scrollToSection = (elementId) => {
  const el = document.getElementById(elementId);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};

const syncArticleFromRoute = () => {
  const slugParam = route.params.slug;
  if (slugParam) {
    const found = articles.value.find((a) => a.slug === slugParam);
    if (found) {
      activeArticle.value = found;
    }
  } else {
    activeArticle.value = null;
  }
};

watch(() => route.params.slug, syncArticleFromRoute);

watch(
  () => route.query.q,
  (newQ) => {
    if (newQ !== undefined) {
      searchQuery.value = String(newQ || '');
    }
  }
);

onMounted(async () => {
  if (route.query.q) {
    searchQuery.value = String(route.query.q);
  }
  if (route.query.tag) {
    selectedTag.value = String(route.query.tag);
  }

  try {
    const [artRes, catRes, tagRes] = await Promise.all([
      obtenerArticulos({ is_published: true }),
      obtenerCategorias({ estado: true }),
      obtenerTags({ estado: true }),
    ]);

    if (catRes.data?.data?.length) {
      categoryOptions.value = catRes.data.data;
    }
    if (tagRes.data?.data?.length) {
      tagOptions.value = tagRes.data.data;
    }
    if (artRes.data?.data?.length) {
      articles.value = artRes.data.data.map((a) => ({
        id: a.id,
        type: a.type === 'tutorial' || a.type === 'Tutorial' ? 'Tutorial' : 'Artículo',
        title: a.title,
        slug: a.slug,
        category: a.category || { id: a.category_id, name: 'General', slug: 'general' },
        author: {
          id: a.author?.id,
          name: a.author?.name || 'Autor',
          role: a.author?.role || 'Docente',
        },
        is_published: Boolean(a.is_published),
        created_at: String(a.created_at || '').slice(0, 10),
        tags: a.tags || [],
        excerpt: a.summary || String(a.content || '').slice(0, 160),
        content: a.content,
      }));
    }
  } catch (e) {
    // Mantiene respaldo local si la API no está corriendo aún
  }

  syncArticleFromRoute();
});
</script>

<style scoped>
.wiki-hero {
  background: linear-gradient(135deg, #0d1239 0%, #1f2d7f 100%);
}

.article-card {
  cursor: pointer;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.article-card:hover {
  transform: translateY(-3px);
}

@media (min-width: 960px) {
  .sticky-panel {
    position: sticky;
    top: 88px;
  }
}
</style>
