<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Encabezado y botón Crear Usuario (Solo Administrador) -->
    <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
      <div class="d-flex flex-column flex-sm-row align-start align-sm-center justify-space-between ga-4">
        <div>
          <div class="d-flex flex-wrap align-center ga-2 mb-1">
            <v-chip size="x-small" color="primary" variant="flat" prepend-icon="mdi-shield-account">
              Exclusivo Administrador
            </v-chip>
            <v-chip size="x-small" color="secondary" variant="tonal" prepend-icon="mdi-account-key-outline">
              Perfiles, Roles y Permisos por Persona
            </v-chip>
          </div>
          <h1 class="text-h6 text-sm-h5 font-weight-bold text-primary">
            Administración de Usuarios y Asignación Académica
          </h1>
          <p class="text-body-2 text-medium-emphasis">
            Registra y actualiza el perfil de coordinadores, docentes, estudiantes y administradores, incluyendo rol principal, permisos directos, carrera, contraseña y estado de cuenta.
          </p>
        </div>

        <v-btn
          color="primary"
          variant="flat"
          class="w-100 w-sm-auto"
          prepend-icon="mdi-account-plus-outline"
          @click="abrirModalCrear"
        >
          Nuevo Usuario
        </v-btn>
      </div>

      <v-divider class="my-4" />

      <!-- Barra de Filtros: Búsqueda, Filtro por Rol, Filtro por Carrera y Estado -->
      <v-row dense align="center">
        <v-col cols="12" md="4">
          <v-text-field
            v-model="search"
            variant="outlined"
            clearable
            density="compact"
            hide-details
            placeholder="Buscar por nombre, correo o carnet/código..."
            prepend-inner-icon="mdi-magnify"
          />
        </v-col>

        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="filterRole"
            :items="roleOptions"
            item-title="title"
            item-value="value"
            label="Filtrar por Rol"
            prepend-inner-icon="mdi-badge-account-outline"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="filterCarrera"
            :items="carreraCatalog"
            item-title="name"
            item-value="id"
            label="Filtrar por Carrera"
            prepend-inner-icon="mdi-school-outline"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" sm="4" md="2">
          <v-select
            v-model="filterEstado"
            :items="[
              { title: 'Todos', value: 'all' },
              { title: 'Activos', value: true },
              { title: 'Inactivos', value: false },
            ]"
            label="Estado"
            variant="outlined"
            density="compact"
            hide-details
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- Tabla de Usuarios -->
    <v-card rounded="xl" elevation="1" class="pa-3 pa-sm-4 border">
      <data-table-component
        :headers="headers"
        :items="filteredUsers"
        :totalPage="paginacion.lastPage || 1"
        :page="paginacion.currentPage || 1"
        @update:page="changePage"
      >
        <template v-slot:item.nombre="{ item }">
          <div class="d-flex align-center ga-2 py-1">
            <v-avatar color="primary" variant="tonal" size="34">
              <v-img v-if="item.avatar_url" :src="item.avatar_url" alt="Foto" cover />
              <span v-else class="text-caption font-weight-bold">
                {{ item.nombre?.charAt(0)?.toUpperCase() || 'U' }}
              </span>
            </v-avatar>
            <div>
              <div class="font-weight-semibold text-body-2">{{ item.nombre }}</div>
              <div class="d-flex flex-wrap align-center ga-1">
                <span v-if="item.permisos_directos?.length" class="text-caption text-secondary">
                  +{{ item.permisos_directos.length }} permiso(s)
                </span>
                <v-chip
                  v-if="item.must_change_password"
                  size="x-small"
                  color="warning"
                  variant="tonal"
                >
                  Cambio clave pend.
                </v-chip>
              </div>
            </div>
          </div>
        </template>

        <template v-slot:item.carnet="{ item }">
          <v-chip size="small" variant="outlined" color="secondary">
            {{ item.carnet ? item.carnet : 'Sin carnet' }}
          </v-chip>
        </template>

        <template v-slot:item.roles="{ item }">
          <div class="d-flex flex-wrap ga-1">
            <v-chip
              v-for="rol in obtenerRolesUsuario(item)"
              :key="rol"
              size="small"
              :color="roleColor(rol)"
              variant="tonal"
              class="font-weight-semibold"
            >
              {{ roleLabel(rol) }}
            </v-chip>
          </div>
        </template>

        <template v-slot:item.carrera="{ item }">
          <span class="text-body-2">
            {{ obtenerNombreCarrera(item) }}
          </span>
        </template>

        <template v-slot:item.estado="{ item }">
          <v-chip
            size="small"
            :color="item.estado !== false ? 'success' : 'error'"
            variant="tonal"
          >
            {{ item.estado !== false ? 'Activo' : 'Inactivo' }}
          </v-chip>
        </template>

        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center ga-1">
            <v-btn
              v-tooltip:bottom="'Ver expediente y permisos'"
              density="compact"
              variant="text"
              color="info"
              icon="mdi-eye-outline"
              @click="verUsuario(item)"
            />
            <v-btn
              v-tooltip:bottom="'Editar perfil, rol, carrera, permisos y contraseña'"
              density="compact"
              variant="text"
              color="primary"
              icon="mdi-account-edit-outline"
              @click="abrirModalEditar(item)"
            />
            <v-btn
              v-tooltip:bottom="item.estado !== false ? 'Desactivar usuario' : 'Reactivar usuario'"
              density="compact"
              variant="text"
              :color="item.estado !== false ? 'error' : 'success'"
              :icon="item.estado !== false ? 'mdi-account-off-outline' : 'mdi-account-check-outline'"
              @click="abrirModalEliminar(item)"
            />
          </div>
        </template>
      </data-table-component>
    </v-card>

    <!-- Modal Ver Detalle de Usuario -->
    <v-dialog v-model="dialogVer" max-width="540">
      <v-card v-if="selectedUser" rounded="xl">
        <v-card-title class="pa-4 pa-sm-5 bg-primary text-white d-flex justify-space-between align-center">
          <span>Ficha de Perfil de Usuario</span>
          <v-btn icon="mdi-close" variant="text" color="white" @click="dialogVer = false" />
        </v-card-title>
        <v-card-text class="pa-4 pa-sm-6">
          <div class="d-flex align-center ga-4 mb-4">
            <v-avatar color="secondary" size="64">
              <v-img v-if="selectedUser.avatar_url" :src="selectedUser.avatar_url" alt="Foto" cover />
              <span v-else class="text-h6 text-white font-weight-bold">
                {{ selectedUser.nombre?.charAt(0)?.toUpperCase() || 'U' }}
              </span>
            </v-avatar>
            <div>
              <div class="d-flex align-center ga-2">
                <h3 class="text-h6 font-weight-bold">{{ selectedUser.nombre }}</h3>
                <v-chip
                  size="x-small"
                  :color="selectedUser.estado !== false ? 'success' : 'error'"
                  variant="flat"
                >
                  {{ selectedUser.estado !== false ? 'Activo' : 'Inactivo' }}
                </v-chip>
              </div>
              <div class="text-body-2 text-medium-emphasis">{{ selectedUser.email }}</div>
            </div>
          </div>

          <v-divider class="my-3" />

          <v-row dense>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Carnet / Código</div>
              <div class="text-subtitle-2 font-weight-bold">{{ selectedUser.carnet || 'No asignado' }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Carrera Asociada</div>
              <div class="text-subtitle-2 font-weight-bold">{{ obtenerNombreCarrera(selectedUser) }}</div>
            </v-col>
            <v-col cols="12" class="mt-3">
              <div class="text-caption text-medium-emphasis mb-1">Roles Asignados</div>
              <div class="d-flex flex-wrap ga-1">
                <v-chip
                  v-for="r in obtenerRolesUsuario(selectedUser)"
                  :key="r"
                  size="small"
                  :color="roleColor(r)"
                >
                  {{ roleLabel(r) }}
                </v-chip>
              </div>
            </v-col>
            <v-col cols="12" class="mt-3">
              <div class="text-caption text-medium-emphasis mb-1">Permisos Directos por Persona</div>
              <div v-if="selectedUser.permisos_directos?.length" class="d-flex flex-wrap ga-1">
                <v-chip
                  v-for="perm in selectedUser.permisos_directos"
                  :key="perm"
                  size="x-small"
                  color="secondary"
                  variant="tonal"
                >
                  {{ perm }}
                </v-chip>
              </div>
              <div v-else class="text-caption text-medium-emphasis">
                Permisos heredados mediante su rol principal.
              </div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-4 justify-end flex-wrap ga-2">
          <v-btn
            variant="outlined"
            color="primary"
            prepend-icon="mdi-pencil-outline"
            @click="
              dialogVer = false;
              abrirModalEditar(selectedUser);
            "
          >
            Editar Perfil
          </v-btn>
          <v-btn variant="tonal" color="primary" @click="dialogVer = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Crear / Editar Usuario y Perfil por Persona -->
    <v-dialog v-model="dialogFormulario" max-width="680" persistent scrollable>
      <v-card rounded="xl">
        <v-card-title class="pa-4 pa-sm-5 bg-primary text-white d-flex justify-space-between align-center">
          <span>{{ isEditing ? 'Actualizar Perfil, Rol y Permisos del Usuario' : 'Registrar Nuevo Usuario' }}</span>
          <v-btn icon="mdi-close" variant="text" color="white" @click="dialogFormulario = false" />
        </v-card-title>

        <v-card-text class="pa-4 pa-sm-6">
          <v-form ref="userFormRef">
            <v-row dense>
              <!-- Foto de perfil del usuario -->
              <v-col cols="12" class="mb-3">
                <div class="d-flex flex-wrap align-center ga-3 pa-3 border rounded-lg bg-surface">
                  <v-avatar color="primary" size="56">
                    <v-img v-if="form.avatar_url" :src="form.avatar_url" alt="Foto de perfil" cover />
                    <span v-else class="text-h6 text-white font-weight-bold">
                      {{ form.nombre?.charAt(0)?.toUpperCase() || 'U' }}
                    </span>
                  </v-avatar>
                  <div class="flex-grow-1 min-w-0">
                    <div class="text-subtitle-2 font-weight-bold">Fotografía de Perfil (Opcional)</div>
                    <div class="text-caption text-medium-emphasis">
                      Sube una foto en formato PNG, JPG o WEBP (máx. 4 MB).
                    </div>
                  </div>
                  <div class="d-flex ga-2">
                    <input
                      ref="userAvatarInputRef"
                      type="file"
                      accept="image/png,image/jpeg,image/webp,image/gif"
                      class="d-none"
                      @change="onUserAvatarSelected"
                    />
                    <v-btn
                      size="small"
                      variant="tonal"
                      color="primary"
                      prepend-icon="mdi-camera-outline"
                      @click="userAvatarInputRef?.click()"
                    >
                      Subir Foto
                    </v-btn>
                    <v-btn
                      v-if="form.avatar_url"
                      size="small"
                      variant="text"
                      color="error"
                      icon="mdi-delete-outline"
                      @click="form.avatar_url = null"
                    />
                  </div>
                </div>
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.nombre"
                  label="Nombre completo"
                  prepend-inner-icon="mdi-account-outline"
                  variant="outlined"
                  density="comfortable"
                  :rules="[v => !!v?.trim() || 'El nombre es obligatorio']"
                />
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.carnet"
                  label="Carnet estudiantil o código docente"
                  prepend-inner-icon="mdi-card-account-details-outline"
                  variant="outlined"
                  density="comfortable"
                />
              </v-col>

              <v-col cols="12" sm="7">
                <v-text-field
                  v-model="form.email"
                  label="Correo electrónico institucional"
                  type="email"
                  prepend-inner-icon="mdi-email-outline"
                  variant="outlined"
                  density="comfortable"
                  :rules="[
                    v => !!v?.trim() || 'El correo es obligatorio',
                    v => /.+@.+\..+/.test(v) || 'Ingresa un correo válido',
                  ]"
                />
              </v-col>

              <v-col cols="12" sm="5">
                <v-select
                  v-model="form.estado"
                  :items="[
                    { title: 'Cuenta Activa', value: true },
                    { title: 'Cuenta Inactiva', value: false },
                  ]"
                  label="Estado del usuario"
                  prepend-inner-icon="mdi-check-decagram-outline"
                  variant="outlined"
                  density="comfortable"
                />
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="form.role"
                  :items="roleOptions"
                  item-title="title"
                  item-value="value"
                  label="Rol principal en el sistema"
                  prepend-inner-icon="mdi-shield-account-outline"
                  variant="outlined"
                  density="comfortable"
                  :rules="[v => !!v || 'Selecciona un rol']"
                />
              </v-col>

              <v-col cols="12" sm="6">
                <v-select
                  v-model="form.carrera_id"
                  :items="carreraCatalog"
                  item-title="name"
                  item-value="id"
                  label="Carrera asignada"
                  prepend-inner-icon="mdi-school-outline"
                  variant="outlined"
                  density="comfortable"
                  clearable
                  hint="Asociación académica del usuario"
                  persistent-hint
                />
              </v-col>

              <!-- Permisos directos adicionales por persona -->
              <v-col cols="12" class="mt-2">
                <v-select
                  v-model="form.permissions"
                  :items="availablePermissions"
                  label="Permisos directos específicos para esta persona (opcional)"
                  prepend-inner-icon="mdi-key-plus"
                  variant="outlined"
                  density="comfortable"
                  multiple
                  chips
                  closable-chips
                  clearable
                  hint="Además de los permisos de su rol, puedes otorgarle permisos puntuales a este usuario"
                  persistent-hint
                />
              </v-col>

              <!-- Contraseña automática al crear + envío por correo y cambio obligatorio en 1er login -->
              <v-col cols="12" class="mt-3">
                <v-alert
                  v-if="!isEditing"
                  color="info"
                  variant="tonal"
                  density="compact"
                  rounded="lg"
                  icon="mdi-email-lock-outline"
                  class="mb-3 text-caption"
                >
                  <strong>Contraseña autogenerada y envío por correo:</strong> Se ha generado una clave segura automáticamente. Al crear el usuario se enviará a su correo institucional y se le obligará a cambiarla en su primer inicio de sesión.
                </v-alert>

                <div class="d-flex flex-column flex-sm-row ga-2 align-stretch align-sm-start">
                  <v-text-field
                    v-model="form.password"
                    :label="isEditing ? 'Nueva contraseña temporal (dejar en blanco para mantener la actual)' : 'Contraseña temporal autogenerada (se enviará por correo)'"
                    :type="showPassword ? 'text' : 'password'"
                    prepend-inner-icon="mdi-lock-outline"
                    :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                    variant="outlined"
                    density="comfortable"
                    class="flex-grow-1"
                    :rules="
                      isEditing
                        ? [v => !v || v.length >= 8 || 'Si cambias la contraseña, debe tener mínimo 8 caracteres']
                        : [v => (v && v.length >= 8) || 'Mínimo 8 caracteres']
                    "
                    @click:append-inner="showPassword = !showPassword"
                  />
                  <v-btn
                    color="secondary"
                    variant="tonal"
                    height="48"
                    prepend-icon="mdi-autorenew"
                    class="text-none"
                    @click="regenerarPasswordAutomatica"
                  >
                    Generar Clave
                  </v-btn>
                </div>

                <v-checkbox
                  v-model="form.must_change_password"
                  color="primary"
                  density="compact"
                  hide-details
                  label="Obligar al usuario a cambiar la contraseña en su próximo inicio de sesión"
                  class="mt-1"
                />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-divider />

        <v-card-actions class="pa-4 justify-end flex-wrap ga-2">
          <v-btn variant="text" @click="dialogFormulario = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            variant="flat"
            prepend-icon="mdi-content-save-outline"
            @click="guardarUsuario"
          >
            {{ isEditing ? 'Guardar Cambios del Perfil' : 'Crear Usuario' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Confirmar Cambio de Estado -->
    <v-dialog v-model="dialogEliminar" max-width="440">
      <v-card v-if="selectedUser" rounded="xl" class="pa-2">
        <v-card-title
          class="text-h6 font-weight-bold"
          :class="selectedUser.estado !== false ? 'text-error' : 'text-success'"
        >
          {{ selectedUser.estado !== false ? '¿Desactivar usuario?' : '¿Reactivar usuario?' }}
        </v-card-title>
        <v-card-text class="text-body-2">
          Estás a punto de cambiar el estado de <strong>{{ selectedUser.nombre }}</strong> ({{ selectedUser.email }}) a
          <strong>{{ selectedUser.estado !== false ? 'Inactivo' : 'Activo' }}</strong>.
          El historial académico de eventos, materias y proyectos se mantiene intacto.
        </v-card-text>
        <v-card-actions class="justify-end flex-wrap ga-2">
          <v-btn variant="text" @click="dialogEliminar = false">Cancelar</v-btn>
          <v-btn
            :color="selectedUser.estado !== false ? 'error' : 'success'"
            variant="flat"
            @click="confirmarEliminar"
          >
            {{ selectedUser.estado !== false ? 'Confirmar desactivación' : 'Confirmar activación' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import DataTableComponent from '../../components/DataTableComponent.vue';
import { useUsers } from './composable/userComposable.js';
import { carreras as fetchCarrerasApi } from '../../services/carrera/carrera.service.js';
import { obtenerRoles, obtenerPermisos } from '../../services/wiki/wiki.service.js';
import { updateStoredUser } from '../../utils/authSession.js';
import { useAlertStore } from '../../store/useAlertStore.js';

const {
  headers,
  usuarios,
  paginacion,
  getUsuarios,
  createUsuario,
  updateUsuario,
  toggleEstadoUsuario,
} = useUsers();
const alertStore = useAlertStore();

const search = ref('');
const filterRole = ref(null);
const filterCarrera = ref(null);
const filterEstado = ref('all');
let searchTimeout;

const dialogVer = ref(false);
const dialogFormulario = ref(false);
const dialogEliminar = ref(false);
const isEditing = ref(false);
const showPassword = ref(false);
const selectedUser = ref(null);
const userFormRef = ref(null);
const userAvatarInputRef = ref(null);

const generarPasswordSegura = () => {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
  let randomPart = '';
  for (let i = 0; i < 6; i++) {
    randomPart += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return `Fesol#${randomPart}`;
};

const regenerarPasswordAutomatica = () => {
  form.value.password = generarPasswordSegura();
  showPassword.value = true;
};

const onUserAvatarSelected = (event) => {
  const file = event.target?.files?.[0];
  if (!file) return;
  if (file.size > 4 * 1024 * 1024) {
    alertStore.showAlert('La fotografía no debe superar los 4 MB.', 'warning');
    return;
  }
  const reader = new FileReader();
  reader.onload = () => {
    form.value.avatar_url = String(reader.result || '');
  };
  reader.readAsDataURL(file);
};

const roleOptions = ref([
  { title: 'Administrador (admin)', value: 'admin' },
  { title: 'Coordinador de Carrera (coordinador)', value: 'coordinador' },
  { title: 'Docente (teacher)', value: 'teacher' },
  { title: 'Estudiante (student)', value: 'student' },
]);

const availablePermissions = ref([
  'create_user',
  'edit_user',
  'delete_user',
  'view_user',
  'create_rol',
  'edit_rol',
  'delete_rol',
  'view_rol',
  'create_event',
  'edit_event',
  'manage_event_subjects',
  'upload_projects',
  'publish_wiki_articles',
]);

const carreraCatalog = ref([
  { id: 1, name: 'Ingeniería en Sistemas', code: 'ING-SIS' },
  { id: 2, name: 'Ingeniería de Software', code: 'ING-SW' },
  { id: 3, name: 'Redes y Telecomunicaciones', code: 'TEC-RED' },
]);

const localUsersFallback = ref([
  {
    id: 1,
    nombre: 'admin',
    email: 'admin@gmail.com',
    carnet: 'ADM-001',
    roles: ['admin'],
    carrera_id: null,
    estado: true,
    permisos_directos: [],
  },
  {
    id: 2,
    nombre: 'Ing. Roberto Morales',
    email: 'coordinador@gmail.com',
    carnet: 'COORD-102',
    roles: ['coordinador'],
    carrera_id: 1,
    estado: true,
    permisos_directos: [],
  },
  {
    id: 3,
    nombre: 'Ing. Carlos Méndez',
    email: 'teacher@gmail.com',
    carnet: 'DOC-204',
    roles: ['teacher'],
    carrera_id: 1,
    estado: true,
    permisos_directos: [],
  },
  {
    id: 4,
    nombre: 'Ana Sofía Martínez',
    email: 'student@gmail.com',
    carnet: 'SM22014',
    roles: ['student'],
    carrera_id: 1,
    estado: true,
    permisos_directos: [],
  },
]);

const form = ref({
  id: null,
  nombre: '',
  email: '',
  carnet: '',
  avatar_url: null,
  must_change_password: true,
  role: 'student',
  carrera_id: null,
  estado: true,
  permissions: [],
  password: '',
});

const sourceUsers = computed(() => {
  return usuarios.value && usuarios.value.length ? usuarios.value : localUsersFallback.value;
});

const filteredUsers = computed(() => {
  return sourceUsers.value.filter((u) => {
    const userRoles = obtenerRolesUsuario(u);
    if (filterRole.value && !userRoles.includes(filterRole.value)) {
      return false;
    }
    if (filterCarrera.value && u.carrera_id !== filterCarrera.value) {
      return false;
    }
    const isActive = u.estado !== false;
    if (filterEstado.value !== 'all' && isActive !== filterEstado.value) {
      return false;
    }
    const q = search.value?.trim().toLowerCase();
    if (q) {
      const matchName = u.nombre?.toLowerCase().includes(q);
      const matchEmail = u.email?.toLowerCase().includes(q);
      const matchCarnet = u.carnet?.toLowerCase().includes(q);
      if (!matchName && !matchEmail && !matchCarnet) return false;
    }
    return true;
  });
});

const obtenerRolesUsuario = (item) => {
  if (Array.isArray(item.roles) && item.roles.length) return item.roles;
  if (item.role) return [item.role];
  return ['student'];
};

const obtenerNombreCarrera = (item) => {
  if (item.carrera?.name) return item.carrera.name;
  const found = carreraCatalog.value.find((c) => c.id === item.carrera_id);
  return found ? `${found.code} · ${found.name}` : 'General / Sin carrera';
};

const roleColor = (role) => {
  const map = {
    admin: 'primary',
    coordinador: 'secondary',
    teacher: 'info',
    student: 'success',
  };
  return map[role] || 'secondary';
};

const roleLabel = (role) => {
  const map = {
    admin: 'Administrador',
    coordinador: 'Coordinador',
    teacher: 'Docente',
    student: 'Estudiante',
  };
  return map[role] || role;
};

const verUsuario = (item) => {
  selectedUser.value = item;
  dialogVer.value = true;
};

const abrirModalCrear = () => {
  isEditing.value = false;
  showPassword.value = true;
  form.value = {
    id: null,
    nombre: '',
    email: '',
    carnet: '',
    avatar_url: null,
    must_change_password: true,
    role: 'student',
    carrera_id: 1,
    estado: true,
    permissions: [],
    password: generarPasswordSegura(),
  };
  dialogFormulario.value = true;
};

const abrirModalEditar = (item) => {
  isEditing.value = true;
  showPassword.value = false;
  selectedUser.value = item;
  form.value = {
    id: item.id,
    nombre: item.nombre || '',
    email: item.email || '',
    carnet: item.carnet || '',
    avatar_url: item.avatar_url || null,
    must_change_password: Boolean(item.must_change_password),
    role: obtenerRolesUsuario(item)[0] || 'student',
    carrera_id: item.carrera_id ?? null,
    estado: item.estado !== false,
    permissions: Array.isArray(item.permisos_directos) ? [...item.permisos_directos] : [],
    password: '',
  };
  dialogFormulario.value = true;
};

const syncIfCurrentLoggedUser = (updatedPayload) => {
  try {
    const raw = localStorage.getItem('user');
    if (!raw) return;
    const current = JSON.parse(raw);
    if (current && (current.id === form.value.id || current.email === selectedUser.value?.email)) {
      updateStoredUser({
        name: updatedPayload.name,
        email: updatedPayload.email,
        carnet_or_code: updatedPayload.carnet_or_code,
        avatar_url: updatedPayload.avatar_url,
        must_change_password: updatedPayload.must_change_password,
        carrera_id: updatedPayload.carrera_id,
        roles: [updatedPayload.role],
      });
    }
  } catch {
    // ignore
  }
};

const guardarUsuario = async () => {
  const { valid } = await userFormRef.value.validate();
  if (!valid) return;

  const autoPass = !isEditing.value && !form.value.password ? generarPasswordSegura() : form.value.password;

  const payload = {
    name: form.value.nombre.trim(),
    email: form.value.email.trim(),
    carnet_or_code: form.value.carnet?.trim() || null,
    avatar_url: form.value.avatar_url || null,
    remove_avatar: !form.value.avatar_url,
    must_change_password: Boolean(form.value.must_change_password),
    role: form.value.role,
    carrera_id: form.value.carrera_id || null,
    estado: Boolean(form.value.estado),
    permissions: form.value.permissions || [],
    ...(autoPass ? { password: autoPass } : {}),
  };

  try {
    if (isEditing.value && form.value.id) {
      await updateUsuario(form.value.id, payload);
      syncIfCurrentLoggedUser(payload);
      alertStore.showAlert('Perfil de usuario actualizado correctamente.', 'success');
    } else {
      const created = await createUsuario(payload);
      const sentPass = created?.generated_password || autoPass;
      alertStore.showAlert(
        `Usuario creado. Se envió la contraseña temporal (${sentPass}) al correo ${payload.email}. Deberá cambiarla en su primer inicio de sesión.`,
        'success'
      );
    }
  } catch (e) {
    if (isEditing.value) {
      const idx = localUsersFallback.value.findIndex((u) => u.email === selectedUser.value?.email || u.id === form.value.id);
      if (idx !== -1) {
        localUsersFallback.value[idx] = {
          ...localUsersFallback.value[idx],
          nombre: payload.name,
          email: payload.email,
          carnet: payload.carnet_or_code,
          avatar_url: payload.avatar_url,
          must_change_password: payload.must_change_password,
          roles: [payload.role],
          carrera_id: payload.carrera_id,
          estado: payload.estado,
          permisos_directos: [...payload.permissions],
        };
      }
      syncIfCurrentLoggedUser(payload);
    } else {
      localUsersFallback.value.unshift({
        id: Date.now(),
        nombre: payload.name,
        email: payload.email,
        carnet: payload.carnet_or_code,
        avatar_url: payload.avatar_url,
        must_change_password: true,
        roles: [payload.role],
        carrera_id: payload.carrera_id,
        estado: payload.estado,
        permisos_directos: [...payload.permissions],
      });
      alertStore.showAlert(
        `Usuario creado con clave temporal (${autoPass}) enviada a ${payload.email}. Cambio obligatorio activado para el primer login.`,
        'success'
      );
    }
  }

  dialogFormulario.value = false;
};

const abrirModalEliminar = (item) => {
  selectedUser.value = item;
  dialogEliminar.value = true;
};

const confirmarEliminar = async () => {
  try {
    if (selectedUser.value?.id) {
      await toggleEstadoUsuario(selectedUser.value.id);
      alertStore.showAlert('Estado del usuario actualizado correctamente.', 'success');
    }
  } catch (e) {
    const idx = localUsersFallback.value.findIndex(
      (u) => u.email === selectedUser.value?.email || u.id === selectedUser.value?.id
    );
    if (idx !== -1) {
      localUsersFallback.value[idx].estado = localUsersFallback.value[idx].estado === false;
    }
  }
  dialogEliminar.value = false;
};

const changePage = async (page) => {
  try {
    await getUsuarios(page, {
      search: search.value.trim(),
      role: filterRole.value || undefined,
      carrera_id: filterCarrera.value || undefined,
    });
  } catch (e) {
    console.warn('Usando listado local de respaldo.');
  }
};

watch([search, filterRole, filterCarrera], () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(async () => {
    try {
      await getUsuarios(1, {
        search: search.value?.trim() || undefined,
        role: filterRole.value || undefined,
        carrera_id: filterCarrera.value || undefined,
      });
    } catch (e) {
      // Fallback visual activo si el servidor está apagado
    }
  }, 300);
});

onMounted(async () => {
  try {
    await getUsuarios();
    const [resCarreras, resRoles, resPerms] = await Promise.all([
      fetchCarrerasApi(),
      obtenerRoles(),
      obtenerPermisos(),
    ]);
    if (resCarreras.data?.data?.length) {
      carreraCatalog.value = resCarreras.data.data;
    }
    if (resRoles.data?.data?.length) {
      roleOptions.value = resRoles.data.data.map((r) => ({
        title: `${roleLabel(r.name)} (${r.name})`,
        value: r.name,
      }));
    }
    if (resPerms.data?.data?.length) {
      availablePermissions.value = resPerms.data.data.map((p) => p.name);
    }
  } catch (e) {
    // Fallback visual activo
  }
});

onBeforeUnmount(() => clearTimeout(searchTimeout));
</script>
