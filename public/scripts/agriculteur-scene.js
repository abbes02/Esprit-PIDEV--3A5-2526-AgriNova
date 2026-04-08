import * as THREE from "/libs/three.module.min.js";
import {
  CSS2DRenderer,
  CSS2DObject,
} from "/libs/three_pkg/examples/jsm/renderers/CSS2DRenderer.js";

const { gsap, ScrollTrigger } = window;
const canvas = document.getElementById("bg");

if (!canvas || !gsap || !ScrollTrigger) {
  throw new Error("AgriNova scene dependencies are unavailable.");
}

const scene = new THREE.Scene();
scene.fog = new THREE.Fog(0x0f1a12, 10, 55);

const camera = new THREE.PerspectiveCamera(
  60,
  window.innerWidth / window.innerHeight,
  0.1,
  200
);
camera.position.set(0, 2.4, 12);

const renderer = new THREE.WebGLRenderer({
  canvas,
  antialias: true,
  alpha: true,
  powerPreference: "high-performance",
});
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.05;
renderer.outputColorSpace = THREE.SRGBColorSpace;

const labelRenderer = new CSS2DRenderer();
labelRenderer.setSize(window.innerWidth, window.innerHeight);
labelRenderer.domElement.className = "label-layer";
document.body.appendChild(labelRenderer.domElement);

const hemiLight = new THREE.HemisphereLight(0xdff5d2, 0x0f1a12, 0.55);
scene.add(hemiLight);

const sunLight = new THREE.DirectionalLight(0xfff1c1, 2.4);
sunLight.position.set(8, 12, 6);
sunLight.castShadow = true;
sunLight.shadow.mapSize.set(1024, 1024);
sunLight.shadow.camera.near = 1;
sunLight.shadow.camera.far = 50;
sunLight.shadow.camera.left = -15;
sunLight.shadow.camera.right = 15;
sunLight.shadow.camera.top = 15;
sunLight.shadow.camera.bottom = -15;
scene.add(sunLight);

const fillLight = new THREE.DirectionalLight(0x8bd68a, 0.6);
fillLight.position.set(-8, 4, -6);
scene.add(fillLight);

const sunGeometry = new THREE.SphereGeometry(2.2, 32, 32);
const sunMaterial = new THREE.MeshBasicMaterial({ color: 0xffd27a });
const sun = new THREE.Mesh(sunGeometry, sunMaterial);
sun.position.set(10, 12, -25);
scene.add(sun);
const sunTarget = new THREE.Vector3(10, 12, -25);

let isRainy = false;
let isSnowy = false;
let isCloudy = false;

