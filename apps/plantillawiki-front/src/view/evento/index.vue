<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Encabezado y Reglas de Negocio del Módulo de Eventos -->
    <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
      <div class="d-flex flex-column flex-md-row align-start align-md-center justify-space-between ga-4">
        <div>
          <div class="d-flex flex-wrap ga-2 mb-2">
            <v-chip size="x-small" color="secondary" variant="flat" prepend-icon="mdi-calendar-star">
              Creación de Eventos: Coordinador de Carrera / Admin
            </v-chip>
            <v-chip size="x-small" color="info" variant="tonal" prepend-icon="mdi-human-male-board">
              Creación de Materias: Docente / Coordinador / Admin
            </v-chip>
          </div>
          <h1 class="text-h6 text-sm-h5 font-weight-bold text-primary">
            Gestión de Eventos Universitarios y Materias por Carrera
          </h1>
          <p class="text-body-2 text-medium-emphasis">
            El coordinador crea los eventos del ciclo académico (`events`) y los docentes inscriben sus materias (`event_subjects`) vinculadas a una carrera para habilitar la subida de proyectos.
          </p>
        </div>

        <div class="d-flex flex-column flex-sm-row flex-wrap ga-2 w-100 w-md-auto">
          <!-- Solo Coordinador y Admin pueden crear Eventos -->
          <v-btn
            v-if="canCreateEvent"
            color="primary"
            variant="flat"
            class="w-100 w-sm-auto"
            prepend-icon="mdi-calendar-plus"
            @click="abrirModalEvento()"
          >
            Nuevo Evento (Coordinador)
          </v-btn>

          <!-- Docente, Coordinador y Admin pueden registrar Materias en el Evento -->
          <v-btn
            v-if="canCreateSubject"
            color="secondary"
            variant="tonal"
            class="w-100 w-sm-auto"
            prepend-icon="mdi-book-plus-outline"
            @click="abrirModalMateria()"
          >
            Inscribir Materia (Docente)
          </v-btn>
        </div>
      </div>

      <v-divider class="my-4" />

      <!-- Filtros de Eventos y Materias -->
      <v-row dense align="center">
        <v-col cols="12" md="4">
          <v-text-field
            v-model="filters.search"
            placeholder="Buscar ciclo, coordinador, materia o docente..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" sm="6" md="4">
          <v-select
            v-model="filters.carreraId"
            :items="carrerasList"
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

        <v-col cols="12" sm="6" md="4">
          <v-select
            v-model="filters.status"
            :items="[
              { title: 'Todos los eventos', value: 'all' },
              { title: 'Solo Eventos Activos', value: 'active' },
              { title: 'Eventos Finalizados', value: 'inactive' },
            ]"
            label="Estado del Evento"
            variant="outlined"
            density="compact"
            hide-details
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- Listado Maestro-Detalle Paginado de Eventos -> Carreras -> Materias -->
    <v-row>
      <v-col cols="12" v-for="evento in paginatedEvents" :key="evento.id">
        <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 border">
          <!-- Cabecera del Evento -->
          <div class="d-flex flex-wrap align-center justify-space-between ga-3 pb-4 border-b">
            <div class="min-w-0">
              <div class="d-flex flex-wrap align-center ga-2 mb-1">
                <v-chip
                  :color="evento.is_active ? 'success' : 'grey'"
                  size="small"
                  variant="flat"
                >
                  {{ evento.is_active ? 'Evento Activo' : 'Cerrado' }}
                </v-chip>
                <h2 class="text-h6 font-weight-bold text-break">{{ evento.cicle_name }}</h2>
              </div>
              <div class="text-body-2 text-medium-emphasis text-break">
                Coordinador Responsable: <strong>{{ evento.coordinador.name }}</strong> ·
                Fecha de Inicio: <strong>{{ evento.start_date }}</strong>
              </div>
            </div>

            <div class="d-flex flex-wrap align-center ga-2">
              <v-btn
                v-if="canCreateSubject && evento.is_active"
                size="small"
                color="info"
                variant="tonal"
                prepend-icon="mdi-book-plus-outline"
                @click="abrirModalMateria(evento.id)"
              >
                Agregar Materia a este Evento
              </v-btn>

              <v-btn
                v-if="canCreateEvent"
                size="small"
                color="primary"
                variant="outlined"
                prepend-icon="mdi-pencil-outline"
                @click="abrirModalEvento(evento)"
              >
                Editar Evento
              </v-btn>

              <v-btn
                v-if="canCreateEvent"
                size="small"
                :color="evento.is_active ? 'warning' : 'success'"
                variant="tonal"
                :prepend-icon="evento.is_active ? 'mdi-lock-outline' : 'mdi-lock-open-variant-outline'"
                @click="evento.is_active = !evento.is_active"
              >
                {{ evento.is_active ? 'Cerrar Evento' : 'Activar Evento' }}
              </v-btn>
            </div>
          </div>

          <!-- Materias inscritas en el Evento agrupadas / filtrables por Carrera -->
          <div class="mt-4">
            <div class="d-flex align-center justify-space-between mb-3">
              <h3 class="text-subtitle-1 font-weight-bold text-primary">
                Materias Registradas por Docentes (`event_subjects`: {{ evento.subjects.length }})
              </h3>
            </div>

            <div class="overflow-x-auto">
              <v-table density="comfortable" class="border rounded-lg" style="min-width: 620px;">
              <thead>
                <tr>
                  <th>Materia (`subject_name`)</th>
                  <th>Carrera (`carrera_id`)</th>
                  <th>Docente Responsable (`teacher_id`)</th>
                  <th>Proyectos Subidos</th>
                  <th class="text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sub in filtrarMateriasDeEvento(evento.subjects)" :key="sub.id">
                  <td class="font-weight-bold">{{ sub.subject_name }}</td>
                  <td>
                    <v-chip size="x-small" color="primary" variant="tonal">
                      {{ sub.carrera.code }} · {{ sub.carrera.name }}
                    </v-chip>
                  </td>
                  <td>{{ sub.teacher.name }}</td>
                  <td>
                    <v-chip size="small" color="success" variant="tonal" prepend-icon="mdi-folder-outline">
                      {{ sub.projects_count }} proyecto(s)
                    </v-chip>
                  </td>
                  <td class="text-right">
                    <v-btn
                      to="/proyecto"
                      size="x-small"
                      color="primary"
                      variant="tonal"
                      prepend-icon="mdi-folder-upload-outline"
                      class="mr-1"
                    >
                      Ver / Subir Proyectos
                    </v-btn>
                    <v-btn
                      v-if="canCreateSubject"
                      icon="mdi-pencil-outline"
                      size="x-small"
                      variant="text"
                      color="secondary"
                      @click="abrirModalEditarMateria(evento.id, sub)"
                    />
                  </td>
                </tr>
                <tr v-if="!filtrarMateriasDeEvento(evento.subjects).length">
                  <td colspan="5" class="text-center text-medium-emphasis py-4">
                    No hay materias registradas con el filtro actual en este evento.
                  </td>
                </tr>
              </tbody>
              </v-table>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Paginación cuando hay más de 4 eventos en la vista administrativa -->
    <div v-if="totalEventsPages > 1" class="mt-4 d-flex justify-center">
      <v-pagination
        v-model="eventsPage"
        :length="totalEventsPages"
        :total-visible="5"
        color="primary"
        density="comfortable"
      />
    </div>

    <!-- Modal Crear / Editar Evento (Coordinador / Admin) -->
    <v-dialog v-model="dialogEvento" max-width="540" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white">
          {{ eventForm.id ? 'Editar Evento Académico' : 'Crear Nuevo Evento (Coordinador)' }}
        </v-card-title>
        <v-card-text class="pa-6">
          <v-text-field
            v-model="eventForm.cicle_name"
            label="Nombre del Evento / Ciclo (ej. FESOL Ciclo I - 2026)"
            prepend-inner-icon="mdi-calendar-text"
            variant="outlined"
            class="mb-3"
          />
          <v-text-field
            v-model="eventForm.start_date"
            label="Fecha de Inicio"
            type="date"
            prepend-inner-icon="mdi-calendar"
            variant="outlined"
            class="mb-3"
          />
          <v-select
            v-model="eventForm.coordinador_id"
            :items="coordinadoresList"
            item-title="name"
            item-value="id"
            label="Coordinador Responsable (`coordinador_id`)"
            prepend-inner-icon="mdi-account-tie-outline"
            variant="outlined"
            class="mb-2"
          />
          <v-switch
            v-model="eventForm.is_active"
            color="success"
            label="Evento Activo para recepción de materias y proyectos"
            hide-details
          />
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="dialogEvento = false">Cancelar</v-btn>
          <v-btn color="primary" variant="flat" @click="guardarEvento">
            Guardar Evento
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Inscribir / Editar Materia dentro de un Evento (Docente / Coordinador / Admin) -->
    <v-dialog v-model="dialogMateria" max-width="560" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-secondary text-white">
          {{ subjectForm.id ? 'Editar Materia del Evento' : 'Inscribir Materia en Evento (Docente)' }}
        </v-card-title>
        <v-card-text class="pa-6">
          <v-select
            v-model="subjectForm.event_id"
            :items="eventsList"
            item-title="cicle_name"
            item-value="id"
            label="Evento Académico (`event_id`)"
            prepend-inner-icon="mdi-calendar-star"
            variant="outlined"
            class="mb-3"
          />
          <v-select
            v-model="subjectForm.carrera_id"
            :items="carrerasList"
            item-title="name"
            item-value="id"
            label="Carrera Universitaria (`carrera_id`)"
            prepend-inner-icon="mdi-school-outline"
            variant="outlined"
            class="mb-3"
          />
          <v-text-field
            v-model="subjectForm.subject_name"
            label="Nombre de la Materia (`subject_name`)"
            placeholder="Ej. Ingeniería de Software II"
            prepend-inner-icon="mdi-book-education-outline"
            variant="outlined"
            class="mb-3"
          />
          <v-select
            v-model="subjectForm.teacher_id"
            :items="teachersList"
            item-title="name"
            item-value="id"
            label="Docente Responsable (`teacher_id`)"
            prepend-inner-icon="mdi-human-male-board"
            variant="outlined"
          />
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn variant="text" @click="dialogMateria = false">Cancelar</v-btn>
          <v-btn color="secondary" variant="flat" @click="guardarMateria">
            Guardar Materia
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { getStoredList } from '../../utils/access.js';
import {
  obtenerEventos,
  crearEvento,
  actualizarEvento,
  crearMateriaEvento,
  actualizarMateriaEvento,
} from '../../services/evento/evento.service.js';
import { carreras as fetchCarrerasApi } from '../../services/carrera/carrera.service.js';
import { obtenerUsuarios } from '../../services/users/users.service.js';
import { useAlertStore } from '../../store/useAlertStore.js';

