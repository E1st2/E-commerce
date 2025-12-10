// =========================
// CART MANAGEMENT FUNCTIONS
// =========================

function getCart() {
  const cartString = localStorage.getItem("cart") || "[]"
  return JSON.parse(cartString)
}

function saveCart(cart) {
  localStorage.setItem("cart", JSON.stringify(cart))
  updateCartCount()
}

function addToCart(product) {
  const cart = getCart()
  const existingItem = cart.find((item) => item.id === product.id)

  if (existingItem) {
    existingItem.quantity += 1
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      price: product.price,
      image: product.image,
      quantity: 1,
    })
  }

  saveCart(cart)
  showNotification(`${product.name} added to cart!`)
}

function clearCart() {
  if (confirm("Are you sure you want to clear your cart?")) {
    localStorage.removeItem("cart")
    updateCartCount()
    showNotification("Cart cleared!")

    if (window.location.pathname.includes("cart.php")) {
      window.location.reload()
    }
  }
}

function updateCartCount() {
  const cart = getCart()
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0)
  const countElement = document.getElementById("cart-count")

  if (countElement) {
    countElement.textContent = totalItems
  }
}

// =========================
// CART PAGE RENDERING
// =========================

function loadCartPage() {
  if (!window.cartPageConfig) {
    console.log("ERROR: cartPageConfig not found")
    return
  }

  const { baseUrl, isLoggedIn } = window.cartPageConfig
  const cart = getCart()

  const itemsContainer = document.getElementById("cart-items-container")
  const summaryContainer = document.getElementById("cart-summary")

  if (!itemsContainer || !summaryContainer) {
    console.log("ERROR: Cart containers not found")
    return
  }

  if (cart.length === 0) {
    itemsContainer.innerHTML = `<p class="empty-cart">Your cart is empty</p>`
    summaryContainer.innerHTML = ""
    return
  }

  let itemsHTML = `<div class="cart-items">`
  const baseUrli = `${baseUrl}/images/`

  cart.forEach((item) => {
    const displayPrice = isLoggedIn ? item.price * 0.95 : item.price
    const itemTotal = (displayPrice * item.quantity).toFixed(0)

    const imagePath = item.image.startsWith("http") ? item.image : `${baseUrli}${item.image}`

    itemsHTML += `
        <div class="cart-item" data-id="${item.id}">
            <img src="${imagePath}" alt="${item.name}"
                onerror="this.onerror=null; this.src='${baseUrl}/images/placeholder.jpg'">

            <div class="cart-item-info">
                <h4>${item.name}</h4>
                <p class="cart-item-price">
                    ${isLoggedIn ? `<span style="text-decoration: line-through; color: #999;">${item.price.toFixed(0)} XAF</span>` : ""}
                    ${displayPrice.toFixed(0)} XAF
                    ${isLoggedIn ? `<span style="color: #10b981; font-size: 0.875rem;">(5% off)</span>` : ""}
                </p>
            </div>

            <div class="quantity-controls">
                <button class="qty-btn qty-decrease" data-id="${item.id}">-</button>
                <span class="quantity">${item.quantity}</span>
                <button class="qty-btn qty-increase" data-id="${item.id}">+</button>
            </div>

            <p class="item-total">${itemTotal} XAF</p>

            <button class="btn-remove" data-id="${item.id}">Remove</button>
        </div>
        `
  })

  itemsHTML += `</div>`
  itemsContainer.innerHTML = itemsHTML

  setupCartEventListeners()

  // =========================
  // SUMMARY SECTION
  // =========================

  const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0)
  const discountAmount = isLoggedIn ? subtotal * 0.05 : 0
  const total = subtotal - discountAmount

  let summaryHTML = `
        <div class="cart-summary-box">
            <h3>Order Summary</h3>

            <div class="summary-row">
                <span>Subtotal:</span>
                <span>${subtotal.toFixed(0)} XAF</span>
            </div>
    `

  if (isLoggedIn) {
    summaryHTML += `
            <div class="summary-row discount-row">
                <span>Account Discount (5%):</span>
                <span style="color: #10b981;">-${discountAmount.toFixed(0)} XAF</span>
            </div>
        `
  }

  summaryHTML += `
            <div class="summary-row total-row">
                <strong>Total:</strong>
                <strong>${total.toFixed(0)} XAF</strong>
            </div>

            <button class="btn btn-primary btn-block btn-place-order">Commander</button>
        </div>
    `

  summaryContainer.innerHTML = summaryHTML

  const placeOrderBtn = document.querySelector(".btn-place-order")
  if (placeOrderBtn) {
    placeOrderBtn.addEventListener("click", placeOrder)
  }
}

function setupCartEventListeners() {
  const itemsContainer = document.getElementById("cart-items-container")
  if (!itemsContainer) return

  // Event delegation for decrease quantity
  itemsContainer.addEventListener("click", (e) => {
    if (e.target.classList.contains("qty-decrease")) {
      const productId = Number.parseInt(e.target.dataset.id)
      updateQuantity(productId, -1)
    }
  })

  // Event delegation for increase quantity
  itemsContainer.addEventListener("click", (e) => {
    if (e.target.classList.contains("qty-increase")) {
      const productId = Number.parseInt(e.target.dataset.id)
      updateQuantity(productId, 1)
    }
  })

  // Event delegation for remove button
  itemsContainer.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-remove")) {
      const productId = Number.parseInt(e.target.dataset.id)
      removeFromCart(productId)
    }
  })
}

