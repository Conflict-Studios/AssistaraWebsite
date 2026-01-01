// ============================================
// NAVBAR LOADER
// ============================================

async function loadNavbar() {
  const navbarContainer = document.getElementById('navbar-container');
  if (!navbarContainer) return;
  
  try {
    const response = await fetch('components/navbar.html');
    const html = await response.text();
    navbarContainer.innerHTML = html;
    
    // Set active link based on current page
    const currentPage = window.location.pathname.split('/').pop().replace('.html', '') || 'index';
    
    // Check if current page is a dropdown item
    const dropdownLinks = document.querySelectorAll('.dropdown-link');
    let isDropdownPage = false;
    
    dropdownLinks.forEach(link => {
      const linkPage = link.getAttribute('href').replace('.html', '');
      if (linkPage === currentPage) {
        link.classList.add('active');
        // Also mark the parent "Leistungen" link as active
        const parentNavLink = link.closest('.nav-dropdown').querySelector('.nav-link');
        if (parentNavLink) {
          parentNavLink.classList.add('active');
        }
        isDropdownPage = true;
      }
    });
    
    // Set active for main nav links if not a dropdown page
    if (!isDropdownPage) {
      const navLinks = document.querySelectorAll('.nav-link');
      navLinks.forEach(link => {
        if (link.dataset.page === currentPage) {
          link.classList.add('active');
        }
      });
    }
    
    // Initialize navigation after loading
    initNavigation();
  } catch (error) {
    console.error('Error loading navbar:', error);
  }
}

// ============================================
// NAVIGATION FUNCTIONALITY
// ============================================

function initNavigation() {
  const nav = document.querySelector('nav');
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');

  // Check initial scroll position and apply scrolled class if needed
  if (window.pageYOffset > 50) {
    nav.classList.add('scrolled');
  }

  // Scroll effect for navbar
  window.addEventListener('scroll', () => {
    if (window.pageYOffset > 50) {
      nav.classList.add('scrolled');
    } else {
      nav.classList.remove('scrolled');
    }
  });

  // Mobile menu toggle
  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
      menuToggle.classList.toggle('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!nav.contains(e.target) && navLinks.classList.contains('active')) {
        navLinks.classList.remove('active');
        menuToggle.classList.remove('active');
      }
    });
    
    // Close menu when clicking a link
    const links = navLinks.querySelectorAll('.nav-link');
    links.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 968) {
          navLinks.classList.remove('active');
          menuToggle.classList.remove('active');
        }
      });
    });
  }
}

// Load navbar on DOM ready
document.addEventListener('DOMContentLoaded', loadNavbar);

// ============================================
// FOOTER LOADER
// ============================================

async function loadFooter() {
  const footerContainer = document.getElementById('footer-container');
  if (!footerContainer) return;
  try {
    const response = await fetch('components/footer.html');
    const html = await response.text();
    footerContainer.innerHTML = html;
  } catch (error) {
    console.error('Error loading footer:', error);
  }
}

// Load footer on DOM ready
document.addEventListener('DOMContentLoaded', loadFooter);

// Form validation
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    // Basic validation
    const formData = new FormData(contactForm);
    let isValid = true;
    
    formData.forEach((value, key) => {
      if (!value.trim()) {
        isValid = false;
      }
    });
    
    if (isValid) {
      alert('Vielen Dank für Ihre Nachricht! Wir werden uns zeitnah bei Ihnen melden.');
      contactForm.reset();
    } else {
      alert('Bitte füllen Sie alle Felder aus.');
    }
  });
}
