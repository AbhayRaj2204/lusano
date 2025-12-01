// DOM Elements
const navbar = document.getElementById("navbar")
const navToggle = document.getElementById("nav-toggle")
const navMenu = document.getElementById("nav-menu")
const navLinks = document.querySelectorAll(".nav-link")

// Mobile Navigation Toggle
navToggle.addEventListener("click", () => {
  navMenu.classList.toggle("active")
  navToggle.classList.toggle("active")
})

// Close mobile menu when clicking on a link
navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    navMenu.classList.remove("active")
    navToggle.classList.remove("active")
  })
})

// Navbar Scroll Effect
window.addEventListener("scroll", () => {
  if (window.scrollY > 100) {
    navbar.classList.add("scrolled")
  } else {
    navbar.classList.remove("scrolled")
  }
})

// Active Navigation Link
function setActiveNavLink() {
  const currentPath = (window.location.pathname.split("/").pop() || "index.html").toLowerCase()

  navLinks.forEach((link) => {
    link.classList.remove("active")
  })

  // If on index.html, use section-based active if possible
  if (currentPath === "" || currentPath === "index.html") {
    let current = "home"
    const sections = document.querySelectorAll("section")
    sections.forEach((section) => {
      const sectionTop = section.offsetTop
      if (scrollY >= sectionTop - 200) {
        current = section.getAttribute("id") || current
      }
    })

    // Try to match a # link first (legacy)
    let matched = false
    navLinks.forEach((link) => {
      const href = link.getAttribute("href") || ""
      if (href.startsWith("#") && href === `#${current}`) {
        link.classList.add("active")
        matched = true
      }
    })

    // Fallback to Home link when using page links
    if (!matched) {
      navLinks.forEach((link) => {
        const href = (link.getAttribute("href") || "").toLowerCase()
        if (href === "index.html") {
          link.classList.add("active")
        }
      })
    }
  } else {
    // On other pages, highlight by pathname
    navLinks.forEach((link) => {
      const href = (link.getAttribute("href") || "").toLowerCase()
      if (!href.startsWith("#") && href === currentPath) {
        link.classList.add("active")
      }
    })
  }
}

window.addEventListener("scroll", setActiveNavLink)
document.addEventListener("DOMContentLoaded", setActiveNavLink)

// Smooth Scrolling for Navigation Links
navLinks.forEach((link) => {
  link.addEventListener("click", (e) => {
    const href = link.getAttribute("href") || ""
    if (href.startsWith("#")) {
      e.preventDefault()
      const targetSection = document.querySelector(href)
      if (targetSection) {
        const offsetTop = targetSection.offsetTop - 80
        window.scrollTo({
          top: offsetTop,
          behavior: "smooth",
        })
      }
    } else {
      // let default navigation happen for non-hash links
    }
  })
})

// Intersection Observer for Animations
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px",
}

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add("aos-animate")
    }
  })
}, observerOptions)

// Observe elements with animation attributes
document.addEventListener("DOMContentLoaded", () => {
  const animatedElements = document.querySelectorAll("[data-aos]")
  animatedElements.forEach((el) => observer.observe(el))
})

// Counter Animation
function animateCounter(element, start, end, duration) {
  let startTimestamp = null
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp
    const progress = Math.min((timestamp - startTimestamp) / duration, 1)
    const current = Math.floor(progress * (end - start) + start)
    element.textContent = current.toLocaleString()
    if (progress < 1) {
      window.requestAnimationFrame(step)
    }
  }
  window.requestAnimationFrame(step)
}

// Animate counters when they come into view
const counterObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const counter = entry.target.querySelector(".stat-number")
        const finalNumber = Number.parseInt(counter.getAttribute("data-count"))
        animateCounter(counter, 0, finalNumber, 2000)
        counterObserver.unobserve(entry.target)
      }
    })
  },
  { threshold: 0.7 },
)

document.querySelectorAll(".stat-item").forEach((item) => {
  counterObserver.observe(item)
})

// Particle Animation
class ParticleSystem {
  constructor(container) {
    this.container = container
    this.particles = []
    this.init()
  }

  init() {
    for (let i = 0; i < 50; i++) {
      this.createParticle()
    }
    this.animate()
  }

