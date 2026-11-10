<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Banner principal compacto con buscador instantáneo -->
    <v-card rounded="xl" elevation="0" class="eventos-hero pa-5 pa-sm-6 pa-md-8 mb-6">
      <v-row align="center">
        <v-col cols="12" md="7">
          <div class="d-flex flex-wrap align-center ga-2 mb-2">
            <v-chip color="accent" variant="tonal" size="small" prepend-icon="mdi-trophy-outline">
              Repositorio Público de Eventos Universitarios
            </v-chip>
            <v-chip color="white" variant="outlined" size="small" class="d-none d-sm-inline-flex">
              Árbol Jerárquico: Evento → Carrera → Materia → Proyecto
            </v-chip>
          </div>
          <h1 class="text-h5 text-sm-h4 font-weight-bold text-white mb-2">
            Explorador de Eventos, Carreras y Proyectos Estudiantiles
          </h1>
          <p class="text-body-2 text-white opacity-85">
            Filtra desde el panel izquierdo por ciclo académico, carrera o materia sin perder de vista los proyectos ni hacer scroll innecesario.
          </p>
        </v-col>
        <v-col cols="12" md="5">
          <v-text-field
            v-model="filters.search"
            placeholder="Buscar proyecto, estudiante, carnet o tecnología (#tag)..."
            prepend-inner-icon="mdi-magnify"
            variant="solo"
            bg-color="surface"
            density="comfortable"
            rounded="lg"
            hide-details
            clearable
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- LAYOUT DE 2 PANELES (MASTER-DETAIL LATERAL) -->
    <v-row>
      <!-- PANEL IZQUIERDO (4 cols en md, 3 en lg): Árbol Jerárquico Evento -> Carrera -> Materia + Tecnologías -->
      <v-col cols="12" md="4" lg="3">
        <v-card rounded="xl" elevation="1" class="pa-4 border sticky-tree">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2 font-weight-bold text-primary d-flex align-center ga-1">
              <v-icon icon="mdi-file-tree-outline" size="small" />
              Navegación por Eventos
            </div>
            <v-btn
              v-if="hasActiveFilters"
              size="x-small"
              variant="text"
              color="error"
              @click="limpiarFiltros"
            >
              Ver todo
            </v-btn>
          </div>

          <!-- Buscador interno de eventos para cuando existan muchos ciclos registrados -->
          <v-text-field
            v-model="filters.eventSearch"
            placeholder="Filtrar ciclo o evento..."
            prepend-inner-icon="mdi-calendar-search"
            variant="outlined"
            density="compact"
            hide-details
            clearable
            class="mb-2"
          />

          <v-switch
            v-model="filters.onlyActiveEvent"
            color="success"
            density="compact"
            hide-details
            label="Solo eventos activos"
            class="mb-3"
          />

          <!-- Botón para ver absolutamente todos los eventos -->
          <v-btn
            block
            size="small"
            :color="!selectedEventId ? 'primary' : 'secondary'"
            :variant="!selectedEventId ? 'flat' : 'tonal'"
            prepend-icon="mdi-layers-triple-outline"
            class="mb-3 text-none justify-start h-auto py-2"
            @click="seleccionarTodo"
          >
            <span class="text-left" style="white-space: normal; word-break: break-word;">
              Todos los Eventos ({{ allFlattenedProjects.length }})
            </span>
          </v-btn>

          <!-- Slide / Ventana de Eventos -> Carreras -> Materias -->
          <v-window v-model="eventsTreePage">
            <v-window-item
              v-for="(eventGroup, gIdx) in eventTreeSlides"
              :key="gIdx"
              :value="gIdx + 1"
            >
              <div v-for="evento in eventGroup" :key="evento.id" class="mb-3">
                <v-sheet
                  rounded="lg"
                  class="pa-3 border tree-event overflow-hidden"
                  :class="{ 'tree-event--active': selectedEventId === evento.id }"
                >
                  <div
                    class="d-flex align-start justify-space-between ga-2 cursor-pointer"
                    @click="seleccionarEvento(evento.id)"
                  >
                    <div class="d-flex align-start ga-2 min-w-0 flex-grow-1">
                      <v-icon
                        size="small"
                        class="flex-shrink-0 mt-1"
                        :icon="selectedEventId === evento.id ? 'mdi-calendar-check' : 'mdi-calendar-outline'"
                        :color="selectedEventId === evento.id ? 'primary' : 'secondary'"
                      />
                      <div class="min-w-0 flex-grow-1">
                        <div class="text-body-2 font-weight-bold" style="word-break: break-word;">
                          {{ evento.cicle_name }}
                        </div>
                        <div class="text-caption text-medium-emphasis">
                          {{ contarProyectosEnEvento(evento) }} proyecto(s)
                        </div>
                      </div>
                    </div>
                    <v-chip
                      size="x-small"
                      :color="evento.is_active ? 'success' : 'grey'"
                      variant="flat"
                      class="flex-shrink-0"
                    >
                      {{ evento.is_active ? 'Activo' : 'Hist.' }}
                    </v-chip>
                  </div>

                  <!-- Sub-árbol de Carreras dentro del Evento (sin botones que corten texto) -->
                  <div v-if="selectedEventId === evento.id" class="mt-3 pt-2 border-t">
                    <div class="text-caption font-weight-bold text-medium-emphasis mb-2">
                      Carreras en este evento:
                    </div>

                    <div
                      v-for="carrera in obtenerCarrerasDeEvento(evento)"
                      :key="carrera.id"
                      class="mb-2"
                    >
                      <v-sheet
                        rounded="lg"
                        class="pa-2 cursor-pointer d-flex align-center justify-space-between ga-2 tree-carrera-item"
                        :class="{ 'tree-carrera-item--active': selectedCarreraId === carrera.id }"
                        @click="seleccionarCarrera(carrera.id)"
                      >
                        <div class="d-flex align-start ga-2 min-w-0 flex-grow-1">
                          <v-icon
                            icon="mdi-school-outline"
                            size="18"
                            class="flex-shrink-0 mt-0.5"
                            :color="selectedCarreraId === carrera.id ? 'primary' : undefined"
                          />
                          <div class="text-caption font-weight-semibold" style="line-height: 1.3; word-break: break-word;">
                            <strong>{{ carrera.code }}</strong> · {{ carrera.name }}
                          </div>
                        </div>
                        <v-chip size="x-small" variant="outlined" class="flex-shrink-0">
                          {{ carrera.projectsCount }}
                        </v-chip>
                      </v-sheet>

                      <!-- Sub-nivel: Materias de la Carrera seleccionada -->
                      <div
                        v-if="selectedCarreraId === carrera.id"
                        class="pl-3 pt-1 d-flex flex-column ga-1"
                      >
                        <v-sheet
                          v-for="sub in obtenerMateriasDeCarreraEnEvento(evento, carrera.id)"
                          :key="sub.id"
                          rounded="md"
                          class="pa-1.5 cursor-pointer d-flex align-center justify-space-between ga-1 tree-subject-item"
                          :class="{ 'tree-subject-item--active': filters.subjectName === sub.subject_name }"
                          @click="
                            filters.subjectName =
                              filters.subjectName === sub.subject_name ? null : sub.subject_name
                          "
                        >
                          <div class="d-flex align-start ga-1.5 min-w-0 flex-grow-1">
                            <v-icon icon="mdi-book-outline" size="15" class="flex-shrink-0 mt-0.5" />
                            <span class="text-caption" style="line-height: 1.25; word-break: break-word;">
                              {{ sub.subject_name }}
                            </span>
                          </div>
                          <span class="text-caption font-weight-bold flex-shrink-0">
                            ({{ sub.projects.length }})
                          </span>
                        </v-sheet>
                      </div>
                    </div>
                  </div>
                </v-sheet>
              </div>
            </v-window-item>
          </v-window>

          <!-- Controles de Slide cuando hay varios eventos (paginación de 4 en 4) -->
          <div v-if="totalEventsTreePages > 1" class="d-flex align-center justify-space-between mt-2 pt-2 border-t">
            <v-btn
              icon="mdi-chevron-left"
              size="x-small"
              variant="tonal"
              color="primary"
              :disabled="eventsTreePage <= 1"
              @click="eventsTreePage--"
            />
            <span class="text-caption font-weight-semibold text-medium-emphasis">
              Slide {{ eventsTreePage }} de {{ totalEventsTreePages }} ({{ filteredEvents.length }} eventos)
            </span>
            <v-btn
              icon="mdi-chevron-right"
              size="x-small"
              variant="tonal"
              color="primary"
              :disabled="eventsTreePage >= totalEventsTreePages"
              @click="eventsTreePage++"
            />
          </div>

          <v-divider class="my-3" />

          <!-- Filtro Semántico por Tecnología (#Tag) conectado con la Wiki -->
          <div class="text-subtitle-2 font-weight-bold text-primary mb-2 d-flex align-center ga-1">
            <v-icon icon="mdi-tag-multiple-outline" size="small" />
            Filtrar por Tecnología
          </div>
          <div class="d-flex flex-wrap ga-1 mb-3">
            <v-chip
              v-for="tech in availableTechTags"
              :key="tech"
              size="small"
              :color="filters.techTag === tech ? 'primary' : undefined"
              :variant="filters.techTag === tech ? 'flat' : 'tonal'"
              @click="filters.techTag = filters.techTag === tech ? null : tech"
            >
              #{{ tech }}
            </v-chip>
          </div>

          <v-sheet
            v-if="filters.techTag"
            rounded="lg"
            color="primary"
            class="pa-3 text-white"
          >
            <div class="text-caption font-weight-bold mb-1">
              ¿Quieres aprender #{{ filters.techTag }}?
            </div>
            <v-btn
              size="x-small"
              color="white"
              variant="flat"
              block
              class="text-primary font-weight-bold text-none mt-1"
              :to="{ path: '/explorar/wiki', query: { tag: filters.techTag } }"
              append-icon="mdi-book-open-page-variant-outline"
            >
              Ver Tutoriales de #{{ filters.techTag }}
            </v-btn>
          </v-sheet>
        </v-card>
      </v-col>

      <!-- PANEL DERECHO PRINCIPAL (8 cols en md, 9 en lg): Resultados Inmediatos de Proyectos -->
      <v-col cols="12" md="8" lg="9">
        <!-- Barra de estado de filtros activos y selector rápido -->
        <v-card rounded="xl" elevation="1" class="pa-4 mb-4 border">
          <div class="d-flex flex-wrap align-center justify-space-between ga-3">
            <div>
              <div class="text-subtitle-1 font-weight-bold text-primary">
                Mostrando {{ filteredProjects.length }} Proyecto(s) Estudiantil(es)
              </div>
              <div class="d-flex flex-wrap align-center ga-1 mt-1">
                <v-chip
                  v-if="activeEvent"
                  size="small"
                  color="primary"
                  variant="tonal"
                  closable
                  @click:close="selectedEventId = null"
                >
                  Evento: {{ activeEvent.cicle_name }}
                </v-chip>
                <v-chip
                  v-if="activeCarreraObj"
                  size="small"
                  color="secondary"
                  variant="tonal"
                  closable
                  @click:close="selectedCarreraId = null"
                >
                  Carrera: {{ activeCarreraObj.code }} · {{ activeCarreraObj.name }}
                </v-chip>
                <v-chip
                  v-if="filters.subjectName"
                  size="small"
                  color="info"
                  variant="tonal"
                  closable
                  @click:close="filters.subjectName = null"
                >
                  Materia: {{ filters.subjectName }}
                </v-chip>
                <v-chip
                  v-if="filters.techTag"
                  size="small"
                  color="success"
                  variant="tonal"
                  closable
                  @click:close="filters.techTag = null"
                >
                  Tecnología: #{{ filters.techTag }}
                </v-chip>
              </div>
            </div>

            <div class="d-flex align-center ga-2 w-100 w-sm-auto">
              <v-select
                v-model="filters.status"
                :items="[
                  { title: 'Todos los estados', value: 'all' },
                  { title: 'Aprobados / Publicados', value: 'approved' },
                  { title: 'En revisión / Pendientes', value: 'pending' },
                ]"
                density="compact"
                variant="outlined"
                hide-details
                class="w-100"
                style="min-width: 190px;"
              />
            </div>
          </div>

          <!-- Chips rápidos de Carreras del Evento Activo para cambiar con 1 clic arriba -->
          <div v-if="activeEvent && obtenerCarrerasDeEvento(activeEvent).length" class="d-flex flex-wrap align-center ga-2 mt-3 pt-3 border-t">
            <span class="text-caption font-weight-bold text-medium-emphasis">
              Cambio rápido de carrera en {{ activeEvent.cicle_name }}:
            </span>
            <v-chip
              size="small"
              :color="!selectedCarreraId ? 'primary' : undefined"
              :variant="!selectedCarreraId ? 'flat' : 'outlined'"
              @click="selectedCarreraId = null"
            >
              Todas
            </v-chip>
            <v-chip
              v-for="car in obtenerCarrerasDeEvento(activeEvent)"
              :key="car.id"
              size="small"
              :color="selectedCarreraId === car.id ? 'primary' : undefined"
              :variant="selectedCarreraId === car.id ? 'flat' : 'tonal'"
              @click="seleccionarCarrera(car.id)"
            >
              {{ car.code }} ({{ car.projectsCount }})
            </v-chip>
          </div>
        </v-card>

        <!-- Cuadrícula de Proyectos Paginada -->
        <v-row v-if="paginatedProjects.length">
          <v-col
            v-for="project in paginatedProjects"
            :key="project.id"
            cols="12"
            lg="6"
          >
            <v-card rounded="xl" elevation="1" class="pa-5 h-100 d-flex flex-column border">
              <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-2">
                <div class="d-flex flex-wrap align-center ga-1">
                  <v-chip size="x-small" color="primary" variant="flat">
                    {{ project.event_name }}
                  </v-chip>
                  <v-chip size="x-small" color="secondary" variant="tonal">
                    {{ project.carrera.code }} · {{ project.carrera.name }}
                  </v-chip>
                </div>
                <v-chip
                  size="x-small"
                  :color="project.status === 'approved' ? 'success' : 'warning'"
                  variant="tonal"
                >
                  {{ project.status === 'approved' ? 'Aprobado' : 'En revisión' }}
                </v-chip>
              </div>

              <h3 class="text-h6 font-weight-bold mb-1">{{ project.title }}</h3>
              <div class="text-caption text-primary font-weight-semibold mb-2">
                Materia: {{ project.subject_name }} · Docente: {{ project.teacher.name }}
              </div>

              <p class="text-body-2 text-medium-emphasis mb-3 flex-grow-1">
                {{ project.description }}
              </p>

              <!-- Chips de Tecnologías (#Tags) conectados con la Wiki -->
              <div v-if="project.tags?.length" class="d-flex flex-wrap ga-1 mb-3">
                <v-chip
                  v-for="tag in project.tags"
                  :key="tag"
                  size="x-small"
                  color="secondary"
                  variant="tonal"
                  @click="filters.techTag = tag"
                >
                  #{{ tag }}
                </v-chip>
              </div>

              <!-- Integrantes del Proyecto -->
              <div class="mb-3">
                <div class="text-caption font-weight-bold text-medium-emphasis mb-1">
                  Equipo de Estudiantes:
                </div>
                <div class="d-flex flex-wrap ga-1">
                  <v-chip
                    v-for="member in project.members"
                    :key="member.id"
                    size="small"
                    :color="member.is_leader ? 'primary' : undefined"
                    :variant="member.is_leader ? 'flat' : 'outlined'"
                    :prepend-icon="member.is_leader ? 'mdi-star-circle' : 'mdi-account-outline'"
                  >
                    {{ member.name }} {{ member.is_leader ? '(Líder)' : '' }}
                  </v-chip>
                </div>
              </div>

              <v-divider class="my-3" />

              <div class="d-flex flex-wrap align-center justify-space-between ga-2">
                <div class="d-flex align-center ga-2">
                  <v-btn
                    v-if="project.repository_url"
                    :href="project.repository_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    size="small"
                    variant="tonal"
                    color="secondary"
                    prepend-icon="mdi-github"
                  >
                    Repositorio
                  </v-btn>
                  <v-chip size="small" variant="text" prepend-icon="mdi-paperclip">
                    {{ project.files.length }} archivo(s)
                  </v-chip>
                </div>

                <v-btn
                  color="primary"
                  variant="flat"
                  size="small"
                  prepend-icon="mdi-eye-outline"
                  @click="verDetalleProyecto(project)"
                >
                  Ver Detalle y Archivos
                </v-btn>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Paginación de Proyectos -->
        <div v-if="totalProjectsPages > 1" class="mt-4 d-flex justify-center">
          <v-pagination
            v-model="projectsPage"
            :length="totalProjectsPages"
            :total-visible="5"
            color="primary"
            density="comfortable"
          />
        </div>

        <v-sheet v-if="!paginatedProjects.length" rounded="xl" class="pa-12 text-center border bg-surface">
          <v-icon icon="mdi-folder-search-outline" size="56" color="secondary" class="mb-2" />
          <div class="text-subtitle-1 font-weight-bold">
            No hay proyectos que coincidan con los filtros seleccionados
          </div>
          <div class="text-body-2 text-medium-emphasis mb-4">
            Prueba seleccionando otra carrera en el árbol izquierdo o limpiando los filtros.
          </div>
          <v-btn color="primary" variant="tonal" size="small" @click="limpiarFiltros">
            Mostrar todos los proyectos
          </v-btn>
        </v-sheet>
      </v-col>
    </v-row>

    <!-- Modal de Detalle de Proyecto y Descarga de Entregables -->
    <v-dialog v-model="projectDialog" max-width="760" scrollable>
      <v-card v-if="activeProject" rounded="xl">
        <v-card-title class="pa-6 bg-primary text-white d-flex justify-space-between align-start">
          <div>
            <div class="d-flex flex-wrap ga-2 mb-2">
              <v-chip size="small" color="accent" variant="flat" class="text-primary font-weight-bold">
                {{ activeProject.event_name }}
              </v-chip>
              <v-chip size="small" color="white" variant="outlined">
                {{ activeProject.carrera.name }}
              </v-chip>
            </div>
            <h2 class="text-h5 font-weight-bold text-wrap">{{ activeProject.title }}</h2>
          </div>
          <v-btn icon="mdi-close" variant="text" color="white" @click="projectDialog = false" />
        </v-card-title>

        <v-card-text class="pa-6">
          <v-row class="mb-4">
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Materia Asignada</div>
              <div class="text-subtitle-1 font-weight-bold">{{ activeProject.subject_name }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Docente Responsable</div>
              <div class="text-subtitle-1 font-weight-bold">{{ activeProject.teacher.name }}</div>
            </v-col>
          </v-row>

          <div class="mb-5">
            <div class="text-subtitle-2 font-weight-bold mb-1">Descripción del Proyecto</div>
            <p class="text-body-2 text-medium-emphasis">{{ activeProject.description }}</p>
          </div>

          <div class="mb-5">
            <div class="text-subtitle-2 font-weight-bold mb-2">Integrantes del Equipo (`project_members`)</div>
            <v-list density="compact" class="border rounded-lg pa-0">
              <v-list-item
                v-for="member in activeProject.members"
                :key="member.id"
                :title="member.name"
                :subtitle="`Carnet: ${member.carnet}`"
              >
                <template #prepend>
                  <v-icon :icon="member.is_leader ? 'mdi-star-circle' : 'mdi-account-school-outline'" :color="member.is_leader ? 'primary' : 'secondary'" />
                </template>
                <template #append>
                  <v-chip v-if="member.is_leader" size="x-small" color="primary" variant="flat">
                    Líder de Equipo
                  </v-chip>
                </template>
              </v-list-item>
            </v-list>
          </div>

          <div>
            <div class="text-subtitle-2 font-weight-bold mb-2">Archivos y Documentación (`project_files`)</div>
            <v-list density="compact" class="border rounded-lg pa-0">
              <v-list-item
                v-for="file in activeProject.files"
                :key="file.id"
                :title="file.original_name"
                :subtitle="`Tipo: ${file.file_type.toUpperCase()} · Extensión: .${file.extension} · Tamaño: ${file.file_size_kb} KB`"
              >
                <template #prepend>
                  <v-icon icon="mdi-file-document-check-outline" color="primary" />
                </template>
                <template #append>
                  <v-btn
                    size="small"
                    variant="tonal"
                    color="primary"
                    prepend-icon="mdi-download"
                    :href="`/api/archivos-proyecto/${file.id}`"
                    target="_blank"
                  >
                    Descargar
                  </v-btn>
                </template>
              </v-list-item>
            </v-list>
          </div>
        </v-card-text>

        <v-divider />

        <v-card-actions class="pa-4 justify-space-between">
          <v-btn
            v-if="activeProject.repository_url"
            :href="activeProject.repository_url"
            target="_blank"
            color="secondary"
            variant="tonal"
            prepend-icon="mdi-github"
          >
            Abrir Repositorio Git
          </v-btn>
          <v-spacer />
          <v-btn color="primary" variant="flat" @click="projectDialog = false">
            Cerrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { obtenerEventos } from '../../services/evento/evento.service.js';

const route = useRoute();

const selectedEventId = ref(1);
const selectedCarreraId = ref(null);
const projectDialog = ref(false);
const activeProject = ref(null);
const eventsTreePage = ref(1);
const eventsPerPage = 4;
const projectsPage = ref(1);
const projectsPerPage = 6;

const filters = ref({
  search: '',
  eventSearch: '',
  subjectName: null,
  techTag: null,
  status: 'all',
  onlyActiveEvent: false,
});

const availableTechTags = ['laravel', 'vue3', 'docker', 'postgresql', 'git', 'seguridad'];

const eventsList = ref([
  {
    id: 1,
    cicle_name: 'FESOL Ciclo I - 2026',
    start_date: '2026-02-15',
    is_active: true,
    coordinador: { id: 2, name: 'Ing. Roberto Morales' },
    subjects: [
      {
        id: 101,
        subject_name: 'Ingeniería de Software II',
        carrera: { id: 1, name: 'Ingeniería en Sistemas', code: 'ING-SIS' },
        teacher: { id: 3, name: 'Ing. Carlos Méndez' },
        projects: [
          {
            id: 1001,
            title: 'Sistema de Gestión Hospitalaria OpenSource con Telemedicina',
            description: 'Plataforma web para control de citas médicas, expediente clínico electrónico y videoconsultas en tiempo real.',
            repository_url: 'https://github.com/fesol-tda/hospital-opensource',
            status: 'approved',
            tags: ['laravel', 'vue3', 'docker', 'postgresql'],
            members: [
              { id: 11, name: 'Ana Sofía Martínez', carnet: 'SM22014', is_leader: true },
              { id: 12, name: 'Diego Alejandro Pérez', carnet: 'PA22089', is_leader: false },
              { id: 13, name: 'Kevin Josué Hernández', carnet: 'HJ22041', is_leader: false },
            ],
            files: [
              { id: 1, file_type: 'documento', original_name: 'Manual_Arquitectura_Hospital.pdf', extension: 'pdf', file_size_kb: 2480 },
              { id: 2, file_type: 'codigo', original_name: 'release-v1.0.zip', extension: 'zip', file_size_kb: 14520 },
            ],
          },
          {
            id: 1002,
            title: 'Plataforma de Trazabilidad Agrícola con Sensores IoT',
            description: 'Monitoreo de humedad y temperatura en cultivos mediante nodos ESP32, API Laravel y panel reactivo en Vue.',
            repository_url: 'https://github.com/fesol-tda/agro-iot-trace',
            status: 'approved',
            tags: ['laravel', 'vue3', 'git'],
            members: [
              { id: 14, name: 'María Fernanda López', carnet: 'LF22003', is_leader: true },
              { id: 15, name: 'José Manuel Rivas', carnet: 'RM22077', is_leader: false },
            ],
            files: [
              { id: 3, file_type: 'documento', original_name: 'Informe_Tecnico_IoT.pdf', extension: 'pdf', file_size_kb: 1850 },
            ],
          },
        ],
      },
      {
        id: 102,
        subject_name: 'Desarrollo de Aplicaciones Web',
        carrera: { id: 2, name: 'Ingeniería de Software', code: 'ING-SW' },
        teacher: { id: 4, name: 'Dra. Elena Ríos' },
        projects: [
          {
            id: 1003,
            title: 'Repositorio Académico y Wiki Colaborativa Universitaria',
            description: 'Aplicación progresiva para documentación técnica estudiantil, gestión de eventos y evaluación de entregables.',
            repository_url: 'https://github.com/fesol-tda/wiki-universitaria',
            status: 'approved',
            tags: ['vue3', 'laravel', 'postgresql', 'git'],
            members: [
              { id: 16, name: 'Gabriel Antonio Castro', carnet: 'CA23019', is_leader: true },
              { id: 17, name: 'Valeria Michelle Ramírez', carnet: 'RM23052', is_leader: false },
            ],
            files: [
              { id: 4, file_type: 'documento', original_name: 'Especificacion_Requisitos_IEEE830.pdf', extension: 'pdf', file_size_kb: 3120 },
            ],
          },
        ],
      },
      {
        id: 103,
        subject_name: 'Seguridad en Redes y Servidores Linux',
        carrera: { id: 3, name: 'Redes y Telecomunicaciones', code: 'TEC-RED' },
        teacher: { id: 5, name: 'MSc. Jorge Villalta' },
        projects: [
          {
            id: 1004,
            title: 'Laboratorio Automatizado de Detección de Intrusos con Suricata y Wazuh',
            description: 'Despliegue reproducible con contenedores Docker para correlación de eventos de seguridad en redes universitarias.',
            repository_url: 'https://github.com/fesol-tda/ids-wazuh-lab',
            status: 'pending',
            tags: ['docker', 'seguridad', 'git'],
            members: [
              { id: 18, name: 'Cristian Eduardo Mejía', carnet: 'ME21034', is_leader: true },
            ],
            files: [
              { id: 5, file_type: 'documento', original_name: 'Guia_Despliegue_SIEM.pdf', extension: 'pdf', file_size_kb: 4100 },
            ],
          },
        ],
      },
    ],
  },
  {
    id: 2,
    cicle_name: 'Feria de Innovación Tecnológica II - 2025',
    start_date: '2025-08-10',
    is_active: false,
    coordinador: { id: 6, name: 'MSc. Laura Gómez' },
    subjects: [
      {
        id: 201,
        subject_name: 'Inteligencia Artificial Aplicada',
        carrera: { id: 1, name: 'Ingeniería en Sistemas', code: 'ING-SIS' },
        teacher: { id: 4, name: 'Dra. Elena Ríos' },
        projects: [
          {
            id: 2001,
            title: 'Clasificador de Plagas en Cultivos de Café mediante Visión Computacional',
            description: 'Modelo ligero entrenado sobre dataset local para diagnóstico temprano de roya en hojas de cafeto.',
            repository_url: 'https://github.com/fesol-tda/cafe-vision-ai',
            status: 'approved',
            tags: ['docker', 'postgresql'],
            members: [
              { id: 19, name: 'Daniela Alejandra Ortiz', carnet: 'OA20012', is_leader: true },
            ],
            files: [
              { id: 6, file_type: 'documento', original_name: 'Paper_Vision_Computacional.pdf', extension: 'pdf', file_size_kb: 5240 },
            ],
          },
        ],
      },
    ],
  },
]);

const filteredEvents = computed(() => {
  const eq = filters.value.eventSearch?.trim().toLowerCase();
  return eventsList.value.filter((ev) => {
    if (filters.value.onlyActiveEvent && !ev.is_active) {
      return false;
    }
    if (eq && !ev.cicle_name.toLowerCase().includes(eq)) {
      return false;
    }
    return true;
  });
});

const eventTreeSlides = computed(() => {
  const slides = [];
  for (let i = 0; i < filteredEvents.value.length; i += eventsPerPage) {
    slides.push(filteredEvents.value.slice(i, i + eventsPerPage));
  }
  return slides.length ? slides : [[]];
});

const totalEventsTreePages = computed(() => eventTreeSlides.value.length);

const activeEvent = computed(() => {
  return eventsList.value.find((e) => e.id === selectedEventId.value) || null;
});

const activeCarreraObj = computed(() => {
  if (!selectedCarreraId.value) return null;
  for (const ev of eventsList.value) {
    for (const sub of ev.subjects) {
      if (sub.carrera.id === selectedCarreraId.value) {
        return sub.carrera;
      }
    }
  }
  return null;
});

const inferTechTags = (title = '', desc = '') => {
  const text = `${title} ${desc}`.toLowerCase();
  const detected = [];
  if (text.includes('laravel') || text.includes('api') || text.includes('hospital')) detected.push('laravel');
  if (text.includes('vue') || text.includes('web') || text.includes('wiki')) detected.push('vue3');
  if (text.includes('docker') || text.includes('contenedor')) detected.push('docker');
  if (text.includes('postgres') || text.includes('datos') || text.includes('hospital')) detected.push('postgresql');
  if (text.includes('seguridad') || text.includes('wazuh') || text.includes('red')) detected.push('seguridad');
  if (!detected.length) detected.push('git');
  return detected;
};

const allFlattenedProjects = computed(() => {
  const result = [];
  for (const ev of eventsList.value) {
    for (const sub of ev.subjects) {
      for (const proj of sub.projects) {
        result.push({
          ...proj,
          tags: proj.tags?.length ? proj.tags : inferTechTags(proj.title, proj.description),
          event_id: ev.id,
          event_name: ev.cicle_name,
          event_active: ev.is_active,
          subject_id: sub.id,
          subject_name: sub.subject_name,
          carrera: sub.carrera,
          teacher: sub.teacher,
        });
      }
    }
  }
  return result;
});

const filteredProjects = computed(() => {
  const q = filters.value.search?.trim().toLowerCase();

  return allFlattenedProjects.value.filter((proj) => {
    if (selectedEventId.value && proj.event_id !== selectedEventId.value) return false;
    if (selectedCarreraId.value && proj.carrera.id !== selectedCarreraId.value) return false;
    if (filters.value.subjectName && proj.subject_name !== filters.value.subjectName) return false;
    if (filters.value.techTag && !(proj.tags || []).includes(filters.value.techTag)) return false;
    if (filters.value.status !== 'all' && proj.status !== filters.value.status) return false;

    if (q) {
      const inTitle = proj.title.toLowerCase().includes(q);
      const inDesc = proj.description.toLowerCase().includes(q);
      const inSubject = proj.subject_name.toLowerCase().includes(q);
      const inTags = (proj.tags || []).some((t) => t.toLowerCase().includes(q));
      const inMembers = proj.members.some(
        (m) => m.name.toLowerCase().includes(q) || m.carnet.toLowerCase().includes(q)
      );
      if (!inTitle && !inDesc && !inSubject && !inTags && !inMembers) return false;
    }

    return true;
  });
});

const totalProjectsPages = computed(() => {
  return Math.max(1, Math.ceil(filteredProjects.value.length / projectsPerPage));
});

const paginatedProjects = computed(() => {
  const start = (projectsPage.value - 1) * projectsPerPage;
  return filteredProjects.value.slice(start, start + projectsPerPage);
});

watch(
  [() => filters.value.search, () => filters.value.subjectName, () => filters.value.techTag, () => filters.value.status, selectedEventId, selectedCarreraId],
  () => {
    projectsPage.value = 1;
  }
);

watch(
  [() => filters.value.eventSearch, () => filters.value.onlyActiveEvent],
  () => {
    eventsTreePage.value = 1;
  }
);

const hasActiveFilters = computed(() => {
  return Boolean(
    filters.value.search ||
      filters.value.eventSearch ||
      selectedCarreraId.value ||
      filters.value.subjectName ||
      filters.value.techTag ||
      filters.value.status !== 'all' ||
      filters.value.onlyActiveEvent
  );
});

const obtenerCarrerasDeEvento = (evento) => {
  if (!evento) return [];
  const map = new Map();
  for (const sub of evento.subjects) {
    const curr = map.get(sub.carrera.id) || {
      ...sub.carrera,
      subjectsCount: 0,
      projectsCount: 0,
    };
    curr.subjectsCount += 1;
    curr.projectsCount += sub.projects.length;
    map.set(sub.carrera.id, curr);
  }
  return Array.from(map.values());
};

const obtenerMateriasDeCarreraEnEvento = (evento, carreraId) => {
  if (!evento) return [];
  return evento.subjects.filter((s) => s.carrera.id === carreraId);
};

const contarProyectosEnEvento = (evento) => {
  return evento.subjects.reduce((acc, s) => acc + s.projects.length, 0);
};

const seleccionarTodo = () => {
  selectedEventId.value = null;
  selectedCarreraId.value = null;
  filters.value.subjectName = null;
};

const seleccionarEvento = (id) => {
  selectedEventId.value = selectedEventId.value === id ? null : id;
  selectedCarreraId.value = null;
  filters.value.subjectName = null;
};

const seleccionarCarrera = (carreraId) => {
  selectedCarreraId.value = selectedCarreraId.value === carreraId ? null : carreraId;
  filters.value.subjectName = null;
};

const limpiarFiltros = () => {
  filters.value.search = '';
  filters.value.eventSearch = '';
  filters.value.subjectName = null;
  filters.value.techTag = null;
  filters.value.status = 'all';
  filters.value.onlyActiveEvent = false;
  selectedCarreraId.value = null;
  selectedEventId.value = eventsList.value[0]?.id || 1;
  eventsTreePage.value = 1;
};

const verDetalleProyecto = (project) => {
  activeProject.value = project;
  projectDialog.value = true;
};

watch(
  () => route.query,
  (q) => {
    if (q.q !== undefined) {
      filters.value.search = String(q.q || '');
      selectedEventId.value = null;
    }
    if (q.tag !== undefined) {
      filters.value.techTag = q.tag ? String(q.tag) : null;
      selectedEventId.value = null;
    }
  },
  { immediate: true }
);

onMounted(async () => {
  try {
    const res = await obtenerEventos();
    if (res.data?.data?.length) {
      eventsList.value = res.data.data.map((ev) => ({
        id: ev.id,
        cicle_name: ev.cicle_name,
        start_date: String(ev.start_date || '').slice(0, 10),
        is_active: Boolean(ev.is_active),
        coordinador: ev.coordinador || { id: ev.coordinador_id, name: 'Coordinador' },
        subjects: (ev.event_subjects || []).map((sub) => ({
          id: sub.id,
          subject_name: sub.subject_name,
          carrera: sub.carrera || { id: sub.carrera_id, name: 'Carrera', code: 'CAR' },
          teacher: sub.teacher || { id: sub.teacher_id, name: 'Docente' },
          projects: (sub.projects || []).map((proj) => ({
            id: proj.id,
            title: proj.title,
            description: proj.description,
            repository_url: proj.repository_url,
            status: proj.status,
            tags: inferTechTags(proj.title, proj.description),
            members: (proj.members || []).map((m) => ({
              id: m.student?.id ?? m.student_id,
              name: m.student?.name || 'Estudiante',
              carnet: m.student?.carnet_or_code || 'S/C',
              is_leader: Boolean(m.is_leader),
            })),
            files: proj.files || [],
          })),
        })),
      }));
      if (!route.query.q && !route.query.tag && eventsList.value.length) {
        selectedEventId.value = eventsList.value[0].id;
      }
    }
  } catch (e) {
    // Mantiene respaldo local si la API no está corriendo aún
  }
});
</script>

<style scoped>
.eventos-hero {
  background: linear-gradient(135deg, #0d1239 0%, #1f2d7f 100%);
}

@media (min-width: 960px) {
  .sticky-tree {
    position: sticky;
    top: 88px;
  }
}

.tree-event {
  transition: all 0.18s ease;
}

.tree-event--active {
  border-color: rgb(var(--v-theme-primary)) !important;
  background: rgba(var(--v-theme-primary), 0.04);
}

.tree-carrera-item {
  transition: background 0.15s ease;
}

.tree-carrera-item:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.tree-carrera-item--active {
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
}

.tree-subject-item:hover {
  background: rgba(var(--v-theme-secondary), 0.08);
}

.tree-subject-item--active {
  background: rgb(var(--v-theme-secondary));
  color: rgb(var(--v-theme-on-secondary));
}

.cursor-pointer {
  cursor: pointer;
}
</style>
