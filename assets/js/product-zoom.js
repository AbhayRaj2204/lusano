class ImageZoom {
  constructor() {
    this.modal = null
    this.modalImage = null
    this.zoomLevel = 1
    this.minZoom = 1
    this.maxZoom = 5
    this.isDragging = false
    this.dragStart = { x: 0, y: 0 }
    this.imageOffset = { x: 0, y: 0 }
    this.touchDistance = 0
    this.lastTouchDistance = 0
    this.init()
  }

  init() {
    this.createZoomModal()
    this.attachEventListeners()
  }

  createZoomModal() {
    const modalHTML = `
      <div class="zoom-modal" id="zoom-modal">
        <button class="zoom-modal-close" id="zoom-close" aria-label="Close zoom">
          <i class="fas fa-times"></i>
        </button>
        <div class="zoom-modal-content">
          <img id="zoom-modal-image" class="zoom-modal-image" src="/placeholder.svg" alt="Zoomed product image">
        </div>
        <div class="zoom-controls">
          <button class="zoom-btn" id="zoom-out" title="Zoom out (-)">
            <i class="fas fa-minus"></i>
          </button>
          <div class="zoom-level" id="zoom-level">100%</div>
          <button class="zoom-btn" id="zoom-in" title="Zoom in (+)">
            <i class="fas fa-plus"></i>
          </button>
          <button class="zoom-btn" id="zoom-reset" title="Reset zoom">
            <i class="fas fa-redo"></i>
          </button>
        </div>
      </div>
    `

    document.body.insertAdjacentHTML("beforeend", modalHTML)

    this.modal = document.getElementById("zoom-modal")
    this.modalImage = document.getElementById("zoom-modal-image")
  }

  attachEventListeners() {
    const mainImages = document.querySelectorAll("#main-image")

    mainImages.forEach((img) => {
      img.addEventListener("click", () => this.openZoom(img.src))
      img.style.cursor = "zoom-in"
    })

    // Close button
    const closeBtn = document.getElementById("zoom-close")
    if (closeBtn) {
      closeBtn.addEventListener("click", () => this.closeZoom())
    }

    // Close on background click
    if (this.modal) {
      this.modal.addEventListener("click", (e) => {
        if (e.target === this.modal) {
          this.closeZoom()
        }
      })
    }

    // Zoom control buttons
    const zoomInBtn = document.getElementById("zoom-in")
    const zoomOutBtn = document.getElementById("zoom-out")
    const zoomResetBtn = document.getElementById("zoom-reset")

    if (zoomInBtn) {
      zoomInBtn.addEventListener("click", () => this.zoomIn())
    }
    if (zoomOutBtn) {
      zoomOutBtn.addEventListener("click", () => this.zoomOut())
    }
    if (zoomResetBtn) {
      zoomResetBtn.addEventListener("click", () => this.resetZoom())
    }

    // Keyboard controls
    document.addEventListener("keydown", (e) => {
      if (!this.modal || !this.modal.classList.contains("active")) return

      if (e.key === "Escape") this.closeZoom()
      if (e.key === "+" || e.key === "=") this.zoomIn()
      if (e.key === "-") this.zoomOut()
      if (e.key === "0") this.resetZoom()
    })

    if (this.modalImage) {
      this.modalImage.addEventListener(
        "wheel",
        (e) => {
          if (!this.modal.classList.contains("active")) return
          e.preventDefault()

          if (e.deltaY < 0) {
            this.zoomIn()
          } else {
            this.zoomOut()
          }
        },
        { passive: false },
      )

      this.modalImage.addEventListener("mousedown", (e) => this.startDrag(e))
      document.addEventListener("mousemove", (e) => this.drag(e))
      document.addEventListener("mouseup", () => this.endDrag())

      this.modalImage.addEventListener("touchstart", (e) => this.handleTouchStart(e), { passive: false })
      this.modalImage.addEventListener("touchmove", (e) => this.handleTouchMove(e), { passive: false })
      this.modalImage.addEventListener("touchend", () => this.handleTouchEnd())
    }
  }

  openZoom(imageSrc) {
    if (!imageSrc || imageSrc.includes("placeholder")) {
      console.log("[v0] No valid image source")
      return
    }

    this.modalImage.src = imageSrc
    this.modal.classList.add("active")
    this.resetZoom()
    document.body.style.overflow = "hidden"
  }

  closeZoom() {
    this.modal.classList.remove("active")
    document.body.style.overflow = "auto"
    this.resetZoom()
  }

  zoomIn() {
    this.zoomLevel = Math.min(this.zoomLevel + 0.3, this.maxZoom)
    this.updateZoom()
  }

  zoomOut() {
    this.zoomLevel = Math.max(this.zoomLevel - 0.3, this.minZoom)
    if (this.zoomLevel === this.minZoom) {
      this.imageOffset = { x: 0, y: 0 }
    }
    this.updateZoom()
  }

  resetZoom() {
    this.zoomLevel = 1
    this.imageOffset = { x: 0, y: 0 }
    this.updateZoom()
  }

  updateZoom() {
    const scale = this.zoomLevel
    const translateX = this.imageOffset.x
    const translateY = this.imageOffset.y

    this.modalImage.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`

    // Update zoom level display
    const zoomLevelDisplay = document.getElementById("zoom-level")
    if (zoomLevelDisplay) {
      zoomLevelDisplay.textContent = `${Math.round(this.zoomLevel * 100)}%`
    }
  }

  startDrag(e) {
    if (this.zoomLevel <= 1 || !this.modal.classList.contains("active")) return
    this.isDragging = true
    this.dragStart = { x: e.clientX, y: e.clientY }
    this.modalImage.style.cursor = "grabbing"
  }

  drag(e) {
    if (!this.isDragging || this.zoomLevel <= 1) return

    const deltaX = (e.clientX - this.dragStart.x) / this.zoomLevel
    const deltaY = (e.clientY - this.dragStart.y) / this.zoomLevel

    this.imageOffset.x += deltaX
    this.imageOffset.y += deltaY

    this.dragStart = { x: e.clientX, y: e.clientY }
    this.updateZoom()
  }

  endDrag() {
    this.isDragging = false
    if (this.modalImage) {
      this.modalImage.style.cursor = "grab"
    }
  }

  handleTouchStart(e) {
    if (!this.modal.classList.contains("active")) return

    if (e.touches.length === 2) {
      const touch1 = e.touches[0]
      const touch2 = e.touches[1]
      this.touchDistance = Math.hypot(touch2.clientX - touch1.clientX, touch2.clientY - touch1.clientY)
      this.lastTouchDistance = this.touchDistance
    } else if (e.touches.length === 1) {
      this.dragStart = { x: e.touches[0].clientX, y: e.touches[0].clientY }
    }
  }

  handleTouchMove(e) {
    if (!this.modal.classList.contains("active")) return

    if (e.touches.length === 2) {
      e.preventDefault()
      const touch1 = e.touches[0]
      const touch2 = e.touches[1]
      const newDistance = Math.hypot(touch2.clientX - touch1.clientX, touch2.clientY - touch1.clientY)

      if (this.lastTouchDistance > 0) {
        const scale = newDistance / this.lastTouchDistance
        this.zoomLevel = Math.min(Math.max(this.zoomLevel * scale, this.minZoom), this.maxZoom)
        this.updateZoom()
      }

      this.lastTouchDistance = newDistance
    } else if (e.touches.length === 1 && this.zoomLevel > 1) {
      e.preventDefault()
      const deltaX = (e.touches[0].clientX - this.dragStart.x) / this.zoomLevel
      const deltaY = (e.touches[0].clientY - this.dragStart.y) / this.zoomLevel

      this.imageOffset.x += deltaX
      this.imageOffset.y += deltaY

      this.dragStart = { x: e.touches[0].clientX, y: e.touches[0].clientY }
      this.updateZoom()
    }
  }

  handleTouchEnd() {
    this.touchDistance = 0
    this.lastTouchDistance = 0
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new ImageZoom()
})