function setSceneMood({ weather, timeOfDay }) {
  const isSunny = weather === "Ensoleille";
  const isRain = weather === "Pluvieux";
  const isSnow = weather === "Neigeux";
  const isCloud = weather === "Nuageux";
  const isMorning = timeOfDay === "Matin";
  const isSunset = timeOfDay === "Coucher de soleil";
  const isNight = timeOfDay === "Nuit";

  isRainy = isRain;
  isSnowy = isSnow;
  isCloudy = isCloud;
  rain.visible = isRainy;
  snow.visible = isSnowy;
  clouds.visible = isCloudy;

  if (isSnow) {
    groundMaterial.map = null;
    groundMaterial.color.set(0xe9f2f7);
    groundMaterial.roughness = 0.7;
    parcelMeshes.forEach((parcel) => {
      parcel.material.color.set(0xdfe9ee);
    });
  } else {
    groundMaterial.map = grassTexture;
    groundMaterial.color.set(0xffffff);
    groundMaterial.roughness = 0.95;
    parcelMeshes.forEach((parcel) => {
      parcel.material.color.copy(parcel.userData.baseColor || parcelBaseColor);
    });
  }
  groundMaterial.needsUpdate = true;

  if (isSunny && isMorning) {
    scene.fog.color.set(0x121d15);
    scene.fog.near = 8;
    scene.fog.far = 65;
    sunTarget.set(12, 14, -24);
    sunMaterial.color.set(0xfff0d2);
    sun.scale.setScalar(1.05);
    hemiLight.intensity = 0.9;
    hemiLight.color.set(0xe3f2e2);
    sunLight.intensity = 3.0;
    sunLight.color.set(0xfff3d4);
    fillLight.intensity = 0.8;
    renderer.toneMappingExposure = 1.18;
    scene.background = null;
    return;
  }

  if (isSunny && isSunset) {
    scene.fog.color.set(0x151f16);
    scene.fog.near = 10;
    scene.fog.far = 60;
    sunTarget.set(8, 7, -22);
    sunMaterial.color.set(0xffc08a);
    sun.scale.setScalar(1.15);
    hemiLight.intensity = 0.7;
    hemiLight.color.set(0xefe1c6);
    sunLight.intensity = 2.4;
    sunLight.color.set(0xffc58f);
    fillLight.intensity = 0.5;
    renderer.toneMappingExposure = 1.08;
    scene.background = null;
    return;
  }

  if (isNight) {
    scene.fog.color.set(0x0f1a12);
    scene.fog.near = 6;
    scene.fog.far = 45;
    sunTarget.set(6, 3, -20);
    sunMaterial.color.set(0x6fa6ff);
    sun.scale.setScalar(0.6);
    hemiLight.intensity = 0.35;
    hemiLight.color.set(0xc7d8e8);
    sunLight.intensity = 0.8;
    sunLight.color.set(0x9cb7ff);
    fillLight.intensity = 0.25;
    renderer.toneMappingExposure = 0.9;
    scene.background = null;
    return;
  }

  scene.fog.color.set(0x0f1a12);
  scene.fog.near = 10;
  scene.fog.far = 55;
  sunTarget.set(10, 10, -25);
  sunMaterial.color.set(isSunny ? 0xffd27a : 0xffc08a);
  sun.scale.setScalar(isSunny ? 1.0 : 0.9);
  hemiLight.intensity = 0.55;
  hemiLight.color.set(0xdff5d2);
  sunLight.intensity = isRain || isSnow ? 1.5 : 2.4;
  sunLight.color.set(isRain || isSnow ? 0xd8e2e8 : 0xfff1c1);
  fillLight.intensity = isRain || isSnow ? 0.35 : 0.6;
  renderer.toneMappingExposure = isRain || isSnow ? 0.95 : 1.05;
  scene.background = null;
}

function createTexture({ base, accent, size = 256, noise = 0.15 }) {
  const textureCanvas = document.createElement("canvas");
  textureCanvas.width = size;
  textureCanvas.height = size;
  const ctx = textureCanvas.getContext("2d");

  if (!ctx) {
    throw new Error("Canvas 2D context is unavailable.");
  }

  ctx.fillStyle = base;
  ctx.fillRect(0, 0, size, size);
  for (let i = 0; i < size * size * noise; i += 1) {
    const x = Math.random() * size;
    const y = Math.random() * size;
    const r = Math.random() * 2 + 0.5;
    ctx.fillStyle = accent;
    ctx.beginPath();
    ctx.arc(x, y, r, 0, Math.PI * 2);
    ctx.fill();
  }
  const texture = new THREE.CanvasTexture(textureCanvas);
  texture.wrapS = THREE.RepeatWrapping;
  texture.wrapT = THREE.RepeatWrapping;
  texture.repeat.set(10, 20);
  return texture;
}

function createCloudTexture(size = 256) {
  const textureCanvas = document.createElement("canvas");
  textureCanvas.width = size;
  textureCanvas.height = size;
  const ctx = textureCanvas.getContext("2d");

  if (!ctx) {
    throw new Error("Canvas 2D context is unavailable.");
  }

  const gradient = ctx.createRadialGradient(
    size * 0.5,
    size * 0.5,
    size * 0.2,
    size * 0.5,
    size * 0.5,
    size * 0.5
  );
  gradient.addColorStop(0, "rgba(255,255,255,0.9)");
  gradient.addColorStop(0.6, "rgba(255,255,255,0.45)");
  gradient.addColorStop(1, "rgba(255,255,255,0)");
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, size, size);
  return new THREE.CanvasTexture(textureCanvas);
}

