export default {
    mounted(el) {
      
      const ripple = document.createElement('span');
      ripple.className =
        'pointer-events-none absolute rounded-full bg-emerald-500 opacity-20';
      ripple.style.position = 'absolute';
      ripple.style.width = ripple.style.height = '300px';
      ripple.style.transform = 'scale(0)';
      ripple.style.transition = 'transform 0.6s ease-out, opacity 0.6s ease-out';
      ripple.style.zIndex = '0';
  
      el.style.position = 'relative';
      el.style.overflow = 'hidden';
      el.appendChild(ripple);
  
      el.addEventListener('click', (event) => {
        const rect = el.getBoundingClientRect();
        const x = event.clientX - rect.left - 150; // 150 = half ripple size
        const y = event.clientY - rect.top - 150;
  
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
  
        ripple.style.transition = 'none';
        ripple.style.transform = 'scale(0)';
        ripple.style.opacity = '0.3';
        void ripple.offsetWidth;
  
        ripple.style.transition = 'transform 1.6s ease-out, opacity 1.6s ease-out';
        ripple.style.transform = 'scale(4)';
        ripple.style.opacity = '0';
      });
    },
  };
  