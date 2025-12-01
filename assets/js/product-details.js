function currency(v) {
  return `₹${Number(v).toLocaleString('en-IN')}`;
}


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
  // Title
  document.title = `${title} – LUSANO`

  // Description
  ensureMeta('meta[name="description"]', "content", description, "meta", { name: "description" })

  // Open Graph
  ensureMeta('meta[property="og:title"]', "content", `${title} – LUSANO`, "meta", { property: "og:title" })
  ensureMeta('meta[property="og:description"]', "content", description, "meta", { property: "og:description" })
  ensureMeta('meta[property="og:image"]', "content", image, "meta", { property: "og:image" })
  ensureMeta('meta[property="og:url"]', "content", url, "meta", { property: "og:url" })

  // Canonical
  ensureMeta('link[rel="canonical"]', "href", url, "link", { rel: "canonical" })
}

function createImageGallery(images) {
  const mainImage = document.getElementById("main-image")
  const thumbnailList = document.getElementById("thumbnail-list")

  // Set main image
  mainImage.src = images[0]

  // Create thumbnails
  thumbnailList.innerHTML = ""
  images.forEach((src, index) => {
    const thumbnail = document.createElement("div")
    thumbnail.className = `thumbnail ${index === 0 ? "active" : ""}`
    thumbnail.innerHTML = `<img src="${src}" alt="Product image ${index + 1}">`
    thumbnail.addEventListener("click", () => {
      mainImage.src = src
      document.querySelectorAll(".thumbnail").forEach((t) => t.classList.remove("active"))
      thumbnail.classList.add("active")
    })
    thumbnailList.appendChild(thumbnail)
  })
}

function createDetailedFeatures(features) {
  const container = document.getElementById("detailed-features")
  container.innerHTML = ""

  features.forEach((feature) => {
    const featureDiv = document.createElement("div")
    featureDiv.className = "feature-detail"
    featureDiv.innerHTML = `
      <h4><i class="fas ${feature.icon}"></i>${feature.title}</h4>
      <p>${feature.description}</p>
    `
    container.appendChild(featureDiv)
  })
}

function initializeTabs() {
  const tabBtns = document.querySelectorAll(".tab-btn")
  const tabPanels = document.querySelectorAll(".tab-panel")

  tabBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetTab = btn.dataset.tab

      // Remove active class from all buttons and panels
      tabBtns.forEach((b) => b.classList.remove("active"))
      tabPanels.forEach((p) => p.classList.remove("active"))

      // Add active class to clicked button and corresponding panel
      btn.classList.add("active")
      document.getElementById(targetTab).classList.add("active")
    })
  })
}

function getBasePath() {
  // e.g. "/mythic/" if the app is mounted under a subpath
  return window.location.pathname.replace(/[^/]+$/, "")
}

function toAbsoluteUrlWithBase(path) {
  if (!path) return path
  if (/^https?:\/\//i.test(path)) return path
  const origin = window.location.origin
  const base = getBasePath()
  if (path.startsWith("/")) {
    // Keep app base (e.g., "/mythic" + "/admin/uploads/...")
    return origin + base.replace(/\/$/, "") + path
  }
  return origin + base + path
}

function createInstallationGuide(videoUrl) {
  const container = document.getElementById("installation-content-wrapper")
  if (!container) {
    console.error("[v0] Installation content wrapper not found")
    return
  }

  let html = ""

  if (videoUrl && videoUrl.trim()) {
    const videoId = extractYouTubeId(videoUrl)
    if (videoId) {
      html += `
        <div style="margin-bottom: 2rem;">
          <h4 style="margin-bottom: 1rem;">Installation Video</h4>
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
          <h4>Preparation</h4>
          <p>Remove existing lock and clean the door surface. Ensure door thickness is compatible.</p>
        </div>
      </div>
      <div class="install-step">
        <div class="step-number">2</div>
        <div class="step-content">
          <h4>Hardware Installation</h4>
          <p>Install the mounting plate and secure with provided screws. Connect the cable harness.</p>
        </div>
      </div>
      <div class="install-step">
        <div class="step-number">3</div>
        <div class="step-content">
          <h4>Setup & Configuration</h4>
          <p>Download the LUSANO app, create your account, and follow the setup wizard.</p>
        </div>
      </div>
    </div>
  `

  container.innerHTML = html
}

function extractYouTubeId(url) {
  const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/
  const match = url.match(regExp)
  return match && match[7].length == 11 ? match[7] : null
}

async function loadColorVariants(productName, productId) {
  try {
    console.log("[v0] Loading color variants for:", productName, "ID:", productId)
    const response = await fetch(`api/get-color-variants.php?product_name=${encodeURIComponent(productName)}&product_id=${encodeURIComponent(productId)}`)
    const data = await response.json()

    console.log("[v0] Full API response:", data)
    console.log("[v0] Variants:", data.variants)

    if (data.success && data.variants && data.variants.length > 0) {
      console.log("[v0] Found", data.variants.length, "color variants")
      displayColorVariants(data.variants)
      displayColorVariantsQuick(data.variants)
    } else {
      console.log("[v0] No color variants returned or API error")
    }
  } catch (error) {
    console.error("[v0] Error loading color variants:", error)
  }
}

function displayColorVariantsQuick(variants) {
  const container = document.getElementById("color-variants-quick")
  if (!container) {
    console.error("[v0] Color variants container not found")
    return
  }

  console.log("[v0] Displaying", variants.length, "color variants in quick section")

  container.innerHTML = ""

  variants.forEach((variant, index) => {
    console.log("[v0] Processing variant:", index, variant)

    const variantItem = document.createElement("div")
    variantItem.className = `color-variant-item ${index === 0 ? "active" : ""}`

    const colorSwatch = document.createElement("div")
    colorSwatch.className = "color-swatch"
    const colorValue = variant.color_hex || variant.color_code || "#000000"
    colorSwatch.style.backgroundColor = colorValue
    colorSwatch.title = variant.color_name
    console.log("[v0] Color swatch for", variant.color_name, ":", colorValue)

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
          thumbnail.className = `thumbnail ${imgIndex === 0 ? "active" : ""}`
          thumbnail.innerHTML = `<img src="${toAbsoluteUrlWithBase(src)}" alt="Color variant image ${imgIndex + 1}">`
          thumbnail.addEventListener("click", () => {
            mainImage.src = toAbsoluteUrlWithBase(src)
            document.querySelectorAll(".thumbnail").forEach((t) => t.classList.remove("active"))
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
        console.log("[v0] Auto-clicking first color variant")
        firstVariant.click()
      }
    }, 100)
  }
}

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
          thumbnail.className = `thumbnail ${index === 0 ? "active" : ""}`
          thumbnail.innerHTML = `<img src="${toAbsoluteUrlWithBase(src)}" alt="Color variant image ${index + 1}">`
          thumbnail.addEventListener("click", () => {
            mainImage.src = toAbsoluteUrlWithBase(src)
            document.querySelectorAll(".thumbnail").forEach((t) => t.classList.remove("active"))
            thumbnail.classList.add("active")
          })
          thumbnailList.appendChild(thumbnail)
        })
      }

      variantDiv.parentElement.querySelectorAll(".color-variant-card").forEach((card) => {
        card.style.borderColor = "#e5e7eb"
      })
      variantDiv.style.borderColor = "var(--admin-primary)"
    })

    container.appendChild(variantDiv)
  })
}

