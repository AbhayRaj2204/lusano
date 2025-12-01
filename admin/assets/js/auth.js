// Default credentials (in production, use proper backend authentication)
const DEFAULT_CREDENTIALS = {
  username: "admin",
  password: "admin123",
}

// Session timeout in milliseconds (20 minutes)
const SESSION_TIMEOUT = 20 * 60 * 1000

// Initialize auth on page load
document.addEventListener("DOMContentLoaded", () => {
  // Check if on login page
  if (document.getElementById("loginForm")) {
    initializeLoginPage()
  } else {
    // Check if user is authenticated on admin pages
    checkAuthentication()
    initializeSessionTimeout()
  }
})

// Initialize login page
function initializeLoginPage() {
  const loginForm = document.getElementById("loginForm")
  const loginBtn = document.getElementById("loginBtn")
  const errorMessage = document.getElementById("errorMessage")

  loginForm.addEventListener("submit", (e) => {
    e.preventDefault()

    const username = document.getElementById("username").value.trim()
    const password = document.getElementById("password").value.trim()

    // Validate credentials
    if (username === DEFAULT_CREDENTIALS.username && password === DEFAULT_CREDENTIALS.password) {
      // Store login credentials in local storage
      const loginData = {
        username: username,
        loginTime: new Date().getTime(),
        lastActivityTime: new Date().getTime(),
      }

      localStorage.setItem("lusano_admin_auth", JSON.stringify(loginData))
      console.log("[v0] User logged in successfully")

      // Redirect to admin dashboard
      window.location.href = "index.php"
    } else {
      // Show error message
      errorMessage.textContent = "Invalid username or password"
      errorMessage.classList.add("show")
      console.log("[v0] Login failed - invalid credentials")

      // Clear password field
      document.getElementById("password").value = ""
    }
  })
}

// Check if user is authenticated
function checkAuthentication() {
  const authData = localStorage.getItem("lusano_admin_auth")

  if (!authData) {
    console.log("[v0] No authentication found, redirecting to login")
    window.location.href = "login.html"
    return
  }

  try {
    const auth = JSON.parse(authData)
    const currentTime = new Date().getTime()
    const loginTime = auth.loginTime

    // Check if session has expired (20 minutes)
    if (currentTime - loginTime > SESSION_TIMEOUT) {
      console.log("[v0] Session expired, logging out")
      logout()
      return
    }

    // Update last activity time
    auth.lastActivityTime = currentTime
    localStorage.setItem("lusano_admin_auth", JSON.stringify(auth))
    console.log("[v0] User authenticated, session valid")
  } catch (error) {
    console.log("[v0] Error parsing auth data:", error)
    logout()
  }
}

// Initialize session timeout monitoring
function initializeSessionTimeout() {
  // Check session every minute
  setInterval(() => {
    const authData = localStorage.getItem("lusano_admin_auth")

    if (!authData) {
      return
    }

    try {
      const auth = JSON.parse(authData)
      const currentTime = new Date().getTime()
      const loginTime = auth.loginTime

      // Check if session has expired
      if (currentTime - loginTime > SESSION_TIMEOUT) {
        console.log("[v0] Session timeout triggered")
        showSessionTimeoutWarning()
        logout()
      }
    } catch (error) {
      console.log("[v0] Error checking session:", error)
    }
  }, 60000) // Check every 60 seconds

  // Track user activity
  document.addEventListener("click", updateActivityTime)
  document.addEventListener("keypress", updateActivityTime)
  document.addEventListener("mousemove", updateActivityTime)
}

// Update last activity time
function updateActivityTime() {
  const authData = localStorage.getItem("lusano_admin_auth")

  if (authData) {
    try {
      const auth = JSON.parse(authData)
      auth.lastActivityTime = new Date().getTime()
      localStorage.setItem("lusano_admin_auth", JSON.stringify(auth))
    } catch (error) {
      console.log("[v0] Error updating activity time:", error)
    }
  }
}

// Show session timeout warning
function showSessionTimeoutWarning() {
  const notification = document.createElement("div")
  notification.className = "notification notification-warning"
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        background: var(--admin-warning);
        color: white;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    `
  notification.textContent = "Your session has expired. Please login again."
  document.body.appendChild(notification)

  setTimeout(() => {
    notification.remove()
  }, 5000)
}

// Logout function
function logout() {
  console.log("[v0] Logging out user")
  localStorage.removeItem("lusano_admin_auth")
  window.location.href = "login.html"
}

// Add logout button functionality to admin pages
function addLogoutButton() {
  const adminHeader = document.querySelector(".admin-header")
  if (adminHeader) {
    const logoutBtn = document.createElement("button")
    logoutBtn.className = "btn btn-danger btn-sm"
    logoutBtn.style.marginLeft = "auto"
    logoutBtn.innerHTML = '<i class="fas fa-sign-out-alt"></i> Logout'
    logoutBtn.addEventListener("click", logout)

    // Insert before the mobile menu toggle
    const mobileToggle = adminHeader.querySelector("#mobile-menu-toggle")
    if (mobileToggle) {
      mobileToggle.parentElement.insertBefore(logoutBtn, mobileToggle)
    } else {
      adminHeader.appendChild(logoutBtn)
    }
  }
}

// Call logout button setup when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", addLogoutButton)
} else {
  addLogoutButton()
}
