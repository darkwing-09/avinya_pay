/**
 * GoCrazy Fullscreen 3D Neon Tube Background with Cursor Follow & Lissajous Idle
 * Powered by threejs-components (tubes1)
 */
import TubesCursor from 'threejs-components/build/cursors/tubes1.min.js';

const PALETTES = [
  // Original demo: magenta, green, violet, lit with lime, orange, hot pink and sky
  { tubes: ['#f967fb', '#53bc28', '#6958d5'], lights: ['#83f36e', '#fe8a2e', '#ff008a', '#60aed5'] },
  // Brand sky blue with pink and amber, lit with violet, coral and cyan
  { tubes: ['#38bdf8', '#f472b6', '#facc15'], lights: ['#38bdf8', '#c084fc', '#ff5f8f', '#ffd166'] },
  // Aurora: mint, violet, rose, lit with cyan, orchid, lime and indigo
  { tubes: ['#22e3b4', '#8b5cf6', '#ff4d6d'], lights: ['#00e5ff', '#ff6ec7', '#b4ff39', '#7c4dff'] },
  // Synthwave: orange, hot pink, electric blue, lit with gold, aqua, red and purple
  { tubes: ['#ff7a1a', '#ff2e97', '#4f7cff'], lights: ['#ffd23f', '#00f0ff', '#ff3864', '#9d4edd'] },
];

const SEGMENTS = { min: 105, max: 135 };
const SEGMENTS_TOUCH = { min: 74, max: 94 };
const LAG_IDLE = 0.0091;
const LAG_FOLLOW = 0.011;
const IDLE_SPEED_X = 0.9;
const IDLE_SPEED_Y = IDLE_SPEED_X * 2;

function followFactor(delta, lag) {
  const dt = Math.min(0.1, Math.max(1 / 240, delta));
  return dt / (lag + dt);
}

function idleRadii(width, height) {
  const rx = Math.min(width * 0.45, 650);
  const ry = Math.min(height * 0.35, 380);
  return { rx, ry };
}

function hasWebGL() {
  try {
    const c = document.createElement('canvas');
    return !!(c.getContext('webgl2') || c.getContext('webgl'));
  } catch {
    return false;
  }
}

function isInteractive(el) {
  return el instanceof Element && !!el.closest('a, button, input, select, textarea, label, [role="dialog"], [role="tab"], summary');
}

export function initGoCrazyBackground() {
  const canvas = document.getElementById('gocrazy-tubes-canvas');
  if (!canvas || !hasWebGL()) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const coarse = window.matchMedia('(pointer: coarse)').matches;
  let paletteIdx = 0;
  const p = PALETTES[paletteIdx];
  const { rx, ry } = idleRadii(window.innerWidth, window.innerHeight);

  try {
    // Canvas fills entire window
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const app = TubesCursor(canvas, {
      bloom: { threshold: 0, strength: 1.25, radius: 0.6 },
      tubes: {
        count: coarse ? 12 : 18,
        colors: p.tubes,
        lights: { intensity: 220, colors: p.lights },
        minTubularSegments: (coarse ? SEGMENTS_TOUCH : SEGMENTS).min,
        maxTubularSegments: (coarse ? SEGMENTS_TOUCH : SEGMENTS).max,
        lerp: followFactor(1 / 60, LAG_IDLE),
      },
      sleepRadiusX: rx,
      sleepRadiusY: ry,
      sleepTimeScale1: IDLE_SPEED_X,
      sleepTimeScale2: IDLE_SPEED_Y,
    });

    let following = false;
    let lag = LAG_IDLE;
    const render = app.three.onBeforeRender;

    // Custom render loop: keeps tubes following cursor across full window
    app.three.onBeforeRender = (time) => {
      const target = following ? LAG_FOLLOW : LAG_IDLE;
      lag += (target - lag) * Math.min(1, time.delta * 2.5);
      app.tubes.options.lerp = followFactor(time.delta, lag);
      render(time);
    };

    app.three.minPixelRatio = 1;
    app.three.maxPixelRatio = Math.min(1.5, window.devicePixelRatio || 1);
    app.three.resize();

    // Ensure opacity is visible
    canvas.style.opacity = '0.9';

    // Track mouse everywhere on the viewport
    const onMove = (e) => {
      if (e.pointerType === 'touch') return;
      following = true;
    };
    const onLeave = () => {
      following = false;
    };
    const onResize = () => {
      const { rx, ry } = idleRadii(window.innerWidth, window.innerHeight);
      app.options.sleepRadiusX = rx;
      app.options.sleepRadiusY = ry;
      app.three.resize();
    };

    window.addEventListener('pointermove', onMove, { passive: true });
    document.documentElement.addEventListener('pointerleave', onLeave);
    window.addEventListener('blur', onLeave);
    window.addEventListener('resize', onResize);

    // Click anywhere on non-interactive ground cycles palette
    document.addEventListener('click', (e) => {
      if (isInteractive(e.target)) return;
      paletteIdx = (paletteIdx + 1) % PALETTES.length;
      const nextPalette = PALETTES[paletteIdx];
      app.tubes.setColors(nextPalette.tubes);
      app.tubes.setLightsColors(nextPalette.lights);
    });
  } catch (err) {
    console.warn('GoCrazy background tubes initialization failed:', err);
  }
}
