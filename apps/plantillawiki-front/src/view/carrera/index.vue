<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Encabezado y controles de Carreras -->
    <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
      <div class="d-flex flex-column flex-sm-row align-start align-sm-center justify-space-between ga-4">
        <div>
          <h1 class="text-h6 text-sm-h5 font-weight-bold text-primary">
            Catálogo de Carreras Universitarias
          </h1>
          <p class="text-body-2 text-medium-emphasis">
            Consulta las carreras registradas y sus materias/usuarios asociados. La creación, edición y cambio de estado están reservados para Administración.
          </p>
        </div>

        <v-btn
          v-role="['admin']"
          color="primary"
          variant="flat"
          class="w-100 w-sm-auto"
          prepend-icon="mdi-plus"
          @click="abrirModalCrear"
        >
          Nueva Carrera
        </v-btn>
      </div>

      <v-divider class="my-4" />

      <v-row dense align="center">
        <v-col cols="12" md="6">
          <v-text-field
            v-model="search"
            placeholder="Filtrar por nombre o código de carrera..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="estadoFilter"
            :items="[
              { title: 'Todos los estados', value: 'all' },
              { title: 'Solo activas', value: true },
              { title: 'Solo inactivas', value: false },
            ]"
            variant="outlined"
            density="compact"
            hide-details
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- Tabla de Carreras -->
    <v-card rounded="xl" elevation="1" class="pa-4 border">
      <data-table-component
        :totalPage="pagination.last_page || 1"
        :page="pagination.current_page || 1"
        :headers="headers"
        :items="filteredCarreras"
        @update:page="paginationMethod"
      >
        <template v-slot:item.code="{ item }">
          <v-chip size="small" color="secondary" variant="tonal" class="font-weight-bold">
            {{ item.code || 'SIN-COD' }}
          </v-chip>
        </template>

        <template v-slot:item.estado="{ item }">
          <v-chip size="small" :color="item.estado ? 'success' : 'error'" variant="tonal">
            {{ item.estado ? 'Activo' : 'Inactivo' }}
          </v-chip>
        </template>

        <template v-slot:item.acciones="{ item }">
          <div class="d-flex align-center ga-1">
            <v-btn
              v-tooltip:bottom="'Ver detalle de carrera'"
              density="compact"
              variant="text"
              color="info"
              icon="mdi-eye-outline"
              @click="verCarrera(item)"
            />

            <v-btn
              v-role="['admin']"
              v-if="item.estado"
              v-tooltip:bottom="'Editar carrera'"
              density="compact"
              variant="text"
              color="primary"
              icon="mdi-pencil-outline"
              @click="ActualizarCarrera(item)"
            />

            <v-btn
              v-role="['admin']"
              v-if="item.estado"
              v-tooltip:bottom="'Desactivar carrera'"
              density="compact"
              variant="text"
              color="error"
              icon="mdi-trash-can-outline"
              @click="abrirModalDesactivar(item)"
            />
          </div>
        </template>
      </data-table-component>
    </v-card>

    <!-- Modal Ver Carrera -->
    <v-dialog v-model="dialogVerCarrera" max-width="500">
      <v-card v-if="carrera" rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white d-flex justify-space-between align-center">
          <span>Detalle de Carrera</span>
          <v-btn icon="mdi-close" variant="text" color="white" @click="dialogVerCarrera = false" />
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="text-caption text-medium-emphasis">Nombre de la Carrera</div>
          <div class="text-h6 font-weight-bold mb-3">{{ carrera.name }}</div>

          <div class="d-flex ga-4">
            <div>
              <div class="text-caption text-medium-emphasis">Código Único</div>
              <v-chip size="small" color="secondary" class="mt-1">{{ carrera.code }}</v-chip>
            </div>
            <div>
              <div class="text-caption text-medium-emphasis">Estado</div>
              <v-chip size="small" :color="carrera.estado ? 'success' : 'error'" class="mt-1">
                {{ carrera.estado ? 'Activo' : 'Inactivo' }}
              </v-chip>
            </div>
          </div>
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn rounded="lg" variant="tonal" color="primary" @click="dialogVerCarrera = false">
            Cerrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Crear Carrera -->
    <v-dialog v-model="dialogCrearCarrera" max-width="500" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white">Registrar Nueva Carrera</v-card-title>
        <v-card-text class="pa-6">
          <v-form ref="formCrear">
            <v-text-field
              v-model="form.name"
              label="Nombre de la carrera"
              variant="outlined"
              class="mb-3"
              :rules="[v => !!v?.trim() || 'El nombre es obligatorio']"
            />
            <v-text-field
              v-model="form.code"
              label="Código único (ej. ING-SIS)"
              variant="outlined"
              :rules="[v => !!v?.trim() || 'El código es obligatorio']"
            />
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="dialogCrearCarrera = false">Cancelar</v-btn>
          <v-btn color="primary" variant="flat" :loading="guardando" @click="guardarCreacion">
            Crear Carrera
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Editar Carrera -->
    <v-dialog v-model="dialogActualizarCarrera" max-width="500" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white">Editar Carrera</v-card-title>
        <v-card-text class="pa-6">
          <v-form ref="formEditar">
            <v-text-field
              v-model="form.name"
              label="Nombre"
              variant="outlined"
              class="mb-3"
              :rules="[v => !!v?.trim() || 'El nombre es obligatorio']"
            />
            <v-text-field v-model="form.code" label="Código" variant="outlined" />
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="dialogActualizarCarrera = false">Cancelar</v-btn>
          <v-btn color="primary" variant="flat" :loading="guardando" @click="guardarEdicion">
            Guardar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Confirmar Desactivación -->
    <v-dialog v-model="dialogEliminarCarrera" max-width="460" persistent>
      <v-card v-if="carrera" rounded="xl">
        <v-card-title class="pa-5 text-error font-weight-bold">
          ¿Desactivar carrera?
        </v-card-title>
        <v-card-text class="px-6 pb-4 text-body-2">
          Vas a cambiar el estado de <strong>{{ carrera.name }}</strong> ({{ carrera.code }}) a <strong>Inactivo</strong>.
          No se eliminará físicamente de la base de datos para conservar el historial de eventos y usuarios.
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="dialogEliminarCarrera = false">Cancelar</v-btn>
          <v-btn color="error" variant="flat" :loading="guardando" @click="confirmarEliminar">
            Desactivar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DataTableComponent from '../../components/DataTableComponent.vue';
