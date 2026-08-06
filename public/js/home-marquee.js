(() => {
  const setupMarquee = (marquee) => {
    const track = marquee.querySelector('[data-home-marquee-track]');
    const group = marquee.querySelector('[data-home-marquee-group]');

    if (!track || !group || marquee.dataset.marqueeReady) return;

    marquee.dataset.marqueeReady = 'true';

    const duplicateGroup = () => {
      const copy = group.cloneNode(true);
      copy.setAttribute('aria-hidden', 'true');
      track.append(copy);
    };

    const measure = () => {
      const gap = Number.parseFloat(getComputedStyle(track).gap) || 0;
      const distance = group.getBoundingClientRect().width + gap;

      while (track.scrollWidth < marquee.clientWidth + distance) duplicateGroup();

      marquee.style.setProperty('--home-marquee-distance', `${distance}px`);
      marquee.style.setProperty('--home-marquee-duration', `${Math.max(18, distance / 54)}s`);
    };

    measure();
    new ResizeObserver(measure).observe(marquee);
  };

  const initialize = () => document.querySelectorAll('[data-home-marquee]').forEach(setupMarquee);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
  } else {
    initialize();
  }
})();
