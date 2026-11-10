<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <v-snackbar
    v-model="alertStore.show"
    :color="alertStore.type"
    :timeout="alertStore.timeout"
    location="top right"
  >
    {{ alertStore.message }}

    <template v-slot:actions>
      <v-btn
        color="white"
        variant="text"
        @click="alertStore.hideAlert"
      >
        Cerrar
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup>
import { useAlertStore } from '../store/useAlertStore';

const alertStore = useAlertStore();
</script>