const grassTexture = createTexture({
  base: "#3f6f3c",
  accent: "rgba(110, 170, 90, 0.35)",
  noise: 0.2,
});
const soilTexture = createTexture({
  base: "#5a3f2c",
  accent: "rgba(110, 76, 52, 0.45)",
  noise: 0.22,
});
const cloudTexture = createCloudTexture();

const groundMaterial = new THREE.MeshStandardMaterial({
  map: grassTexture,
  roughness: 0.95,
  metalness: 0.02,
});
const ground = new THREE.Mesh(new THREE.PlaneGeometry(90, 180), groundMaterial);
ground.rotation.x = -Math.PI / 2;
ground.position.y = -1.4;
ground.receiveShadow = true;
scene.add(ground);

const parcels = new THREE.Group();
const parcelMeshes = [];
const parcelMaterial = new THREE.MeshStandardMaterial({
  color: 0x4c8a43,
  roughness: 0.85,
  metalness: 0.05,
});
const parcelBaseColor = parcelMaterial.color.clone();
const parcelGeometry = new THREE.BoxGeometry(4, 0.28, 8);

const furrowMaterial = new THREE.MeshStandardMaterial({
  map: soilTexture,
  roughness: 0.9,
  metalness: 0.02,
});
const furrowGeometry = new THREE.BoxGeometry(0.6, 0.2, 60);

const parcelCountZ = 5;
const parcelCountX = 3;
for (let z = 0; z < parcelCountZ; z += 1) {
  for (let x = 0; x < parcelCountX; x += 1) {
    const parcel = new THREE.Mesh(parcelGeometry, parcelMaterial.clone());
    parcel.position.set((x - 1) * 6, -1.2, -z * 14);
    parcel.castShadow = true;
    parcel.receiveShadow = true;
    parcel.userData.isParcel = true;
    parcel.userData.baseColor = parcel.material.color.clone();
    parcel.userData.highlighted = false;
    parcel.material.transparent = true;
    parcel.material.opacity = 0;
    parcelMeshes.push(parcel);
    parcels.add(parcel);
  }
}

const sortedParcels = [...parcelMeshes].sort((a, b) => {
  if (a.position.z !== b.position.z) {
    return b.position.z - a.position.z;
  }
  return a.position.x - b.position.x;
});

const parcelLabels = Array.from(document.querySelectorAll(".parcel-card"));
const parcelZValues = parcelMeshes.map((parcel) => parcel.position.z);
const maxParcelZ = Math.max(...parcelZValues);
const minParcelZ = Math.min(...parcelZValues);

parcelLabels.forEach((label) => {
  const index = Number(label.dataset.parcelIndex);
  const parcel = Number.isFinite(index)
    ? sortedParcels[index] || parcelMeshes[index]
    : null;

  if (!parcel) {
    return;
  }

  const labelObject = new CSS2DObject(label);
  labelObject.position.set(0, 0.6, 0);
  const t =
    (maxParcelZ - parcel.position.z) / Math.max(1, maxParcelZ - minParcelZ);
  const scale = THREE.MathUtils.lerp(1.1, 0.7, t);
  labelObject.scale.setScalar(scale);
  parcel.add(labelObject);
});

for (let x = -1; x <= 1; x += 1) {
  const furrow = new THREE.Mesh(furrowGeometry, furrowMaterial);
  furrow.position.set(x * 6 + 2.6, -1.25, -30);
  furrow.receiveShadow = true;
  furrow.userData.isParcel = false;
  parcels.add(furrow);
}
scene.add(parcels);

