import * as THREE from "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js";

console.log("🌾 Agriech 3D Farm - Script loaded");

let agriechSceneInitialized = false;

function initializeAgriechScene() {
  if (agriechSceneInitialized) {
    document.dispatchEvent(new Event("agriech:scene-refresh"));
    return;
  }

  agriechSceneInitialized = true;
  console.log("🌾 Agriech 3D Farm - DOM Ready, initializing...");
  
  const gsap = window.gsap;
  const ScrollTrigger = window.ScrollTrigger;
  
  const canvas = document.getElementById("bg");
  
  if (!canvas) {
    console.error("❌ Canvas element #bg not found!");
    return;
  }
  
  function syncFarm3dMode() {
    if (document.querySelector(".farm-3d-header")) {
      document.body.classList.add("farm-3d-mode");
    } else {
      document.body.classList.remove("farm-3d-mode");
    }
  }

  syncFarm3dMode();
  
  console.log("✅ Canvas found, setting up Three.js...");

  const scene = new THREE.Scene();
  // Bright blue sky background
  scene.background = new THREE.Color(0x4da6ff);
  // Blue fog to blend with sky
  scene.fog = new THREE.Fog(0x6bb8ff, 80, 250);

  const camera = new THREE.PerspectiveCamera(
    55,
    window.innerWidth / window.innerHeight,
    0.1,
    300  // Extended far plane
  );
  camera.position.set(0, 4, 12); // Higher and further back for grid overview

  const renderer = new THREE.WebGLRenderer({
    canvas,
    antialias: true,
    alpha: true,
    powerPreference: "high-performance",
  });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  renderer.toneMapping = THREE.NoToneMapping;  // No tone mapping for clearer cards
  renderer.outputColorSpace = THREE.SRGBColorSpace;

  // Lighting (sun-like)
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

  // Sun (glowing sphere for background)
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
  const isSunny = weather === "Ensoleillé";
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

  // Default / other weather
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

// Simple procedural textures (grass + soil) using canvas
function createTexture({ base, accent, size = 256, noise = 0.15 }) {
  const cvs = document.createElement("canvas");
  cvs.width = size;
  cvs.height = size;
  const ctx = cvs.getContext("2d");
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
  const texture = new THREE.CanvasTexture(cvs);
  texture.wrapS = THREE.RepeatWrapping;
  texture.wrapT = THREE.RepeatWrapping;
  texture.repeat.set(10, 20);
  return texture;
}

function createCloudTexture(size = 256) {
  const cvs = document.createElement("canvas");
  cvs.width = size;
  cvs.height = size;
  const ctx = cvs.getContext("2d");
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
  const texture = new THREE.CanvasTexture(cvs);
  return texture;
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

// Ground (farm field with grass texture) - MUCH LARGER for seamless look
const groundMaterial = new THREE.MeshStandardMaterial({
  map: grassTexture,
  roughness: 0.95,
  metalness: 0.02,
});
const ground = new THREE.Mesh(new THREE.PlaneGeometry(300, 400), groundMaterial);
ground.rotation.x = -Math.PI / 2;
ground.position.y = -1.4;
ground.position.z = -100; // Center it better
ground.receiveShadow = true;
scene.add(ground);

// Field parcels with separations (soil furrows between rows)
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
const furrowGeometry = new THREE.BoxGeometry(0.6, 0.2, 200); // Much longer furrows

const parcelCountZ = 12; // More parcels
const parcelCountX = 5;  // Wider spread
for (let z = 0; z < parcelCountZ; z += 1) {
  for (let x = 0; x < parcelCountX; x += 1) {
    const parcel = new THREE.Mesh(parcelGeometry, parcelMaterial.clone());
    parcel.position.set((x - 2) * 6, -1.2, -z * 14 + 10);
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

// Skip CSS2D labels for now - they require additional module loading

// Long furrows to separate parcels - extend across full field
for (let x = -2; x <= 2; x += 1) {
  const furrow = new THREE.Mesh(furrowGeometry, furrowMaterial);
  furrow.position.set(x * 6 + 2.6, -1.25, -80);
  furrow.castShadow = false;
  furrow.receiveShadow = true;
  furrow.userData.isParcel = false;
  parcels.add(furrow);
}
scene.add(parcels);

// Rows of crops (instanced for performance + wind animation) - MORE and WIDER
const cropMaterial = new THREE.MeshStandardMaterial({
  color: 0x6fbe57,
  roughness: 0.75,
});
const cropGeometry = new THREE.ConeGeometry(0.12, 0.6, 6);
const cropCount = 600; // Many more crops
const crops = new THREE.InstancedMesh(cropGeometry, cropMaterial, cropCount);
const cropMatrix = new THREE.Matrix4();
const cropPosition = new THREE.Vector3();
const cropQuaternion = new THREE.Quaternion();
const cropScale = new THREE.Vector3(1, 1, 1);
const cropData = [];
let cropIndex = 0;
for (let row = 0; row < 20; row += 1) {
  const z = -row * 8 + 10;
  for (let col = 0; col < 30; col += 1) {
    const x = (col - 15) * 1.2;
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

// Trees spread wide along edges - no visible boundaries
for (let i = 0; i < 60; i += 1) {
  const trunk = new THREE.Mesh(
    new THREE.CylinderGeometry(0.15, 0.22, 1.6, 8),
    treeTrunkMaterial
  );
  const leaves = new THREE.Mesh(
    new THREE.ConeGeometry(0.7, 1.8, 10),
    treeLeafMaterial
  );
  // Trees on left and right edges, spread very far
  const side = i % 2 === 0 ? -1 : 1;
  const xOffset = 18 + Math.random() * 30; // Far from center
  trunk.position.set(
    side * xOffset,
    -0.3,
    20 - Math.random() * 180 // Full depth
  );
  leaves.position.set(trunk.position.x, trunk.position.y + 1.4, trunk.position.z);
  trunk.castShadow = true;
  leaves.castShadow = true;
  scene.add(trunk, leaves);
}

// Subtle atmospheric particles (instanced) - spread wider
const dustGeometry = new THREE.SphereGeometry(0.03, 6, 6);
const dustMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff, opacity: 0.6, transparent: true });
const dustCount = 150;
const dust = new THREE.InstancedMesh(dustGeometry, dustMaterial, dustCount);
const dustMatrix = new THREE.Matrix4();
for (let i = 0; i < dustCount; i += 1) {
  dustMatrix.setPosition(
    (Math.random() - 0.5) * 60,
    Math.random() * 6 + 1,
    20 - Math.random() * 160
  );
  dust.setMatrixAt(i, dustMatrix);
}
scene.add(dust);

// Clouds (hidden unless weather is cloudy)
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

// Rain particles (points, hidden unless weather is rainy)
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

// Snow particles (points, hidden unless weather is snowy)
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
snowGeometry.setAttribute("position", new THREE.BufferAttribute(snowPositions, 3));
const snowMaterial = new THREE.PointsMaterial({
  color: 0xe9f3ff,
  size: 0.12,
  transparent: true,
  opacity: 0.85,
});
const snow = new THREE.Points(snowGeometry, snowMaterial);
snow.visible = false;
scene.add(snow);

// ============================================
// 3D EQUIPMENT SYSTEM - Farm Products
// ============================================
const equipmentGroup = new THREE.Group();
equipmentGroup.name = "equipment";
scene.add(equipmentGroup);

const equipmentMeshes = [];
let selectedEquipment = null;
let hoveredEquipment = null;

// Status colors
const STATUS_COLORS = {
  'Disponible': 0x4ade80,  // Green
  'Loue': 0xfbbf24,        // Orange/Yellow
  'En Panne': 0xef4444     // Red
};

// Create glow material for selection
function createGlowMaterial(color) {
  return new THREE.MeshBasicMaterial({
    color: color,
    transparent: true,
    opacity: 0.3,
    side: THREE.BackSide
  });
}

// Create 3D floating product card - modern glass design
function createProductCard(itemData) {
  const group = new THREE.Group();
  const baseColor = STATUS_COLORS[itemData.etat] || 0x888888;
  
  // BIGGER card dimensions for better visibility
  const cardWidth = 4.0;
  const cardHeight = 5.0;
  
  // Create canvas for card texture - HIGH RES for sharp text
  const canvas = document.createElement('canvas');
  canvas.width = 1024;
  canvas.height = 1280;
  const ctx = canvas.getContext('2d');
  
  // Improve canvas rendering
  ctx.imageSmoothingEnabled = true;
  ctx.imageSmoothingQuality = 'high';
  
  // Create texture reference - we'll update it when image loads
  const texture = new THREE.CanvasTexture(canvas);
  
  // Scale factor for positioning (since we increased resolution)
  const scale = 1024 / 800;
  
  // Function to draw the complete card
  function drawCard(img) {
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw drop shadow first (offset black rounded rect)
    ctx.fillStyle = 'rgba(0, 0, 0, 0.4)';
    ctx.beginPath();
    ctx.roundRect(15 * scale, 20 * scale, canvas.width, canvas.height, 40 * scale);
    ctx.fill();
    
    // Solid white background - fully opaque
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.roundRect(0, 0, canvas.width, canvas.height, 40 * scale);
    ctx.fill();
    
    // Strong dark border for definition
    ctx.strokeStyle = '#555555';
    ctx.lineWidth = 4 * scale;
    ctx.beginPath();
    ctx.roundRect(4 * scale, 4 * scale, canvas.width - 8 * scale, canvas.height - 8 * scale, 38 * scale);
    ctx.stroke();
    
    // Status color accent bar at top (thicker)
    const statusColorHex = '#' + baseColor.toString(16).padStart(6, '0');
    ctx.fillStyle = statusColorHex;
    ctx.beginPath();
    ctx.roundRect(0, 0, canvas.width, 30 * scale, [40 * scale, 40 * scale, 0, 0]);
    ctx.fill();
    
    // Status badge (bigger)
    ctx.fillStyle = statusColorHex;
    ctx.beginPath();
    ctx.roundRect(38 * scale, 55 * scale, 260 * scale, 75 * scale, 38 * scale);
    ctx.fill();
    ctx.fillStyle = '#ffffff';
    ctx.font = `bold ${36 * scale}px system-ui, sans-serif`;
    ctx.textAlign = 'center';
    let statusText = itemData.etat === 'Disponible' ? '✓ Disponible' : 
                     itemData.etat === 'Loue' ? '⏱ Loué' : '⚠ En Panne';
    ctx.fillText(statusText, 168 * scale, 107 * scale);
    
    // Type badge (right side) - solid dark background
    ctx.fillStyle = '#2d5a27';
    ctx.beginPath();
    ctx.roundRect(canvas.width - 280 * scale, 55 * scale, 240 * scale, 75 * scale, 38 * scale);
    ctx.fill();
    ctx.fillStyle = '#ffffff';
    ctx.font = `bold ${30 * scale}px system-ui, sans-serif`;
    ctx.textAlign = 'center';
    ctx.fillText(itemData.typeLabel || itemData.type || 'Matériel', canvas.width - 160 * scale, 107 * scale);
    
    // Equipment name - bold and prominent (BIGGER)
    ctx.fillStyle = '#000000';
    ctx.font = `bold ${60 * scale}px system-ui, sans-serif`;
    ctx.textAlign = 'left';
    const name = (itemData.nom || 'Sans nom').substring(0, 18);
    ctx.fillText(name, 50 * scale, 200 * scale);
    
    // === PRODUCT IMAGE AREA ===
    const imgX = 50 * scale;
    const imgY = 230 * scale;
    const imgW = canvas.width - 100 * scale;
    const imgH = 350 * scale;
    
    // Image container with rounded corners
    ctx.save();
    ctx.beginPath();
    ctx.roundRect(imgX, imgY, imgW, imgH, 20 * scale);
    ctx.clip();
    
    if (img && img.complete && img.naturalWidth > 0) {
      // Draw actual image - cover fit
      const imgScale = Math.max(imgW / img.naturalWidth, imgH / img.naturalHeight);
      const w = img.naturalWidth * imgScale;
      const h = img.naturalHeight * imgScale;
      const x = imgX + (imgW - w) / 2;
      const y = imgY + (imgH - h) / 2;
      ctx.drawImage(img, x, y, w, h);
    } else {
      // Placeholder gradient
      const grad = ctx.createLinearGradient(imgX, imgY, imgX, imgY + imgH);
      grad.addColorStop(0, '#e8f5e9');
      grad.addColorStop(1, '#c8e6c9');
      ctx.fillStyle = grad;
      ctx.fillRect(imgX, imgY, imgW, imgH);
      
      // Placeholder icon
      ctx.fillStyle = '#81c784';
      ctx.font = `${100 * scale}px system-ui, sans-serif`;
      ctx.textAlign = 'center';
      ctx.fillText('🚜', imgX + imgW/2, imgY + imgH/2 + 30 * scale);
    }
    ctx.restore();
    
    // Image border
    ctx.strokeStyle = 'rgba(0,0,0,0.2)';
    ctx.lineWidth = 3 * scale;
    ctx.beginPath();
    ctx.roundRect(imgX, imgY, imgW, imgH, 20 * scale);
    ctx.stroke();
    
    // Owner section with icon - SOLID dark background
    ctx.fillStyle = '#2d5a27';
    ctx.beginPath();
    ctx.roundRect(38 * scale, 610 * scale, canvas.width - 76 * scale, 90 * scale, 20 * scale);
    ctx.fill();
    ctx.fillStyle = '#ffffff';
    ctx.font = `bold ${36 * scale}px system-ui, sans-serif`;
    ctx.textAlign = 'left';
    ctx.fillText('👤 ' + (itemData.proprietaire || 'Propriétaire'), 70 * scale, 670 * scale);
    
    // Description (shorter, below owner)
    ctx.fillStyle = '#333333';
    ctx.font = `${30 * scale}px system-ui, sans-serif`;
    ctx.textAlign = 'left';
    const desc = (itemData.description || 'Équipement agricole disponible.').substring(0, 50);
    ctx.fillText(desc, 50 * scale, 750 * scale);
    
    // Large price section - BIGGER with solid dark background
    ctx.fillStyle = statusColorHex;
    ctx.shadowColor = 'rgba(0,0,0,0.3)';
    ctx.shadowBlur = 25 * scale;
    ctx.shadowOffsetY = 8 * scale;
    ctx.beginPath();
    ctx.roundRect(38 * scale, canvas.height - 280 * scale, canvas.width - 76 * scale, 200 * scale, 30 * scale);
    ctx.fill();
    ctx.shadowBlur = 0;
    ctx.shadowOffsetY = 0;
    
    // Add dark overlay for better text contrast
    ctx.fillStyle = 'rgba(0,0,0,0.1)';
    ctx.beginPath();
    ctx.roundRect(38 * scale, canvas.height - 280 * scale, canvas.width - 76 * scale, 200 * scale, 30 * scale);
    ctx.fill();
    
    // Price value - MUCH BIGGER with text shadow
    ctx.textAlign = 'center';
    ctx.font = `bold ${90 * scale}px system-ui, sans-serif`;
    const price = itemData.prixLocation ? itemData.prixLocation.toFixed(2) : '0.00';
    
    // Text shadow
    ctx.fillStyle = 'rgba(0,0,0,0.4)';
    ctx.fillText(price + ' TND', canvas.width / 2 + 4 * scale, canvas.height - 145 * scale + 4 * scale);
    
    // Main white text
    ctx.fillStyle = '#ffffff';
    ctx.fillText(price + ' TND', canvas.width / 2, canvas.height - 145 * scale);
    
    // Price unit
    ctx.font = `bold ${36 * scale}px system-ui, sans-serif`;
    ctx.fillStyle = 'rgba(255,255,255,0.95)';
    ctx.fillText('par jour', canvas.width / 2, canvas.height - 95 * scale);
    
    // Click instruction at bottom
    ctx.fillStyle = '#555555';
    ctx.font = `bold ${28 * scale}px system-ui, sans-serif`;
    ctx.fillText('Cliquez pour voir les détails →', canvas.width / 2, canvas.height - 30 * scale);
    
    // Update texture
    texture.needsUpdate = true;
  }
  
  // Draw initial card (without image)
  drawCard(null);
  
  // Load product image asynchronously
  if (itemData.imageUrl) {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function() {
      drawCard(img);
    };
    img.onerror = function() {
      console.log('Failed to load image:', itemData.imageUrl);
    };
    // Handle relative and absolute URLs
    if (itemData.imageUrl.startsWith('http') || itemData.imageUrl.startsWith('//')) {
      img.src = itemData.imageUrl;
    } else {
      img.src = itemData.imageUrl.startsWith('/') ? itemData.imageUrl : '/' + itemData.imageUrl;
    }
  }
  
  // Improve texture quality
  texture.minFilter = THREE.LinearFilter;
  texture.magFilter = THREE.LinearFilter;
  texture.generateMipmaps = false;
  texture.colorSpace = THREE.SRGBColorSpace;
  
  // Create card mesh - floating in air (use BasicMaterial for full brightness)
  const cardGeometry = new THREE.PlaneGeometry(cardWidth, cardHeight);
  const cardMaterial = new THREE.MeshBasicMaterial({
    map: texture,
    side: THREE.DoubleSide,
    transparent: false  // No transparency for sharper cards
  });
  
  const cardMesh = new THREE.Mesh(cardGeometry, cardMaterial);
  cardMesh.position.y = 2.2; // Float higher in the air
  group.add(cardMesh);
  
  // Soft shadow beneath card (removed ring and beam decorations)
  const shadowGeom = new THREE.CircleGeometry(1.5, 32);
  const shadowMat = new THREE.MeshBasicMaterial({
    color: 0x000000,
    transparent: true,
    opacity: 0.1
  });
  const shadow = new THREE.Mesh(shadowGeom, shadowMat);
  shadow.rotation.x = -Math.PI / 2;
  shadow.position.y = 0.02;
  group.add(shadow);
  
  // Selection glow plane (behind card)
  const glowGeom = new THREE.PlaneGeometry(cardWidth + 0.5, cardHeight + 0.5);
  const glowMat = new THREE.MeshBasicMaterial({
    color: baseColor,
    transparent: true,
    opacity: 0,
    side: THREE.DoubleSide
  });
  const glowMesh = new THREE.Mesh(glowGeom, glowMat);
  glowMesh.position.copy(cardMesh.position);
  glowMesh.position.z = -0.08;
  glowMesh.visible = false;
  glowMesh.name = 'glow';
  group.add(glowMesh);
  
  // Store references
  group.userData.cardMesh = cardMesh;
  group.userData.glowMesh = glowMesh;
  
  return group;
}

// Helper function to wrap text on canvas
function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
  const words = text.split(' ');
  let line = '';
  let lineCount = 0;
  const maxLines = 4;
  
  for (let n = 0; n < words.length && lineCount < maxLines; n++) {
    const testLine = line + words[n] + ' ';
    const metrics = ctx.measureText(testLine);
    if (metrics.width > maxWidth && n > 0) {
      ctx.fillText(line, x, y);
      line = words[n] + ' ';
      y += lineHeight;
      lineCount++;
    } else {
      line = testLine;
    }
  }
  if (lineCount < maxLines) {
    ctx.fillText(line, x, y);
  }
}

// Equipment model factory - creates 3D models for each type (legacy fallback)
function createEquipmentModel(type, status) {
  const group = new THREE.Group();
  const baseColor = STATUS_COLORS[status] || 0x888888;
  const bodyMat = new THREE.MeshStandardMaterial({ 
    color: 0x2d5a27, 
    roughness: 0.6,
    metalness: 0.3
  });
  const metalMat = new THREE.MeshStandardMaterial({ 
    color: 0x444444, 
    roughness: 0.4,
    metalness: 0.7
  });
  const wheelMat = new THREE.MeshStandardMaterial({ 
    color: 0x1a1a1a, 
    roughness: 0.8 
  });
  const accentMat = new THREE.MeshStandardMaterial({ 
    color: baseColor, 
    roughness: 0.5,
    emissive: baseColor,
    emissiveIntensity: 0.2
  });

  switch(type) {
    case 'tractor':
      // Tractor body
      const tractorBody = new THREE.Mesh(
        new THREE.BoxGeometry(0.8, 0.5, 1.2),
        bodyMat
      );
      tractorBody.position.y = 0.45;
      tractorBody.castShadow = true;
      group.add(tractorBody);
      
      // Cabin
      const cabin = new THREE.Mesh(
        new THREE.BoxGeometry(0.6, 0.5, 0.6),
        new THREE.MeshStandardMaterial({ color: 0x87ceeb, transparent: true, opacity: 0.7 })
      );
      cabin.position.set(0, 0.9, -0.15);
      cabin.castShadow = true;
      group.add(cabin);
      
      // Front wheels (smaller)
      [-0.35, 0.35].forEach(x => {
        const wheel = new THREE.Mesh(
          new THREE.CylinderGeometry(0.18, 0.18, 0.12, 16),
          wheelMat
        );
        wheel.rotation.z = Math.PI / 2;
        wheel.position.set(x, 0.18, 0.45);
        wheel.castShadow = true;
        group.add(wheel);
      });
      
      // Back wheels (larger)
      [-0.4, 0.4].forEach(x => {
        const wheel = new THREE.Mesh(
          new THREE.CylinderGeometry(0.3, 0.3, 0.15, 16),
          wheelMat
        );
        wheel.rotation.z = Math.PI / 2;
        wheel.position.set(x, 0.3, -0.35);
        wheel.castShadow = true;
        group.add(wheel);
      });
      
      // Status indicator light
      const light = new THREE.Mesh(
        new THREE.SphereGeometry(0.08, 8, 8),
        accentMat
      );
      light.position.set(0, 1.2, 0);
      group.add(light);
      break;

    case 'harvester':
      // Large harvester body
      const harvBody = new THREE.Mesh(
        new THREE.BoxGeometry(1.2, 0.7, 1.8),
        bodyMat
      );
      harvBody.position.y = 0.6;
      harvBody.castShadow = true;
      group.add(harvBody);
      
      // Harvester cabin
      const harvCabin = new THREE.Mesh(
        new THREE.BoxGeometry(0.8, 0.6, 0.8),
        new THREE.MeshStandardMaterial({ color: 0x87ceeb, transparent: true, opacity: 0.7 })
      );
      harvCabin.position.set(0, 1.25, -0.3);
      harvCabin.castShadow = true;
      group.add(harvCabin);
      
      // Front cutting bar
      const cutter = new THREE.Mesh(
        new THREE.BoxGeometry(1.6, 0.15, 0.3),
        metalMat
      );
      cutter.position.set(0, 0.3, 1.1);
      cutter.castShadow = true;
      group.add(cutter);
      
      // Wheels
      [-0.5, 0.5].forEach(x => {
        const wheel = new THREE.Mesh(
          new THREE.CylinderGeometry(0.35, 0.35, 0.2, 16),
          wheelMat
        );
        wheel.rotation.z = Math.PI / 2;
        wheel.position.set(x, 0.35, 0);
        wheel.castShadow = true;
        group.add(wheel);
      });
      
      // Status light
      const harvLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.1, 8, 8),
        accentMat
      );
      harvLight.position.set(0, 1.6, 0);
      group.add(harvLight);
      break;

    case 'seeder':
      // Seeder frame
      const seederFrame = new THREE.Mesh(
        new THREE.BoxGeometry(1.4, 0.2, 0.6),
        metalMat
      );
      seederFrame.position.y = 0.35;
      seederFrame.castShadow = true;
      group.add(seederFrame);
      
      // Seed hoppers
      for(let i = -0.5; i <= 0.5; i += 0.5) {
        const hopper = new THREE.Mesh(
          new THREE.ConeGeometry(0.15, 0.3, 8),
          bodyMat
        );
        hopper.rotation.x = Math.PI;
        hopper.position.set(i, 0.6, 0);
        hopper.castShadow = true;
        group.add(hopper);
      }
      
      // Wheels
      [-0.6, 0.6].forEach(x => {
        const wheel = new THREE.Mesh(
          new THREE.CylinderGeometry(0.2, 0.2, 0.1, 16),
          wheelMat
        );
        wheel.rotation.z = Math.PI / 2;
        wheel.position.set(x, 0.2, 0);
        wheel.castShadow = true;
        group.add(wheel);
      });
      
      // Status indicator
      const seederLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.06, 8, 8),
        accentMat
      );
      seederLight.position.set(0, 0.8, 0);
      group.add(seederLight);
      break;

    case 'sprayer':
      // Tank
      const tank = new THREE.Mesh(
        new THREE.CylinderGeometry(0.4, 0.4, 1.0, 16),
        new THREE.MeshStandardMaterial({ color: 0x3b82f6, roughness: 0.3 })
      );
      tank.rotation.z = Math.PI / 2;
      tank.position.y = 0.55;
      tank.castShadow = true;
      group.add(tank);
      
      // Spray arms
      const arms = new THREE.Mesh(
        new THREE.BoxGeometry(2.0, 0.05, 0.1),
        metalMat
      );
      arms.position.set(0, 0.3, 0.3);
      arms.castShadow = true;
      group.add(arms);
      
      // Wheels
      [-0.35, 0.35].forEach(x => {
        const wheel = new THREE.Mesh(
          new THREE.CylinderGeometry(0.2, 0.2, 0.1, 16),
          wheelMat
        );
        wheel.rotation.z = Math.PI / 2;
        wheel.position.set(x, 0.2, 0);
        wheel.castShadow = true;
        group.add(wheel);
      });
      
      // Status light
      const sprayLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.06, 8, 8),
        accentMat
      );
      sprayLight.position.set(0, 1.0, 0);
      group.add(sprayLight);
      break;

    case 'plow':
      // Plow frame
      const plowFrame = new THREE.Mesh(
        new THREE.BoxGeometry(0.8, 0.15, 1.2),
        metalMat
      );
      plowFrame.position.y = 0.25;
      plowFrame.castShadow = true;
      group.add(plowFrame);
      
      // Plow blades
      for(let i = 0; i < 3; i++) {
        const blade = new THREE.Mesh(
          new THREE.BoxGeometry(0.6, 0.4, 0.08),
          metalMat
        );
        blade.rotation.x = -0.5;
        blade.position.set(0, 0.35, -0.3 + i * 0.4);
        blade.castShadow = true;
        group.add(blade);
      }
      
      // Wheels
      [-0.35, 0.35].forEach(x => {
        const wheel = new THREE.Mesh(
          new THREE.CylinderGeometry(0.15, 0.15, 0.08, 16),
          wheelMat
        );
        wheel.rotation.z = Math.PI / 2;
        wheel.position.set(x, 0.15, 0.5);
        wheel.castShadow = true;
        group.add(wheel);
      });
      
      // Status light
      const plowLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.05, 8, 8),
        accentMat
      );
      plowLight.position.set(0, 0.6, 0);
      group.add(plowLight);
      break;

    case 'trailer':
      // Trailer bed
      const bed = new THREE.Mesh(
        new THREE.BoxGeometry(1.0, 0.1, 1.8),
        metalMat
      );
      bed.position.y = 0.35;
      bed.castShadow = true;
      group.add(bed);
      
      // Sides
      ['front', 'back', 'left', 'right'].forEach((side, i) => {
        let geom, pos;
        if(side === 'front' || side === 'back') {
          geom = new THREE.BoxGeometry(1.0, 0.4, 0.05);
          pos = new THREE.Vector3(0, 0.6, side === 'front' ? 0.87 : -0.87);
        } else {
          geom = new THREE.BoxGeometry(0.05, 0.4, 1.8);
          pos = new THREE.Vector3(side === 'left' ? -0.47 : 0.47, 0.6, 0);
        }
        const wall = new THREE.Mesh(geom, bodyMat);
        wall.position.copy(pos);
        wall.castShadow = true;
        group.add(wall);
      });
      
      // Wheels
      [-0.4, 0.4].forEach(x => {
        const wheel = new THREE.Mesh(
          new THREE.CylinderGeometry(0.2, 0.2, 0.12, 16),
          wheelMat
        );
        wheel.rotation.z = Math.PI / 2;
        wheel.position.set(x, 0.2, -0.5);
        wheel.castShadow = true;
        group.add(wheel);
      });
      
      // Status light
      const trailerLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.05, 8, 8),
        accentMat
      );
      trailerLight.position.set(0, 0.9, 0);
      group.add(trailerLight);
      break;

    case 'pump':
      // Pump body
      const pumpBody = new THREE.Mesh(
        new THREE.CylinderGeometry(0.25, 0.3, 0.5, 16),
        metalMat
      );
      pumpBody.position.y = 0.35;
      pumpBody.castShadow = true;
      group.add(pumpBody);
      
      // Motor housing
      const motor = new THREE.Mesh(
        new THREE.BoxGeometry(0.4, 0.3, 0.3),
        bodyMat
      );
      motor.position.set(0.35, 0.25, 0);
      motor.castShadow = true;
      group.add(motor);
      
      // Pipes
      const pipeIn = new THREE.Mesh(
        new THREE.CylinderGeometry(0.08, 0.08, 0.4, 8),
        new THREE.MeshStandardMaterial({ color: 0x666666 })
      );
      pipeIn.rotation.z = Math.PI / 2;
      pipeIn.position.set(-0.35, 0.35, 0);
      group.add(pipeIn);
      
      const pipeOut = new THREE.Mesh(
        new THREE.CylinderGeometry(0.08, 0.08, 0.5, 8),
        new THREE.MeshStandardMaterial({ color: 0x666666 })
      );
      pipeOut.position.set(0, 0.85, 0);
      group.add(pipeOut);
      
      // Status light
      const pumpLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.05, 8, 8),
        accentMat
      );
      pumpLight.position.set(0, 1.15, 0);
      group.add(pumpLight);
      break;

    case 'irrigation':
      // Center pivot
      const pivot = new THREE.Mesh(
        new THREE.CylinderGeometry(0.15, 0.2, 0.4, 8),
        metalMat
      );
      pivot.position.y = 0.2;
      pivot.castShadow = true;
      group.add(pivot);
      
      // Arm
      const arm = new THREE.Mesh(
        new THREE.BoxGeometry(2.0, 0.08, 0.08),
        metalMat
      );
      arm.position.set(0, 0.5, 0);
      arm.castShadow = true;
      group.add(arm);
      
      // Sprinkler heads
      for(let i = -0.8; i <= 0.8; i += 0.4) {
        const sprinkler = new THREE.Mesh(
          new THREE.ConeGeometry(0.05, 0.1, 6),
          new THREE.MeshStandardMaterial({ color: 0x3b82f6 })
        );
        sprinkler.rotation.x = Math.PI;
        sprinkler.position.set(i, 0.42, 0);
        group.add(sprinkler);
      }
      
      // Support wheel
      const irrWheel = new THREE.Mesh(
        new THREE.CylinderGeometry(0.12, 0.12, 0.06, 12),
        wheelMat
      );
      irrWheel.rotation.z = Math.PI / 2;
      irrWheel.position.set(0.9, 0.12, 0);
      group.add(irrWheel);
      
      // Status light
      const irrLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.05, 8, 8),
        accentMat
      );
      irrLight.position.set(0, 0.65, 0);
      group.add(irrLight);
      break;

    case 'tool':
      // Toolbox
      const toolbox = new THREE.Mesh(
        new THREE.BoxGeometry(0.6, 0.35, 0.35),
        new THREE.MeshStandardMaterial({ color: 0xdc2626, roughness: 0.4 })
      );
      toolbox.position.y = 0.25;
      toolbox.castShadow = true;
      group.add(toolbox);
      
      // Handle
      const handle = new THREE.Mesh(
        new THREE.BoxGeometry(0.35, 0.06, 0.06),
        metalMat
      );
      handle.position.set(0, 0.48, 0);
      group.add(handle);
      
      // Status light
      const toolLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.04, 8, 8),
        accentMat
      );
      toolLight.position.set(0.25, 0.45, 0);
      group.add(toolLight);
      break;

    default: // 'other' or unknown
      // Generic crate
      const crate = new THREE.Mesh(
        new THREE.BoxGeometry(0.5, 0.4, 0.5),
        new THREE.MeshStandardMaterial({ color: 0x8b5a2b, roughness: 0.8 })
      );
      crate.position.y = 0.25;
      crate.castShadow = true;
      group.add(crate);
      
      // Straps
      const strap1 = new THREE.Mesh(
        new THREE.BoxGeometry(0.52, 0.04, 0.04),
        metalMat
      );
      strap1.position.set(0, 0.35, 0.15);
      group.add(strap1);
      
      const strap2 = new THREE.Mesh(
        new THREE.BoxGeometry(0.52, 0.04, 0.04),
        metalMat
      );
      strap2.position.set(0, 0.35, -0.15);
      group.add(strap2);
      
      // Status light
      const crateLight = new THREE.Mesh(
        new THREE.SphereGeometry(0.04, 8, 8),
        accentMat
      );
      crateLight.position.set(0, 0.5, 0);
      group.add(crateLight);
      break;
  }

  // Add selection glow (invisible by default)
  const glowGeom = new THREE.SphereGeometry(1.0, 16, 16);
  const glowMesh = new THREE.Mesh(glowGeom, createGlowMaterial(baseColor));
  glowMesh.visible = false;
  glowMesh.name = 'glow';
  group.add(glowMesh);

  return group;
}

