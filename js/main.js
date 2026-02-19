// ============================================
// NAVBAR LOADER
// ============================================

async function loadNavbar() {
  const navbarContainer = document.getElementById('navbar-container');
  if (!navbarContainer) return;
  
  try {
    const response = await fetch('/components/navbar.html');
    const html = await response.text();
    navbarContainer.innerHTML = html;
    
    // Get current path (without trailing slash)
    const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
    
    // Check if current page is a dropdown item
    const dropdownLinks = document.querySelectorAll('.dropdown-link');
    let isDropdownPage = false;
    
    dropdownLinks.forEach(link => {
      const linkPath = link.getAttribute('href').replace(/\/$/, '');
      if (linkPath.toLowerCase() === currentPath.toLowerCase()) {
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
        const linkPath = link.getAttribute('href').replace(/\/$/, '');
        if (linkPath.toLowerCase() === currentPath.toLowerCase()) {
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

  // Create backdrop element
  let backdrop = document.querySelector('.nav-backdrop');
  if (!backdrop) {
    backdrop = document.createElement('div');
    backdrop.className = 'nav-backdrop';
    document.body.appendChild(backdrop);
  }

  // Mobile menu toggle
  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
      menuToggle.classList.toggle('active');
      backdrop.classList.toggle('active');
      
      // Prevent body scroll when menu is open
      if (navLinks.classList.contains('active')) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    });
    
    // Close menu when clicking backdrop
    backdrop.addEventListener('click', () => {
      navLinks.classList.remove('active');
      menuToggle.classList.remove('active');
      backdrop.classList.remove('active');
      document.body.style.overflow = '';
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!nav.contains(e.target) && !backdrop.contains(e.target) && navLinks.classList.contains('active')) {
        navLinks.classList.remove('active');
        menuToggle.classList.remove('active');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
    
    // Mobile dropdown toggle
    const dropdownParents = document.querySelectorAll('.nav-dropdown');
    dropdownParents.forEach(dropdown => {
      const parentLink = dropdown.querySelector('.nav-link');
      
      if (parentLink) {
        parentLink.addEventListener('click', (e) => {
          if (window.innerWidth <= 968) {
            e.preventDefault();
            dropdown.classList.toggle('open');
          }
        });
      }
    });
    
    // Close menu when clicking a dropdown link (not parent)
    const dropdownLinks = navLinks.querySelectorAll('.dropdown-link');
    dropdownLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 968) {
          navLinks.classList.remove('active');
          menuToggle.classList.remove('active');
          backdrop.classList.remove('active');
          document.body.style.overflow = '';
        }
      });
    });
    
    // Close menu when clicking a regular nav-link (not dropdown parent)
    const regularLinks = navLinks.querySelectorAll('.nav-link:not(.nav-dropdown .nav-link)');
    regularLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 968) {
          navLinks.classList.remove('active');
          menuToggle.classList.remove('active');
          backdrop.classList.remove('active');
          document.body.style.overflow = '';
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
    const response = await fetch('/components/footer.html');
    const html = await response.text();
    footerContainer.innerHTML = html;
  } catch (error) {
    console.error('Error loading footer:', error);
  }
}


// Load footer on DOM ready
document.addEventListener('DOMContentLoaded', loadFooter);

// ============================================
// HERO SLIDESHOW
// ============================================
document.addEventListener('DOMContentLoaded', function () {
  const slideshowImages = [
    'data/images/slideshow/Assistara_Bild_Slideshow01.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow02.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow03.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow04.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow05.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow06.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow07.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow08.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow09.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow10.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow11.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow12.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow13.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow14.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow15.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow16.jpeg',
    'data/images/slideshow/Assistara_Bild_Slideshow17.jpeg',
  ];
  const slideshowContainer = document.getElementById('hero-slideshow');
  if (!slideshowContainer) return;

  // Create image elements
  slideshowImages.forEach((src, idx) => {
    const img = document.createElement('img');
    img.src = src;
    img.alt = 'Assistara Slideshow Bild ' + (idx + 1);
    img.className = 'slideshow-image';
    img.style.opacity = idx === 0 ? '1' : '0';
    img.style.zIndex = idx === 0 ? '2' : '1';
    slideshowContainer.appendChild(img);
  });

  let current = 0;
  const images = slideshowContainer.querySelectorAll('.slideshow-image');
  if (images.length <= 1) return;

  setInterval(() => {
    const prev = current;
    current = (current + 1) % images.length;
    images[prev].style.transition = 'opacity 1.2s';
    images[current].style.transition = 'opacity 1.2s';
    images[prev].style.opacity = '0';
    images[current].style.opacity = '1';
    images[current].style.zIndex = '2';
    images[prev].style.zIndex = '1';
  }, 5000);
});
