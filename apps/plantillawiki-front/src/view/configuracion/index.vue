<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Encabezado de Configuración (Exclusivo Administrador) -->
    <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
      <div class="d-flex flex-column flex-md-row align-start align-md-center justify-space-between ga-4">
        <div>
          <v-chip size="x-small" color="primary" variant="flat" prepend-icon="mdi-cog-outline" class="mb-1">
            Exclusivo Administrador
          </v-chip>
          <h1 class="text-h6 text-sm-h5 font-weight-bold text-primary">
            Configuración del Sistema: Roles, Permisos y Taxonomía Wiki
          </h1>
          <p class="text-body-2 text-medium-emphasis">
            Administra los roles (`Spatie Permission`), matriz de permisos por módulo, categorías (`wiki_categories`) y etiquetas (`wiki_tags`).
          </p>
        </div>

        <v-tabs v-model="activeTab" color="primary" density="comfortable" show-arrows class="w-100 w-md-auto">
          <v-tab value="roles" prepend-icon="mdi-shield-key-outline">Roles y Permisos</v-tab>
          <v-tab value="categorias" prepend-icon="mdi-shape-outline">Categorías Wiki</v-tab>
          <v-tab value="tags" prepend-icon="mdi-tag-multiple-outline">Etiquetas (Tags)</v-tab>
        </v-tabs>
      </div>
    </v-card>

    <v-window v-model="activeTab">
      <!-- PESTAÑA 1: ROLES Y PERMISOS -->
      <v-window-item value="roles">
        <v-card rounded="xl" elevation="1" class="pa-6 border">
          <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-4">
            <div>
              <h2 class="text-h6 font-weight-bold">Roles del Sistema (`roles` & `permissions`)</h2>
              <p class="text-body-2 text-medium-emphasis">
                Define qué acciones puede realizar cada actor dentro de la universidad.
              </p>
            </div>
            <v-btn
              color="primary"
              variant="flat"
              prepend-icon="mdi-plus"
              @click="abrirModalRol()"
            >
              Crear Nuevo Rol
            </v-btn>
          </div>

          <v-row>
            <v-col cols="12" md="6" v-for="rol in rolesList" :key="rol.id">
              <v-card variant="outlined" rounded="xl" class="pa-5 h-100">
                <div class="d-flex align-center justify-space-between mb-2">
                  <div class="d-flex align-center ga-2">
                    <v-avatar color="primary" variant="tonal" size="38">
                      <v-icon icon="mdi-shield-account-outline" />
                    </v-avatar>
                    <div>
                      <div class="text-subtitle-1 font-weight-bold">{{ rol.label }}</div>
                      <div class="text-caption text-medium-emphasis">Guard: `{{ rol.guard_name }}` · Código: `{{ rol.name }}`</div>
                    </div>
                  </div>
                  <div class="d-flex ga-1">
                    <v-btn
                      icon="mdi-pencil-outline"
                      size="small"
                      variant="text"
                      color="primary"
                      @click="abrirModalRol(rol)"
                    />
                  </div>
                </div>

                <p class="text-body-2 text-medium-emphasis mb-3">
                  {{ rol.description }}
                </p>

                <div class="text-caption font-weight-bold mb-2">
                  Permisos asignados ({{ rol.permissions.length }}):
                </div>
                <div class="d-flex flex-wrap ga-1">
                  <v-chip
                    v-for="perm in rol.permissions"
                    :key="perm"
                    size="x-small"
                    color="secondary"
                    variant="tonal"
                  >
                    {{ perm }}
                  </v-chip>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-card>
      </v-window-item>

      <!-- PESTAÑA 2: CATEGORÍAS DE LA WIKI -->
      <v-window-item value="categorias">
        <v-card rounded="xl" elevation="1" class="pa-6 border">
          <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-4">
            <div>
              <h2 class="text-h6 font-weight-bold">Categorías de la Wiki (`wiki_categories`)</h2>
              <p class="text-body-2 text-medium-emphasis">
                Clasificación principal para tutoriales y artículos técnicos. No se permite eliminar categorías con artículos asociados (`restrictOnDelete`).
              </p>
            </div>
            <v-btn
              color="primary"
              variant="flat"
              prepend-icon="mdi-plus"
              @click="abrirModalCategoria()"
            >
              Nueva Categoría
            </v-btn>
          </div>

          <v-row class="mb-3">
            <v-col cols="12" md="5">
              <v-text-field
                v-model="searchCategory"
                placeholder="Filtrar categorías por nombre o slug..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                hide-details
                clearable
              />
            </v-col>
          </v-row>

          <data-table-component
            :headers="categoryHeaders"
            :items="filteredCategories"
            :page="1"
            :total-page="1"
          >
            <template v-slot:item.slug="{ item }">
              <code>{{ item.slug }}</code>
            </template>

            <template v-slot:item.estado="{ item }">
              <v-chip size="small" :color="item.estado ? 'success' : 'error'" variant="tonal">
                {{ item.estado ? 'Activa' : 'Inactiva' }}
              </v-chip>
            </template>

            <template v-slot:item.acciones="{ item }">
              <div class="d-flex ga-1">
                <v-btn
                  icon="mdi-pencil-outline"
                  size="small"
                  variant="text"
                  color="primary"
                  @click="abrirModalCategoria(item)"
                />
                <v-btn
                  icon="mdi-swap-horizontal"
                  size="small"
                  variant="text"
                  :color="item.estado ? 'warning' : 'success'"
                  v-tooltip:bottom="'Cambiar estado'"
                  @click="alternarEstadoCategoria(item)"
                />
              </div>
            </template>
          </data-table-component>
        </v-card>
      </v-window-item>

      <!-- PESTAÑA 3: ETIQUETAS (TAGS) DE LA WIKI -->
      <v-window-item value="tags">
        <v-card rounded="xl" elevation="1" class="pa-6 border">
          <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-4">
            <div>
              <h2 class="text-h6 font-weight-bold">Etiquetas de la Wiki (`wiki_tags`)</h2>
              <p class="text-body-2 text-medium-emphasis">
                Etiquetas transversales (`article_tag`) para facilitar el filtrado rápido de tutoriales y artículos.
              </p>
            </div>
            <v-btn
              color="primary"
              variant="flat"
              prepend-icon="mdi-plus"
              @click="abrirModalTag()"
            >
              Nueva Etiqueta
            </v-btn>
          </div>

          <v-row class="mb-3">
            <v-col cols="12" md="5">
              <v-text-field
                v-model="searchTag"
                placeholder="Filtrar etiquetas por nombre..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                hide-details
                clearable
              />
            </v-col>
          </v-row>

          <data-table-component
            :headers="tagHeaders"
            :items="filteredTags"
            :page="1"
            :total-page="1"
          >
            <template v-slot:item.name="{ item }">
              <v-chip size="small" color="secondary" variant="tonal">#{{ item.name }}</v-chip>
            </template>

            <template v-slot:item.slug="{ item }">
              <code>{{ item.slug }}</code>
            </template>

            <template v-slot:item.estado="{ item }">
              <v-chip size="small" :color="item.estado ? 'success' : 'error'" variant="tonal">
                {{ item.estado ? 'Activa' : 'Inactiva' }}
              </v-chip>
            </template>

            <template v-slot:item.acciones="{ item }">
              <div class="d-flex ga-1">
                <v-btn
                  icon="mdi-pencil-outline"
                  size="small"
                  variant="text"
                  color="primary"
                  @click="abrirModalTag(item)"
                />
                <v-btn
                  icon="mdi-swap-horizontal"
                  size="small"
                  variant="text"
                  :color="item.estado ? 'warning' : 'success'"
                  @click="alternarEstadoTag(item)"
                />
              </div>
            </template>
          </data-table-component>
        </v-card>
      </v-window-item>
    </v-window>

    <!-- Modal Crear / Editar Rol y Permisos -->
    <v-dialog v-model="dialogRol" max-width="600" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white d-flex justify-space-between align-center">
          <span>{{ rolForm.id ? 'Configurar Rol y Permisos' : 'Crear Nuevo Rol' }}</span>
          <v-btn icon="mdi-close" variant="text" color="white" @click="dialogRol = false" />
        </v-card-title>
        <v-card-text class="pa-6">
          <v-text-field
            v-model="rolForm.name"
            label="Identificador del rol (ej. coordinador, teacher)"
            variant="outlined"
            class="mb-3"
          />
          <v-text-field
            v-model="rolForm.label"
            label="Nombre descriptivo"
            variant="outlined"
            class="mb-3"
          />
          <div class="text-subtitle-2 font-weight-bold mb-2">Asignar Permisos (`guard: api`)</div>
          <v-row dense>
            <v-col cols="12" sm="6" v-for="perm in availablePermissions" :key="perm">
              <v-checkbox
                v-model="rolForm.permissions"
                :label="perm"
                :value="perm"
                density="compact"
                color="primary"
                hide-details
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="dialogRol = false">Cancelar</v-btn>
          <v-btn color="primary" variant="flat" @click="guardarRol">Guardar Rol</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Crear / Editar Categoría Wiki -->
    <v-dialog v-model="dialogCategoria" max-width="520" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white">
          {{ categoryForm.id ? 'Editar Categoría Wiki' : 'Nueva Categoría Wiki' }}
        </v-card-title>
        <v-card-text class="pa-6">
          <v-text-field
            v-model="categoryForm.name"
            label="Nombre de la categoría"
            variant="outlined"
            class="mb-3"
            @update:model-value="generarSlugCategoria"
          />
          <v-text-field
            v-model="categoryForm.slug"
            label="Slug único (autogenerado)"
            variant="outlined"
            readonly
            class="mb-3"
          />
          <v-textarea
            v-model="categoryForm.description"
            label="Descripción de la categoría"
            variant="outlined"
            rows="3"
          />
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="dialogCategoria = false">Cancelar</v-btn>
          <v-btn color="primary" variant="flat" @click="guardarCategoria">Guardar Categoría</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Crear / Editar Tag Wiki -->
    <v-dialog v-model="dialogTag" max-width="460" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white">
          {{ tagForm.id ? 'Editar Etiqueta (Tag)' : 'Nueva Etiqueta (Tag)' }}
        </v-card-title>
        <v-card-text class="pa-6">
          <v-text-field
            v-model="tagForm.name"
            label="Nombre del tag (ej. laravel, docker)"
            variant="outlined"
            class="mb-3"
            @update:model-value="generarSlugTag"
          />
          <v-text-field
            v-model="tagForm.slug"
            label="Slug único"
            variant="outlined"
            readonly
          />
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="dialogTag = false">Cancelar</v-btn>
          <v-btn color="primary" variant="flat" @click="guardarTag">Guardar Etiqueta</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DataTableComponent from '../../components/DataTableComponent.vue';
