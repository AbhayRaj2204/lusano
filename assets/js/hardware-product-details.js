// Hardware Product Details JavaScript

function getBasePath() {
  return window.location.pathname.replace(/[^/]+$/, "")
}

function toAbsoluteUrlWithBase(path) {
  if (!path) return path
  if (/^https?:\/\//i.test(path)) return path
  const origin = window.location.origin
  const base = getBasePath()
  if (path.startsWith("/")) {
    return origin + base.replace(/\/$/, "") + path
  }
  return origin + base + path
}

document.addEventListener("DOMContentLoaded", () => {
  // Hardware product data
  let currentProduct = null

  function toAbsoluteUrl(path) {
    try {
      return new URL(path, window.location.origin).href
    } catch {
      return path
    }
  }

  function ensureMeta(selector, attr, value, createAs = "meta", attrs = {}) {
    let el = document.querySelector(selector)
    if (!el) {
      el = document.createElement(createAs)
      Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v))
      document.head.appendChild(el)
    }
    el.setAttribute(attr, value)
    return el
  }

  function setProductMeta({ title, description, image, url }) {
    document.title = `${title} – LUSANO`
    ensureMeta('meta[name="description"]', "content", description, "meta", { name: "description" })
    ensureMeta('meta[property="og:title"]', "content", `${title} – LUSANO`, "meta", { property: "og:title" })
    ensureMeta('meta[property="og:description"]', "content", description, "meta", { property: "og:description" })
    ensureMeta('meta[property="og:image"]', "content", image, "meta", { property: "og:image" })
    ensureMeta('meta[property="og:url"]', "content", url, "meta", { property: "og:url" })
    ensureMeta('link[rel="canonical"]', "href", url, "link", { rel: "canonical" })
  }

  async function loadProductData() {
    try {
      const urlParams = new URLSearchParams(window.location.search)
      const productId = urlParams.get("id") || sessionStorage.getItem("selectedHardwareProduct") || "closer"

      if (!urlParams.get("id")) {
        const url = new URL(window.location.href)
        url.searchParams.set("id", productId)
        window.history.replaceState(null, "", url.toString())
      }

      console.log("[v0] Loading product data for ID:", productId)

      const response = await fetch(`api/get-products.php?type=hardware&id=${productId}`)

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const product = await response.json()
      console.log("[v0] Loaded product data:", product)

      currentProduct = product
      displayProductData(product)
    } catch (error) {
      console.error("[v0] Error loading product data:", error)
      // Fallback to hardcoded data
      loadFallbackData()
    }
  }

  function displayProductData(product) {
    // Update basic product information
    document.getElementById("hw-title").textContent = product.title
    document.getElementById("hw-badge").textContent = product.badge || "Professional Series"
    document.getElementById("hw-tagline").textContent = product.tagline || "Professional hardware solution"
    document.getElementById("hw-price").textContent = `₹${product.price}`

    const originalPriceElement = document.getElementById("original-price")
    if (product.original_price && product.original_price > product.price) {
      originalPriceElement.textContent = `₹${product.original_price}`
      originalPriceElement.style.display = "inline"
    } else {
      originalPriceElement.style.display = "none"
    }

    const featuresList = document.getElementById("hw-features")
    featuresList.innerHTML = ""

    if (product.features) {
      const featuresString = typeof product.features === "string" ? product.features : product.features.join("|")
      const features = featuresString.split("|")

      features.forEach((feature) => {
        const parts = feature.split("~")
        const text = parts[0]
        const icon = parts[1] || "fas fa-check"

        if (text && text.trim()) {
          const li = document.createElement("li")
          li.innerHTML = `<i class="${icon}"></i> ${text.trim()}`
          featuresList.appendChild(li)
        }
      })
    }

    const specsGrid = document.getElementById("hw-specs")
    if (specsGrid && product.specs) {
      specsGrid.innerHTML = ""

      if (typeof product.specs === "object") {
        // Already parsed by API
        Object.entries(product.specs).forEach(([label, value]) => {
          const specItem = document.createElement("div")
          specItem.className = "spec-item"
          specItem.innerHTML = `
            <span class="spec-label">${label}</span>
            <span class="spec-value">${value}</span>
          `
          specsGrid.appendChild(specItem)
        })
      }
    }

    const images =
      product.images && product.images.length > 0
        ? product.images.map((img) => img.path)
        : [`/placeholder.svg?height=500&width=500&query=${encodeURIComponent(product.title)}`]

    initializeImageGallery(images)

    const pageParams = new URLSearchParams(window.location.search)
    const productId = pageParams.get("id") || product.id || "closer"
    const basePath = getBasePath() // e.g. /mythic/
    const mainImageUrl = toAbsoluteUrlWithBase(images[0] || "/computer-hardware.png")

    // Include title/desc/img so PHP share page can render exact OG
    const shareUrl = `${window.location.origin}${basePath}share/?type=hardware&id=${encodeURIComponent(productId)}`

    const quoteButton = document.getElementById("hw-quote")
    if (quoteButton) {
      const whatsappText = `Product enquiry:\n*${product.title}*\n${shareUrl}`
      quoteButton.href = `https://wa.me/9709226079?text=${encodeURIComponent(whatsappText)}`
    }

    setProductMeta({
      title: product.title,
      description: product.tagline || "Discover LUSANO hardware product details.",
      image: mainImageUrl,
      url: shareUrl,
    })

    createInstallationGuide(product.installation_guide_video)

    loadColorVariants(product.title, product.id)
  }

  function loadFallbackData() {
    console.log("[v0] Using fallback hardcoded data")

    const hardwareProducts = {
      closer: {
        title: "HC-200 Door Closer",
        badge: "Professional Series",
        tagline: "Precision-engineered for commercial and residential applications.",
        price: "$129",
        originalPrice: "$159",
        images: [
          "/door-closer-hardware.jpg",
          "/door-closer-mechanism.jpg",
          "/door-closer-installation.jpg",
          "/door-closer-assembly.jpg",
        ],
        features: [
          { icon: "fas fa-cog", text: "Adjustable closing force (1-6 settings)" },
          { icon: "fas fa-shield-alt", text: "Weather-resistant aluminum body" },
          { icon: "fas fa-tools", text: "Easy installation with standard templates" },
          { icon: "fas fa-clock", text: "Self-lubricating mechanism" },
          { icon: "fas fa-certificate", text: "EN 1154 certified performance" },
        ],
        specs: {
          Model: "HC-200 Professional",
          "Door Weight": "Up to 120kg (265 lbs)",
          "Door Width": 'Up to 1400mm (55")',
          "Closing Force": "Adjustable 1-6 (EN 1154)",
          Material: "Aluminum body, steel internals",
          Finish: "Silver anodized, Black powder coat",
          "Operating Temperature": "-40°C to +60°C (-40°F to +140°F)",
          Certification: "EN 1154, CE marked",
        },
        installation_guide_video: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
      },
      hinge: {
        title: "SH-90 Stainless Hinge",
        badge: "Premium Series",
        tagline: "316 stainless steel construction for superior durability.",
        price: "$39",
        originalPrice: "$49",
        images: ["/stainless-steel-hinge.jpg", "/hinge-mechanism.jpg", "/hinge-installation.jpg", "/hinge-finish.jpg"],
        features: [
          { icon: "fas fa-gem", text: "316 stainless steel construction" },
          { icon: "fas fa-water", text: "Corrosion resistant finish" },
          { icon: "fas fa-cogs", text: "Smooth pivot action" },
          { icon: "fas fa-weight-hanging", text: "Heavy-duty load capacity" },
          { icon: "fas fa-tools", text: "Standard mounting pattern" },
        ],
        specs: {
          Model: "SH-90 Premium",
          Material: "316 Stainless Steel",
          "Door Weight": "Up to 80kg (176 lbs)",
          "Hinge Size": '100mm x 75mm (4" x 3")',
          "Pin Diameter": '13mm (0.5")',
          Finish: "Satin stainless steel",
          "Operating Temperature": "-30°C to +80°C (-22°F to +176°F)",
          Certification: "ANSI/BHMA A156.1",
        },
        installation_guide_video: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
      },
      cylinder: {
        title: "CY-Pro Cylinder",
        badge: "Security Series",
        tagline: "Advanced security cylinder with anti-drill protection.",
        price: "$59",
        originalPrice: "$79",
        images: [
          "/security-cylinder.jpg",
          "/cylinder-key-profile.jpg",
          "/cylinder-installation.jpg",
          "/cylinder-security.jpg",
        ],
        features: [
          { icon: "fas fa-shield-alt", text: "Anti-drill core protection" },
          { icon: "fas fa-key", text: "Pick-resistant profile" },
          { icon: "fas fa-users", text: "Master key system ready" },
          { icon: "fas fa-lock", text: "High-security pins" },
          { icon: "fas fa-certificate", text: "Security grade certified" },
        ],
        specs: {
          Model: "CY-Pro Security",
          "Security Rating": "Grade 1 (ANSI/BHMA)",
          "Key Profile": "Restricted keyway",
          "Pin Configuration": "6-pin tumbler",
          Material: "Brass body, hardened steel pins",
          Finish: "Satin chrome, Antique brass",
          "Key Control": "Patent protected",
          Certification: "UL Listed, ANSI Grade 1",
        },
        installation_guide_video: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
      },
    }

    const productType = sessionStorage.getItem("selectedHardwareProduct") || "closer"
    const product = hardwareProducts[productType]

    if (product) {
      // Update product information
      document.getElementById("hw-title").textContent = product.title
      document.getElementById("hw-badge").textContent = product.badge
      document.getElementById("hw-tagline").textContent = product.tagline
      document.getElementById("hw-price").textContent = product.price
      document.getElementById("original-price").textContent = product.originalPrice

      // Update features list
      const featuresList = document.getElementById("hw-features")
      featuresList.innerHTML = ""
      product.features.forEach((feature) => {
        const li = document.createElement("li")
        li.innerHTML = `<i class="${feature.icon}"></i> ${feature.text}`
        featuresList.appendChild(li)
      })

      const specsGrid = document.getElementById("hw-specs")
      if (specsGrid && product.specs) {
        specsGrid.innerHTML = ""
        Object.entries(product.specs).forEach(([label, value]) => {
          const specItem = document.createElement("div")
          specItem.className = "spec-item"
          specItem.innerHTML = `
            <span class="spec-label">${label}</span>
            <span class="spec-value">${value}</span>
          `
          specsGrid.appendChild(specItem)
        })
      }

      // Initialize image gallery
      initializeImageGallery(product.images)

      const mainImageUrl = toAbsoluteUrlWithBase(images[0] || "/computer-hardware.png")
      const pageParams = new URLSearchParams(window.location.search)
      const productId = pageParams.get("id") || sessionStorage.getItem("selectedHardwareProduct") || "closer"
      const basePath = getBasePath()
      const shareUrl = `${window.location.origin}${basePath}share/?type=hardware&id=${encodeURIComponent(productId)}`
      const quoteButton = document.getElementById("hw-quote")
      if (quoteButton) {
        const whatsappText = `Product enquiry:\n*${product.title}*\n${shareUrl}`
        quoteButton.href = `https://wa.me/9709226079?text=${encodeURIComponent(whatsappText)}`
      }
      setProductMeta({
        title: product.title,
        description: product.tagline || "Discover LUSANO hardware product details.",
        image: mainImageUrl,
        url: shareUrl,
      })

      createInstallationGuide(product.installation_guide_video)

      loadColorVariants(product.title)
    }
  }

  loadProductData()

  // Tab functionality
  const tabButtons = document.querySelectorAll(".tab-btn")
  const tabPanels = document.querySelectorAll(".tab-panel")

  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const targetTab = button.getAttribute("data-tab")

      // Remove active class from all tabs and panels
      tabButtons.forEach((btn) => btn.classList.remove("active"))
      tabPanels.forEach((panel) => panel.classList.remove("active"))

      // Add active class to clicked tab and corresponding panel
      button.classList.add("active")
      document.getElementById(targetTab).classList.add("active")
    })
  })

  const wishlistBtn = document.getElementById("wishlist-btn")
  if (wishlistBtn) {
    let isWishlisted = false
    wishlistBtn.addEventListener("click", () => {
      isWishlisted = !isWishlisted
      const icon = wishlistBtn.querySelector("i")
      const text = wishlistBtn.querySelector("span")

      if (isWishlisted) {
        icon.className = "fas fa-heart"
        text.textContent = "Added to Wishlist"
        wishlistBtn.style.background = "#ef4444"
      } else {
        icon.className = "far fa-heart"
        text.textContent = "Add to Wishlist"
        wishlistBtn.style.background = ""
      }
    })
  }
})