  createParticle() {
    const particle = document.createElement("div")
    particle.style.position = "absolute"
    particle.style.width = "2px"
    particle.style.height = "2px"
    particle.style.backgroundColor = "rgba(0, 0, 0, 0.1)"
    particle.style.borderRadius = "50%"
    particle.style.pointerEvents = "none"

    const x = Math.random() * window.innerWidth
    const y = Math.random() * window.innerHeight
    const vx = (Math.random() - 0.5) * 0.5
    const vy = (Math.random() - 0.5) * 0.5

    particle.style.left = x + "px"
    particle.style.top = y + "px"

    this.container.appendChild(particle)
    this.particles.push({
      element: particle,
      x: x,
      y: y,
      vx: vx,
      vy: vy,
    })
  }

  animate() {
    this.particles.forEach((particle) => {
      particle.x += particle.vx
      particle.y += particle.vy

      // Wrap around screen
      if (particle.x > window.innerWidth) particle.x = 0
      if (particle.x < 0) particle.x = window.innerWidth
      if (particle.y > window.innerHeight) particle.y = 0
      if (particle.y < 0) particle.y = window.innerHeight

      particle.element.style.left = particle.x + "px"
      particle.element.style.top = particle.y + "px"
    })

    requestAnimationFrame(() => this.animate())
  }
}

// Initialize particles
const particlesContainer = document.querySelector(".particles")
if (particlesContainer) {
  new ParticleSystem(particlesContainer)
}

// Button Click Effects
document.querySelectorAll(".btn").forEach((button) => {
  button.addEventListener("click", function (e) {
    const ripple = document.createElement("span")
    const rect = this.getBoundingClientRect()
    const size = Math.max(rect.width, rect.height)
    const x = e.clientX - rect.left - size / 2
    const y = e.clientY - rect.top - size / 2

    ripple.style.width = ripple.style.height = size + "px"
    ripple.style.left = x + "px"
    ripple.style.top = y + "px"
    ripple.classList.add("ripple")

    this.appendChild(ripple)

    setTimeout(() => {
      ripple.remove()
    }, 600)
  })
})

// Add ripple effect CSS
const rippleCSS = `
.btn {
    position: relative;
    overflow: hidden;
}

.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}
`

const style = document.createElement("style")
style.textContent = rippleCSS
document.head.appendChild(style)

// Parallax Effect
window.addEventListener("scroll", () => {
  const scrolled = window.pageYOffset
  const parallaxElements = document.querySelectorAll(".floating, .floating-delayed")

  parallaxElements.forEach((element) => {
    const speed = 0.5
    const yPos = -(scrolled * speed)
    element.style.transform = `translateY(${yPos}px)`
  })
})

// Typing Effect for Hero Title
function typeWriter(element, text, speed = 100) {
  let i = 0
  element.innerHTML = ""

  function type() {
    if (i < text.length) {
      element.innerHTML += text.charAt(i)
      i++
      setTimeout(type, speed)
    }
  }

  type()
}

// Mouse Follow Effect
document.addEventListener("mousemove", (e) => {
  const cursor = document.querySelector(".cursor")
  if (!cursor) {
    const newCursor = document.createElement("div")
    newCursor.classList.add("cursor")
    document.body.appendChild(newCursor)
  }

  const cursorElement = document.querySelector(".cursor")
  cursorElement.style.left = e.clientX + "px"
  cursorElement.style.top = e.clientY + "px"
})

// Add cursor CSS
const cursorCSS = `
.cursor {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    position: fixed;
    pointer-events: none;
    z-index: 9999;
    transition: all 0.1s ease;
    transform: translate(-50%, -50%);
}

@media (pointer: coarse) {
    .cursor {
        display: none;
    }
}
`

const cursorStyle = document.createElement("style")
cursorStyle.textContent = cursorCSS
document.head.appendChild(cursorStyle)

// Hover Effects for Interactive Elements
document.querySelectorAll(".feature-card, .product-item").forEach((card) => {
  card.addEventListener("mouseenter", function () {
    this.style.transform = "translateY(-10px) scale(1.02)"
  })

  card.addEventListener("mouseleave", function () {
    this.style.transform = "translateY(0) scale(1)"
  })
})

