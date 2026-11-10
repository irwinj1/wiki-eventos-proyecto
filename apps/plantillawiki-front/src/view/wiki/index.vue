<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Encabezado de Gestión de la Wiki (Tutoriales y Artículos) -->
    <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
      <div class="d-flex flex-column flex-md-row align-start align-md-center justify-space-between ga-4">
        <div>
          <div class="d-flex flex-wrap ga-2 mb-2">
            <v-chip size="x-small" color="primary" variant="flat" prepend-icon="mdi-book-edit-outline">
              Gestión Editorial de Wiki y Tutoriales
            </v-chip>
          </div>
          <h1 class="text-h6 text-sm-h5 font-weight-bold text-primary">
            Artículos y Tutoriales de la Wiki (`wiki_articles`)
          </h1>
          <p class="text-body-2 text-medium-emphasis">
            Crea, edita, clasifica por categoría (`category_id`), etiqueta (`article_tag`) y publica/despublica tutoriales y artículos técnicos.
          </p>
        </div>

        <div class="d-flex flex-column flex-sm-row flex-wrap ga-2 w-100 w-md-auto">
          <v-btn
            to="/explorar/wiki"
            variant="outlined"
            color="secondary"
            class="w-100 w-sm-auto"
            prepend-icon="mdi-earth"
          >
            Ver Vista Pública
          </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            class="w-100 w-sm-auto"
            prepend-icon="mdi-plus"
            @click="abrirEditor()"
          >
            Nuevo Tutorial / Artículo
          </v-btn>
        </div>
      </div>

      <v-divider class="my-4" />

      <!-- Filtros de la Wiki Interna -->
      <v-row dense>
        <v-col cols="12" md="4">
          <v-text-field
            v-model="filters.search"
            placeholder="Buscar por título, slug o autor..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="filters.categoryId"
            :items="categories"
            item-title="name"
            item-value="id"
            label="Categoría (`category_id`)"
            prepend-inner-icon="mdi-shape-outline"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="filters.tagSlug"
            :items="tags"
            item-title="name"
            item-value="slug"
            label="Etiqueta (`tag_id`)"
            prepend-inner-icon="mdi-tag-outline"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" sm="4" md="2">
          <v-select
            v-model="filters.published"
            :items="[
              { title: 'Todos', value: 'all' },
              { title: 'Publicados', value: true },
              { title: 'Borradores', value: false },
            ]"
            label="Estado"
            variant="outlined"
            density="compact"
            hide-details
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- Tabla de Gestión de Artículos y Tutoriales -->
    <v-card rounded="xl" elevation="1" class="pa-4 border">
      <data-table-component
        :headers="headers"
        :items="filteredArticles"
        :page="1"
        :total-page="1"
      >
        <template v-slot:item.title="{ item }">
          <div class="py-2">
            <div class="d-flex align-center ga-2">
              <v-chip
                size="x-small"
                :color="item.type === 'Tutorial' ? 'info' : 'secondary'"
                variant="tonal"
              >
                {{ item.type }}
              </v-chip>
              <span class="font-weight-bold">{{ item.title }}</span>
            </div>
            <div class="text-caption text-medium-emphasis">
              Slug: <code>{{ item.slug }}</code>
            </div>
          </div>
        </template>

        <template v-slot:item.category="{ item }">
          <v-chip size="small" color="primary" variant="tonal">
            {{ item.category.name }}
          </v-chip>
        </template>

        <template v-slot:item.tags="{ item }">
          <div class="d-flex flex-wrap ga-1">
            <v-chip
              v-for="t in item.tags"
              :key="t.slug"
              size="x-small"
              variant="outlined"
            >
              #{{ t.name }}
            </v-chip>
          </div>
        </template>

        <template v-slot:item.is_published="{ item }">
          <v-chip
            size="small"
            :color="item.is_published ? 'success' : 'warning'"
            variant="tonal"
          >
            {{ item.is_published ? 'Publicado' : 'Borrador' }}
          </v-chip>
        </template>

        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center ga-1">
            <v-btn
              v-tooltip:bottom="'Vista previa'"
              icon="mdi-eye-outline"
              size="small"
              variant="text"
              color="info"
              @click="previsualizar(item)"
            />
            <v-btn
              v-tooltip:bottom="'Editar contenido'"
              icon="mdi-pencil-outline"
              size="small"
              variant="text"
              color="primary"
              @click="abrirEditor(item)"
            />
            <v-btn
              v-tooltip:bottom="item.is_published ? 'Despublicar' : 'Publicar'"
              :icon="item.is_published ? 'mdi-eye-off-outline' : 'mdi-publish'"
              size="small"
              variant="text"
              :color="item.is_published ? 'warning' : 'success'"
              @click="item.is_published = !item.is_published"
            />
          </div>
        </template>
      </data-table-component>
    </v-card>

    <!-- Modal Editor de Tutorial / Artículo -->
    <v-dialog v-model="editorDialog" max-width="760" persistent scrollable>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white">
          {{ articleForm.id ? 'Editar Tutorial / Artículo' : 'Redactar Nuevo Tutorial / Artículo' }}
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row dense>
            <v-col cols="12" sm="4">
              <v-select
                v-model="articleForm.type"
                :items="['Tutorial', 'Artículo']"
                label="Tipo de Publicación"
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" sm="8">
              <v-select
                v-model="articleForm.category_id"
                :items="categories"
                item-title="name"
                item-value="id"
                label="Categoría (`category_id`)"
                variant="outlined"
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="articleForm.title"
                label="Título del Tutorial o Artículo (`title`)"
                variant="outlined"
                @update:model-value="actualizarSlug"
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="articleForm.slug"
                label="Slug URL único (`slug`)"
                variant="outlined"
                readonly
              />
            </v-col>

            <v-col cols="12">
              <v-select
                v-model="articleForm.tagSlugs"
                :items="tags"
                item-title="name"
                item-value="slug"
                label="Etiquetas asociadas (`article_tag`)"
                multiple
                chips
                variant="outlined"
              />
            </v-col>

            <v-col cols="12">
              <div class="text-subtitle-2 font-weight-bold text-primary mb-2 d-flex align-center justify-space-between flex-wrap ga-2">
                <span>Contenido Enriquecido del Tutorial / Artículo (`content`)</span>
                <v-chip size="x-small" color="info" variant="tonal" prepend-icon="mdi-format-text">
                  Editor WYSIWYG Activo (Títulos, Listas, Código, Enlaces)
                </v-chip>
              </div>
              <RichTextEditor v-model="articleForm.content" />
            </v-col>

            <v-col cols="12">
              <v-switch
                v-model="articleForm.is_published"
                color="success"
                label="Publicar inmediatamente en el portal público (`is_published`)"
                hide-details
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="editorDialog = false">Cancelar</v-btn>
          <v-btn color="primary" variant="flat" @click="guardarArticulo">
            Guardar Publicación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Previsualización -->
    <v-dialog v-model="previewDialog" max-width="760">
      <v-card v-if="selectedArticle" rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white">
          {{ selectedArticle.title }}
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="d-flex flex-wrap ga-2 mb-4">
            <v-chip size="small" color="primary">{{ selectedArticle.category.name }}</v-chip>
            <v-chip
              v-for="t in selectedArticle.tags"
              :key="t.slug"
              size="small"
              variant="outlined"
            >
              #{{ t.name }}
            </v-chip>
          </div>
          <div
            class="wiki-rich-render text-body-1"
            v-html="formatRichPreview(selectedArticle.content)"
          />
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn color="primary" variant="tonal" @click="previewDialog = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DataTableComponent from '../../components/DataTableComponent.vue';
import RichTextEditor from '../../components/RichTextEditor.vue';
import {
  obtenerArticulos,
  crearArticulo,
  actualizarArticulo,
  obtenerCategorias,
  obtenerTags,
} from '../../services/wiki/wiki.service.js';
import { useAlertStore } from '../../store/useAlertStore.js';