// Image gallery functionality
function initializeImageGallery(images) {
  const mainImage = document.getElementById("main-image")
  const thumbnailList = document.getElementById("thumbnail-list")
  const prevBtn = document.getElementById("thumb-prev")
  const nextBtn = document.getElementById("thumb-next")

  let currentImageIndex = 0

  // Set main image
  mainImage.src = images[0]

  // Create thumbnails
  thumbnailList.innerHTML = ""
  images.forEach((image, index) => {
    const thumbnail = document.createElement("div")
    thumbnail.className = `thumbnail-item ${index === 0 ? "active" : ""}`
    thumbnail.innerHTML = `<img src="${image}" alt="Product thumbnail ${index + 1}">`

    thumbnail.addEventListener("click", () => {
      currentImageIndex = index
      updateMainImage()
      updateThumbnails()
    })

    thumbnailList.appendChild(thumbnail)
  })

  // Navigation buttons
  if (prevBtn) {
    prevBtn.addEventListener("click", () => {
      currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : images.length - 1
      updateMainImage()
      updateThumbnails()
    })
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", () => {
      currentImageIndex = currentImageIndex < images.length - 1 ? currentImageIndex + 1 : 0
      updateMainImage()
      updateThumbnails()
    })
  }

  function updateMainImage() {
    mainImage.src = images[currentImageIndex]
  }

  function updateThumbnails() {
    const thumbnails = thumbnailList.querySelectorAll(".thumbnail-item")
    thumbnails.forEach((thumb, index) => {
      thumb.classList.toggle("active", index === currentImageIndex)
    })
  }

  // Image zoom functionality
  const zoomOverlay = document.querySelector(".image-zoom-overlay")
  if (zoomOverlay) {
    zoomOverlay.addEventListener("click", () => {
      // Simple zoom implementation - could be enhanced with a modal
      window.open(images[currentImageIndex], "_blank")
    })
  }
}