async function render() {
  const params = new URLSearchParams(window.location.search)
  const id = (params.get("id") || "s3-slim").toLowerCase()

  try {
    const productData = await window.productDataLoader.fetchProduct(id, "digital")

    if (!productData) {
      console.error("Product not found:", id)
      return
    }

    const data = window.productDataLoader.formatProductForDisplay(productData)

    // Fill hero section
    document.getElementById("pd-badge").textContent = data.badge
    document.getElementById("pd-title").textContent = data.title
    document.getElementById("pd-price").textContent = currency(data.price)
    document.getElementById("original-price").textContent = currency(data.originalPrice)
    document.getElementById("pd-tagline").textContent = data.tagline

    const iconEl = document.getElementById("pd-icon")
    if (iconEl) iconEl.className = `fas ${data.icon}`

    // Create image gallery
    createImageGallery(data.images)

    const quote = document.getElementById("pd-quote")
    const pageUrl = window.location.href
    const mainImageUrl = toAbsoluteUrlWithBase(data.images[0] || "/diverse-products-still-life.png")

    // Build share link with title/desc/img so PHP can render OG even for digital products
    const basePath = getBasePath() // e.g. /mythic/
    const shareUrl = `${window.location.origin}${basePath}share/?type=digital&id=${encodeURIComponent(data.id || id)}`

    // Organized WhatsApp message: title in bold + clean link, and updated number 9709226079
    const waText = `Product enquiry:\n*${data.title}*\n${shareUrl}`
    if (quote) quote.href = `https://wa.me/9709226079?text=${encodeURIComponent(waText)}`

    // Keep meta url pointing to the clean share link
    setProductMeta({
      title: data.title,
      description: data.tagline || "Explore product details, features, and specifications.",
      image: mainImageUrl,
      url: shareUrl,
    })

    // Features
    const ul = document.getElementById("pd-features")
    ul.innerHTML = ""
    data.features.forEach((f) => {
      const li = document.createElement("li")
      li.innerHTML = `<i class="fas fa-check-circle"></i>${f}`
      ul.appendChild(li)
    })

    // Detailed features
    createDetailedFeatures(data.detailedFeatures)

    // Specs
    const specsWrap = document.getElementById("pd-specs")
    specsWrap.innerHTML = ""
    Object.entries(data.specs).forEach(([k, v]) => {
      const row = document.createElement("div")
      row.className = "spec-row"
      row.innerHTML = `<div class="spec-k">${k}</div><div class="spec-v">${v}</div>`
      specsWrap.appendChild(row)
    })

    // Initialize tabs
    initializeTabs()

    createInstallationGuide(data.installationGuideVideo)

    loadColorVariants(data.title, data.id)

    const wishlistBtn = document.getElementById("wishlist-btn")
    if (wishlistBtn) {
      wishlistBtn.addEventListener("click", () => {
        const icon = wishlistBtn.querySelector("i")
        const span = wishlistBtn.querySelector("span")

        if (icon.classList.contains("far")) {
          icon.classList.remove("far")
          icon.classList.add("fas")
          span.textContent = "Added to Wishlist"
          wishlistBtn.style.background = "#28a745"
        } else {
          icon.classList.remove("fas")
          icon.classList.add("far")
          span.textContent = "Add to Wishlist"
          wishlistBtn.style.background = "transparent"
        }
      })
    }
  } catch (error) {
    console.error("Error loading product data:", error)
  }
}

document.addEventListener("DOMContentLoaded", render)
