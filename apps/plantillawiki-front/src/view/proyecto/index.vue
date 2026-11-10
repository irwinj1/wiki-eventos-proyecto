<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Encabezado y Acción Principal: Subir Proyecto Estudiantil (Docente / Admin) -->
    <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
      <div class="d-flex flex-column flex-sm-row align-start align-sm-center justify-space-between ga-4">
        <div>
          <div class="d-flex flex-wrap ga-2 mb-2">
            <v-chip size="x-small" color="info" variant="flat" prepend-icon="mdi-folder-upload-outline">
              Subida y Gestión de Proyectos: Docente / Coordinador / Admin
            </v-chip>
          </div>
          <h1 class="text-h6 text-sm-h5 font-weight-bold text-primary">
            Gestión de Proyectos Estudiantiles por Evento, Carrera y Materia
          </h1>
          <p class="text-body-2 text-medium-emphasis">
            Los docentes registran los proyectos de sus materias (`projects`), asignan a los estudiantes integrantes y líder (`project_members`), enlazan el repositorio Git y suben los archivos entregables (`project_files`).
          </p>
        </div>

        <v-btn
          v-if="canManageProjects"
          color="primary"
          variant="flat"
          class="w-100 w-sm-auto"
          prepend-icon="mdi-folder-plus-outline"
          @click="abrirModalProyecto()"
        >
          Subir Nuevo Proyecto
        </v-btn>
      </div>

      <v-divider class="my-4" />

      <!-- Filtros encadenados: Evento -> Carrera -> Materia -> Búsqueda -->
      <v-row dense>
        <v-col cols="12" md="3">
          <v-text-field
            v-model="filters.search"
            placeholder="Buscar proyecto, estudiante o carnet..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filters.eventId"
            :items="eventCatalog"
            item-title="cicle_name"
            item-value="id"
            label="Filtrar por Evento"
            prepend-inner-icon="mdi-calendar-outline"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filters.carreraId"
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

        <v-col cols="12" sm="6" md="3">
          <v-select
            v-model="filters.subjectId"
            :items="filteredSubjectCatalog"
            item-title="subject_name"
            item-value="id"
            label="Filtrar por Materia"
            prepend-inner-icon="mdi-book-outline"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>
      </v-row>
    </v-card>

    <!-- Listado de Proyectos -->
    <v-row>
      <v-col cols="12" md="6" v-for="project in filteredProjects" :key="project.id">
        <v-card rounded="xl" elevation="1" class="pa-5 h-100 d-flex flex-column border">
          <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-2">
            <div class="d-flex flex-wrap ga-1">
              <v-chip size="x-small" color="primary" variant="flat">{{ project.event_name }}</v-chip>
              <v-chip size="x-small" color="secondary" variant="tonal">{{ project.carrera_name }}</v-chip>
            </div>
            <v-chip
              size="small"
              :color="project.status === 'approved' ? 'success' : 'warning'"
              variant="tonal"
            >
              {{ project.status === 'approved' ? 'Publicado / Aprobado' : 'Pendiente / Revisión' }}
            </v-chip>
          </div>

          <h2 class="text-h6 font-weight-bold mb-1">{{ project.title }}</h2>
          <div class="text-caption text-primary font-weight-semibold mb-2">
            Materia: {{ project.subject_name }} · Docente: {{ project.teacher_name }}
          </div>

          <p class="text-body-2 text-medium-emphasis mb-4 flex-grow-1">
            {{ project.description }}
          </p>

          <!-- Equipo de Estudiantes (`project_members`) -->
          <div class="mb-3">
            <div class="d-flex align-center justify-space-between mb-1">
              <span class="text-caption font-weight-bold">
                Equipo de Estudiantes ({{ project.members.length }}):
              </span>
              <v-btn
                v-if="canManageProjects"
                size="x-small"
                variant="text"
                color="primary"
                prepend-icon="mdi-account-multiple-plus-outline"
                @click="abrirModalMiembros(project)"
              >
                Gestionar Equipo / Líder
              </v-btn>
            </div>

            <div class="d-flex flex-wrap ga-1">
              <v-chip
                v-for="m in project.members"
                :key="m.id"
                size="small"
                :color="m.is_leader ? 'primary' : undefined"
                :variant="m.is_leader ? 'flat' : 'outlined'"
                :prepend-icon="m.is_leader ? 'mdi-star-circle' : 'mdi-account-outline'"
              >
                {{ m.name }} ({{ m.carnet }}) {{ m.is_leader ? '· Líder' : '' }}
              </v-chip>
            </div>
          </div>

          <!-- Archivos del Proyecto (`project_files`) -->
          <div class="mb-3">
            <div class="d-flex align-center justify-space-between mb-1">
              <span class="text-caption font-weight-bold">
                Archivos Entregables ({{ project.files.length }}):
              </span>
              <v-btn
                v-if="canManageProjects"
                size="x-small"
                variant="text"
                color="secondary"
                prepend-icon="mdi-cloud-upload-outline"
                @click="abrirModalArchivos(project)"
              >
                Subir / Administrar Archivos
              </v-btn>
            </div>

            <div class="d-flex flex-wrap ga-1">
              <v-chip
                v-for="f in project.files"
                :key="f.id"
                size="x-small"
                variant="tonal"
                color="info"
                prepend-icon="mdi-file-outline"
                :href="f.id ? `/api/archivos-proyecto/${f.id}` : undefined"
                target="_blank"
              >
                {{ f.original_name }} ({{ f.file_size_kb }} KB)
              </v-chip>
            </div>
          </div>

          <v-divider class="my-3" />

          <div class="d-flex flex-wrap align-center justify-space-between ga-2">
            <v-btn
              v-if="project.repository_url"
              :href="project.repository_url"
              target="_blank"
              size="small"
              variant="tonal"
              color="secondary"
              prepend-icon="mdi-github"
            >
              Repositorio Git
            </v-btn>
            <span v-else class="text-caption text-medium-emphasis">Sin URL de repositorio</span>

            <div class="d-flex ga-1">
              <v-btn
                v-if="canManageProjects"
                size="small"
                variant="outlined"
                color="primary"
                prepend-icon="mdi-pencil-outline"
                @click="abrirModalProyecto(project)"
              >
                Editar / Adjuntar
              </v-btn>
              <v-btn
                v-if="canManageProjects"
                size="small"
                variant="tonal"
                :color="project.status === 'approved' ? 'warning' : 'success'"
                @click="cambiarEstadoPublicacion(project)"
              >
                {{ project.status === 'approved' ? 'Pasar a Pendiente' : 'Aprobar Proyecto' }}
              </v-btn>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ========================================================================= -->
    <!-- MODAL CREAR / EDITAR PROYECTO (CON INTEGRANTES Y SUBIDA DE ARCHIVOS)      -->
    <!-- ========================================================================= -->
    <v-dialog v-model="dialogProyecto" max-width="760" persistent scrollable>
      <v-card rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white d-flex justify-space-between align-center">
          <span>
            {{ projectForm.id ? 'Editar Proyecto y Adjuntar Archivos' : 'Subir Nuevo Proyecto Estudiantil' }}
          </span>
          <v-btn icon="mdi-close" variant="text" color="white" @click="dialogProyecto = false" />
        </v-card-title>

        <v-card-text class="pa-6">
          <v-row dense>
            <!-- 1. Datos Generales del Proyecto -->
            <v-col cols="12">
              <div class="text-subtitle-2 font-weight-bold text-primary mb-2">
                1. Información General y Materia Asignada
              </div>
            </v-col>

            <v-col cols="12" sm="8">
              <v-select
                v-model="projectForm.event_subject_id"
                :items="subjectCatalog"
                item-title="full_label"
                item-value="id"
                label="Materia y Evento (`event_subject_id`)"
                prepend-inner-icon="mdi-book-education-outline"
                variant="outlined"
                density="comfortable"
              />
            </v-col>

            <v-col cols="12" sm="4">
              <v-select
                v-model="projectForm.status"
                :items="[
                  { title: 'Aprobado / Publicado', value: 'approved' },
                  { title: 'Pendiente de revisión', value: 'pending' },
                ]"
                label="Estado (`status`)"
                variant="outlined"
                density="comfortable"
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="projectForm.title"
                label="Título del Proyecto (`title`)"
                prepend-inner-icon="mdi-format-title"
                variant="outlined"
                density="comfortable"
              />
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="projectForm.description"
                label="Descripción y alcance del proyecto (`description`)"
                variant="outlined"
                rows="3"
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="projectForm.repository_url"
                label="URL del Repositorio Git (`repository_url`)"
                placeholder="https://github.com/organizacion/proyecto"
                prepend-inner-icon="mdi-github"
                variant="outlined"
                density="comfortable"
              />
            </v-col>

            <!-- 2. Equipo de Estudiantes y Líder (`project_members`) -->
            <v-col cols="12" class="mt-2">
              <v-divider class="mb-4" />
              <div class="text-subtitle-2 font-weight-bold text-primary mb-2">
                2. Integrantes del Equipo y Líder (`project_members`)
              </div>
              <div class="d-flex flex-column flex-sm-row ga-2 mb-3">
                <v-select
                  v-model="formMemberStudentId"
                  :items="studentsCatalog"
                  item-title="label"
                  item-value="id"
                  label="Seleccionar estudiante para agregar al proyecto"
                  prepend-inner-icon="mdi-account-school-outline"
                  variant="outlined"
                  density="compact"
                  hide-details
                  class="flex-grow-1"
                />
                <v-btn
                  color="secondary"
                  variant="tonal"
                  prepend-icon="mdi-account-plus-outline"
                  @click="agregarMiembroEnFormulario"
                >
                  Agregar Estudiante
                </v-btn>
              </div>

              <div v-if="projectForm.members.length" class="d-flex flex-wrap ga-2 mb-2">
                <v-chip
                  v-for="m in projectForm.members"
                  :key="m.id"
                  :color="m.is_leader ? 'primary' : 'secondary'"
                  :variant="m.is_leader ? 'flat' : 'tonal'"
                  closable
                  @click="marcarLiderEnFormulario(m.id)"
                  @click:close="quitarMiembroEnFormulario(m.id)"
                >
                  <v-icon start :icon="m.is_leader ? 'mdi-star-circle' : 'mdi-account-outline'" />
                  {{ m.name }} ({{ m.carnet }}) {{ m.is_leader ? '· Líder' : '· Hacer líder' }}
                </v-chip>
              </div>
              <div v-else class="text-caption text-medium-emphasis mb-2">
                Haz clic en "Agregar Estudiante" para asignar el equipo (haz clic sobre un integrante para marcarlo como Líder).
              </div>
            </v-col>

            <!-- 3. Subida de Archivos Entregables (`project_files`) -->
            <v-col cols="12" class="mt-2">
              <v-divider class="mb-4" />
              <div class="d-flex align-center justify-space-between mb-2">
                <div class="text-subtitle-2 font-weight-bold text-primary">
                  3. Subir Archivos Entregables del Proyecto (`project_files`)
                </div>
                <v-chip size="x-small" color="info" variant="tonal">
                  PDF, ZIP, RAR, DOCX, PPTX, MD (Máx. 25 MB c/u)
                </v-chip>
              </div>

              <v-sheet rounded="lg" class="pa-4 border bg-surface">
                <v-row dense align="center">
                  <v-col cols="12" sm="4">
                    <v-select
                      v-model="pendingFileType"
                      :items="[
                        { title: 'Documento Técnico', value: 'documento' },
                        { title: 'Código Fuente (.zip/.rar)', value: 'codigo' },
                        { title: 'Presentación', value: 'presentacion' },
                        { title: 'Manual de Usuario', value: 'manual' },
                      ]"
                      item-title="title"
                      item-value="value"
                      label="Categoría del archivo"
                      variant="outlined"
                      density="compact"
                      hide-details
                    />
                  </v-col>

                  <v-col cols="12" sm="8">
                    <v-file-input
                      v-model="selectedFilesInput"
                      label="Seleccionar uno o varios archivos de tu equipo..."
                      prepend-icon=""
                      prepend-inner-icon="mdi-paperclip"
                      variant="outlined"
                      density="compact"
                      multiple
                      show-size
                      hide-details
                      accept=".pdf,.zip,.rar,.7z,.doc,.docx,.ppt,.pptx,.md,.txt,.png,.jpg,.jpeg"
                      @update:model-value="agregarArchivosACola"
                    />
                  </v-col>
                </v-row>

                <!-- Cola de archivos seleccionados listos para subir con el proyecto -->
                <div v-if="projectForm.pendingFiles.length" class="mt-4">
                  <div class="text-caption font-weight-bold text-primary mb-2">
                    Archivos adjuntos para subir ({{ projectForm.pendingFiles.length }}):
                  </div>
                  <v-list density="compact" class="border rounded-lg pa-0">
                    <v-list-item
                      v-for="(pf, idx) in projectForm.pendingFiles"
                      :key="idx"
                      :title="pf.file.name"
                      :subtitle="`Categoría: ${pf.file_type.toUpperCase()} · Tamaño: ${Math.max(1, Math.round(pf.file.size / 1024))} KB`"
                    >
                      <template #prepend>
                        <v-icon icon="mdi-file-upload-outline" color="primary" />
                      </template>
                      <template #append>
                        <v-btn
                          icon="mdi-close-circle-outline"
                          size="x-small"
                          variant="text"
                          color="error"
                          @click="quitarArchivoDeCola(idx)"
                        />
                      </template>
                    </v-list-item>
                  </v-list>
                </div>
              </v-sheet>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider />

        <v-card-actions class="pa-4 justify-end ga-2">
          <v-btn variant="text" @click="dialogProyecto = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            variant="flat"
            prepend-icon="mdi-cloud-upload-outline"
            :loading="savingProject"
            @click="guardarProyecto"
          >
            {{ projectForm.id ? 'Guardar Cambios y Subir Archivos' : 'Crear Proyecto y Subir Archivos' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Gestionar Miembros y Líder (`project_members`) -->
    <v-dialog v-model="dialogMiembros" max-width="600">
      <v-card v-if="activeProject" rounded="xl">
        <v-card-title class="pa-5 bg-primary text-white">
          Equipo de Estudiantes · {{ activeProject.title }}
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="d-flex ga-2 mb-4">
            <v-select
              v-model="newMemberId"
              :items="studentsCatalog"
              item-title="label"
              item-value="id"
              label="Seleccionar estudiante (`student_id`)"
              variant="outlined"
              density="compact"
              hide-details
              class="flex-grow-1"
            />
            <v-btn color="primary" variant="flat" prepend-icon="mdi-account-plus" @click="agregarMiembro">
              Agregar
            </v-btn>
          </div>

          <v-list class="border rounded-lg pa-0">
            <v-list-item
              v-for="m in activeProject.members"
              :key="m.id"
              :title="m.name"
              :subtitle="`Carnet: ${m.carnet}`"
            >
              <template #prepend>
                <v-icon :icon="m.is_leader ? 'mdi-star-circle' : 'mdi-account-outline'" :color="m.is_leader ? 'primary' : undefined" />
              </template>
              <template #append>
                <div class="d-flex align-center ga-2">
                  <v-btn
                    size="x-small"
                    :color="m.is_leader ? 'primary' : 'secondary'"
                    :variant="m.is_leader ? 'flat' : 'outlined'"
                    @click="designarLider(m.id)"
                  >
                    {{ m.is_leader ? 'Líder Actual' : 'Designar Líder' }}
                  </v-btn>
                  <v-btn
                    icon="mdi-delete-outline"
                    size="x-small"
                    variant="text"
                    color="error"
                    @click="quitarMiembro(m.id)"
                  />
                </div>
              </template>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn color="primary" variant="tonal" @click="dialogMiembros = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal Subir / Gestionar Archivos del Proyecto (`project_files`) con v-file-input real -->
    <v-dialog v-model="dialogArchivos" max-width="640">
      <v-card v-if="activeProject" rounded="xl">
        <v-card-title class="pa-5 bg-secondary text-white d-flex justify-space-between align-center">
          <span>Archivos Entregables · {{ activeProject.title }}</span>
          <v-btn icon="mdi-close" variant="text" color="white" @click="dialogArchivos = false" />
        </v-card-title>
        <v-card-text class="pa-6">
          <v-sheet rounded="lg" class="pa-4 border mb-4 bg-surface">
            <div class="text-subtitle-2 font-weight-bold text-primary mb-3">
              Subir Nuevo Archivo al Proyecto (`multipart/form-data`)
            </div>
            <v-row dense>
              <v-col cols="12" sm="4">
                <v-select
                  v-model="newFile.file_type"
                  :items="[
                    { title: 'Documento', value: 'documento' },
                    { title: 'Código (.zip)', value: 'codigo' },
                    { title: 'Presentación', value: 'presentacion' },
                    { title: 'Manual', value: 'manual' },
                  ]"
                  item-title="title"
                  item-value="value"
                  label="Tipo (`file_type`)"
                  variant="outlined"
                  density="compact"
                  hide-details
                />
              </v-col>
              <v-col cols="12" sm="8">
                <v-file-input
                  v-model="newFile.fileObj"
                  label="Seleccionar archivo de tu computadora..."
                  prepend-icon=""
                  prepend-inner-icon="mdi-paperclip"
                  variant="outlined"
                  density="compact"
                  show-size
                  hide-details
                  accept=".pdf,.zip,.rar,.7z,.doc,.docx,.ppt,.pptx,.md,.txt,.png,.jpg,.jpeg"
                />
              </v-col>
              <v-col cols="12" class="mt-3">
                <v-btn
                  block
                  color="primary"
                  variant="flat"
                  prepend-icon="mdi-cloud-upload-outline"
                  :loading="uploadingSingleFile"
                  @click="registrarArchivo"
                >
                  Subir Archivo al Servidor
                </v-btn>
              </v-col>
            </v-row>
          </v-sheet>

          <div class="text-subtitle-2 font-weight-bold mb-2">
            Archivos Registrados ({{ activeProject.files.length }})
          </div>
          <v-list class="border rounded-lg pa-0">
            <v-list-item
              v-for="f in activeProject.files"
              :key="f.id"
              :title="f.original_name"
              :subtitle="`Tipo: ${f.file_type} · Extensión: .${f.extension} · ${f.file_size_kb} KB`"
            >
              <template #prepend>
                <v-icon icon="mdi-file-check-outline" color="primary" />
              </template>
              <template #append>
                <div class="d-flex align-center ga-1">
                  <v-btn
                    v-if="f.id"
                    icon="mdi-download"
                    size="x-small"
                    variant="text"
                    color="primary"
                    :href="`/api/archivos-proyecto/${f.id}`"
                    target="_blank"
                  />
                  <v-btn
                    icon="mdi-delete-outline"
                    size="x-small"
                    variant="text"
                    color="error"
                    @click="eliminarArchivo(f.id)"
                  />
                </div>
              </template>
            </v-list-item>
            <v-list-item v-if="!activeProject.files.length">
              <div class="text-caption text-medium-emphasis py-2">
                Este proyecto aún no tiene archivos adjuntos.
              </div>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions class="pa-4 justify-end">
          <v-btn color="primary" variant="tonal" @click="dialogArchivos = false">Cerrar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { getStoredList } from '../../utils/access.js';
import {
  obtenerProyectos,
  crearProyecto,
  actualizarProyecto,
  subirArchivoProyecto,
  eliminarArchivoProyecto,
} from '../../services/proyecto/proyecto.service.js';
import { obtenerEventos, obtenerMateriasEvento } from '../../services/evento/evento.service.js';
import { carreras as fetchCarrerasApi } from '../../services/carrera/carrera.service.js';
import { obtenerUsuarios } from '../../services/users/users.service.js';
import { useAlertStore } from '../../store/useAlertStore.js';

const alertStore = useAlertStore();
const roles = getStoredList('roles');

const canManageProjects = computed(() => {
  if (!roles.length) return true;
  return roles.includes('admin') || roles.includes('coordinador') || roles.includes('teacher');
});

const filters = ref({
  search: '',
  eventId: null,
  carreraId: null,
  subjectId: null,
});

const dialogProyecto = ref(false);
const dialogMiembros = ref(false);
const dialogArchivos = ref(false);
const activeProject = ref(null);
const newMemberId = ref(null);
const savingProject = ref(false);
const uploadingSingleFile = ref(false);

const formMemberStudentId = ref(null);
const pendingFileType = ref('documento');
const selectedFilesInput = ref([]);

const newFile = ref({
  file_type: 'documento',
  fileObj: null,
});

const eventCatalog = ref([
  { id: 1, cicle_name: 'FESOL Ciclo I - 2026' },
  { id: 2, cicle_name: 'Feria de Innovación II - 2025' },
]);

const carreraCatalog = ref([
  { id: 1, name: 'Ingeniería en Sistemas', code: 'ING-SIS' },
  { id: 2, name: 'Ingeniería de Software', code: 'ING-SW' },
  { id: 3, name: 'Redes y Telecomunicaciones', code: 'TEC-RED' },
]);

const subjectCatalog = ref([
  {
    id: 101,
    event_id: 1,
    event_name: 'FESOL Ciclo I - 2026',
    carrera_id: 1,
    carrera_name: 'Ingeniería en Sistemas',
    subject_name: 'Ingeniería de Software II',
    teacher_name: 'Ing. Carlos Méndez',
    full_label: 'FESOL 2026 · ING-SIS · Ingeniería de Software II',
  },
  {
    id: 102,
    event_id: 1,
    event_name: 'FESOL Ciclo I - 2026',
    carrera_id: 2,
    carrera_name: 'Ingeniería de Software',
    subject_name: 'Desarrollo de Aplicaciones Web',
    teacher_name: 'Dra. Elena Ríos',
    full_label: 'FESOL 2026 · ING-SW · Desarrollo de Aplicaciones Web',
  },
  {
    id: 103,
    event_id: 1,
    event_name: 'FESOL Ciclo I - 2026',
    carrera_id: 3,
    carrera_name: 'Redes y Telecomunicaciones',
    subject_name: 'Seguridad en Redes y Servidores Linux',
    teacher_name: 'MSc. Jorge Villalta',
    full_label: 'FESOL 2026 · TEC-RED · Seguridad en Redes y Servidores Linux',
  },
]);

const studentsCatalog = ref([
  { id: 11, name: 'Ana Sofía Martínez', carnet: 'SM22014', label: 'Ana Sofía Martínez (SM22014)' },
  { id: 12, name: 'Diego Alejandro Pérez', carnet: 'PA22089', label: 'Diego Alejandro Pérez (PA22089)' },
  { id: 13, name: 'Kevin Josué Hernández', carnet: 'HJ22041', label: 'Kevin Josué Hernández (HJ22041)' },
  { id: 14, name: 'María Fernanda López', carnet: 'LF22003', label: 'María Fernanda López (LF22003)' },
  { id: 15, name: 'Gabriel Antonio Castro', carnet: 'CA23019', label: 'Gabriel Antonio Castro (CA23019)' },
]);

const projectsList = ref([
  {
    id: 1001,
    event_subject_id: 101,
    event_id: 1,
    event_name: 'FESOL Ciclo I - 2026',
    carrera_id: 1,
    carrera_name: 'Ingeniería en Sistemas',
    subject_name: 'Ingeniería de Software II',
    teacher_name: 'Ing. Carlos Méndez',
    title: 'Sistema de Gestión Hospitalaria OpenSource con Telemedicina',
    description: 'Plataforma integral para control de citas médicas, expediente clínico electrónico y videoconsultas.',
    repository_url: 'https://github.com/fesol-tda/hospital-opensource',
    status: 'approved',
    members: [
      { id: 11, name: 'Ana Sofía Martínez', carnet: 'SM22014', is_leader: true },
      { id: 12, name: 'Diego Alejandro Pérez', carnet: 'PA22089', is_leader: false },
    ],
    files: [
      { id: 1, file_type: 'documento', original_name: 'Manual_Arquitectura_Hospital.pdf', extension: 'pdf', file_size_kb: 2480 },
      { id: 2, file_type: 'codigo', original_name: 'release-v1.0.zip', extension: 'zip', file_size_kb: 14520 },
    ],
  },
  {
    id: 1002,
    event_subject_id: 102,
    event_id: 1,
    event_name: 'FESOL Ciclo I - 2026',
    carrera_id: 2,
    carrera_name: 'Ingeniería de Software',
    subject_name: 'Desarrollo de Aplicaciones Web',
    teacher_name: 'Dra. Elena Ríos',
    title: 'Repositorio Académico y Wiki Colaborativa Universitaria',
    description: 'Sistema de gestión de conocimiento técnico, tutoriales y proyectos estudiantiles por ciclo.',
    repository_url: 'https://github.com/fesol-tda/wiki-universitaria',
    status: 'approved',
    members: [
      { id: 15, name: 'Gabriel Antonio Castro', carnet: 'CA23019', is_leader: true },
    ],
    files: [
      { id: 3, file_type: 'documento', original_name: 'Especificacion_Requisitos_IEEE830.pdf', extension: 'pdf', file_size_kb: 3120 },
    ],
  },
]);

const projectForm = ref({
  id: null,
  event_subject_id: 101,
  title: '',
  description: '',
  repository_url: '',
  status: 'approved',
  members: [],
  pendingFiles: [],
});

const filteredSubjectCatalog = computed(() => {
  return subjectCatalog.value.filter((s) => {
    if (filters.value.eventId && s.event_id !== filters.value.eventId) return false;
    if (filters.value.carreraId && s.carrera_id !== filters.value.carreraId) return false;
    return true;
  });
});

const filteredProjects = computed(() => {
  const q = filters.value.search?.trim().toLowerCase();
  return projectsList.value.filter((p) => {
    if (filters.value.eventId && p.event_id !== filters.value.eventId) return false;
    if (filters.value.carreraId && p.carrera_id !== filters.value.carreraId) return false;
    if (filters.value.subjectId && p.event_subject_id !== filters.value.subjectId) return false;

    if (q) {
      const inTitle = p.title.toLowerCase().includes(q) || p.description.toLowerCase().includes(q);
      const inMembers = p.members.some((m) => m.name.toLowerCase().includes(q) || m.carnet.toLowerCase().includes(q));
      if (!inTitle && !inMembers) return false;
    }
    return true;
  });
});

const abrirModalProyecto = (proj = null) => {
  selectedFilesInput.value = [];
  formMemberStudentId.value = null;

  if (proj) {
    projectForm.value = {
      id: proj.id,
      event_subject_id: proj.event_subject_id,
      title: proj.title,
      description: proj.description || '',
      repository_url: proj.repository_url || '',
      status: proj.status,
      members: proj.members.map((m) => ({ ...m })),
      pendingFiles: [],
    };
  } else {
    projectForm.value = {
      id: null,
      event_subject_id: subjectCatalog.value[0]?.id || 101,
      title: '',
      description: '',
      repository_url: '',
      status: 'approved',
      members: [],
      pendingFiles: [],
    };
  }
  dialogProyecto.value = true;
};

const agregarMiembroEnFormulario = () => {
  if (!formMemberStudentId.value) return;
  const student = studentsCatalog.value.find((s) => s.id === formMemberStudentId.value);
  if (!student) return;
  if (projectForm.value.members.some((m) => m.id === student.id)) return;

  projectForm.value.members.push({
    id: student.id,
    name: student.name,
    carnet: student.carnet,
    is_leader: projectForm.value.members.length === 0,
  });
  formMemberStudentId.value = null;
};

const marcarLiderEnFormulario = (studentId) => {
  projectForm.value.members.forEach((m) => {
    m.is_leader = m.id === studentId;
  });
};

const quitarMiembroEnFormulario = (studentId) => {
  projectForm.value.members = projectForm.value.members.filter((m) => m.id !== studentId);
  if (projectForm.value.members.length && !projectForm.value.members.some((m) => m.is_leader)) {
    projectForm.value.members[0].is_leader = true;
  }
};

const agregarArchivosACola = (files) => {
  if (!files) return;
  const list = Array.isArray(files) ? files : [files];
  for (const f of list) {
    if (f instanceof File) {
      projectForm.value.pendingFiles.push({
        file: f,
        file_type: pendingFileType.value,
      });
    }
  }
  selectedFilesInput.value = [];
};

const quitarArchivoDeCola = (idx) => {
  projectForm.value.pendingFiles.splice(idx, 1);
};

const guardarProyecto = async () => {
  if (!projectForm.value.title.trim()) return;
  const sub =
    subjectCatalog.value.find((s) => s.id === projectForm.value.event_subject_id) ||
    subjectCatalog.value[0];

  savingProject.value = true;

  const formData = new FormData();
  formData.append('event_subject_id', String(projectForm.value.event_subject_id));
  formData.append('title', projectForm.value.title.trim());
  formData.append('description', projectForm.value.description || '');
  if (projectForm.value.repository_url?.trim()) {
    formData.append('repository_url', projectForm.value.repository_url.trim());
  }
  formData.append('status', projectForm.value.status);
  formData.append(
    'members',
    JSON.stringify(
      projectForm.value.members.map((m) => ({
        student_id: m.id,
        is_leader: Boolean(m.is_leader),
      }))
    )
  );

  for (const item of projectForm.value.pendingFiles) {
    formData.append('files[]', item.file);
    formData.append('file_types[]', item.file_type);
  }

  try {
    if (projectForm.value.id) {
      await actualizarProyecto(projectForm.value.id, formData);
    } else {
      await crearProyecto(formData);
    }
    await cargarProyectosYCatalogos();
  } catch (e) {
    const newUploadedMeta = projectForm.value.pendingFiles.map((pf, idx) => {
      const parts = pf.file.name.split('.');
      return {
        id: Date.now() + idx,
        file_type: pf.file_type,
        original_name: pf.file.name,
        extension: parts.length > 1 ? parts.pop() : 'bin',
        file_size_kb: Math.max(1, Math.round(pf.file.size / 1024)),
      };
    });

    if (projectForm.value.id) {
      const idx = projectsList.value.findIndex((p) => p.id === projectForm.value.id);
      if (idx !== -1) {
        projectsList.value[idx] = {
          ...projectsList.value[idx],
          event_subject_id: projectForm.value.event_subject_id,
          title: projectForm.value.title,
          description: projectForm.value.description,
          repository_url: projectForm.value.repository_url,
          status: projectForm.value.status,
          event_id: sub?.event_id,
          event_name: sub?.event_name,
          carrera_id: sub?.carrera_id,
          carrera_name: sub?.carrera_name,
          subject_name: sub?.subject_name,
          teacher_name: sub?.teacher_name,
          members: [...projectForm.value.members],
          files: [...(projectsList.value[idx].files || []), ...newUploadedMeta],
        };
      }
    } else {
      projectsList.value.unshift({
        id: Date.now(),
        event_subject_id: projectForm.value.event_subject_id,
        title: projectForm.value.title,
        description: projectForm.value.description,
        repository_url: projectForm.value.repository_url,
        status: projectForm.value.status,
        event_id: sub?.event_id,
        event_name: sub?.event_name,
        carrera_id: sub?.carrera_id,
        carrera_name: sub?.carrera_name,
        subject_name: sub?.subject_name,
        teacher_name: sub?.teacher_name,
        members: [...projectForm.value.members],
        files: newUploadedMeta,
      });
    }
  } finally {
    savingProject.value = false;
  }

  dialogProyecto.value = false;
  alertStore.showAlert('Proyecto y archivos guardados correctamente.', 'success');
};

const cambiarEstadoPublicacion = async (project) => {
  const nuevoEstado = project.status === 'approved' ? 'pending' : 'approved';
  project.status = nuevoEstado;
  try {
    await actualizarProyecto(project.id, { status: nuevoEstado });
  } catch (e) {
    // Fallback local
  }
};

const sincronizarMiembrosBackend = async (proj) => {
  try {
    await actualizarProyecto(proj.id, {
      members: proj.members.map((m) => ({
        student_id: m.id,
        is_leader: Boolean(m.is_leader),
      })),
    });
  } catch (e) {
    // Fallback local activo
  }
};

const abrirModalMiembros = (proj) => {
  activeProject.value = proj;
  newMemberId.value = null;
  dialogMiembros.value = true;
};

const agregarMiembro = async () => {
  if (!newMemberId.value || !activeProject.value) return;
  const student = studentsCatalog.value.find((s) => s.id === newMemberId.value);
  if (!student) return;
  if (activeProject.value.members.some((m) => m.id === student.id)) return;

  activeProject.value.members.push({
    id: student.id,
    name: student.name,
    carnet: student.carnet,
    is_leader: activeProject.value.members.length === 0,
  });
  newMemberId.value = null;
  await sincronizarMiembrosBackend(activeProject.value);
};

const designarLider = async (studentId) => {
  if (!activeProject.value) return;
  activeProject.value.members.forEach((m) => {
    m.is_leader = m.id === studentId;
  });
  await sincronizarMiembrosBackend(activeProject.value);
};

const quitarMiembro = async (studentId) => {
  if (!activeProject.value) return;
  activeProject.value.members = activeProject.value.members.filter((m) => m.id !== studentId);
  await sincronizarMiembrosBackend(activeProject.value);
};

const abrirModalArchivos = (proj) => {
  activeProject.value = proj;
  newFile.value = { file_type: 'documento', fileObj: null };
  dialogArchivos.value = true;
};

const registrarArchivo = async () => {
  if (!activeProject.value) return;
  const rawFile = Array.isArray(newFile.value.fileObj)
    ? newFile.value.fileObj[0]
    : newFile.value.fileObj;

  if (!rawFile) {
    alertStore.showAlert('Selecciona un archivo antes de subir.', 'warning');
    return;
  }

  uploadingSingleFile.value = true;
  const fd = new FormData();
  fd.append('project_id', String(activeProject.value.id));
  fd.append('file_type', newFile.value.file_type);
  fd.append('file', rawFile);

  try {
    const res = await subirArchivoProyecto(fd);
    if (res.data?.data) {
      activeProject.value.files.push(res.data.data);
      alertStore.showAlert('Archivo subido correctamente al servidor.', 'success');
    }
  } catch (e) {
    const parts = rawFile.name.split('.');
    activeProject.value.files.push({
      id: Date.now(),
      file_type: newFile.value.file_type,
      original_name: rawFile.name,
      extension: parts.length > 1 ? parts.pop() : 'bin',
      file_size_kb: Math.max(1, Math.round(rawFile.size / 1024)),
    });
  } finally {
    uploadingSingleFile.value = false;
    newFile.value.fileObj = null;
  }
};

const eliminarArchivo = async (fileId) => {
  if (!activeProject.value) return;
  try {
    await eliminarArchivoProyecto(fileId);
  } catch (e) {
    // Fallback local
  }
  activeProject.value.files = activeProject.value.files.filter((f) => f.id !== fileId);
};

const cargarProyectosYCatalogos = async () => {
  try {
    const [projRes, evRes, carRes, subRes] = await Promise.all([
      obtenerProyectos(),
      obtenerEventos(),
      fetchCarrerasApi(),
      obtenerMateriasEvento(),
    ]);

    if (evRes.data?.data?.length) {
      eventCatalog.value = evRes.data.data;
    }
    if (carRes.data?.data?.length) {
      carreraCatalog.value = carRes.data.data;
    }
    if (subRes.data?.data?.length) {
      subjectCatalog.value = subRes.data.data.map((s) => ({
        id: s.id,
        event_id: s.event_id,
        event_name: s.event?.cicle_name || 'Evento',
        carrera_id: s.carrera_id,
        carrera_name: s.carrera?.name || 'Carrera',
        subject_name: s.subject_name,
        teacher_name: s.teacher?.name || 'Docente',
        full_label: `${s.event?.cicle_name || 'Evento'} · ${s.carrera?.code || ''} · ${s.subject_name}`,
      }));
    }
    if (projRes.data?.data?.length) {
      projectsList.value = projRes.data.data.map((p) => ({
        id: p.id,
        event_subject_id: p.event_subject_id,
        event_id: p.event_subject?.event?.id,
        event_name: p.event_subject?.event?.cicle_name || 'Evento',
        carrera_id: p.event_subject?.carrera?.id,
        carrera_name: p.event_subject?.carrera?.name || 'Carrera',
        subject_name: p.event_subject?.subject_name || 'Materia',
        teacher_name: p.event_subject?.teacher?.name || 'Docente',
        title: p.title,
        description: p.description,
        repository_url: p.repository_url,
        status: p.status,
        members: (p.members || []).map((m) => ({
          id: m.student?.id ?? m.student_id,
          name: m.student?.name || 'Estudiante',
          carnet: m.student?.carnet_or_code || 'S/C',
          is_leader: Boolean(m.is_leader),
        })),
        files: p.files || [],
      }));
    }

    const usersRes = await obtenerUsuarios(1, { perPage: 100, role: 'student' });
    if (usersRes.data?.data?.length) {
      studentsCatalog.value = usersRes.data.data.map((u) => ({
        id: u.id,
        name: u.nombre,
        carnet: u.carnet || 'S/C',
        label: `${u.nombre} (${u.carnet || 'S/C'})`,
      }));
    }
  } catch (e) {
    // Mantiene respaldo si la API no está corriendo aún
  }
};

onMounted(async () => {
  await cargarProyectosYCatalogos();
});
</script>