// Function to create installation guide with video
function createInstallationGuide(videoUrl) {
  const installationPanel = document.getElementById("installation")
  if (!installationPanel) return

  let html = ""

  // Add YouTube video if available
  if (videoUrl && videoUrl.trim()) {
    const videoId = extractYouTubeId(videoUrl)
    if (videoId) {
      html += `
        <div style="margin-bottom: 2rem;">
          <h4 style="margin-bottom: 1rem;">Installation Video Guide</h4>
          <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; border-radius: 0.5rem; overflow: hidden;">
            <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;" 
                    src="https://www.youtube.com/embed/${videoId}" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
            </iframe>
          </div>
        </div>
      `
    }
  }

  // Add installation steps
  html += `
    <div class="installation-content">
      <div class="install-step">
        <div class="step-number">1</div>
        <div class="step-content">
          <h4>Template Positioning</h4>
          <p>Use the provided template to mark mounting holes on door and frame. Ensure proper clearance for arm movement and door swing.</p>
        </div>
      </div>
      <div class="install-step">
        <div class="step-number">2</div>
        <div class="step-content">
          <h4>Mounting Installation</h4>
          <p>Secure the closer body to the door using provided screws. Install the bracket on the frame, ensuring proper alignment with the closer arm.</p>
        </div>
      </div>
      <div class="install-step">
        <div class="step-number">3</div>
        <div class="step-content">
          <h4>Adjustment & Testing</h4>
          <p>Connect the arm assembly and adjust closing force using the adjustment valve. Test door operation and fine-tune settings as needed.</p>
        </div>
      </div>
    </div>
  `

  installationPanel.innerHTML = html
}

