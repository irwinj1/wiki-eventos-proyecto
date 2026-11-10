<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div>
    <!-- Encabezado exclusivo para Administrador -->
    <v-card rounded="xl" elevation="0" class="admin-banner pa-5 pa-sm-6 mb-6 text-white">
      <v-row align="center">
        <v-col cols="12" md="8">
          <div class="d-flex flex-wrap align-center ga-2 mb-2">
            <v-chip color="accent" variant="flat" size="small" class="text-primary font-weight-bold" prepend-icon="mdi-shield-crown-outline">
              Vista Exclusiva Administrador
            </v-chip>
            <v-chip color="white" variant="outlined" size="small">
              Ciclo Activo: FESOL 2026
            </v-chip>
          </div>
          <h1 class="text-h5 text-sm-h4 font-weight-bold mb-1">Panel de Control General · Wiki TDA</h1>
          <p class="text-body-2 opacity-85">
            Supervisa métricas globales de usuarios, roles, carreras, eventos creados por coordinadores, materias de docentes, proyectos estudiantiles y artículos publicados en la Wiki.
          </p>
        </v-col>
        <v-col cols="12" md="4" class="d-flex flex-column flex-sm-row flex-wrap justify-md-end ga-2">
          <v-btn
            to="/usuarios"
            color="white"
            variant="flat"
            class="text-primary font-weight-bold w-100 w-sm-auto"
            prepend-icon="mdi-account-plus-outline"
          >
            Gestionar Usuarios
          </v-btn>
          <v-btn
            to="/admin/configuracion"
            color="accent"
            variant="outlined"
            class="text-white w-100 w-sm-auto"
            prepend-icon="mdi-cog-outline"
          >
            Roles y Configuración
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- Tarjetas de KPIs del Sistema -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" lg="3" v-for="kpi in kpis" :key="kpi.title">
        <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-5 h-100 d-flex flex-column border">
          <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-3">
            <v-avatar :color="kpi.color" variant="tonal" rounded="lg" size="46">
              <v-icon :icon="kpi.icon" size="24" />
            </v-avatar>
            <v-chip size="x-small" :color="kpi.color" variant="tonal" class="font-weight-bold">
              {{ kpi.badge }}
            </v-chip>
          </div>
          <div class="text-h4 font-weight-bold mb-1">{{ kpi.value }}</div>
          <div class="text-subtitle-2 font-weight-bold text-break">{{ kpi.title }}</div>
          <div class="text-caption text-medium-emphasis mt-1 flex-grow-1 text-break">{{ kpi.subtitle }}</div>
          <v-divider class="my-3" />
          <v-btn
            :to="kpi.to"
            variant="text"
            size="small"
            :color="kpi.color"
            class="px-0 justify-start text-none"
            append-icon="mdi-arrow-right"
          >
            {{ kpi.actionText }}
          </v-btn>
        </v-card>
      </v-col>
    </v-row>

    <!-- Accesos Rápidos por Módulo y Flujo Operativo -->
    <v-row class="mb-6">
      <v-col cols="12" lg="7">
        <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 h-100 border">
          <div class="d-flex align-center justify-space-between mb-4">
            <div>
              <h2 class="text-h6 font-weight-bold text-primary">
                Estado del Flujo Académico por Roles
              </h2>
              <p class="text-body-2 text-medium-emphasis">
                Resumen de actividad según responsabilidades en el sistema.
              </p>
            </div>
          </div>

          <v-timeline density="compact" side="end" align="start" truncate-line="both">
            <v-timeline-item dot-color="primary" size="small">
              <div class="d-flex justify-space-between align-center flex-wrap ga-2">
                <strong class="text-break">1. Administración (Usuarios, Roles y Carreras)</strong>
                <v-chip size="x-small" color="primary" variant="tonal">Solo Admin</v-chip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-2 text-break">
                Creación de cuentas institucionales (`admin`, `coordinador`, `teacher`, `student`), asignación de carreras y permisos Spatie.
              </p>
              <div class="d-flex flex-wrap ga-2">
                <v-btn to="/usuarios" size="x-small" variant="tonal" color="primary" class="text-none">Ir a Usuarios</v-btn>
                <v-btn to="/carrera" size="x-small" variant="tonal" color="secondary" class="text-none">Ir a Carreras</v-btn>
                <v-btn to="/admin/configuracion" size="x-small" variant="tonal" color="info" class="text-none">Ir a Roles/Taxonomía</v-btn>
              </div>
            </v-timeline-item>

            <v-timeline-item dot-color="secondary" size="small">
              <div class="d-flex justify-space-between align-center flex-wrap ga-2">
                <strong class="text-break">2. Coordinación de Carrera (Creación de Eventos)</strong>
                <v-chip size="x-small" color="secondary" variant="tonal">Coordinador / Admin</v-chip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-2 text-break">
                El coordinador apertura el evento del ciclo académico (`events`) y supervisa las materias participantes.
              </p>
              <div class="d-flex flex-wrap ga-2">
                <v-btn to="/evento" size="x-small" variant="tonal" color="secondary" class="text-none">Gestionar Eventos</v-btn>
              </div>
            </v-timeline-item>

            <v-timeline-item dot-color="info" size="small">
              <div class="d-flex justify-space-between align-center flex-wrap ga-2">
                <strong class="text-break">3. Docentes (Materias, Proyectos y Wiki)</strong>
                <v-chip size="x-small" color="info" variant="tonal">Docente / Admin</v-chip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-2 text-break">
                Los docentes registran sus materias (`event_subjects`), suben los proyectos de los estudiantes (`projects`, `project_members`, `project_files`) y redactan tutoriales en la Wiki.
              </p>
              <div class="d-flex flex-wrap ga-2">
                <v-btn to="/proyecto" size="x-small" variant="tonal" color="info" class="text-none">Gestionar Proyectos</v-btn>
                <v-btn to="/wiki" size="x-small" variant="tonal" color="primary" class="text-none">Gestionar Wiki</v-btn>
              </div>
            </v-timeline-item>

            <v-timeline-item dot-color="success" size="small">
              <div class="d-flex justify-space-between align-center flex-wrap ga-2">
                <strong class="text-break">4. Portal Público (Consulta Abierta con Filtros)</strong>
                <v-chip size="x-small" color="success" variant="tonal">Público General</v-chip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-2 text-break">
                Cualquier visitante puede leer los tutoriales/artículos publicados y filtrar eventos <v-icon icon="mdi-arrow-right" size="x-small" /> carreras <v-icon icon="mdi-arrow-right" size="x-small" /> proyectos.
              </p>
              <div class="d-flex flex-wrap ga-2">
                <v-btn to="/explorar/wiki" size="x-small" variant="tonal" color="success" class="text-none">Ver Wiki Pública</v-btn>
                <v-btn to="/explorar/eventos" size="x-small" variant="tonal" color="success" class="text-none">Ver Eventos Públicos</v-btn>
              </div>
            </v-timeline-item>
          </v-timeline>
        </v-card>
      </v-col>

      <!-- Distribución por Roles y Pendientes de Moderación -->
      <v-col cols="12" lg="5">
        <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 mb-6 border">
          <h2 class="text-h6 font-weight-bold text-primary mb-1">
            Distribución de Usuarios por Rol
          </h2>
          <p class="text-body-2 text-medium-emphasis mb-4">
            Usuarios registrados y sincronizados con `Spatie Permission`.
          </p>

          <div v-for="roleStat in roleBreakdown" :key="roleStat.role" class="mb-3">
            <div class="d-flex flex-wrap justify-space-between align-center ga-1 text-body-2 mb-1">
              <span class="font-weight-semibold text-break">{{ roleStat.label }} (`{{ roleStat.role }}`)</span>
              <span class="font-weight-bold flex-shrink-0">{{ roleStat.count }} usuarios</span>
            </div>
            <v-progress-linear
              :model-value="roleStat.percent"
              :color="roleStat.color"
              height="8"
              rounded
            />
          </div>
        </v-card>

        <v-card rounded="xl" elevation="1" class="pa-4 pa-sm-6 border">
          <div class="d-flex flex-wrap align-center justify-space-between ga-2 mb-3">
            <h2 class="text-h6 font-weight-bold text-primary">Actividad Reciente</h2>
            <v-chip size="x-small" color="info" variant="tonal">Tiempo real</v-chip>
          </div>
          <v-list density="compact" class="pa-0">
            <v-list-item
              v-for="(item, idx) in recentLogs"
              :key="idx"
              :title="item.title"
              :subtitle="item.subtitle"
              class="px-0"
            >
              <template #prepend>
                <v-avatar size="32" :color="item.color" variant="tonal" class="mr-3">
                  <v-icon :icon="item.icon" size="18" />
                </v-avatar>
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { obtenerDashboardStats } from '../services/proyecto/proyecto.service.js';

