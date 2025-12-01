// Category Management JavaScript

document.addEventListener("DOMContentLoaded", () => {
  // Initialize category form if it exists
  const categoryForm = document.getElementById("category-form")
  if (categoryForm) {
    initializeCategoryForm()
  }

  // Initialize search and filters
  initializeFilters()

  // Initialize icon preview
  initializeIconPreview()
})

function initializeCategoryForm() {
  const form = document.getElementById("category-form")

  form.addEventListener("submit", (e) => {
    e.preventDefault()
    saveCategory()
  })

  // Auto-generate ID from name
  const nameInput = form.querySelector('input[name="name"]')
  const idInput = form.querySelector('input[name="id"]')

  if (nameInput && idInput && !idInput.value && !idInput.readOnly) {
    nameInput.addEventListener("input", function () {
      const id = this.value
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-")
        .trim()
      idInput.value = id
    })
  }
}

function initializeIconPreview() {
  const iconInput = document.querySelector('input[name="icon"]')
  const iconPreview = document.getElementById("icon-preview")

  if (iconInput && iconPreview) {
    iconInput.addEventListener("input", function () {
      const iconClass = this.value || "fa-tag"
      const iconElement = iconPreview.querySelector("i")
      iconElement.className = `fas ${iconClass}`
    })
  }
}

function selectIcon(iconClass) {
  const iconInput = document.querySelector('input[name="icon"]')
  const iconPreview = document.getElementById("icon-preview")

  if (iconInput) {
    iconInput.value = iconClass

    if (iconPreview) {
      const iconElement = iconPreview.querySelector("i")
      iconElement.className = `fas ${iconClass}`
    }

    // Highlight selected icon
    document.querySelectorAll(".icon-option").forEach((option) => {
      option.style.background = "var(--admin-card-hover)"
      option.style.borderColor = "var(--admin-border)"
    })

    event.target.closest(".icon-option").style.background = "var(--admin-primary)"
    event.target.closest(".icon-option").style.borderColor = "var(--admin-primary)"
  }
}

function saveCategory() {
  const form = document.getElementById("category-form")
  const formData = new FormData(form)

  // Show loading state
  const submitBtn = form.querySelector('button[type="submit"]')
  const originalText = submitBtn.innerHTML
  submitBtn.innerHTML = '<div class="spinner"></div> Saving...'
  submitBtn.disabled = true

  fetch("api/categories.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Category saved successfully!", "success")
        setTimeout(() => {
          window.location.href = "categories.php"
        }, 1500)
      } else {
        showNotification("Error: " + data.message, "error")
        submitBtn.innerHTML = originalText
        submitBtn.disabled = false
      }
    })
    .catch((error) => {
      showNotification("Error saving category", "error")
      submitBtn.innerHTML = originalText
      submitBtn.disabled = false
    })
}

function deleteCategory(id) {
  if (!confirmDelete("Are you sure you want to delete this category? This action cannot be undone.")) {
    return
  }

  const formData = new FormData()
  formData.append("action", "delete")
  formData.append("id", id)

  fetch("api/categories.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Category deleted successfully", "success")
        setTimeout(() => {
          location.reload()
        }, 1000)
      } else {
        showNotification("Error deleting category: " + data.message, "error")
      }
    })
    .catch((error) => {
      showNotification("Error deleting category", "error")
    })
}

function initializeFilters() {
  const searchInput = document.getElementById("search-input")
  const statusFilter = document.getElementById("status-filter")
  const table = document.getElementById("categories-table")

  if (!searchInput || !table) return

  function filterTable() {
    const searchTerm = searchInput.value.toLowerCase()
    const statusValue = statusFilter.value.toLowerCase()
    const rows = table.querySelectorAll("tbody tr")

    rows.forEach((row) => {
      const name = row.cells[0].textContent.toLowerCase()
      const description = row.cells[1].textContent.toLowerCase()
      const status = row.cells[3].textContent.toLowerCase()

      const matchesSearch = name.includes(searchTerm) || description.includes(searchTerm)
      const matchesStatus = !statusValue || status.includes(statusValue)

      row.style.display = matchesSearch && matchesStatus ? "" : "none"
    })
  }

  searchInput.addEventListener("input", filterTable)
  statusFilter.addEventListener("change", filterTable)
}

function previewCategory() {
  const form = document.getElementById("category-form")
  const formData = new FormData(form)

  // Create preview modal
  const modal = document.createElement("div")
  modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    `

  const content = document.createElement("div")
  content.style.cssText = `
        background: var(--admin-card);
        border-radius: 0.75rem;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        border: 1px solid var(--admin-border);
    `

  const name = formData.get("name") || "Category Name"
  const icon = formData.get("icon") || "fa-tag"
  const description = formData.get("description") || "Category description"

  content.innerHTML = `
        <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1.5rem;">
            <h3>Category Preview</h3>
            <button onclick="this.closest('.modal').remove()" style="background: none; border: none; color: var(--admin-text); font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="background: var(--admin-primary); color: white; padding: 1.5rem; border-radius: 0.75rem; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                <i class="fas ${icon}"></i>
            </div>
            <h4>${name}</h4>
            <p style="color: var(--admin-text-muted);">${description}</p>
        </div>
        
        <div style="background: var(--admin-input); border-radius: 0.5rem; padding: 1rem;">
            <h5>Filter Button Preview:</h5>
            <button style="background: var(--admin-primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas ${icon}"></i>
                ${name}
            </button>
        </div>
    `

  modal.className = "modal"
  modal.appendChild(content)
  document.body.appendChild(modal)

  // Close on background click
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.remove()
    }
  })
}

// Add hover effects for icon options
document.addEventListener("DOMContentLoaded", () => {
  const style = document.createElement("style")
  style.textContent = `
        .icon-option:hover {
            background: var(--admin-primary) !important;
            border-color: var(--admin-primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--admin-shadow);
        }
        
        .icon-option:hover i {
            color: white !important;
        }
        
        .icon-option:hover div {
            color: white !important;
        }
    `
  document.head.appendChild(style)
})

// Declare showNotification and confirmDelete functions
function showNotification(message, type) {
  alert(message) // Placeholder for actual notification implementation
}

function confirmDelete(message) {
  return confirm(message) // Placeholder for actual confirmation implementation
}