const cropMaterial = new THREE.MeshStandardMaterial({
  color: 0x6fbe57,
  roughness: 0.75,
});
const cropGeometry = new THREE.ConeGeometry(0.12, 0.6, 6);
const cropCount = 220;
const crops = new THREE.InstancedMesh(cropGeometry, cropMaterial, cropCount);
const cropMatrix = new THREE.Matrix4();
const cropPosition = new THREE.Vector3();
const cropQuaternion = new THREE.Quaternion();
const cropScale = new THREE.Vector3(1, 1, 1);
const cropData = [];
let cropIndex = 0;

for (let row = 0; row < 8; row += 1) {
  const z = -row * 8;
  for (let col = 0; col < 10; col += 1) {
    const x = (col - 5) * 1.2;
    const zPos = z - Math.random() * 1.6;
    cropData.push({
      x,
      y: -0.7,
      z: zPos,
      phase: Math.random() * Math.PI * 2,
    });
    cropPosition.set(x, -0.7, zPos);
    cropQuaternion.setFromEuler(new THREE.Euler(0, 0, 0));
    cropMatrix.compose(cropPosition, cropQuaternion, cropScale);
    crops.setMatrixAt(cropIndex, cropMatrix);
    cropIndex += 1;
    if (cropIndex >= cropCount) {
      break;
    }
  }
  if (cropIndex >= cropCount) {
    break;
  }
}
scene.add(crops);

const treeTrunkMaterial = new THREE.MeshStandardMaterial({
  color: 0x5a3f2c,
  roughness: 0.9,
});
const treeLeafMaterial = new THREE.MeshStandardMaterial({
  color: 0x3f8f3f,
  roughness: 0.7,
});

for (let i = 0; i < 14; i += 1) {
  const trunk = new THREE.Mesh(
    new THREE.CylinderGeometry(0.15, 0.22, 1.6, 8),
    treeTrunkMaterial
  );
  const leaves = new THREE.Mesh(
    new THREE.ConeGeometry(0.7, 1.8, 10),
    treeLeafMaterial
  );
  trunk.position.set(
    (Math.random() - 0.5) * 24,
    -0.3,
    -8 - Math.random() * 70
  );
  leaves.position.set(
    trunk.position.x,
    trunk.position.y + 1.4,
    trunk.position.z
  );
  trunk.castShadow = true;
  leaves.castShadow = true;
  scene.add(trunk, leaves);
}

const dustGeometry = new THREE.SphereGeometry(0.03, 6, 6);
const dustMaterial = new THREE.MeshBasicMaterial({
  color: 0xffffff,
  opacity: 0.6,
  transparent: true,
});
const dustCount = 80;
const dust = new THREE.InstancedMesh(dustGeometry, dustMaterial, dustCount);
const dustMatrix = new THREE.Matrix4();

for (let i = 0; i < dustCount; i += 1) {
  dustMatrix.setPosition(
    (Math.random() - 0.5) * 20,
    Math.random() * 6 + 1,
    -Math.random() * 80
  );
  dust.setMatrixAt(i, dustMatrix);
}
scene.add(dust);

const cloudMaterial = new THREE.MeshBasicMaterial({
  map: cloudTexture,
  transparent: true,
  opacity: 0.35,
  depthWrite: false,
});
const cloudGeometry = new THREE.PlaneGeometry(9, 5.5);
const clouds = new THREE.Group();
const cloudData = [];
const cloudCount = 14;

for (let i = 0; i < cloudCount; i += 1) {
  const cloud = new THREE.Mesh(cloudGeometry, cloudMaterial.clone());
  cloud.material.opacity = 0.18 + Math.random() * 0.28;
  cloud.position.set(
    (Math.random() - 0.5) * 34,
    7.5 + Math.random() * 5,
    -8 - Math.random() * 70
  );
  cloud.rotation.y = Math.random() * Math.PI;
  cloud.scale.setScalar(0.9 + Math.random() * 1.2);
  cloudData.push({
    mesh: cloud,
    speed: 0.35 + Math.random() * 0.55,
    drift: 0.2 + Math.random() * 0.4,
    phase: Math.random() * Math.PI * 2,
  });
  clouds.add(cloud);
}
clouds.visible = false;
scene.add(clouds);