// Load equipment from page data
function loadEquipmentFromData() {
  const dataElement = document.getElementById('farm-equipment-data');
  if (!dataElement) {
    console.log('No equipment data found on page');
    return [];
  }
  
  try {
    return JSON.parse(dataElement.textContent);
  } catch (e) {
    console.error('Failed to parse equipment data:', e);
    return [];
  }
}

// Place equipment on the farm as 3D floating product cards
function placeEquipment() {
  const equipmentData = loadEquipmentFromData();
  console.log("🚜 Equipment data loaded:", equipmentData.length, "items");

  // Clear existing equipment
  while(equipmentGroup.children.length > 0) {
    equipmentGroup.remove(equipmentGroup.children[0]);
  }
  equipmentMeshes.length = 0;

  // Hide HTML marketplace cards - we're using 3D now!
  const marketplaceGrid = document.querySelector('.marketplace-grid');
  if (marketplaceGrid) {
    marketplaceGrid.style.display = equipmentData.length > 0 ? "none" : "";
  }

  if (equipmentData.length === 0) {
    console.log("ℹ️ No equipment data on this page");
    return;
  }
  
  // Organized grid layout - cards arranged in rows for easy browsing
  const cardsPerRow = 3;
  const rowSpacing = 7;        // Distance between rows (z)
  const columnSpacing = 5;     // Distance between columns (x)
  const startZ = 0;            // Start position
  
  equipmentData.forEach((item, index) => {
    const model = createProductCard(item);
    
    // Grid position calculation
    const row = Math.floor(index / cardsPerRow);
    const col = index % cardsPerRow;
    
    // Center the columns
    const totalWidth = (cardsPerRow - 1) * columnSpacing;
    const x = (col * columnSpacing) - (totalWidth / 2);
    const z = startZ - (row * rowSpacing);
    
    // Cards face forward (toward camera)
    model.rotation.y = 0;
    
    model.position.set(x, 0, z);
    model.userData = { ...item, index };
    model.name = `equipment-${item.id}`;
    
    // Good scale for visibility
    model.scale.setScalar(1.0);
    
    equipmentGroup.add(model);
    equipmentMeshes.push(model);
    
    console.log(`  🃏 Card placed: "${item.nom}" row ${row}, col ${col}`);
  });
  
  console.log(`✅ Placed ${equipmentData.length} floating cards in grid`);
}