const alertStore = useAlertStore();

const filters = ref({
  search: '',
  categoryId: null,
  tagSlug: null,
  published: 'all',
});

const editorDialog = ref(false);
const previewDialog = ref(false);
const selectedArticle = ref(null);

const headers = [
  { title: 'Título / Slug', key: 'title' },
  { title: 'Categoría', key: 'category' },
  { title: 'Autor', key: 'author_name' },
  { title: 'Etiquetas', key: 'tags' },
  { title: 'Publicación', key: 'is_published' },
  { title: 'Acciones', key: 'acciones' },
];

const categories = ref([
  { id: 1, name: 'Desarrollo Backend', slug: 'desarrollo-backend' },
  { id: 2, name: 'Frontend Moderno', slug: 'frontend-moderno' },
  { id: 3, name: 'Base de Datos y SQL', slug: 'base-de-datos-sql' },
  { id: 4, name: 'Estándares de Proyectos', slug: 'estandares-proyectos' },
]);

const tags = ref([
  { id: 1, name: 'laravel', slug: 'laravel' },
  { id: 2, name: 'vue3', slug: 'vue3' },
  { id: 3, name: 'docker', slug: 'docker' },
  { id: 4, name: 'postgresql', slug: 'postgresql' },
  { id: 5, name: 'git', slug: 'git' },
]);

