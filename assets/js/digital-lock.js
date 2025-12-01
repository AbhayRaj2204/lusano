; (() => {
  let chips = document.querySelectorAll(".filter-chip")

  const applyFilter = (filter, items = null) => {
    const productItems = items || document.querySelectorAll(".products-grid .product-item")

    productItems.forEach((item) => {
      if (filter === "all") {
        item.classList.remove("is-hidden")
        return
      }
      const category = (item.getAttribute("data-category") || "").toLowerCase()
      if (category.includes(filter)) {
        item.classList.remove("is-hidden")
      } else {
        item.classList.add("is-hidden")
      }
    })
  }

  const initializeFilters = () => {
    chips = document.querySelectorAll(".filter-chip")

    chips.forEach((chip) => {
      // Remove existing listeners to avoid duplicates
      chip.removeEventListener("click", handleChipClick)
      chip.addEventListener("click", handleChipClick)
    })
  }

  const handleChipClick = (event) => {
    const chip = event.currentTarget
    chips.forEach((c) => c.classList.remove("active"))
    chip.classList.add("active")
    const filter = chip.getAttribute("data-filter") || "all"
    applyFilter(filter)
    window.scrollTo({ top: document.querySelector("#catalog").offsetTop - 80, behavior: "smooth" })
  }

  window.initializeFilters = initializeFilters

  const navigateToDigitalLockProduct = (productId) => {
    // Navigate to product details page with the product ID
    window.location.href = `product-details.html?id=${productId}`
  }

  window.navigateToDigitalLockProduct = navigateToDigitalLockProduct

  document.addEventListener("DOMContentLoaded", async () => {
    console.log("[v0] Digital lock page loaded")

    if (window.categoriesLoader) {
      await window.categoriesLoader.loadCategoriesIntoFilters()
    }

    // Initialize filter functionality
    initializeFilters()

    const staticProductItems = document.querySelectorAll(".product-item")
    staticProductItems.forEach((item) => {
      // Make sure cards are visible
      item.style.opacity = "1"
      item.style.transform = "translateY(0)"
      item.style.cursor = "pointer"

      // Add hover effects
      item.addEventListener("mouseenter", function () {
        this.style.transform = "translateY(-5px)"
        this.style.transition = "transform 0.3s ease"
      })

      item.addEventListener("mouseleave", function () {
        this.style.transform = "translateY(0)"
      })
    })

    try {
      console.log("[v0] Attempting to load dynamic products from CSV...")

      // Check if productDataLoader is available
      if (!window.productDataLoader) {
        console.log("[v0] Product data loader not available, keeping static products")
        return
      }

      const products = await window.productDataLoader.fetchProducts("digital")
      console.log("[v0] Loaded products:", products)

      if (products && products.length > 0) {
        console.log("[v0] Updating product grid with CSV data")
        updateProductGrid(products)
      } else {
        console.log("[v0] No products found in CSV")
        const productsGrid = document.querySelector(".products-grid")
        if (productsGrid) {
          productsGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;"><p>No products found. Please add products from the Admin Panel.</p></div>'
        }
      }
    } catch (error) {
      console.error("[v0] Failed to load dynamic products:", error)
      console.log("[v0] Keeping static products visible")
    }
  })

  function updateProductGrid(products) {
    const productsGrid = document.querySelector(".products-grid")
    if (!productsGrid || !products.length) {
      console.log("[v0] No products grid found or no products to display")
      return
    }

    console.log("[v0] Replacing static products with CSV products")
    productsGrid.innerHTML = ""

    products.forEach((product) => {
      const formattedProduct = window.productDataLoader.formatProductForDisplay(product)
      if (formattedProduct) {
        const productElement = createProductElement(formattedProduct)
        productsGrid.appendChild(productElement)
      }
    })

    // Reapply filter functionality to new elements
    const items = document.querySelectorAll(".products-grid .product-item")
    const activeFilter = document.querySelector(".filter-chip.active")?.getAttribute("data-filter") || "all"
    applyFilter(activeFilter, items)
  }

  function createProductElement(product) {
    const productDiv = document.createElement("div")
    productDiv.className = "product-item"
    productDiv.setAttribute("data-category", product.categories.join(" "))
    productDiv.setAttribute("data-product-id", product.id)
    productDiv.onclick = () => navigateToDigitalLockProduct(product.id)

    productDiv.style.opacity = "1"
    productDiv.style.transform = "translateY(0)"
    productDiv.style.cursor = "pointer"

    productDiv.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px)"
      this.style.transition = "transform 0.3s ease"
    })

    productDiv.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)"
    })

    const imageUrl =
      product.images && product.images.length > 0
        ? product.images[0]
        : `/placeholder.svg?height=200&width=200&query=${encodeURIComponent(product.title + " digital lock")}`

    productDiv.innerHTML = `
      <div class="product-image-container">
        <div class="product-bg"></div>
        <img src="${imageUrl}" alt="${product.title}" class="product-icon" 
             onerror="this.onerror=null; this.src='/placeholder.svg?height=200&width=200&query=Digital%20Lock'">
      </div>
      <div class="product-info">
        <h3>${product.title}</h3>
        <p>${product.tagline}</p>
        <div class="product-price">From <span>₹${product.price}</span></div>
        
      </div>
    `

    return productDiv
  }
})()