// Function to extract YouTube video ID from various URL formats
function extractYouTubeId(url) {
  const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/
  const match = url.match(regExp)
  return match && match[7].length == 11 ? match[7] : null
}

// Function to load and display color variants for hardware
async function loadColorVariants(productTitle, productId) {
  try {
    console.log("[v0] Loading color variants for:", productTitle, "ID:", productId)
    const response = await fetch(`api/get-color-variants.php?product_name=${encodeURIComponent(productTitle)}&product_id=${encodeURIComponent(productId)}`)
    const data = await response.json()

    console.log("[v0] Color variants response:", data)

    if (data.success && data.variants && data.variants.length > 0) {
      displayColorVariants(data.variants)
      displayColorVariantsQuick(data.variants)
    } else {
      console.log("[v0] No color variants returned")
    }
  } catch (error) {
    console.error("[v0] Error loading color variants:", error)
  }
}

// Function to display color variants
function displayColorVariants(variants) {
  const container = document.getElementById("color-variants-container")
  if (!container) return

  container.innerHTML = ""

  variants.forEach((variant) => {
    const variantDiv = document.createElement("div")
    variantDiv.className = "color-variant-card"
    variantDiv.style.cssText = `
      padding: 1rem;
      border: 2px solid #e5e7eb;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
    `

    const colorSwatch = document.createElement("div")
    colorSwatch.style.cssText = `
      width: 60px;
      height: 60px;
      background-color: ${variant.color_hex || variant.color_code || "#000000"};
      border-radius: 0.5rem;
      margin: 0 auto 1rem;
      border: 1px solid #d1d5db;
    `

    const name = document.createElement("h4")
    name.textContent = variant.color_name
    name.style.cssText = "margin-bottom: 0.5rem;"

    const images = typeof variant.images === "string" ? JSON.parse(variant.images || "[]") : variant.images || []
    const preview = document.createElement("div")
    preview.style.cssText = `
      width: 100%;
      height: 100px;
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      border-radius: 0.25rem;
      margin-bottom: 0.5rem;
      border: 1px solid #e5e7eb;
    `

    if (images.length > 0) {
      preview.style.backgroundImage = `url('${toAbsoluteUrlWithBase(images[0])}')`
    }

    variantDiv.appendChild(colorSwatch)
    variantDiv.appendChild(name)
    variantDiv.appendChild(preview)

    variantDiv.addEventListener("click", () => {
      const mainImage = document.getElementById("main-image")
      const thumbnailList = document.getElementById("thumbnail-list")

      if (mainImage && thumbnailList && images.length > 0) {
        mainImage.src = toAbsoluteUrlWithBase(images[0])

        thumbnailList.innerHTML = ""
        images.forEach((src, index) => {
          const thumbnail = document.createElement("div")
          thumbnail.className = `thumbnail-item ${index === 0 ? "active" : ""}`
          thumbnail.innerHTML = `<img src="${toAbsoluteUrlWithBase(src)}" alt="Color variant image ${index + 1}">`
          thumbnail.addEventListener("click", () => {
            mainImage.src = toAbsoluteUrlWithBase(src)
            document.querySelectorAll(".thumbnail-item").forEach((t) => t.classList.remove("active"))
            thumbnail.classList.add("active")
          })
          thumbnailList.appendChild(thumbnail)
        })
      }

      variantDiv.parentElement.querySelectorAll(".color-variant-card").forEach((card) => {
        card.style.borderColor = "#e5e7eb"
      })
      variantDiv.style.borderColor = "#3b82f6"
    })

    container.appendChild(variantDiv)
  })
}

