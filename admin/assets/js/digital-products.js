// Digital Products Management JavaScript

document.addEventListener("DOMContentLoaded", () => {
  // Initialize product form if it exists
  const productForm = document.getElementById("product-form")
  if (productForm) {
    initializeProductForm()
  }

  // Initialize search and filters
  initializeFilters()
})

function initializeProductForm() {
  const form = document.getElementById("product-form")

  form.addEventListener("submit", (e) => {
    e.preventDefault()
    saveProduct()
  })

  // Auto-generate ID from title
  const titleInput = form.querySelector('input[name="title"]')
  const idInput = form.querySelector('input[name="id"]')

  if (titleInput && idInput && !idInput.value) {
    titleInput.addEventListener("input", function () {
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

function saveProduct() {
  const form = document.getElementById("product-form")
  const formData = new FormData(form)

  formData.append("type", "digital")

  // Show loading state
  const submitBtn = form.querySelector('button[type="submit"]')
  const originalText = submitBtn.innerHTML
  submitBtn.innerHTML = '<div class="spinner"></div> Saving...'
  submitBtn.disabled = true

  console.log("[v0] Submitting digital product form with data:", Object.fromEntries(formData))

  fetch("api/products.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      console.log("[v0] Server response:", data)
      if (data.success) {
        showNotification("Product saved successfully!", "success")
        setTimeout(() => {
          window.location.href = "digital-products.php"
        }, 1500)
      } else {
        showNotification("Error: " + data.message, "error")
        submitBtn.innerHTML = originalText
        submitBtn.disabled = false
      }
    })
    .catch((error) => {
      console.log("[v0] Error saving product:", error)
      showNotification("Error saving product: " + error.message, "error")
      submitBtn.innerHTML = originalText
      submitBtn.disabled = false
    })
}

function addFeature() {
  const container = document.getElementById("features-container")
  const template = `
        <div class="form-field-group" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <input type="text" name="features[]" class="form-input" placeholder="Feature description">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `
  addFormField(container, template)
}

function addDetailedFeature() {
  const container = document.getElementById("detailed-features-container")
  const index = container.children.length
  const template = `
        <div class="form-field-group" style="border: 1px solid var(--admin-border); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem;">
                <input type="text" name="detailed_features[${index}][title]" class="form-input" placeholder="Feature title">
                <input type="text" name="detailed_features[${index}][icon]" class="form-input" placeholder="fa-icon-name">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <textarea name="detailed_features[${index}][description]" class="form-input form-textarea" placeholder="Detailed description"></textarea>
        </div>
    `
  addFormField(container, template)
}

function addSpecification() {
  const container = document.getElementById("specs-container")
  const template = `
        <div class="form-field-group" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem;">
            <input type="text" name="specs_keys[]" class="form-input" placeholder="Specification name">
            <input type="text" name="specs_values[]" class="form-input" placeholder="Specification value">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `
  addFormField(container, template)
}

function initializeFilters() {
  const searchInput = document.getElementById("search-input")
  const statusFilter = document.getElementById("status-filter")
  const table = document.getElementById("products-table")

  if (!searchInput || !table) return

  function filterTable() {
    const searchTerm = searchInput.value.toLowerCase()
    const statusValue = statusFilter.value.toLowerCase()
    const rows = table.querySelectorAll("tbody tr")

    rows.forEach((row) => {
      const title = row.cells[0].textContent.toLowerCase()
      const status = row.cells[3].textContent.toLowerCase()

      const matchesSearch = title.includes(searchTerm)
      const matchesStatus = !statusValue || status.includes(statusValue)

      row.style.display = matchesSearch && matchesStatus ? "" : "none"
    })
  }

  searchInput.addEventListener("input", filterTable)
  statusFilter.addEventListener("change", filterTable)
}

function deleteProduct(id, type) {
  if (!confirmDelete("Are you sure you want to delete this product? This action cannot be undone.")) {
    return
  }

  const formData = new FormData()
  formData.append("action", "delete")
  formData.append("id", id)
  formData.append("type", type)

  fetch("api/products.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Product deleted successfully", "success")
        setTimeout(() => {
          location.reload()
        }, 1000)
      } else {
        showNotification("Error deleting product: " + data.message, "error")
      }
    })
    .catch((error) => {
      showNotification("Error deleting product", "error")
    })
}

function duplicateProduct(id) {
  const formData = new FormData()
  formData.append("action", "duplicate")
  formData.append("id", id)
  formData.append("type", "digital")

  fetch("api/products.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Product duplicated successfully", "success")
        setTimeout(() => {
          window.location.href = "digital-products.php?action=edit&id=" + data.new_id
        }, 1000)
      } else {
        showNotification("Error duplicating product: " + data.message, "error")
      }
    })
    .catch((error) => {
      showNotification("Error duplicating product", "error")
    })
}

function previewProduct() {
  const form = document.getElementById("product-form")
  const formData = new FormData(form)

  // Open preview in new window
  const previewWindow = window.open("", "_blank", "width=1200,height=800")
  previewWindow.document.write(
    "<html><head><title>Product Preview</title></head><body><h1>Loading preview...</h1></body></html>",
  )

  fetch("api/preview.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((html) => {
      previewWindow.document.open()
      previewWindow.document.write(html)
      previewWindow.document.close()
    })
    .catch((error) => {
      previewWindow.document.body.innerHTML = "<h1>Error loading preview</h1>"
    })
}

function showNotification(message, type) {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    color: white;
    font-weight: 500;
    z-index: 9999;
    max-width: 400px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    background: ${type === "success" ? "#10b981" : "#ef4444"};
  `
  notification.textContent = message

  document.body.appendChild(notification)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.parentNode.removeChild(notification)
    }
  }, 5000)

  console.log(`[v0] Notification (${type}): ${message}`)
}

function addFormField(container, template) {
  // Implementation for adding form fields
  const div = document.createElement("div")
  div.innerHTML = template
  container.appendChild(div.firstChild)
}

function confirmDelete(message) {
  // Implementation for confirming deletion
  return confirm(message)
}

function removeFormField(button) {
  const fieldGroup = button.parentElement
  fieldGroup.parentElement.removeChild(fieldGroup)
}