// Initialize equipment
placeEquipment();

document.addEventListener("agriech:scene-refresh", () => {
  syncFarm3dMode();
  placeEquipment();
});

// Listen for search/filter events from the toolbar
document.addEventListener("agriech:filter-equipment", (e) => {
  const { query, status } = e.detail || {};
  const q = (query || "").toLowerCase().trim();
  equipmentMeshes.forEach(eq => {
    const d = eq.userData || {};
    const name = (d.nom || "").toLowerCase();
    const type = (d.type || "").toLowerCase();
    const owner = (d.proprietaire || "").toLowerCase();
    const etat = (d.etat || "").toLowerCase();
    const statusMap = { 'Disponible': 'available', 'Loue': 'rented', 'En Panne': 'down' };
    const mappedStatus = statusMap[d.etat] || '';
    const matchSearch = !q || name.indexOf(q) !== -1 || type.indexOf(q) !== -1 || owner.indexOf(q) !== -1;
    const matchStatus = !status || mappedStatus === status;
    eq.visible = matchSearch && matchStatus;
  });
});

// Create info card element
function createInfoCard() {
  const existing = document.getElementById('equipment-info-card');
  if (existing) return existing;
  
  const card = document.createElement('div');
  card.id = 'equipment-info-card';
  card.innerHTML = `
    <button class="close-btn" aria-label="Fermer">&times;</button>
    <div class="eq-status"></div>
    <h3 class="eq-name"></h3>
    <p class="eq-type"></p>
    <p class="eq-desc"></p>
    <div class="eq-price">
      <span class="price-value"></span>
      <span class="price-unit">TND / jour</span>
    </div>
    <p class="eq-owner"></p>
    <div class="eq-actions">
      <a class="btn btn-view" href="#">
        <i class="fas fa-eye"></i> Voir détails
      </a>
      <a class="btn btn-rent" href="#">
        <i class="fas fa-calendar-plus"></i> Louer
      </a>
    </div>
  `;
  document.body.appendChild(card);
  
  // Close button handler
  card.querySelector('.close-btn').addEventListener('click', () => {
    hideInfoCard();
    deselectEquipment();
  });
  
  return card;
}