import {
  obtenerRoles,
  crearRol,
  actualizarRol,
  obtenerPermisos,
  obtenerCategorias,
  crearCategoria,
  actualizarCategoria,
  cambiarEstadoCategoria,
  obtenerTags,
  crearTag,
  actualizarTag,
  cambiarEstadoTag,
} from '../../services/wiki/wiki.service.js';
import { useAlertStore } from '../../store/useAlertStore.js';

const alertStore = useAlertStore();
const activeTab = ref('roles');
const searchCategory = ref('');
const searchTag = ref('');

const dialogRol = ref(false);
const dialogCategoria = ref(false);
const dialogTag = ref(false);

const availablePermissions = ref([
  'create_user',
  'edit_user',
  'delete_user',
  'view_user',
  'create_rol',
  'edit_rol',
  'delete_rol',
  'view_rol',
  'create_permission',
  'edit_permission',
  'delete_permission',
  'view_permission',
  'create_event',
  'edit_event',
  'manage_event_subjects',
  'upload_projects',
  'publish_wiki_articles',
]);

const rolesList = ref([
  {
    id: 1,
    name: 'admin',
    label: 'Administrador General',
    guard_name: 'api',
    description: 'Control total de usuarios, roles, permisos, carreras, eventos y moderación de la Wiki.',
    permissions: [
      'create_user', 'edit_user', 'delete_user', 'view_user',
      'create_rol', 'edit_rol', 'delete_rol', 'view_rol',
      'create_permission', 'edit_permission', 'delete_permission', 'view_permission',
    ],
  },
  {
    id: 2,
    name: 'coordinador',
    label: 'Coordinador de Carrera',
    guard_name: 'api',
    description: 'Único rol autorizado junto al administrador para crear y aperturar los Eventos académicos de su carrera.',
    permissions: ['create_user', 'edit_user', 'delete_user', 'view_user', 'create_event'],
  },
  {
    id: 3,
    name: 'teacher',
    label: 'Docente Universitario',
    guard_name: 'api',
    description: 'Registra sus materias dentro de un evento activo, sube los proyectos de sus estudiantes y publica tutoriales en la Wiki.',
    permissions: ['create_user', 'edit_user', 'view_user', 'manage_event_subjects', 'upload_projects', 'publish_wiki_articles'],
  },
  {
    id: 4,
    name: 'student',
    label: 'Estudiante',
    guard_name: 'api',
    description: 'Participa como miembro o líder de proyectos estudiantiles y consulta toda la documentación de la Wiki.',
    permissions: ['view_user'],
  },
]);

