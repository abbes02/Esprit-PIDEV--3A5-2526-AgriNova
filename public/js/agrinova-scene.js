import * as THREE from "../libs/three.module.min.js";
import {
  CSS2DRenderer,
  CSS2DObject,
} from "../libs/three_pkg/package/examples/jsm/renderers/CSS2DRenderer.js";

// ─── Canvas & Scene ───────────────────────────────────────────────────────────
const canvas = document.getElementById("bg");
const scene  = new THREE.Scene();
scene.fog    = new THREE.Fog(0x0f1a12, 10, 55);

const camera = new THREE.PerspectiveCamera(
  60, window.innerWidth / window.innerHeight, 0.1, 200
);
camera.position.set(0, 2.4, 8);

const renderer = new THREE.WebGLRenderer({
  canvas, antialias: true, alpha: true,
  powerPreference: "high-performance",
});
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.shadowMap.enabled  = true;
renderer.shadowMap.type     = THREE.PCFSoftShadowMap;
renderer.toneMapping        = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.05;
renderer.outputColorSpace   = THREE.SRGBColorSpace;

// ─── CSS2D Label Renderer ─────────────────────────────────────────────────────
const labelRenderer = new CSS2DRenderer();
labelRenderer.setSize(window.innerWidth, window.innerHeight);
labelRenderer.domElement.className = "label-layer";
document.body.appendChild(labelRenderer.domElement);

// ─── Lighting ─────────────────────────────────────────────────────────────────
const hemiLight = new THREE.HemisphereLight(0xdff5d2, 0x0f1a12, 0.55);
scene.add(hemiLight);

const sunLight = new THREE.DirectionalLight(0xfff1c1, 2.4);
sunLight.position.set(8, 12, 6);
sunLight.castShadow = true;
sunLight.shadow.mapSize.set(1024, 1024);
sunLight.shadow.camera.near   = 1;
sunLight.shadow.camera.far    = 50;
sunLight.shadow.camera.left   = -15;
sunLight.shadow.camera.right  = 15;
sunLight.shadow.camera.top    = 15;
sunLight.shadow.camera.bottom = -15;
scene.add(sunLight);

const fillLight = new THREE.DirectionalLight(0x8bd68a, 0.6);
fillLight.position.set(-8, 4, -6);
scene.add(fillLight);

// ─── Sun sphere ───────────────────────────────────────────────────────────────
const sunMaterial = new THREE.MeshBasicMaterial({ color: 0xffd27a });
const sun = new THREE.Mesh(new THREE.SphereGeometry(2.2, 32, 32), sunMaterial);
sun.position.set(10, 12, -25);
scene.add(sun);
const sunTarget = new THREE.Vector3(10, 12, -25);

// ─── Ground ───────────────────────────────────────────────────────────────────
const grassTexture = (() => {
  const size = 256;
  const data = new Uint8Array(size * size * 3);
  for (let i = 0; i < size * size; i++) {
    const v = 80 + Math.random() * 40;
    data[i * 3]     = 30 + v * 0.3;
    data[i * 3 + 1] = 60 + v * 0.6;
    data[i * 3 + 2] = 20 + v * 0.2;
  }
  const tex = new THREE.DataTexture(data, size, size, THREE.RGBFormat);
  tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
  tex.repeat.set(8, 8);
  tex.needsUpdate = true;
  return tex;
})();

const groundMaterial = new THREE.MeshStandardMaterial({
  map: grassTexture, roughness: 0.95, metalness: 0.0,
});
const ground = new THREE.Mesh(
  new THREE.PlaneGeometry(60, 60, 1, 1), groundMaterial
);
ground.rotation.x = -Math.PI / 2;
ground.receiveShadow = true;
scene.add(ground);

// ─── Crop rows ────────────────────────────────────────────────────────────────
const parcelBaseColor = new THREE.Color(0x3a7d44);
const parcelMeshes   = [];

function makeCropRow(x, z, color) {
  const mat  = new THREE.MeshStandardMaterial({ color, roughness: 0.85 });
  const mesh = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.4, 8), mat);
  mesh.position.set(x, 0.2, z);
  mesh.castShadow    = true;
  mesh.receiveShadow = true;
  mesh.userData.baseColor = new THREE.Color(color);
  scene.add(mesh);
  parcelMeshes.push(mesh);
}

