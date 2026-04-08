import * as THREE from "three";
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import {
  CSS2DObject,
  CSS2DRenderer,
} from "three/examples/jsm/renderers/CSS2DRenderer.js";

gsap.registerPlugin(ScrollTrigger);

const pageId = document.body.dataset.page || "default";
const canvas = document.getElementById("bg");
const homepageUi = document.querySelector(".homepage-ui");

if (!canvas) {
  throw new Error("Scene canvas is missing.");
}

const scene = new THREE.Scene();
scene.fog = new THREE.Fog(0x0f1a12, 10, 55);

const camera = new THREE.PerspectiveCamera(
  60,
  window.innerWidth / window.innerHeight,
  0.1,
  200
);
camera.position.set(0, pageId === "homepage" ? 2.4 : 3.1, pageId === "homepage" ? 8 : 10);

const renderer = new THREE.WebGLRenderer({
  canvas,
  antialias: true,
  alpha: true,
  powerPreference: "high-performance",
});
renderer.setPixelRatio(
  Math.min(window.devicePixelRatio, pageId === "homepage" ? 1.35 : 1.1)
);
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
let isCloudy = false;

function createTexture({ base, accent, size = 256, noise = 0.15 }) {
  const texCanvas = document.createElement("canvas");
  texCanvas.width = size;
  texCanvas.height = size;
  const ctx = texCanvas.getContext("2d");
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
  const texture = new THREE.CanvasTexture(texCanvas);
  texture.wrapS = THREE.RepeatWrapping;
  texture.wrapT = THREE.RepeatWrapping;
  texture.repeat.set(10, 20);
  return texture;
}

function createCloudTexture(size = 256) {
  const texCanvas = document.createElement("canvas");
  texCanvas.width = size;
  texCanvas.height = size;
  const ctx = texCanvas.getContext("2d");
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
  return new THREE.CanvasTexture(texCanvas);
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
  transparent: true,
  opacity: 0,
});
const parcelGeometry = new THREE.BoxGeometry(4, 0.28, 8);
const furrowMaterial = new THREE.MeshStandardMaterial({
  map: soilTexture,
  roughness: 0.9,
  metalness: 0.02,
});
const furrowGeometry = new THREE.BoxGeometry(0.6, 0.2, 60);

for (let z = 0; z < 5; z += 1) {
  for (let x = 0; x < 3; x += 1) {
    const parcel = new THREE.Mesh(parcelGeometry, parcelMaterial.clone());
    parcel.position.set((x - 1) * 6, -1.2, -z * 14);
    parcel.castShadow = true;
    parcel.receiveShadow = true;
    parcel.material.opacity = pageId === "homepage" ? 0 : 0.28;
    parcel.userData.slotIndex = parcelMeshes.length;
    parcelMeshes.push(parcel);
    parcels.add(parcel);
  }
}

for (let x = -1; x <= 1; x += 1) {
  const furrow = new THREE.Mesh(furrowGeometry, furrowMaterial);
  furrow.position.set(x * 6 + 2.6, -1.25, -30);
  furrow.receiveShadow = true;
  parcels.add(furrow);
}
scene.add(parcels);

const sortedParcels = [...parcelMeshes].sort((a, b) => {
  if (a.position.z !== b.position.z) {
    return b.position.z - a.position.z;
  }
  return a.position.x - b.position.x;
});

function soilColor(type) {
  const value = String(type || "").toLowerCase();
  if (value.includes("arg")) return 0x5b8f54;
  if (value.includes("sabl")) return 0x9aaa66;
  if (value.includes("lim")) return 0x6fbf60;
  if (value.includes("hum")) return 0x4f9f4e;
  return 0x5f9a52;
}

const rawParcelNodes = Array.from(
  document.querySelectorAll(".scene-parcelle-label, .parcel-label")
);
const parcelRecords = [];
const parcelRecordMap = new Map();
rawParcelNodes.forEach((node, index) => {
  const recordId = node.dataset.parcelleId || `slot-${node.dataset.parcelIndex || index}`;
  if (parcelRecordMap.has(recordId)) {
    return;
  }
  const record = {
    id: recordId,
    node,
    preferredIndex: Number.isFinite(Number(node.dataset.parcelIndex))
      ? Number(node.dataset.parcelIndex)
      : null,
    longueur: Number(node.dataset.longueur || 8),
    largeur: Number(node.dataset.largeur || 4),
    typeSol: node.dataset.typeSol || "",
  };
  parcelRecordMap.set(recordId, record);
  parcelRecords.push(record);
});

const assignedSlots = new Set();
const parcelSlotById = new Map();
const interactiveParcels = [];