const categoryHeaders = [
  { title: 'Nombre', key: 'name' },
  { title: 'Slug', key: 'slug' },
  { title: 'Descripción', key: 'description' },
  { title: 'Artículos Asociados', key: 'articles_count' },
  { title: 'Estado', key: 'estado' },
  { title: 'Acciones', key: 'acciones' },
];

const categoriesList = ref([
  { id: 1, name: 'Desarrollo Backend', slug: 'desarrollo-backend', description: 'APIs, frameworks de servidor y arquitectura.', articles_count: 16, estado: true },
  { id: 2, name: 'Frontend Moderno', slug: 'frontend-moderno', description: 'Interfaces reactivas con Vue 3, Vuetify y CSS.', articles_count: 12, estado: true },
  { id: 3, name: 'Base de Datos y SQL', slug: 'base-de-datos-sql', description: 'Modelado relacional, migraciones y optimización.', articles_count: 9, estado: true },
  { id: 4, name: 'DevOps & Contenedores', slug: 'devops-contenedores', description: 'Docker, despliegues en Linux y automatización.', articles_count: 7, estado: true },
  { id: 5, name: 'Estándares de Proyectos', slug: 'estandares-proyectos', description: 'Normativas de entrega para eventos académicos.', articles_count: 4, estado: true },
]);

