// LUSANO Admin Panel JavaScript

document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuToggle = document.getElementById("mobile-menu-toggle")
  const sidebar = document.getElementById("sidebar")

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open")
    })
  }

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", (e) => {
    if (window.innerWidth <= 768) {
      if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
        sidebar.classList.remove("open")
      }
    }
  })

  // Initialize image upload functionality
  initializeImageUpload()

  // Initialize form validation
  initializeFormValidation()

  // Initialize tooltips
  initializeTooltips()

  // Add logout button to header
  addLogoutButton()
})

// Image Upload Functionality
function initializeImageUpload() {
  const uploadAreas = document.querySelectorAll(".image-upload-area")

  uploadAreas.forEach((area) => {
    const input = area.querySelector('input[type="file"]')
    const preview = area.nextElementSibling

    // Drag and drop events
    area.addEventListener("dragover", (e) => {
      e.preventDefault()
      area.classList.add("dragover")
    })

    area.addEventListener("dragleave", (e) => {
      e.preventDefault()
      area.classList.remove("dragover")
    })

    area.addEventListener("drop", (e) => {
      e.preventDefault()
      area.classList.remove("dragover")

      const files = e.dataTransfer.files
      handleFiles(files, preview, input)
    })

    // Click to upload
    area.addEventListener("click", () => {
      input.click()
    })

    // File input change
    input.addEventListener("change", function () {
      handleFiles(this.files, preview, input)
    })
  })
}

function handleFiles(files, preview, input) {
  console.log("[v0] Handling files:", files.length)

  const dt = new DataTransfer()

  // Add new files only (don't add existing files to prevent duplicates)
  Array.from(files).forEach((file) => {
    if (file.type.startsWith("image/")) {
      dt.items.add(file)
      const reader = new FileReader()
      reader.onload = (e) => {
        addImagePreview(e.target.result, file.name, preview)
      }
      reader.readAsDataURL(file)
    }
  })

  // Update input files
  input.files = dt.files
  console.log("[v0] Updated input files count:", input.files.length)
}

function addImagePreview(src, name, container) {
  const previewItem = document.createElement("div")
  previewItem.className = "image-preview-item"
  previewItem.innerHTML = `
        <img src="${src}" alt="${name}">
        <button type="button" class="image-remove" onclick="removeImagePreview(this)">
            <i class="fas fa-times"></i>
        </button>
        <div class="image-name" style="padding: 0.5rem; font-size: 0.75rem; color: var(--admin-text-muted); text-align: center;">${name}</div>
    `
  container.appendChild(previewItem)
  console.log("[v0] Added image preview for:", name)
}

function removeImagePreview(button) {
  const previewItem = button.parentElement
  const container = previewItem.parentElement
  const uploadArea = container.previousElementSibling
  const input = uploadArea.querySelector('input[type="file"]')

  // Get the image name to remove
  const imageName = previewItem.querySelector(".image-name")?.textContent

  if (input && imageName) {
    // Create new FileList without the removed file
    const dt = new DataTransfer()
    for (let i = 0; i < input.files.length; i++) {
      if (input.files[i].name !== imageName) {
        dt.items.add(input.files[i])
      }
    }
    input.files = dt.files
    console.log("[v0] Removed file, remaining count:", input.files.length)
  }

  previewItem.remove()
}

// Form Validation
function initializeFormValidation() {
  const forms = document.querySelectorAll("form[data-validate]")

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      if (!validateForm(form)) {
        e.preventDefault()
      }
    })
  })
}

function validateForm(form) {
  let isValid = true
  const requiredFields = form.querySelectorAll("[required]")

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      showFieldError(field, "This field is required")
      isValid = false
    } else {
      clearFieldError(field)
    }
  })

  return isValid
}

function showFieldError(field, message) {
  clearFieldError(field)

  field.style.borderColor = "var(--admin-danger)"

  const errorDiv = document.createElement("div")
  errorDiv.className = "field-error"
  errorDiv.style.color = "var(--admin-danger)"
  errorDiv.style.fontSize = "0.75rem"
  errorDiv.style.marginTop = "0.25rem"
  errorDiv.textContent = message

  field.parentNode.appendChild(errorDiv)
}

function clearFieldError(field) {
  field.style.borderColor = ""
  const existingError = field.parentNode.querySelector(".field-error")
  if (existingError) {
    existingError.remove()
  }
}

// Tooltips
function initializeTooltips() {
  const tooltipElements = document.querySelectorAll("[data-tooltip]")

  tooltipElements.forEach((element) => {
    element.addEventListener("mouseenter", showTooltip)
    element.addEventListener("mouseleave", hideTooltip)
  })
}

function showTooltip(e) {
  const text = e.target.getAttribute("data-tooltip")
  const tooltip = document.createElement("div")
  tooltip.className = "tooltip"
  tooltip.textContent = text
  tooltip.style.cssText = `
        position: absolute;
        background: var(--admin-card);
        color: var(--admin-text);
        padding: 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        z-index: 1000;
        border: 1px solid var(--admin-border);
        box-shadow: 0 2px 4px var(--admin-shadow);
    `

  document.body.appendChild(tooltip)

  const rect = e.target.getBoundingClientRect()
  tooltip.style.left = rect.left + "px"
  tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px"

  e.target._tooltip = tooltip
}

function hideTooltip(e) {
  if (e.target._tooltip) {
    e.target._tooltip.remove()
    delete e.target._tooltip
  }
}

// Utility Functions
function showNotification(message, type = "success") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        color: white;
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `

  switch (type) {
    case "success":
      notification.style.background = "var(--admin-success)"
      break
    case "error":
      notification.style.background = "var(--admin-danger)"
      break
    case "warning":
      notification.style.background = "var(--admin-warning)"
      break
    default:
      notification.style.background = "var(--admin-primary)"
  }

  notification.textContent = message
  document.body.appendChild(notification)

  setTimeout(() => {
    notification.remove()
  }, 5000)
}

function confirmDelete(message = "Are you sure you want to delete this item?") {
  return confirm(message)
}

// Dynamic form fields
function addFormField(container, template) {
  const newField = document.createElement("div")
  newField.innerHTML = template
  container.appendChild(newField)
}

function removeFormField(button) {
  button.closest(".form-field-group").remove()
}

// Auto-save functionality
function enableAutoSave(form, endpoint) {
  const inputs = form.querySelectorAll("input, textarea, select")

  inputs.forEach((input) => {
    input.addEventListener("change", () => {
      const formData = new FormData(form)
      formData.append("auto_save", "1")

      fetch(endpoint, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showNotification("Changes saved automatically", "success")
          }
        })
        .catch((error) => {
          console.error("Auto-save failed:", error)
        })
    })
  })
}

// Add CSS animations
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .notification {
        animation: slideIn 0.3s ease;
    }
`
document.head.appendChild(style)

// Function to add logout button to header
function addLogoutButton() {
  const header = document.getElementById("header")
  const logoutButton = document.createElement("button")
  logoutButton.className = "logout-button"
  logoutButton.textContent = "Logout"
  logoutButton.style.cssText = `
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 0.5rem 1rem;
        background: var(--admin-primary);
        color: white;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
    `

  header.appendChild(logoutButton)
}

document.addEventListener("DOMContentLoaded", () => {
  // Add logout button to header
  addLogoutButton()
})