function claimSlot(preferredIndex = null) {
  if (
    Number.isInteger(preferredIndex) &&
    preferredIndex >= 0 &&
    preferredIndex < sortedParcels.length &&
    !assignedSlots.has(preferredIndex)
  ) {
    assignedSlots.add(preferredIndex);
    return sortedParcels[preferredIndex];
  }

  for (let index = 0; index < sortedParcels.length; index += 1) {
    if (!assignedSlots.has(index)) {
      assignedSlots.add(index);
      return sortedParcels[index];
    }
  }

  return null;
}

for (const record of parcelRecords) {
  const slot = claimSlot(record.preferredIndex);
  if (!slot) {
    break;
  }

  const lengthScale = THREE.MathUtils.clamp(record.longueur / 10, 0.75, 1.6);
  const widthScale = THREE.MathUtils.clamp(record.largeur / 8, 0.75, 1.4);
  slot.scale.set(widthScale, 1, lengthScale);
  slot.material.opacity = pageId === "homepage" ? 0 : 0.92;
  slot.material.color.setHex(soilColor(record.typeSol));
  slot.userData.recordId = record.id;
  parcelSlotById.set(record.id, slot);
  interactiveParcels.push(slot);

  const labelObject = new CSS2DObject(record.node);
  labelObject.position.set(0, 0.9, 0);
  const t =
    (Math.max(...parcelMeshes.map((parcel) => parcel.position.z)) - slot.position.z) /
    Math.max(
      1,
      Math.max(...parcelMeshes.map((parcel) => parcel.position.z)) -
        Math.min(...parcelMeshes.map((parcel) => parcel.position.z))
  );
  labelObject.scale.setScalar(THREE.MathUtils.lerp(1.05, 0.72, t));
  slot.add(labelObject);
  slot.userData.labelElement = record.node;
  slot.userData.labelObject = labelObject;
  if (pageId === "parcelles") {
    record.node.classList.add("is-hidden");
    labelObject.visible = false;
  }
}

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
  trunk.position.set((Math.random() - 0.5) * 24, -0.3, -8 - Math.random() * 70);
  leaves.position.set(trunk.position.x, trunk.position.y + 1.4, trunk.position.z);
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
for (let i = 0; i < 14; i += 1) {
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
rainGeometry.setAttribute("position", new THREE.BufferAttribute(rainPositions, 3));
const rainMaterial = new THREE.PointsMaterial({
  color: 0x9fb9cf,
  size: 0.06,
  transparent: true,
  opacity: 0.7,
});
const rain = new THREE.Points(rainGeometry, rainMaterial);
rain.visible = false;
scene.add(rain);

const plantGroup = new THREE.Group();
scene.add(plantGroup);
const interactivePlants = [];

const plantNodes = Array.from(document.querySelectorAll(".scene-plant-marker"));
const slotPlantCounts = new Map();
for (const node of plantNodes) {
  const parcelleId = node.dataset.parcelleId;
  const slot = parcelSlotById.get(parcelleId);
  if (!slot) {
    continue;
  }
  const count = slotPlantCounts.get(parcelleId) || 0;
  slotPlantCounts.set(parcelleId, count + 1);

  const perRow = Math.max(
    2,
    Math.min(4, Math.round(2 + slot.scale.x * 1.2))
  );
  const row = Math.floor(count / perRow);
  const col = count % perRow;
  const spreadX = THREE.MathUtils.clamp(0.95 * slot.scale.x, 0.85, 1.45);
  const spreadZ = THREE.MathUtils.clamp(1.15 * slot.scale.z, 0.95, 1.75);
  const xStart = -((perRow - 1) * spreadX) / 2;
  const zStart = 0.85;
  const localX = xStart + col * spreadX + (row % 2 === 0 ? 0 : spreadX * 0.22);
  const localZ = zStart - row * spreadZ + Math.sin(count * 1.7) * 0.08;

  const marker = new THREE.Mesh(
    new THREE.CylinderGeometry(0.08, 0.12, 0.7, 7),
    new THREE.MeshStandardMaterial({
      color: 0x9ad27d,
      roughness: 0.55,
      emissive: 0x1b341d,
      transparent: true,
      opacity: pageId === "plantes" ? 0.45 : 0.82,
    })
  );

  marker.position.set(slot.position.x + localX, -0.65, slot.position.z + localZ);
  marker.castShadow = true;

  const crown = new THREE.Mesh(
    new THREE.SphereGeometry(0.18, 10, 10),
    new THREE.MeshStandardMaterial({
      color: 0x6fbe57,
      roughness: 0.55,
      transparent: true,
      opacity: pageId === "plantes" ? 0.45 : 0.9,
    })
  );
  crown.position.y = 0.45;
  marker.add(crown);

  const labelObject = new CSS2DObject(node);
  labelObject.position.set(0, 0.84, 0);
  labelObject.scale.setScalar(0.72);
  marker.add(labelObject);
  marker.userData.parcelleId = parcelleId;
  marker.userData.labelElement = node;
  marker.userData.labelObject = labelObject;
  marker.userData.crown = crown;
  marker.userData.baseY = marker.position.y;
  node.classList.remove("is-visible");

  plantGroup.add(marker);
  interactivePlants.push(marker);
}

const raycaster = new THREE.Raycaster();
const pointer = new THREE.Vector2(10, 10);
let selectedPlant = null;
let selectedParcel = null;

function setSelectedParcel(parcel) {
  selectedParcel = parcel || null;
  for (const target of interactiveParcels) {
    const isSelected = target === selectedParcel;
    if (target.userData.labelElement) {
      target.userData.labelElement.classList.toggle("is-hidden", !isSelected);
    }
    if (target.userData.labelObject) {
      target.userData.labelObject.visible = isSelected;
    }
  }
  if (pageId === "parcelles") {
    sceneSteps.forEach((step) => step.classList.remove("is-active"));
    if (selectedParcel) {
      sceneStepById.get(String(selectedParcel.userData.recordId))?.classList.add("is-active");
    }
  }
}

function setSelectedPlant(marker) {
  selectedPlant = marker || null;
  for (const plant of interactivePlants) {
    const isSelected = plant === selectedPlant;
    if (plant.userData.labelElement) {
      plant.userData.labelElement.classList.toggle("is-visible", isSelected);
    }
    if (plant.userData.labelObject) {
      plant.userData.labelObject.visible = isSelected;
    }
  }
}

if (pageId === "parcelles") {
  setSelectedParcel(null);
}

for (const plant of interactivePlants) {
  if (plant.userData.labelObject) {
    plant.userData.labelObject.visible = false;
  }
}

function setSceneMood(weather) {
  isRainy = weather === "rainy";
  isCloudy = weather === "cloudy";
  rain.visible = isRainy;
  clouds.visible = isCloudy;

  if (weather === "sunny") {
    scene.fog.color.set(0x121d15);
    scene.fog.near = 8;
    scene.fog.far = 65;
    sunTarget.set(12, 14, -24);
    sunMaterial.color.set(0xfff0d2);
    sun.scale.setScalar(1.05);
    hemiLight.intensity = 0.9;
    sunLight.intensity = 3.0;
    sunLight.color.set(0xfff3d4);
    fillLight.intensity = 0.8;
    renderer.toneMappingExposure = 1.18;
    return;
  }

  if (weather === "night") {
    scene.fog.color.set(0x0f1a12);
    scene.fog.near = 6;
    scene.fog.far = 45;
    sunTarget.set(6, 3, -20);
    sunMaterial.color.set(0x6fa6ff);
    sun.scale.setScalar(0.6);
    hemiLight.intensity = 0.35;
    sunLight.intensity = 0.8;
    sunLight.color.set(0x9cb7ff);
    fillLight.intensity = 0.25;
    renderer.toneMappingExposure = 0.9;
    return;
  }

  if (weather === "rainy") {
    scene.fog.color.set(0x0e1720);
    scene.fog.near = 8;
    scene.fog.far = 50;
    sunTarget.set(9, 9, -24);
    sunMaterial.color.set(0xbdd1ee);
    sun.scale.setScalar(0.85);
    hemiLight.intensity = 0.45;
    sunLight.intensity = 1.5;
    sunLight.color.set(0xd8e2e8);
    fillLight.intensity = 0.35;
    renderer.toneMappingExposure = 0.95;
    return;
  }

  scene.fog.color.set(0x151a18);
  scene.fog.near = 10;
  scene.fog.far = 55;
  sunTarget.set(10, 10, -25);
  sunMaterial.color.set(0xffd27a);
  sun.scale.setScalar(0.95);
  hemiLight.intensity = 0.55;
  sunLight.intensity = 2.0;
  sunLight.color.set(0xe2ebf2);
  fillLight.intensity = 0.45;
  renderer.toneMappingExposure = 1.0;
}

const weatherSelect = document.getElementById("weather");
function applyWeather() {
  setSceneMood(weatherSelect ? weatherSelect.value.trim() : "sunny");
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
  weatherSelect.addEventListener("change", applyWeather);
}
applyWeather();

const clock = new THREE.Clock();
let rafId = null;
let isRunning = true;
let cameraBob = 0;
const lookAtTarget = {
  x: 0,
  y: pageId === "homepage" ? -0.6 : -0.3,
  z: pageId === "homepage" ? -30 : -24,
};

const sceneSteps = Array.from(document.querySelectorAll(".scene-step[data-scene-id]"));
const sceneStepById = new Map(
  sceneSteps.map((step) => [String(step.dataset.sceneId), step])
);

function setActiveParcel(parcelId) {
  if (pageId !== "parcelles") {
    sceneSteps.forEach((step) => {
      step.classList.toggle("is-active", step.dataset.sceneId === String(parcelId));
    });
  }

  for (const parcel of parcelMeshes) {
    const isActive = parcel.userData.recordId === String(parcelId);
    const targetOpacity =
      pageId === "homepage"
        ? parcel.material.opacity
        : isActive
          ? 0.98
          : pageId === "plantes"
            ? 0.22
            : 0.38;
    gsap.to(parcel.material, {
      opacity: targetOpacity,
      duration: 0.35,
      overwrite: true,
    });
  }

  for (const marker of plantGroup.children) {
    const isActive = marker.userData.parcelleId === String(parcelId);
    gsap.to(marker.scale, {
      x: isActive ? 1.08 : 0.72,
      y: isActive ? 1.08 : 0.72,
      z: isActive ? 1.08 : 0.72,
      duration: 0.35,
      overwrite: true,
    });
    gsap.to(marker.material, {
      opacity: isActive ? 0.96 : 0.18,
      duration: 0.35,
      overwrite: true,
    });
    if (marker.userData.crown?.material) {
      gsap.to(marker.userData.crown.material, {
        opacity: isActive ? 0.96 : 0.18,
        duration: 0.35,
        overwrite: true,
      });
    }
    if (marker.userData.labelObject) {
      marker.userData.labelObject.visible = isActive && marker === selectedPlant;
    }
    if (!isActive && selectedPlant === marker) {
      setSelectedPlant(null);
    }
  }
}

if (homepageUi) {
  const walkTimeline = gsap.timeline({
    scrollTrigger: {
      trigger: ".homepage-ui",
      start: "top top",
      end: "bottom bottom",
      scrub: 0.8,
    },
  });

  walkTimeline
    .to(camera.position, { z: -50, y: 1.7, ease: "none" }, 0)
    .to(camera.rotation, { y: 0.06, x: -0.02, ease: "none" }, 0)
    .to(lookAtTarget, { z: -70, ease: "none" }, 0);

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

  document.querySelectorAll(".reveal-text").forEach((el) => splitText(el));

  gsap.fromTo(
    ".hero-panel .reveal-text .char",
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
      stagger: 0.18,
      scrollTrigger: {
        trigger: ".info",
        start: "top 78%",
      },
    }
  );

  document.querySelectorAll(".parallax").forEach((el) => {
    const speed = Number(el.dataset.speed || 0.3);
    gsap.to(el, {
      y: -60 * speed,
      ease: "none",
      scrollTrigger: {
        trigger: ".homepage-ui",
        start: "top top",
        end: "bottom bottom",
        scrub: true,
      },
    });
  });
}

