class ProductDataLoader {
  constructor() {
    this.cache = new Map()
  }

  async fetchProducts(type = "digital") {
    const cacheKey = `products_${type}`

    if (this.cache.has(cacheKey)) {
      return this.cache.get(cacheKey)
    }

    try {
      // [v0] Added debug logging for API calls
      console.log(`[v0] Fetching products from API: api/get-products.php?type=${type}`)

      const [productsResponse, variantsResponse] = await Promise.all([
        fetch(`api/get-products.php?type=${type}`),
        fetch('api/get-color-variants.php')
      ]);

      if (!productsResponse.ok) {
        console.error(`[v0] API response not ok: ${productsResponse.status} ${productsResponse.statusText}`)
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

      // Merge variant images
      products.forEach(product => {
        // Find variants for this product (loose match on title)
        const productVariants = variants.filter(v =>
          v.product_name.toLowerCase().trim() === product.title.toLowerCase().trim()
        );

        if (productVariants.length > 0) {
          // Use the first variant's first image as the main image
          const firstVariant = productVariants[0];
          if (firstVariant.images && firstVariant.images.length > 0) {
            // Override the main image path
            product.image = firstVariant.images[0];

            // Update images array structure to match what formatProductForDisplay expects
            // The loader expects product.images to be array of objects with .path
            // But we can also just let formatProductForDisplay handle the main image override
            // Let's just override product.images with the variant images formatted correctly
            product.images = firstVariant.images.map(path => ({
              path: path,
              alt: product.title + ' ' + firstVariant.color_name,
              sort_order: 0
            }));
          }
        }
      });

      console.log(`[v0] Received ${products.length} products from API`)
      this.cache.set(cacheKey, products)
      return products
    } catch (error) {
      console.error("[v0] Error fetching products:", error)
      return []
    }
  }

  async fetchProduct(id, type = "digital") {
    const cacheKey = `product_${type}_${id}`

    if (this.cache.has(cacheKey)) {
      return this.cache.get(cacheKey)
    }

    try {
      const response = await fetch(`api/get-products.php?type=${type}&id=${id}`)
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const product = await response.json()
      this.cache.set(cacheKey, product)
      return product
    } catch (error) {
      console.error("Error fetching product:", error)
      return null
    }
  }

  formatProductForDisplay(product) {
    if (!product) return null

    let images = []

    // First, check if product has images from the API (from product_images.csv)
    if (product.images && Array.isArray(product.images) && product.images.length > 0) {
      images = product.images.map((img) => img.path)
    }
    // Fallback to main product image if no additional images
    else if (product.image && product.image.trim()) {
      images = [product.image.trim()]
    }

    // Add fallback placeholder images if no images provided
    if (images.length === 0) {
      images = [
        `/placeholder.svg?height=500&width=500&query=${encodeURIComponent(product.title + " smart lock")}`,
        `/placeholder.svg?height=500&width=500&query=${encodeURIComponent(product.title + " installation")}`,
        `/placeholder.svg?height=500&width=500&query=${encodeURIComponent(product.title + " app interface")}`,
        `/placeholder.svg?height=500&width=500&query=${encodeURIComponent(product.title + " mechanism")}`,
        `/placeholder.svg?height=500&width=500&query=${encodeURIComponent(product.title + " packaging")}`,
      ]
    }

    return {
      id: product.id,
      title: product.title,
      price: product.price,
      originalPrice: product.original_price,
      icon: product.icon,
      badge: product.badge,
      tagline: product.tagline,
      features: product.features || [],
      detailedFeatures: product.detailed_features || [],
      specs: product.specs || {},
      categories: product.categories || [],
      images: images,
      installationGuideVideo: product.installation_guide_video || "",
    }
  }
}

// Create global instance
window.productDataLoader = new ProductDataLoader()