const infoCard = createInfoCard();

function showInfoCard(data) {
  const card = document.getElementById('equipment-info-card');
  if (!card) return;
  
  // Status class
  let statusClass = 'available';
  let statusText = 'Disponible';
  if (data.etat === 'Loue') {
    statusClass = 'rented';
    statusText = 'Loué';
  } else if (data.etat === 'En Panne') {
    statusClass = 'down';
    statusText = 'En Panne';
  }
  
  card.querySelector('.eq-status').className = `eq-status ${statusClass}`;
  card.querySelector('.eq-status').textContent = statusText;
  card.querySelector('.eq-name').textContent = data.nom;
  card.querySelector('.eq-type').textContent = data.typeLabel || data.type;
  card.querySelector('.eq-desc').textContent = data.description || 'Pas de description disponible.';
  card.querySelector('.price-value').textContent = data.prixLocation?.toFixed(2) || '0.00';
  card.querySelector('.eq-owner').innerHTML = `<i class="fas fa-user"></i> ${data.proprietaire}`;
  
  // Update links
  card.querySelector('.btn-view').href = data.showUrl || '#';
  const rentBtn = card.querySelector('.btn-rent');
  if (data.etat === 'Disponible') {
    rentBtn.href = data.rentUrl || '#';
    rentBtn.style.display = 'inline-flex';
  } else {
    rentBtn.style.display = 'none';
  }
  
  card.classList.add('visible');
}

