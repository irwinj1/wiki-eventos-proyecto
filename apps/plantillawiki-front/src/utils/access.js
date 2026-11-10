/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
export const getStoredList = (key) => {
  const storedValue = localStorage.getItem(key);

  if (!storedValue) {
    return [];
  }

  try {
    const value = JSON.parse(storedValue);
    return Array.isArray(value) ? value : [];
  } catch (error) {
    console.error(`No se pudo leer la lista "${key}" del almacenamiento local.`, error);
    return [];
  }
};
