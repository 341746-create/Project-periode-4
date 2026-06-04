document.addEventListener('DOMContentLoaded', () => {
    // Main Login Selectors
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    // Registration Modal Selectors
    const modal = document.getElementById('registerModal');
    const openModalBtn = document.getElementById('createAccountLink');
    const closeModalBtn = document.getElementById('closeModal');
    const registerForm = document.getElementById('registerForm');

    /* ==========================================
       1. PASSWORD VISIBILITY TOGGLE (EYE ICON)
       ========================================== */
    if (togglePasswordBtn && passwordInput && eyeIcon) {
        togglePasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            if (isPassword) {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    }

    /* ==========================================
       2. REGISTRATION SYSTEM (Saves to LocalStorage)
       ========================================== */
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Grabbing unique input field values by ID mapping
            const regName = document.getElementById('regName').value.trim();
            const regEmail = document.getElementById('regEmail').value.trim().toLowerCase();
            const regPassword = document.getElementById('regPassword').value;

            // Simple data field completeness validation check
            if (!regName || !regEmail || !regPassword) {
                alert("Please fill in all registration fields.");
                return;
            }

            // Look up if user database key already exists inside system memory
            const existingUser = localStorage.getItem(regEmail);
            if (existingUser) {
                alert("An account with this email address already exists!");
                return;
            }

            // Bundle structural user settings object data values
            const userData = {
                name: regName,
                email: regEmail,
                password: regPassword
            };

            // Set account strings securely into client local environment mapping keys
            localStorage.setItem(regEmail, JSON.stringify(userData));
            alert(`Account created successfully for ${regName}! You can now sign in.`);
            
            // Clear out register fields and transition off popup modal overlay panel
            registerForm.reset();
            if (modal) modal.style.display = 'none';
        });
    }

    /* ==========================================
       3. AUTHENTICATION CONTROLLER SYSTEM
       ========================================== */
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const enteredEmail = emailInput.value.trim().toLowerCase();
            const enteredPassword = passwordInput.value;

            // Fetch stored string object dataset from system keys
            const savedUserDataJSON = localStorage.getItem(enteredEmail);

            if (!savedUserDataJSON) {
                alert("No profile account found matching this email. Please register an account first!");
                return;
            }

            // Convert raw structural string data formats into functional JSON data sets
            const savedUser = JSON.parse(savedUserDataJSON);

            // Conditional access verification validation matches processing
            if (enteredPassword === savedUser.password) {
                alert(`Login Successful! Welcome back under the spotlight, ${savedUser.name}!`);
                loginForm.reset();
                
                // Route landing handler redirect strings layout placement:
                // window.location.href = 'home.html';
            } else {
                alert("Incorrect password choice! Please verify inputs and try again.");
                passwordInput.value = "";
                passwordInput.focus();
            }
        });
    }

    /* ==========================================
       4. MODAL VISIBILITY HANDLERS
       ========================================== */
    if (openModalBtn && modal) {
        openModalBtn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.style.display = 'flex';
        });
    }

    if (closeModalBtn && modal) {
        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});