if (!homepageUi && sceneSteps.length > 0) {
  const focusSequence = sceneSteps
    .map((step) => ({
      step,
      parcelId: String(step.dataset.sceneId),
      slot: parcelSlotById.get(String(step.dataset.sceneId)),
    }))
    .filter((entry) => entry.slot);

  if (focusSequence.length > 0) {
    const viewConfig =
      pageId === "plantes"
        ? { y: 3.6, zOffset: 5.8, xOffset: 0, lookY: -0.55 }
        : { y: 7.2, zOffset: 3.1, xOffset: 0.6, lookY: -1.0 };

    const intro = focusSequence[0];
    camera.position.set(
      intro.slot.position.x + viewConfig.xOffset,
      viewConfig.y,
      intro.slot.position.z + viewConfig.zOffset
    );
    lookAtTarget.x = intro.slot.position.x;
    lookAtTarget.y = viewConfig.lookY;
    lookAtTarget.z = intro.slot.position.z;
    setActiveParcel(intro.parcelId);

    const timeline = gsap.timeline({
      scrollTrigger: {
        trigger: ".scene-page",
        start: "top top",
        end: "bottom bottom",
        scrub: 0.35,
        snap: focusSequence.length > 1 ? {
          snapTo: 1 / (focusSequence.length - 1),
          duration: 0.16,
          ease: "power1.out",
        } : false,
      },
    });

    focusSequence.forEach((entry, index) => {
      timeline.to(
        camera.position,
        {
          x: entry.slot.position.x + viewConfig.xOffset,
          y: viewConfig.y,
          z: entry.slot.position.z + viewConfig.zOffset,
          ease: "none",
        },
        index
      );
      timeline.to(
        lookAtTarget,
        {
          x: entry.slot.position.x,
          y: viewConfig.lookY,
          z: entry.slot.position.z,
          ease: "none",
        },
        index
      );
    });

    focusSequence.forEach((entry) => {
      ScrollTrigger.create({
        trigger: entry.step,
        start: "top center",
        end: "bottom center",
        onEnter: () => setActiveParcel(entry.parcelId),
        onEnterBack: () => setActiveParcel(entry.parcelId),
      });
    });
  }
}