const rainCount = 800;
const rainPositions = new Float32Array(rainCount * 3);
const rainSpeeds = new Float32Array(rainCount);
for (let i = 0; i < rainCount; i += 1) {
  const idx = i * 3;
  rainPositions[idx] = (Math.random() - 0.5) * 24;
  rainPositions[idx + 1] = Math.random() * 10 + 2;
  rainPositions[idx + 2] = -Math.random() * 80;
  rainSpeeds[i] = 7 + Math.random() * 4;
}
const rainGeometry = new THREE.BufferGeometry();
rainGeometry.setAttribute(
  "position",
  new THREE.BufferAttribute(rainPositions, 3)
);
const rainMaterial = new THREE.PointsMaterial({
  color: 0x9fb9cf,
  size: 0.06,
  transparent: true,
  opacity: 0.7,
});
const rain = new THREE.Points(rainGeometry, rainMaterial);
rain.visible = false;
scene.add(rain);

const snowCount = 500;
const snowPositions = new Float32Array(snowCount * 3);
const snowSpeeds = new Float32Array(snowCount);
for (let i = 0; i < snowCount; i += 1) {
  const idx = i * 3;
  snowPositions[idx] = (Math.random() - 0.5) * 24;
  snowPositions[idx + 1] = Math.random() * 10 + 2;
  snowPositions[idx + 2] = -Math.random() * 80;
  snowSpeeds[i] = 1.2 + Math.random() * 1.4;
}
const snowGeometry = new THREE.BufferGeometry();
snowGeometry.setAttribute(
  "position",
  new THREE.BufferAttribute(snowPositions, 3)
);
const snowMaterial = new THREE.PointsMaterial({
  color: 0xe9f3ff,
  size: 0.12,
  transparent: true,
  opacity: 0.85,
});
const snow = new THREE.Points(snowGeometry, snowMaterial);
snow.visible = false;
scene.add(snow);

const raycaster = new THREE.Raycaster();
const pointer = new THREE.Vector2();
let hoveredParcel = null;

function updatePointer(event) {
  const { clientX, clientY } = event;
  pointer.x = (clientX / window.innerWidth) * 2 - 1;
  pointer.y = -(clientY / window.innerHeight) * 2 + 1;
}

window.addEventListener("pointermove", updatePointer);
window.addEventListener("click", () => {
  if (!hoveredParcel) {
    return;
  }

  hoveredParcel.userData.highlighted = !hoveredParcel.userData.highlighted;
  hoveredParcel.material.color.set(
    hoveredParcel.userData.highlighted
      ? 0x9ad27d
      : hoveredParcel.userData.baseColor
  );
});

const clock = new THREE.Clock();
let rafId = null;
let isRunning = true;

