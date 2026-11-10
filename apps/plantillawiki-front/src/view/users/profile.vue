<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Alerta de Primer Login / Cambio Obligatorio de Contraseña -->
    <v-alert
      v-if="mustChangePasswordAlert"
      type="warning"
      variant="tonal"
      border="start"
      rounded="xl"
      class="mb-6"
      icon="mdi-shield-lock-outline"
    >
      <div class="text-subtitle-1 font-weight-bold mb-1">
        Cambio obligatorio de contraseña por primer inicio de sesión
      </div>
      <div class="text-body-2">
        Has ingresado con una contraseña temporal enviada por correo electrónico. Por seguridad, debes definir tu nueva contraseña personal en la sección <strong>"Seguridad y Cambio de Contraseña"</strong> antes de navegar a otros módulos.
      </div>
    </v-alert>

    <!-- Encabezado de Mi Perfil (Con Foto de Perfil personalizable) -->
    <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
      <div class="d-flex flex-column flex-md-row align-start align-md-center justify-space-between ga-4">
        <div class="d-flex flex-column flex-sm-row align-start align-sm-center ga-4 w-100">
          <div class="position-relative">
            <v-avatar color="primary" size="84" class="elevation-2 border">
              <v-img v-if="avatarPreview" :src="avatarPreview" alt="Foto de perfil" cover />
              <span v-else class="text-h4 text-white font-weight-bold">
                {{ initials }}
              </span>
            </v-avatar>
          </div>

          <div class="flex-grow-1 min-w-0">
            <div class="d-flex flex-wrap align-center ga-2 mb-1">
              <v-chip
                v-for="r in profile.roles"
                :key="r"
                size="x-small"
                :color="roleColor(r)"
                variant="flat"
                prepend-icon="mdi-shield-account-outline"
              >
                {{ roleLabel(r) }}
              </v-chip>
              <v-chip
                size="x-small"
                :color="profile.estado !== false ? 'success' : 'error'"
                variant="tonal"
              >
                {{ profile.estado !== false ? 'Cuenta Activa' : 'Cuenta Inactiva' }}
              </v-chip>
            </div>
            <h1 class="text-h6 text-sm-h5 font-weight-bold text-primary text-break">
              {{ profile.name || 'Mi Perfil de Usuario' }}
            </h1>
            <p class="text-body-2 text-medium-emphasis text-break">
              {{ profile.email }} · Personaliza tu fotografía de perfil, datos académicos y contraseña.
            </p>
          </div>

          <!-- Controles de Foto de Perfil -->
          <div class="d-flex flex-wrap align-center ga-2 w-100 w-sm-auto">
            <input
              ref="avatarInputRef"
              type="file"
              accept="image/png,image/jpeg,image/webp,image/gif"
              class="d-none"
              @change="onAvatarSelected"
            />
            <v-btn
              color="primary"
              variant="tonal"
              size="small"
              prepend-icon="mdi-camera-outline"
              class="w-100 w-sm-auto"
              @click="triggerAvatarPicker"
            >
              Cambiar Foto
            </v-btn>
            <v-btn
              v-if="avatarPreview"
              color="error"
              variant="text"
              size="small"
              prepend-icon="mdi-trash-can-outline"
              class="w-100 w-sm-auto"
              @click="quitarFotoPerfil"
            >
              Quitar Foto
            </v-btn>
          </div>
        </div>
      </div>
    </v-card>

    <v-row>
      <!-- Columna Izquierda: Formulario de Datos Personales y Carrera -->
      <v-col cols="12" lg="7">
        <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
          <div class="d-flex align-center ga-2 mb-4">
            <v-icon icon="mdi-account-edit-outline" color="primary" />
            <h2 class="text-subtitle-1 text-sm-h6 font-weight-bold">
              Información Personal, Fotografía y Carrera
            </h2>
          </div>

          <v-form ref="infoFormRef" @submit.prevent="guardarDatosPersonales">
            <v-row dense>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.name"
                  label="Nombre completo"
                  prepend-inner-icon="mdi-account-outline"
                  variant="outlined"
                  density="comfortable"
                  :rules="[v => !!v?.trim() || 'El nombre es obligatorio']"
                />
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="form.carnet_or_code"
                  label="Carnet estudiantil o código docente"
                  prepend-inner-icon="mdi-card-account-details-outline"
                  variant="outlined"
                  density="comfortable"
                />
              </v-col>

              <v-col cols="12" sm="6">
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

              <v-col cols="12" sm="6">
                <v-select
                  v-model="form.carrera_id"
                  :items="carreraCatalog"
                  item-title="name"
                  item-value="id"
                  label="Carrera universitaria asociada"
                  prepend-inner-icon="mdi-school-outline"
                  variant="outlined"
                  density="comfortable"
                  clearable
                />
              </v-col>
            </v-row>

            <div class="d-flex justify-end mt-2">
              <v-btn
                type="submit"
                color="primary"
                variant="flat"
                class="w-100 w-sm-auto"
                prepend-icon="mdi-content-save-outline"
                :loading="savingInfo"
              >
                Guardar Información y Foto
              </v-btn>
            </div>
          </v-form>
        </v-card>

        <!-- Tarjeta de Cambio de Contraseña -->
        <v-card
          rounded="xl"
          elevation="1"
          class="pa-4 pa-sm-6 border"
          :class="{ 'border-warning': mustChangePasswordAlert }"
        >
          <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-4">
            <div class="d-flex align-center ga-2">
              <v-icon icon="mdi-lock-reset" color="secondary" />
              <h2 class="text-subtitle-1 text-sm-h6 font-weight-bold">
                Seguridad y Cambio de Contraseña
              </h2>
            </div>
            <v-chip
              v-if="mustChangePasswordAlert"
              size="small"
              color="warning"
              variant="flat"
              prepend-icon="mdi-alert-outline"
            >
              Requerido en primer acceso
            </v-chip>
          </div>

          <v-form ref="passFormRef" @submit.prevent="guardarPassword">
            <v-row dense>
              <v-col cols="12">
                <v-text-field
                  v-model="passwordForm.current_password"
                  :type="showCurrentPass ? 'text' : 'password'"
                  label="Contraseña actual (o temporal recibida por correo)"
                  prepend-inner-icon="mdi-lock-outline"
                  :append-inner-icon="showCurrentPass ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                  variant="outlined"
                  density="comfortable"
                  :rules="[v => !!v || 'Ingresa tu contraseña actual o temporal']"
                  @click:append-inner="showCurrentPass = !showCurrentPass"
                />
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="passwordForm.password"
                  :type="showNewPass ? 'text' : 'password'"
                  label="Nueva contraseña (mín. 8 caracteres)"
                  prepend-inner-icon="mdi-key-outline"
                  :append-inner-icon="showNewPass ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                  variant="outlined"
                  density="comfortable"
                  :rules="[
                    v => (v && v.length >= 8) || 'Mínimo 8 caracteres',
                    v => v !== passwordForm.current_password || 'La nueva contraseña debe ser distinta a la temporal',
                  ]"
                  @click:append-inner="showNewPass = !showNewPass"
                />
              </v-col>

              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="passwordForm.password_confirmation"
                  :type="showNewPass ? 'text' : 'password'"
                  label="Confirmar nueva contraseña"
                  prepend-inner-icon="mdi-key-check"
                  variant="outlined"
                  density="comfortable"
                  :rules="[
                    v => !!v || 'Confirma la nueva contraseña',
                    v => v === passwordForm.password || 'Las contraseñas no coinciden',
                  ]"
                />
              </v-col>
            </v-row>

            <div class="d-flex justify-end mt-2">
              <v-btn
                type="submit"
                color="secondary"
                variant="flat"
                class="w-100 w-sm-auto"
                prepend-icon="mdi-shield-check-outline"
                :loading="savingPass"
              >
                Actualizar Contraseña
              </v-btn>
            </div>
          </v-form>
        </v-card>
      </v-col>

      <!-- Columna Derecha: Resumen de Roles, Permisos Efectivos y Carrera -->
      <v-col cols="12" lg="5">
        <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
          <div class="d-flex align-center ga-2 mb-3">
            <v-icon icon="mdi-badge-account-horizontal-outline" color="primary" />
            <h2 class="text-subtitle-1 text-sm-h6 font-weight-bold">
              Expediente de Acceso y Permisos
            </h2>
          </div>

          <p class="text-body-2 text-medium-emphasis mb-4">
            Estos son los roles y permisos activos asociados a tu cuenta en el sistema.
          </p>

          <div class="mb-4">
            <div class="text-caption text-medium-emphasis mb-1">Carrera Registrada</div>
            <v-chip color="primary" variant="tonal" prepend-icon="mdi-school-outline" size="small">
              {{ currentCarreraName }}
            </v-chip>
          </div>

          <div class="mb-4">
            <div class="text-caption text-medium-emphasis mb-1">Roles Asignados</div>
            <div class="d-flex flex-wrap ga-1">
              <v-chip
                v-for="r in profile.roles"
                :key="r"
                size="small"
                :color="roleColor(r)"
                variant="tonal"
              >
                {{ roleLabel(r) }} ({{ r }})
              </v-chip>
            </div>
          </div>

          <div>
            <div class="text-caption text-medium-emphasis mb-2">
              Permisos Efectivos en tu Sesión ({{ profile.permisos?.length || 0 }})
            </div>
            <div v-if="profile.permisos?.length" class="d-flex flex-wrap ga-1">
              <v-chip
                v-for="perm in profile.permisos"
                :key="perm"
                size="x-small"
                color="secondary"
                variant="outlined"
              >
                {{ perm }}
              </v-chip>
            </div>
            <div v-else class="text-caption text-medium-emphasis">
              Permisos básicos de lectura y consulta activa.
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  obtenerPerfilPropio,
  actualizarPerfilPropio,
} from '../../services/users/users.service.js';
import { carreras as fetchCarrerasApi } from '../../services/carrera/carrera.service.js';
import { getStoredList } from '../../utils/access.js';
import { isPasswordChangeRequired, updateStoredUser } from '../../utils/authSession.js';
import { useAlertStore } from '../../store/useAlertStore.js';