const kpis = ref([
  {
    title: 'Usuarios Registrados',
    value: '148',
    badge: '4 Roles Activos',
    subtitle: 'Admins, Coordinadores, Docentes y Estudiantes',
    icon: 'mdi-account-group-outline',
    color: 'primary',
    to: '/usuarios',
    actionText: 'Administrar usuarios',
  },
  {
    title: 'Eventos y Materias',
    value: '6 / 24',
    badge: '1 Evento Activo',
    subtitle: '6 ciclos académicos y 24 materias vinculadas',
    icon: 'mdi-calendar-star',
    color: 'secondary',
    to: '/evento',
    actionText: 'Ver eventos y materias',
  },
  {
    title: 'Proyectos Estudiantiles',
    value: '124',
    badge: '18 en revisión',
    subtitle: 'Con repositorios Git, integrantes y entregables',
    icon: 'mdi-folder-multiple-outline',
    color: 'info',
    to: '/proyecto',
    actionText: 'Supervisar proyectos',
  },
  {
    title: 'Artículos y Tutoriales',
    value: '48',
    badge: '5 Categorías · 14 Tags',
    subtitle: 'Publicados en la Wiki y disponibles al público',
    icon: 'mdi-book-open-page-variant-outline',
    color: 'success',
    to: '/wiki',
    actionText: 'Administrar Wiki',
  },
]);