function updatePointer(event) {
  pointer.x = (event.clientX / window.innerWidth) * 2 - 1;
  pointer.y = -(event.clientY / window.innerHeight) * 2 + 1;
}

window.addEventListener("pointermove", updatePointer);
window.addEventListener("click", (event) => {
  updatePointer(event);
  raycaster.setFromCamera(pointer, camera);
  if (pageId === "plantes") {
    const hits = raycaster.intersectObjects(interactivePlants, true);
    const picked = hits.length > 0
      ? hits[0].object.parent?.userData?.labelElement
        ? hits[0].object.parent
        : interactivePlants.find((plant) => plant === hits[0].object || plant.children.includes(hits[0].object))
      : null;
    setSelectedPlant(picked || null);
    return;
  }

  if (pageId === "parcelles") {
    const hits = raycaster.intersectObjects(interactiveParcels, false);
    const picked = hits.length > 0 ? hits[0].object : null;
    if (picked && selectedParcel === picked) {
      setSelectedParcel(null);
    } else {
      setSelectedParcel(picked || null);
    }
  }
});

function animate() {
  if (!isRunning) {
    return;
  }
  const delta = clock.getDelta();
  const elapsed = clock.getElapsedTime();

  dust.rotation.y += delta * 0.02;
  dust.position.z = Math.sin(elapsed * 0.3) * 0.6;

  if (isRainy) {
    const gust = (Math.sin(elapsed * 1.6) + 1.2) * 50.0;
    for (let i = 0; i < rainCount; i += 1) {
      const idx = i * 3;
      rainPositions[idx + 1] -= delta * (rainSpeeds[i] * 50.0 + gust);
      if (rainPositions[idx + 1] < -1) {
        rainPositions[idx + 1] = Math.random() * 10 + 6;
        rainPositions[idx] = (Math.random() - 0.5) * 24;
        rainPositions[idx + 2] = -Math.random() * 80;
      }
    }
    rainGeometry.attributes.position.needsUpdate = true;
  }

  if (isCloudy) {
    for (const cloud of cloudData) {
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
    cropQuaternion.setFromEuler(new THREE.Euler(sway * 0.6, sway * 0.2, sway));
    cropMatrix.compose(cropPosition, cropQuaternion, cropScale);
    crops.setMatrixAt(i, cropMatrix);
  }
  crops.instanceMatrix.needsUpdate = true;

  for (let i = 0; i < plantGroup.children.length; i += 1) {
    const marker = plantGroup.children[i];
    marker.rotation.y += delta * 0.25;
    marker.position.y = marker.userData.baseY + Math.sin(elapsed * 1.4 + i * 0.6) * 0.025;
  }

  const bob = Math.sin(elapsed * 3.2) * (homepageUi ? 0.03 : 0.018);
  camera.position.y += bob - cameraBob;
  cameraBob = bob;
  if (!homepageUi) {
    camera.position.x += Math.sin(elapsed * 0.24) * 0.0015;
  }
  camera.lookAt(lookAtTarget.x, lookAtTarget.y, lookAtTarget.z);
  sun.position.y += Math.sin(elapsed * 0.4) * 0.0006;

  if (homepageUi) {
    const camZ = camera.position.z;
    for (const parcel of parcelMeshes) {
      const distance = camZ - parcel.position.z;
      const reveal = THREE.MathUtils.smoothstep(distance, 4, 22);
      parcel.material.opacity = Math.min(1, Math.max(parcel.material.opacity, reveal));
    }
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
  renderer.setPixelRatio(
    Math.min(window.devicePixelRatio, pageId === "homepage" ? 1.35 : 1.1)
  );
  labelRenderer.setSize(window.innerWidth, window.innerHeight);
  ScrollTrigger.refresh();
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
