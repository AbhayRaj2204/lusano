/**
 * Comprehensive Lazy Loading System
 * Loads critical content first, then defers images and videos
 */

class LazyLoadingManager {
  constructor() {
    this.imageQueue = []
    this.videoQueue = []
    this.isLoading = false
    this.loadedCount = 0
    this.totalCount = 0
    this.init()
  }

  init() {
    // Wait for DOM to be ready
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () => this.start())
    } else {
      this.start()
    }
  }

  start() {
    this.collectMediaElements()
    this.setupIntersectionObserver()
    this.startProgressiveLoading()
  }

  collectMediaElements() {
    // Collect images with data-src attribute
    document.querySelectorAll("img[data-src]").forEach((img) => {
      this.imageQueue.push(img)
    })

    // Collect video elements
    document.querySelectorAll("video[data-src]").forEach((video) => {
      this.videoQueue.push(video)
    })

    // Collect carousel videos
    document.querySelectorAll(".carousel-video[data-src]").forEach((video) => {
      this.videoQueue.push(video)
    })

    this.totalCount = this.imageQueue.length + this.videoQueue.length
  }

  setupIntersectionObserver() {
    const observerOptions = {
      root: null,
      rootMargin: "50px",
      threshold: 0.01,
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          this.loadMedia(entry.target)
          observer.unobserve(entry.target)
        }
      })
    }, observerOptions)

    // Observe all media elements
    this.imageQueue.forEach((img) => observer.observe(img))
    this.videoQueue.forEach((video) => observer.observe(video))
  }

  startProgressiveLoading() {
    const criticalImages = this.imageQueue.filter((img) => {
      const rect = img.getBoundingClientRect()
      return rect.top < window.innerHeight
    })

    // Load critical images immediately
    criticalImages.forEach((img) => this.loadMedia(img))

    // Load remaining images progressively
    window.addEventListener("load", () => {
      this.loadRemainingMedia()
    })
  }

  loadMedia(element) {
    if (element.tagName === "IMG") {
      this.loadImage(element)
    } else if (element.tagName === "VIDEO") {
      this.loadVideo(element)
    }
  }

  loadImage(img) {
    if (!img.dataset.src || img.src) return

    const src = img.dataset.src
    const tempImg = new Image()

    tempImg.onload = () => {
      img.src = src
      img.classList.remove("lazy")
      img.classList.add("loaded")
      this.loadedCount++
      this.updateProgress()
    }

    tempImg.onerror = () => {
      img.classList.add("error")
      this.loadedCount++
      this.updateProgress()
    }

    tempImg.src = src
  }

  loadVideo(video) {
    if (!video.dataset.src) return

    const src = video.dataset.src
    const source = document.createElement("source")
    source.src = src
    source.type = "video/mp4"

    video.appendChild(source)
    video.load()
    this.loadedCount++
    this.updateProgress()
  }

  loadRemainingMedia() {
    const remainingImages = this.imageQueue.filter((img) => !img.src)
    const batchSize = 5
    let index = 0

    const loadBatch = () => {
      for (let i = 0; i < batchSize && index < remainingImages.length; i++) {
        this.loadImage(remainingImages[index])
        index++
      }

      if (index < remainingImages.length) {
        requestIdleCallback(loadBatch, { timeout: 2000 })
      }
    }

    if (remainingImages.length > 0) {
      loadBatch()
    }
  }

  updateProgress() {
    const progress = (this.loadedCount / this.totalCount) * 100
    const loader = document.getElementById("page-loader")
    if (loader) {
      const level = loader.querySelector(".water-level")
      if (level) {
        level.style.height = Math.min(progress, 90) + "%"
      }
    }
  }
}

// Initialize lazy loading manager
const lazyManager = new LazyLoadingManager()