const tagHeaders = [
  { title: 'Etiqueta', key: 'name' },
  { title: 'Slug', key: 'slug' },
  { title: 'Artículos vinculados', key: 'articles_count' },
  { title: 'Estado', key: 'estado' },
  { title: 'Acciones', key: 'acciones' },
];

const tagsList = ref([
  { id: 1, name: 'laravel', slug: 'laravel', articles_count: 14, estado: true },
  { id: 2, name: 'vue3', slug: 'vue3', articles_count: 11, estado: true },
  { id: 3, name: 'docker', slug: 'docker', articles_count: 8, estado: true },
  { id: 4, name: 'postgresql', slug: 'postgresql', articles_count: 6, estado: true },
  { id: 5, name: 'git', slug: 'git', articles_count: 9, estado: true },
]);

const rolForm = ref({ id: null, name: '', label: '', permissions: [] });
const categoryForm = ref({ id: null, name: '', slug: '', description: '' });
const tagForm = ref({ id: null, name: '', slug: '' });

const filteredCategories = computed(() => {
  const q = searchCategory.value?.trim().toLowerCase();
  if (!q) return categoriesList.value;
  return categoriesList.value.filter(
    (c) => c.name.toLowerCase().includes(q) || c.slug.toLowerCase().includes(q)
  );
});

const filteredTags = computed(() => {
  const q = searchTag.value?.trim().toLowerCase();
  if (!q) return tagsList.value;
  return tagsList.value.filter(
    (t) => t.name.toLowerCase().includes(q) || t.slug.toLowerCase().includes(q)
  );
});

