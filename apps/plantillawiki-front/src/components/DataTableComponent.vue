<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div class="data-table-wrapper">
    <div class="overflow-x-auto">
      <v-data-table
        :headers="headers"
        :items="items"
        :no-data-text="noData"
        hide-default-footer
        density="comfortable"
        class="data-table-responsive"
      >
        <template v-for="(_, slotName) in $slots" :key="slotName" #[slotName]="slotProps">
          <slot :name="slotName" v-bind="slotProps"></slot>
        </template>
      </v-data-table>
    </div>
    <div class="mt-3 d-flex justify-center">
      <Pagination :total-page="totalPage" :page="page" @update:page="onPageChange"></Pagination>
    </div>
  </div>
</template>
<script setup>
import Pagination from "./Pagination.vue";
import { defineProps, defineEmits } from "vue";

const emit = defineEmits(['update:page']);

const onPageChange = (newPage) => {
  emit('update:page', newPage);
};

const props = defineProps({
  headers: {
    type: Array,
    default: [],
  },
  items: {
    type: Array,
    default: [],
  },
  noData: {
    type: String,
    default: "No hay datos",
  },
  totalPage:{
    type:Number,
    default:1
  },
  page:{
    type:Number,
    default:1
  },
  
});
</script>

<style scoped>
.data-table-responsive :deep(table) {
  min-width: 640px;
}
</style>
