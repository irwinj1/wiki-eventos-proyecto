<!--
  @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
  @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
  @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
  @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
-->
<template>
  <div class="rich-editor border rounded-xl overflow-hidden bg-surface">
    <!-- Barra de herramientas WYSIWYG -->
    <div class="rich-editor__toolbar pa-2 d-flex flex-wrap align-center ga-1 border-b">
      <v-btn-group density="compact" variant="tonal" color="primary" class="mr-1">
        <v-btn
          size="small"
          icon="mdi-format-bold"
          title="Negrita (Ctrl+B)"
          @click.prevent="execCmd('bold')"
        />
        <v-btn
          size="small"
          icon="mdi-format-italic"
          title="Cursiva (Ctrl+I)"
          @click.prevent="execCmd('italic')"
        />
        <v-btn
          size="small"
          icon="mdi-format-underline"
          title="Subrayado (Ctrl+U)"
          @click.prevent="execCmd('underline')"
        />
        <v-btn
          size="small"
          icon="mdi-format-strikethrough"
          title="Tachado"
          @click.prevent="execCmd('strikeThrough')"
        />
      </v-btn-group>

      <v-btn-group density="compact" variant="tonal" color="secondary" class="mr-1">
        <v-btn
          size="small"
          class="text-caption font-weight-bold px-2"
          title="Título Principal (H2)"
          @click.prevent="formatHeading('H2')"
        >
          H2
        </v-btn>
        <v-btn
          size="small"
          class="text-caption font-weight-bold px-2"
          title="Subtítulo (H3)"
          @click.prevent="formatHeading('H3')"
        >
          H3
        </v-btn>
        <v-btn
          size="small"
          class="text-caption px-2"
          title="Párrafo normal"
          @click.prevent="formatHeading('P')"
        >
          P
        </v-btn>
      </v-btn-group>

      <v-btn-group density="compact" variant="tonal" color="primary" class="mr-1">
        <v-btn
          size="small"
          icon="mdi-format-list-bulleted"
          title="Lista con viñetas"
          @click.prevent="execCmd('insertUnorderedList')"
        />
        <v-btn
          size="small"
          icon="mdi-format-list-numbered"
          title="Lista numerada"
          @click.prevent="execCmd('insertOrderedList')"
        />
        <v-btn
          size="small"
          icon="mdi-format-quote-close"
          title="Cita destacada"
          @click.prevent="formatHeading('BLOCKQUOTE')"
        />
      </v-btn-group>

      <v-btn-group density="compact" variant="tonal" color="info" class="mr-1">
        <v-btn
          size="small"
          icon="mdi-code-tags"
          title="Insertar bloque de código / comandos"
          @click.prevent="insertCodeBlock"
        />
        <v-btn
          size="small"
          icon="mdi-link-plus"
          title="Insertar enlace"
          @click.prevent="insertLink"
        />
        <v-btn
          size="small"
          icon="mdi-alert-circle-check-outline"
          title="Insertar nota técnica destacada"
          @click.prevent="insertCalloutBox"
        />
      </v-btn-group>

      <v-spacer />

      <v-btn
        size="x-small"
        variant="text"
        color="secondary"
        prepend-icon="mdi-format-clear"
        @click.prevent="execCmd('removeFormat')"
      >
        Limpiar formato
      </v-btn>
    </div>

    <!-- Área editable de texto enriquecido -->
    <div
      ref="editorRef"
      class="rich-editor__content pa-4"
      contenteditable="true"
      :data-placeholder="placeholder"
      @input="onInput"
      @blur="onInput"
    />
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Escribe aquí el contenido enriquecido del tutorial o artículo (títulos, listas, bloques de código, enlaces)...',
  },
});

const emit = defineEmits(['update:modelValue']);
const editorRef = ref(null);

const normalizeToHtml = (val) => {
  if (!val) return '';
  if (/<[a-z][\s\S]*>/i.test(val)) {
    return val;
  }
  return String(val)
    .split(/\n\s*\n/)
    .map((block) => `<p>${block.replace(/\n/g, '<br>')}</p>`)
    .join('');
};

const syncFromModel = (val) => {
  if (!editorRef.value) return;
  const html = normalizeToHtml(val);
  if (editorRef.value.innerHTML !== html) {
    editorRef.value.innerHTML = html;
  }
};

const onInput = () => {
  if (!editorRef.value) return;
  emit('update:modelValue', editorRef.value.innerHTML);
};

const execCmd = (command, value = null) => {
  if (!editorRef.value) return;
  editorRef.value.focus();
  document.execCommand(command, false, value);
  onInput();
};

const formatHeading = (tag) => {
  execCmd('formatBlock', tag);
};

const insertCodeBlock = () => {
  if (!editorRef.value) return;
  editorRef.value.focus();
  const selection = window.getSelection()?.toString() || '// Escribe tu código o comando aquí\nphp artisan migrate --seed';
  const html = `<pre class="wiki-code-block"><code>${selection
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')}</code></pre><p><br></p>`;
  document.execCommand('insertHTML', false, html);
  onInput();
};

const insertCalloutBox = () => {
  if (!editorRef.value) return;
  editorRef.value.focus();
  const html = `<blockquote class="wiki-callout"><strong>Nota técnica:</strong> Escribe aquí una recomendación o paso importante del tutorial.</blockquote><p><br></p>`;
  document.execCommand('insertHTML', false, html);
  onInput();
};

const insertLink = () => {
  const url = window.prompt('Ingresa la URL del enlace (ej. https://docs.laravel.com):', 'https://');
  if (url && url !== 'https://') {
    execCmd('createLink', url);
  }
};

watch(
  () => props.modelValue,
  (newVal) => {
    if (!editorRef.value) return;
    if (document.activeElement !== editorRef.value) {
      syncFromModel(newVal);
    }
  }
);

onMounted(() => {
  syncFromModel(props.modelValue);
});
</script>

<style scoped>
.rich-editor__toolbar {
  background: rgba(var(--v-theme-primary), 0.04);
}

.rich-editor__content {
  min-height: 260px;
  max-height: 460px;
  overflow-y: auto;
  outline: none;
  line-height: 1.75;
  font-size: 0.96rem;
}

.rich-editor__content:empty::before {
  content: attr(data-placeholder);
  color: rgba(var(--v-theme-on-surface), 0.45);
  pointer-events: none;
}

.rich-editor__content :deep(h2) {
  font-size: 1.35rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  margin: 14px 0 6px;
}

.rich-editor__content :deep(h3) {
  font-size: 1.12rem;
  font-weight: 700;
  color: rgb(var(--v-theme-secondary));
  margin: 12px 0 4px;
}

.rich-editor__content :deep(p) {
  margin-bottom: 10px;
}

.rich-editor__content :deep(ul),
.rich-editor__content :deep(ol) {
  padding-left: 24px;
  margin-bottom: 12px;
}

.rich-editor__content :deep(pre),
.rich-editor__content :deep(.wiki-code-block) {
  background: #0d1239;
  color: #e8eeff;
  padding: 12px 16px;
  border-radius: 10px;
  font-family: monospace;
  font-size: 0.86rem;
  overflow-x: auto;
  margin: 12px 0;
}

.rich-editor__content :deep(blockquote),
.rich-editor__content :deep(.wiki-callout) {
  border-left: 4px solid rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.06);
  padding: 10px 14px;
  border-radius: 0 8px 8px 0;
  margin: 12px 0;
}

.rich-editor__content :deep(a) {
  color: rgb(var(--v-theme-primary));
  text-decoration: underline;
}
</style>