function animate() {
  if (!isRunning) {
    return;
  }

  const delta = clock.getDelta();
  const elapsed = clock.getElapsedTime();

  dust.rotation.y += delta * 0.02;
  dust.position.z = Math.sin(elapsed * 0.3) * 0.6;

  if (isRainy) {
    const gust = (Math.sin(elapsed * 1.6) + 1.2) * 50;
    for (let i = 0; i < rainCount; i += 1) {
      const idx = i * 3;
      rainPositions[idx + 1] -= delta * (rainSpeeds[i] * 50 + gust);
      if (rainPositions[idx + 1] < -1) {
        rainPositions[idx + 1] = Math.random() * 10 + 6;
        rainPositions[idx] = (Math.random() - 0.5) * 24;
        rainPositions[idx + 2] = -Math.random() * 80;
      }
    }
    rainGeometry.attributes.position.needsUpdate = true;
  }

  if (isSnowy) {
    const drift = Math.sin(elapsed * 0.4) * 0.3;
    for (let i = 0; i < snowCount; i += 1) {
      const idx = i * 3;
      snowPositions[idx + 1] -= delta * (snowSpeeds[i] * 70);
      snowPositions[idx] += drift * delta;
      if (snowPositions[idx + 1] < -1) {
        snowPositions[idx + 1] = Math.random() * 10 + 6;
        snowPositions[idx] = (Math.random() - 0.5) * 24;
        snowPositions[idx + 2] = -Math.random() * 80;
      }
    }
    snowGeometry.attributes.position.needsUpdate = true;
  }

  if (isCloudy) {
    for (let i = 0; i < cloudData.length; i += 1) {
      const cloud = cloudData[i];
      const bob = Math.sin(elapsed * 0.4 + cloud.phase) * cloud.drift;
      const depthFactor = THREE.MathUtils.mapLinear(
        cloud.mesh.position.z,
        -80,
        -8,
        0.6,
        1.1
      );
      cloud.mesh.position.x += delta * cloud.speed * depthFactor;
      cloud.mesh.position.y = 8 + bob;
      if (cloud.mesh.position.x > 20) {
        cloud.mesh.position.x = -20;
        cloud.mesh.position.z = -8 - Math.random() * 70;
        cloud.mesh.position.y = 7.5 + Math.random() * 5;
      }
    }
  }

  for (let i = 0; i < cropData.length; i += 1) {
    const data = cropData[i];
    const sway = Math.sin(elapsed * 1.6 + data.phase) * 0.08;
    cropPosition.set(data.x, data.y, data.z);
    cropQuaternion.setFromEuler(
      new THREE.Euler(sway * 0.6, sway * 0.2, sway)
    );
    cropMatrix.compose(cropPosition, cropQuaternion, cropScale);
    crops.setMatrixAt(i, cropMatrix);
  }
  crops.instanceMatrix.needsUpdate = true;

  raycaster.setFromCamera(pointer, camera);
  const intersects = raycaster
    .intersectObjects(parcels.children, false)
    .filter((hit) => hit.object.userData.isParcel);

  if (intersects.length) {
    const target = intersects[0].object;
    if (hoveredParcel && hoveredParcel !== target) {
      hoveredParcel.material.color.copy(
        hoveredParcel.userData.highlighted
          ? new THREE.Color(0x9ad27d)
          : hoveredParcel.userData.baseColor
      );
    }
    hoveredParcel = target;
    hoveredParcel.material.color.set(
      hoveredParcel.userData.highlighted ? 0x9ad27d : 0x6fbf60
    );
  } else if (hoveredParcel) {
    hoveredParcel.material.color.copy(
      hoveredParcel.userData.highlighted
        ? new THREE.Color(0x9ad27d)
        : hoveredParcel.userData.baseColor
    );
    hoveredParcel = null;
  }

  renderer.render(scene, camera);
  labelRenderer.render(scene, camera);
  rafId = requestAnimationFrame(animate);
}

animate();

function onResize() {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
  labelRenderer.setSize(window.innerWidth, window.innerHeight);
}
window.addEventListener("resize", onResize);

document.addEventListener("visibilitychange", () => {
  if (document.hidden) {
    isRunning = false;
    if (rafId) {
      cancelAnimationFrame(rafId);
      rafId = null;
    }
  } else if (!isRunning) {
    isRunning = true;
    clock.start();
    animate();
  }
});

gsap.registerPlugin(ScrollTrigger);

const lookAtTarget = { x: 0, y: -0.6, z: -36 };
const walkTimeline = gsap.timeline({
  scrollTrigger: {
    trigger: ".agri-page",
    start: "top top",
    end: "bottom bottom",
    scrub: 1,
  },
});

walkTimeline
  .to(camera.position, { z: -82, y: 1.5, x: 0.8, ease: "none" }, 0)
  .to(camera.rotation, { y: 0.08, x: -0.03, ease: "none" }, 0)
  .to(lookAtTarget, { z: -104, x: 0.4, ease: "none" }, 0);

function splitText(element) {
  const text = element.textContent.trim();
  element.textContent = "";
  for (const char of text) {
    const span = document.createElement("span");
    span.className = "char";
    span.textContent = char === " " ? "\u00A0" : char;
    element.appendChild(span);
  }
}