const rowColors = [0x3a7d44, 0x4a8f55, 0x2e6b38, 0x56a060, 0x3a7d44];
for (let i = -4; i <= 4; i += 2) {
  makeCropRow(i, -2, rowColors[Math.abs(i / 2) % rowColors.length]);
}

// ─── Trees ────────────────────────────────────────────────────────────────────
function makeTree(x, z, scale = 1) {
  const g = new THREE.Group();
  const trunk = new THREE.Mesh(
    new THREE.CylinderGeometry(0.08, 0.12, 0.8, 6),
    new THREE.MeshStandardMaterial({ color: 0x5c3d1e, roughness: 0.9 })
  );
  trunk.position.y = 0.4;
  trunk.castShadow = true;
  g.add(trunk);

  const foliage = new THREE.Mesh(
    new THREE.ConeGeometry(0.55, 1.4, 7),
    new THREE.MeshStandardMaterial({ color: 0x2d6a2f, roughness: 0.8 })
  );
  foliage.position.y = 1.5;
  foliage.castShadow = true;
  g.add(foliage);

  g.position.set(x, 0, z);
  g.scale.setScalar(scale);
  scene.add(g);
}

makeTree(-7, -5, 1.2);
makeTree(-6, -1, 0.9);
makeTree(7, -4, 1.1);
makeTree(6, 0, 1.0);
makeTree(-8, 2, 0.85);
makeTree(8, 2, 1.0);

// ─── Barn ─────────────────────────────────────────────────────────────────────
function makeBarn() {
  const g = new THREE.Group();
  const body = new THREE.Mesh(
    new THREE.BoxGeometry(2.4, 1.6, 3.2),
    new THREE.MeshStandardMaterial({ color: 0x8b2e2e, roughness: 0.85 })
  );
  body.position.y = 0.8;
  body.castShadow = true;
  g.add(body);

  const roof = new THREE.Mesh(
    new THREE.CylinderGeometry(0, 1.6, 1.0, 4),
    new THREE.MeshStandardMaterial({ color: 0x5a1a1a, roughness: 0.9 })
  );
  roof.position.y = 2.1;
  roof.rotation.y = Math.PI / 4;
  roof.castShadow = true;
  g.add(roof);

  g.position.set(-5, 0, 2);
  scene.add(g);

  // Label
  const div = document.createElement("div");
  div.className  = "scene-label";
  div.textContent = "Grange";
  const label = new CSS2DObject(div);
  label.position.set(0, 3.2, 0);
  g.add(label);
}
makeBarn();

// ─── Silo ─────────────────────────────────────────────────────────────────────
function makeSilo() {
  const g = new THREE.Group();
  const body = new THREE.Mesh(
    new THREE.CylinderGeometry(0.55, 0.55, 3.5, 12),
    new THREE.MeshStandardMaterial({ color: 0xc8b560, roughness: 0.7, metalness: 0.2 })
  );
  body.position.y = 1.75;
  body.castShadow = true;
  g.add(body);

  const cap = new THREE.Mesh(
    new THREE.ConeGeometry(0.6, 0.7, 12),
    new THREE.MeshStandardMaterial({ color: 0x8a7a30, roughness: 0.8 })
  );
  cap.position.y = 3.85;
  g.add(cap);

  g.position.set(5.5, 0, 1.5);
  scene.add(g);

  const div = document.createElement("div");
  div.className  = "scene-label";
  div.textContent = "Silo";
  const label = new CSS2DObject(div);
  label.position.set(0, 4.8, 0);
  g.add(label);
}
makeSilo();

// ─── Fence ────────────────────────────────────────────────────────────────────
function makeFence(x1, x2, z) {
  const mat = new THREE.MeshStandardMaterial({ color: 0x8b6914, roughness: 0.9 });
  for (let x = x1; x <= x2; x += 1.2) {
    const post = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.7, 0.08), mat);
    post.position.set(x, 0.35, z);
    post.castShadow = true;
    scene.add(post);
  }
  const rail = new THREE.Mesh(new THREE.BoxGeometry(x2 - x1, 0.06, 0.06), mat);
  rail.position.set((x1 + x2) / 2, 0.55, z);
  scene.add(rail);
}
makeFence(-9, 9, 4.5);
makeFence(-9, 9, -8);