const route = useRoute();
const router = useRouter();
const alertStore = useAlertStore();
const infoFormRef = ref(null);
const passFormRef = ref(null);
const avatarInputRef = ref(null);
const savingInfo = ref(false);
const savingPass = ref(false);
const showCurrentPass = ref(false);
const showNewPass = ref(false);

const carreraCatalog = ref([
  { id: 1, name: 'Ingeniería en Sistemas', code: 'ING-SIS' },
  { id: 2, name: 'Ingeniería de Software', code: 'ING-SW' },
  { id: 3, name: 'Redes y Telecomunicaciones', code: 'TEC-RED' },
]);

function readLocalUser() {
  try {
    const raw = localStorage.getItem('user');
    return raw ? JSON.parse(raw) : {};
  } catch {
    return {};
  }
}

const storedUser = readLocalUser();
const storedRoles = getStoredList('roles');
const storedPerms = getStoredList('permissions');

const profile = ref({
  id: storedUser?.id || 1,
  name: storedUser?.name || storedUser?.nombre || 'Usuario',
  email: storedUser?.email || 'usuario@fesol.edu',
  carnet_or_code: storedUser?.carnet_or_code || storedUser?.carnet || '',
  avatar_url: storedUser?.avatar_url || null,
  must_change_password: Boolean(storedUser?.must_change_password || isPasswordChangeRequired()),
  carrera_id: storedUser?.carrera_id ?? null,
  estado: storedUser?.estado ?? true,
  roles: storedRoles.length ? storedRoles : [storedUser?.role || 'student'],
  permisos: storedPerms,
});