document.querySelectorAll(".reveal-text").forEach((element) => splitText(element));

gsap.fromTo(
  ".hero .reveal-text .char",
  { opacity: 0, y: 18 },
  {
    opacity: 1,
    y: 0,
    duration: 0.6,
    ease: "power3.out",
    stagger: 0.02,
    delay: 0.1,
  }
);

gsap.fromTo(
  ".info .reveal-text .char",
  { opacity: 0, y: 18 },
  {
    opacity: 1,
    y: 0,
    duration: 0.5,
    ease: "power3.out",
    stagger: 0.015,
    scrollTrigger: {
      trigger: ".info",
      start: "top 70%",
    },
  }
);

gsap.fromTo(
  ".space-card",
  { opacity: 0, y: 40, z: -160 },
  {
    opacity: 1,
    y: 0,
    z: 0,
    duration: 1.1,
    ease: "power3.out",
    stagger: 0.25,
    scrollTrigger: {
      trigger: ".info",
      start: "top 75%",
    },
  }
);

document.querySelectorAll(".parallax").forEach((element) => {
  const speed = Number(element.dataset.speed || 0.3);
  gsap.to(element, {
    y: -60 * speed,
    z: 220 * speed,
    ease: "none",
    scrollTrigger: {
      trigger: ".agri-page",
      start: "top top",
      end: "bottom bottom",
      scrub: true,
    },
  });
});

let cameraBob = 0;
function updateLookAt() {
  const t = clock.getElapsedTime();
  const bob = Math.sin(t * 3.2) * 0.03;
  camera.position.y += bob - cameraBob;
  cameraBob = bob;
  camera.lookAt(lookAtTarget.x, lookAtTarget.y, lookAtTarget.z);

  sun.position.y += Math.sin(t * 0.4) * 0.0006;

  const camZ = camera.position.z;
  parcelMeshes.forEach((parcel) => {
    const distance = camZ - parcel.position.z;
    const reveal = THREE.MathUtils.smoothstep(distance, 4, 22);
    parcel.material.opacity = Math.min(
      1,
      Math.max(parcel.material.opacity, reveal)
    );
  });
}
gsap.ticker.add(updateLookAt);

gsap.fromTo(
  ".hero-copy, .hero-panel",
  { z: -220, opacity: 0.35 },
  {
    z: 0,
    opacity: 1,
    ease: "power3.out",
    duration: 1.2,
    stagger: 0.15,
  }
);

gsap.fromTo(
  ".info-card",
  { z: -180, y: 60, opacity: 0 },
  {
    z: 0,
    y: 0,
    opacity: 1,
    duration: 1,
    ease: "power3.out",
    stagger: 0.16,
    scrollTrigger: {
      trigger: ".info",
      start: "top 80%",
    },
  }
);

const timeEl = document.getElementById("time");
const weatherSelect = document.getElementById("weather");
const timeSelect = document.getElementById("timeOfDay");

function applyWeatherTime() {
  const weather = weatherSelect ? weatherSelect.value.trim() : "Ensoleille";
  const timeOfDay = timeSelect ? timeSelect.value.trim() : "Matin";
  setSceneMood({ weather, timeOfDay });
  gsap.to(sun.position, {
    duration: 1.4,
    x: sunTarget.x,
    y: sunTarget.y,
    z: sunTarget.z,
    ease: "power2.out",
  });
  gsap.to(sunLight.position, {
    duration: 1.4,
    x: sunTarget.x * 0.7,
    y: sunTarget.y + 1.5,
    z: sunTarget.z * 0.6,
    ease: "power2.out",
  });
}

if (weatherSelect) {
  weatherSelect.addEventListener("change", applyWeatherTime);
}
if (timeSelect) {
  timeSelect.addEventListener("change", applyWeatherTime);
}
applyWeatherTime();

function updateTime() {
  if (!timeEl) {
    return;
  }
  const now = new Date();
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  timeEl.textContent = `${hours}:${minutes}`;
}

updateTime();
setInterval(updateTime, 1000);