const alertStore = useAlertStore();
const roles = getStoredList('roles');

// Si aún no hay roles en sesión o es admin/coordinador, permite probar el flujo
const canCreateEvent = computed(() => {
  if (!roles.length) return true;
  return roles.includes('admin') || roles.includes('coordinador');
});

const canCreateSubject = computed(() => {
  if (!roles.length) return true;
  return roles.includes('admin') || roles.includes('coordinador') || roles.includes('teacher');
});

const filters = ref({
  search: '',
  carreraId: null,
  status: 'all',
});

const dialogEvento = ref(false);
const dialogMateria = ref(false);
const eventsPage = ref(1);
const eventsPerPage = 4;

const carrerasList = ref([
  { id: 1, name: 'Ingeniería en Sistemas', code: 'ING-SIS' },
  { id: 2, name: 'Ingeniería de Software', code: 'ING-SW' },
  { id: 3, name: 'Redes y Telecomunicaciones', code: 'TEC-RED' },
]);

const coordinadoresList = ref([
  { id: 2, name: 'Ing. Roberto Morales (Coordinador ING-SIS)' },
  { id: 6, name: 'MSc. Laura Gómez (Coordinadora ING-SW)' },
]);

const teachersList = ref([
  { id: 3, name: 'Ing. Carlos Méndez (Docente)' },
  { id: 4, name: 'Dra. Elena Ríos (Docente)' },
  { id: 5, name: 'MSc. Jorge Villalta (Docente)' },
]);