// Loading Animation
window.addEventListener("load", () => {
  const loader = document.querySelector(".loader")
  if (loader) {
    loader.style.opacity = "0"
    setTimeout(() => {
      loader.style.display = "none"
    }, 500)
  }
})

// Scroll to Top Functionality
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  })
}

// Create scroll to top button
const createScrollToTopButton = () => {
  const button = document.createElement("button")
  button.innerHTML = '<i class="fas fa-chevron-up"></i>'
  button.classList.add("scroll-to-top")
  button.addEventListener("click", scrollToTop)
  document.body.appendChild(button)

  // Show/hide button based on scroll position
  window.addEventListener("scroll", () => {
    if (window.pageYOffset > 500) {
      button.classList.add("visible")
    } else {
      button.classList.remove("visible")
    }
  })
}

// Add scroll to top button CSS
const scrollToTopCSS = `
.scroll-to-top {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    color: var(--secondary-color);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    opacity: 0;
    transform: translateY(100px);
    transition: all 0.3s ease;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.scroll-to-top.visible {
    opacity: 1;
    transform: translateY(0);
}

.scroll-to-top:hover {
    background: var(--accent-color);
    transform: translateY(-5px);
}
`

const scrollStyle = document.createElement("style")
scrollStyle.textContent = scrollToTopCSS
document.head.appendChild(scrollStyle)

// Initialize scroll to top button
createScrollToTopButton()

// Lazy Loading for Images
const lazyImages = document.querySelectorAll("img[data-src]")
const imageObserver = new IntersectionObserver((entries, observer) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      const img = entry.target
      img.src = img.dataset.src
      img.classList.remove("lazy")
      imageObserver.unobserve(img)
    }
  })
})

lazyImages.forEach((img) => imageObserver.observe(img))

// Performance optimization
const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Debounced scroll handler
const debouncedScrollHandler = debounce(() => {
  // Handle scroll-intensive operations here
}, 10)

window.addEventListener("scroll", debouncedScrollHandler)

// Initialize all animations and effects when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  // Add entrance animations to elements
  const elementsToAnimate = document.querySelectorAll(
    ".hero-content, .hero-visual, .feature-card, .product-item, .stat-item",
  )

  elementsToAnimate.forEach((el, index) => {
    el.style.opacity = "0"
    el.style.transform = "translateY(30px)"

    setTimeout(() => {
      el.style.transition = "all 0.8s ease-out"
      el.style.opacity = "1"
      el.style.transform = "translateY(0)"
    }, index * 100)
  })

  const modelsGrid = document.querySelector(".new-models .models-grid")
  if (!modelsGrid) return

  // Prevent card navigation when clicking inner buttons/links
  modelsGrid.querySelectorAll(".hero-buttons .btn").forEach((btn) => {
    btn.addEventListener("click", (e) => e.stopPropagation())
  })

  modelsGrid.addEventListener("click", (e) => {
    const card = e.target.closest(".product-card")
    if (!card) return
    const id = card.getAttribute("data-product-id")
    if (!id) return
    window.location.href = `product-details.html?id=${encodeURIComponent(id)}`
  })

  initializeCarousel()

  console.log("LUSANO website loaded successfully!")
})

