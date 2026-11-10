/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
export function clearAuthSession() {
  for (const key of ['token', 'user', 'roles', 'permissions', 'must_change_password']) {
    localStorage.removeItem(key)
  }
  window.dispatchEvent(new CustomEvent('wiki-user-updated', { detail: null }))
}

export function isPasswordChangeRequired() {
  if (localStorage.getItem('must_change_password') === 'true') {
    return true
  }
  try {
    const raw = localStorage.getItem('user')
    if (!raw) return false
    const u = JSON.parse(raw)
    return Boolean(u?.must_change_password)
  } catch {
    return false
  }
}

export function updateStoredUser(userPayload) {
  if (!userPayload) return
  const currentRaw = localStorage.getItem('user')
  let current = {}
  if (currentRaw) {
    try {
      current = JSON.parse(currentRaw) || {}
    } catch {
      current = {}
    }
  }
  const merged = {
    ...current,
    ...userPayload,
    name: userPayload.name ?? userPayload.nombre ?? current.name,
    avatar_url: userPayload.avatar_url !== undefined ? userPayload.avatar_url : current.avatar_url,
    must_change_password:
      userPayload.must_change_password !== undefined
        ? Boolean(userPayload.must_change_password)
        : Boolean(current.must_change_password),
  }
  localStorage.setItem('user', JSON.stringify(merged))
  localStorage.setItem('must_change_password', merged.must_change_password ? 'true' : 'false')
  if (Array.isArray(userPayload.roles)) {
    localStorage.setItem('roles', JSON.stringify(userPayload.roles))
  }
  if (Array.isArray(userPayload.permisos)) {
    localStorage.setItem('permissions', JSON.stringify(userPayload.permisos))
  }
  window.dispatchEvent(new CustomEvent('wiki-user-updated', { detail: merged }))
}
