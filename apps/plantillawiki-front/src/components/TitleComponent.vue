<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
    <v-row>
        <v-col  class="d-flex justify-center font-weight-black">
            <p class="text-primary">
                {{ title }}
            </p>
        </v-col>
    </v-row>
</template>
<script setup>
import {defineProps} from 'vue'
const props = defineProps({
    title:{
        type:String,
        default:'DashBoard'
    }
})
</script>
<style scoped>

</style>