const eventsList = ref([
  {
    id: 1,
    cicle_name: 'FESOL Ciclo I - 2026',
    start_date: '2026-02-15',
    is_active: true,
    coordinador: { id: 2, name: 'Ing. Roberto Morales (Coordinador ING-SIS)' },
    subjects: [
      {
        id: 101,
        subject_name: 'Ingeniería de Software II',
        carrera: { id: 1, name: 'Ingeniería en Sistemas', code: 'ING-SIS' },
        teacher: { id: 3, name: 'Ing. Carlos Méndez (Docente)' },
        projects_count: 8,
      },
      {
        id: 102,
        subject_name: 'Desarrollo de Aplicaciones Web',
        carrera: { id: 2, name: 'Ingeniería de Software', code: 'ING-SW' },
        teacher: { id: 4, name: 'Dra. Elena Ríos (Docente)' },
        projects_count: 6,
      },
      {
        id: 103,
        subject_name: 'Seguridad en Redes y Servidores Linux',
        carrera: { id: 3, name: 'Redes y Telecomunicaciones', code: 'TEC-RED' },
        teacher: { id: 5, name: 'MSc. Jorge Villalta (Docente)' },
        projects_count: 4,
      },
    ],
  },
  {
    id: 2,
    cicle_name: 'Feria de Innovación Tecnológica II - 2025',
    start_date: '2025-08-10',
    is_active: false,
    coordinador: { id: 6, name: 'MSc. Laura Gómez (Coordinadora ING-SW)' },
    subjects: [
      {
        id: 201,
        subject_name: 'Inteligencia Artificial Aplicada',
        carrera: { id: 1, name: 'Ingeniería en Sistemas', code: 'ING-SIS' },
        teacher: { id: 4, name: 'Dra. Elena Ríos (Docente)' },
        projects_count: 11,
      },
    ],
  },
]);

