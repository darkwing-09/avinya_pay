/**
 * GoCrazy Interactive Spotlight & 3D Tilt Hover Effects
 */
export function initGoCrazyHoverEffects() {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Spotlight radial highlight on cards (.spot or [data-spot])
  document.addEventListener('pointermove', (e) => {
    const card = e.target.closest('.spot, [data-spot], .gocrazy-glass, .card');
    if (!card) return;

    const rect = card.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;

    card.style.setProperty('--mx', `${x}%`);
    card.style.setProperty('--my', `${y}%`);

    // Dynamic 3D tilt on hovered cards with data-tilt or .tilt
    if (!reducedMotion && (card.hasAttribute('data-tilt') || card.classList.contains('tilt'))) {
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      const rotateY = (((e.clientX - rect.left) - centerX) / centerX) * 3.5;
      const rotateX = -(((e.clientY - rect.top) - centerY) / centerY) * 3.5;
      card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateZ(4px)`;
    }
  }, { passive: true });

  // Reset 3D tilt on pointer leave
  document.addEventListener('pointerout', (e) => {
    const card = e.target.closest('[data-tilt], .tilt');
    if (!card || card.contains(e.relatedTarget)) return;
    card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
  });
}