function hideInfoCard() {
  const card = document.getElementById('equipment-info-card');
  if (card) {
    card.classList.remove('visible');
  }
}

function selectEquipment(mesh) {
  if (selectedEquipment === mesh) return;
  
  // Deselect previous
  deselectEquipment();
  
  selectedEquipment = mesh;
  const glow = mesh.getObjectByName('glow');
  if (glow) {
    glow.visible = true;
    glow.material.opacity = 0.4;
  }
  
  // Pulse animation for selected card
  if (mesh.userData.cardMesh) {
    mesh.scale.setScalar(1.05);
  }
  
  showInfoCard(mesh.userData);
}

function deselectEquipment() {
  if (selectedEquipment) {
    const glow = selectedEquipment.getObjectByName('glow');
    if (glow) {
      glow.visible = false;
      glow.material.opacity = 0;
    }
    // Reset scale
    selectedEquipment.scale.setScalar(1.0);
    selectedEquipment = null;
  }
}

function highlightEquipment(mesh, highlight) {
  if (mesh === selectedEquipment) return; // Don't change if selected
  
  const glow = mesh.getObjectByName('glow');
  if (glow) {
    glow.visible = highlight;
    glow.material.opacity = highlight ? 0.2 : 0;
  }
  
  // Slight scale for hover feedback
  mesh.scale.setScalar(highlight ? 1.02 : 1.0);
}

