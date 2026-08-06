(() => {
  const initProductFan = () => {
    const section = document.querySelector('[data-home-product-fan]');
    if (!section) return;

    const stage = section.querySelector('[data-product-fan-stage]');
    const items = Array.from(section.querySelectorAll('[data-product-fan-item]'));
    const selects = Array.from(section.querySelectorAll('[data-product-fan-select]'));
    const previous = section.querySelector('[data-product-fan-prev]');
    const next = section.querySelector('[data-product-fan-next]');
    const status = section.querySelector('[data-product-fan-status]');
    const mobile = window.matchMedia('(max-width: 767px)');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let active = 0;
    let timer = 0;
    let pointerActive = false;
    let focusActive = false;

    if (!stage || items.length < 2) return;

    const normalize = (index) => (index + items.length) % items.length;

    const setActive = (index, announce = true, scrollMobile = true) => {
      active = normalize(index);

      items.forEach((item, itemIndex) => {
        const offset = normalize(itemIndex - active);
        item.classList.toggle('is-active', offset === 0);
        item.classList.toggle('is-right', offset === 1);
        item.classList.toggle('is-right-back', offset === 2);
        item.classList.toggle('is-left-back', offset === items.length - 2);
        item.classList.toggle('is-left', offset === items.length - 1);
      });

      selects.forEach((select, itemIndex) => {
        const isActive = itemIndex === active;
        select.setAttribute('aria-pressed', String(isActive));
        select.setAttribute('aria-label', `${isActive ? 'Produk aktif' : 'Pilih produk'}: ${select.querySelector('img').alt}`);
      });

      if (mobile.matches && scrollMobile) {
        const item = items[active];
        stage.scrollTo({ left: item.offsetLeft, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
      }

      if (announce && status) status.textContent = `Produk ${active + 1} dari ${items.length}: ${selects[active].querySelector('img').alt}`;
    };

    const stop = () => {
      window.clearTimeout(timer);
      timer = 0;
    };

    const start = () => {
      stop();
      if (reducedMotion.matches || document.hidden || pointerActive || focusActive) return;
      timer = window.setTimeout(() => {
        setActive(active + 1, true);
        start();
      }, 4600);
    };

    selects.forEach((select, index) => {
      select.addEventListener('click', () => {
        if (index === active) {
          window.location.assign(select.dataset.productFanUrl);
          return;
        }

        stop();
        setActive(index);
        start();
      });
    });

    previous.addEventListener('click', () => { stop(); setActive(active - 1); start(); });
    next.addEventListener('click', () => { stop(); setActive(active + 1); start(); });
    stage.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft') { event.preventDefault(); stop(); setActive(active - 1); start(); }
      if (event.key === 'ArrowRight') { event.preventDefault(); stop(); setActive(active + 1); start(); }
      if (event.key === 'Home') { event.preventDefault(); stop(); setActive(0); start(); }
      if (event.key === 'End') { event.preventDefault(); stop(); setActive(items.length - 1); start(); }
    });

    stage.addEventListener('pointerenter', () => { pointerActive = true; stop(); });
    stage.addEventListener('pointerleave', () => { pointerActive = false; start(); });
    stage.addEventListener('focusin', () => { focusActive = true; stop(); });
    stage.addEventListener('focusout', (event) => {
      if (event.relatedTarget && stage.contains(event.relatedTarget)) return;
      focusActive = false;
      start();
    });
    stage.addEventListener('scroll', () => {
      if (!mobile.matches) return;
      const closest = items.reduce((selected, item, index) => (
        Math.abs(item.offsetLeft - stage.scrollLeft) < Math.abs(items[selected].offsetLeft - stage.scrollLeft) ? index : selected
      ), 0);
      if (closest !== active) setActive(closest, false, false);
    }, { passive: true });

    document.addEventListener('visibilitychange', start);
    mobile.addEventListener('change', () => setActive(active, false, mobile.matches));
    reducedMotion.addEventListener('change', start);

    setActive(0, false, false);
    start();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductFan, { once: true });
  } else {
    initProductFan();
  }
})();
