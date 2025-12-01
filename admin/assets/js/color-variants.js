// Color picker sync
const colorPicker = document.getElementById("color-picker")
const colorHexText = document.getElementById("color-hex-text")

if (colorPicker) {
  colorPicker.addEventListener("change", function () {
    colorHexText.value = this.value.toUpperCase()
  })
}

if (colorHexText) {
  colorHexText.addEventListener("change", function () {
    if (/^#[0-9A-F]{6}$/i.test(this.value)) {
      colorPicker.value = this.value
    }
  })
}

document.querySelectorAll(".image-upload-wrapper").forEach((wrapper, index) => {
  const inputIndex = index + 1
  const input = document.getElementById("image-input-" + inputIndex)

  if (!input) return

  wrapper.addEventListener("click", () => {
    input.click()
  })

  input.addEventListener("change", function (e) {
    if (this.files && this.files[0]) {
      const file = this.files[0]

      // Validate file size
      if (file.size > 5 * 1024 * 1024) {
        alert("File size must be less than 5MB")
        return
      }

      const reader = new FileReader()
      reader.onload = (e) => {
        // Clear wrapper and recreate with preview
        wrapper.innerHTML = ""

        const img = document.createElement("img")
        img.src = e.target.result
        img.className = "preview-image"
        img.style.cssText =
          "max-width: 100%; max-height: 200px; border-radius: 0.25rem; margin-bottom: 0.5rem; object-fit: contain;"
        wrapper.appendChild(img)

        // Add click to change text
        const text = document.createElement("div")
        text.textContent = "Click to change image"
        text.style.cssText = "color: var(--admin-text-muted); font-size: 0.875rem;"
        wrapper.appendChild(text)

        wrapper.setAttribute("data-has-image", "true")
        wrapper.style.display = "flex"
        wrapper.style.flexDirection = "column"
        wrapper.style.alignItems = "center"
        wrapper.style.justifyContent = "center"
      }
      reader.readAsDataURL(file)
    }
  })

  // Drag and drop
  wrapper.addEventListener("dragover", function (e) {
    e.preventDefault()
    this.style.backgroundColor = "var(--admin-primary)"
    this.style.opacity = "0.5"
  })

  wrapper.addEventListener("dragleave", function (e) {
    this.style.backgroundColor = ""
    this.style.opacity = "1"
  })

  wrapper.addEventListener("drop", function (e) {
    e.preventDefault()
    this.style.backgroundColor = ""
    this.style.opacity = "1"

    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      input.files = e.dataTransfer.files
      input.dispatchEvent(new Event("change"))
    }
  })
})

// Form submission
const form = document.getElementById("color-variant-form")
if (form) {
  form.addEventListener("submit", async function (e) {
    e.preventDefault()

    const formData = new FormData(this)
    const action = formData.get("action")

    // Add product name and category from select
    const productSelect = document.getElementById("product-select")
    formData.set("product_name", productSelect.options[productSelect.selectedIndex].dataset.name)
    formData.set("product_category", productSelect.options[productSelect.selectedIndex].dataset.category)

    // Add correct action for API
    if (action === "add") {
      formData.set("action", "save")
    } else if (action === "edit") {
      formData.set("action", "update")
    }

    try {
      const response = await fetch("api/color-variants.php", {
        method: "POST",
        body: formData,
      })

      const data = await response.json()

      if (data.success) {
        alert(data.message)
        window.location.href = "color-variants.php"
      } else {
        alert("Error: " + data.message)
      }
    } catch (error) {
      alert("Error submitting form: " + error.message)
    }
  })
}

// Table filtering
const searchInput = document.getElementById("search-input")
const productFilter = document.getElementById("product-filter")
const variantsTable = document.getElementById("variants-table")

function filterTable() {
  if (!variantsTable) return

  const searchTerm = searchInput?.value.toLowerCase() || ""
  const productId = productFilter?.value || ""

  variantsTable.querySelectorAll("tbody tr").forEach((row) => {
    const productName = row.querySelector("td")?.textContent.toLowerCase() || ""
    const matchesSearch = productName.includes(searchTerm)

    const productCell = row.querySelector("td:nth-child(3)")
    const rowProductId = productCell?.textContent || ""
    const matchesProduct = !productId || rowProductId.includes(productId)

    row.style.display = matchesSearch && matchesProduct ? "" : "none"
  })
}

searchInput?.addEventListener("input", filterTable)
productFilter?.addEventListener("change", filterTable)

// Delete function
function deleteVariant(id) {
  if (confirm("Are you sure you want to delete this color variant?")) {
    const formData = new FormData()
    formData.append("action", "delete")
    formData.append("id", id)

    fetch("api/color-variants.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Color variant deleted successfully")
          location.reload()
        } else {
          alert("Error deleting variant: " + data.message)
        }
      })
      .catch((error) => alert("Error: " + error.message))
  }
}