// ============================================
// END OF 3D EQUIPMENT SYSTEM
// ============================================

// Raycaster for hover/click on parcels and equipment
const raycaster = new THREE.Raycaster();
const pointer = new THREE.Vector2();
let hoveredParcel = null;

// Mouse-based camera panning for looking left/right
let targetCameraX = 0;
let currentCameraX = 0;
const maxPanX = 3; // Maximum horizontal pan distance

function updatePointer(event) {
  const { clientX, clientY } = event;
  pointer.x = (clientX / window.innerWidth) * 2 - 1;
  pointer.y = -(clientY / window.innerHeight) * 2 + 1;
  
  // Update target camera X based on mouse position (horizontal panning)
  // Only if we're on the 3D marketplace page
  if (document.querySelector('.farm-3d-spacer')) {
    targetCameraX = pointer.x * maxPanX;
  }
}

window.addEventListener("pointermove", (event) => {
  updatePointer(event);
});

window.addEventListener("click", (event) => {
  // Check for equipment click first
  raycaster.setFromCamera(pointer, camera);
  
  // Get all meshes from equipment groups for intersection
  const equipmentChildMeshes = [];
  equipmentMeshes.forEach(eq => {
    eq.traverse(child => {
      if (child.isMesh && child.name !== 'glow') {
        equipmentChildMeshes.push(child);
      }
    });
  });
  
  const equipmentIntersects = raycaster.intersectObjects(equipmentChildMeshes, false);
  if (equipmentIntersects.length > 0) {
    // Find parent equipment group
    let target = equipmentIntersects[0].object;
    while (target.parent && !target.userData.id) {
      target = target.parent;
    }
    if (target.userData.id) {
      selectEquipment(target);
      return;
    }
  }
  
  // If clicked elsewhere, deselect
  if (selectedEquipment && equipmentIntersects.length === 0) {
    // Check if we didn't click on the info card
    const card = document.getElementById('equipment-info-card');
    if (card && !card.contains(event.target)) {
      deselectEquipment();
      hideInfoCard();
    }
  }
  
  // Parcel click handling
  if (hoveredParcel) {
    hoveredParcel.userData.highlighted = !hoveredParcel.userData.highlighted;
    hoveredParcel.material.color.set(
      hoveredParcel.userData.highlighted ? 0x9ad27d : hoveredParcel.userData.baseColor
    );
  }
});