// =========================
// QUANTITY UPDATES
// =========================

function updateQuantity(productId, change) {
  const cart = getCart()
  const item = cart.find((i) => i.id === productId)

  if (item) {
    item.quantity = Math.max(1, item.quantity + change)
    saveCart(cart)
    loadCartPage()
  }
}

function removeFromCart(productId) {
  let cart = getCart()
  cart = cart.filter((i) => i.id !== productId)
  saveCart(cart)
  loadCartPage()
  showNotification("Item removed from cart")
}

// =========================
// ORDER PROCESSING
// =========================

async function placeOrder() {
  if (!window.cartPageConfig) {
    return
  }

  const { baseUrl, isLoggedIn } = window.cartPageConfig
  const cart = getCart()

  if (cart.length === 0) {
    alert("Your cart is empty!")
    return
  }

  if (!isLoggedIn) {
    sessionStorage.setItem("guestOrder", JSON.stringify(cart))
    localStorage.removeItem("cart")
    updateCartCount()
    window.location.href = baseUrl + "/payment-success.php"
    return
  }

  try {
    const response = await fetch(baseUrl + "/api/place-order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ items: cart }),
    })

    const data = await response.json()

    if (data.success) {
      sessionStorage.setItem("lastOrderId", data.order_id)
      localStorage.removeItem("cart")
      updateCartCount()
      window.location.href = baseUrl + "/payment-success.php"
    } else {
      alert(data.message || "Failed to place order")
    }
  } catch (error) {
    console.error("Order error:", error)
    alert("An error occurred. Please try again.")
  }
}

// =========================
// CHAT SYSTEM
// =========================

function initChat() {
  const chatContainer = document.getElementById("chat-widget")
  if (!chatContainer) return

  const chat = {
    isOpen: false,
    messages: JSON.parse(localStorage.getItem("chatMessages") || "[]"),
  }

  function toggleChat() {
    chat.isOpen = !chat.isOpen
    const chatBox = document.querySelector(".chat-box")
    const chatToggle = document.querySelector(".chat-toggle")

    if (chat.isOpen) {
      chatBox.style.display = "flex"
      chatToggle.style.backgroundColor = "#ef4444"
    } else {
      chatBox.style.display = "none"
      chatToggle.style.backgroundColor = "#3b82f6"
    }
  }

  function sendMessage(text) {
    if (!text.trim()) return

    const userMessage = {
      sender: "user",
      text: text,
      time: new Date().toLocaleTimeString(),
    }

    chat.messages.push(userMessage)
    localStorage.setItem("chatMessages", JSON.stringify(chat.messages))
    renderMessages()

    // Auto-reply after 1 second
    setTimeout(() => {
      const botMessage = {
        sender: "bot",
        text: "Thanks for your message! Our team will respond shortly.",
        time: new Date().toLocaleTimeString(),
      }
      chat.messages.push(botMessage)
      localStorage.setItem("chatMessages", JSON.stringify(chat.messages))
      renderMessages()
    }, 1000)
  }

  function renderMessages() {
    const messagesDiv = document.querySelector(".chat-messages")
    if (!messagesDiv) return

    messagesDiv.innerHTML = chat.messages
      .map(
        (msg) => `
                <div class="chat-message ${msg.sender}">
                    <p>${msg.text}</p>
                    <small>${msg.time}</small>
                </div>
            `,
      )
      .join("")

    messagesDiv.scrollTop = messagesDiv.scrollHeight
  }

  // Event listeners
  const chatToggle = document.querySelector(".chat-toggle")
  const sendBtn = document.querySelector(".chat-send-btn")
  const messageInput = document.querySelector(".chat-input")

  if (chatToggle) chatToggle.addEventListener("click", toggleChat)
  if (sendBtn) sendBtn.addEventListener("click", () => sendMessage(messageInput.value))
  if (messageInput) {
    messageInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") sendMessage(messageInput.value)
    })
  }

  renderMessages()
  window.toggleChat = toggleChat
}

// =========================
// NOTIFICATION SYSTEM
// =========================

function showNotification(message) {
  const notification = document.createElement("div")
  notification.className = "notification"

  notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background-color: #10b981;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    `

  notification.textContent = message
  document.body.appendChild(notification)

  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease-out"
    setTimeout(() => notification.remove(), 300)
  }, 2000)
}

// =========================
// MAKE FUNCTIONS GLOBAL
// =========================

window.addToCart = addToCart
window.clearCart = clearCart
window.updateCartCount = updateCartCount
window.loadCartPage = loadCartPage
window.updateQuantity = updateQuantity
window.removeFromCart = removeFromCart
window.placeOrder = placeOrder
window.initChat = initChat

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  updateCartCount()
  initChat()
})
