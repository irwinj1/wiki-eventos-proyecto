<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
    <v-pagination :length="totalPage" v-model="paginas" @update:modelValue="onPageChange"></v-pagination>
</template>
<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    page:{
        type:Number,
        default:1
    },
    totalPage:{
        type:Number,
        default:1
    }
})

const emit = defineEmits(['update:page']);

const paginas = ref(props.page);

watch(() => props.page, (newPage) => {
    paginas.value = newPage;
});

const onPageChange = (newPage) => {
    emit('update:page', newPage);
};
</script>
