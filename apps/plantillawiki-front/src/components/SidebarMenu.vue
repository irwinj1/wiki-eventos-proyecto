<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { menuItems } from '../utils/menuDb';
import { getStoredList } from '../utils/access';

const emit = defineEmits(['navigate']);
const isAuthenticated = ref(Boolean(localStorage.getItem('token')));
const roles = ref(getStoredList('roles'));
const permissions = ref(getStoredList('permissions'));

const syncSessionMenu = () => {
  isAuthenticated.value = Boolean(localStorage.getItem('token'));
  roles.value = getStoredList('roles');
  permissions.value = getStoredList('permissions');
};

onMounted(() => {
  window.addEventListener('wiki-user-updated', syncSessionMenu);
});

onBeforeUnmount(() => {
  window.removeEventListener('wiki-user-updated', syncSessionMenu);
});

const hasAccess = (item) => {
  if (item.guestOnly && isAuthenticated.value) {
    return false;
  }

  if (item.requiresAuth && !isAuthenticated.value) {
    return false;
  }

  const hasRoleRequirement = item.roles?.length > 0;
  const hasPermissionRequirement = item.permisos?.length > 0;

  if (hasRoleRequirement && !item.roles.some((role) => roles.value.includes(role))) {
    return false;
  }

  if (
    hasPermissionRequirement &&
    !item.permisos.some((permission) => permissions.value.includes(permission))
  ) {
    return false;
  }

  return true;
};

const filterMenu = (items) =>
  items
    .filter(hasAccess)
    .map((item) => {
      if (!item.children) {
        return item;
      }

      return {
        ...item,
        children: filterMenu(item.children),
      };
    })
    .filter((item) => !item.children || item.children.length > 0);

const menu = computed(() => filterMenu(menuItems));
</script>

<template>
  <v-list class="sidebar-menu" color="primary" density="comfortable" nav>
    <template v-for="item in menu" :key="item.title">
      <v-list-item
        v-if="!item.children"
        :to="item.to"
        :prepend-icon="item.icon"
        :title="item.title"
        active-class="sidebar-menu__item--active"
        exact
        @click="emit('navigate')"
      />

      <v-list-group v-else :value="item.title">
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            :prepend-icon="item.icon"
            :title="item.title"
          />
        </template>

        <v-list-item
          v-for="child in item.children"
          :key="child.title"
          :to="child.to"
          :prepend-icon="child.icon"
          :title="child.title"
          active-class="sidebar-menu__item--active"
          exact
          @click="emit('navigate')"
        />
      </v-list-group>
    </template>
  </v-list>
</template>

<style scoped>
.sidebar-menu {
  padding: 16px 12px;
}

.sidebar-menu :deep(.v-list-item) {
  min-height: 46px;
  margin-bottom: 4px;
  border-radius: 10px;
  
}

.sidebar-menu :deep(.v-list-item--active) {
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

.sidebar-menu :deep(.v-list-item--active .v-icon) {
  color: rgb(var(--v-theme-on-primary));
}
</style>
