/**
 * Page Loader: fills the water gauge until the page fully loads,
 * then fades out and removes the overlay.
 */
;(() => {
  const onReady = (fn) => (document.readyState !== "loading" ? fn() : document.addEventListener("DOMContentLoaded", fn))

  onReady(() => {
    const loader = document.getElementById("page-loader")
    if (!loader) return

    const level = loader.querySelector(".water-level")
    let progress = 0

    const tick = () => {
      progress = Math.min(progress + Math.random() * 3, 85)
      if (level) level.style.height = progress + "%"
    }
    const interval = setInterval(tick, 150)

    const completeLoading = () => {
      clearInterval(interval)
      requestAnimationFrame(() => {
        if (level) level.style.height = "100%"
        setTimeout(() => {
          loader.classList.add("pl-hide")
          setTimeout(() => loader.remove(), 450)
        }, 300)
      })
    }

    // Complete loading when window fully loads
    window.addEventListener("load", completeLoading)

    // Fallback: complete after 8 seconds even if resources don't load
    setTimeout(() => {
      if (loader && !loader.classList.contains("pl-hide")) {
        completeLoading()
      }
    }, 8000)
  })
})()
