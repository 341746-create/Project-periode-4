document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.neon-ticket-card');
    const markAllBtn = document.getElementById('markAllRead');
    const counterNum = document.querySelector('.count-num');
    const counterBadge = document.getElementById('notificationCounter');

    // ==========================================
    // 1. DYNAMIC INTERACTIVE 3D TILT ENGINE
    // ==========================================
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            
            // Get mouse position relative to the target card
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            
            // Core Center Calculations
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            // Calculate tilt factors (higher number = milder tilt physics)
            const rotateX = (centerY - mouseY) / 10; 
            const rotateY = (mouseX - centerX) / 15;
            
            // Apply real-time 3D style rotation matrix
            card.style.transform = `translateY(-6px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(15px)`;
        });

        // Reset positions gracefully when moving away
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) rotateX(0) rotateY(0) translateZ(0)';
        });

        // ==========================================
        // 2. MARK INDIVIDUAL TICKET AS READ ON CLICK
        // ==========================================
        card.addEventListener('click', () => {
            if (card.classList.contains('unread-ticket')) {
                markAsRead(card);
                updateCounter();
            }
        });
    });

    // Helper function to update system status classes safely
    function markAsRead(ticket) {
        ticket.classList.remove('unread-ticket');
        
        // Remove the live tracking animation dots
        const dot = ticket.querySelector('.neon-pulse-dot');
        if (dot) dot.style.display = 'none';
        
        // Dim the item smoothly to signify read history status
        ticket.style.opacity = '0.5';
    }

    // ==========================================
    // 3. DYNAMIC NOTIFICATION SYSTEM COUNTER
    // ==========================================
    function updateCounter() {
        const remainingUnread = document.querySelectorAll('.unread-ticket').length;
        
        if (remainingUnread > 0) {
            counterNum.textContent = remainingUnread;
        } else {
            // Turn badge off beautifully if nothing is pending
            counterBadge.style.background = 'rgba(255, 255, 255, 0.05)';
            counterBadge.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            counterBadge.style.color = '#807270';
            counterBadge.innerHTML = `<i class="fa-solid fa-check"></i> Geen updates`;
        }
    }

    // ==========================================
    // 4. ACTION CONTROLLER: MARK ALL READ BUTTON
    // ==========================================
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            const unreadTickets = document.querySelectorAll('.unread-ticket');
            
            unreadTickets.forEach((ticket, index) => {
                // Staggered delay effect to make the transition look professional
                setTimeout(() => {
                    markAsRead(ticket);
                    if (index === unreadTickets.length - 1) {
                        updateCounter();
                    }
                }, index * 150); 
            });
        });
    }
});