// ─── Particles: Rain ──────────────────────────────────────────────────────────
const rainGeo = new THREE.BufferGeometry();
const rainCount = 1800;
const rainPos = new Float32Array(rainCount * 3);
for (let i = 0; i < rainCount; i++) {
  rainPos[i * 3]     = (Math.random() - 0.5) * 40;
  rainPos[i * 3 + 1] = Math.random() * 20;
  rainPos[i * 3 + 2] = (Math.random() - 0.5) * 40;
}
rainGeo.setAttribute("position", new THREE.BufferAttribute(rainPos, 3));
const rain = new THREE.Points(
  rainGeo,
  new THREE.PointsMaterial({ color: 0x99ccff, size: 0.07, transparent: true, opacity: 0.6 })
);
rain.visible = false;
scene.add(rain);

// ─── Particles: Snow ──────────────────────────────────────────────────────────
const snowGeo = new THREE.BufferGeometry();
const snowCount = 1200;
const snowPos = new Float32Array(snowCount * 3);
for (let i = 0; i < snowCount; i++) {
  snowPos[i * 3]     = (Math.random() - 0.5) * 40;
  snowPos[i * 3 + 1] = Math.random() * 20;
  snowPos[i * 3 + 2] = (Math.random() - 0.5) * 40;
}
snowGeo.setAttribute("position", new THREE.BufferAttribute(snowPos, 3));
const snow = new THREE.Points(
  snowGeo,
  new THREE.PointsMaterial({ color: 0xffffff, size: 0.12, transparent: true, opacity: 0.85 })
);
snow.visible = false;
scene.add(snow);

// ─── Clouds ───────────────────────────────────────────────────────────────────
const clouds = new THREE.Group();
function makeCloud(x, y, z) {
  const mat = new THREE.MeshStandardMaterial({ color: 0xd0d8e0, roughness: 1, transparent: true, opacity: 0.88 });
  [0, 0.6, -0.6, 0.3, -0.3].forEach((ox, i) => {
    const s = 0.5 + Math.random() * 0.4;
    const puff = new THREE.Mesh(new THREE.SphereGeometry(s, 7, 7), mat);
    puff.position.set(ox * 1.2, (i === 0 ? 0 : -0.2 + Math.random() * 0.3), 0);
    clouds.add(puff);
  });
  clouds.position.set(x, y, z);
}
makeCloud(-4, 7, -10);
makeCloud(3, 8, -14);
makeCloud(0, 6.5, -8);
clouds.visible = false;
scene.add(clouds);

// ─── Weather / Time state ─────────────────────────────────────────────────────
let isRainy = false;
let isSnowy = false;
let isCloudy = false;

