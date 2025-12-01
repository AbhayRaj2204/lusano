// Hardware Categories loader to fetch categories from hardware_categories.csv file
window.hardwareCategoriesLoader = {
  async fetchCategories() {
    try {
      console.log("[v0] Fetching hardware categories from CSV...")

      const response = await fetch("admin/data/hardware_categories.csv")

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const csvText = await response.text()
      console.log("[v0] Hardware categories CSV loaded successfully")

      return this.parseCSV(csvText)
    } catch (error) {
      console.error("[v0] Error fetching hardware categories:", error)
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

    console.log("[v0] Parsed hardware categories:", categories)
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

      console.log("[v0] Hardware categories loaded into filter bar")

      // Initialize filter functionality
      this.initializeFilters()

      this.applyFilterFromQuery()
    } catch (error) {
      console.error("[v0] Error loading hardware categories into filters:", error)
    }
  },

  initializeFilters() {
    const filterButtons = document.querySelectorAll(".filter-chip")

    filterButtons.forEach((button) => {
      button.addEventListener("click", () => {
        // Remove active class from all buttons
        filterButtons.forEach((btn) => btn.classList.remove("active"))
        // Add active class to clicked button
        button.classList.add("active")

        const filter = button.getAttribute("data-filter")
        this.filterProducts(filter)
      })
    })
  },

  filterProducts(filter) {
    const productItems = document.querySelectorAll(".product-item")

    productItems.forEach((item) => {
      if (filter === "all") {
        item.style.display = ""
        return
      }

      const categoriesAttr = item.getAttribute("data-categories") || ""
      const categories = categoriesAttr
        .split(",")
        .map((c) => c.trim())
        .filter(Boolean)
      if (categories.includes(filter)) {
        item.style.display = ""
      } else {
        item.style.display = "none"
      }
    })
  },

  applyActiveFilterFromUI() {
    const activeBtn = document.querySelector(".filter-chip.active")
    const filter = activeBtn?.getAttribute("data-filter") || "all"
    this.filterProducts(filter)
  },

  applyFilterFromQuery() {
    const params = new URLSearchParams(window.location.search)
    const category = params.get("category")
    if (!category) return

    const filterButtons = document.querySelectorAll(".filter-chip")
    let matched = false
    filterButtons.forEach((btn) => {
      if (btn.getAttribute("data-filter") === category) {
        filterButtons.forEach((b) => b.classList.remove("active"))
        btn.classList.add("active")
        matched = true
      }
    })

    if (matched) {
      this.filterProducts(category)
    }
  },
}
