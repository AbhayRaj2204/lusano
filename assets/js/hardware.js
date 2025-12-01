; (() => {
  console.log("[LUSANO] Hardware page loaded")

  let hardwareProducts = []

  // Fetch hardware products from CSV
  async function loadHardwareProducts() {
    try {
      console.log("[v0] Fetching hardware products from CSV...")

      const [productsResponse, variantsResponse] = await Promise.all([
        fetch("api/get-products.php?type=hardware"),
        fetch("api/get-color-variants.php")
      ]);

      if (!productsResponse.ok) {
        throw new Error(`HTTP error! status: ${productsResponse.status}`)
      }

      const products = await productsResponse.json()
      let variants = [];
      try {
        const variantsData = await variantsResponse.json();
        if (variantsData.success) {
          variants = variantsData.variants;
        }
      } catch (e) {
        console.error("[v0] Error parsing variants:", e);
      }


      // Merge variant images - match by product_id first, then by product_name
      products.forEach(product => {
        // Find variants for this product - try matching by product_id first
        let productVariants = variants.filter(v =>
          v.product_id && product.id && v.product_id.toLowerCase().trim() === product.id.toLowerCase().trim()
        );

        // If no match by ID, try matching by product_name against title
        if (productVariants.length === 0) {
          productVariants = variants.filter(v =>
            v.product_name && product.title &&
            v.product_name.toLowerCase().trim() === product.title.toLowerCase().trim()
          );
        }

        if (productVariants.length > 0) {
          // Use the first variant's first image as the main image
          const firstVariant = productVariants[0];
          console.log("[v0] Found variant for product:", product.title, "Variant:", firstVariant);
          if (firstVariant.images && firstVariant.images.length > 0) {
            // Override the main image path
            product.image = firstVariant.images[0];

            // Update images array structure to match what the display expects
            product.images = firstVariant.images.map(path => ({
              path: path,
              alt: product.title + ' ' + firstVariant.color_name,
              sort_order: 0
            }));
          }
        } else {
          console.log("[v0] No variant found for product:", product.title, "ID:", product.id);
        }
      });

      console.log("[v0] Loaded hardware products:", products)

      hardwareProducts = products
      displayProducts()
    } catch (error) {
      console.error("[v0] Error loading hardware products:", error)
      const productsGrid = document.querySelector(".products-grid")
      if (productsGrid) {
        productsGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;"><p>Error loading products. Please try again later.</p></div>'
      }
    }
  }

  function displayProducts() {
    const productsGrid = document.querySelector(".products-grid")
    if (!productsGrid) return

    if (hardwareProducts.length === 0) {
      productsGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;"><p>No hardware products found. Please add products from the Admin Panel.</p></div>'
      return
    }

    productsGrid.innerHTML = ""

    hardwareProducts.forEach((product) => {
      // Parse features to get first feature for description
      let features = []
      if (product.features) {
        const featureString = typeof product.features === "string" ? product.features : product.features.join("|")
        features = featureString.split("|").map((f) => {
          const parts = f.split("~")
          return parts[0] || f
        })
      }

      const categoriesArray = Array.isArray(product.categories)
        ? product.categories
        : product.categories
          ? String(product.categories).split(",")
          : []
      const categoriesAttr = categoriesArray
        .map((c) => String(c).trim())
        .filter(Boolean)
        .join(",")

      const productElement = document.createElement("div")
      productElement.className = "product-item"
      productElement.setAttribute("data-type", product.id)
      productElement.setAttribute("data-categories", categoriesAttr)
      productElement.onclick = () => window.navigateToProduct(product.id)

      // Get main product image - check images array first, then product.image, then placeholder
      // Create a simple SVG placeholder as data URI
      const placeholderSvg = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect fill='%23222' width='200' height='200'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='14' fill='%23666'%3E${encodeURIComponent(product.title)}%3C/text%3E%3C/svg%3E`;
      let mainImage = placeholderSvg;

      if (product.images && product.images.length > 0 && product.images[0].path) {
        mainImage = product.images[0].path;
      } else if (product.image && product.image.trim()) {
        mainImage = product.image.trim();
      }

      productElement.innerHTML = `
        <div class="product-image-container">
          <div class="product-bg"></div>
          <img src="${mainImage}" alt="${product.title}" class="product-icon" 
               onerror="this.onerror=null; this.src='${placeholderSvg}'">
        </div>
        <div class="product-info">
          <h3>${product.title}</h3>
          <p>${features[0] || product.tagline || "Professional hardware solution"}</p>
          <div class="product-price">From <span>₹${product.price}</span></div>
          
        </div>
      `

      productsGrid.appendChild(productElement)
    })

    // Re-apply hover effects to new elements
    addHoverEffects()

    if (window.hardwareCategoriesLoader?.applyActiveFilterFromUI) {
      window.hardwareCategoriesLoader.applyActiveFilterFromUI()
    }
  }



  // Product navigation function
  window.navigateToProduct = (productId) => {
    sessionStorage.setItem("selectedHardwareProduct", productId)
    window.location.href = "hardware-product-details.html"
  }

  // Add hover effects to product items
  function addHoverEffects() {
    const productItems = document.querySelectorAll(".product-item")

    productItems.forEach((item) => {
      item.style.cursor = "pointer"

      item.addEventListener("mouseenter", function () {
        this.style.transform = "translateY(-5px)"
        this.style.transition = "transform 0.3s ease"
      })

      item.addEventListener("mouseleave", function () {
        this.style.transform = "translateY(0)"
      })
    })
  }

  document.addEventListener("DOMContentLoaded", () => {
    loadHardwareProducts()
    if (window.hardwareCategoriesLoader) {
      window.hardwareCategoriesLoader.loadCategoriesIntoFilters()
    }
  })
})()