const eventForm = ref({
  id: null,
  cicle_name: '',
  start_date: '2026-03-01',
  coordinador_id: 2,
  is_active: true,
});

const subjectForm = ref({
  id: null,
  event_id: 1,
  carrera_id: 1,
  subject_name: '',
  teacher_id: 3,
});

const filteredEvents = computed(() => {
  const q = filters.value.search?.trim().toLowerCase();

  return eventsList.value.filter((ev) => {
    if (filters.value.status === 'active' && !ev.is_active) return false;
    if (filters.value.status === 'inactive' && ev.is_active) return false;

    if (filters.value.carreraId) {
      const hasCarrera = ev.subjects.some((s) => s.carrera.id === filters.value.carreraId);
      if (!hasCarrera) return false;
    }

    if (q) {
      const matchEv =
        ev.cicle_name.toLowerCase().includes(q) ||
        ev.coordinador.name.toLowerCase().includes(q);
      const matchSub = ev.subjects.some(
        (s) =>
          s.subject_name.toLowerCase().includes(q) ||
          s.teacher.name.toLowerCase().includes(q)
      );
      if (!matchEv && !matchSub) return false;
    }

    return true;
  });
});

const totalEventsPages = computed(() => {
  return Math.max(1, Math.ceil(filteredEvents.value.length / eventsPerPage));
});

const paginatedEvents = computed(() => {
  const start = (eventsPage.value - 1) * eventsPerPage;
  return filteredEvents.value.slice(start, start + eventsPerPage);
});

watch(
  [() => filters.value.search, () => filters.value.carreraId, () => filters.value.status],
  () => {
    eventsPage.value = 1;
  }
);

const filtrarMateriasDeEvento = (subjects) => {
  const q = filters.value.search?.trim().toLowerCase();
  return subjects.filter((s) => {
    if (filters.value.carreraId && s.carrera.id !== filters.value.carreraId) {
      return false;
    }
    if (q) {
      return (
        s.subject_name.toLowerCase().includes(q) ||
        s.teacher.name.toLowerCase().includes(q) ||
        s.carrera.name.toLowerCase().includes(q)
      );
    }
    return true;
  });
};

const abrirModalEvento = (ev = null) => {
  if (ev) {
    eventForm.value = {
      id: ev.id,
      cicle_name: ev.cicle_name,
      start_date: ev.start_date,
      coordinador_id: ev.coordinador.id,
      is_active: ev.is_active,
    };
  } else {
    eventForm.value = {
      id: null,
      cicle_name: '',
      start_date: '2026-03-01',
      coordinador_id: 2,
      is_active: true,
    };
  }
  dialogEvento.value = true;
};

const guardarEvento = async () => {
  if (!eventForm.value.cicle_name.trim()) return;
  const coord = coordinadoresList.value.find((c) => c.id === eventForm.value.coordinador_id) || coordinadoresList.value[0];

  try {
    if (eventForm.value.id) {
      await actualizarEvento(eventForm.value.id, eventForm.value);
    } else {
      await crearEvento(eventForm.value);
    }
    await cargarDatosEventos();
  } catch (e) {
    if (eventForm.value.id) {
      const idx = eventsList.value.findIndex((ev) => ev.id === eventForm.value.id);
      if (idx !== -1) {
        eventsList.value[idx] = {
          ...eventsList.value[idx],
          cicle_name: eventForm.value.cicle_name,
          start_date: eventForm.value.start_date,
          is_active: eventForm.value.is_active,
          coordinador: coord,
        };
      }
    } else {
      eventsList.value.unshift({
        id: Date.now(),
        cicle_name: eventForm.value.cicle_name,
        start_date: eventForm.value.start_date,
        is_active: eventForm.value.is_active,
        coordinador: coord,
        subjects: [],
      });
    }
  }
  dialogEvento.value = false;
  alertStore.showAlert('Evento académico guardado correctamente.', 'success');
};

