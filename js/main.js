// Navigation functionality
const links = document.querySelectorAll('.nav-link');
const blob = document.getElementById('navBlob');
const menuToggle = document.getElementById('menuToggle');
const navLinks = document.getElementById('navLinks');

function updateBlob(link) {
  if (window.innerWidth > 768) {
    const rect = link.getBoundingClientRect();
    const navRect = link.parentElement.getBoundingClientRect();
    blob.style.width = rect.width + 'px';
    blob.style.height = rect.height + 'px';
    blob.style.left = (rect.left - navRect.left) + 'px';
    blob.style.top = (rect.top - navRect.top) + 'px';
    blob.classList.add('active');
  }
}

links.forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    links.forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    updateBlob(link);
    
    if (window.innerWidth <= 768) {
      navLinks.classList.remove('active');
      menuToggle.classList.remove('active');
    }
  });
});

if (menuToggle) {
  menuToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    menuToggle.classList.toggle('active');
  });
}

const activeLink = document.querySelector('.nav-link.active');
if (activeLink && window.innerWidth > 768) {
  setTimeout(() => updateBlob(activeLink), 100);
}

window.addEventListener('resize', () => {
  const activeLink = document.querySelector('.nav-link.active');
  if (activeLink && window.innerWidth > 768) {
    updateBlob(activeLink);
  }
});

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
