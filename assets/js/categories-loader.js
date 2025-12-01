// Categories loader to fetch categories from CSV file
window.categoriesLoader = {
  async fetchCategories() {
    try {
      console.log("[v0] Fetching categories from CSV...")

      // Try to fetch from admin/data/categories.csv first
      let response = await fetch("admin/data/categories.csv")

      if (!response.ok) {
        console.log("[v0] Admin categories not found, trying fallback location...")
        response = await fetch("data/categories.csv")
      }

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const csvText = await response.text()
      console.log("[v0] Categories CSV loaded successfully")

      return this.parseCSV(csvText)
    } catch (error) {
      console.error("[v0] Error fetching categories:", error)
      return []
    }
  },

  parseCSV(csvText) {
    const lines = csvText.trim().split("\n")
    if (lines.length < 2) return []

    const headers = lines[0].split(",").map((h) => h.trim())
    const categories = []

    for (let i = 1; i < lines.length; i++) {
      const line = lines[i].trim()
      if (!line) continue

      const values = this.parseCSVLine(line)
      if (values.length >= headers.length) {
        const category = {}
        headers.forEach((header, index) => {
          category[header] = values[index] ? values[index].trim().replace(/^"|"$/g, "") : ""
        })

        // Only include active categories
        if (category.status === "active") {
          categories.push(category)
        }
      }
    }

    console.log("[v0] Parsed categories:", categories)
    return categories
  },

  parseCSVLine(line) {
    const values = []
    let current = ""
    let inQuotes = false

    for (let i = 0; i < line.length; i++) {
      const char = line[i]

      if (char === '"') {
        inQuotes = !inQuotes
      } else if (char === "," && !inQuotes) {
        values.push(current)
        current = ""
      } else {
        current += char
      }
    }

    values.push(current)
    return values
  },

  createCategoryButton(category) {
    const button = document.createElement("button")
    button.className = "filter-chip"
    button.setAttribute("data-filter", category.id)

    let iconClass = category.icon || "fa-tag"
    if (iconClass && !iconClass.includes("fas") && !iconClass.includes("far") && !iconClass.includes("fab")) {
      iconClass = `fas ${iconClass}`
    }

    button.innerHTML = `<i class="${iconClass}"></i> ${category.name}`

    return button
  },

  async loadCategoriesIntoFilters() {
    try {
      const categories = await this.fetchCategories()
      const filtersContainer = document.getElementById("category-filters")

      if (!filtersContainer) {
        console.error("[v0] Category filters container not found")
        return
      }

      // Keep the "All" button and add dynamic categories after it
      const allButton = filtersContainer.querySelector('[data-filter="all"]')

      // Remove any existing dynamic category buttons
      const existingButtons = filtersContainer.querySelectorAll('.filter-chip:not([data-filter="all"])')
      existingButtons.forEach((button) => button.remove())

      // Add new category buttons
      categories.forEach((category) => {
        const button = this.createCategoryButton(category)
        filtersContainer.appendChild(button)
      })

      console.log("[v0] Categories loaded into filter bar")

      // Reinitialize filter functionality
      if (window.initializeFilters) {
        window.initializeFilters()
      }
    } catch (error) {
      console.error("[v0] Error loading categories into filters:", error)
    }
  },
}