function setSceneMood({ weather, timeOfDay }) {
  const isSunny  = weather   === "Ensoleillé";
  const isRain   = weather   === "Pluvieux";
  const isSnow   = weather   === "Neigeux";
  const isCloud  = weather   === "Nuageux";
  const isMorning = timeOfDay === "Matin";
  const isSunset  = timeOfDay === "Coucher de soleil";
  const isNight   = timeOfDay === "Nuit";

  isRainy = isRain; isSnowy = isSnow; isCloudy = isCloud;
  rain.visible   = isRainy;
  snow.visible   = isSnowy;
  clouds.visible = isCloudy || isRain;

  if (isSnow) {
    groundMaterial.map = null;
    groundMaterial.color.set(0xe9f2f7);
    groundMaterial.roughness = 0.7;
    parcelMeshes.forEach(p => p.material.color.set(0xdfe9ee));
  } else {
    groundMaterial.map = grassTexture;
    groundMaterial.color.set(0xffffff);
    groundMaterial.roughness = 0.95;
    parcelMeshes.forEach(p => p.material.color.copy(p.userData.baseColor || parcelBaseColor));
  }
  groundMaterial.needsUpdate = true;

  if (isSunny && isMorning) {
    scene.fog.color.set(0x121d15); scene.fog.near = 8; scene.fog.far = 65;
    sunTarget.set(12, 14, -24); sunMaterial.color.set(0xfff0d2); sun.scale.setScalar(1.05);
    hemiLight.intensity = 0.9; hemiLight.color.set(0xe3f2e2);
    sunLight.intensity = 3.0; sunLight.color.set(0xfff3d4);
    fillLight.intensity = 0.8; renderer.toneMappingExposure = 1.18;
    return;
  }
  if (isSunny && isSunset) {
    scene.fog.color.set(0x151f16); scene.fog.near = 10; scene.fog.far = 60;
    sunTarget.set(8, 7, -22); sunMaterial.color.set(0xffc08a); sun.scale.setScalar(1.15);
    hemiLight.intensity = 0.7; hemiLight.color.set(0xefe1c6);
    sunLight.intensity = 2.4; sunLight.color.set(0xffc58f);
    fillLight.intensity = 0.5; renderer.toneMappingExposure = 1.08;
    return;
  }
  if (isNight) {
    scene.fog.color.set(0x0f1a12); scene.fog.near = 6; scene.fog.far = 45;
    sunTarget.set(6, 3, -20); sunMaterial.color.set(0x6fa6ff); sun.scale.setScalar(0.6);
    hemiLight.intensity = 0.35; hemiLight.color.set(0xc7d8e8);
    sunLight.intensity = 0.8; sunLight.color.set(0x9cb7ff);
    fillLight.intensity = 0.25; renderer.toneMappingExposure = 0.9;
    return;
  }
  // Default
  scene.fog.color.set(0x0f1a12); scene.fog.near = 10; scene.fog.far = 55;
  sunTarget.set(10, 10, -25);
  sunMaterial.color.set(isSunny ? 0xffd27a : 0xffc08a);
  sun.scale.setScalar(isSunny ? 1.0 : 0.9);
  hemiLight.intensity = 0.55; hemiLight.color.set(0xdff5d2);
  sunLight.intensity  = (isRain || isSnow) ? 1.2 : 2.4;
  sunLight.color.set(0xfff1c1);
  fillLight.intensity = isRain ? 0.3 : 0.6;
  renderer.toneMappingExposure = isNight ? 0.9 : 1.05;
}

// ─── UI Controls ──────────────────────────────────────────────────────────────
let currentWeather = "Ensoleillé";
let currentTime    = "Jour";

document.querySelectorAll("[data-weather]").forEach(btn => {
  btn.addEventListener("click", () => {
    currentWeather = btn.dataset.weather;
    document.querySelectorAll("[data-weather]").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    setSceneMood({ weather: currentWeather, timeOfDay: currentTime });
  });
});

document.querySelectorAll("[data-time]").forEach(btn => {
  btn.addEventListener("click", () => {
    currentTime = btn.dataset.time;
    document.querySelectorAll("[data-time]").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    setSceneMood({ weather: currentWeather, timeOfDay: currentTime });
  });
});

// ─── Animate ──────────────────────────────────────────────────────────────────
const clock = new THREE.Clock();

function animateParticles(geo, posArray, count, speedY, wrapY = 20) {
  for (let i = 0; i < count; i++) {
    posArray[i * 3 + 1] -= speedY;
    if (posArray[i * 3 + 1] < -1) posArray[i * 3 + 1] = wrapY;
  }
  geo.attributes.position.needsUpdate = true;
}

function animate() {
  requestAnimationFrame(animate);
  const t = clock.getElapsedTime();

  // Gentle camera sway
  camera.position.x = Math.sin(t * 0.08) * 0.4;
  camera.position.y = 2.4 + Math.sin(t * 0.12) * 0.12;
  camera.lookAt(0, 0.5, 0);

  // Sun drift
  sun.position.lerp(sunTarget, 0.02);

  // Particles
  if (isRainy) animateParticles(rainGeo, rainPos, rainCount, 0.18);
  if (isSnowy) animateParticles(snowGeo, snowPos, snowCount, 0.04);

  // Cloud drift
  if (isCloudy || isRainy) clouds.position.x = Math.sin(t * 0.05) * 2;

  renderer.render(scene, camera);
  labelRenderer.render(scene, camera);
}

animate();
setSceneMood({ weather: "Ensoleillé", timeOfDay: "Jour" });

// ─── Resize ───────────────────────────────────────────────────────────────────
window.addEventListener("resize", () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
  labelRenderer.setSize(window.innerWidth, window.innerHeight);
});