function initializeCarousel() {
  const slides = document.querySelectorAll(".carousel-slide")
  const indicators = document.querySelectorAll(".carousel-indicator")
  const prevBtn = document.getElementById("carousel-prev")
  const nextBtn = document.getElementById("carousel-next")

  if (!slides.length || !indicators.length) {
    console.log("Carousel elements not found")
    return
  }

  let currentSlide = 0
  let autoSlideInterval
  let isVideoPlaying = false

  const slideContent = [
    {
      title: "Smart Living",
      description: "Watch how LUSANO transforms your daily routine with seamless security and smart home integration.",
    },
    {
      title: "Digital Security",
      description:
        "Advanced biometric authentication and military-grade encryption ensure your home stays protected with cutting-edge technology.",
    },
    {
      title: "Smart Control",
      description:
        "Control your entire security system remotely through our intuitive mobile app with real-time notifications and monitoring.",
    },
    {
      title: "Home Integration",
      description:
        "Seamlessly integrate with your existing smart home ecosystem for a complete automated living experience.",
    },
  ]

  function startAutoSlide() {
    if (isVideoPlaying) return

    autoSlideInterval = setInterval(() => {
      const currentSlideElement = slides[currentSlide]
      if (currentSlideElement && currentSlideElement.dataset.type === "video") {
        const video = currentSlideElement.querySelector(".carousel-video")
        if (video && !video.ended) {
          return
        }
      }
      nextSlide()
    }, 5000)
  }

  function stopAutoSlide() {
    if (autoSlideInterval) {
      clearInterval(autoSlideInterval)
    }
  }

  function handleVideoEvents() {
    slides.forEach((slide, index) => {
      if (slide.dataset.type === "video") {
        const video = slide.querySelector(".carousel-video")
        if (video) {
          video.addEventListener("play", () => {
            if (index === currentSlide) {
              isVideoPlaying = true
              stopAutoSlide()
            }
          })

          video.addEventListener("pause", () => {
            isVideoPlaying = false
            startAutoSlide()
          })

          video.addEventListener("ended", () => {
            isVideoPlaying = false
            startAutoSlide()
            setTimeout(() => {
              nextSlide()
            }, 1000)
          })

          video.addEventListener("error", (e) => {
            console.log("Video loading error:", e)
            // Continue with carousel even if video fails
            nextSlide()
          })
        }
      }
    })
  }

  // Navigation button handlers
  if (prevBtn) {
    prevBtn.addEventListener("click", (e) => {
      e.preventDefault()
      stopAutoSlide()
      prevSlide()
      startAutoSlide()
    })
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", (e) => {
      e.preventDefault()
      stopAutoSlide()
      nextSlide()
      startAutoSlide()
    })
  }

  // Indicator click handlers
  indicators.forEach((indicator, index) => {
    indicator.addEventListener("click", (e) => {
      e.preventDefault()
      stopAutoSlide()
      goToSlide(index)
      startAutoSlide()
    })
  })

  // Touch/swipe support for mobile
  let startX = 0
  let endX = 0

  const heroSection = document.querySelector(".hero")
  if (heroSection) {
    heroSection.addEventListener("touchstart", (e) => {
      startX = e.touches[0].clientX
    })

    heroSection.addEventListener("touchend", (e) => {
      endX = e.changedTouches[0].clientX
      handleSwipe()
    })
  }

  function handleSwipe() {
    const swipeThreshold = 50
    const diff = startX - endX

    if (Math.abs(diff) > swipeThreshold) {
      stopAutoSlide()
      if (diff > 0) {
        nextSlide()
      } else {
        prevSlide()
      }
      startAutoSlide()
    }
  }

  function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length
    updateCarousel()
  }

  function prevSlide() {
    currentSlide = currentSlide === 0 ? slides.length - 1 : currentSlide - 1
    updateCarousel()
  }

  function goToSlide(index) {
    currentSlide = index
    updateCarousel()
  }

  function updateCarousel() {
    // Update slides
    slides.forEach((slide, index) => {
      const isActive = index === currentSlide
      slide.classList.toggle("active", isActive)

      if (slide.dataset.type === "video") {
        const video = slide.querySelector(".carousel-video")
        if (video) {
          if (isActive) {
            video.currentTime = 0
            const playPromise = video.play()
            if (playPromise !== undefined) {
              playPromise.catch((e) => {
                console.log("Video autoplay prevented:", e)
                // Show play button or handle autoplay restriction
              })
            }
          } else {
            video.pause()
          }
        }
      }
    })

    // Update indicators
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle("active", index === currentSlide)
    })
  }

  // Pause carousel on hover
  const hero = document.querySelector(".hero")
  if (hero) {
    hero.addEventListener("mouseenter", stopAutoSlide)
    hero.addEventListener("mouseleave", () => {
      if (!isVideoPlaying) startAutoSlide()
    })
  }

  handleVideoEvents()

  // Initialize carousel
  updateCarousel()
  startAutoSlide()

  console.log("Enhanced video carousel initialized successfully!")
  console.log(`Found ${slides.length} slides and ${indicators.length} indicators`)
}