// Animation loop
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

  if (isSnowy) {
    const drift = Math.sin(elapsed * 0.4) * 0.3;
    for (let i = 0; i < snowCount; i += 1) {
      const idx = i * 3;
      snowPositions[idx + 1] -= delta * (snowSpeeds[i] * 70.0);
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

  // Wind sway for crops
  for (let i = 0; i < cropData.length; i += 1) {
    const data = cropData[i];
    const sway = Math.sin(elapsed * 1.6 + data.phase) * 0.08;
    cropPosition.set(data.x, data.y, data.z);
    cropQuaternion.setFromEuler(new THREE.Euler(sway * 0.6, sway * 0.2, sway));
    cropMatrix.compose(cropPosition, cropQuaternion, cropScale);
    crops.setMatrixAt(i, cropMatrix);
  }
  crops.instanceMatrix.needsUpdate = true;

  // Smooth mouse-based horizontal camera panning
  currentCameraX += (targetCameraX - currentCameraX) * 0.05;
  camera.position.x = currentCameraX;
  // Also slightly rotate camera to look toward center
  camera.rotation.y = -currentCameraX * 0.03;

  // Floating cards animation - gentle hover and ring pulse
  equipmentMeshes.forEach(eq => {
    // Subtle floating motion
    const floatOffset = Math.sin(elapsed * 1.2 + eq.userData.index * 0.7) * 0.08;
    eq.position.y = floatOffset;
    
    // Pulsing ring effect
    if (eq.userData.ring) {
      const pulse = 0.6 + Math.sin(elapsed * 2 + eq.userData.index) * 0.2;
      eq.userData.ring.material.opacity = pulse;
      eq.userData.ring.scale.setScalar(1 + Math.sin(elapsed * 1.5 + eq.userData.index) * 0.1);
    }
    
    // Beam opacity pulse
    if (eq.userData.beam) {
      eq.userData.beam.material.opacity = 0.2 + Math.sin(elapsed * 2.5 + eq.userData.index) * 0.15;
    }
  });

  // Hover detection for parcels
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
  
  // Hover detection for equipment
  const equipmentChildMeshes = [];
  equipmentMeshes.forEach(eq => {
    eq.traverse(child => {
      if (child.isMesh && child.name !== 'glow') {
        equipmentChildMeshes.push(child);
      }
    });
  });
  
  const eqIntersects = raycaster.intersectObjects(equipmentChildMeshes, false);
  if (eqIntersects.length > 0) {
    // Find parent equipment group
    let target = eqIntersects[0].object;
    while (target.parent && !target.userData.id) {
      target = target.parent;
    }
    if (target.userData.id && target !== hoveredEquipment) {
      // Unhighlight previous
      if (hoveredEquipment) {
        highlightEquipment(hoveredEquipment, false);
      }
      hoveredEquipment = target;
      highlightEquipment(hoveredEquipment, true);
      canvas.style.cursor = 'pointer';
    }
  } else if (hoveredEquipment) {
    highlightEquipment(hoveredEquipment, false);
    hoveredEquipment = null;
    canvas.style.cursor = 'default';
  }

  renderer.render(scene, camera);
  rafId = requestAnimationFrame(animate);
}
animate();

// Responsive handling
function onResize() {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
}
window.addEventListener("resize", onResize);

// Pause rendering when the tab is hidden to save resources
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

// GSAP ScrollTrigger animations (only if GSAP is available)
// Camera target for scroll animations
let lookAtTarget = { x: 0, y: -0.6, z: -30 };

if (gsap && ScrollTrigger) {
  gsap.registerPlugin(ScrollTrigger);

  // Check if we're on the marketplace page with 3D spacer
  const farm3dSpacer = document.querySelector('.farm-3d-spacer');
  const scrollHint = document.getElementById('scroll-hint');
  
  if (farm3dSpacer && equipmentMeshes.length > 0) {
    // Calculate travel distance based on number of rows
    const cardsPerRow = 3;
    const rowSpacing = 7;
    const totalRows = Math.ceil(equipmentMeshes.length / cardsPerRow);
    const maxTravelZ = -(totalRows * rowSpacing) - 5;
    
    // Get header element for scroll animation
    const farmHeader = document.getElementById('farm-header');
    
    // 3D Farm Marketplace scroll - smooth camera movement through grid
    gsap.to(camera.position, {
      z: maxTravelZ + 8,
      y: 3.5, // Slightly higher for better overview
      ease: "none",
      scrollTrigger: {
        trigger: farm3dSpacer,
        start: "top top",
        end: "bottom bottom",
        scrub: 0.8,
        onUpdate: (self) => {
          // Hide scroll hint after scrolling starts
          if (scrollHint && self.progress > 0.03) {
            scrollHint.style.opacity = '0';
          }
          // Add scrolled class to header for compact mode
          if (farmHeader) {
            if (self.progress > 0.05) {
              farmHeader.classList.add('scrolled');
            } else {
              farmHeader.classList.remove('scrolled');
            }
          }
        }
      }
    });
    
    // Camera looks slightly down at cards
    gsap.to(camera.rotation, {
      x: -0.12,
      ease: "none",
      scrollTrigger: {
        trigger: farm3dSpacer,
        start: "top top",
        end: "bottom bottom",
        scrub: 0.8
      }
    });
    
    console.log("📜 3D Marketplace grid scroll enabled - " + totalRows + " rows");
  } else {
    // Default scroll behavior for other pages
    const walkTimeline = gsap.timeline({
      scrollTrigger: {
        trigger: ".ui",
        start: "top top",
        end: "bottom bottom",
        scrub: 0.8,
      },
    });

    walkTimeline
      .to(camera.position, { z: -50, y: 1.7, ease: "none" }, 0)
      .to(camera.rotation, { y: 0.06, x: -0.02, ease: "none" }, 0)
      .to(lookAtTarget, { z: -70, ease: "none" }, 0);
  }

// Progressive text reveal
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

// Space-like entrance: cards come forward one after another
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

// Subtle parallax for UI elements
document.querySelectorAll(".parallax").forEach((el) => {
  const speed = Number(el.dataset.speed || 0.3);
  gsap.to(el, {
    y: -60 * speed,
    ease: "none",
    scrollTrigger: {
      trigger: ".ui",
      start: "top top",
      end: "bottom bottom",
      scrub: true,
    },
  });
});
} // End of GSAP if block

// Keep camera oriented toward the travel direction
let cameraBob = 0;
function updateLookAt() {
  // Subtle head-bob for a walking sensation
  const t = clock.getElapsedTime();
  const bob = Math.sin(t * 3.2) * 0.03;
  camera.position.y += bob - cameraBob;
  cameraBob = bob;
  camera.lookAt(lookAtTarget.x, lookAtTarget.y, lookAtTarget.z);

  // Gentle sun drift to keep it alive
  sun.position.y += Math.sin(t * 0.4) * 0.0006;

  // Progressive parcel reveal based on camera distance
  const camZ = camera.position.z;
  parcelMeshes.forEach((parcel) => {
    const distance = camZ - parcel.position.z;
    const reveal = THREE.MathUtils.smoothstep(distance, 4, 22);
    parcel.material.opacity = Math.min(1, Math.max(parcel.material.opacity, reveal));
  });
}
if (gsap) {
  gsap.ticker.add(updateLookAt);
}

// Top bar time (local, updates every second)
const timeEl = document.getElementById("time");
const weatherSelect = document.getElementById("weather");
const timeSelect = document.getElementById("timeOfDay");

function applyWeatherTime() {
  const weather = weatherSelect ? weatherSelect.value.trim() : "Ensoleillé";
  const timeOfDay = timeSelect ? timeSelect.value.trim() : "Matin";
  setSceneMood({ weather, timeOfDay });
  if (gsap) {
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

console.log("🌾 Agriech 3D Farm scene initialized successfully!");
console.log("📍 Camera position:", camera.position);
console.log("🚜 Equipment count:", equipmentMeshes.length);
}

document.addEventListener("turbo:load", initializeAgriechScene);
document.addEventListener("DOMContentLoaded", initializeAgriechScene);
if (document.readyState !== "loading") {
  initializeAgriechScene();
}