function displayColorVariantsQuick(variants) {
  const container = document.getElementById("color-variants-quick")
  if (!container) {
    console.error("[v0] Color variants container not found")
    return
  }

  console.log("[v0] Displaying color variants:", variants)

  container.innerHTML = ""

  variants.forEach((variant, index) => {
    const variantItem = document.createElement("div")
    variantItem.className = `color-variant-item ${index === 0 ? "active" : ""}`

    const colorSwatch = document.createElement("div")
    colorSwatch.className = "color-swatch"
    colorSwatch.style.backgroundColor = variant.color_hex || variant.color_code || "#000000"
    colorSwatch.title = variant.color_name

    const colorName = document.createElement("div")
    colorName.className = "color-name"
    colorName.textContent = variant.color_name

    variantItem.appendChild(colorSwatch)
    variantItem.appendChild(colorName)

    variantItem.addEventListener("click", () => {
      container.querySelectorAll(".color-variant-item").forEach((item) => item.classList.remove("active"))
      variantItem.classList.add("active")

      const images = typeof variant.images === "string" ? JSON.parse(variant.images || "[]") : variant.images || []
      console.log("[v0] Selected variant images:", images)

      const mainImage = document.getElementById("main-image")
      const thumbnailList = document.getElementById("thumbnail-list")

      if (mainImage && thumbnailList && images.length > 0) {
        mainImage.src = toAbsoluteUrlWithBase(images[0])

        thumbnailList.innerHTML = ""
        images.forEach((src, imgIndex) => {
          const thumbnail = document.createElement("div")
          thumbnail.className = `thumbnail-item ${imgIndex === 0 ? "active" : ""}`
          thumbnail.innerHTML = `<img src="${toAbsoluteUrlWithBase(src)}" alt="Color variant image ${imgIndex + 1}">`
          thumbnail.addEventListener("click", () => {
            mainImage.src = toAbsoluteUrlWithBase(src)
            document.querySelectorAll(".thumbnail-item").forEach((t) => t.classList.remove("active"))
            thumbnail.classList.add("active")
          })
          thumbnailList.appendChild(thumbnail)
        })
      }
    })

    container.appendChild(variantItem)
  })

  if (variants.length > 0) {
    setTimeout(() => {
      const firstVariant = container.querySelector(".color-variant-item.active")
      if (firstVariant) {
        firstVariant.click()
      }
    }, 100)
  }
}