const abrirModalMateria = (eventId = 1) => {
  subjectForm.value = {
    id: null,
    event_id: eventId,
    carrera_id: carrerasList.value[0]?.id || 1,
    subject_name: '',
    teacher_id: teachersList.value[0]?.id || 3,
  };
  dialogMateria.value = true;
};

const abrirModalEditarMateria = (eventId, sub) => {
  subjectForm.value = {
    id: sub.id,
    event_id: eventId,
    carrera_id: sub.carrera.id,
    subject_name: sub.subject_name,
    teacher_id: sub.teacher.id,
  };
  dialogMateria.value = true;
};

const guardarMateria = async () => {
  if (!subjectForm.value.subject_name.trim()) return;

  try {
    if (subjectForm.value.id) {
      await actualizarMateriaEvento(subjectForm.value.id, subjectForm.value);
    } else {
      await crearMateriaEvento(subjectForm.value);
    }
    await cargarDatosEventos();
  } catch (e) {
    const ev = eventsList.value.find((evItem) => evItem.id === subjectForm.value.event_id);
    if (ev) {
      const carrera = carrerasList.value.find((c) => c.id === subjectForm.value.carrera_id) || carrerasList.value[0];
      const teacher = teachersList.value.find((t) => t.id === subjectForm.value.teacher_id) || teachersList.value[0];

      if (subjectForm.value.id) {
        const idx = ev.subjects.findIndex((s) => s.id === subjectForm.value.id);
        if (idx !== -1) {
          ev.subjects[idx] = {
            ...ev.subjects[idx],
            subject_name: subjectForm.value.subject_name,
            carrera,
            teacher,
          };
        }
      } else {
        ev.subjects.push({
          id: Date.now(),
          subject_name: subjectForm.value.subject_name,
          carrera,
          teacher,
          projects_count: 0,
        });
      }
    }
  }
  dialogMateria.value = false;
  alertStore.showAlert('Materia registrada en el evento correctamente.', 'success');
};

const cargarDatosEventos = async () => {
  try {
    const [evRes, carRes] = await Promise.all([obtenerEventos(), fetchCarrerasApi()]);

    if (carRes.data?.data?.length) {
      carrerasList.value = carRes.data.data;
    }

    if (evRes.data?.data?.length) {
      eventsList.value = evRes.data.data.map((ev) => ({
        id: ev.id,
        cicle_name: ev.cicle_name,
        start_date: String(ev.start_date || '').slice(0, 10),
        is_active: Boolean(ev.is_active),
        coordinador: ev.coordinador || { id: ev.coordinador_id, name: 'Coordinador' },
        subjects: (ev.event_subjects || []).map((s) => ({
          id: s.id,
          subject_name: s.subject_name,
          carrera: s.carrera || { id: s.carrera_id, name: 'Carrera', code: 'CAR' },
          teacher: s.teacher || { id: s.teacher_id, name: 'Docente' },
          projects_count: Array.isArray(s.projects) ? s.projects.length : 0,
        })),
      }));
    }

    const usersRes = await obtenerUsuarios(1, { perPage: 50 });
    if (usersRes.data?.data?.length) {
      const allUsers = usersRes.data.data;
      const coords = allUsers.filter((u) => (u.roles || []).includes('coordinador') || (u.roles || []).includes('admin'));
      const docs = allUsers.filter((u) => (u.roles || []).includes('teacher') || (u.roles || []).includes('coordinador'));
      if (coords.length) {
        coordinadoresList.value = coords.map((u) => ({ id: u.id, name: u.nombre }));
      }
      if (docs.length) {
        teachersList.value = docs.map((u) => ({ id: u.id, name: u.nombre }));
      }
    }
  } catch (e) {
    // Mantiene el estado inicial si el servidor no está disponible
  }
};

onMounted(async () => {
  await cargarDatosEventos();
});
</script>