const articlesList = ref([
  {
    id: 1,
    type: 'Tutorial',
    title: 'Guía paso a paso: Configuración de APIs RESTful en Laravel 12 con JWT',
    slug: 'guia-configuracion-apis-restful-laravel-12-jwt',
    category: { id: 1, name: 'Desarrollo Backend' },
    author_name: 'Ing. Carlos Méndez',
    is_published: true,
    tags: [
      { name: 'laravel', slug: 'laravel' },
      { name: 'docker', slug: 'docker' },
    ],
    content: 'Contenido detallado paso a paso para configurar autenticación JWT y permisos en Laravel 12.',
  },
  {
    id: 2,
    type: 'Artículo',
    title: 'Arquitectura Modular en Vue 3 con Vuetify 4 y Pinia',
    slug: 'arquitectura-modular-vue-3-vuetify-4-pinia',
    category: { id: 2, name: 'Frontend Moderno' },
    author_name: 'Dra. Elena Ríos',
    is_published: true,
    tags: [{ name: 'vue3', slug: 'vue3' }],
    content: 'Estructura de carpetas recomendada para mantener escalabilidad en aplicaciones universitarias.',
  },
  {
    id: 3,
    type: 'Tutorial',
    title: 'Normativa de entrega de repositorios Git para proyectos FESOL',
    slug: 'normativa-entrega-repositorios-git-fesol',
    category: { id: 4, name: 'Estándares de Proyectos' },
    author_name: 'Ing. Roberto Morales',
    is_published: false,
    tags: [{ name: 'git', slug: 'git' }],
    content: 'Borrador de lineamientos técnicos para los equipos estudiantiles del ciclo actual.',
  },
]);

const articleForm = ref({
  id: null,
  type: 'Tutorial',
  category_id: 1,
  title: '',
  slug: '',
  tagSlugs: ['laravel'],
  content: '',
  is_published: true,
});

const filteredArticles = computed(() => {
  const q = filters.value.search?.trim().toLowerCase();
  return articlesList.value.filter((a) => {
    if (filters.value.categoryId && a.category.id !== filters.value.categoryId) return false;
    if (filters.value.tagSlug && !a.tags.some((t) => t.slug === filters.value.tagSlug)) return false;
    if (filters.value.published !== 'all' && a.is_published !== filters.value.published) return false;

    if (q) {
      return (
        a.title.toLowerCase().includes(q) ||
        a.slug.toLowerCase().includes(q) ||
        a.author_name.toLowerCase().includes(q)
      );
    }
    return true;
  });
});