import { useCarreras } from './composable/carreraComposable';

const search = ref('');
const estadoFilter = ref('all');

const dialogVerCarrera = ref(false);
const dialogCrearCarrera = ref(false);
const dialogActualizarCarrera = ref(false);
const dialogEliminarCarrera = ref(false);

const form = ref({ id: null, name: '', code: '' });
const formCrear = ref(null);
const formEditar = ref(null);
const guardando = ref(false);
const carrera = ref(null);

const {
  carreras,
  pagination,
  obtenerCarreras,
  crearNuevaCarrera,
  actualizarCarreras,
  cambiarEstadoCarrera,
} = useCarreras();

const fallbackCarreras = ref([
  { id: 1, name: 'Ingeniería en Sistemas y Computación', code: 'ING-SIS', estado: true },
  { id: 2, name: 'Ingeniería en Desarrollo de Software', code: 'ING-SW', estado: true },
  { id: 3, name: 'Técnico en Redes y Telecomunicaciones', code: 'TEC-RED', estado: true },
  { id: 4, name: 'Licenciatura en Ciencia de Datos e IA', code: 'LIC-IA', estado: false },
]);

const headers = ref([
  { title: 'Nombre de la Carrera', key: 'name' },
  { title: 'Código', key: 'code' },
  { title: 'Estado', key: 'estado' },
  { title: 'Acciones', key: 'acciones' },
]);

const sourceCarreras = computed(() => {
  return carreras.value && carreras.value.length ? carreras.value : fallbackCarreras.value;
});

const filteredCarreras = computed(() => {
  const q = search.value?.trim().toLowerCase();
  return sourceCarreras.value.filter((item) => {
    if (estadoFilter.value !== 'all' && item.estado !== estadoFilter.value) {
      return false;
    }
    if (q) {
      return (
        item.name?.toLowerCase().includes(q) ||
        item.code?.toLowerCase().includes(q)
      );
    }
    return true;
  });
});

const verCarrera = (item) => {
  carrera.value = item;
  dialogVerCarrera.value = true;
};

const abrirModalCrear = () => {
  form.value = { id: null, name: '', code: '' };
  dialogCrearCarrera.value = true;
};

const guardarCreacion = async () => {
  const { valid } = await formCrear.value.validate();
  if (!valid) return;

  guardando.value = true;
  const ok = await crearNuevaCarrera({
    name: form.value.name,
    code: form.value.code,
  });
  guardando.value = false;

  if (!ok) {
    fallbackCarreras.value.unshift({
      id: Date.now(),
      name: form.value.name,
      code: form.value.code,
      estado: true,
    });
  }
  dialogCrearCarrera.value = false;
};

const ActualizarCarrera = (item) => {
  form.value = { id: item.id, name: item.name, code: item.code };
  carrera.value = item;
  dialogActualizarCarrera.value = true;
};

const guardarEdicion = async () => {
  const { valid } = await formEditar.value.validate();
  if (!valid) return;

  guardando.value = true;
  const ok = await actualizarCarreras(form.value.id, {
    name: form.value.name,
    code: form.value.code,
  });
  guardando.value = false;

  if (!ok) {
    const idx = fallbackCarreras.value.findIndex((c) => c.id === form.value.id);
    if (idx !== -1) {
      fallbackCarreras.value[idx] = {
        ...fallbackCarreras.value[idx],
        name: form.value.name,
        code: form.value.code,
      };
    }
  }
  dialogActualizarCarrera.value = false;
};

const abrirModalDesactivar = (item) => {
  carrera.value = item;
  dialogEliminarCarrera.value = true;
};

const confirmarEliminar = async () => {
  if (!carrera.value) return;
  guardando.value = true;
  const ok = await cambiarEstadoCarrera(carrera.value.id);
  guardando.value = false;

  if (!ok) {
    const idx = fallbackCarreras.value.findIndex((c) => c.id === carrera.value.id);
    if (idx !== -1) {
      fallbackCarreras.value[idx].estado = false;
    }
  }
  dialogEliminarCarrera.value = false;
};

const paginationMethod = async (page) => {
  await obtenerCarreras({ page });
};

onMounted(async () => {
  await obtenerCarreras();
});
</script>