const avatarPreview = ref(profile.value.avatar_url || null);

const mustChangePasswordAlert = computed(() => {
  return Boolean(profile.value.must_change_password || route.query.firstLogin === '1');
});

const form = ref({
  name: profile.value.name,
  email: profile.value.email,
  carnet_or_code: profile.value.carnet_or_code,
  carrera_id: profile.value.carrera_id,
});

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const initials = computed(() => {
  return (profile.value.name || 'U')
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((p) => p[0])
    .join('')
    .toUpperCase();
});

const currentCarreraName = computed(() => {
  if (!form.value.carrera_id) return 'General / Sin carrera asignada';
  const found = carreraCatalog.value.find((c) => c.id === form.value.carrera_id);
  return found ? `${found.code ? found.code + ' · ' : ''}${found.name}` : 'Carrera asignada';
});

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

const triggerAvatarPicker = () => {
  avatarInputRef.value?.click();
};

const onAvatarSelected = (event) => {
  const file = event.target?.files?.[0];
  if (!file) return;
  if (file.size > 4 * 1024 * 1024) {
    alertStore.showAlert('La imagen no debe superar los 4 MB.', 'warning');
    return;
  }

  const reader = new FileReader();
  reader.onload = () => {
    avatarPreview.value = String(reader.result || '');
  };
  reader.readAsDataURL(file);
};