const actualizarSlug = (val) => {
  articleForm.value.slug = String(val || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');
};

const abrirEditor = (item = null) => {
  if (item) {
    articleForm.value = {
      id: item.id,
      type: item.type,
      category_id: item.category.id,
      title: item.title,
      slug: item.slug,
      tagSlugs: item.tags.map((t) => t.slug),
      content: item.content,
      is_published: item.is_published,
    };
  } else {
    articleForm.value = {
      id: null,
      type: 'Tutorial',
      category_id: 1,
      title: '',
      slug: '',
      tagSlugs: ['laravel'],
      content: '',
      is_published: true,
    };
  }
  editorDialog.value = true;
};

const guardarArticulo = async () => {
  if (!articleForm.value.title.trim()) return;
  const cat = categories.value.find((c) => c.id === articleForm.value.category_id) || categories.value[0];
  const selectedTags = tags.value.filter((t) => articleForm.value.tagSlugs.includes(t.slug));
  const tagIds = selectedTags.map((t) => t.id);

  try {
    const payload = {
      type: articleForm.value.type,
      category_id: articleForm.value.category_id,
      title: articleForm.value.title,
      content: articleForm.value.content,
      is_published: articleForm.value.is_published,
      tags: tagIds,
    };

    if (articleForm.value.id) {
      await actualizarArticulo(articleForm.value.id, payload);
    } else {
      await crearArticulo(payload);
    }
    await cargarWiki();
  } catch (e) {
    if (articleForm.value.id) {
      const idx = articlesList.value.findIndex((a) => a.id === articleForm.value.id);
      if (idx !== -1) {
        articlesList.value[idx] = {
          ...articlesList.value[idx],
          type: articleForm.value.type,
          title: articleForm.value.title,
          slug: articleForm.value.slug,
          category: cat,
          tags: selectedTags,
          content: articleForm.value.content,
          is_published: articleForm.value.is_published,
        };
      }
    } else {
      articlesList.value.unshift({
        id: Date.now(),
        type: articleForm.value.type,
        title: articleForm.value.title,
        slug: articleForm.value.slug,
        category: cat,
        author_name: 'Usuario Actual',
        tags: selectedTags,
        content: articleForm.value.content,
        is_published: articleForm.value.is_published,
      });
    }
  }
  editorDialog.value = false;
  alertStore.showAlert('Publicación de Wiki guardada correctamente.', 'success');
};

const previsualizar = (item) => {
  selectedArticle.value = item;
  previewDialog.value = true;
};

const formatRichPreview = (raw) => {
  if (!raw) return '';
  const cleaned = String(raw).replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
  if (/<[a-z][\s\S]*>/i.test(cleaned)) {
    return cleaned;
  }
  return cleaned.replace(/\n/g, '<br>');
};

const cargarWiki = async () => {
  try {
    const [artRes, catRes, tagRes] = await Promise.all([
      obtenerArticulos(),
      obtenerCategorias(),
      obtenerTags(),
    ]);

    if (catRes.data?.data?.length) {
      categories.value = catRes.data.data;
    }
    if (tagRes.data?.data?.length) {
      tags.value = tagRes.data.data;
    }
    if (artRes.data?.data?.length) {
      articlesList.value = artRes.data.data.map((a) => ({
        id: a.id,
        type: a.type === 'tutorial' || a.type === 'Tutorial' ? 'Tutorial' : 'Artículo',
        title: a.title,
        slug: a.slug,
        category: a.category || { id: a.category_id, name: 'General' },
        author_name: a.author?.name || 'Autor',
        is_published: Boolean(a.is_published),
        tags: a.tags || [],
        content: a.content,
      }));
    }
  } catch (e) {
    // Mantiene respaldo si la API no está corriendo aún
  }
};

onMounted(async () => {
  await cargarWiki();
});
</script>
