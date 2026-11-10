<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <v-btn
    :icon="isDarkTheme ? 'mdi-weather-sunny' : 'mdi-weather-night'"
    variant="text"
    color="secondary"
    :aria-label="isDarkTheme ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'"
    :title="isDarkTheme ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'"
    @click="toggleTheme"
  />
</template>

<script setup>
import { computed } from 'vue'
import { useTheme } from 'vuetify'

const theme = useTheme()
const isDarkTheme = computed(() => theme.global.name.value === 'dark')

function toggleTheme() {
  const nextTheme = isDarkTheme.value ? 'light' : 'dark'
  theme.global.name.value = nextTheme
  localStorage.setItem('theme', nextTheme)
}
</script>