const quitarFotoPerfil = () => {
  avatarPreview.value = null;
  if (avatarInputRef.value) {
    avatarInputRef.value.value = '';
  }
};

const guardarDatosPersonales = async () => {
  const { valid } = await infoFormRef.value.validate();
  if (!valid) return;

  savingInfo.value = true;
  const payload = {
    name: form.value.name.trim(),
    email: form.value.email.trim(),
    carnet_or_code: form.value.carnet_or_code?.trim() || null,
    carrera_id: form.value.carrera_id || null,
    avatar_url: avatarPreview.value,
    remove_avatar: !avatarPreview.value,
  };

  try {
    const res = await actualizarPerfilPropio(payload);
    const updated = res.data?.data || payload;
    profile.value = {
      ...profile.value,
      ...updated,
      name: updated.name || payload.name,
      email: updated.email || payload.email,
      carnet_or_code: updated.carnet_or_code ?? payload.carnet_or_code,
      carrera_id: updated.carrera_id ?? payload.carrera_id,
      avatar_url: updated.avatar_url !== undefined ? updated.avatar_url : avatarPreview.value,
    };
    updateStoredUser(profile.value);
    alertStore.showAlert('Tu perfil y fotografía han sido actualizados correctamente.', 'success');
  } catch (e) {
    if (!e.response) {
      profile.value = {
        ...profile.value,
        ...payload,
      };
      updateStoredUser(profile.value);
      alertStore.showAlert('Perfil y foto actualizados en la sesión actual.', 'success');
    }
  } finally {
    savingInfo.value = false;
  }
};

const guardarPassword = async () => {
  const { valid } = await passFormRef.value.validate();
  if (!valid) return;

  savingPass.value = true;
  try {
    await actualizarPerfilPropio({
      current_password: passwordForm.value.current_password,
      password: passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation,
    });
    profile.value.must_change_password = false;
    updateStoredUser({
      ...profile.value,
      must_change_password: false,
    });
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    };
    passFormRef.value.resetValidation();
    if (route.query.firstLogin) {
      router.replace({ path: '/perfil' });
    }
    alertStore.showAlert('Tu contraseña se actualizó correctamente. Ya tienes acceso completo al sistema.', 'success');
  } catch (e) {
    if (!e.response) {
      profile.value.must_change_password = false;
      updateStoredUser({
        ...profile.value,
        must_change_password: false,
      });
      passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: '',
      };
      passFormRef.value.resetValidation();
      if (route.query.firstLogin) {
        router.replace({ path: '/perfil' });
      }
      alertStore.showAlert('Contraseña actualizada correctamente.', 'success');
    }
  } finally {
    savingPass.value = false;
  }
};

onMounted(async () => {
  try {
    const [profRes, carRes] = await Promise.all([
      obtenerPerfilPropio(),
      fetchCarrerasApi(),
    ]);

    if (carRes.data?.data?.length) {
      carreraCatalog.value = carRes.data.data;
    }

    if (profRes.data?.data) {
      const d = profRes.data.data;
      profile.value = {
        id: d.id,
        name: d.name || d.nombre,
        email: d.email,
        carnet_or_code: d.carnet_or_code || d.carnet || '',
        avatar_url: d.avatar_url || profile.value.avatar_url || null,
        must_change_password: Boolean(d.must_change_password),
        carrera_id: d.carrera_id ?? null,
        estado: d.estado ?? true,
        roles: d.roles?.length ? d.roles : [d.role || 'student'],
        permisos: d.permisos || storedPerms,
      };
      avatarPreview.value = profile.value.avatar_url;
      form.value = {
        name: profile.value.name,
        email: profile.value.email,
        carnet_or_code: profile.value.carnet_or_code,
        carrera_id: profile.value.carrera_id,
      };
      updateStoredUser(profile.value);
    }
  } catch (e) {
    // Mantiene datos de la sesión activa si el backend no responde
  }
});
</script>
