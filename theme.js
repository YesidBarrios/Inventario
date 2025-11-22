// Sistema de tema para toda la aplicación
// Este archivo debe incluirse en TODAS las páginas del sistema

// Aplicar tema inmediatamente al cargar (evita parpadeo)
;(() => {
  const savedTheme = localStorage.getItem("theme") || "light"
  if (savedTheme === "dark") {
    document.documentElement.classList.add("dark")
  }
})()

// Función para alternar el tema (se ejecuta después de que el DOM esté listo)
function toggleTheme() {
  const html = document.documentElement
  const isDark = html.classList.contains("dark")

  if (isDark) {
    html.classList.remove("dark")
    localStorage.setItem("theme", "light")
  } else {
    html.classList.add("dark")
    localStorage.setItem("theme", "dark")
  }

  // Actualizar iconos
  updateThemeIcons()
}

// Actualizar los iconos del botón de tema
function updateThemeIcons() {
  const html = document.documentElement
  const isDark = html.classList.contains("dark")
  const lightIcon = document.getElementById("theme-icon-light")
  const darkIcon = document.getElementById("theme-icon-dark")

  if (lightIcon && darkIcon) {
    if (isDark) {
      lightIcon.classList.add("hidden")
      darkIcon.classList.remove("hidden")
    } else {
      lightIcon.classList.remove("hidden")
      darkIcon.classList.add("hidden")
    }
  }
}

// Inicializar iconos cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  updateThemeIcons()

  // Conectar el botón de tema
  const themeToggleBtn = document.getElementById("theme-toggle-btn")
  if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", toggleTheme)
  }
})