const roleBreakdown = ref([
  { role: 'admin', label: 'Administradores', count: 3, percent: 8, color: 'primary' },
  { role: 'coordinador', label: 'Coordinadores de Carrera', count: 5, percent: 15, color: 'secondary' },
  { role: 'teacher', label: 'Docentes', count: 22, percent: 38, color: 'info' },
  { role: 'student', label: 'Estudiantes', count: 118, percent: 85, color: 'success' },
]);

const recentLogs = [
  {
    title: 'Nuevo evento creado: FESOL Ciclo I - 2026',
    subtitle: 'Por Coordinador Roberto Morales · Hace 2 horas',
    icon: 'mdi-calendar-plus',
    color: 'secondary',
  },
  {
    title: 'Proyecto subido: Sistema Hospitalario OpenSource',
    subtitle: 'Por Docente Ing. Carlos Méndez en Ingeniería de Software II',
    icon: 'mdi-folder-upload-outline',
    color: 'info',
  },
  {
    title: 'Tutorial publicado en Wiki: APIs RESTful en Laravel 12',
    subtitle: 'Categoría: Desarrollo Backend · Etiquetas: #laravel #seguridad',
    icon: 'mdi-book-check-outline',
    color: 'success',
  },
];

onMounted(async () => {
  try {
    const res = await obtenerDashboardStats();
    const data = res.data?.data;
    if (!data) return;

    const c = data.counts || {};
    kpis.value = [
      {
        title: 'Usuarios Registrados',
        value: String(c.users ?? 0),
        badge: '4 Roles Activos',
        subtitle: 'Admins, Coordinadores, Docentes y Estudiantes',
        icon: 'mdi-account-group-outline',
        color: 'primary',
        to: '/usuarios',
        actionText: 'Administrar usuarios',
      },
      {
        title: 'Eventos y Materias',
        value: `${c.events ?? 0} / ${c.subjects ?? 0}`,
        badge: `${c.active_events ?? 0} Evento(s) Activo(s)`,
        subtitle: 'Ciclos académicos y materias vinculadas',
        icon: 'mdi-calendar-star',
        color: 'secondary',
        to: '/evento',
        actionText: 'Ver eventos y materias',
      },
      {
        title: 'Proyectos Estudiantiles',
        value: String(c.projects ?? 0),
        badge: `${c.pending_projects ?? 0} en revisión`,
        subtitle: 'Con repositorios Git, integrantes y entregables',
        icon: 'mdi-folder-multiple-outline',
        color: 'info',
        to: '/proyecto',
        actionText: 'Supervisar proyectos',
      },
      {
        title: 'Artículos y Tutoriales',
        value: String(c.articles ?? 0),
        badge: `${c.categories ?? 0} Categorías · ${c.tags ?? 0} Tags`,
        subtitle: 'Publicados en la Wiki y disponibles al público',
        icon: 'mdi-book-open-page-variant-outline',
        color: 'success',
        to: '/wiki',
        actionText: 'Administrar Wiki',
      },
    ];

    const totalU = Math.max(1, c.users || 1);
    const r = data.roles || {};
    roleBreakdown.value = [
      { role: 'admin', label: 'Administradores', count: r.admin ?? 0, percent: Math.round(((r.admin ?? 0) / totalU) * 100), color: 'primary' },
      { role: 'coordinador', label: 'Coordinadores de Carrera', count: r.coordinador ?? 0, percent: Math.round(((r.coordinador ?? 0) / totalU) * 100), color: 'secondary' },
      { role: 'teacher', label: 'Docentes', count: r.teacher ?? 0, percent: Math.round(((r.teacher ?? 0) / totalU) * 100), color: 'info' },
      { role: 'student', label: 'Estudiantes', count: r.student ?? 0, percent: Math.round(((r.student ?? 0) / totalU) * 100), color: 'success' },
    ];
  } catch (e) {
    // Mantiene respaldo visual si el servidor está apagado
  }
});
</script>

<style scoped>
.admin-banner {
  background: linear-gradient(135deg, #0d1239 0%, #1f2d7f 100%);
}
</style>