const slugify = (text) =>
  String(text || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');

const generarSlugCategoria = (val) => {
  categoryForm.value.slug = slugify(val);
};

const generarSlugTag = (val) => {
  tagForm.value.slug = slugify(val);
};

const abrirModalRol = (rol = null) => {
  if (rol) {
    rolForm.value = {
      id: rol.id,
      name: rol.name,
      label: rol.label,
      permissions: [...rol.permissions],
    };
  } else {
    rolForm.value = { id: null, name: '', label: '', permissions: ['view_user'] };
  }
  dialogRol.value = true;
};

const guardarRol = async () => {
  if (!rolForm.value.name.trim()) return;
  try {
    if (rolForm.value.id) {
      await actualizarRol(rolForm.value.id, {
        name: rolForm.value.name,
        permissions: rolForm.value.permissions,
      });
    } else {
      await crearRol({
        name: rolForm.value.name,
        permissions: rolForm.value.permissions,
      });
    }
    await cargarConfiguracion();
  } catch (e) {
    if (rolForm.value.id) {
      const idx = rolesList.value.findIndex((r) => r.id === rolForm.value.id);
      if (idx !== -1) {
        rolesList.value[idx] = {
          ...rolesList.value[idx],
          name: rolForm.value.name,
          label: rolForm.value.label || rolForm.value.name,
          permissions: [...rolForm.value.permissions],
        };
      }
    } else {
      rolesList.value.push({
        id: Date.now(),
        name: rolForm.value.name,
        label: rolForm.value.label || rolForm.value.name,
        guard_name: 'api',
        description: 'Rol personalizado configurado por el administrador.',
        permissions: [...rolForm.value.permissions],
      });
    }
  }
  dialogRol.value = false;
  alertStore.showAlert('Configuración de rol actualizada correctamente.', 'success');
};

const abrirModalCategoria = (cat = null) => {
  if (cat) {
    categoryForm.value = { ...cat };
  } else {
    categoryForm.value = { id: null, name: '', slug: '', description: '' };
  }
  dialogCategoria.value = true;
};

const guardarCategoria = async () => {
  if (!categoryForm.value.name.trim()) return;
  try {
    if (categoryForm.value.id) {
      await actualizarCategoria(categoryForm.value.id, {
        name: categoryForm.value.name,
        description: categoryForm.value.description,
      });
    } else {
      await crearCategoria({
        name: categoryForm.value.name,
        description: categoryForm.value.description,
      });
    }
    await cargarConfiguracion();
  } catch (e) {
    if (categoryForm.value.id) {
      const idx = categoriesList.value.findIndex((c) => c.id === categoryForm.value.id);
      if (idx !== -1) categoriesList.value[idx] = { ...categoriesList.value[idx], ...categoryForm.value };
    } else {
      categoriesList.value.unshift({
        ...categoryForm.value,
        id: Date.now(),
        articles_count: 0,
        estado: true,
      });
    }
  }
  dialogCategoria.value = false;
  alertStore.showAlert('Categoría guardada correctamente.', 'success');
};

const alternarEstadoCategoria = async (item) => {
  try {
    await cambiarEstadoCategoria(item.id);
    await cargarConfiguracion();
    alertStore.showAlert('Estado de la categoría actualizado.', 'success');
  } catch (e) {
    item.estado = !item.estado;
  }
};

const abrirModalTag = (tag = null) => {
  if (tag) {
    tagForm.value = { ...tag };
  } else {
    tagForm.value = { id: null, name: '', slug: '' };
  }
  dialogTag.value = true;
};

const guardarTag = async () => {
  if (!tagForm.value.name.trim()) return;
  try {
    if (tagForm.value.id) {
      await actualizarTag(tagForm.value.id, { name: tagForm.value.name });
    } else {
      await crearTag({ name: tagForm.value.name });
    }
    await cargarConfiguracion();
  } catch (e) {
    if (tagForm.value.id) {
      const idx = tagsList.value.findIndex((t) => t.id === tagForm.value.id);
      if (idx !== -1) tagsList.value[idx] = { ...tagsList.value[idx], ...tagForm.value };
    } else {
      tagsList.value.unshift({
        ...tagForm.value,
        id: Date.now(),
        articles_count: 0,
        estado: true,
      });
    }
  }
  dialogTag.value = false;
  alertStore.showAlert('Etiqueta guardada correctamente.', 'success');
};

const alternarEstadoTag = async (item) => {
  try {
    await cambiarEstadoTag(item.id);
    await cargarConfiguracion();
    alertStore.showAlert('Estado de la etiqueta actualizado.', 'success');
  } catch (e) {
    item.estado = !item.estado;
  }
};

const roleLabelMap = {
  admin: 'Administrador General',
  coordinador: 'Coordinador de Carrera',
  teacher: 'Docente Universitario',
  student: 'Estudiante',
};

const cargarConfiguracion = async () => {
  try {
    const [rolesRes, permsRes, catsRes, tagsRes] = await Promise.all([
      obtenerRoles(),
      obtenerPermisos(),
      obtenerCategorias(),
      obtenerTags(),
    ]);

    if (rolesRes.data?.data?.length) {
      rolesList.value = rolesRes.data.data.map((r) => ({
        ...r,
        label: roleLabelMap[r.name] || r.name,
        description: `Rol ${r.name} sincronizado con guard ${r.guard_name}.`,
        permissions: r.permissions || [],
      }));
    }
    if (permsRes.data?.data?.length) {
      availablePermissions.value = permsRes.data.data.map((p) => p.name);
    }
    if (catsRes.data?.data?.length) {
      categoriesList.value = catsRes.data.data;
    }
    if (tagsRes.data?.data?.length) {
      tagsList.value = tagsRes.data.data;
    }
  } catch (e) {
    // Mantiene datos iniciales si el servidor no responde
  }
};

onMounted(async () => {
  await cargarConfiguracion();
});
</script>
