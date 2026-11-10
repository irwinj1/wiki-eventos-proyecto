<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <v-navigation-drawer
    :model-value="drawer"
    :permanent="permanent"
    :temporary="!permanent"
    :rail="rail"
    :width="264"
    :rail-width="76"
    color="surface"
    class="sidebar"
    @update:model-value="emit('update:drawer', $event)"
  >
    <div class="sidebar__brand" :class="{ 'sidebar__brand--rail': rail }">
      <template v-if="rail">
        <div class="sidebar__logo" :class="{ 'sidebar__logo--dark': isDark }">
          <img :src="logoUlsUrl" alt="Logo ULS" class="sidebar__logo-rail-img" />
        </div>
      </template>
      <template v-else>
        <div class="sidebar__brand-copy" :class="{ 'sidebar__brand-copy--dark': isDark }">
          <img :src="logoUlsUrl" alt="Logo ULS" class="sidebar__logo-img" />
        </div>
      </template>
    </div>

    <v-divider />

    <SidebarMenu @navigate="emit('navigate')" />
  </v-navigation-drawer>
</template>

<script setup>
import { computed } from 'vue';
import { useTheme } from 'vuetify';
import SidebarMenu from './SidebarMenu.vue';
import logoUlsUrl from '../assets/logouls.png';

const theme = useTheme();
const isDark = computed(() => theme.global.current.value.dark);
defineProps({
  drawer: {
    type: Boolean,
    default: true,
  },
  permanent: {
    type: Boolean,
    default: false,
  },
  rail: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits(['update:drawer', 'navigate']);
</script>

<style scoped>
.sidebar {
  border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.sidebar__brand {
  display: flex;
  min-height: 84px;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 14px 18px;
}

.sidebar__brand--rail {
  padding: 14px 10px;
}

.sidebar__logo {
  display: grid;
  width: 48px;
  height: 48px;
  flex: 0 0 48px;
  place-items: center;
  border-radius: 12px;
  padding: 4px;
  overflow: hidden;
  transition: background 0.2s ease, box-shadow 0.2s ease;
}

.sidebar__logo--dark {
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
}

.sidebar__logo-rail-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.sidebar__brand-copy {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 14px;
  transition: background 0.2s ease, box-shadow 0.2s ease;
}

.sidebar__brand-copy--dark {
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
}

.sidebar__logo-img {
  width: 100%;
  max-width: 190px;
  max-height: 58px;
  height: auto;
  object-fit: contain;
  display: block;
}
</style>